<?php

/**
 * @package GrandObjects
 */

class Person extends BackboneModel {

    static $cache = array();
    static $rolesCache = array();
    static $universityCache = array();
    static $leaderCache = array();
    static $themeLeaderCache = array();
    static $aliasCache = array();
    static $authorshipCache = array();
    static $namesCache = array();
    static $idsCache = array();
    static $allocationsCache = array();
    static $disciplineMap = array();
    static $allPeopleCache = array();

    var $user = null;
    var $name;
    var $email;
    var $phone;
    var $nationality;
    var $gender;
    var $photo;
    var $twitter;
    var $website;
    var $publicProfile;
    var $privateProfile;
    var $realname;
    var $firstName;
    var $lastName;
    var $middleName;
    var $prevFirstName;
    var $prevLastName;
    var $honorific;
    var $language;
    var $projects;
    var $university;
    var $universityDuring;
    var $isProjectLeader;
    var $groups;
    var $roles;
    var $rolesDuring;
    var $candidate;
    var $isEvaluator = array();
    var $relations;
    var $hqps;
    var $historyHqps;
    var $contributions;
    var $multimedia;
    var $aliases = false;
    var $roleHistory;
    var $budgets = array();
    var $leadershipCache = array();
    var $themesCache = array();
    var $coordCache = array();
    var $hqpCache = array();
    var $projectCache = array();
    var $evaluateCache = array();
    //CAPS specific
    var $confidential;
    var $collectDemo;
    var $collectComments;
    var $postal_code;
    var $city;
    var $province;
    var $province_string;
    var $specialty;
    var $years_in_practice;
    var $prior_abortion_service;
    var $accept_referrals;
    var $alias;

    
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
        self::$cache[$person->id] = $person;
        self::$cache[strtolower($person->name)] = $person;
        return $person;
    }
    
    /**
     * Returns a new Person from the given name
     * @param string $name The name of the person
     * @return Person The Person from the given name
     */
    static function newFromName($name){
        $name = strtolower(str_replace(' ', '.', $name));
        if(isset(Person::$cache[$name])){
            return Person::$cache[$name];
        }
        self::generateNamesCache();
        $data = array();
        if(isset(self::$namesCache[$name])){
            $data[] = self::$namesCache[$name];
        }
        $person = new Person($data);
        self::$cache[$person->id] = $person;
        self::$cache[strtolower($person->name)] = $person;
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
                                    array('LOWER(CONVERT(user_email using latin1))' => strtolower($email)));
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
        $person = Person::newFromId($wgUser->getId());
        $person->user = $wgUser;
        return $person;
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
        $name = unaccentChars(strtolower($name));
        self::generateNamesCache();
        $data = array();
        if(isset(self::$namesCache[$name])){
            $data[] = self::$namesCache[$name];
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
    static function newFromAlias($alias){
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
            if(isset($aliases[$alias]) && isset(self::$idsCache[$aliases[$alias]])){
                $data = array(self::$idsCache[$aliases[$alias]]);
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

    /**
     * Returns a new Person from the alias
     * @return Person The Person with the alias
     */
    static function newFromAliasCaps($var){
        $data = DBFunctions::select(array('mw_user'),
                                    array('user_id'),
                                    array('alias' => strtolower($var)));
        if(count($data) > 0){
            return Person::newFromId($data[0]['user_id']);
        }
        else{
            return null;
        }
    }
    /**
     * Removes certain characters from a person's name to help matching
     * @param string $name The name to clean
     * @return string The cleaned up name
     */
    static function cleanName($name){
        $name = preg_replace("/\(.*\)/", "", $name);
        $name = str_replace("'", "", $name);
        $name = str_replace(".", "", $name);
        $name = str_replace("*", "", $name);
        $name = str_replace("And ", "", $name);
        $name = str_replace("and ", "", $name);
        $name = trim($name);
        return $name;
    }
    
    /**
     * Caches the resultset of the alis table for superfast access
     */
    static function generateAliasCache(){
        if(count(self::$aliasCache) == 0){
            $data = DBFunctions::select(array('mw_user_aliases' => 'ua',
                                              'mw_user' => 'u'),
                                        array('ua.alias',
                                              'u.user_id'),
                                        array('ua.user_id' => EQ(COL('u.user_id')),
                                              'u.deleted' => NEQ(1)));
            foreach($data as $row){
                self::$aliasCache[$row['alias']] = $row['user_id'];
            }
        }
    }

    /**
     * Caches the resultset of the user table for superfast access
     */
    static function generateNamesCache(){
        if(count(self::$namesCache) == 0){
            $phoneNumbers = array();
            $phoneData = DBFunctions::select(array('grand_user_telephone'),
                                             array('user_id',
                                                   'area_code', 
                                                   'number'),
                                             array('primary_indicator' => EQ(1)));
            foreach($phoneData as $row){
                if($row['area_code'] == ""){
                    $phoneNumbers[$row['user_id']] = $row['number'];
                }
                else{
                    $phoneNumbers[$row['user_id']] = "{$row['area_code']}-{$row['number']}";
                }
            }
            $data = DBFunctions::select(array('mw_user'),
                                        array('user_id',
                                              'user_name',
                                              'user_real_name',
                                              'first_name',
                                              'middle_name',
                                              'last_name',
                                              //'prev_first_name',
                                              //'prev_last_name',
                                              //'honorific',
                                              //'language',
                                              'user_email',
                                              'user_twitter',
                                              'user_website',
                                              'user_public_profile',
                                              'user_private_profile',
                                              'user_nationality',
                                              'user_gender',
                                              'candidate',
                                              'collect_comments',
                                              'collect_demo',
                                              'confidential',
                                              'alias'),
                                        array('deleted' => NEQ(1)));
            foreach($data as $row){
                if(isset($phoneNumbers[$row['user_id']])){
                    $row['phone'] = $phoneNumbers[$row['user_id']];
                }
                $exploded = explode(".", unaccentChars($row['user_name']));
                
                $firstName = ($row['first_name'] != "") ? unaccentChars($row['first_name']) : @$exploded[0];
                $lastName = ($row['last_name'] != "") ? unaccentChars($row['last_name']) : @$exploded[1];
                $middleName = $row['middle_name'];
                
                self::$idsCache[$row['user_id']] = $row;
                self::$namesCache[strtolower($row['user_name'])] = $row;
                self::$namesCache[strtolower("$firstName $lastName")] = $row;
                self::$namesCache[strtolower("$lastName $firstName")] = $row;
                self::$namesCache[strtolower("$firstName ".substr($lastName, 0, 1))] = $row;
                self::$namesCache[strtolower("$lastName ".substr($firstName, 0, 1))] = $row;
                self::$namesCache[strtolower(substr($firstName, 0, 1)." $lastName")] = $row;
                if(trim($row['user_real_name']) != '' && $row['user_name'] != trim($row['user_real_name'])){
                    self::$namesCache[unaccentChars(strtolower(str_replace("&nbsp;", " ", $row['user_real_name'])))] = $row;
                }
                if($middleName != ""){
                    $middleName = unaccentChars($middleName);
                    self::$namesCache[strtolower("$firstName $middleName $lastName")] = $row;
                    self::$namesCache[strtolower("$firstName ".substr($middleName, 0, 1)." $lastName")] = $row;
                    self::$namesCache[strtolower("$lastName ".substr($firstName, 0, 1).substr($middleName, 0, 1))] = $row;
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
     * Caches the resultset of the leaders
     */
    static function generateLeaderCache(){
        if(count(self::$leaderCache) == 0){
            $sql = "SELECT l.user_id, p.id, p.name, s.type, s.status
                    FROM grand_project_leaders l, grand_project p, grand_project_status s
                    WHERE l.type = 'leader'
                    AND p.id = l.project_id
                    AND p.id = s.project_id
                    AND (l.end_date = '0000-00-00 00:00:00'
                         OR l.end_date > CURRENT_TIMESTAMP)
                    GROUP BY s.project_id, l.user_id";
            $data = DBFunctions::execSQL($sql);
            self::$leaderCache[-1][] = array();
            foreach($data as $row){
                self::$leaderCache[$row['user_id']][] = $row;
            }
        }
    }
    
    /**
     * Caches the resultset of the theme leaders
     */
    static function generateThemeLeaderCache(){
        if(count(self::$themeLeaderCache) == 0){
            $sql = "SELECT *
                    FROM grand_theme_leaders
                    WHERE co_lead = 'False'
                    AND (end_date = '0000-00-00 00:00:00'
                         OR end_date > CURRENT_TIMESTAMP)";
            $data = DBFunctions::execSQL($sql);
            self::$themeLeaderCache[TL] = array();
            self::$themeLeaderCache[TC] = array();
            foreach($data as $row){
                $type = ($row['coordinator'] == 'True') ? TC : TL;
                self::$themeLeaderCache[$type][$row['user_id']][] = $row;
            }
        }
    }
    
    /**
     * Caches the resultset of the user universities
     */
    static function generateUniversityCache(){
        if(count(self::$universityCache) == 0){
            $sql = "SELECT user_id, university_name, department, position, end_date
                    FROM (SELECT * 
                          FROM grand_user_university 
                          ORDER BY REPLACE(end_date, '0000-00-00 00:00:00', '9999-12-31 00:00:00') DESC) uu,
                          grand_universities u, grand_positions p 
                    WHERE u.university_id = uu.university_id
                    AND uu.position_id = p.position_id
                    GROUP BY user_id";
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                self::$universityCache[$row['user_id']] = 
                    array("university" => $row['university_name'],
                          "department" => $row['department'],
                          "position"   => $row['position'],
                          "date"       => $row['end_date']);
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
             $data = DBFunctions::select(array('grand_product_authors'),
                                        array('author', 'product_id'));
            foreach($data as $row){
                if(is_numeric($row['author'])){
                    self::$authorshipCache[$row['author']][] = $row['product_id'];
                }
            }
        }
    }
    
    /**
     * Caches the partial resultset of the mw_user table
     */
    static function generateAllPeopleCache(){
        if(count(self::$allPeopleCache) == 0){
            $data = DBFunctions::select(array('mw_user'),
                                        array('user_id'),
                                        array('deleted' => NEQ(1),
                                              'candidate' => NEQ(1)),
                                        array('user_name' => 'ASC'));
            
            foreach($data as $row){
                self::$allPeopleCache[] = $row['user_id'];
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
                                    array('`order`' => 'ASC',
                                          'university_name' => 'ASC'));
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
                                    array('`order`' => 'ASC',
                                          'position' => 'ASC'));
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
        self::generateAllPeopleCache();
        $people = array();
        foreach(self::$allPeopleCache as $row){
            $person = Person::newFromId($row);
            if($person->isRoleAtLeast(STAFF)){
                $people[] = $person;
            }
        }
        return $people;
    }

    /**
     * Returns an array of People of the type $filter
     * @param string $filter The role to filter by
     * @param boolean $idOnly Whether or not to only include the id, rather than instantiating a Person object (may result in slightly different results!)
     * @return array The array of People of the type $filter
     */
    static function getAllPeople($filter=null, $idOnly=false){
        global $config;
        if($filter == NI){
            $ars = self::getAllPeople(AR);
            $cis = self::getAllPeople(CI);
            return array_merge($ars, $cis);
        }
        $me = Person::newFromWgUser();
        self::generateAllPeopleCache();
        self::generateRolesCache();
        $people = array();
        foreach(self::$allPeopleCache as $row){
            if($filter == INACTIVE && !isset(self::$rolesCache[$row])){
                $person = Person::newFromId($row);
                if($idOnly){
                    $people[] = $row;
                }
                else{
                    $people[] = $person;
                }
            }
            if($filter == TL || $filter == TC || $filter == PL || $filter == APL){
                self::generateThemeLeaderCache();
                self::generateLeaderCache();
                if(isset(self::$themeLeaderCache[$filter][$row]) ||
                   (($filter == PL || $filter == APL) && isset(self::$leaderCache[$row]))){
                    $person = Person::newFromId($row);
                    if($filter == APL && !$person->isRole(APL)){
                        continue;
                    }
                    if($person->getName() != "WikiSysop"){
                        if($me->isLoggedIn() || $person->isRoleAtLeast(NI)){
                            if($idOnly){
                                $people[] = $row;
                            }
                            else{
                                $people[] = $person;
                            }
                        }
                    }
                }
            }
            if($filter == null || $filter == "all" || isset(self::$rolesCache[$row])){
                if($filter != null && $filter != "all"){
                    $found = false;
                    foreach(self::$rolesCache[$row] as $role){
                        if($role['role'] == $filter){
                            $found = true;
                        }
                    }
                    if(!$found){
                        continue;
                    }
                }
                if($idOnly){
                    $people[] = $row;
                    continue;
                }
                $person = Person::newFromId($row);
                if($person->getName() != "WikiSysop"){
                    if($me->isLoggedIn() || $person->isRoleAtLeast(NI) || $config->getValue('hqpIsPublic') ){
                        $people[] = $person;
                    }
                }
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
        self::generateAllPeopleCache();
        $people = array();
        foreach(self::$allPeopleCache as $row){
            $person = Person::newFromId($row);
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
        self::generateAllPeopleCache();
        $people = array();
        foreach(self::$allPeopleCache as $row){
            $person = Person::newFromId($row);
            if($person->getName() != "WikiSysop" && ($filter == null || $filter == "all" || $person->isRoleOn($filter, $date))){
                $people[] = $person;
            }
        }
        return $people;
    }
    
    /**
     * Returns an array of People of the type $filter and are also candidates
     * @param string $filter The role to filter by
     * @return array The array of People of the type $filter
     */
    static function getAllCandidates($filter=null){
        if($filter == NI){
            $ars = self::getAllCandidates(AR);
            $cis = self::getAllCandidates(CI);
            $nps = self::getAllCandidates(NP);
            $mws = self::getAllCandidates(MW);
            return array_merge($ars, $cis, $nps, $mws);
        }
        $me = Person::newFromWgUser();
        $data = DBFunctions::select(array('mw_user'),
                                    array('user_id', 'user_name'),
                                    array('deleted' => NEQ(1)),
                                    array('user_name' => 'ASC'));
        $people = array();
        foreach($data as $row){
            $rowA = array();
            $rowA[0] = $row;
            $person = Person::newFromId($rowA[0]['user_id']);
            if($person->getName() != "WikiSysop" && ($filter == null || $filter == "all" || $person->isRole($filter.'-Candidate'))){
                if($me->isLoggedIn() || $person->isRoleAtLeast(NI)){
                    $people[] = $person;
                }
            }
        }
        return $people;
    }

    // Constructor
    // Takes in a resultset containing the 'user id' and 'user name'
    function __construct($data){
        global $wgUser;
        if(count($data) > 0){
            if(@$data[0]['candidate'] == 1 && !$wgUser->isLoggedIn()){
                return;
            }
            $this->id = @$data[0]['user_id'];
            $this->name = @$data[0]['user_name'];
            $this->realname = @$data[0]['user_real_name'];
            $this->firstName = @$data[0]['first_name'];
            $this->lastName = @$data[0]['last_name'];
            $this->middleName = @$data[0]['middle_name'];
            //$this->prevFirstName = @$data[0]['prev_first_name'];
            //$this->prevLastName = @$data[0]['prev_last_name'];
            //$this->honorific = @$data[0]['honorific'];
            //$this->language = @$data[0]['language'];
            $this->email = @$data[0]['user_email'];
            $this->phone = @$data[0]['phone'];
            $this->gender = @$data[0]['user_gender'];
            $this->nationality = @$data[0]['user_nationality'];
            $this->university = false;
            $this->twitter = @$data[0]['user_twitter'];
            $this->website = @$data[0]['user_website'];
            $this->publicProfile = @$data[0]['user_public_profile'];
            $this->privateProfile = @$data[0]['user_private_profile'];
            $this->hqps = null;
            $this->historyHqps = null;
            $this->candidate = @$data[0]['candidate'];
            $this->confidential = @$data[0]['confidential'];
            $this->collectDemo = @$data[0]['collect_demo'];
            $this->collectComments = @$data[0]['collect_comments'];
            $this->alias = @$data[0]['alias'];
        }
	    $this->getCapsPersonalInformation();
    }
    
    function toArray(){
        global $wgUser;
        $privateProfile = "";
        $publicProfile = $this->getProfile(false);
        if($wgUser->isLoggedIn()){
            $privateProfile = $this->getProfile(true);
        }
        $roles = array();
        foreach($this->getRoles() as $role){
            if($role->getId() != -1){
                $roles[] = array('id' => $role->getId(),
                                 'role' => $role->getRole(),
                                 'title' => $role->getTitle());
            }
        }
        foreach($this->leadership() as $project){
            $role = PL;
            if($project->getType() == 'Administrative'){
                $role = APL;
            }
            $roles[] = array('id' => '',
                             'role' => $role,
                             'title' => $project->getName());
        }
        foreach($this->getLeadThemes() as $theme){
            $roles[] = array('id' => '',
                             'role' => TL,
                             'title' => $theme->getAcronym());
        }
        foreach($this->getCoordThemes() as $theme){
            $roles[] = array('id' => '',
                             'role' => TC,
                             'title' => $theme->getAcronym());
        }
        $json = array('id' => $this->getId(),
                      'name' => $this->getName(),
                      'realName' => $this->getRealName(),
                      'fullName' => $this->getNameForForms(),
                      'reversedName' => $this->getReversedName(),
                      'email' => $this->getEmail(),
                      'phone' => $this->getPhoneNumber(),
                      'gender' => $this->getGender(),
                      'nationality' => $this->getNationality(),
                      'twitter' => $this->getTwitter(),
                      'website' => $this->getWebsite(),
                      'photo' => $this->getPhoto(),
                      'cachedPhoto' => $this->getPhoto(true),
                      'university' => $this->getUni(),
                      'department' => $this->getDepartment(),
                      'position' => $this->getPosition(),
                      'roles' => $roles,
                      'publicProfile' => $publicProfile,
                      'privateProfile' => $privateProfile,
                      'url' => $this->getUrl(),
		              'postal_code' => $this->getPostalCode(),
		              'city' => $this->getCity(),
		              'province' => $this->getProvince(),
		              'specialty' => $this->getSpecialty(),
		              'years_in_practice' => $this->getYearsInPractice(),
		              'prior_abortion_service' => $this->getPriorAbortionService(),
		              'accept_referrals' => $this->getAcceptReferrals());
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

             $status = DBFunctions::insert('grand_personal_caps_info',
                                     array('user_id' => $this->getId(),
                                           'postal_code' => $this->getPostalCode(),
                                           'city' => $this->getCity(),
                                           'province' => $this->getProvince(),
                                           'specialty' => $this->getSpecialty(),
                                           'years_in_practice' => $this->getYearsInPractice(),
                                           'prior_abortion_service' => $this->getPriorAbortionService(),
                                           'accept_referrals' => $this->getAcceptReferrals()),
                                     true);
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
                                          'first_name' => $this->getFirstName(),
                                          'middle_name' => $this->getMiddleName(),
                                          'last_name' => $this->getLastName(),
                                          //'prev_first_name' => $this->getPrevFirstName(),
                                          //'prev_last_name' => $this->getPrevLastName(),
                                          //'honorific' => $this->getHonorific(),
                                          //'language' => $this->getCorrespondenceLanguage(),
                                          'user_twitter' => $this->getTwitter(),
                                          'user_website' => $this->getWebsite(),
                                          'user_gender' => $this->getGender(),
                                          'alias' => $this->getAlias(),
                                          'user_nationality' => $this->getNationality(),
                                          'user_public_profile' => $this->getProfile(false),
                                          'user_private_profile' => $this->getProfile(true)),
                                    array('user_id' => EQ($this->getId())));


            $province = Province::newFromName($this->getProvinceString());
            $status = DBFunctions::update('grand_personal_caps_info',
                                    array('postal_code' => $this->getPostalCode(),
                                          'city' => $this->getCity(),
                                          'province' =>$province->getId(),
                                          'specialty' => $this->getSpecialty(),
                                          'years_in_practice' => $this->getYearsInPractice(),
                                          'prior_abortion_service' => $this->getPriorAbortionService(),
                                          'accept_referrals' => $this->accept_referrals),
                                    array('user_id' =>EQ($this->getId())));
            $this->getUser()->invalidateCache();
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
    
    /**
     * Returns whether or not this Person is allowed to edit the specified Person
     * @param Person $person The Person to edit
     * @return Person Whether or not this Person is allowd to edit the specified Person
     */
    function isAllowedToEdit($person){
        if($this->isRoleAtLeast(STAFF)){
            return true;
        }
        if($this->isRole(NI) && !$person->isRoleAtLeast(COMMITTEE)){
            return true;
        }
        if($this->isProjectLeader() && (!$person->isRoleAtLeast(COMMITTEE) || $person->isRole(NI) || $person->isRole(HQP))){
            return true;
        }
        if($this->isThemeCoordinator() && (!$person->isRoleAtLeast(COMMITTEE) || $person->isRole(NI) || $person->isRole(HQP))){
            return true;
        }
        if($this->isRoleAtLeast(COMMITTEE) && !$person->isRoleAtLeast(STAFF)){
            return true;
        }
        return false;
        if(!$this->isRoleAtLeast(STAFF) && // Handles Staff+
           (($this->isRole(NI) && $person->isRoleAtLeast(COMMITTEE)) || // Handles regular NI
            ($this->isProjectLeader() && $person->isRoleAtLeast(COMMITTEE) && !$person->isRole(NI) && $person->isRole(HQP)) || // Handles PL
            ($this->isThemeLeader() && $person->isRoleAtLeast(COMMITTEE) && !$person->isRole(NI) && $person->isRole(HQP)) || // Handles TL
            ($this->isThemeCoordinator() && $person->isRoleAtLeast(COMMITTEE) && !$person->isRole(NI) && $person->isRole(HQP)) || // Handles TC
            ($this->isRoleAtLeast(COMMITTEE) && $person->isRoleAtLeast(STAFF))  // Handles RMC-GOV
           )){
            return false;
        }
        return true;
    }
    
    /**
     * Returns the Mediawiki User object for this Person
     * @return User The Mediawiki User object for this Person
     */
    function getUser(){
        if($this->user == null){
            $this->user = User::newFromId($this->id);
            $this->user->load();
        }
        return $this->user;
    }
    
    /**
     * Returns whether or not this Person is logged in or not
     * @return boolean Whether or not this Person is logged in or not
     */
    function isLoggedIn(){
        $user = $this->getUser();
        return $user->isLoggedIn();
    }
    
    /**
     * Returns when the User registered
     * @return string The string representing the date that this user Registered
     */
    function getRegistration(){
        return $this->getUser()->getRegistration();
    }
      
    /**
     * Returns whether this Person is a member of the given Project or not
     * @param Project $project The Project to check
     * @return boolean Whether or not this Person is currently a member of the given Project
     */
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
    
    /**
     * Returns whether this Person is a member of the given Project during the given dates
     * @param Project $project The Project to check
     * @param string $start The start date
     * @param string $end The end date
     * @return boolean Whether or not this Person is a member of the given Project
     */
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
     * @param Project $project The Project that the Person has been on
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
    
    /**
     * Returns whether or not this Person is theme leader
     * @return boolean Whether or not this Person is a theme leader
     */
    function isThemeLeader(){
        self::generateThemeLeaderCache();
        return (isset(self::$themeLeaderCache[TL][$this->id]));
    }
    
    /**
     * Returns whether or not this Person is a theme coodinator
     * @return boolean Whether or not this Person is a theme coodinator
     */
    function isThemeCoordinator(){
        self::generateThemeLeaderCache();
        return (isset(self::$themeLeaderCache[TC][$this->id]));
    }
    
    /**
     * Returns whether or not this Person is a theme leader of the given Project
     * @param Project $project The Project to check
     * @return boolean Whether or not this Person is a theme leader of the given Project
     */
    function isThemeLeaderOf($project){
        $themes = $this->getLeadThemes();
        if($project instanceof Theme){
            $challenge = $project;
        }
        else {
            $challenge = $project->getChallenge();
        }
        foreach($themes as $theme){
            if($challenge->getId() == $theme->getId()){
                return true;
            }
        }
        return false;
    }
    
    /**
     * Returns whether or not this Person is a theme coodinator of the given Project
     * @param Project $project The Project to check
     * @return boolean Whether or not this Person is a theme coordinator of the given Project
     */
    function isThemeCoordinatorOf($project){
        $themes = $this->getCoordThemes();
        if($project instanceof Theme){
            $challenge = $project;
        }
        else {
            $challenge = $project->getChallenge();
        }
        foreach($themes as $theme){
            if($challenge->getId() == $theme->getId()){
                return true;
            }
        }
        return false;
    }
    
    /**
     * Returns whether or not this Person is a champion of the given Project
     * @param Project $project The Project to check
     * @return boolean Whether or not this Person is a champion of the given Project
     */
    function isChampionOf($project){
        $champs = $project->getChampions();
        foreach($champs as $champ){
            if($champ['user']->getId() == $this->getId()){
                return true;
            }
        }
        return false;
    }
    
    /**
     * Returns whether or not this Person is a champion of the given Project on the given date
     * @param Project $project The Project to check
     * @param string $date The date that the Person was on the Project
     * @return boolean Whether or not this Person is a champion of the Project
     */
    function isChampionOfOn($project, $date){
        $champs = $project->getChampionsOn($date);
        foreach($champs as $champ){
            if($champ['user']->getId() == $this->getId()){
                return true;
            }
        }
        return false;
    }
    
    /**
     * Returns whether or not this Person is a champion of the given Project between the given dates
     * @param Project $project The Project to check
     * @param string $start The start date that the Person was on the Project
     * @param string $end The end date that the Person was on the Project
     * @return boolean Whether or not this Person is a champion of the Project
     */
    function isChampionOfDuring($project, $start, $end){
        $champs = $project->getChampionsDuring($start, $end);
        foreach($champs as $champ){
            if($champ['user']->getId() == $this->getId()){
                return true;
            }
        }
        return false;
    }
    
    /**
     * Returns the id of this Person.  
     * Returns 0 if the user doesn't exist or if is an HQP and the current user is not logged in 
     * @return int The id of this Person
     */
    function getId(){
        global $config;
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn() && !$this->isRoleAtLeast(NI) && !$config->getValue('hqpIsPublic')){
            return 0;
        }
        return $this->id;
    }
    
    /**
     * Returns the user name of this Person
     * @return string The user name of this Person
     */
    function getName(){
        $me = Person::newFromWgUser();
	    if($me->isRoleAtLeast(MANAGER) || $this->getId() == $me->getId() || $this->isRole(MANAGER) || $this->isRole(Expert)){
            return $this->name;
        }
        else if($this->name != ""){
            return $this->getAlias();
        }
        return "";
    }
    
    /**
     * Returns the real name of this Person
     * @return string The real name of this Person
     */
    function getRealName(){
        $me = Person::newFromWgUser();
	    if($me->isRoleAtLeast(MANAGER) || $this->getId() == $me->getId() || $this->isRole(MANAGER) || $this->isRole(Expert)){
            return $this->realname;
        }
        else if($this->realname != ""){
            return $this->getAlias();
        }
        return "";
    }
    
    /**
     * Returns the email address of this Person
     * @return string The email address of this Person
     */
    function getEmail(){
        $me = Person::newFromWgUser();
        if($me->isLoggedIn() || $this->isRoleAtLeast(STAFF) || $this->isRole(SD) || $this->isRoleAtLeast(COMMITTEE)){
            return "{$this->email}";
        }
        return "";
    }
    
    /**
     * Returns the phone number of this Person
     * @return string The phone number of this Person
     */
    function getPhoneNumber(){
        $me = Person::newFromWgUser();
        if($me->isLoggedIn() || $this->isRoleAtLeast(STAFF) || $this->isRole(SD)){
            return trim("{$this->phone}");
        }
        return "";
    }
    
    /**
     * Returns the gender of this Person
     * @return string The gender of this Person
     */
    function getGender(){
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            return $this->gender;
        }
        return "";
    }
    
    /**
     * Returns the nationality of this Person
     * @return string The nationality of this Person
     */
    function getNationality(){
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            return $this->nationality;
        }
        return "";
    }
    
    /**
     * Returns the handle of this Person's twitter account
     * @return string The handle of this Person's twitter account
     */
    function getTwitter(){
        return $this->twitter;
    }

    /**
     * Returns the postal code of this Person
     * @return string The postal code of this Person
     */
    function getPostalCode(){
        $me = Person::newFromWgUser();
        if($me->isLoggedIn() || $this->isRoleAtLeast(STAFF) || $this->isRole(SD)){
            return $this->postal_code;
        }
    }

    /**
     * Returns the city of this Person
     * @return string The city of this Person
     */
    function getCity(){
        return $this->city;
    }
   
    /**
     * Returns the alias of this Person
     * @return string The alias of this Person
     */
    function getAlias(){
        return $this->alias;
    }

    /**
     * Returns the province ID of this Person
     * @return string The province ID of this Person
     */
    function getProvince(){
        $province = "";
        $data = DBFunctions::select(array('grand_provinces'),
                                    array('*'),
                                    array('id' => $this->province));
        if(count($data)>0){
            $province = $data[0]['province'];
        }
        return $province;
    }
    
    function getProvinceFromPostalCode(){
        $postal = strtoupper(substr(trim($this->getPostalCode()), 0, 1));
        switch($postal){
            case "A":
                return "Newfoundland and Labrador";
            case "B":
                return "Nova Scotia";
            case "C":
                return "Prince Edward Island";
            case "E":
                return "New Brunswick";
            case "G":
            case "H":
            case "J":
                return "Quebec";
            case "K":
            case "L":
            case "M":
            case "N":
            case "P":
                return "Ontario";
            case "R":
                return "Manitoba";
            case "S":
                return "Saskatchewan";
            case "T":
                return "Alberta";
            case "V":
                return "British Columbia";
            case "X":
                return "Northwest Territories and Nunavut";
            case "Y":
                return "Yukon Territory";
        }
        return $this->getProvince();
    }

    /**
     * Returns the province of this Person
     * @return string The province of this Person
     */
    function getProvinceString(){
        return $this->province_string;

    }

    /**
     * Returns the specialty of this Person
     * @return string The specialty of this Person
     */
    function getSpecialty(){
        return $this->specialty;
    }

    /**
     * Returns the city of this Person
     * @return string The city of this Person
     */
    function getYearsInPractice(){
        return $this->years_in_practice;
    }

    /**
     * Returns the city of this Person
     * @return string The city of this Person
     */
    function getPriorAbortionService(){
	if($this->prior_abortion_service === 0){
	     return false;
	}
        return true;
    }

    /**
     * Returns the city of this Person
     * @return string The city of this Person
     */
    function getAcceptReferrals(){
        if($this->accept_referrals == 0){
             return "No";
        }
        return "Yes";
    }
 
    /**
     * Returns the url of this Person's website
     * @return string The url of this Person's website
     */
    function getWebsite(){
        if (preg_match("#https?://#", $this->website) === 0) {
            $this->website = 'http://'.$this->website;
        }
        return $this->website;
    }
    
    /**
     * Returns the url of this Person's profile page
     * @return string The url of this Person's profile page
     */
    function getUrl(){
        global $wgServer, $wgScriptPath, $config;
        $me = Person::newFromWgUser();
        if($this->id > 0 && ($me->isLoggedIn() || $config->getValue('hqpIsPublic') ||$this->isRoleAtLeast(NI)) && (!isset($_GET['embed']) || $_GET['embed'] == 'false')){
            return "{$wgServer}{$wgScriptPath}/index.php/{$this->getType()}:{$this->getId()}";
        }
        else if($this->id > 0 && ($me->isLoggedIn() || $config->getValue('hqpIsPublic') || $this->isRoleAtLeast(NI)) && isset($_GET['embed'])){
            return "{$wgServer}{$wgScriptPath}/index.php/{$this->getType()}:{$this->getId()}?embed";
        }
        return "";
    }
    
    /**
     * Returns the path to a photo of this Person if it exists
     * @param boolen $cached Whether or not to use a cached version
     * @return string The path to a photo of this Person
     */
    function getPhoto($cached=false){
        global $wgServer, $wgScriptPath;
        if($this->photo == null || !$cached){
            if(file_exists("Photos/".str_ireplace(".", "_", $this->getName()).".jpg")){
                $this->photo = "$wgServer$wgScriptPath/Photos/".str_ireplace(".", "_", $this->getName()).".jpg";
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
    
    /**
     * Returns the name of this Person with dots replaced by spaces
     * @return string The name of this Person with dots replaced by spaces
     */
    function getNameForPost(){
        $repl = array('.' => '_', ' ' => '_');
        return strtr($this->getName(), $repl);
    }
    
    /**
     * Returns an array of the name in the form ["first", "last"]
     * @return array An array containing the first and last names
     */
    function splitName(){
        if(!empty($this->realname)){
            $names = explode(" ", $this->realname);
            $lastname = ucfirst($names[count($names)-1]);
            unset($names[count($names)-1]);
            $firstname = implode(" ", $names);
        }
        else{
            $names = explode(".", $this->getName(), 2);
            $lastname = "";
            if(count($names) > 1){
                $lastname = str_ireplace(".", " ", $names[1]);
            }
            else if(strstr($names[0], " ") != false){
            // Some names do not follow the First.Last convention, so we need to do some extra work
                $names = explode(" ", $this->getName(), 2);
                if(count($names > 1)){
                    $lastname = $names[1];
                }
            }
            $firstname = $names[0];
        }
        return array("first" => str_replace("&nbsp;", " ", ucfirst($firstname)), "last" => str_replace("&nbsp;", " ", ucfirst($lastname)));
    }
    
    /**
     * Returns the first name of this Person
     * If the first name was explicitly set then use that, 
     * otherwise it will parse it from the username
     * @return String The first name of this Person
     */
    function getFirstName(){
        if($this->firstName != ""){
            return $this->firstName;
        }
        $splitName = $this->splitName();
        return $splitName['first'];
    }
    
    /**
     * Returns the middle name of this Person
     * @return String The middle name of this Person
     */
    function getMiddleName(){
        return $this->middleName;
    }
    
    /**
     * Returns the last name of this Person
     * If the last name was explicitly set then use that, 
     * otherwise it will parse it from the username
     * @return String The last name of this Person
     */
    function getLastName(){
        if($this->lastName != ""){
            return $this->lastName;
        }
        $splitName = $this->splitName();
        return $splitName['last'];
    }
    
    /**
     * Returns the previous first name of this Person
     * @return String The previous first name of this Person
     */
    function getPrevFirstName(){
        return $this->prevFirstName;
    }
    
    /**
     * Returns the previous last name of this Person
     * @return String The previous last name of this Person
     */
    function getPrevLastName(){
        return $this->prevLastName;
    }
    
    /**
     * Returns the honorific (Dr. Mrs. etc) of this Person
     * @return String The honorific (Dr. Mrs. etc) of this Person
     */
    function getHonorific(){
        return $this->honorific;
    }
    
    /**
     * Returns the correspondence language of this Person (either 'French' or 'English')
     * @return String The correspondence language of this Person (either 'French' or 'English')
     */
    function getCorrespondenceLanguage(){
        return $this->language;
    }
    
    /**
     * Returns an array of UserLanguage objects that this Person knows
     * @return array The UserLanguages that this Person knows
     */
    function getLanguages(){
        $data = DBFunctions::select(array('grand_user_languages'),
                                    array('id'),
                                    array('user_id' => EQ($this->getId())));
        $languages = array();
        foreach($data as $row){
            $language = UserLanguage::newFromId($row['id']);
            $languages[$language->getLanguage()] = $language;
        }
        return $languages;
    }
    
    /**
     * Returns an array of Address objects that this Person is from
     * @return array The Address objects that this Person is from
     */
    function getAddresses(){
        $data = DBFunctions::select(array('grand_user_addresses'),
                                    array('id'),
                                    array('user_id' => EQ($this->getId())));
        $addresses = array();
        foreach($data as $row){
            $address = Address::newFromId($row['id']);
            $addresses[$address->getId()] = $address;
        }
        return $addresses;
    }
    
    /**
     * Returns an array of Telephone objects that this Person has
     * @return array The Telephone objects that this Person has
     */
    function getTelephones(){
        $data = DBFunctions::select(array('grand_user_telephone'),
                                    array('id'),
                                    array('user_id' => EQ($this->getId())));
        $telephones = array();
        foreach($data as $row){
            $phone = Telephone::newFromId($row['id']);
            $telephones[$phone->getId()] = $phone;
        }
        return $telephones;
    }
    
    /**
     * Returns a this Person's name in the form "Last, First"
     * @return string This Person's name in the form "Last, First"
     */
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

    /**
     * Returns a name usable in forms ("First Last" usually)
     * @return string A name usable in forms
     */
    function getNameForForms($sep = ' ') {
        $me = Person::newFromWgUser();
	    if($me->isRoleAtLeast(MANAGER) || $this->getId() == $me->getId() || $this->isRole(MANAGER) || $this->isRole(Expert)){
            $alias = "";        
            if($this->getAlias() != ""){    
                $alias .= " ({$this->getAlias()})";
            }
            if (!empty($this->realname))
                return str_replace("\"", "<span class='noshow'>&quot;</span>", str_replace("&nbsp;", " ", ucfirst($this->realname))).$alias;
            else
                return str_replace("\"", "<span class='noshow'>&quot;</span>", trim($this->getFirstName()." ".$this->getLastName())).$alias;
        }
        else{
            if($this->getAlias() == ""){
                return "Anonymous";
            }
            else{
                return $this->getAlias();
            }
        }
    }

    private function formatName($matches){
        foreach($matches as $key => $match){
            $match1 = $match;
            $match2 = $match;
            $match1 = str_replace("%first", $this->getFirstName(), $match1);
            $match1 = str_replace("%middle", str_replace(".","",$this->getMiddleName()), $match1);
            $match1 = str_replace("%last", $this->getLastName(), $match1);
            $match1 = str_replace("%f", substr($this->getFirstName(), 0,1), $match1);
            $match1 = str_replace("%m", substr($this->getMiddleName(), 0,1), $match1);
            $match1 = str_replace("%l", substr($this->getLastName(),0,1), $match1);

            $match2 = str_replace("%first", "", $match2);
            $match2 = str_replace("%middle", "", $match2);
            $match2 = str_replace("%last", "", $match2);
            $match2 = str_replace("%f", "", $match2);
            $match2 = str_replace("%m", "", $match2);
            $match2 = str_replace("%l", "", $match2);
            if($match1 == $match2){
                 $matches[$key] = "";
            }
            else{
                $matches[$key] = str_replace("}","",str_replace("{","",$match1));
            }
        }
        return implode("",$matches);
    }

    function getNameForProduct(){
        global $config;
        if($this->getId() == 0){
            return $this->getNameForForms();
        }
        $firstname = $this->getFirstName();
        $middlename = $this->getMiddleName();
        $lastname = $this->getLastName();

        $regex = "/\{.*?\}/";
        $format = strtolower($config->getValue("nameFormat"));
        $format = preg_replace_callback($regex,"self::formatName",$format);
        return $format;
    }


    
    // Returns the user's profile.
    // If $private is true, then it grabs the private version, otherwise it gets the public
    /**
     * Returns the text from this Person's profile
     * @param boolean $private If tru, then it grabs the private version, otherwise it gets the public
     * @return string This Person's profile text
     */
    function getProfile($private=false){
        if($private){
            return $this->privateProfile;
        }
        else{
            return $this->publicProfile;
        }
    }
    
    /**
     * Returns the moved on row for when this HQP was inactivated
     * @return array An array of key/value pairs representing the DB row
     */
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
    
    /**
     * Returns all of the moved on rows for when this HQP was inactivated
     * @return array An array of moved on rows
     */
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

    /**
     * Returns the people who moved on between the given dates
     * @param string $startRange The start date
     * @param string $endRange The end date
     * @return array An array of People
     */
    static function getAllMovedOnDuring($startRange, $endRange){
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

    /**
     * Returns when this Person's degree started (NOTE: This is a guesstimate)
     * @return string The date that this Person's degree started
     */
    function getDegreeStartDate($guess = true){
        $data = DBFunctions::select(array('grand_relations'),
                                    array('start_date'),
                                    array('user2' => EQ($this->getId())),
                                    array('start_date' => 'ASC'));
        if(DBFunctions::getNRows() > 0)
          return $data[0]['start_date'];
        return NULL;
    }

    /**
     * Returns when this Person's degree ended (NOTE: This is a guesstimate)
     * @return string The date that this Person's degree started
     */
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

    /**
     * Returns the name of the University that this Person is at
     * @return string The name of the University
     */
    function getUni(){
        $university = $this->getUniversity();
        return (isset($university['university'])) ? $university['university'] : "Unknown";
    }
    
    /**
     * Returns the name of the Department that this Person is at
     * @return string The name of the Department
     */
    function getDepartment(){
        $university = $this->getUniversity();
        return (isset($university['department'])) ? $university['department'] : "Unknown";
    }
    
    /**
     * Returns the name of the Position/Title that this Person is
     * @return string The name of the Postion/Title
     */
    function getPosition(){
        $university = $this->getUniversity();
        return (isset($university['position'])) ? $university['position'] : "Unknown";
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
        $data = $this->getUniversitiesDuring($startRange, $endRange);
        if(isset($data[0])){
            return $data[0];
        }
        return null;
    }
    
    /**
     * Returns all the Universities that this Person was at between the given range
     * @param string $startRange The start date to look at
     * @param string $endRange The end date to look at
     * @return array The Universities that this Person was at between the given range
     */ 
    function getUniversitiesDuring($startRange, $endRange){
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
            $universities = array();
            if(count($data) > 0){
                foreach($data as $row){
                    if($row['university_name'] != "Unknown"){
                        $universities[] = array("university" => $row['university_name'],
                                                "department" => $row['department'],
                                                "position"   => $row['position']);
                    }
                }
            }
            $this->universityDuring[$startRange.$endRange] = $universities;
        }
        return $this->universityDuring[$startRange.$endRange];
    }
    
    /**
     * Returns all the Universities that this Person has been a part of
     * @return array All the Universities that this Person has been a part of
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
    
    /**
     * Returns an array of groups that this Person is in
     * @return array The groups that this Person is in
     */
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
    
    /**
     * Returns an array of rights that this Person has
     * @return array The rights that this Person has
     */
    function getRights(){
        $user = $this->getUser();
        if($user->mRights == null){
            $user->mRights = array();
        }
        //GrandAccess::setupGrandAccess($user, $user->mRights);
        return $user->mRights;
    }
    
    /**
     * Returns one of the roles that this person is
     * @return string One of the roles that this person is
     */
    function getType(){
        $roles = $this->getRoles();
        foreach($roles as $role){
            if(!$role->isAlias()){
                return $role->getRole();
            }
        }
        return null;
    }
    
    /**
     * Returns a string containing the full role information
     * @return string The full role information for this Person
     */
    function getRoleString(){
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn() && !$this->isRoleAtLeast(NI)){
            return "";
        }
        $roles = $this->getRoles();
        $roleNames = array();
        foreach($roles as $role){
            $roleNames[] = $role->getRole();
        }
        if($this->isProjectLeader()){
            $roleNames[] = "PL";
        }
        if($this->isThemeLeader()){
            $roleNames[] = TL;
        }
        if($this->isThemeCoordinator()){
            $roleNames[] = TC;
        }
        foreach($roleNames as $key => $role){
            if($role == INACTIVE){
                if($this->isProjectLeader()){
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
    
    /**
     * Returns the Role object that matches the name of the role
     * @param string $role The name of the role
     * @return Role The role of this Person
     */
    function getRole($role){
        foreach($this->getRoles() as $r){
            if($r->getRole() == $role){
                return $r;
            }
        }
        return new Role(array());
    }
    
    /**
     * Returns all of this Person's Roles
     * @param boolean $history Whether or not to include all the history of roles
     * @param array The Roles this Person has
     */
    function getRoles($history=false){
        if($history !== false && $this->id != null){
            $this->roles = array();
            if($history === true){
                if($this->roleHistory === null){
                    $data = DBFunctions::select(array('grand_roles'),
                                                array('*'),
                                                array('user_id' => $this->id),
                                                array('end_date' => 'DESC'));
                    $this->roleHistory = $data;
                }
                $data = $this->roleHistory;
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
                    $role = new Role(array($row));
                    if($role->id != null){
                        $roles[] = new Role(array($row));
                    }
                }
            }
            return $roles;
        }
        self::generateRolesCache();
        if($this->roles == null && $this->id != null){
            if(isset(self::$rolesCache[$this->id])){
                foreach(self::$rolesCache[$this->id] as $row){
                    $role = new Role(array($row));
                    if($role->id != null){
                        $this->roles[] = $role;
                    }
                }
            }
            else{
                $this->roles[] = new Role(array(0 => array('id' => -1,
                                                           'user_id' => $this->id,
                                                           'role' => INACTIVE,
                                                           'title' => '',
                                                           'start_date' => '0000-00-00 00:00:00',
                                                           'end_date' => '0000-00-00 00:00:00',
                                                           'comment' => '')));
            }
        }
        else if($this->id == null){
            $this->roles = array();
        }
        return $this->roles;
    }
    
    /**
     * Returns the role that this Person is on the given Project
     * @param Project $project The Project to check the roles of
     * @param integer $year The year to check
     * @param boolean $aliases Whether or not to include alias roles in the return
     * @return string The name of the role
     */
    function getRoleOn($project, $year=null, $aliases=false){
        global $config;
        $committees = $config->getValue('committees');
        if($year == null){
            $year = date('Y-m-d H:i:s');
        }
        if($this->isRoleOn(AR, $year, $project) && !$this->leadershipOf($project)){
            return AR;
        }
        else if($this->isRoleOn(CI, $year, $project) && !$this->leadershipOf($project)){
            return CI;
        }
        else if($this->isRoleOn(NP, $year, $project) && !$this->leadershipOf($project)){
            return NP;
        }
        else if($this->isRoleOn(MW, $year, $project) && !$this->leadershipOf($project)){
            return MW;
        }
        else if($this->leadershipOf($project)){
            return PL;
        }
        else if($this->isRoleOn(HQP, $year, $project)){
            return HQP;
        }
        else if($aliases && $this->isRoleOn("FAKENI", $year, $project)){
            return "FAKENI";
        }
        else {
            foreach($this->getRoles() as $role){
                if(!isset($committees[$role->getRole()]) && $this->isRoleOn($role->getRole(), $year, $project)){
                    return $role->getRole();
                }
            }
        }
        return $this->getType();
    }

    /*
     * Returns a list of roles (strings) which this Person is allowed to edit
     * @return array A list of roles (string) which this Person is allowed to edit
     */
    function getAllowedRoles(){
        global $wgRoleValues, $wgRoles;
        $maxValue = 0;
        $roles = array();
        if(is_array($this->getRoles())){
            foreach($this->getRoles() as $role){
                $maxValue = max($maxValue, $wgRoleValues[$role->getRole()]);
            }
            foreach($wgRoleValues as $role => $value){
                if($value <= $maxValue && array_search($role, $wgRoles) !== false){
                    $roles[$role] = $role;
                }
            }
        }
        sort($roles);
        return $roles;
    }
    
    /*
     * Returns a list of projects (strings) which this Person is allowed to edit
     * @returns array A list of projects (strings) which this Person is allowed to edit
     */
    function getAllowedProjects(){
        $projects = array();
        foreach($this->getProjects() as $project){
            if(!$project->isSubProject()){
                $projects[$project->getId()] = $project->getName();
            }
        }
        foreach($this->leadership() as $project){
            if(!$project->isSubProject()){
                $projects[$project->getId()] = $project->getName();
            }
        }
        foreach($this->getThemeProjects() as $project){
            if(!$project->isSubProject()){
                $projects[$project->getId()] = $project->getName();
            }
        }
        if($this->isRoleAtLeast(STAFF)){
            foreach(Project::getAllProjects() as $project){
                if(!$project->isSubProject()){
                    $projects[$project->getId()] = $project->getName();
                }
            }
        }
        asort($projects);
        return array_values($projects);
    }
    
    /**
     * Returns the first role that this Person had
     * @return Role The first role that this Person had, null if this Person has never had any Roles
     */
    function getFirstRole(){
        $roles = $this->getRoles(true);
        if(count($roles) > 0){
            return $roles[0];
        }
        return null;
    }
    
    /**
     * Returns the last role that this Person had
     * @return Role The last role that this Person had, null if this Person has never had any Roles
     */
    function getLastRole(){
        $roles = $this->getRoles(true);
        if(count($roles) > 0){
            return $roles[count($roles)-1];
        }
        return null;
    }
    
    /**
     * Checks whether this Person's last Role was the given role
     * @param string $role The name of the role
     * @return boolean Whether this Person's last Role was the given role
     */
    function wasLastRole($role){
        if($role == NI){
            return ($this->wasLastRole(AR) || 
                    $this->wasLastRole(CI) ||
                    $this->wasLastRole(NP) ||
                    $this->wasLastRole(MW));
        }
        $lastRole = $this->getLastRole();
        if($lastRole != null && $lastRole->getRole() == $role){
            return true;
        }
        return false;
    }
    
    /**
     * Checks whether this Person's last Role was at least the given role
     * @param string $role The name of the role
     * @return boolean Whether this Person's last Role was at least the given role
     */
    function wasLastRoleAtLeast($role){
        global $wgRoleValues;
        if($role == NI){
            return ($this->wasLastRoleAtLeast(AR) || 
                    $this->wasLastRoleAtLeast(CI) ||
                    $this->wasLastRoleAtLeast(NP) ||
                    $this->wasLastRoleAtLeast(MW));
        }
        if($this->getRoles() != null){
            $r = $this->getLastRole();
            if($r != null && $wgRoleValues[$r->getRole()] >= $wgRoleValues[$role]){
                return true;
            }
        }
        return false;
    }
    
    /**
     * Checks whether this Person's last Role was at most the given role
     * @param string $role The name of the role
     * @return boolean Whether this Person's last Role was at most the given role
     */
    function wasLastRoleAtMost($role){
        global $wgRoleValues;
        if($role == NI){
            return ($this->wasLastRoleAtMost(AR) || 
                    $this->wasLastRoleAtMost(CI) ||
                    $this->wasLastRoleAtMost(NP) ||
                    $this->wasLastRoleAtMost(MW));
        }
        if($this->getRoles() != null){
            $r = $this->getLastRole();
            if($r != null && $wgRoleValues[$r->getRole()] <= $wgRoleValues[$role]){
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the Roles this Person had between the given dates
     * @param string $startRange The start date
     * @param string $endRange The end date
     * @return array The Roles this Person had between the given dates
     */
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
    
    /**
     * Returns the Roles this Person had on the given date
     * @param string $date The date to check
     * @return array The Roles this Person had on the given date
     */
    function getRolesOn($date){
        if($this->id == 0){
            return array();
        }
        $cacheId = "personRolesDuring".$this->id."_".$date;
        if(Cache::exists($cacheId)){
            $data = Cache::fetch($cacheId);
        }
        else{
            $sql = "SELECT *
                    FROM grand_roles
                    WHERE user_id = '{$this->id}'
                    AND (('$date' BETWEEN start_date AND end_date) OR (start_date <= '$date' AND end_date = '0000-00-00 00:00:00'))";
            $data = DBFunctions::execSQL($sql);
            Cache::store($cacheId, $data);
        }
        $roles = array();
        foreach($data as $row){
            $roles[] = new Role(array(0 => $row));
        }
        return $roles;        
    }
    
    /**
     * Returns an array of the subRoles that this Person is in
     * @return array The subRoles that this Person is in
     */
    function getSubRoles(){
        $roles = array();
        $data = DBFunctions::select(array('grand_role_subtype'),
                                    array('sub_role'),
                                    array('user_id' => EQ($this->getId())));
        foreach($data as $row){
            $roles[] = $row['sub_role'];
        }
        return $roles;
    }
    
    /**
     * Returns whether or not this Person is in the subRole or not
     * @param string $subRole The subrole to check
     * @return boolean Whether or not this Person is in the subRole or not
     */
    function isSubRole($subRole){
        $roles = $this->getSubRoles();
        return (array_search($subRole, $roles) !== false);
    }

    
    /**
     * Returns all of the Projects that this Person has been a member of
     * @param boolean $groupBySubs Whether or not to group by sub-projects
     * @return array The Projects that this Person has been a member of
     */
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

    /**
     * Returns an array of Projects that this Person is a part of
     * @param boolean $history Whether or not to include the full history
     * @param boolean $allowProposed Whether or not to include proposed projects
     * @return array The Projects that this Person is a part of
     */
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

    /**
     * Returns an array of Projects that this Person is a part of between the given dates
     * @param string $start The start date
     * @param string $end The end date
     * @param boolean $allowProposed Whether or not to include proposed Projects
     * @return array The Projects that this Person is a part of
     */
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
    
    /**
     * Returns the number of months an HQP has been a part of a project
     * TODO: This need to be updated! (it was used for 2010)
     * @param Project $project The Project the HQP was on
     * @return string The number of months
     */
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
    
    /**
     * Returns the Relationships this Person has between the given dates
     * @param string $type The type of Relationship
     * @param string $startRange The start date
     * @param string $endRange The end date
     * @return array The Relationships this Person has
     */
    function getRelationsDuring($type='all', $startRange, $endRange){
        $type = DBFunctions::escape($type);
        $startRange = DBFunctions::escape($startRange);
        $endRange = DBFunctions::escape($endRange);
        $sql = "SELECT *
                FROM grand_relations
                WHERE user1 = '{$this->id}'
                AND user1 != user2 \n";
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
    
    /**
     * Returns the Relationships this Person has
     * @param string $type The type of Relationship
     * @param boolean $history Whether or not to include the full history of Relationships
     * @return array The Relationships this Person has
     */
    function getRelations($type='all', $history=false){
        if($type == "all"){
            $sql = "SELECT id, type
                    FROM grand_relations, mw_user u1, mw_user u2
                    WHERE user1 = '{$this->id}'
                    AND user1 != user2
                    AND u1.user_id = user1
                    AND u2.user_id = user2
                    AND u1.deleted != '1'
                    AND u2.deleted != '1'";
            if(!$history){
                $sql .= "AND start_date >= end_date";
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
                    AND user1 != user2
                    AND u1.user_id = user1
                    AND u2.user_id = user2
                    AND u1.deleted != '1'
                    AND u2.deleted != '1'
                    AND type <> '".WORKS_WITH."'";
            if(!$history){
                $sql .= "AND start_date >= end_date";
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
                    AND user1 != user2
                    AND u1.user_id = user1
                    AND u2.user_id = user2
                    AND u1.deleted != '1'
                    AND u2.deleted != '1'
                    AND type = '{$type}'";
            if(!$history){
                $sql .= "AND start_date >= end_date";
            }
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                $this->relations[$row['type']][$row['id']] = Relationship::newFromId($row['id']);
            }
        //}
        return $this->relations[$type];
    }

    /**
     * Returns this Person's Contributions
     * @return array This Person's Contributions
     */
    function getContributions(){
        if($this->contributions == null){
            $this->contributions = array();
            $sql = "SELECT id
                    FROM(SELECT id, name, rev_id
                    FROM grand_contributions
                    WHERE users LIKE '%\"{$this->id}\"%'
                    AND (access_id = '{$this->id}' OR access_id = '0')
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
    
    /**
     * Returns the Contributions this Person has made during the given year
     * @param string $year The year of the Contribution
     * @return array The Contribution this Person has made
     */
    function getContributionsDuring($year){
        $contribs = array();
        foreach($this->getContributions() as $contrib){
            if($contrib->getStartYear() <= $year && $contrib->getEndYear() >= $year){
                $contribs[] = $contrib;
            }
        }
        return $contribs;
    }
    
    /**
     * Returns the Multimedia this Person has made
     * @return array the Multimedia this Person has made
     */
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
    
    function isCandidate(){
        return $this->candidate;
    }
    
    function isConfidential(){
        return $this->confidential;
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
    
    /**
     * Returns whether or not this person is a Student
     * @return boolean Whether or not his person is a Student
     */
    function isStudent(){
        if($this->isRole(HQP)){
            $uni = $this->getUniversity();
            if(strtolower($uni['position']) == "graduate student - master's" ||
               strtolower($uni['position']) == "graduate student - doctoral" ||
               strtolower($uni['position']) == "post-doctoral fellow" ||
               strtolower($uni['position']) == 'undergraduate' ||
               strtolower($uni['position']) == 'summer student'){
                return true;
            }
        }
        return false;
    }

    /**
     * Returns whether this Person is the same as $wgUser
     * @return boolean Whether this Person is the same as $wgUser
     */
    function isMe(){
        global $wgUser;
        return ($wgUser->getId() == $this->getId());
    }

    /**
     * Returns whether this Person is the given role (on the given optional project)
     * @param string $role The role of this Person
     * @param Project $project The Project the Person is a role on
     * @return boolean Whether or not the Person is the given role
     */
    function isRole($role, $project=null){
        if($role == NI){
            return ($this->isRole(AR, $project) || 
                    $this->isRole(CI, $project) ||
                    $this->isRole(NP, $project) ||
                    $this->isRole(MW, $project));
        }
        if($role == NI.'-Candidate'){
            return ($this->isRole(AR.'-Candidate', $project) || 
                    $this->isRole(CI.'-Candidate', $project) ||
                    $this->isRole(NP.'-Candidate', $project) ||
                    $this->isRole(MW.'-Candidate', $project));
        }
        if($role == PL || $role == 'PL'){
            return $this->isProjectLeader();
        }
        if($role == APL){
            $leadership = $this->leadership(false, true, 'Administrative');
            if(count($leadership) > 0){
                return true;
            }
            return false;
        }
        if($role == TL || $role == 'TL'){
            return $this->isThemeLeader();
        }
        if($role == TC || $role == 'TC'){
            return $this->isThemeCoordinator();
        }
        if($role == EVALUATOR){
            return $this->isEvaluator();
        }
        $roles = array();
        $role_objs = $this->getRoles();
        if(!empty($role_objs)){
            foreach($role_objs as $r){
                $skip = false;
                if($project != null && count($r->getProjects()) > 0){
                    $skip = true;
                    foreach($r->getProjects() as $p){
                        if($p->getId() == $project->getId()){
                            $skip = false;
                            break;
                        }
                    }
                }
                if(!$skip){
                    $roles[] = $r->getRole();
                }
            }
        }
        else{
            return false;
        }
        if($this->isCandidate()){
            foreach($roles as $key => $r){
                $roles[$key] = $r."-Candidate";
            }    
        }
        return (array_search($role, $roles) !== false);
    }
    
    /**
     * Returns whether this Person is the given role on the given date (on the given optional project)
     * @param string $role The role of this Person
     * @param string $data The date of the role
     * @param Project $project The Project the Person is a role on
     * @return boolean Whether or not the Person is the given role
     */
    function isRoleOn($role, $date, $project=null){
        if($role == NI){
            return ($this->isRoleOn(AR, $date, $project) || 
                    $this->isRoleOn(CI, $date, $project) ||
                    $this->isRoleOn(NP, $date, $project) ||
                    $this->isRoleOn(MW, $date, $project));
        }
        if($role == NI.'-Candidate'){
            return ($this->isRoleOn(AR.'-Candidate', $date, $project) || 
                    $this->isRoleOn(CI.'-Candidate', $date, $project) ||
                    $this->isRoleOn(NP.'-Candidate', $date, $project) ||
                    $this->isRoleOn(MW.'-Candidate', $date, $project));
        }
        $roles = array();
        $role_objs = $this->getRolesOn($date);
        if($role == PL || $role == "PL"){
            $project_objs = $this->leadershipOn($date);
            if(count($project_objs) > 0){
                $roles[] = "PL";
            }
        }
        if(count($role_objs) > 0){
            foreach($role_objs as $r){
                $skip = false;
                if($project != null && count($r->getProjects()) > 0){
                    $skip = true;
                    foreach($r->getProjects() as $p){
                        if($p->getId() == $project->getId()){
                            $skip = false;
                            break;
                        }
                    }
                }
                if(!$skip){
                    $roles[] = $r->getRole();
                }
            }
        }
        if($role == EVALUATOR && $this->isEvaluator()){
            $roles[] = EVALUATOR;
        }
        if(count($roles) == 0){
            return false;
        }
        if($this->isCandidate()){
            foreach($roles as $key => $r){
                $roles[$key] = $r."-Candidate";
            }    
        }
        return (array_search($role, $roles) !== false);
    }
    
    /**
     * Returns whether this Person is the given role between the given dates (on the given optional project)
     * @param string $role The role of this Person
     * @param string $startRange The start date
     * @param string $endRange The end date
     * @param Project $project The Project the Person is a role on
     * @return boolean Whether or not the Person is the given role
     */
    function isRoleDuring($role, $startRange, $endRange, $project=null){
        if($role == NI){
            return ($this->isRoleDuring(AR, $startRange, $endRange, $project) || 
                    $this->isRoleDuring(CI, $startRange, $endRange, $project) ||
                    $this->isRoleDuring(NP, $startRange, $endRange, $project) ||
                    $this->isRoleDuring(MW, $startRange, $endRange, $project));
        }
        if($role == NI.'-Candidate'){
            return ($this->isRoleDuring(AR.'-Candidate', $startRange, $endRange, $project) || 
                    $this->isRoleDuring(CI.'-Candidate', $startRange, $endRange, $project) ||
                    $this->isRoleDuring(NP.'-Candidate', $startRange, $endRange, $project) ||
                    $this->isRoleDuring(MW.'-Candidate', $startRange, $endRange, $project));
        }
        $roles = array();
        $role_objs = $this->getRolesDuring($startRange, $endRange);
        if($role == PL || $role == "PL"){
            $project_objs = $this->leadershipDuring($startRange, $endRange);
            if(count($project_objs) > 0){
                $roles[] = "PL";
            }
        }
        if(count($role_objs) > 0){
            foreach($role_objs as $r){
                $skip = false;
                if($project != null && count($r->getProjects()) > 0){
                    // Projects are explicitely specified
                    $skip = true;
                    foreach($r->getProjects() as $p){
                        if($p->getId() == $project->getId()){
                            $skip = false;
                            break;
                        }
                    }
                }
                else if($project != null && count($r->getProjects()) == 0){
                    // Projects are not explicitely specified
                    $skip = true;
                    foreach($this->getProjectsDuring($startRange, $endRange) as $p){
                        if($p->getId() == $project->getId()){
                            $skip = false;
                            break;
                        }
                    }
                }
                if(!$skip){
                    $roles[] = $r->getRole();
                }
            }
        }
        if($role == EVALUATOR && $this->isEvaluator()){
            $roles[] = EVALUATOR;
        }
        if(count($roles) == 0){
            return false;
        }
        if($this->isCandidate()){
            foreach($roles as $key => $r){
                $roles[$key] = $r."-Candidate";
            }    
        }
        return (array_search($role, $roles) !== false);
    }
    
    /**
     * Returns whether this Person was at least the given role between the given dates
     * @param string $role The role of this Person
     * @param string $startRange The start date
     * @param string $endRange The end date
     * @return boolean Whether or not the Person is the given role
     */
    function isRoleAtLeastDuring($role, $startRange, $endRange){
        global $wgRoleValues;
        if($role == NI){
            return ($this->isRoleAtLeastDuring(AR, $startRange, $endRange) || 
                    $this->isRoleAtLeastDuring(CI, $startRange, $endRange) ||
                    $this->isRoleAtLeastDuring(NP, $startRange, $endRange) ||
                    $this->isRoleAtLeastDuring(MW, $startRange, $endRange));
        }
        if($role == NI.'-Candidate'){
            return ($this->isRoleAtLeastDuring(AR.'-Candidate', $startRange, $endRange) || 
                    $this->isRoleAtLeastDuring(CI.'-Candidate', $startRange, $endRange) ||
                    $this->isRoleAtLeastDuring(NP.'-Candidate', $startRange, $endRange) ||
                    $this->isRoleAtLeastDuring(MW.'-Candidate', $startRange, $endRange));
        }
        if($this->isCandidate()){
            return false;
        }
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
        return false;
    }
    
    /**
     * Returns whether this Person is at least the given role
     * @param string $role The role of this Person
     * @return boolean Whether or not the Person is the given role
     */
    function isRoleAtLeast($role){
        global $wgRoleValues;
        if($role == NI){
            return ($this->isRoleAtLeast(AR) || 
                    $this->isRoleAtLeast(CI) ||
                    $this->isRoleAtLeast(NP) ||
                    $this->isROleAtLeast(MW));
        }
        if($role == NI.'-Candidate'){
            return ($this->isRoleAtLeast(AR.'-Candidate') || 
                    $this->isRoleAtLeast(CI.'-Candidate') ||
                    $this->isRoleAtLeast(NP.'-Candidate') ||
                    $this->isRoleAtLeast(MW.'-Candidate'));
        }
        $me = Person::newFromWgUser();
        if($this->isCandidate()){
            return false;
        }
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
        if($wgRoleValues[TL] >= $wgRoleValues[$role]){
            if($this->isThemeLeader()){
                return true;
            }
        }
        if($wgRoleValues[TC] >= $wgRoleValues[$role]){
            if($this->isThemeCoordinator()){
                return true;
            }
        }
        return false;
    }
    
    /**
     * Returns whether this Person is at most the given role
     * @param string $role The role of this Person
     * @return boolean Whether or not the Person is the given role
     */
    function isRoleAtMost($role){
        global $wgRoleValues;
        if($role == NI){
            return ($this->isRoleAtMost(AR) || 
                    $this->isRoleAtMost(CI) ||
                    $this->isRoleAtMost(NP) ||
                    $this->isRoleAtMost(MW));
        }
        if($role == NI.'-Candidate'){
            return ($this->isRoleAtMost(AR.'-Candidate') || 
                    $this->isRoleAtMost(CI.'-Candidate') ||
                    $this->isRoleAtMost(NP.'-Candidate') ||
                    $this->isRoleAtMost(MW.'-Candidate'));
        }
        if($this->isCandidate()){
            return true;
        }
        foreach($this->getRoles() as $r){
            if($r->getRole() != "" && $wgRoleValues[$r->getRole()] > $wgRoleValues[$role]){
                return false;
            }
        }
        if($wgRoleValues[PL] > $wgRoleValues[$role]){
            if($this->isProjectLeader()){
                return false;
            }
        }
        return true;
    }
    
    /**
     * Returns the People who requested this Person, or an empty array if no one Requested
     * @return array The People who requested this Person
     */
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
    
    /**
     * Returns the People that this Person has requested to be created
     * @return array The People that this Person has requested to be created
     */
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

    /**
     * Returns this Person's HQP
     * @param mixed $history Whether or not to include all HQP in history (can also be a specific date)
     * @return array This Person's HQP
     */
    function getHQP($history=false){
        if($history !== false && $this->id != null){
            $this->roles = array();
            if($history === true){
                if($this->historyHqps !== null){
                    return $this->historyHqps;
                }
                $sql = "SELECT *
                        FROM grand_relations
                        WHERE user1 = '{$this->id}'
                        AND user1 != user2
                        AND type = 'Supervises'";
            }
            else{
                $sql = "SELECT *
                        FROM grand_relations
                        WHERE user1 = '{$this->id}'
                        AND user1 != user2
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
                AND user1 != user2
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
    
    /**
     * Returns this Person's Champions between the given dates (based on the Works With relation)
     * @param string $startRange The start date
     * @param string $endRange The end date
     * @return array This Person's Champions
     */
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
    
    /**
     * Returns this Person's HQP during the given dates
     * @param string $startRange The start date
     * @param string $endRange The end date
     * @return array This Person's HQP
     */
    function getHQPDuring($startRange, $endRange){
        if(isset($this->hqpCache[$startRange.$endRange])){
            return $this->hqpCache[$startRange.$endRange];
        }
        $sql = "SELECT *
                FROM grand_relations
                WHERE user1 = '{$this->id}'
                AND user1 != user2
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
    
    /**
     * Returns this Person's Supervisors
     * @param mixed $history Whether or not to include all Supervisors in history (can also be a specific date)
     * @return array This Person's Supervisors
     */
    function getSupervisors($history=false){
        if($history !== false && $this->id != null){
            $this->roles = array();
            if($history === true){
                $sql = "SELECT *
                        FROM grand_relations
                        WHERE user2 = '{$this->id}'
                        AND user1 != user2
                        AND type = 'Supervises'";
            }
            else{
                $sql = "SELECT *
                        FROM grand_relations
                        WHERE user2 = '{$this->id}'
                        AND user1 != user2
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
                AND user1 != user2
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
    
    /**
     * Returns this Person's Supervisors between the given dates
     * @param string $startRange The start date
     * @param string $endRange The end date
     * @return array This Person's Supervisors
     */
    function getSupervisorsDuring($startRange, $endRange){
        $sql = "SELECT *
                FROM grand_relations
                WHERE user2 = '{$this->id}'
                AND user1 != user2
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

    /**
     * Returns whether this Person is a supervisor
     * @param mixed $history Whether or not to include the full history (can also be a specific date)
     * @return boolean Whether this Person is a supervisor
     */
    function isSupervisor($history=false){
        if($history !== false && $this->id != null){
            $this->roles = array();
            if($history === true){
                $sql = "SELECT *
                        FROM grand_relations
                        WHERE user1 = '{$this->id}'
                        AND user1 != user2
                        AND type = 'Supervises'";
            }
            else{
                $sql = "SELECT *
                        FROM grand_relations
                        WHERE user1 = '{$this->id}'
                        AND user1 != user2
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
                AND user1 != user2
                AND type = 'Supervises'
                AND start_date > end_date";
        $data = DBFunctions::execSQL($sql);
        return count($data);
    }

    /**
     * Returns whether or not this person is related to another Person through a given relationship
     * @param Person $person The Person that this Person is related to
     * @param string $relationship The type of Relationship
     * @return boolean Whether or not this Person is related to another Person
     */
    function relatedTo($person, $relationship){
        if( $person instanceof Person ){
            $person_id = $person->getId();
            $data = DBFunctions::select(array('grand_relations'),
                                        array('*'),
                                        array('user1' => EQ($this->getId()),
                                              'user2' => EQ($person->getId()),
                                              'type' => EQ($relationship),
                                              'start_date' => GT(COL('end_date'))));
            return (count($data) > 0);   
        }
        else{
            return null;
        }
    }
    
    /**
     * Returns and array of Person objects who this Person can delegate
     * @return array The list of People who this Person can delegate
     */
    function getDelegates(){
        $data = DBFunctions::select(array('grand_delegate'),
                                    array('user_id'),
                                    array('delegate' => EQ($this->getId())));
        $people = array();
        foreach($data as $row){
            $people[] = Person::newFromId($row['user_id']);   
        }
        return $people;
    }
    
    /**
     * Returns whether or not this Person is a delegate for the given Person
     * @param Person
     * @return boolean Whether or not this Person is a delegate for the given Person
     */
    function isDelegateFor($person){
        foreach($this->getDelegates() as $delegate){
            if($delegate->getId() == $person->getId()){
                return true;
            }
        }
        return false;
    }
    
    /**
     * Returns an array of Paper(s) authored or co-authored by this Person _or_ their HQP
     * @param string $category The category of Paper to get
     * @param boolean $history Whether or not to include past publications (ie. written by past HQP)
     * @param string $grand Whether to include 'grand' 'nonGrand' or 'both' Papers
     * @param boolean $onlyPublic Whether or not to only include Papers with access_id = 0
     * @param string $access Whether to include 'Forum' or 'Public' access
     * @return array Returns an array of Paper(s) authored or co-authored by this Person _or_ their HQP
     */ 
    function getPapers($category="all", $history=false, $grand='grand', $onlyPublic=true, $access='Forum'){
        $me = Person::newFromWgUser();
        self::generateAuthorshipCache();
        $processed = array();
        $papersArray = array();
        $papers = array();
        foreach($this->getHQP($history) as $hqp){
            $ps = $hqp->getPapers($category, $history, $grand, $onlyPublic, $access);
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
            $allPapers = Paper::getAllPapers('all', $category, $grand, $onlyPublic, $access);
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
            if(($paper->getAccess() == $access || ($paper->getAccess() == 'Forum' && $me->isLoggedIn())) &&
               !$paper->deleted && 
               ($category == 'all' || $paper->getCategory() == $category)){
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
     * @param boolean $networkRelated Whether or not the products need to be associated with a project
     * @return array Returns an array of Paper(s) authored/co-authored by this Person during the specified dates
     */
    function getPapersAuthored($category="all", $startRange = CYCLE_START, $endRange = CYCLE_START_ACTUAL, $includeHQP=false, $networkRelated=true){
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
               (!$networkRelated || $paper->isGrandRelated()) &&
               (strcmp($date, $startRange) >= 0 && strcmp($date, $endRange) <= 0 )){
                $papersArray[] = $paper;
            }
        }
        return $papersArray;
    }

    /**
     * Returns when this Person's top products were last updated
     * @return string When this Person's to products were last updated
     */
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
    
    /**
     * Returns the list of this Person's top products
     * @return array This Person's top products
     */
    function getTopProducts(){
        $products = array();
        $data = DBFunctions::select(array('grand_top_products'),
                                    array('product_type','product_id'),
                                    array('type' => EQ('PERSON'),
                                          'obj_id' => EQ($this->getId())));
        foreach($data as $row){
            if($row['product_type'] == "CONTRIBUTION"){
                $product = Contribution::newFromId($row['product_id']);
                $year = $product->getStartYear();
            }
            else{
                $product = Product::newFromId($row['product_id']);
            }
            if($product->getTitle() == ""){
                continue;
            }
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
    
    /**
     * Returns an array of People who are authors of Products writted by this Person or their HQP
     * @param string $category The category of Papers to get
     * @param boolean $history Whether or not to include past publications (ie. written by past HQP)
     * @param string $grand Whether to include 'grand' 'nonGrand' or 'both' Papers
     * @param boolean $onlyPublic Whether or not to only include Papers with access_id = 0
     * @param string $access Whether to include 'Forum' or 'Public' access
     * @return array Returns an array of People who are authors of Products writted by this Person or their HQP
     */
    function getCoAuthors($category="all", $history=false, $grand='grand', $onlyPublic=true, $access='Forum'){
        $coauthors = array();
        $papers = $this->getPapers($category, $history, $grand, $onlyPublic, $access);
        foreach($papers as $paper){
            $authors = $paper->getAuthors();
            foreach($authors as $author){
                if(!isset($coauthors[$author->getName()])){
                    $coauthors[$author->getName()] = 0;
                }
                $coauthors[$author->getName()] += 1;
            }
        }
        return $coauthors;
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

    function getUserStories(){
        global $wgUser;
        $me = Person::newFromWgUser();
        $stories = array();
        if($me->isRoleAtLeast(MANAGER)){
            $data = DBFunctions::select(array('grand_user_stories'),
			                            array('rev_id'));
        }
        else{	
            $data = DBFunctions::select(array('grand_user_stories'),
                                        array('rev_id'),
                                        array('user_id' => EQ(COL($this->getId()))));
        }
        foreach($data as $row){
            $stories[] = Story::newFromRevId($row['rev_id']);
        }
        return $stories;
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
    
    /**
     * Returns an array of Projects that this Person is a leader or co-leader of
     * @param boolean $history Whether or not to include the entire leadership history
     * @param boolean $idsOnly Whether or not to just return the ids of the Projects
     * @param string $type The type of Project (ie. 'Administrative', 'Research')
     * @return array The array of Projects
     */
    function leadership($history=false, $idsOnly=false, $type='') {
        $ret = array();
        $res = array();
        if(!$history){
            self::generateLeaderCache();
            if(isset(self::$leaderCache[$this->getId()])){
                $res = self::$leaderCache[$this->getId()];
            }
        }
        else{
            if(isset($this->leadershipCache['history'])){
                return $this->leadershipCache['history'];
            }
            $res = DBFunctions::execSQL("SELECT id
                                         FROM grand_project_leaders l, grand_project p
                                         WHERE l.project_id = p.id
                                         AND l.user_id = '{$this->id}'");
        }
        foreach ($res as &$row) {
            if($idsOnly){
                if($type == '' || !isset($row['type']) || $type == $row['type']){
                    $ret[] = $row['id'];
                }
            }
            else{
                if($type == '' || !isset($row['type']) || $type == $row['type']){
                    $project = Project::newFromId($row['id']);
                    if($project != null && $project->getName() != "" && !$project->isDeleted()){
                        $ret[] = $project;
                    }
                }
            }
        }
        if($history){
            $this->leadershipCache['history'] = $ret;
        }
        return $ret;
    }
    
    /**
     * Returns an array of Projects that this Person is a leader of between the given dates
     * @param string $startRange The start date
     * @param string $endRange The end date
     * @return The Projects that this Person is a leader of
     */
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
    
    /**
     * Returns an array of Projects that this Person is a leader of on the given date
     * @param string $date The date this Person was a leader of
     * @return The Projects that this Person is a leader of
     */
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
    
    /**
     * Returns whether or not this Person is a leader of a given Project
     * @param mixed $project The Project object (or name)
     * @param string $type The type of leadership (depricated)
     * @return boolean Whether or not this Person is a leader of a given Project
     */
    function leadershipOf($project, $type=null) {
        if($project instanceof Project ||
           $project instanceof Theme){
            $p = $project;
        }
        else{
            $p = Project::newFromHistoricName($project);
        }
        if($p == null || $p->getName() == ""){
            return false;
        }
        $extra = "";
        if($type != null){
            $extra = "AND l.type = '$type'";
        }
        $data = DBFunctions::execSQL("SELECT 1
                                     FROM grand_project_leaders l, grand_project p 
                                     WHERE l.project_id = p.id
                                     AND l.user_id = '{$this->id}'
                                     AND p.name = '{$p->getName()}'
                                     AND (l.end_date = '0000-00-00 00:00:00'
                                          OR l.end_date > CURRENT_TIMESTAMP)
                                     $extra");
       
        if(DBFunctions::getNRows() > 0){
            return true;
        }
        if($p instanceof Project && !$p->clear){
            foreach($p->getPreds() as $pred){
                if($this->leadershipOf($pred, $type)){
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Returns whether or not this Person is a leader of at least one Project
     * @return boolean Whether or not this Person is a leader
     */
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
    
    /**
     * Returns whether or not this Person was a leader between the given dates
     * @param string $startRange The start date
     * @param string $endRange The end date
     * @return boolean Whether or not this Person was a leader
     */
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
    
    /**
     * Returns the Themes that this Person is a leader of
     * @return array The Themes that this Person is a leader of
     */
    function getLeadThemes(){
        $themes = array();
        self::generateThemeLeaderCache();
        if(isset(self::$themeLeaderCache[TL][$this->getId()])){
            $data = self::$themeLeaderCache[TL][$this->getId()];
            foreach($data as $row){
                $themes[$row['theme']] = Theme::newFromId($row['theme']);
            }
        }
        return $themes;
    }
    
    /**
     * Returns the Themes that this Person is a coordinator of
     * @return array The Themes that this Person is a coordinator of
     */
    function getCoordThemes(){
        $themes = array();
        self::generateThemeLeaderCache();
        if(isset(self::$themeLeaderCache[TC][$this->getId()])){
            $data = self::$themeLeaderCache[TC][$this->getId()];
            foreach($data as $row){
                $themes[$row['theme']] = Theme::newFromId($row['theme']);
            }
        }
        return $themes;
    }
    
    /**
     * Returns an array of Projects that this Person is a Theme Leader of
     * @return array The Projects that this Person is a Theme Leader of
     */
    function getThemeProjects(){
        $projects = array();
        $themes = array_merge($this->getLeadThemes(), $this->getCoordThemes());
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
                $projects[$project->getName()] = $project;
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
        return $this->getRequestedBudget($year, 'RES_ALLOC_BUDGET');
    }
    
    /**
     * Returns the requested Budget for this Person for the given year
     * @param int $year The reporting year that the budget was requested
     * @param int $type Can be either RES_BUDGET or RES_ALLOC_BUDGET
     * @return Budget The requested Budget for this Person for the given year
     */
    function getRequestedBudget($year, $type='RES_BUDGET'){
        global $wgServer,$wgScriptPath, $reporteeId;
        if($type == 'RES_BUDGET'){
            $index = 'r'.$year;
        }
        else{
            $index = 's'.$year;
        }
        $uid = $this->id;
       
        $blob_type=BLOB_EXCEL;
        $rptype = 'RP_RESEARCHER';
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
            if($year != 2010 && $type == 'RES_BUDGET'){
                $budget = new Budget("XLS", REPORT2_STRUCTURE, $data);
            }
            else if($year == 2010 && $type == 'RES_BUDGET'){
                $budget = new Budget("CSV", REPORT_STRUCTURE, $data);
            }
            else {
                if($type == 'RES_ALLOC_BUDGET' && $this->isRoleDuring(NI, $year.CYCLE_START_MONTH, $year.CYCLE_END_MONTH)){
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
    
    /**
     * Returns the CCV XML that belongs to this Person
     * @return string The CCV XML that belongs to this Person
     */
    function getCCV(){
        $data = DBFunctions::select(array('grand_ccv'),
                                    array('ccv'),
                                    array('user_id' => $this->getId()));
        if(count($data) > 0){
            return $data[0]['ccv'];
        }
        return "";
    }
    
    /**
     * Returns whether or not this Person is an evaluator on the given Year
     * @param string $year The year this Person was an evaluator
     * @return boolean Whether or not this Person is an evaluator
     */
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
    
    /**
     * Returns the list of evaluation assignments for this Person
     * @param string $year The year for the assignments
     * @return array The evaluation assignments for this Person
     */
    function getEvaluateSubs($year = YEAR){
        $sql = "SELECT *
                FROM grand_eval
                WHERE user_id = '{$this->id}'
                AND year = '{$year}'";
        $data = DBFunctions::execSQL($sql);
        $subs = array();
        foreach($data as $row){
            if($row['type'] == "Project" || $row['type'] == "SAB"){
                $subs[] = Project::newFromId($row['sub_id']);
            }
            else if($row['type'] == "Researcher" || $row['type'] == "NI"){
                $subs[] = Person::newFromId($row['sub_id']);
            }
        }
        $this->evaluateCache[$year] = $subs;
        return $subs;
    }
    
    /**
     * Returns all of the evaluation assignments
     * @param string $type The type of assignment
     * @param string $year The year for the assignments
     * @return array The evaluation assignments
     */
    static function getAllEvaluates($type, $year = YEAR, $class = "Person"){
        $type = DBFunctions::escape($type);
        
        $sql = "SELECT DISTINCT sub_id 
                FROM grand_eval
                WHERE type = '$type'
                AND year = '{$year}'";
        $data = DBFunctions::execSQL($sql);
        $subs = array();
        foreach($data as $row){
            if($type != "Project" && 
               $type != "SAB" && $class != "Project"){
                $subs[] = Person::newFromId($row['sub_id']);
            }
            else{
                $subs[] = Project::newFromId($row['sub_id']);
            }
        }
        return $subs;
    }

    /**
     * Returns all of the evaluation assignments for this Person
     * @param string $type The type of assignment
     * @param string $year The year for the assignments
     * @param string $class The class of the evaluatee
     * @return array The evaluation assignments for this Person
     */
    function getEvaluates($type, $year = YEAR, $class = "Person"){
        $type = DBFunctions::escape($type);
        
        $sql = "SELECT *
                FROM grand_eval
                WHERE user_id = '{$this->id}'
                AND type = '$type'
                AND year = '{$year}'";
        $data = DBFunctions::execSQL($sql);
        $subs = array();

        foreach($data as $row){
            if($row['type'] == "Project" || $row['type'] == "SAB" || $class == "Project"){
                $project = Project::newFromId($row['sub_id']);
                $subs[$project->getName()] = $project;
            }
            else{
                $person = Person::newFromId($row['sub_id']);
                $subs[$person->getReversedName()] = $person;
            }
        }
        ksort($subs);
        $subs = array_values($subs);
        return $subs;
    }
    
    function isEvaluatorOf($object, $type, $year = YEAR, $class = "Person"){
        $evals = $this->getEvaluates($type, $year, $class);
        foreach($evals as $eval){
            if($eval == $object){
                return true;
            }
        }
        return false;
    }

    /**
     * Returns a list of the evaluators who are evaluating this Person
     * @param string $year The year of the evaluation
     * @param string $type The type of evaluation
     * @return array The list of People who are evaluating this Person
     */
    function getEvaluators($year = YEAR, $type='Researcher'){
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
    
    /**
     * Returns whether or not this Person received the given Contribution
     * @param Contribution $contribution The Contribution
     * @return boolean Whether or not this Person received the given Contribution
     */
    function isReceiverOf($contribution){
        if($contribution instanceof Contribution){
            $con_people = $contribution->getPeople();
            
            $con_receiver = false;
            if(is_array($con_people)){
                foreach($con_people as $con_pers){
                    if($con_pers instanceof Person){
                        $con_pers = $con_pers->getId();
                        if ( $con_pers == $this->id ){
                            $con_receiver =  true;
                            break;
                        }
                    }
                }
            }
            return $con_receiver;
        }
        else{
            return false;
        }
    }
    
    /**
     * Returns whether or not this Person has the specified reporting ticket
     * @param mised $project The report's Project (can also be an id or name)
     * @param string $year The year of the report
     * @param string $reportType The type of report
     * @param string $ticket The ticket string
     * @return boolean Whether or not this Person has the specified reporting ticket
     */
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
  
    function getCapsPersonalInformation(){
	$caps_info = array();
        $data = DBFunctions::select(array('grand_personal_caps_info'),
                                    array('*'),
                                    array('user_id' => $this->id));
	if(count($data)>0){
	    $this->postal_code = $data[0]['postal_code'];
	    $this->city = $data[0]['city'];
	    $this->province = $data[0]['province'];
	    $this->specialty = $data[0]['specialty'];
	    $this->years_in_practice = $data[0]['years_in_practice'];
	    $this->prior_abortion_service = $data[0]['prior_abortion_service'];
	    $this->accept_referrals = $data[0]['accept_referrals'];
	    $caps_info = $data[0];
	}
	return $caps_info;

    }
}
?>
