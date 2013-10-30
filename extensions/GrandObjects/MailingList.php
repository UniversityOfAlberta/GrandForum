<?php

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
        $listname = MailingList::listName($project);
        $email = $person->getEmail();
		$command =  "echo \"$email\" | /usr/lib/mailman/bin/add_members --welcome-msg=n --admin-notify=n -r - $listname";
		exec($command, $output);
		$out = $output;
		if(count($output) > 0 && strstr($output[0], "Subscribed:") !== false){
		    $rows = DBFunctions::select(array('wikidev_projects'),
		                                array('projectid'),
		                                array('projectname' => EQ($listname)));
			$row = array();
            if(count($rows) > 0){
                $row = $rows[0];
            }
		    if(isset($row['projectid']) && $row['projectid'] != null){
		        DBFunctions::insert('wikidev_projectroles',
		                            array('projectid' => $row['projectid'],
		                                  'userid' => $person->getName()));
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
        $listname = MailingList::listName($project);
        $email = $person->getEmail();
		$command =  "/usr/lib/mailman/bin/remove_members -n -N $listname $email";
		exec($command, $output);
		$out = $output;
		if(count($output) == 0 || (count($output) > 0 && $output[0] == "")){
		    $rows = DBFunctions::select(array('wikidev_projects'),
		                                array('projectid'),
		                                array('projectname' => EQ($listname)));
            $row = array();
            if(count($rows) > 0){
                $row = $rows[0];
            }
		    if(isset($row['projectid']) && $row['projectid'] != null){
		        DBFunctions::delete('wikidev_projectroles',
		                            array('userid' => EQ($person->getName()),
		                                  'projectid' => EQ($row['projectid'])));
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
        $listname = MailingList::listName($project);
        $email = $person->getEmail();
        if(!isset(self::$membershipCache[$listname])){
            $command = "/usr/lib/mailman/bin/list_members $listname";
            exec($command, $output);
            self::$membershipCache[$listname] = $output;
        }
        $emails = self::$membershipCache[$listname];
        if(count($emails) > 0){
            foreach($emails as $addr){
                if(strtolower($addr) == strtolower($email)){
                    return true;
                }
            }
        }
		return false;
    }
    
    /**
     * Returns a list name for the given string or Project
     * @param mixed $project The string or Project
     * @return string The list name
     */
    static function listName($project){
        if($project instanceof Project){
            $listname = strtolower($project->getName());
        }
        else{
            $listname = $project;
        }
        return $listname;
    }

    // Creates a new mailman mailing list
    static function createMailingList($project){
        /*
        global $wgListAdmins, $wgListAdminPassword;
        $output = "";
        $listname = strtolower($project->getName());
        $command = "/usr/lib/mailman/bin/newlist --quiet $listname ".implode('\n', $listAdmins)." $wgListAdminPassword";
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
        $data = DBFunctions::select(array('wikidev_projects'),
                                    array('mailListName'),
                                    array('projectid' => GTEQ(1000),
                                          'projectid' => LTEQ(1999)));
        $lists = array();
        foreach($data as $row){
            $lists[] = $row['mailListName'];
        }
        return $lists;
    }
    
    // TODO: Put this in the database somewhere since this is a really ugly function
    static function getListByUniversity($university){
        $hash = array('University of British Columbia' => array('grand-vancouver'),
                      'Simon Fraser University' => array('grand-vancouver'),
                      'Emily Carr University of Art and Design', array('grand-vancouver'),
                      'University of Alberta' => array('grand-alberta'),
                      'University of Calgary' => array('grand-calgary'),
                      'University of Ottawa' => array('grand-ottawa', 'grand-ontario'),
                      'Carleton University' => array('grand-ottawa', 'grand-ontario'),
                      'University of Victoria' => array('grand-victoria'),
                      'University of Toronto' => array('grand-toronto', 'grand-ontario'),
                      'Ryerson University' => array('grand-toronto', 'grand-ontario'),
                      'York University' => array('grand-toronto', 'grand-ontario'),
                      'Ontario College of Art & Design' => array('grand-toronto', 'grand-ontario'),
                      'University of Ontario Institute of Technology' => array('grand-toronto', 'grand-ontario'),
                      'Queen`s University' => array('grand-ontario'),
                      'University of Waterloo' => array('grand-ontario'),
                      'University of Western Ontario' => array('grand-ontario'),
                      'Wilfrid Laurier University' => array('grand-ontario'));
        if(isset($hash[$university])){
            return $hash[$university];
        }
        return array();           
    }
}

?>
