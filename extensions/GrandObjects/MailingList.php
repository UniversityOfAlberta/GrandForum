<?php

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

    // This could be tricky, and for the moment, not entirely useful,
    // but it could be in the future.
    static function createMailingList($project){
        
    }
    
    // This could be tricky, and for the moment, not entirely useful,
    // but it could be in the future.
    static function removeMailingList($project){
        
    }
}

?>
