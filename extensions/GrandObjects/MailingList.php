<?php
global $listAdmins;
$listAdmins = array("dwt@ualberta.ca",
                    "dgolovan@ualberta.ca",
                    "adrian_sheppard@gnwc.ca");

class MailingList {

    // Subscribes the given Person to the given Project
    // Returns true on success, and false on failure
    static function subscribe($project, $person, &$out=""){
        global $wgImpersonating;
        if($wgImpersonating){
            return 1;
        }
        $listname = strtolower($project->getName());
        $email = $person->getEmail();
		$command =  "echo \"$email\" | /usr/lib/mailman/bin/add_members --welcome-msg=n --admin-notify=n -r - $listname";
		exec($command, $output);
		$out = $output;
		if(count($output) > 0 && strstr($output[0], "Subscribed:") !== false){
		    $sql = "SELECT projectid
			    FROM wikidev_projects
			    WHERE projectname = '$listname'";
			$row = array();
            $rows = DBFunctions::execSQL($sql);
            if(count($rows) > 0){
                $row = $rows[0];
            }
		    if(isset($row['projectid']) && $row['projectid'] != null){
			    $sql = "INSERT INTO wikidev_projectroles (projectid, userid) VALUES ('{$row['projectid']}','{$person->getName()}')";
			    DBFunctions::execSQL($sql, true);
		    }
		    return 1;
		}
		else{
		    return 0;
		}
    }

    // Unsubscribes the given Person from the given Project
    // Returns true on success, and false on failure
    static function unsubscribe($project, $person, &$out=""){
        global $wgImpersonating;
        if($wgImpersonating){
            return 1;
        }
        $listname = strtolower($project->getName());
        $email = $person->getEmail();
		$command =  "/usr/lib/mailman/bin/remove_members -n -N $listname $email";
		exec($command, $output);
		$out = $output;
		if(count($output) == 0 || (count($output) > 0 && $output[0] == "")){
		    $sql = "SELECT projectid
			    FROM wikidev_projects
			    WHERE projectname = '$listname'";
            $row = array();
            $rows = DBFunctions::execSQL($sql);
            if(count($rows) > 0){
                $row = $rows[0];
            }
		    if(isset($row['projectid']) && $row['projectid'] != null){
			    $sql = "DELETE FROM wikidev_projectroles WHERE userid = '{$person->getName()}' AND projectid = '{$row['projectid']}'";
			    DBFunctions::execSQL($sql, true);
		    }
		    return 1;
		}
		else{
		    return 0;
		}
    }
    
    // Returns true if the Person is subscribed to the given mailing list,
    // And false if not
    static function isSubscribed($project, $person){
        $listname = strtolower($project->getName());
        $name = $person->getName();
        $sql = "SELECT COUNT(*) as count
                  FROM wikidev_projects p, wikidev_projectroles r
                  WHERE p.projectname = '$listname'
                  AND p.projectid = r.projectid
                  AND r.userid = '$name'";
        $rows = DBFunctions::execSQL($sql);
		if($rows[0]['count'] > 0){
		    return true;
		}
		return false;
    }

    // Creates a new mailman mailing list
    static function createMailingList($project){
        global $listAdmins;
        $listname = strtolower($project->getName());
        $command = "/usr/lib/mailman/bin/newlist --quiet $listname ".implode('\n', $listAdmins)." BigLasagna";
        @exec($command, $output);
        $alias = "
        
## $listname mailing list
$listname:              |/usr/lib/mailman/mail/mailman post $listname
$listname-admin:        |/usr/lib/mailman/mail/mailman admin $listname
$listname-bounces:      |/usr/lib/mailman/mail/mailman bounces $listname
$listname-confirm:      |/usr/lib/mailman/mail/mailman confirm $listname
$listname-join:         |/usr/lib/mailman/mail/mailman join $listname
$listname-leave:        |/usr/lib/mailman/mail/mailman leave $listname
$listname-owner:        |/usr/lib/mailman/mail/mailman owner $listname
$listname-request:      |/usr/lib/mailman/mail/mailman request $listname
$listname-subscribe:    |/usr/lib/mailman/mail/mailman subscribe $listname
$listname-unsubscribe:  |/usr/lib/mailman/mail/mailman unsubscribe $listname";
        
        $contents = file_get_contents("/etc/aliases");
        $contents .= $alias;
        file_put_contents("/etc/aliases", $contents);
        exec("/usr/bin/newaliases", $output);
        
        exec("/usr/lib/mailman/bin/config_list");
        
        $sql = "INSERT INTO `wikidev_projects` (`projectname`,`mailListName`)
                VALUES ('{$project->getName()}','$listname')";
        DBFunctions::execSQL($sql, true);
    }
    
    // This could be tricky, and for the moment, not entirely useful,
    // but it could be in the future.
    static function removeMailingList($project){
        $listname = strtolower($project->getName());
        $command = "/usr/lib/mailman/bin/rmlist $listname";
    }
}

?>
