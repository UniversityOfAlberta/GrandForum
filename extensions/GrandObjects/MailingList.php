<?php

class MailingList {

    static $black_list = array('grand',
                               'administrator',
                               'mailman');
    static $lists = array();
    static $membershipCache = array();
    static $threadCache = array();

    static function getThreads($project_name){
        $project_name = mysql_real_escape_string($project_name);
        $sql = "SELECT m.refid_header, m.project_id, MIN(date) as first_date, MAX(date) as last_date
                FROM wikidev_projects p, wikidev_messages m
                WHERE m.project_id = p.projectid
                AND p.mailListName = '$project_name'
                GROUP BY m.refid_header
                ORDER BY first_date DESC";
        return DBFunctions::execSQL($sql);
    }
    
    static function getMessages($project_id, $thread){
        if(!isset(self::$threadCache[$project_id][$thread])){
            $thread = mysql_real_escape_string($thread);
            $project_id = mysql_real_escape_string($project_id);
            $data = DBFunctions::select(array('wikidev_messages'),
                                        array('user_name',
                                              'author',
                                              'address',
                                              'subject',
                                              'date',
                                              'body',
                                              'refid_header' => 'thread'),
                                        array('project_id' => EQ($project_id)));
            foreach($data as $row){
                self::$threadCache[$project_id][$row['thread']][] = $row;
            }
        }
        return self::$threadCache[$project_id][$thread];
    }

    /**
     * Returns the lists that the given person is on
     * @param Person $person The Person to get the lists for
     * @return array Returns the array of mailing lists
     */
    static function getPersonLists($person){
        $lists = array();
        $command = "/usr/lib/mailman/bin/find_member \"^{$person->getEmail()}$\" | grep \"     \"";
        exec($command, $output);
        foreach($output as $line){
            $line = trim($line);
            if(array_search($line, self::listLists()) !== false){
                $lists[] = $line;
            }
        }
        return $lists;
    }
    
    /**
     * Returns all the lists in the system
     * @return array Returns the array of mailing lists
     */
    static function listLists(){
        if(count(self::$lists) == 0){
            $command =  "/usr/lib/mailman/bin/list_lists -b 2> /dev/null";
		    exec($command, $lists);
		    foreach($lists as $list){
		        if(array_search($list, self::$black_list) === false){
		            self::$lists[] = $list;
		        }
		    }
		}
		return self::$lists;
    }

    /**
     * Subscribes the given Person to the given Project
     * @param Project $project The Project to subscribe to
     * @param Person $person The Person to subscribe
     * @param string $out The output string for the command output
     * @return int Returns 1 on success, and 0 on failure
     */ 
    static function subscribe($project, $person, &$out=""){
        global $wgImpersonating, $wgMessage;
        $listname = MailingList::listName($project);
        if($wgImpersonating){
            return 1;
        }
        if(self::hasUnsubbed($project, $person)){
            $wgMessage->addWarning("<b>{$person->getNameForForms()}</b> has requested to not be added to the <i>$listname</i> mailing list");
            return 1;
        }
        $email = $person->getEmail();
		$command =  "echo \"$email\" | /usr/lib/mailman/bin/add_members --welcome-msg=n --admin-notify=n -r - $listname 2> /dev/null";
		exec($command, $output);
		$out = $output;
		self::$membershipCache = array();
		if(!self::isSubscribed($project, $person)){
		    $wgMessage->addError("<b>{$person->getNameForForms()}</b> could not be added to the <i>$listname</i> mailing list");
		}
		if(count($output) > 0 && strstr($output[0], "Subscribed:") !== false){
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
        global $wgImpersonating, $wgMessage;
        if($wgImpersonating){
            return 1;
        }
        $listname = MailingList::listName($project);
        $email = $person->getEmail();
		$command =  "/usr/lib/mailman/bin/remove_members -n -N $listname \"$email\" 2> /dev/null";
		exec($command, $output);
		self::$membershipCache = array();
		if(self::isSubscribed($project, $person)){
		    $wgMessage->addError("<b>{$person->getNameForForms()}</b> could not be removed from <i>$listname</i> mailing list");
		}
		$out = $output;
		if(count($output) == 0 || (count($output) > 0 && $output[0] == "")){
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
            $command = "/usr/lib/mailman/bin/list_members $listname 2> /dev/null";
            exec($command, $output);
            self::$membershipCache[$listname] = $output;
        }
        $emails = self::$membershipCache[$listname];
        if(count($emails) > 0){
            foreach($emails as $addr){
                if(trim(strtolower($addr)) == trim(strtolower($email))){
                    return true;
                }
            }
        }
		return false;
    }
    
    static function manuallyUnsubscribe($project, $person){
        $listname = MailingList::listName($project);
        if(self::unsubscribe($project, $person)){
            $projects = DBFunctions::select(array('wikidev_projects'),
                                            array('projectid', 
                                                  'mailListName'),
                                            array('mailListName' => EQ($listname)));
            if(count($projects) > 0){
                foreach($projects as $proj){
                    if(!self::hasUnsubbed($proj['mailListName'], $person)){
                        DBFunctions::insert('wikidev_unsubs',
                                            array('project_id' => $proj['projectid'],
                                                  'user_id' => $person->getId()));
                    }
                }
            }
        }
    }
    
    /**
     * Returns whether this Person has manually unsubscribed from the given mailing list or not
     * @param Project $project The Project to check
     * @param Person $person The Person to check
     * @return boolean Returns true if the Person has unsubscribed from the mailing list and false if not
     */
    static function hasUnsubbed($project, $person){
        $listname = MailingList::listName($project);
        $data = DBFunctions::select(array('wikidev_unsubs', 'wikidev_projects'),
                                    array('project_id',
                                          'user_id'),
                                    array('project_id' => EQ(COL('projectid')),
                                          'mailListName' => EQ($listname),
                                          'user_id' => EQ($person->getId())));
        return (count($data) > 0);
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
