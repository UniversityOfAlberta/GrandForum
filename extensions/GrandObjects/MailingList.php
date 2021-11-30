<?php

/**
 * @package GrandObjects
 */

class MailingList extends BackboneModel {

    static $allMailingLists = array();
    static $cache = array();
    static $black_list = array('grand',
                               'administrator',
                               'mailman');
    static $lists = array();
    static $membershipCache = array();
    static $unsubCache = array();
    static $threadCache = array();
    
    var $id;
    var $name;
    var $public;
    var $rules = null;
    
    static function newFromId($id){
        if(isset($cache[$id])){
            return $cache[$id];
        }
        $data = DBFunctions::select(array('wikidev_projects'),
                                    array('*'),
                                    array('projectid' => EQ($id)));
        $list = new MailingList($data);
        $cache[$id] = &$list;
        return $list;
    }
    
    static function newFromName($name){
        $name = MailingList::listName($name);
        if(isset($cache[$name])){
            return $cache[$name];
        }
        $data = DBFunctions::select(array('wikidev_projects'),
                                    array('*'),
                                    array('mailListName' => EQ($name)));
        $list = new MailingList($data);
        $cache[$name] = &$list;
        return $list;
    }
    
    static function getAllMailingLists(){
        if(count(self::$allMailingLists) == 0){
            $data = DBFunctions::select(array('wikidev_projects'),
                                        array('*'),
                                        array(),
                                        array('mailListName' => 'ASC'));
            foreach($data as $row){
                if(array_search($row['mailListName'], MailingList::listLists()) !== false){
                    self::$allMailingLists[] = MailingList::newFromId($row['projectid']);
                }
            }
        }
        return self::$allMailingLists;
    }
    
    function __construct($data){
        if(count($data) > 0){
            $this->id = $data[0]['projectid'];
            $this->name = $data[0]['mailListName'];
            $this->public = $data[0]['public'];
        }
    }
    
    function getId(){
        return $this->id;
    }
    
    function getName(){
        return $this->name;
    }
    
    function isPublic(){
        return $this->public;
    }
    
    function getRules(){
        if($this->rules == null){
            $this->rules = array();
            $data = DBFunctions::select(array('wikidev_projects_rules'),
                                        array('*'),
                                        array('project_id' => EQ($this->id)));
            foreach($data as $row){
                $this->rules[] = MailingListRule::newFromId($row['id']);
            }
        }
        return $this->rules;
    }
    
    function create(){
        
    }
    
    function update(){
        
    }
    
    function delete(){
        
    }
    
    function toArray(){
        return array('id' => $this->id,
                     'name' => $this->name);
    }
    
    function exists(){
        return true;
    }
    
    function getCacheId(){
        
    }

    static function getThreads($project_name){
        $project_name = DBFunctions::escape($project_name);
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
            $thread = DBFunctions::escape($thread);
            $project_id = DBFunctions::escape($project_id);
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
    
    static function getPublicLists(){
        $lists = array();
        $data = DBFunctions::select(array('wikidev_projects'),
                                    array('mailListName'),
                                    array('public' => EQ('1')));
        if(count($data) > 0){
            foreach($data as $row){
                $lists[] = $row['mailListName'];
            }
        }
        return $lists;
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
     * Returns the lists that the given person should be on
     * @param Person $person The Person to get the lists for
     * @return array Returns the array of mailing lists
     */
    static function getPersonListsByRules($person){
        global $config;
        $lists = MailingList::getAllMailingLists();
        $personLists = array();
        foreach($lists as $list){
            $results = array();
            $subscribe = false;
            $roleResult = false;
            $subRoleResult = false;
            $projResult = false;
            $locResult = false;
            $rules = $list->getRules();
            $phaseRules = array();
            $projRules = array();
            foreach($rules as $rule){
                // Phase rules are a little different than other types
                if($rule->getType() == "PHASE"){
                    $phaseRules[] = $rule->getValue();
                }
            }
            foreach($rules as $rule){
                // Phase rules are a little different than other types
                if($rule->getType() == "PROJ"){
                    $projRules[] = $rule->getValue();
                }
            }
            foreach($rules as $rule){
                $subscribe = true;
                $value = $rule->getValue();
                switch($rule->getType()){
                    case "ROLE":
                        if($value == CHAMP && $person->isRoleDuring($value, EOT, EOT)){
                            foreach($person->getProjects() as $proj){
                                if(count($phaseRules) > 0){
                                    $roleResult = ($roleResult || (array_search($proj->getPhase(), $phaseRules) !== false));
                                }
                                else{
                                    $roleResult = ($roleResult || true);
                                }
                            }
                        }
                        else if($value == PL && $person->isRoleDuring(PL, EOT, EOT)){
                            $leadership = $person->leadership();
                            foreach($leadership as $proj){
                                if(count($projRules) > 0){
                                    $roleResult = ($roleResult || (array_search($proj->getId(), $projRules) !== false));
                                }
                                else if(count($phaseRules) > 0){
                                    $roleResult = ($roleResult || (array_search($proj->getPhase(), $phaseRules) !== false));
                                }
                                else{
                                    $roleResult = ($roleResult || true);
                                }
                            }
                        }
                        else if($value == TL && $person->isThemeLeaderDuring(EOT, EOT)){
                            $roleResult = ($roleResult || true);
                        }
                        else if($value == EVALUATOR && $person->isEvaluator()){
                            $roleResult = ($roleResult || true);
                        }
                        else if($value == STUDENT && $person->isStudent()){
                            $roleResult = ($roleResult || true);
                        }
                        else if($value == "Stakeholder" && $person->getStakeholder()){
                            $roleResult = ($roleResult || true);
                        }
                        else {
                            $roleResult = ($roleResult || $person->isRoleDuring($value, EOT, EOT));
                        }
                        $results['roleResult'] = $roleResult;
                        break;
                    case "SUB-ROLE":
                        $subRoles = array_flip($config->getValue('subRoles'));
                        $subRole = @$subRoles[$value];
                        if($person->isSubRole($value) || $person->isSubRole($subRole)){
                            $subRoleResult = ($subRoleResult || true);
                        }
                        $results['subRoleResult'] = $subRoleResult;
                        break;
                    case "PROJ":
                        $project = Project::newFromId($value);
                        $projResult = ($projResult || $person->isMemberOfDuring($project, EOT, EOT));
                        $results['projResult'] = $projResult;
                        break;
                    case "LOC":
                        $found = false;
                        foreach($person->getCurrentUniversities() as $uni){
                            $uni = University::newFromName($uni['university']);
                            if($uni->getId() == $value){
                                $found = true;
                                break;
                            }
                        }
                        $locResult = ($locResult || $found);
                        $results['locResult'] = $locResult;
                        break;
                }
            }
            foreach($results as $result){
                $subscribe = $subscribe && $result;
            }
            if($subscribe){
                $personLists[] = $list->getName(); 
            }
        }
        return $personLists;
    }
    
    /**
     * Returns all the lists in the system
     * @return array Returns the array of mailing lists
     */
    static function listLists(){
        if(count(self::$lists) == 0){
            $data = DBFunctions::select(array('wikidev_projects'),
                                        array('mailListName'));
            $listNames = array();
            foreach($data as $row){
                $listNames[] = $row['mailListName'];
            }
            $command =  "/usr/lib/mailman/bin/list_lists -b 2> /dev/null";
            exec($command, $lists);
            foreach($lists as $list){
                if(array_search($list, self::$black_list) === false && 
                   array_search($list, $listNames) !== false){
                    self::$lists[] = $list;
                }
            }
        }
        return self::$lists;
    }
    
    /**
     * Subscribes the given Person to all the mailing lists 
     * that the Person should be in based on the mailing list rules
     * @param Person $person The Person to subscribe
     */
    static function subscribeAll($person){
        global $wgMessage;
        foreach(MailingList::getPersonListsByRules($person) as $list){
            MailingList::subscribe($list, $person);
        }
        self::$membershipCache = array();
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
        if(trim($person->getEmail()) == ""){
            return 1;
        }
        $command =  "echo \"$email\" | /usr/lib/mailman/bin/add_members --welcome-msg=n --admin-notify=n -r - $listname 2> /dev/null";
        exec($command, $output);
        $out = $output;
        if(count($output) > 0 && strstr($output[0], "Subscribed:") !== false){
            self::$membershipCache[$listname][] = $email;
            return 1;
        }
        else{
            //$wgMessage->addError("<b>{$person->getNameForForms()}</b> could not be added to the <i>$listname</i> mailing list");
            return 0;
        }
    }
    
    /**
     * Unsubscribes the given Person from all the mailing lists 
     * that the Person should be in based on the mailing list rules
     * @param Person $person The Person to subscribe
     */
    static function unsubscribeAll($person){
        global $wgMessage;
        foreach(MailingList::getPersonListsByRules($person) as $list){
            MailingList::unsubscribe($list, $person);
        }
        self::$membershipCache = array();
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
        if(trim($person->getEmail()) == ""){
            return 1;
        }
        $command =  "/usr/lib/mailman/bin/remove_members -n -N $listname \"$email\" 2> /dev/null";
        exec($command, $output);
        $out = $output;
        if(count($output) == 0 || (count($output) > 0 && $output[0] == "")){
            self::$membershipCache[$listname] = array();
            return 1;
        }
        else{
            //$wgMessage->addError("<b>{$person->getNameForForms()}</b> could not be removed from <i>$listname</i> mailing list");
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
        $email = $person->getEmail();
        $emails = MailingList::listMembers($project);
        if(count($emails) > 0){
            foreach($emails as $addr){
                if(trim(strtolower($addr)) == trim(strtolower($email))){
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Returns an array of email addresses who are subscribed to the given mailing list
     * @param Project $project The Project to check
     * @return array An array of email addresses
     */
    static function listMembers($project){
        $listname = MailingList::listName($project);
        if(!isset(self::$membershipCache[$listname])){
            $command = "/usr/lib/mailman/bin/list_members $listname";
            exec($command, $output);
            self::$membershipCache[$listname] = $output;
        }
        return self::$membershipCache[$listname];
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
        self::$unsubCache = array();
    }
    
    /**
     * Returns whether this Person has manually unsubscribed from the given mailing list or not
     * @param Project $project The Project to check
     * @param Person $person The Person to check
     * @return boolean Returns true if the Person has unsubscribed from the mailing list and false if not
     */
    static function hasUnsubbed($project, $person){
        $listname = MailingList::listName($project);
        if(count(self::$unsubCache) == 0){
            self::$unsubCache[-1] = true;
            $data = DBFunctions::select(array('wikidev_unsubs', 'wikidev_projects'),
                                        array('mailListName', 'user_id'));
            foreach($data as $row){
                self::$unsubCache[$row['mailListName'].$row['user_id']] = true;
            }
        }
        return isset(self::$unsubCache[$listname.$person->getId()]);
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
}

?>
