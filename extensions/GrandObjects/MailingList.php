<?php
global $listAdmins;
$listAdmins = array("dwt@ualberta.ca",
                    "adrian_sheppard@gnwc.ca");

class MailingList {

    static $membershipCache = array();

    /**
     * Subscribes the given Person to the given Project
     * @param Project $project The Project to subscribe to
     * @param Person $person The Person to subscribe
     * @param string $out The output string for the command output
     * @return int Returns 1 on success, and 0 on failure
     */ 
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

    /**
     * Unsubscribes the given Person from the given Project
     * @param Project $project The Project to unsubscribe from
     * @param Person $person The Person to unsubscribe
     * @param string $out The output string for the command output
     * @return int Returns 1 on success, and 0 on failure
     */
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
    
    /**
     * Returns whether the Person is subscribed to the given mailing list or not 
     * (This is potentially slow if ran on all lists since it needs to do a system call)
     * @param Project $project The Project to check 
     * @param Person $person The Person to check
     * @return boolean Returns true if the Person is subscribed to the given mailing list and false if not
     */
    static function isSubscribed($project, $person){
        $listname = strtolower($project->getName());
        $email = $person->getEmail();
        if(!isset(self::$membershipCache[$listname])){
            $command = "/usr/lib/mailman/bin/list_members $listname";
            exec($command, $output);
            self::$membershipCache[$listname] = $output;
        }
        $emails = self::$membershipCache[$listname];
        if(count($emails) > 0){
            foreach($emails as $addr){
                if($addr == $email){
                    return true;
                }
            }
        }
		return false;
    }

    // Creates a new mailman mailing list
    static function createMailingList($project){
        /*
        global $listAdmins;
        $output = "";
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
        
        //while(file_exists("/tmp/aliases")){
            // Try again in 1 second
        //    sleep(1);
        //}
        $contents = file_get_contents("/etc/aliases");
        $contents .= $alias;
        file_put_contents("/tmp/aliases", $contents);
        exec("/usr/sbin/updatealiases", $output);
        print_r($output);
        exec("/usr/bin/newaliases", $output);
        print_r($output);
        //exec("/usr/lib/mailman/bin/config_list");
        //unlink("/tmp/aliases");
        
        $sql = "INSERT INTO `wikidev_projects` (`projectname`,`mailListName`)
                VALUES ('{$project->getName()}','$listname')";
        DBFunctions::execSQL($sql, true);
        */
    }
    
    // Removes the specified mailman mailing list
    static function removeMailingList($project){
        $listname = strtolower($project->getName());
        exec("/usr/lib/mailman/bin/rmlist $listname");
        $sql = "DELETE FROM `wikidev_projects`
                WHERE `mailListName` = '$listname'";
        DBFunctions::execSQL($sql, true);
    }
    
    /**
     * Returns all the location based lists
     * Location lists are considered to be between 1000 and 1999 inclusive
     * @return array Returns all the location based lists
     */
    static function getLocationBasedLists(){
        $sql = "SELECT mailListName
                FROM wikidev_projects m
                WHERE m.projectid >= 1000
                AND m.projectid <= 1999";
        $data = DBFunctions::execSQL($sql);
        $lists = array();
        foreach($data as $row){
            $lists[] = $row['mailListName'];
        }
        return $lists;
    }
    
    // TODO: Put this in the database somewhere since this is a really ugly function
    static function getListByUniversity($university){
        $hash = array('University of British Columbia' => 'grand-vancouver',
                      'Simon Fraser University' => 'grand-vancouver',
                      'Emily Carr University of Art and Design', 'grand-vancouver',
                      'University of Alberta' => 'grand-alberta',
                      'University of Calgary' => 'grand-calgary',
                      'University of Ottawa' => 'grand-ottawa',
                      'Carleton University' => 'grand-ottawa',
                      'University of Victoria' => 'grand-victoria',
                      'University of Toronto' => 'grand-toronto',
                      'Ryerson University' => 'grand-toronto',
                      'York University' => 'grand-toronto',
                      'Ontario College of Art & Design' => 'grand-toronto',
                      'University of Ontario Institute of Technology' => 'grand-toronto');
        return @$hash[$university];             
    }
}

?>
