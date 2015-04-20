<?php

/**
 * @package GrandObjects
 */

class Person extends BackboneModel {

    static $cache = array();
    static $rolesCache = array();
    static $universityCache = array();
    static $coLeaderCache = array();
    static $leaderCache = array();
    static $aliasCache = array();
    static $authorshipCache = array();
    static $namesCache = array();
    static $idsCache = array();
    static $disciplineMap = array();

    var $user = null;
    var $name;
    var $email;
    var $nationality;
    var $gender;
    var $photo;
    var $twitter;
    var $website;
    var $publicProfile;
    var $privateProfile;
    var $realname;
    var $projects;
    var $university;
    var $universityDuring;
    var $isProjectLeader;
    var $isProjectCoLeader;
    var $groups;
    var $roles;
    var $rolesDuring;
    var $isEvaluator = array();
    var $isProjectManager = null;
    var $relations;
    var $hqps;
    var $historyHqps;
    var $contributions;
    var $multimedia;
    var $acknowledgements;
    var $aliases = false;
    var $budgets = array();
    var $leadershipCache = array();
    var $themesCache = array();
    var $hqpCache = array();
    var $projectCache = array();
    var $evaluateCache = array();
    
    /**
     * Returns a new Person from the given id
     * @param int $id The id of the person
     * @return Person The Person from the given id
     */
    static function newFromId($id){
        if(isset(self::$cache[$id])){
            return self::$cache[$id];
        }
        self::generateNamesCache();
        $data = array();
        if(isset(self::$idsCache[$id])){
            $data[] = self::$idsCache[$id];
        }
        $person = new Person($data);
        self::$cache[$person->id] = &$person;
        self::$cache[$person->name] = &$person;
        return $person;
    }
    
    /**
     * Returns a new Person from the given name
     * @param string $name The name of the person
     * @return Person The Person from the given name
     */
    static function newFromName($name){
        $name = str_replace(' ', '.', $name);
        if(isset(Person::$cache[$name])){
            return Person::$cache[$name];
        }
        self::generateNamesCache();
        $data = array();
        if(isset(self::$namesCache[$name])){
            $data[] = self::$namesCache[$name];
        }
        $person = new Person($data);
        self::$cache[$person->id] = &$person;
        self::$cache[$person->name] = &$person;
        return $person;
    }
    
    /**
     * Returns a new Person from the given reversed name
     * @param string $name The reversed name of the person
     * @return Person The Person from the given reversed name
     */
    static function newFromReversedName($name){
        $exploded = explode(",", $name, 2);
        if(count($exploded) == 2){
            $fullName = trim($exploded[1])." ".trim($exploded[0]);
        }
        else{
            $fullName = $exploded[0];
        }
        return self::newFromNameLike($fullName);
    }
    
    /**
     * Returns a new Person from the given email (null if not found)
     * In the event of a collision, the first user is returned
     * @param string $email The email address of the Person
     * @return Person The Person from the given email
     */
    static function newFromEmail($email){
        $data = DBFunctions::select(array('mw_user'),
                                    array('user_id'),
                                    array('user_email' => $email));
        if(count($data) > 0){
            return Person::newFromId($data[0]['user_id']);
        }
        else{
            return null;
        }
    }

    /**
     * Returns a new Person from the given Mediawiki User
     * @param User $user The Mediawiki User
     * @return Person The Person from the given Mediawiki User
     */
    static function newFromUser($user){
        return Person::newFromId($user->getId());
    }

    /**
     * Returns a new Person from the current logged in user ($wgUser)
     * @return Person The Person who is currently logged in
     */
    static function newFromWgUser(){
        global $wgUser;
        return Person::newFromId($wgUser->getId());
    }
    
    /**
     * Returns a new Person who's name is similar to $name
     * Similarity is based on re-arranging the name where there are spaces, or dots etc.
     * Abbreviated names will also attempt to be matched
     * @param string $name The name of the Person
     * @return Person the Person that matches the name
     */
    static function newFromNameLike($name){
        $name = Person::cleanName($name);
        self::generateNamesCache();
        $data = array();
        if(isset(self::$namesCache[$name])){
            $data = array(0 => self::$namesCache[$name]);
        }
        return new Person($data);
    }

    /**
     * Returns a new Person from the given alias, if found
     * the respective user ID is valid (ie, non-zero).
     * NOTE: if the alias is not unique, an exception is thrown
     * @param string $alias The alias of the Person
     * @return Person the Person from the given alias
     */
    static function newFromAlias($alias) {
        // Normalize the alias: trim, remove duplicate spaces / dots, and strip HTML.
        $alias = preg_replace(
                array('/\s+/', '/\.+/', '/\s*\.+\s*/', '/<[^>]*>/'),
                array(' ', '.', '. ', ''),
                $alias);
        $alias = trim($alias);
        
        if (array_key_exists($alias, self::$cache)) {
            return self::$cache[$alias];
        }
        else {
            self::generateAliasCache();
            $aliases = self::$aliasCache;
            if(isset($aliases[$alias])){
                $data = $aliases[$alias];
            }
            else{
                $data = array();
            }
        }

        switch (count($data)) {
        case 0:
            self::$cache[$alias] = false;
            return false;
        case 1:
            // Check again the cache, in case the alias is an alternate
            // for an already-instantiated user.
            $id = $data[0]['user_id'];
            if (array_key_exists($id, self::$cache)) {
                // Mark this alias too.
                self::$cache[$alias] = self::$cache[$id];
                return self::$cache[$id];
            }

            $person = new Person($data);
            self::$cache[$alias] = &$person;
            self::$cache[$person->getId()] = &$person;
            self::$cache[$person->getName()] = &$person;
            return $person;
        default:
            throw new DomainException("Alias is not unique.");
        }
    }
    
    static function cleanName($name){
        $name = preg_replace("/\(.*\)/", "", $name);
        $name = str_replace("'", "", $name);
        $name = str_replace(".", "", $name);
        $name = str_replace("*", "", $name);
        $name = trim($name);
        return $name;
    }
    
    /**
     * Caches the resultset of the alis table for superfast access
     */
    static function generateAliasCache(){
        if(count(self::$aliasCache) == 0){
            $uaTable = getTableName("user_aliases");
            $uTable = getTableName("user");
            $sql = "SELECT ua.alias, u.user_id, u.user_name, u.user_real_name, u.user_email, u.user_twitter, u.user_website, user_public_profile, user_private_profile, user_nationality, user_gender
                    FROM `mw_user_aliases` as ua, `mw_user` as u 
                    WHERE ua.user_id = u.user_id
                    AND u.deleted != '1'";
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                self::$aliasCache[$row['alias']] = array(0 => $row);
            }
        }
    }

    /**
     * Caches the resultset of the user table for superfast access
     */
    static function generateNamesCache(){
        if(count(self::$namesCache) == 0){
            $sql = "SELECT `user_id`,`user_name`,`user_real_name`,`user_email`,`user_twitter`,`user_website`,`user_public_profile`,`user_private_profile`,`user_nationality`,`user_gender`
                    FROM `mw_user` u
                    WHERE `deleted` != '1'";
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                $exploded = explode(".", $row['user_name']);
                $firstName = @$exploded[0];
                $lastName = @$exploded[1];
                self::$idsCache[$row['user_id']] = $row;
                self::$namesCache[$row['user_name']] = $row;
                self::$namesCache["$firstName $lastName"] = $row;
                self::$namesCache["$lastName $firstName"] = $row;
                self::$namesCache["$firstName ".substr($lastName, 0, 1)] = $row;
                self::$namesCache["$lastName ".substr($firstName, 0, 1)] = $row;
                self::$namesCache[substr($firstName, 0, 1)." $lastName"] = $row;
                if(trim($row['user_real_name']) != '' && $row['user_name'] != trim($row['user_real_name'])){
                    self::$namesCache[str_replace("&nbsp;", " ", $row['user_real_name'])] = $row;
                }
            }
        }
    }
    
    /**
     * Caches the resultset of the user roles table
     * NOTE: This only caches the current roles, not the history
     */
    static function generateRolesCache(){
        if(count(self::$rolesCache) == 0){
            $sql = "SELECT *
                    FROM grand_roles
                    WHERE (end_date = '0000-00-00 00:00:00'
                           OR end_date > CURRENT_TIMESTAMP)
                    AND start_date <= CURRENT_TIMESTAMP";
            $data = DBFunctions::execSQL($sql);
            if(count($data) > 0){
                foreach($data as $row){
                    if(!isset(self::$rolesCache[$row['user_id']])){
                        self::$rolesCache[$row['user_id']] = array();
                    }
                    self::$rolesCache[$row['user_id']][] = $row;
                }
            }
        }
    }
    
    /**
     * Caches the resultset of the co leaders
     */
    static function generateCoLeaderCache(){
        if(count(self::$coLeaderCache) == 0){
            $sql = "SELECT *
                    FROM grand_project_leaders l, grand_project p
                    WHERE l.type = 'co-leader'
                    AND p.id = l.project_id
                    AND (l.end_date = '0000-00-00 00:00:00'
                         OR l.end_date > CURRENT_TIMESTAMP)";
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                self::$coLeaderCache[$row['user_id']][] = $row;
            }
        }
    }
    
    /**
     * Caches the resultset of the leaders
     */
    static function generateLeaderCache(){
        if(count(self::$leaderCache) == 0){
            $sql = "SELECT *
                    FROM grand_project_leaders l, grand_project p
                    WHERE l.type = 'leader'
                    AND p.id = l.project_id
                    AND (l.end_date = '0000-00-00 00:00:00'
                         OR l.end_date > CURRENT_TIMESTAMP)";
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                self::$leaderCache[$row['user_id']][] = $row;
            }
        }
    }
    
    /**
     * Caches the resultset of the user universities
     */
    static function generateUniversityCache(){
        if(count(self::$universityCache) == 0){
            $data = DBFunctions::select(array('grand_user_university' => 'uu',
                                              'grand_universities' => 'u',
                                              'grand_positions' => 'p'),
                                        array('*'),
                                        array('u.university_id' => EQ(COL('uu.university_id')),
                                              'uu.position_id' => EQ(COL('p.position_id'))),
                                        array('uu.id' => 'DESC'));
            foreach($data as $row){
                if(!isset(self::$universityCache[$row['user_id']]) || 
                   (self::$universityCache[$row['user_id']]['date'] != '0000-00-00 00:00:00' && 
                    self::$universityCache[$row['user_id']]['date'] <= $row['end_date']) || // Get the most recent
                   $row['end_date'] == '0000-00-00 00:00:00'){
                    self::$universityCache[$row['user_id']] = 
                        array("university" => $row['university_name'],
                              "department" => $row['department'],
                              "position"   => $row['position'],
                              "date"       => $row['end_date']);
                }
            }
        }
    }
    
    /**
     * Caches the resultset of the disciplines map
     */
    static function generateDisciplineMap(){
        if(count(self::$disciplineMap) == 0){
            $sql = "SELECT m.department, d.discipline
                    FROM `grand_disciplines_map` m, `grand_disciplines` d
                    WHERE m.discipline = d.id";
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                self::$disciplineMap[strtolower($row['department'])] = $row['discipline'];
            }
        }
    }
    
    /**
     * Caches the resultset of the product authors
     */
    static function generateAuthorshipCache(){
        if(count(self::$authorshipCache) == 0){
            $sql = "SELECT *
                    FROM `grand_product_authors`";
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                self::$authorshipCache[$row['author']][] = $row['product_id'];
            }
        }
    }
    
    /**
     * Returns an array of all University names
     * @return array An array of all University names
     */
    static function getAllUniversities(){
        //TODO: This should eventually be extracted to a new Class
        $data = DBFunctions::select(array('grand_universities'),
                                    array('*'),
                                    array(),
                                    array('`order`' => 'ASC'));
        $universities = array();
        foreach($data as $row){
            $universities[$row['university_id']] = $row['university_name'];
        }
        return $universities;
    }
    
    /**
     * Returns an array of all Position names
     * @return array An array of all Position names
     */
    static function getAllPositions(){
        //TODO: This should eventually be extracted to a new Class
        $data = DBFunctions::select(array('grand_positions'),
                                    array('*'),
                                    array(),
                                    array('`order`' => 'ASC'));
        $positions = array();
        foreach($data as $row){
            $positions[$row['position_id']] = $row['position'];
        }
        return $positions;
    }
    
    /**
     * Returns an array of all Department names
     * @return array An array of all Department names
     */
    static function getAllDepartments(){
        //TODO: This should eventually be extracted to a new Class
        $data = DBFunctions::select(array('grand_user_university'),
                                    array('*'),
                                    array());
        $departments = array();
        foreach($data as $row){
            $departments[$row['department']] = $row['department'];
        }
        return $departments;
    }
    
    /**
     * Returns the default University name
     * @return string The default University name
     */
    static function getDefaultUniversity(){
        $data = DBFunctions::select(array('grand_universities'),
                                    array('*'),
                                    array('`default`' => EQ(1)));
        if(count($data) > 0){
            return $data[0]['university_name'];
        }
        return "";
    }
    
    /**
     * Returns the default Position name
     * @return string The default Position name
     */
    static function getDefaultPosition(){
        $data = DBFunctions::select(array('grand_positions'),
                                    array('*'),
                                    array('`default`' => EQ(1)));
        if(count($data) > 0){
            return $data[0]['position'];
        }
        return "";
    }
    
    /**
     * Returns all the People with the given ids
     * @param array $ids The array of ids
     * @return array The array of People
     */
    static function getByIds($ids){
        $data = DBFunctions::select(array('mw_user'),
                                    array('*'),
                                    array('user_id' => IN($ids)));
        $people = array();
        foreach($data as $row){
            if(isset(self::$cache[$row['user_id']])){
                $people[] = self::$cache[$row['user_id']];
            }
            else{
                $person = new Person(array($row));
                self::$cache[$person->getId()] = $person;
                $people[$person->getId()] = $person;
            }
        }
        return $people;
    }
    
    /**
     * Returns all the People who currently have at least the Staff role
     * @return array The People who currently have at least the Staff fole
     */
    static function getAllStaff(){
        $data = DBFunctions::select(array('mw_user'),
                                    array('user_id', 'user_name'),
                                    array('deleted' => NEQ(1)),
                                    array('user_name' => 'ASC'));
        $people = array();
        foreach($data as $row){
            $rowA = array();
            $rowA[0] = $row;
            $person = Person::newFromId($rowA[0]['user_id']);
            if($person->isRoleAtLeast(STAFF)){
                $people[] = $person;
            }
        }
        return $people;
    }

    /**
     * Returns an array of People of the type $filter
     * @param string $filter The role to filter by
     * @return array The array of People of the type $filter
     */
    static function getAllPeople($filter=null){
        $data = DBFunctions::select(array('mw_user'),
                                    array('user_id', 'user_name'),
                                    array('deleted' => NEQ(1)),
                                    array('user_name' => 'ASC'));
        $people = array();
        foreach($data as $row){
            $rowA = array();
            $rowA[0] = $row;
            $person = Person::newFromId($rowA[0]['user_id']);
            if($person->getName() != "WikiSysop" && ($filter == null || $filter == "all" || $person->isRole($filter))){
                $people[] = $person;
            }
        }
        return $people;
    }
    
    /**
     * Returns an array of People of the type $filter between $startRange and $endRange
     * @param string $filter The role to filter by
     * @param string $startRange The start date of the role
     * @param string $endRange The end date of the role
     * @return array The array of People of the type $filter between $startRange and $endRange
     */
    static function getAllPeopleDuring($filter=null, $startRange, $endRange){
        $data = DBFunctions::select(array('mw_user'),
                                    array('user_id', 'user_name'),
                                    array('deleted' => NEQ(1)),
                                    array('user_name' => 'ASC'));
        $people = array();
        foreach($data as $row){
            $rowA = array();
            $rowA[0] = $row;
            $person = Person::newFromId($rowA[0]['user_id']);
            if($person->getName() != "WikiSysop" && ($filter == null || $filter == "all" || $person->isRoleDuring($filter, $startRange, $endRange))){
                $people[] = $person;
            }
        }
        return $people;
    }
    
    /**
     * Returns an array of People of the type $filter
     * @param string $filter The role to get ('all' if including everyone, even if on no project)
     * @param string $date The date that the person was on the role $filter
     * @return array An array of People of the type $filter
     */
    static function getAllPeopleOn($filter=null, $date){
        $data = DBFunctions::select(array('mw_user'),
                                    array('user_id', 'user_name'),
                                    array('deleted' => NEQ(1)),
                                    array('user_name' => 'ASC'));
        $people = array();
        foreach($data as $row){
            $rowA = array();
            $rowA[0] = $row;
            $person = Person::newFromId($rowA[0]['user_id']);
            if($person->getName() != "WikiSysop" && ($filter == null || $filter == "all" || $person->isRoleOn($filter, $date))){
                $people[] = $person;
            }
        }
        return $people;
    }
    
    /// Returns an array of registered evaluators (Person instances).
    /// Optionally, user IDs can be filtered out from the query as a single
    /// ID as a string, or an array of user IDs.
    static function getAllEvaluators($filterout = 4) {
        if (is_array($filterout)) {
            $managers = Person::getAllPeople(MANAGER);
            foreach($managers as $manager){
                $filterout[] = $manager->getId();
            }
            $filterout = implode(',', $filterout);
        }
        if (strlen($filterout) > 0)
            $filterout = "WHERE user_id NOT IN ({$filterout})";

        $ret = array();
        $data = DBFunctions::execSQL("SELECT DISTINCT user_id FROM grand_eval {$filterout};");
        foreach ($data as &$q){
            $ret[$q['user_id']] = Person::newFromId($q['user_id']);
        }
        return $ret;
    }

    static function getAllProjectManagers() {
        
        $ret = array();
        $sql = "SELECT pl.user_id FROM grand_project_leaders pl, mw_user u
                WHERE pl.user_id NOT IN (4, 150)
                AND pl.type='manager'
                AND u.user_id = pl.user_id
                AND u.deleted != '1'
                AND (pl.end_date = '0000-00-00 00:00:00'
                     OR pl.end_date > CURRENT_TIMESTAMP)";
        $data = DBFunctions::execSQL($sql);
        
        foreach ($data as &$row){
            $ret[$row['user_id']] = Person::newFromId($row['user_id']);
        }

        return $ret;
    }

    // Constructor
    // Takes in a resultset containing the 'user id' and 'user name'
    function Person($data){
        if(count($data) > 0){
            $this->id = $data[0]['user_id'];
            $this->name = $data[0]['user_name'];
            $this->realname = $data[0]['user_real_name'];
            $this->email = $data[0]['user_email'];
            $this->gender = $data[0]['user_gender'];
            $this->nationality = $data[0]['user_nationality'];
            $this->university = false;
            $this->twitter = $data[0]['user_twitter'];
            $this->website = $data[0]['user_website'];
            $this->publicProfile = $data[0]['user_public_profile'];
            $this->privateProfile = $data[0]['user_private_profile'];
            $this->hqps = null;
            $this->historyHqps = null;
        }
    }
    
    function toArray(){
        global $wgUser;
        $privateProfile = "";
        $publicProfile = $this->getProfile(false);
        if($wgUser->isLoggedIn()){
            $privateProfile = $this->getProfile(true);
        }
        if($this->isRole(CHAMP)){
            $university = $this->getPartnerName();
            $department = $this->getPartnerDepartment();
            $position = $this->getPartnerTitle();
            
            $university = ($university != "") ? $university : $this->getUni();
            $department = ($department != "") ? $department : $this->getDepartment();
            $position = ($position != "") ? $position : $this->getPosition();
        }
        else{
            $university = $this->getUni();
            $department = $this->getDepartment();
            $position = $this->getPosition();
        }
        $json = array('id' => $this->getId(),
                      'name' => $this->getName(),
                      'realName' => $this->getRealName(),
                      'fullName' => $this->getNameForForms(),
                      'reversedName' => $this->getReversedName(),
                      'email' => $this->getEmail(),
                      'gender' => $this->getGender(),
                      'nationality' => $this->getNationality(),
                      'twitter' => $this->getTwitter(),
                      'website' => $this->getWebsite(),
                      'photo' => $this->getPhoto(),
                      'cachedPhoto' => $this->getPhoto(true),
                      'university' => $university,
                      'department' => $department,
                      'position' => $position,
                      'publicProfile' => $publicProfile,
                      'privateProfile' => $publicProfile,
                      'url' => $this->getURL());
        return $json;
    }
    
    function create(){
        global $wgRequest;
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(STAFF)){
            $wgRequest->setVal('wpCreateaccountMail', ($this->email!=""));
            $wgRequest->setVal('wpCreateaccount', ($this->email==""));
            $wgRequest->setSessionData('wsCreateaccountToken', 'true');
            $wgRequest->setVal('wpCreateaccountToken', 'true');
            $wgRequest->setVal('wpName', $this->name);
            $wgRequest->setVal('wpEmail', $this->email);
            if($this->email != ""){
                $_POST['wpCreateaccount'] = false;
                $_POST['wpCreateaccountMail'] = true;
            }
            else{
                $_POST['wpCreateaccount'] = true;
                $_POST['wpCreateaccountMail'] = false;
            }
            $_POST['wpCreateaccountToken'] = 'true';
            $_POST['wpName'] = $this->name;
            $_POST['wpEmail'] = $this->email;
            $_POST['wpRealName'] = $this->realname;
            $_POST['wpUserType'] = array();
            $_POST['wpNS'] = array();
            $_POST['wpSendMail'] = true;
            $specialUserLogin = new LoginForm($wgRequest, 'signup');
            $specialUserLogin->execute();
            $status = DBFunctions::update('mw_user', 
                                    array('user_twitter' => $this->getTwitter(),
                                          'user_website' => $this->getWebsite(),
                                          'user_gender' => $this->getGender(),
                                          'user_nationality' => $this->getNationality(),
                                          'user_public_profile' => $this->getProfile(false),
                                          'user_private_profile' => $this->getProfile(true)),
                                    array('user_name' => EQ($this->getName())));
            DBFunctions::commit();
            Person::$cache = array();
            Person::$namesCache = array();
            Person::$aliasCache = array();
            Person::$idsCache = array();
            $person = Person::newFromName($_POST['wpName']);
            if($person->exists()){
                return $status;
            }
        }
        return false;
    }
    
    function update(){
        $me = Person::newFromWgUser();
        foreach($this->getSupervisors() as $supervisor){
            if($supervisor->getId() == $me->getId()){
                $isSupervisor = true;
                break;
            }
        }
        if($me->getId() == $this->getId() ||
           $me->isRoleAtLeast(MANAGER) ||
           $isSupervisor){
            $status = DBFunctions::update('mw_user', 
                                    array('user_name' => $this->getName(),
                                          'user_real_name' => $this->getRealName(),
                                          'user_twitter' => $this->getTwitter(),
                                          'user_website' => $this->getWebsite(),
                                          'user_gender' => $this->getGender(),
                                          'user_nationality' => $this->getNationality(),
                                          'user_public_profile' => $this->getProfile(false),
                                          'user_private_profile' => $this->getProfile(true)),
                                    array('user_id' => EQ($this->getId())));
            Person::$cache = array();
            Person::$namesCache = array();
            Person::$aliasCache = array();
            Person::$idsCache = array();
            return $status;
        }
        return false;
    }
    
    function delete(){
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(MANAGER)){
            return DBFunctions::update('mw_user',
                                 array('deleted' => 1),
                                 array('user_id' => EQ($this->getId())));
        }
        return false;
    }
    
    function exists(){
        $person = Person::newFromName($this->getName());
        return ($person != null && $person->getName() != "");
    }
    
    function getCacheId(){
        global $wgSitename;
    }
    
    // Returns the Mediawiki User object for this Person
    function getUser(){
        if($this->user == null){
            $this->user = User::newFromId($this->id);
            $this->user->load();
        }
        return $this->user;
    }
    
    // Returns whether or not this Person is logged in or not
    function isLoggedIn(){
        $user = $this->getUser();
        return $user->isLoggedIn();
    }
    
    // Returns when the user registered
    function getRegistration(){
        return $this->getUser()->getRegistration();
    }
    
    // Returns an array of names similar to this Person's name
    function getSimilarNames(){
        $sql = "SELECT authors
                FROM grand_products";
        $authorRows = DBFunctions::execSQL($sql);
        $possibleNames = array();
        foreach($authorRows as $authors){
            if($authors['authors'] != ""){
                $authors = unserialize($authors['authors']);
                foreach($authors as $author){
                    if($author != ""){
                        $authorN = $author;
                        $authorN = str_replace("  ", " ", $authorN);
                        $exploded = explode(".", str_replace(" ", "", $author));
                        if(count($exploded) > 1 && $exploded[1] != ""){
                            $authorN = str_replace(".", ".*", $authorN);
                        }
                        else{
                            $authorN = str_replace(".", "\.", $authorN);
                        }
                        $authorN = str_replace(" ", ".*", $authorN);
                        if(preg_match("/.*$authorN.*/", $this->name) > 0){
                            $possibleNames[] = $author;
                        }
                    }
                }
            }
        }
        $sql = "SELECT alias
                FROM mw_user_aliases
                WHERE user_id = '{$this->id}'";
        $data = DBFunctions::execSQL($sql);
        foreach($data as $row){
            $possibleNames[] = $row['alias'];
        }
        return $possibleNames;
    }
    
    // Returns whether this Person is a member of the given Project or not
    function isMemberOf($project){
        $projects = $this->getProjects(false, true);
        if(count($projects) > 0 && $project != null){
            foreach($projects as $project1){
                if($project1 != null && $project->getName() == $project1->getName()){
                    return true;
                }
            }
        }
        return false;
    }
    
    // Returns whether this Person is a member of the given Project during the given dates
    function isMemberOfDuring($project, $start, $end){
        $projects = $this->getProjectsDuring($start, $end);
        if(count($projects) > 0 && $project != null){
            foreach($projects as $project1){
                if($project1 != null && $project->getName() == $project1->getName()){
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Returns the amount of time that this Person has been on the specified project
     * @param Project $project The Project that the person has been on
     * @param string $format The format for the time (Defaults to number of days)
     * @param string $now What time to compare the join date to (Defaults to now)
     * @return string The time spent on the specified project
     */
    function getTimeOnProject($project, $format="%d", $now=""){
        if($now == ""){
            $now = time();
        }
        $joined = new DateTime($project->getJoinDate($this));
        $now = new DateTime(date("Y-m-d", $now));
        $interval = $joined->diff($now);
        $diff = $interval->format('%m');
        return $diff;
    }
    
    function isThemeLeader(){
        $themes = array_merge($this->getLeadThemes(), $this->getCoLeadThemes());
        return (count($themes) > 0);
    }
    
    function isThemeLeaderOf($project){
        $themes = array_merge($this->getLeadThemes(), $this->getCoLeadThemes());
        $challenge = $project->getChallenge();
        foreach($themes as $theme){
            if($challenge->getId() == $theme->getId()){
                return true;
            }
        }
        return false;
    }
    
    function isChampionOf($project){
        $champs = $project->getChampions();
        foreach($champs as $champ){
            if($champ['user']->getId() == $this->getId()){
                return true;
            }
        }
        return false;
    }
    
    function isChampionOfOn($project, $date){
        $champs = $project->getChampionsOn($date);
        foreach($champs as $champ){
            if($champ['user']->getId() == $this->getId()){
                return true;
            }
        }
        return false;
    }
    
    function isChampionOfDuring($project, $start, $end){
        $champs = $project->getChampionsDuring($start, $end);
        foreach($champs as $champ){
            if($champ['user']->getId() == $this->getId()){
                return true;
            }
        }
        return false;
    }
    
    // Returns the name of this Person
    function getName(){
        return $this->name;
    }
    
    // Returns the real name of this Person
    function getRealName(){
        return $this->realname;
    }
    
    // Returns the email of this Person
    function getEmail(){
        return $this->email;
    }
    
    // Returns the gender of this Person
    // Will be either "Male" "Female" or ""
    function getGender(){
        return $this->gender;
    }
    
    // Returns the nationality of this Person
    function getNationality(){
        return $this->nationality;
    }
    
    /**
     * Returns the handle of this Person's twitter account
     * @return string The handle of this Person's twitter account
     */
    function getTwitter(){
        return $this->twitter;
    }
    
    /**
     * Returns the url of this Person's website
     * @return string The url of this Person's website
     */
    function getWebsite(){
        return $this->website;
    }
    
    /**
     * Returns the url of this Person's profile page
     * @return string The url of this Person's profile page
     */
    function getUrl(){
        global $wgServer, $wgScriptPath;
        if($this->id > 0){
            return "{$wgServer}{$wgScriptPath}/index.php/{$this->getType()}:{$this->getName()}";
        }
        return "";
    }
    
    // Returns the path to a photo of this Person if it exists
    function getPhoto($cached=false){
        global $wgServer, $wgScriptPath;
        if($this->photo == null || !$cached){
            if(file_exists("Photos/".str_ireplace(".", "_", $this->name).".jpg")){
                $this->photo = "$wgServer$wgScriptPath/Photos/".str_ireplace(".", "_", $this->name).".jpg";
                if(!$cached){
                    return $this->photo."?".microtime(true);
                }
            }
            else {
                $this->photo = "$wgServer$wgScriptPath/skins/face.png";
            }
        }
        return $this->photo;
    }
    
    // Returns the name of this Person with dots and spaces replaced by underscores.
    function getNameForPost(){
        $repl = array('.' => '_', ' ' => '_');
        return strtr($this->name, $repl);
    }
    
    // Returns an array of the name in the form ["first", "last"]
    function splitName(){
        if(!empty($this->realname)){
            $names = explode(" ", $this->realname);
            $lastname = ucfirst($names[count($names)-1]);
            unset($names[count($names)-1]);
            $firstname = implode(" ", $names);
        }
        else{
            $names = explode(".", $this->name, 2);
            $lastname = "";
            if(count($names) > 1){
                $lastname = str_ireplace(".", " ", $names[1]);
            }
            else if(strstr($names[0], " ") != false){
            // Some names do not follow the First.Last convention, so we need to do some extra work
                $names = explode(" ", $this->name, 2);
                if(count($names > 1)){
                    $lastname = $names[1];
                }
            }
            $firstname = $names[0];
        }
        return array("first" => str_replace("&nbsp;", " ", ucfirst($firstname)), "last" => str_replace("&nbsp;", " ", ucfirst($lastname)));
    }
    
    function getFirstName(){
        $splitName = $this->splitName();
        return $splitName['first'];
    }
    
    function getLastName(){
        $splitName = $this->splitName();
        return $splitName['last'];
    }
    
    function getReversedName(){
        $first = $this->getFirstName();
        $last = $this->getLastName();
        if($first != ""){
            return "{$last}, {$first}";
        }
        else{
            return "{$last}";
        }
    }

    // Returns a name usable in forms.
    function getNameForForms($sep = ' ') {
        if (!empty($this->realname))
            return str_replace("&nbsp;", " ", ucfirst($this->realname));
        else
            return str_replace("&nbsp;", " ", str_replace('.', $sep, $this->name));
    }
    
    // Returns the user's profile.
    // If $private is true, then it grabs the private version, otherwise it gets the public
    function getProfile($private=false){
        if($private){
            return $this->privateProfile;
        }
        else{
            return $this->publicProfile;
        }
    }
    
    // Returns the moved on row for when HQPs are inactivated
    // Returns an array of key/value pairs representing the DB row
    function getMovedOn(){
        $sql = "SELECT *
                FROM `grand_movedOn`
                WHERE `user_id` = '{$this->getId()}'";
        $data = DBFunctions::execSQL($sql);
        if(DBFunctions::getNRows() > 0){
            return $data[0];
        }
        else{
            return array("where" => "",
                         "studies" => "",
                         "employer" => "",
                         "city" => "",
                         "country" => "",
                         "effective_date" => "");
        }
    }
    
    function getAllMovedOn(){
        $sql = "SELECT *
                FROM `grand_movedOn`
                WHERE `user_id` = '{$this->getId()}'
                ORDER BY `effective_date` DESC";
        $data = DBFunctions::execSQL($sql);
        if(DBFunctions::getNRows() > 0){
            $newData = array();
            foreach($data as $row){
                $sql = "SELECT *
                        FROM `grand_theses`
                        WHERE `moved_on` = '{$row['id']}'";
                $thesis = DBFunctions::execSQL($sql);
                $row['thesis'] = null;
                $row['reason'] = "movedOn";
                $row['effective_date'] = substr($row['effective_date'], 0, 10);
                if(count($thesis) > 0){
                    $row['thesis'] = Product::newFromId($thesis[0]['publication_id']);
                    $row['reason'] = "graduated";
                }
                $newData[$row['id']] = $row;
            }
            return $newData;
        }
        return array();
    }

    // Returns the moved on row for when HQPs are inactivated
    // Returns an array of key/value pairs representing the DB row
    function getAllMovedOnDuring($startRange, $endRange){
        $sql = "SELECT `user_id`
                FROM `grand_movedOn`
                WHERE date_created BETWEEN '$startRange' AND '$endRange'";
        $data = DBFunctions::execSQL($sql);
        $people = array();
        foreach($data as $row){
            $people[] = Person::newFromId($row['user_id']);
        }
        return $people;
    }
    
    /**
     * Returns the reported thesis for when HQPs are inactivated
     * @param boolean $guess Whether or not to take a guess at what the thesis is
     * @return Product The Product object representing the thesis
     */
    function getThesis($guess = true){
        $data = DBFunctions::select(array('grand_theses'),
                                    array('publication_id'),
                                    array('user_id' => EQ($this->getId())));
        $paper = null;
        if(DBFunctions::getNRows() > 0){
            $paper = Paper::newFromId($data[0]['publication_id']);
            if($paper->getId() == 0){
                $paper = null;
            }
        }
        //Not in theses table, try to find a publication
        if($guess && is_null($paper)){
            $papers = $this->getPapers();
            foreach($papers as $p){
                if($p->getType() == 'Masters Thesis' ||
                   $p->getType() == 'PhD Thesis'){
                     $paper = $p;
                     break; 
                }
            }
        }
        return $paper;
    }

    // Returns the date that degree was started 
    // Currently set to the supervision start date
    function getDegreeStartDate($guess = true){
        $data = DBFunctions::select(array('grand_relations'),
                                    array('start_date'),
                                    array('user2' => EQ($this->getId())),
                                    array('start_date' => 'ASC'));
        if(DBFunctions::getNRows() > 0)
          return $data[0]['start_date'];
        return NULL;
    }

    // Returns the date that degree was received
    // Currently set to the supervision end date
    function getDegreeReceivedDate($guess = true){
        $data = DBFunctions::select(array('grand_relations'),
                                    array('end_date'),
                                    array('user2' => EQ($this->getId()),
                                          'type' => EQ('Supervises')),
                                    array('end_date' => 'ASC'));
        if(DBFunctions::getNRows() > 0)
          return $data[0]['end_date'];
        return NULL;
    }
    
    /**
     * Returns whether this Person has worked on their survey
     * @return boolean whether this Person has worked on their survey
     */
    function hasDoneSurvey(){
        $sql = "SELECT *
                FROM `survey_results`
                WHERE `user_id` = '{$this->id}'";
        $data = DBFunctions::execSQL($sql);
        return (DBFunctions::getNRows() > 0);
    }
    
    /**
     * Returns this Person's primary funding agency from their response in the Survey
     * @return string This Person's primary funding agency from their response in the Survey
     */
    function getPrimaryFundingAgency(){
        $sql = "SELECT `discipline`
                FROM `survey_results`
                WHERE `user_id` = '{$this->id}'";
        $data = DBFunctions::execSQL($sql);
        if(DBFunctions::getNRows() > 0){
            $discipline = json_decode($data[0]['discipline']);
            if(isset($discipline->d_level1a)){
                return $discipline->d_level1a;
            }
        }
        return "Unknown";
    }
    
    /**
     * Returns whether this Person is funded or not for the given year
     * This is only for the CNIs that are in the `grand_funded_cni` table
     * @param integer $year The year to see if the Person is funded or not
     * @return boolean Whether or not this Person is funded
     */
    function isFundedFor($year){
        if($this->isRoleDuring(CNI, $year."-01-01", $year."-12-31")){
            // The Person was a CNI Now check if they were actually funded
            $data = DBFunctions::select(array('grand_funded_cni'),
                                        array('*'),
                                        array('user_id' => EQ($this->getId()),
                                              'year' => EQ($year)));
            if(count($data) > 0){
                // This Person was funded
                return true;
            }
        }
        // This Person was not funded
        return false;
    }
    
    /**
     * Returns this Person's primary discipline from the Survey
     * @return string This Person's primary discipline from the Survey
     */
    function getSurveyDiscipline(){
        $sql = "SELECT `discipline`
                FROM `survey_results`
                WHERE `user_id` = '{$this->id}'";
        $data = DBFunctions::execSQL($sql);
        if(DBFunctions::getNRows() > 0){
            $discipline = json_decode($data[0]['discipline']);
            if(isset($discipline->d_level2)){
                return $discipline->d_level2;
            }
        }
        return "Unknown";
    }
    
    /**
     * Returns this Person's first degree connections from their response in the Survey
     * @return array This Person's first degree connections from their response in the Survey
     */
    function getSurveyFirstDegreeConnections(){
        $sql = "SELECT `grand_connections`
                FROM `survey_results`
                WHERE `user_id` = '{$this->id}'";
        $data = DBFunctions::execSQL($sql);
        if(DBFunctions::getNRows() > 0){
            $connections = json_decode($data[0]['grand_connections']);
            if(count($connections) > 0){
                return $connections;
            }
            else{
                return array();
            }
        }
        return array();
    }
    
    /**
     * Returns the current University that this Person is at
     * @return array The current University this Person is at
     */ 
    function getUniversity(){
        self::generateUniversityCache();
        if($this->university !== false){
            return $this->university;
        }
        $this->university = @self::$universityCache[$this->id];
        return $this->university;
    }

    function getUni(){
        $university = $this->getUniversity();
        return (isset($university['university'])) ? $university['university'] : "Unknown";
    }

    function getDepartment(){
        $university = $this->getUniversity();
        return (isset($university['department'])) ? $university['department'] : "Unknown";
    }

    function getPosition(){
        $university = $this->getUniversity();
        return (isset($university['position'])) ? $university['position'] : "Unkown";
    }    
    
    /**
     * Used by CCVExport to determine the current position of active/inactive HQP
     */
    function getPresentPosition(){
        $pos = array();
        ## See if still studying w/ GRAND
        if($this->isActive()){
          $hqp_pos = $this->getUniversity();
          if ($hqp_pos['position'] !== '') 
            $pos[] = $hqp_pos['position'];
          if ($hqp_pos['department'] !== '') 
            $pos[] = $hqp_pos['department'];
          if ($hqp_pos['university'] !== '') 
            $pos[] = $hqp_pos['university'];
        } else {
          ## Otherwise get new position
          $hqp_pos = $this->getMovedOn();
          if(!empty($hqp_pos)){
            if ($hqp_pos['studies'] !== '') 
              $pos[] = $hqp_pos['studies'];
            if ($hqp_pos['employer'] !== '') 
              $pos[] = $hqp_pos['employer'];
            if ($hqp_pos['city'] !== '') 
              $pos[] = $hqp_pos['city'];
            if ($hqp_pos['country'] !== '') 
              $pos[] = $hqp_pos['country'];
          }   
        }
        return implode(", ", $pos);
    }
    
    /**
     * Returns the last University that this Person was at between the given range
     * @param string $startRange The start date to look at
     * @param string $endRange The end date to look at
     * @return array The last University that this Person was at between the given range
     */ 
    function getUniversityDuring($startRange, $endRange){
        if(!isset($this->universityDuring[$startRange.$endRange])){
            $sql = "SELECT * 
                    FROM grand_user_university uu, grand_universities u, grand_positions p
                    WHERE uu.user_id = '{$this->id}'
                    AND u.university_id = uu.university_id
                    AND uu.position_id = p.position_id
                    AND ( 
                    ( (end_date != '0000-00-00 00:00:00') AND
                    (( start_date BETWEEN '$startRange' AND '$endRange' ) || ( end_date BETWEEN '$startRange' AND '$endRange' ) || (start_date <= '$startRange' AND end_date >= '$endRange') ))
                    OR
                    ( (end_date = '0000-00-00 00:00:00') AND
                    ((start_date <= '$endRange')))
                    )
                    ORDER BY uu.id DESC";
            $data = DBFunctions::execSQL($sql);
            if(count($data) > 0){
                $this->universityDuring[$startRange.$endRange] = array("university" => $data[0]['university_name'],
                                                                       "department" => $data[0]['department'],
                                                                       "position"   => $data[0]['position']);
            }
            else{
                $this->universityDuring[$startRange.$endRange] = null;
            }
        }
        return $this->universityDuring[$startRange.$endRange];
    }
    
    /**
     * Returns all the Universities that this Person has been a part of
     * @return array The last University that this Person was at between the given range
     */ 
    function getUniversities(){
        $sql = "SELECT * 
                FROM grand_user_university uu, grand_universities u, grand_positions p
                WHERE uu.user_id = '{$this->id}'
                AND u.university_id = uu.university_id
                AND uu.position_id = p.position_id
                ORDER BY uu.id DESC";
        $data = DBFunctions::execSQL($sql);
        $array = array();
        if(count($data) > 0){
            foreach($data as $row){
                $array[] = array("university" => $row['university_name'],
                                 "department" => $row['department'],
                                 "position"   => $row['position'],
                                 "start" => $row['start_date'],
                                 "end" => $row['end_date']);
            }
        }
        return $array;
    }
    
    /**
     * Returns the discipline of this Person
     * @return string The name of the discipline that this Person belongs to
     */
    function getDiscipline(){
        self::generateDisciplineMap();
        $dept = strtolower($this->getDepartment());
        if(isset(self::$disciplineMap[$dept])){
            return self::$disciplineMap[$dept];
        }
        return "Other";
    }
    
    /**
     * Returns the discipline of this Person during the given start and end dates
     * @param string $startRange The start date to look at
     * @param string $endRange The end date to look at
     * @param boolean $checkLater Whether or not to check the current Discipline if the range specified does not return any results
     * @return string The name of the discipline that this Person belongs to during the specified dates
     */
    function getDisciplineDuring($startRange, $endRange, $checkLater=false){
        self::generateDisciplineMap();
        $university = $this->getUniversityDuring($startRange, $endRange);
        if($checkLater && $university['department'] == "" || $university['university'] == ""){
            $university = $this->getUniversity();
        }
        $dept = strtolower($university['department']);
        if(isset(self::$disciplineMap[$dept])){
            return self::$disciplineMap[$dept];
        }
        return "Other";
    }
    
    // Returns an array of Strings, representing each user group name
    function getGroups(){
        if($this->groups == null){
            $uTable = getTableName("user");
            $ugTable = getTableName("user_groups");
            $this->groups = array();
            $sql = "SELECT DISTINCT ug.ug_group
                    FROM $uTable u, $ugTable ug
                    WHERE u.user_id = ug.ug_user
                    AND u.user_name = '{$this->name}'
                    ORDER BY ug.ug_group";
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                $this->groups[] = $row['ug_group'];
            }
        }
        return $this->groups;
    }
    
    function getRights(){
        $user = $this->getUser();
        if($user->mRights == null){
            $user->mRights = array();
        }
        GrandAccess::setupGrandAccess($user, $user->mRights);
        return $user->mRights;
    }
    
    // Returns what type of Person this is.  This is determined on the Person's user page, and the namespace it is in.
    // Since a person may belong to multiple roles, this only picks one of those roles.  This method may be useful for making urls for a PersonPage
    function getType(){
        $roles = $this->getRoles();
        if($roles == null || (count($roles) == 1 && $roles[0]->getRole() == INACTIVE)){
            $leadershipRoles = $this->getLeadershipRoles();
            if($roles == null){
                $roles = $leadershipRoles;
            }
            else{
                $roles = array_merge($roles, $leadershipRoles);
            }
        }
        if($roles != null && count($roles) > 0){
            return $roles[count($roles) - 1]->getRole();
        }
        return null;
    }
    
    /**
     * Returns a string containing the full role information
     * @return string The full role information for this Person
     */
    function getRoleString(){
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn() && !$this->isRoleAtLeast(CNI)){
            return "";
        }
        $roles = $this->getRoles();
        $roleNames = array();
        foreach($roles as $role){
            $roleNames[] = $role->getRole();
        }
        $pm = $this->isProjectManager();
        if($this->isProjectLeader() && !$pm){
            $roleNames[] = "PL";
        }
        if($this->isProjectCoLeader() && !$pm){
            $roleNames[] = "COPL";
        }
        if($pm){
            $roleNames[] = "PM";
        }
        foreach($roleNames as $key => $role){
            if($role == "Inactive"){
                if($this->isProjectManager() || $this->isProjectLeader() || $this->isProjectCoLeader()){
                    unset($roleNames[$key]);
                    continue;
                }
                $lastRole = $this->getLastRole();
                if($lastRole != null){
                    $roleNames[$key] = "Inactive-".$lastRole->getRole();
                }
            }
        }
        return implode(", ", $roleNames);
    }
    
    // Returns an array of roles that the user is a part of
    // If history is set to true, then all the roles regardless of date are included
    function getRoles($history=false){
        if($history !== false && $this->id != null){
            $this->roles = array();
            if($history === true){
                $data = DBFunctions::select(array('grand_roles'),
                                            array('*'),
                                            array('user_id' => $this->id),
                                            array('end_date' => 'DESC'));
            }
            else{
                $sql = "SELECT *
                        FROM grand_roles
                        WHERE user_id = '{$this->id}'
                        AND start_date <= '{$history}'
                        AND (end_date >= '{$history}' OR end_date = '0000-00-00 00:00:00')";
                $data = DBFunctions::execSQL($sql);
            }
            $roles = array();
            if(count($data) > 0){
                foreach($data as $row){
                    $roles[] = new Role(array($row));
                }
            }
            return $roles;
        }
        self::generateRolesCache();
        if($this->roles == null && $this->id != null){
            if(isset(self::$rolesCache[$this->id])){
                foreach(self::$rolesCache[$this->id] as $row){
                    $this->roles[] = new Role(array(0 => $row));
                }
            }
            else{
                $this->roles[] = new Role(array(0 => array('id' => -1,
                                                           'user_id' => $this->id,
                                                           'role' => INACTIVE,
                                                           'start_date' => '0000-00-00 00:00:00',
                                                           'end_date' => '0000-00-00 00:00:00',
                                                           'comment' => '')));
            }
        }
        return $this->roles;
    }
    
    function getLeadershipRoles(){
        $roles = array();
        $pm = $this->isProjectManager();
        if($this->isProjectLeader() && !$pm){
            $roles[] = new Role(array(0 => array('id' => -1,
                                                       'user_id' => $this->id,
                                                       'role' => "PL",
                                                       'start_date' => '0000-00-00 00:00:00',
                                                       'end_date' => '0000-00-00 00:00:00',
                                                       'comment' => '')));
        }
        if($this->isProjectCoLeader() && !$pm){
            $roles[] = new Role(array(0 => array('id' => -1,
                                                       'user_id' => $this->id,
                                                       'role' => "COPL",
                                                       'start_date' => '0000-00-00 00:00:00',
                                                       'end_date' => '0000-00-00 00:00:00',
                                                       'comment' => '')));
        }
        if($pm){
            $roles[] = new Role(array(0 => array('id' => -1,
                                                       'user_id' => $this->id,
                                                       'role' => "PM",
                                                       'start_date' => '0000-00-00 00:00:00',
                                                       'end_date' => '0000-00-00 00:00:00',
                                                       'comment' => '')));
        }
        return $roles;
    }
    
    function getLeadershipRolesDuring($startDate=false, $endDate=false){
        $roles = array();
        $pm = $this->isProjectManagerDuring($startDate, $endDate);
        if($this->isProjectLeaderDuring($startDate, $endDate) && !$pm){
            $roles[] = new Role(array(0 => array('id' => -1,
                                                       'user_id' => $this->id,
                                                       'role' => "PL",
                                                       'start_date' => '0000-00-00 00:00:00',
                                                       'end_date' => '0000-00-00 00:00:00',
                                                       'comment' => '')));
        }
        if($this->isProjectCoLeaderDuring($startDate, $endDate) && !$pm){
            $roles[] = new Role(array(0 => array('id' => -1,
                                                       'user_id' => $this->id,
                                                       'role' => "COPL",
                                                       'start_date' => '0000-00-00 00:00:00',
                                                       'end_date' => '0000-00-00 00:00:00',
                                                       'comment' => '')));
        }
        if($pm){
            $roles[] = new Role(array(0 => array('id' => -1,
                                                       'user_id' => $this->id,
                                                       'role' => "PM",
                                                       'start_date' => '0000-00-00 00:00:00',
                                                       'end_date' => '0000-00-00 00:00:00',
                                                       'comment' => '')));
        }
        return $roles;
    }
    
    // Returns the first role that this Person had, null if this Person has never had any Roles
    function getFirstRole(){
        $roles = $this->getRoles(true);
        if(count($roles) > 0){
            return $roles[0];
        }
        return null;
    }
    
    // Returns the last role that this Person had before they were Inactivated, null if this Person has never had any Roles
    function getLastRole(){
        $roles = $this->getRoles(true);
        if(count($roles) > 0){
            return $roles[count($roles)-1];
        }
        return null;
    }
    
    // Checks whether the Person's last role was $role
    function wasLastRole($role){
        $lastRole = $this->getLastRole();
        if($lastRole != null && $lastRole->getRole() == $role){
            return true;
        }
        return false;
    }
    
    // Checks whether the Person's last role was at least $role
    function wasLastRoleAtLeast($role){
        global $wgRoleValues;
        if($this->getRoles() != null){
            $r = $this->getLastRole();
            if($r != null && $wgRoleValues[$r->getRole()] >= $wgRoleValues[$role]){
                return true;
            }
        }
        return false;
    }
    
    // Checks whether the Person's last role was at most $role
    function wasLastRoleAtMost($role){
        global $wgRoleValues;
        if($this->getRoles() != null){
            $r = $this->getLastRole();
            if($r != null && $wgRoleValues[$r->getRole()] <= $wgRoleValues[$role]){
                return true;
            }
        }
        return false;
    }
    
    // Returns an array of roles that the user is a part of
    // During a given range.
    function getRolesDuring($startRange, $endRange){
        if($this->id == 0){
            return array();
        }
        $cacheId = "personRolesDuring".$this->id."_".$startRange.$endRange;
        if(Cache::exists($cacheId)){
            $data = Cache::fetch($cacheId);
        }
        else{
            $sql = "SELECT *
                    FROM grand_roles
                    WHERE user_id = '{$this->id}'
                    AND ( 
                    ( (end_date != '0000-00-00 00:00:00') AND
                    (( start_date BETWEEN '$startRange' AND '$endRange' ) || ( end_date BETWEEN '$startRange' AND '$endRange' ) || (start_date <= '$startRange' AND end_date >= '$endRange') ))
                    OR
                    ( (end_date = '0000-00-00 00:00:00') AND
                    ((start_date <= '$endRange')))
                    )";
            $data = DBFunctions::execSQL($sql);
            Cache::store($cacheId, $data);
        }
        $roles = array();
        foreach($data as $row){
            $roles[] = new Role(array(0 => $row));
        }
        return $roles; 
    }
    
    function getRolesOn($date){
        if($this->id == 0){
            return array();
        }
        
        $sql = "SELECT *
                FROM grand_roles
                WHERE user_id = '{$this->id}'
                AND (('$date' BETWEEN start_date AND end_date) OR (start_date <= '$date' AND end_date = '0000-00-00 00:00:00'))";
        $data = DBFunctions::execSQL($sql);
        $roles = array();
        foreach($data as $row){
            $roles[] = new Role(array(0 => $row));
        }
        return $roles;        
    }
    
    function getProjectHistory($groupBySubs=false){
        $projects = array();
        $tmpProjects = array();
        $data = DBFunctions::select(array('grand_project_members'),
                                    array('*'),
                                    array('user_id' => EQ($this->getId())));
        foreach($data as $row){
            $start = $row['start_date'];
            $end = $row['end_date'];
            if($end == "0000-00-00 00:00:00"){
                $end = "9999";
            }
            $tmpProjects[$end.$start.$row['id']] = $row;
        }
        ksort($tmpProjects);
        $projects = array_reverse($tmpProjects);
        if($groupBySubs){
            $tmpProjects = array();
            foreach($projects as $proj){
                $project = Project::newFromId($proj['project_id']);
                if($project != null && !$project->isSubProject()){
                    $tmpProjects[] = $proj;
                    foreach($projects as $id => $proj2){
                        $sub = Project::newFromId($proj2['project_id']);
                        if($sub != null && $sub->isSubProject() && $sub->getParent()->getId() == $project->getId()){
                            $tmpProjects[] = $proj2;
                            unset($projects[$id]);
                        }  
                    }
                }
            }
            $projects = $tmpProjects;
        }
        return $projects;
    }
    
    // Returns an array of Projects that this Person is a part of
    // If history is set to true, then all the Projects regardless of date are included
    function getProjects($history=false, $allowProposed=false){
        $projects = array();
        if(($this->projects == null || $history) && $this->id != null){
            $sql = "SELECT p.name
                    FROM grand_project_members u, grand_project p
                    WHERE user_id = '{$this->id}'
                    AND p.id = u.project_id \n";
            if($history === false){
                $sql .= "AND (end_date = '0000-00-00 00:00:00'
                         OR end_date > CURRENT_TIMESTAMP)\n";
            }
            else if($history !== true){
                $sql .= "AND start_date <= '{$history}'
                         AND (end_date >= '{$history}' OR (end_date = '0000-00-00 00:00:00'))\n";
            }
            $sql .= "ORDER BY p.name";
            $data = DBFunctions::execSQL($sql);
            $projectNames = array();
            foreach($data as $row){
                $project = Project::newFromHistoricName($row['name']);
                if($project != null && $project->getName() != ""){
                    if(!isset($projectNames[$project->getName()])){
                        // Make sure that the project is not being added twice
                        if((!$project->isDeleted() || ($project->isDeleted() && $history)) && ($allowProposed || $project->getStatus() != "Proposed")){
                            // Make sure the project is not deleted or proposed, and then add it
                            $projectNames[$project->getName()] = true;
                            $projects[] = $project;
                        }
                    }
                }
            }
        }
        if($history === false && $this->projects == null){
            $this->projects = $projects;
        }
        if($history === false && $this->projects != null){
            return $this->projects;
        }
        return $projects;
    }
    
    // Returns an array of Projects that this Person is a part of
    // TODO: This might be slow.
    function getProjectsDuring($start, $end, $allowProposed=false){
        if(isset($this->projectCache[$start.$end])){
            return $this->projectCache[$start.$end];
        }
        $projectsDuring = array();
        $projects = $this->getProjects(true);
        if(count($projects) > 0){
            foreach($projects as $project){
                $project = Project::newFromHistoricName($project->getName());
                if(((!$project->isDeleted()) || 
                    ($project->isDeleted() && !($project->effectiveDate < $start))) &&
                   ($allowProposed || $project->getStatus() != "Proposed")){
                    $members = $project->getAllPeopleDuring(null, $start, $end, true);
                    foreach($members as $member){
                        if($member->getId() == $this->id){
                            $projectsDuring[] = $project;
                            break;
                        }
                    }
                }
            }
        }
        $this->projectCache[$start.$end] = $projectsDuring;
        return $projectsDuring;
    }
    
    static function getAllPartnerNames(){
        $data = DBFunctions::select(array('grand_champion_partners'),
                                    array('*'));
        $names = array();
        foreach($data as $row){
            $names[$row['partner']] = $row['partner'];
        }
        return $names;
    }
    
    static function getAllPartnerTitles(){
        $data = DBFunctions::select(array('grand_champion_partners'),
                                    array('*'));
        $titles = array();
        foreach($data as $row){
            $titles[$row['title']] = $row['title'];
        }
        return $titles;
    }
    
    static function getAllPartnerDepartments(){
        $data = DBFunctions::select(array('grand_champion_partners'),
                                    array('*'));
        $depts = array();
        foreach($data as $row){
            $depts[$row['department']] = $row['department'];
        }
        return $depts;
    }
    
    // Returns the name of the partner of this user
    function getPartnerName(){
        $data = DBFunctions::select(array('grand_champion_partners'),
                                    array('*'),
                                    array('user_id' => EQ($this->id)));
        if(count($data) > 0){
            return $data[0]['partner'];
        }
        return "";
    }
    
    function getPartnerTitle(){
        $data = DBFunctions::select(array('grand_champion_partners'),
                                    array('*'),
                                    array('user_id' => EQ($this->id)));
        if(count($data) > 0){
            return $data[0]['title'];
        }
        return "";      
    }
    
    function getPartnerDepartment(){
        $data = DBFunctions::select(array('grand_champion_partners'),
                                    array('*'),
                                    array('user_id' => EQ($this->id)));
        if(count($data) > 0){
            return $data[0]['department'];
        }
        return "";      
    }
    
    // Returns the number of months an HQP has been a part of a project for(Based on data from 2010)
    function getHQPMonth($project){
        $sql = "SELECT months 
                FROM grand_hqp_months
                WHERE user_id = '{$this->id}'
                AND project_id = '{$project->getId()}'";
        $data = DBFunctions::execSQL($sql);
        if(isset($data[0]) && isset($data[0]['months'])){
            return $data[0]['months'];
        }
        else{
            return "Unknown";
        }
    }
    
    function getRelationsDuring($type='all', $startRange, $endRange){
        $type = mysql_real_escape_string($type);
        $startRange = mysql_real_escape_string($startRange);
        $endRange = mysql_real_escape_string($endRange);
        $sql = "SELECT *
                FROM grand_relations
                WHERE user1 = '{$this->id}'\n";
        if($type == "public"){
            $sql .= "AND type != '".WORKS_WITH."'\n"; 
        }
        else if($type == "all"){
            // do nothing
        }
        else{
            $sql .= "AND type = '$type'\n";
        }
        $sql .= "AND ( 
                ( (end_date != '0000-00-00 00:00:00') AND
                (( start_date BETWEEN '$startRange' AND '$endRange' ) || ( end_date BETWEEN '$startRange' AND '$endRange' ) || (start_date <= '$startRange' AND end_date >= '$endRange') ))
                OR
                ( (end_date = '0000-00-00 00:00:00') AND
                ((start_date <= '$endRange')))
                )";
        $data = DBFunctions::execSQL($sql);
        $relations = array();
        foreach($data as $row){
            $relations[] = Relationship::newFromId($row['id']);
        }
        return $relations;
    }
    
    // Returns an array of relations for this Person of the given type
    // If history is set to true, then all the relations regardless of date are included
    function getRelations($type='all', $history=false){
        if($type == "all"){
            $sql = "SELECT id, type
                    FROM grand_relations, mw_user u1, mw_user u2
                    WHERE user1 = '{$this->id}'
                    AND u1.user_id = user1
                    AND u2.user_id = user2
                    AND u1.deleted != '1'
                    AND u2.deleted != '1'";
            if(!$history){
                $sql .= "AND start_date > end_date";
            }
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                $this->relations[$row['type']][$row['id']] = Relationship::newFromId($row['id']);
            }
            return $this->relations;
        }
        else if($type == "public"){
            $sql = "SELECT id, type
                    FROM grand_relations, mw_user u1, mw_user u2
                    WHERE user1 = '{$this->id}'
                    AND u1.user_id = user1
                    AND u2.user_id = user2
                    AND u1.deleted != '1'
                    AND u2.deleted != '1'
                    AND type <> '".WORKS_WITH."'";
            if(!$history){
                $sql .= "AND start_date > end_date";
            }
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                $this->relations[$row['type']][$row['id']] = Relationship::newFromId($row['id']);
            }
            return $this->relations;
        }
        //if(!isset($this->relations[$type])){
            $this->relations[$type] = array();
            $sql = "SELECT id, type
                    FROM grand_relations, mw_user u1, mw_user u2
                    WHERE user1 = '{$this->id}'
                    AND u1.user_id = user1
                    AND u2.user_id = user2
                    AND u1.deleted != '1'
                    AND u2.deleted != '1'
                    AND type = '{$type}'";
            if(!$history){
                $sql .= "AND start_date > end_date";
            }
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                $this->relations[$row['type']][$row['id']] = Relationship::newFromId($row['id']);
            }
        //}
        return $this->relations[$type];
    }
    
    // Returns an array of relations for this Person of the given type
    // If history is set to true, then all the relations regardless of date are included
    function getStudents($type='all', $history=false){
        $supervision = array();

        $sql = "SELECT r.id, r.type, r.user2
                FROM grand_relations r, mw_user u1, mw_user u2
                WHERE r.user1 = '{$this->id}'
                AND u1.user_id = r.user1
                AND u2.user_id = r.user2
                AND u1.deleted != '1'
                AND u2.deleted != '1'
                AND r.type = 'Supervises'";
        if(!$history){
            $sql .= "AND start_date > end_date";
        }
        $data = DBFunctions::execSQL($sql);

        $students = array();
        foreach($data as $row){
            // if($type == "all"){
            // }
            // else if($type == "Masters"){
            // }
            // else if($type == "PhD"){
            // }

            $students[] = Person::newFromId($row['user2']);
        }
        
        return $students;
     
    }

    // Returns the contributions this person has made
    function getContributions(){
        if($this->contributions == null){
            $this->contributions = array();
            $sql = "SELECT id
                    FROM(SELECT id, name, rev_id
                    FROM grand_contributions
                    WHERE users LIKE '%\"{$this->id}\"%'
                    GROUP BY id, name, rev_id
                    ORDER BY id ASC, rev_id DESC) a
                    GROUP BY id";
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                $contribution = Contribution::newFromId($row['id']);
                if($this->isReceiverOf($contribution)){
                    $this->contributions[] = $contribution;
                }
            }
        }
        return $this->contributions;
    }
    
    // Returns the contributions this person has made during the given year
    function getContributionsDuring($year){
        $contribs = array();
        foreach($this->getContributions() as $contrib){
            if($contrib->getYear() == $year){
                $contribs[] = $contrib;
            }
        }
        return $contribs;
    }
    
    // Returns an array of Multimedia involved by this Person
    function getMultimedia(){
        if($this->multimedia == null){
            $this->multimedia = array();
            $sql = "SELECT m.id
                    FROM `grand_materials` m, `grand_materials_people` p
                    WHERE p.user_id = '{$this->id}'
                    AND p.material_id = m.id";
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                $this->multimedia[] = Material::newFromId($row['id']);
            }
        }
        return $this->multimedia;
    }
    
    /**
     * Returns an array of objects representing this user's recordings
     * @return array An array of objects representing this user's recordings
     */
    function getRecordings(){
        $data = DBFunctions::select(array('grand_recordings'),
                                    array('*'),
                                    array('user_id' => EQ($this->id)));
        $array = array();
        foreach($data as $row){
            $events = json_decode($row['story']);
            $story = (object)'a';
            $story->id = $row['id'];
            $story->person = $row['user_id'];
            $story->created = $row['created'];
            $story->events = $events;
            if(count($events) > 0){
                foreach($events as $event){
                    $date = @$event->date;
                    $time = strtotime($date);
                    $event->date = date('D, F n, Y e - h:i:s', $time);
                }
            }
            $array[] = $story;
        }
        return $array;
    }
    
    // Returns an array of Acknowledgements uploaded by this Person
    function getAcknowledgements(){
        if($this->acknowledgements == null){
            $this->acknowledgements = array();
            $sql = "SELECT `id`
                    FROM `grand_acknowledgements`
                    WHERE user_id = '{$this->id}'";
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                $this->acknowledgements[] = Acknowledgement::newFromId($row['id']);
            }
        }
        return $this->acknowledgements;
    }
    
    function isActive(){
        $roles = $this->getRoles();
        if(count($roles) > 0){
            $role = $roles[0]->getRole();
            return $role != INACTIVE;
        }
        else{
            return false;
        }
    }
    
    /// Returns whether this Person is a PNI or not.
    function isPNI() {
        return $this->isRole(PNI);
    }
    
    /// Returns whether this Person is a CNI or not.
    function isCNI() {
        return $this->isRole(CNI);
    }
    
    /// Returns whether this Person is an HQP or not.
    function isHQP() {
        return $this->isRole(HQP);
    }
    
    /**
     * Returns whether or not this person is a Student
     * @return boolean Returns whether or not his person is a Student
     */
    function isStudent(){
        if($this->isRole(HQP)){
            $uni = $this->getUniversity();
            if(strtolower($uni['position']) == 'undergraduate' ||
               strtolower($uni['position']) == 'masters student' ||
               strtolower($uni['position']) == 'phd student' ||
               strtolower($uni['position']) == 'postdoc'){
                return true;
            }
        }
        return false;
    }
    
    // Returns whether this Person is the same as $wgUser
    function isMe(){
        global $wgUser;
        return ($wgUser->getId() == $this->getId());
    }

    // Returns whether this Person is of type $role or not.
    function isRole($role){
        $roles = array();
        $role_objs = $this->getRoles();
        if(count($role_objs) > 0){
            foreach($role_objs as $r){
                $roles[] = $r->getRole();
            }
        }
        else{
            return false;
        }
        if(($role == PL || $role == 'PL') && $this->isProjectLeader() && !$this->isProjectManager()){
            $roles[] = PL;
            $roles[] = 'PL';
        }
        if(($role == COPL || $role == 'COPL') && $this->isProjectCoLeader() && !$this->isProjectManager()){
            $roles[] = COPL;
            $roles[] = 'COPL';
        }
        if(($role == PM || $role == 'PM') && $this->isProjectManager()){
            $roles[] = PM;
            $roles[] = 'PM';
        }
        if($role == EVALUATOR && $this->isEvaluator()){
            $roles[] = EVALUATOR;
        }
        return (array_search($role, $roles) !== false);
    }
    
    function isRoleOn($role, $date){
        $roles = array();
        $role_objs = $this->getRolesOn($date);
        if($role == PL || $role == COPL || $role == "PL" || $role == "COPL"){
            $project_objs = $this->leadershipOn($date);
            if(count($project_objs) > 0){
                $roles[] = "PL";
                $roles[] = "COPL";
            }
        }
        if(count($role_objs) > 0){
            foreach($role_objs as $r){
                $roles[] = $r->getRole();
            }
        }
        if($role == EVALUATOR && $this->isEvaluator()){
            $roles[] = EVALUATOR;
        }
        if(count($roles) == 0){
            return false;
        }
        return (array_search($role, $roles) !== false);
    }
    
    // Returns whether this Person is of type $role or not during a specific period
    function isRoleDuring($role, $startRange, $endRange){
        $roles = array();
        $role_objs = $this->getRolesDuring($startRange, $endRange);
        if($role == PL || $role == COPL || $role == "PL" || $role == "COPL"){
            $project_objs = $this->leadershipDuring($startRange, $endRange);
            if(count($project_objs) > 0){
                $roles[] = "PL";
                $roles[] = "COPL";
            }
        }
        if(count($role_objs) > 0){
            foreach($role_objs as $r){
                $roles[] = $r->getRole();
            }
        }
        if($role == EVALUATOR && $this->isEvaluator()){
            $roles[] = EVALUATOR;
        }
        if(count($roles) == 0){
            return false;
        }
        return (array_search($role, $roles) !== false);
    }
    
    function isRoleAtLeastDuring($role, $startRange, $endRange){
        global $wgRoleValues;
        $roles = $this->getRolesDuring($startRange, $endRange);
        if($roles != null){
            foreach($roles as $r){
                if($r->getRole() != "" && $wgRoleValues[$r->getRole()] >= $wgRoleValues[$role]){
                    return true;
                }
            }
        }
        if($wgRoleValues[PL] >= $wgRoleValues[$role]){
            if($this->isProjectLeaderDuring($startRange, $endRange)){
                return true;
            }
        }
        if($wgRoleValues[COPL] >= $wgRoleValues[$role]){
            if($this->isProjectCoLeaderDuring($startRange, $endRange)){
                return true;
            }
        }
        return false;
    }
    
    // Returns whether or not the Person has a role of at least the given role
    function isRoleAtLeast($role){
        global $wgRoleValues;
        if($this->getRoles() != null){
            foreach($this->getRoles() as $r){
                if($r->getRole() != "" && $wgRoleValues[$r->getRole()] >= $wgRoleValues[$role]){
                    return true;
                }
            }
        }
        if($wgRoleValues[PL] >= $wgRoleValues[$role]){
            if($this->isProjectLeader()){
                return true;
            }
        }
        if($wgRoleValues[COPL] >= $wgRoleValues[$role]){
            if($this->isProjectCoLeader()){
                return true;
            }
        }
        return false;
    }
    
    // Returns whether or not the Person has a role of at most the given role
    function isRoleAtMost($role){
        global $wgRoleValues;
        foreach($this->getRoles() as $r){
            if($r->getRole() != "" && $wgRoleValues[$r->getRole()] <= $wgRoleValues[$role]){
                return true;
            }
        }
        if($wgRoleValues[PL] <= $wgRoleValues[$role]){
            if($this->isProjectLeader()){
                return true;
            }
        }
        if($wgRoleValues[COPL] <= $wgRoleValues[$role]){
            if($this->isProjectCoLeader()){
                return true;
            }
        }
        
        return false;
    }
    
    // Returns an array of Person(s) who requested this User, or an empty array if there was no such Person
    function getCreators(){
        $data = DBFunctions::select(array('grand_user_request'),
                                    array('DISTINCT requesting_user'),
                                    array('wpName' => EQ($this->name)));
        $creators = array();
        foreach($data as $row){
            if($row['requesting_user'] != 0){
                $creators[] = Person::newFromId($row['requesting_user']);
            }
        }
        return $creators;
    }
    
    function getRequestedMembers(){
        $data = DBFunctions::select(array('grand_user_request'),
                                    array('DISTINCT wpName'),
                                    array('requesting_user' => $this->id,
                                          'created' => EQ(1)));
        $members = array();
        foreach($data as $row){
            $members[] = Person::newFromName($row['wpName']);
        }
        return $members;
    }

    function getHQP($history=false){
        if($history !== false && $this->id != null){
            $this->roles = array();
            if($history === true){
                if($this->historyHqps != null){
                    return $this->historyHqps;
                }
                $sql = "SELECT *
                        FROM grand_relations
                        WHERE user1 = '{$this->id}'
                        AND type = 'Supervises'";
            }
            else{
                $sql = "SELECT *
                        FROM grand_relations
                        WHERE user1 = '{$this->id}'
                        AND type = 'Supervises'
                        AND start_date <= '{$history}'
                        AND (end_date >= '{$history}' OR end_date = '0000-00-00 00:00:00')";
            }
            $data = DBFunctions::execSQL($sql);
            $hqps = array();
            foreach($data as $row){
                $hqps[] = Person::newFromId($row['user2']);
            }
            if($history === true){
                $this->historyHqps = $hqps;
            }
            return $hqps;
        }
        if($this->hqps != null){
            return $this->hqps;
        }
        $sql = "SELECT *
                FROM grand_relations
                WHERE user1 = '{$this->id}'
                AND type = 'Supervises'
                AND start_date > end_date";
        $data = DBFunctions::execSQL($sql);
        $hqps = array();
        foreach($data as $row){
            $hqp = Person::newFromId($row['user2']);
            if($hqp->isRoleDuring(HQP, '0000-00-00 00:00:00', '2100-00-00 00:00:00')){
                $hqps[] = $hqp;
            }
        }
        $this->hqps = $hqps;
        return $this->hqps;
    }
    
    function getChampionsDuring($startRange, $endRange){
        $champions = array();
        $relations = $this->getRelations(WORKS_WITH, true);
        foreach($relations as $relation){
            $start = $relation->getStartDate();
            $end = $relation->getEndDate();
            if((strcmp($end, $startRange) >= 0 && strcmp($end, $endRange) <= 0 && strcmp($end, "0000-00-00 00:00:00") != 0) ||
                (strcmp($start, $startRange) >= 0 && (strcmp($end, $endRange) >= 0 || strcmp($end, "0000-00-00 00:00:00") == 0))){
                $user1 = $relation->getUser1();
                $user2 = $relation->getUser2();
                if($user1->getId() != $this->id && $user1->isRoleDuring(CHAMP, $startRange, $endRange)){
                    $champions[] = $user1;
                }
                else if($user2->getId() != $this->id && $user2->isRoleDuring(CHAMP, $startRange, $endRange)){
                    $champions[] = $user2;
                }
            }
        }
        return $champions;
    }
    
    function getHQPDuring($startRange, $endRange){
        if(isset($this->hqpCache[$startRange.$endRange])){
            return $this->hqpCache[$startRange.$endRange];
        }
        $sql = "SELECT *
                FROM grand_relations
                WHERE user1 = '{$this->id}'
                AND type = 'Supervises'
                AND ( 
                ( (end_date != '0000-00-00 00:00:00') AND
                (( start_date BETWEEN '$startRange' AND '$endRange' ) || ( end_date BETWEEN '$startRange' AND '$endRange' ) || (start_date <= '$startRange' AND end_date >= '$endRange') ))
                OR
                ( (end_date = '0000-00-00 00:00:00') AND
                ((start_date <= '$endRange')))
                )";
    
        $data = DBFunctions::execSQL($sql);
        $hqps = array();
        $hqps_uniq_ids = array();
        foreach($data as $row){
            $hqp = Person::newFromId($row['user2']);
            if( !in_array($hqp->getId(), $hqps_uniq_ids) && $hqp->getId() != null){
                $hqps_uniq_ids[] = $hqp->getId();
                if(!$hqp->isRoleDuring(HQP, $startRange, $endRange)){
                    continue;
                }
                $hqps[] = $hqp;
            }
        }
        $this->hqpCache[$startRange.$endRange] = $hqps;
        return $hqps;
    }
    
    function getSupervisors($history=false){
        if($history !== false && $this->id != null){
            $this->roles = array();
            if($history === true){
                $sql = "SELECT *
                        FROM grand_relations
                        WHERE user2 = '{$this->id}'
                        AND type = 'Supervises'";
            }
            else{
                $sql = "SELECT *
                        FROM grand_relations
                        WHERE user2 = '{$this->id}'
                        AND type = 'Supervises'
                        AND start_date <= '{$history}'
                        AND (end_date >= '{$history}' OR end_date = '0000-00-00 00:00:00')";
            }
            $data = DBFunctions::execSQL($sql);
            $people = array();
            foreach($data as $row){
                $person = Person::newFromId($row['user1']);
                $people[$person->getId()] = $person;
            }
            return array_values($people);
        }
        $sql = "SELECT *
                FROM grand_relations
                WHERE user2 = '{$this->id}'
                AND type = 'Supervises'
                AND start_date > end_date";
        $data = DBFunctions::execSQL($sql);
        $people = array();
        foreach($data as $row){
            $person = Person::newFromId($row['user1']);
            $people[$person->getId()] = $person;
        }
        return array_values($people);
    }
    
    function getSupervisorsDuring($startRange, $endRange){
        $sql = "SELECT *
                FROM grand_relations
                WHERE user2 = '{$this->id}'
                AND type = 'Supervises'
                AND ( 
                ( (end_date != '0000-00-00 00:00:00') AND
                (( start_date BETWEEN '$startRange' AND '$endRange' ) || ( end_date BETWEEN '$startRange' AND '$endRange' ) || (start_date <= '$startRange' AND end_date >= '$endRange') ))
                OR
                ( (end_date = '0000-00-00 00:00:00') AND
                ((start_date <= '$endRange')))
                )";
    
        $data = DBFunctions::execSQL($sql);
        $sups = array();
        $sups_uniq_ids = array();
        foreach($data as $row){
            $sup = Person::newFromId($row['user1']);
            if( !in_array($sup->getId(), $sups_uniq_ids) && $sup->getName() != ""){
                $sups_uniq_ids[] = $sup->getId();
                $sups[] = $sup;
            }
        }
        return $sups;
    }

    function getSupervisedOnProjects($history=false){
        if($history !== false && $this->id != null){
            $this->roles = array();
            if($history === true){
                $sql = "SELECT *
                        FROM grand_relations
                        WHERE user2 = '{$this->id}'
                        AND type = 'Supervises'";
            }
            else{
                $sql = "SELECT *
                        FROM grand_relations
                        WHERE user2 = '{$this->id}'
                        AND type = 'Supervises'
                        AND start_date <= '{$history}'
                        AND (end_date >= '{$history}' OR end_date = '0000-00-00 00:00:00')";
            }
            $data = DBFunctions::execSQL($sql);
            $projects = array();
            $project_ids = array();
            foreach($data as $row){
                if(!empty($row['projects'])){
                    $p_ids = unserialize($row['projects']);
                    foreach($p_ids as $p_id){
                        if(!in_array($p_id, $project_ids)){
                            $projects[] = Project::newFromId($p_id);
                            $project_ids[] = $p_id;
                        }
                    }
                }
            }
            return $projects;
        }
        $sql = "SELECT *
                FROM grand_relations
                WHERE user2 = '{$this->id}'
                AND type = 'Supervises'
                AND start_date > end_date";
        $data = DBFunctions::execSQL($sql);
        $projects = array();
        $project_ids = array();
        foreach($data as $row){
            if(!empty($row['projects'])){
                $p_ids = unserialize($row['projects']);
                foreach($p_ids as $p_id){
                    if(!in_array($p_id, $project_ids)){
                        $projects[] = Project::newFromId($p_id);
                        $project_ids[] = $p_id;
                    }
                }
            }
        }
        return $projects;
    }

    function isSupervisor($history=false){
        if($history !== false && $this->id != null){
            $this->roles = array();
            if($history === true){
                $sql = "SELECT *
                        FROM grand_relations
                        WHERE user1 = '{$this->id}'
                        AND type = 'Supervises'";
            }
            else{
                $sql = "SELECT *
                        FROM grand_relations
                        WHERE user1 = '{$this->id}'
                        AND type = 'Supervises'
                        AND start_date <= '{$history}'
                        AND (end_date >= '{$history}' OR end_date = '0000-00-00 00:00:00')";
            }
            $data = DBFunctions::execSQL($sql);
            return count($data);
        }
        $sql = "SELECT *
                FROM grand_relations
                WHERE user1 = '{$this->id}'
                AND type = 'Supervises'
                AND start_date > end_date";
        $data = DBFunctions::execSQL($sql);
        return count($data);
    }
    
    // Returns True, if this Person is related to another given Person, through a given relationship
    // Returns False, if no such relationship found
    // Returns Null, if there is a problem with the given Person
    //TODO: Perhaps will need to implement history argument
    function relatedTo($person, $relationship){
        if( $person instanceof Person ){
            $person_id = $person->getId();
            $sql = "SELECT *
                    FROM grand_relations
                    WHERE user1 = '{$this->id}'
                    AND user2 = '{$person_id}'
                    AND type = '$relationship'
                    AND start_date > end_date";
                
            $data = DBFunctions::execSQL($sql);
            if(count($data) > 0){
                return true;
            }
            else{
                return false;
            }     
        }
        else{
            return null;
        }
    }
    
    /**
     * Returns an array of Paper(s) authored or co-authored by this Person _or_ their HQP
     * @param string $category The category of Paper to get
     * @param boolean $history Whether or not to include past publications (ie. written by past HQP)
     * @param string $grand Whether to include 'grand' 'nonGrand' or 'both' Papers
     * @param boolean $onlyPublic Whether to include 'grand' 'nonGrand' or 'both' Papers
     * @return array Returns an array of Paper(s) authored or co-authored by this Person _or_ their HQP
     */ 
    function getPapers($category="all", $history=false, $grand='grand', $onlyPublic=true){
        self::generateAuthorshipCache();
        $processed = array();
        $papersArray = array();
        $papers = array();
        foreach($this->getHQP($history) as $hqp){
            $ps = $hqp->getPapers($category, $history, $grand, $onlyPublic);
            foreach($ps as $p){
                if(!isset($processed[$p->getId()])){
                    $processed[$p->getId()] = true;
                    $papersArray[] = $p;
                }
            }
        }
        
        if(isset(self::$authorshipCache[$this->id])){
            foreach(self::$authorshipCache[$this->id] as $id){
                if(!isset($processed[$id])){
                    $processed[$id] = true;
                    $papers[] = $id;
                }
            }
        }
        
        if(!$onlyPublic){
            $allPapers = Paper::getAllPapers('all', $category, $grand, $onlyPublic);
            foreach($allPapers as $paper){
                if(!isset($processed[$paper->getId()]) &&
                   ($paper->getCreatedBy() == $this->id || 
                    $paper->getAccessId() == $this->id)){
                    $processed[$paper->getId()] = true;
                    $papers[] = $paper->getId();
                }
            }
        }
        
        foreach($papers as $pId){
            $paper = Paper::newFromId($pId);
            if(!$paper->deleted && ($category == 'all' || $paper->getCategory() == $category)){
                if($grand == 'grand' && $paper->isGrandRelated()){
                    $papersArray[] = $paper;
                }
                else if($grand == 'nonGrand' && !$paper->isGrandRelated()){
                    $papersArray[] = $paper;
                }
                else if($grand == 'both'){
                    $papersArray[] = $paper;
                }
            }
        }
        return $papersArray;
    }
    
    /**
     * Returns an array of Paper(s) authored/co-authored by this Person during the specified dates
     * @param string $category The category of Paper to get
     * @param string $startRange The starting date (start of the current reporting year if not specified)
     * @param string $endRange The end date (end of the current reporting year if not specified)
     * @param boolean $includeHQP Whether or not to include HQP in the result
     * @return array Returns an array of Paper(s) authored/co-authored by this Person during the specified dates
     */
    function getPapersAuthored($category="all", $startRange = CYCLE_START, $endRange = CYCLE_START_ACTUAL, $includeHQP=false){
        self::generateAuthorshipCache();
        $processed = array();
        $papersArray = array();
        $papers = array();
        if($includeHQP){
            foreach($this->getHQPDuring($startRange, $endRange) as $hqp){
                $ps = $hqp->getPapersAuthored($category, $startRange, $endRange, false);
                foreach($ps as $p){
                    if(!isset($processed[$p->getId()])){
                        $processed[$p->getId()] = true;
                        $papersArray[] = $p;
                    }
                }
            }
        }
        
        if(isset(self::$authorshipCache[$this->id])){
            foreach(self::$authorshipCache[$this->id] as $id){
                if(!isset($processed[$id])){
                    $papers[] = $id;
                }
            }
        }
        
        $papers = Product::getByIds($papers);
        foreach($papers as $paper){
            $date = $paper->getDate();
            if(!$paper->deleted && ($category == 'all' || $paper->getCategory() == $category) &&
               $paper->isGrandRelated() &&
               (strcmp($date, $startRange) >= 0 && strcmp($date, $endRange) <= 0 )){
                $papersArray[] = $paper;
            }
        }
        return $papersArray;
    }
    
    function getTopProductsLastUpdated(){
        $data = DBFunctions::select(array('grand_top_products'),
                                    array('changed'),
                                    array('type' => EQ('PERSON'),
                                          'obj_id' => EQ($this->getId())),
                                    array('changed' => 'DESC'));
        if(count($data) > 0){
            return $data[0]['changed'];
        }
    }
    
    function getTopProducts(){
        $products = array();
        $data = DBFunctions::select(array('grand_top_products'),
                                    array('product_id'),
                                    array('type' => EQ('PERSON'),
                                          'obj_id' => EQ($this->getId())));
        foreach($data as $row){
            $product = Product::newFromId($row['product_id']);
            $year = substr($product->getDate(), 0, 4);
            $authors = $product->getAuthors();
            $name = "";
            foreach($authors as $author){
                $name = $author->getNameForForms();
                break;
            }
            $products["{$year}"][$name][] = $product;
            ksort($products["{$year}"]);
        }
        ksort($products);
        $products = array_reverse($products);
        $newProducts = array();
        foreach($products as $year => $prods){
            foreach($prods as $prod){
                $newProducts = array_merge($newProducts, $prod);
            }
        }
        return $newProducts;
    }
    
    function getCoAuthors(){
        $coauthors = array();
        $papers = $this->getPapers();
        foreach($papers as $paper){
            $authors = $paper->getAuthors();
            foreach($authors as $author){
                $coauthors[$author->getName()] = $author->getName();
            }
        }
        return $coauthors;
    }
    
    // Returns a list of GRAND posters created by this user, or this user's HQP
    function getGrandPosters(){
        $posters = array();
        $hqps = array();
        if(!$this->isRole(HQP)){
            foreach($this->getHQP() as $hqp){
                $hqps[] = $hqp->getName();
            }
        }
        foreach($hqps as $hqp){
            $sql = "SELECT p1.*
                    FROM `mw_templatelinks` t, `mw_pagelinks`, mw_page p1, mw_page p2, mw_an_extranamespaces ns, mw_user u
                    WHERE pl_from = p1.page_id
                    AND pl_namespace = ns.nsId
                    AND pl_title = p2.page_title
                    AND pl_title = u.user_name
                    AND t.tl_from = p1.page_id
                    AND t.tl_title = 'Poster'
                    AND u.user_name = '{$hqp}'
                    AND u.deleted != '1'";
             $data = DBFunctions::execSQL($sql);
            if(DBFunctions::getNRows() > 0){
                foreach($data as $row){
                    $posters[$row['page_id']] = $row;
                }
            }
        }
        $sql = "SELECT p1.*
                FROM `mw_templatelinks` t, `mw_pagelinks`, mw_page p1, mw_page p2, mw_an_extranamespaces ns, mw_user u
                WHERE pl_from = p1.page_id
                AND pl_namespace = ns.nsId
                AND pl_title = p2.page_title
                AND pl_title = u.user_name
                AND t.tl_from = p1.page_id
                AND t.tl_title = 'Poster'
                AND u.user_name = '{$this->name}'
                AND u.deleted != '1'";
        $data = DBFunctions::execSQL($sql);
        if(DBFunctions::getNRows() > 0){
            foreach($data as $row){
                $posters[$row['page_id']] = $row;
            }
        }
        return $posters;
    }
    
    /**
     * Returns the date that this person became leader of the given Project
     * @param Project $project The Project that this person is/was a leader of
     * @return string The date that this person became a leader
     */
    function getLeaderStartDate($project){
        $dates = $this->getLeaderDates($project, 'leader');
        return $dates['start_date'];
    }
    
    /**
     * Returns the date that this person stopped being leader of the given Project
     * @param Project $project The Project that this person is/was a leader of
     * @return string The date that this person stopped being a leader
     */
    function getLeaderEndDate($project){
        $dates = $this->getLeaderDates($project, 'leader');
        return $dates['end_date'];
    }
    
    /**
     * Returns the date that this person became co-leader of the given Project
     * @param Project $project The Project that this person is/was a co-leader of
     * @return string The date that this person became a co-leader
     */
    function getCoLeaderStartDate($project){
        $dates = $this->getLeaderDates($project, 'co-leader');
        return $dates['start_date'];
    }
    
    /**
     * Returns the date that this person stopped being co-leader of the given Project
     * @param Project $project The Project that this person is/was a co-leader of
     * @return string The date that this person stopped being a co-leader
     */
    function getCoLeaderEndDate($project){
        $dates = $this->getLeaderDates($project, 'co-leader');
        return $dates['end_date'];
    }
    
    /**
     * Returns an array containing both the start and end dates that this Person
     * was leader/co-leader of the given project
     * @param Project $project The Project that this person is/was a leader of
     * @param string $lead Whether to look for 'leader' or 'co-leader'
     * @return array An array containing both the start and end 
     */
    private function getLeaderDates($project, $lead='leader'){
        foreach($project->getAllPreds() as $pred){
            $projectIds[] = $pred->getId();
        }
        $sql = "SELECT start_date, end_date
                FROM grand_project_leaders l, grand_project p
                WHERE l.project_id = p.id
                AND p.id IN (".implode(",", $projectIds).")
                AND l.user_id = '{$this->id}'
                AND l.type = '$lead'";
        $data = DBFunctions::execSQL($sql);
        $date = "";
        if(count($data) > 0){
            return $data[0];
        }
        return array('start_date' => '0000-00-00 00:00:00',
                     'end_date'   => '0000-00-00 00:00:00');
    }
    
    // Returns an array of projects that this person is a leader or co-leader.
    function leadership($history=false) {
        $ret = array();
        if(!$history){
            if(isset($this->leadershipCache['current'])){
                return $this->leadershipCache['current'];
            }
            $res = DBFunctions::execSQL("SELECT project_id
                                         FROM grand_project_leaders l, grand_project p
                                         WHERE l.project_id = p.id
                                         AND l.user_id = '{$this->id}'
                                         AND (l.end_date = '0000-00-00 00:00:00'
                                              OR l.end_date > CURRENT_TIMESTAMP)");
        }
        else{
            if(isset($this->leadershipCache['history'])){
                return $this->leadershipCache['history'];
            }
            $res = DBFunctions::execSQL("SELECT project_id
                                         FROM grand_project_leaders l, grand_project p
                                         WHERE l.project_id = p.id
                                         AND l.user_id = '{$this->id}'");
        }
        foreach ($res as &$row) {
            $project = Project::newFromId($row['project_id']);
            if($project != null && $project->getName() != "" && !$project->isDeleted()){
                $ret[] = $project;
            }
        }
        if(!$history){
            $this->leadershipCache['current'] = $ret;
        }
        else{
            $this->leadershipCache['history'] = $ret;
        }
        return $ret;
    }
    
    // Returns an array of projects that the user is a leader of
    // During a given range. If no range is provided, the range for the current year is given
    function leadershipDuring($startRange, $endRange){
        if(isset($this->leadershipCache[$startRange.$endRange])){
            return $this->leadershipCache[$startRange.$endRange];
        }
        
        $sql = "SELECT DISTINCT project_id
                FROM grand_project_leaders
                WHERE user_id = '{$this->id}'
                AND ( 
                ( (end_date != '0000-00-00 00:00:00') AND
                (( start_date BETWEEN '$startRange' AND '$endRange' ) || ( end_date BETWEEN '$startRange' AND '$endRange' ) || (start_date <= '$startRange' AND end_date >= '$endRange') ))
                OR
                ( (end_date = '0000-00-00 00:00:00') AND
                ((start_date <= '$endRange')))
                )";
        $data = DBFunctions::execSQL($sql);
        $projects = array();
        foreach($data as $row){
            $project = Project::newFromId($row['project_id']);
            if($project != null && 
               ((!$project->isDeleted()) || 
               ($project->isDeleted() && !($project->effectiveDate < $startRange)))){
                $projects[] = $project;
            }
        }
        $this->leadershipCache[$startRange.$endRange] = $projects;
        return $projects;
    }
    
    function leadershipOn($date){
        if(isset($this->leadershipCache[$date])){
            return $this->leadershipCache[$date];
        }
        
        $sql = "SELECT DISTINCT project_id
                FROM grand_project_leaders
                WHERE user_id = '{$this->id}'
                AND (('$date' BETWEEN start_date AND end_date ) OR (start_date <= '$date' AND end_date = '0000-00-00 00:00:00'))";
        $data = DBFunctions::execSQL($sql);
        $projects = array();
        foreach($data as $row){
            $project = Project::newFromId($row['project_id']);
            if($project != null && 
               ((!$project->isDeleted()) || 
               ($project->isDeleted() && !($project->effectiveDate < $date)))){
                $projects[] = $project;
            }
        }
        $this->leadershipCache[$date] = $projects;
        return $projects;
    } 
    
    // Returns true if this person is a leader or co-leader of a given project, false otherwise
    function leadershipOf($project) {
        if($project instanceof Project){
            $p = $project;
        }
        else{
            $p = Project::newFromHistoricName($project);
        }
        if($p == null || $p->getName() == ""){
            return false;
        }
        $data = DBFunctions::execSQL("SELECT 1
                                     FROM grand_project_leaders l, grand_project p 
                                     WHERE l.project_id = p.id
                                     AND l.user_id = '{$this->id}'
                                     AND p.name = '{$p->getName()}'
                                     AND (l.end_date = '0000-00-00 00:00:00'
                                          OR l.end_date > CURRENT_TIMESTAMP)");
       
        if(DBFunctions::getNRows() > 0){
            return true;
        }
        if(!$p->clear){
            foreach($p->getPreds() as $pred){
                if($this->leadershipOf($pred)){
                    return true;
                }
            }
        }
        return false;
    }
    
    // Returns true if the person is a leader of at least one project
    function isProjectLeader(){
        if($this->isProjectLeader != null){
            return $this->isProjectLeader;
        }
        self::generateLeaderCache();
        if(isset(self::$leaderCache[$this->id])){
            $this->isProjectLeader = true;
        }
        else{
            $this->isProjectLeader = false;
        }
        return $this->isProjectLeader;
    }
    
    function isProjectLeaderDuring($startRange, $endRange){
        $sql = "SELECT p.id
                FROM grand_project_leaders l, grand_project p
                WHERE l.type = 'leader'
                AND p.id = l.project_id
                AND l.user_id = '{$this->id}' 
                AND ( 
                ( (l.end_date != '0000-00-00 00:00:00') AND
                (( l.start_date BETWEEN '$startRange' AND '$endRange' ) || ( l.end_date BETWEEN '$startRange' AND '$endRange' ) || (l.start_date <= '$startRange' AND l.end_date >= '$endRange') ))
                OR
                ( (l.end_date = '0000-00-00 00:00:00') AND
                ((l.start_date <= '$endRange')))
                )";
        $data = DBFunctions::execSQL($sql);
        if(count($data) > 0){
            return true;
        }
        else{
            return false;
        }
    }
    
    // Returns true if the person is a manager of at least one project
    function isProjectManager(){
        if($this->isProjectManager === null){
            $sql = "SELECT p.id
                    FROM grand_project_leaders l, grand_project p
                    WHERE l.type = 'manager'
                    AND p.id = l.project_id
                    AND l.user_id = '{$this->id}' 
                    AND (l.end_date = '0000-00-00 00:00:00'
                         OR l.end_date > CURRENT_TIMESTAMP)";
            $data = DBFunctions::execSQL($sql);
            if(count($data) > 0){
                $this->isProjectManager = false;
                foreach($data as $row){
                    $project = Project::newFromId($row['id']);
                    if($project != null && !$project->isDeleted()){
                        $this->isProjectManager = true;
                        break;
                    }
                }
            }
            else{
                $this->isProjectManager = false;
            }
        }
        return $this->isProjectManager;
    }
    
    function isProjectManagerDuring($startRange, $endRange){
        $sql = "SELECT p.id
                FROM grand_project_leaders l, grand_project p
                WHERE l.type = 'manager'
                AND p.id = l.project_id
                AND l.user_id = '{$this->id}' 
                AND ( 
                ( (l.end_date != '0000-00-00 00:00:00') AND
                (( l.start_date BETWEEN '$startRange' AND '$endRange' ) || ( l.end_date BETWEEN '$startRange' AND '$endRange' ) || (l.start_date <= '$startRange' AND l.end_date >= '$endRange') ))
                OR
                ( (l.end_date = '0000-00-00 00:00:00') AND
                ((l.start_date <= '$endRange')))
                )";
        $data = DBFunctions::execSQL($sql);
        if(count($data) > 0){
            return true;
        }
        else{
            return false;
        }
    }
    
    function managementOf($project) {
        if($project instanceof Project){
            $p = $project;
        }
        else{
            $p = Project::newFromHistoricName($project);
        }
        if($p == null || $p->getName() == ""){
            return false;
        }
        $data = DBFunctions::execSQL("SELECT 1
                                     FROM grand_project_leaders l, grand_project p 
                                     WHERE l.project_id = p.id
                                     AND l.user_id = '{$this->id}'
                                     AND p.name = '{$p->getName()}' 
                                     AND l.type = 'manager'
                                     AND (l.end_date = '0000-00-00 00:00:00'
                                          OR l.end_date > CURRENT_TIMESTAMP)");
       
        if(DBFunctions::getNRows() > 0){
            return true;
        }
        if(!$p->clear){
            foreach($p->getPreds() as $pred){
                if($this->managementOf($pred)){
                    return true;
                }
            }
        }
        return false;
    }
    
    // Returns true if the person is a co-leader of at least one project
    function isProjectCoLeader(){
        if($this->isProjectCoLeader != null){
            return $this->isProjectCoLeader;
        }
        self::generateCoLeaderCache();
        if(isset(self::$coLeaderCache[$this->id])){
            $this->isProjectCoLeader = true;
        }
        else{
            $this->isProjectCoLeader = false;
        }
        return $this->isProjectCoLeader;
    }
    
    function isProjectCoLeaderDuring($startRange, $endRange){
        $sql = "SELECT p.id
                FROM grand_project_leaders l, grand_project p
                WHERE l.type = 'co-leader'
                AND p.id = l.project_id
                AND l.user_id = '{$this->id}' 
                AND ( 
                ( (l.end_date != '0000-00-00 00:00:00') AND
                (( l.start_date BETWEEN '$startRange' AND '$endRange' ) || ( l.end_date BETWEEN '$startRange' AND '$endRange' ) || (l.start_date <= '$startRange' AND l.end_date >= '$endRange') ))
                OR
                ( (l.end_date = '0000-00-00 00:00:00') AND
                ((l.start_date <= '$endRange')))
                )";
        $data = DBFunctions::execSQL($sql);
        if(count($data) > 0){
            return true;
        }
        else{
            return false;
        }
    }
    
    function getLeadProjects($history=false){
        $sql = "SELECT l.*
                FROM grand_project_leaders l
                WHERE l.user_id = '{$this->id}'
                AND l.type = 'leader'\n";
        if(!$history){
            $sql .= "AND (l.end_date = '0000-00-00 00:00:00'
                          OR l.end_date > CURRENT_TIMESTAMP)";
        }
        $data = DBFunctions::execSQL($sql);
        $projects = array();
        foreach($data as $row){
            $project = Project::newFromId($row['project_id']);
            $projects[$project->getName()] = $project;
        }
        ksort($projects);
        $projects = array_values($projects);
        return $projects;
    }
    
    function getCoLeadProjects($history=false){
        $sql = "SELECT *
                FROM grand_project_leaders l
                WHERE l.user_id = '{$this->id}'
                AND l.type = 'co-leader'\n";
      
        if(!$history){
            $sql .= "AND (l.end_date = '0000-00-00 00:00:00'
                          OR l.end_date > CURRENT_TIMESTAMP)";
        }
        $data = DBFunctions::execSQL($sql);
        $projects = array();
        foreach($data as $row){
            $project = Project::newFromId($row['project_id']);
            $projects[$project->getName()] = $project;
        }
        ksort($projects);
        $projects = array_values($projects);
        return $projects;
    }
    
    function getLeadAndCoLeadProjects($history=false){
        $sql = "SELECT *
                FROM grand_project_leaders l
                WHERE l.user_id = '{$this->id}'
                AND l.type IN ('leader','co-leader')\n";
        
        if(!$history){
            $sql .= "AND (l.end_date = '0000-00-00 00:00:00'
                          OR l.end_date > CURRENT_TIMESTAMP)";
        }
        $data = DBFunctions::execSQL($sql);
        $projects = array();
        foreach($data as $row){
            $project = Project::newFromId($row['project_id']);
            $projects[$project->getName()] = $project;
        }
        ksort($projects);
        $projects = array_values($projects);
        return $projects;
    }
    
    function getManagerProjects($history=false){
        $sql = "SELECT l.*
                FROM grand_project_leaders l
                WHERE l.user_id = '{$this->id}'
                AND l.type = 'manager'\n";
                
        if(!$history){
            $sql .= "AND (l.end_date = '0000-00-00 00:00:00'
                          OR l.end_date > CURRENT_TIMESTAMP)";
        }
        $data = DBFunctions::execSQL($sql);
        $projects = array();
        foreach($data as $row){
            $project = Project::newFromId($row['project_id']);
            $projects[$project->getName()] = $project;
        }
        ksort($projects);
        $projects = array_values($projects);
        return $projects;
    }
    
    function getLeadThemes($history=false){
        if(!$history && isset($this->themesCache['currentLead'])){
            return $this->themesCache['currentLead'];
        }
        $sql = "SELECT *
                FROM grand_theme_leaders
                WHERE user_id = '{$this->id}'
                AND co_lead = 'False'\n";
        if(!$history){
            $sql .= "AND (end_date = '0000-00-00 00:00:00'
                          OR end_date > CURRENT_TIMESTAMP)";
        }
        $data = DBFunctions::execSQL($sql);
        $themes = array();
        foreach($data as $row){
            $themes[$row['theme']] = Theme::newFromId($row['theme']);
        }
        if(!$history){
            $this->themesCache['currentLead'] = &$themes;
        }
        return $themes;
    }
    
    function getCoLeadThemes($history=false){
        if(!$history && isset($this->themesCache['currentCoLead'])){
            return $this->themesCache['currentCoLead'];
        }
        $sql = "SELECT *
                FROM grand_theme_leaders
                WHERE user_id = '{$this->id}'
                AND co_lead = 'True'\n";
        if(!$history){
            $sql .= "AND (end_date = '0000-00-00 00:00:00'
                          OR end_date > CURRENT_TIMESTAMP)";
        }
        $data = DBFunctions::execSQL($sql);
        $themes = array();
        foreach($data as $row){
            $themes[$row['theme']] = Theme::newFromId($row['theme']);
        }
        if(!$history){
            $this->themesCache['currentCoLead'] = &$themes;
        }
        return $themes;
    }
    
    /**
     * Returns an array of Projects that this Person is a Theme Leader of
     * @return array The Projects that this Person is a Theme Leader of
     */
    function getThemeProjects(){
        $projects = array();
        $themes = array_merge($this->getLeadThemes(), $this->getCoLeadThemes());
        if(count($themes) > 0){
            $themeIds = array();
            foreach($themes as $theme){
                $themeIds[] = $theme->getId();
            }
            $data = DBFunctions::select(array('grand_project_challenges'),
                                        array('project_id'),
                                        array('challenge_id' => IN($themeIds)));
            foreach($data as $row){
                $project = Project::newFromId($row['project_id']);
            }
        }
        return $projects;
    }
    
    /**
     * Returns the allocated amount that this Person received for the specified $year and $project
     * If no Project is specified, then the total amount for that year is returned.  If the data is not in the DB
     * Then it falls back to checking the uploaded revised budgets
     * @param int $year The allocation year
     * @param Project $project Which project this person received funding for
     * @param boolean $byProject Whether or not to return an array index by project with each allocation amount
     * @return int The amount of allocation
     */
    function getAllocatedAmount($year, $project=null, $byProject=false){
        if($byProject){
            $alloc = array();
        }
        else{
            $alloc = 0;
        }
        $data = DBFunctions::select(array('grand_allocations'),
                                    array('amount', 'project_id'),
                                    array('user_id' => EQ($this->getId()),
                                          'year' => EQ($year)));
        if(count($data) > 0){
            foreach($data as $row){
                if($project == null || $row['project_id'] == $project->getId()){
                    if($byProject){
                        $alloc[$row['project_id']] = $row['amount'];
                    }
                    else{
                        $alloc += $row['amount'];
                    }
                }
            }
        }
        else {
            // Check if there was an allocated budget uploaded for this Person
            $allocated = $this->getAllocatedBudget($year-1);
            if($allocated != null){
                if($project == null){
                    if($byProject){
                        $projects = $allocated->copy()->rasterize()->select(V_PROJ, array(".+"))->where(V_PROJ);
                        
                        
                        foreach($projects->xls as $rowN => $row){
                            foreach($row as $colN => $cell){
                                $projectName = $cell->getValue();
                                $proj = Project::newFromName($projectName);
                                if($proj != null){
                                    $alloc[$proj->getId()] = str_replace(",", "", 
                                                             str_replace("$", "", $allocated->copy()->rasterize()->select(V_PROJ, array("$projectName"))->where(COL_TOTAL)->toString()));
                                }
                            }
                        }
                    }
                    else{
                        $alloc = $allocated->copy()->rasterize()->where(COL_TOTAL)->select(ROW_TOTAL)->toString();
                    }
                }
                else {
                    $alloc = $allocated->copy()->rasterize()->select(V_PROJ, array("{$project->getName()}"))->where(COL_TOTAL)->toString();
                }
                if(!$byProject){
                    $alloc = str_replace("$", "", $alloc);
                    $alloc = str_replace(",", "", $alloc);
                    $alloc = intval($alloc);
                }
            }
        }
        return $alloc;
    }
    
    /**
     * Returns the allocated Budget for this Person for the given year
     * @param int $year The reporting year that the budget was requested
     * @return Budget The allocated Budget for this Person for the given year
     */
    function getAllocatedBudget($year){
        global $wgServer,$wgScriptPath;
        return $this->getRequestedBudget($year, RES_ALLOC_BUDGET);
    }
    
    /**
     * Returns the requested Budget for this Person for the given year
     * @param int $year The reporting year that the budget was requested
     * @param int $type Can be either RES_BUDGET or RES_ALLOC_BUDGET
     * @return Budget The requested Budget for this Person for the given year
     */
    function getRequestedBudget($year, $type=RES_BUDGET){
        global $wgServer,$wgScriptPath, $reporteeId;
        if($type == RES_BUDGET){
            $index = 'r'.$year;
        }
        else{
            $index = 's'.$year;
        }
        $uid = $this->id;
       
        $blob_type=BLOB_EXCEL;
        $rptype = RP_RESEARCHER;
        $section = $type;
        $item = 0;
        $subitem = 0;
        $rep_addr = ReportBlob::create_address($rptype,$section,$item,$subitem);
        $budget_blob = new ReportBlob($blob_type, $year, $uid, 0);
        $budget_blob->load($rep_addr);
        $lastChanged = $budget_blob->getLastChanged();
        $fileName = CACHE_FOLDER."personBudget{$this->id}_$index";
        if(Cache::exists($fileName)){
            $contents = Cache::fetch($fileName);
            if(strcmp($contents[0], $lastChanged) == 0){
                return $contents[1];
            }
        }
        if(file_exists($fileName)){
            // Check file cache as backup
            $contents = unserialize(implode("", gzfile($fileName)));
            if(strcmp($contents[0], $lastChanged) == 0){
                Cache::store($fileName, $contents);
                return $contents[1];
            }
        }
        $data = $budget_blob->getData();
        if (! empty($data)) {
            if($year != 2010 && $type == RES_BUDGET){
                $budget = new Budget("XLS", REPORT2_STRUCTURE, $data);
            }
            else if($year == 2010 && $type == RES_BUDGET){
                $budget = new Budget("CSV", REPORT_STRUCTURE, $data);
            }
            else {
                if($type == RES_ALLOC_BUDGET && $this->isRoleDuring(CNI, $year.CYCLE_START_MONTH, $year.CYCLE_END_MONTH)){
                    $budget = new Budget("XLS", REPORT2_STRUCTURE, $data);
                }
                else{
                    $budget = new Budget("XLS", SUPPLEMENTAL_STRUCTURE, $data);
                }
            }
            if($budget->nRows()*$budget->nCols() > 1){
                $budget->xls[0][1]->setValue($this->getNameForForms());
            }
            $contents = array($lastChanged, $budget);
            Cache::store($fileName, $contents);
            if(is_writable(CACHE_FOLDER)){
                $zp = gzopen($fileName, "w9");
                gzwrite($zp, serialize($contents));
                gzclose($zp);
            }
            return $budget;
        }
        else{
            return null;
        }
    }

    function isUnassignedEvaluator(){
        $current_evals = array(17,563,152,25,90,27,28,564,32,565,566,36,38,41,48,55,60,61,150,717,1263,1316,1317);
        if(in_array($this->id, $current_evals)){
            return true;
        }
        else{
            return false;
        }
    }
    
    // Returns true if the person is an evaluator
    function isEvaluator($year = YEAR){
        if(!isset($this->isEvaluator[$year])){
            $sql = "SELECT *
                    FROM grand_eval
                    WHERE user_id = '{$this->id}'
                    AND year = '{$year}'";
            $data = DBFunctions::execSQL($sql);
            if(count($data) > 0){
	            $this->isEvaluator[$year] = true;
	        }
	        else {
	            $this->isEvaluator[$year] = false;
	        }
	    }
	    return $this->isEvaluator[$year];
    }
    
    // Returns the list of Evaluation Submissions for this person
    function getEvaluateSubs($year = YEAR){
        $sql = "SELECT *
                FROM grand_eval
                WHERE user_id = '{$this->id}'
                AND year = '{$year}'";
        $data = DBFunctions::execSQL($sql);
        $subs = array();
        foreach($data as $row){
            if($row['type'] == "Project"){
                $subs[] = Project::newFromId($row['sub_id']);
            }
            else if($row['type'] == "Researcher" || $row['type'] == "PNI" || $row['type'] == "CNI"){
                $subs[] = Person::newFromId($row['sub_id']);
            }
        }
        $this->evaluateCache[$year] = $subs;
        return $subs;
    }
    
    static function getAllEvaluates($type, $year = YEAR){
        $type = mysql_real_escape_string($type);
        
        $sql = "SELECT DISTINCT sub_id 
                FROM grand_eval
                WHERE type = '$type'
                AND year = '{$year}'";
        $data = DBFunctions::execSQL($sql);
        $subs = array();
        foreach($data as $row){
            if($type != "Project"){
                $subs[] = Person::newFromId($row['sub_id']);
            }
            else{
                $subs[] = Project::newFromId($row['sub_id']);
            }
        }
        return $subs;
    }

    function getEvaluates($type, $year = YEAR){
        $type = mysql_real_escape_string($type);
        
        $sql = "SELECT *
                FROM grand_eval
                WHERE user_id = '{$this->id}'
                AND type = '$type'
                AND year = '{$year}'";
        $data = DBFunctions::execSQL($sql);
        $subs = array();

        foreach($data as $row){
            if($row['type'] == "Project"){
                $subs[] = Project::newFromId($row['sub_id']);
            }
            else if($row['type'] == "CNI" || $row['type'] == "PNI"){
                $subs[] = Person::newFromId($row['sub_id']);
            }
            else if($row['type'] == "LOI" || $row['type'] == "OPT_LOI"){
                $subs[] = LOI::newFromId($row['sub_id']);
            }
        }
        return $subs;
    }

    function getEvaluatePNIs($year = YEAR){
        $sql = "SELECT *
                FROM grand_eval
                WHERE user_id = '{$this->id}'
                AND type = 'PNI'
                AND year = '{$year}'";
        $data = DBFunctions::execSQL($sql);
        $subs = array();
        foreach($data as $row){
            if($row['type'] == "PNI"){
                $subs[] = Person::newFromId($row['sub_id']);
            }
        }
        return $subs;
    }
    
    // Returns the list of Evaluation Submissions for this person
    function getEvaluateCNIs($year = YEAR){
        $sql = "SELECT *
                FROM grand_eval
                WHERE user_id = '{$this->id}'
                AND type = 'CNI'
                AND year = '{$year}'";
        $data = DBFunctions::execSQL($sql);
        $subs = array();
        foreach($data as $row){
            if($row['type'] == "CNI"){
                $subs[] = Person::newFromId($row['sub_id']);
            }
        }
        return $subs;
    }
    
    function getEvaluateProjects($year = YEAR){
        $sql = "SELECT *
                FROM grand_eval
                WHERE user_id = '{$this->id}'
                AND type = 'Project'
                AND year = '{$year}'";
        $data = DBFunctions::execSQL($sql);
        $subs = array();
        foreach($data as $row){
            if($row['type'] == "Project"){
                $subs[] = Project::newFromId($row['sub_id']);
            }
        }
        return $subs;
    }

    // Returns a list of the evaluators who are evaluating this Person
    // Provide type 
    function getEvaluators($type='Researcher', $year = YEAR){
        $sql = "SELECT *
                FROM grand_eval
                WHERE sub_id = '{$this->id}'
                AND type = '{$type}'
                AND year = '{$year}'";
        $data = DBFunctions::execSQL($sql);
        $subs = array();
        foreach($data as $row){
            $subs[] = Person::newFromId($row['user_id']);
        }
        return $subs;
    }

    /**
     * Returns the allocation for this Person for year $year
     * @param string $year The allocation year to use
     * @return array The allocation information
     */
    function getAllocation($year) {
        $allocation = array('allocated_amount' => null, 'overall_score'=>null, 'email_sent'=>null);

        if (!is_numeric($year)) {
            return $allocation;
        }

        $query = "SELECT * FROM grand_review_results WHERE user_id = '{$this->id}' AND year='{$year}'";
        $res = DBFunctions::execSQL($query);

        if (count($res) > 0) {
            $allocation['allocated_amount'] = $res[0]['allocated_amount'];
            $allocation['overall_score'] = $res[0]['overall_score'];
            $allocation['email_sent'] = $res[0]['email_sent'];
        }
        
        return $allocation;
    }

    function getEthics(){
        $query = "SELECT * FROM grand_ethics WHERE user_id='{$this->id}'";
        $data = DBFunctions::execSQL($query);
        
        $ethics = array();

        $ethics['completed_tutorial'] = (isset($data[0]['completed_tutorial']))? $data[0]['completed_tutorial'] : 0;
        $ethics['date'] = (isset($data[0]['date']))? $data[0]['date'] : '0000-00-00';

        return $ethics; 
    }
    
    /**
     * Returns whether or not this Person is the author of the given Product
     * @param Product $paper The Product to see if this Person is on
     * @return boolean Whether or not this Person is the author of the given Product
     */
    function isAuthorOf($paper){
        if($paper instanceof Paper){
            $paper_authors = $paper->getAuthors();
            
            $im_author = false;    
            foreach ($paper_authors as $auth){
                if($auth->getName() == $this->name){
                    $im_author = true;
                    break;
                }
            }
            return $im_author;
        }
        else{
            return false;
        }
    }
    
    function isReceiverOf($contribution){
        if($contribution instanceof Contribution){
            $con_people = $contribution->getPeople();
            
            $con_receiver = false;
            foreach($con_people as $con_pers){
                if($con_pers instanceof Person){
                    $con_pers = $con_pers->getId();
                    if ( $con_pers == $this->id ){
                        $con_receiver =  true;
                        break;
                    }
                }
            }
            return $con_receiver;
        }
        else{
            return false;
        }
    }
    
    function hasReportingTicket($project, $year, $reportType, $ticket){
        $year = str_replace("'", "", $year);
        $ticket = str_replace("'", "", $ticket);
        if(!($project instanceof Project)){
            if(is_numeric($project)){
                $project = Project::newFromId($project);
            }
            else{
                $project = Project::newFromName($project);
            }
            if($project == null){
                $project = new Project(array());
                $project->id = 0;
            }
        }
        $sql = "SELECT *
                FROM `grand_reporting_year_ticket`
                WHERE `year` = '$year'
                AND `report_type` = '$reportType'
                AND `ticket` = '$ticket'
                AND `user_id` = '{$this->id}'
                AND `project_id` = '{$project->getId()}'
                AND `expires` >= CURRENT_TIMESTAMP
                LIMIT 1";
        $data = DBFunctions::execSQL($sql);
        if(count($data) > 0){
            return true;
        }
        return false;
    }
}
?>
