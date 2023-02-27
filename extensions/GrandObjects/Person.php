<?php

/**
 * @package GrandObjects
 */
 
/* Faculty map isn't required but can be used to map Department -> Faculty if needed
 *
 * should set a variable $facultyMap that equals:
 * $facultyMap = array('faculty' => array('dept1', 'dept2', 'dept3'));
 */

@include_once("facultyMap.php");

class Person extends BackboneModel {

    static $cache = array();
    static $rolesCache = array();
    static $subRolesCache = array();
    static $universityCache = array();
    static $leaderCache = array();
    static $themeLeaderCache = array();
    static $aliasCache = array();
    static $authorshipCache = array();
    static $namesCache = array();
    static $idsCache = array();
    static $allPeopleCache = array();
    static $facultyMap = array();

    var $user = null;
    var $name;
    var $employeeId;
    var $email;
    var $phone;
    var $nationality;
    var $stakeholder;
    var $earlyCareerResearcher;
    var $agencies;
    var $mitacs;
    var $canadaResearchChair;
    var $gender;
    var $pronouns;
    var $photo;
    var $twitter;
    var $website;
    var $linkedin;
    var $googleScholar;
    var $orcid;
    var $scopus;
    var $researcherId;
    var $office;
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
    var $birthDate;
    var $indigenousStatus;
    var $minorityStatus;
    var $disabilityStatus;
    var $ethnicity;
    var $projects;
    var $university;
    var $universityDuring;
    var $groups;
    var $roles;
    var $rolesDuring;
    var $candidate;
    var $isEvaluator = array();
    var $hqps;
    var $historyHqps;
    var $contributions;
    var $multimedia;
    var $aliases = false;
    var $roleHistory;
    var $degreeReceived;
    var $degreeStartDate;
    var $movedOn;
    var $thesis;
    var $extra = array();
    var $leadershipCache = array();
    var $themesCache = array();
    var $hqpCache = array();
    var $projectCache = array();
    var $evaluateCache = array();
    var $clipboard = null;

    /**
     * Returns a new Person from the given id
     * @param int $id The id of the person
     * @return Person The Person from the given id
     */
    static function newFromId($id){
        if(isset(self::$cache[$id])){
            return self::$cache[$id];
        }
        if(!Cache::exists("idsCache_$id")){
            self::generateNamesCache();
            $data = array();
            if(isset(self::$idsCache[$id])){
                $data[] = self::$idsCache[$id];
            }
            Cache::store("idsCache_$id", $data);
        }
        else{
            $data = Cache::fetch("idsCache_$id");
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
        
        if(isset(self::$cache[$alias])){
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

        if(count($data) == 0){
            self::$cache[$alias] = false;
            return false;
        }
        else {
            // This could have multiple results if there is a duplicate alias
            $id = $data[0]['user_id'];
            if(isset(self::$cache[$id])){
                // Mark this alias too.
                self::$cache[$alias] = self::$cache[$id];
                return self::$cache[$id];
            }

            $person = new Person($data);
            self::$cache[$alias] = &$person;
            self::$cache[$person->getId()] = &$person;
            self::$cache[$person->getName()] = &$person;
            return $person;
        }
    }
    
    /**
     * Removes certain characters from a person's name to help matching
     * @param string $name The name to clean
     * @return string The cleaned up name
     */
    static function cleanName($name){
        $name = preg_replace("/\(.*\)/", "", $name);
        $name = preg_replace('/\s+/', ' ',$name);
        $name = str_replace("'", "", $name);
        $name = str_replace(".", "", $name);
        $name = str_replace("*", "", $name);
        $name = str_replace(" And ", " ", $name);
        $name = str_replace(" and ", " ", $name);
        $name = trim($name);
        return $name;
    }
    
    /**
     * Caches the resultset of the alis table for superfast access
     */
    static function generateAliasCache(){
        if(empty(self::$aliasCache)){
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
        if(empty(self::$namesCache)){
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
                                              'employee_id',
                                              'user_email',
                                              'user_twitter',
                                              'user_website',
                                              'user_linkedin',
                                              'user_google_scholar',
                                              'user_orcid',
                                              'user_scopus',
                                              'user_researcherid',
                                              'user_office',
                                              'user_public_profile',
                                              'user_private_profile',
                                              'user_nationality',
                                              'user_stakeholder',
                                              'user_ecr',
                                              'user_agencies',
                                              'user_mitacs',
                                              'user_crc',
                                              'user_extra',
                                              'user_gender',
                                              'user_pronouns',
                                              'user_birth_date',
                                              'user_indigenous_status',
                                              'user_minority_status',
                                              'user_disability_status',
                                              'user_ethnicity',
                                              'candidate'),
                                        array('deleted' => NEQ(1)));
            foreach($data as $row){
                if(isset($phoneNumbers[$row['user_id']])){
                    $row['phone'] = $phoneNumbers[$row['user_id']];
                }
                self::$idsCache[$row['user_id']] = $row;
                
                $keys = array();
                if(!Cache::exists("nameCache_{$row['user_id']}")){
                    $exploded = explode(".", unaccentChars($row['user_name']));
                    $firstName = ($row['first_name'] != "") ? unaccentChars($row['first_name']) : @$exploded[0];
                    $lastName = ($row['last_name'] != "") ? unaccentChars($row['last_name']) : @$exploded[1];
                    $middleName = $row['middle_name'];
                    $keys = array(
                        strtolower($row['user_name']),
                        strtolower(str_replace(".", " ", $row['user_name'])),
                        strtolower("$firstName $lastName"),
                        strtolower("$lastName $firstName"),
                        strtolower("$firstName ".substr($lastName, 0, 1)),
                        strtolower("$lastName ".substr($firstName, 0, 1)),
                        strtolower(substr($firstName, 0, 1)." $lastName")
                    );
                    if(trim($row['user_real_name']) != '' && $row['user_name'] != trim($row['user_real_name'])){
                        $keys[] = unaccentChars(strtolower(str_replace("&nbsp;", " ", $row['user_real_name'])));
                    }
                    if($middleName != ""){
                        $middleName = unaccentChars($middleName);
                        $keys[] = strtolower("$firstName $middleName $lastName");
                        $keys[] = strtolower("$firstName ".substr($middleName, 0, 1)." $lastName");
                        $keys[] = strtolower(substr($firstName, 0, 1)." ".substr($middleName, 0, 1)." $lastName");
                        $keys[] = strtolower(substr($firstName, 0, 1)."".substr($middleName, 0, 1)." $lastName");
                        $keys[] = strtolower("$lastName ".substr($firstName, 0, 1).substr($middleName, 0, 1));
                    }
                    Cache::store("nameCache_{$row['user_id']}", $keys);
                }
                else{
                    $keys = Cache::fetch("nameCache_{$row['user_id']}");
                }
                foreach($keys as $key){
                    self::$namesCache[$key] = $row;
                }
            }
        }
    }
    
    /**
     * Caches the resultset of the user roles table
     * NOTE: This only caches the current roles, not the history
     */
    static function generateRolesCache(){
        if(empty(self::$rolesCache)){
            if(Cache::exists("rolesCache")){
                self::$rolesCache = Cache::fetch("rolesCache");
            }
            else{
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
                Cache::store("rolesCache", self::$rolesCache);
            }
        }
    }
    
    /**
     * Caches the resultset of the leaders
     */
    static function generateLeaderCache(){
        if(empty(self::$leaderCache)){
            $sql = "SELECT r.user_id, p.id, p.name, s.type, s.status
                    FROM grand_roles r, grand_role_projects rp, grand_project p, grand_project_status s
                    WHERE rp.role_id = r.id
                    AND rp.project_id = p.id
                    AND rp.project_id = s.project_id
                    AND r.role = '".PL."'
                    AND (r.end_date = '0000-00-00 00:00:00'
                         OR r.end_date > CURRENT_TIMESTAMP)
                    GROUP BY s.project_id, r.user_id";
            $data = DBFunctions::execSQL($sql);
            self::$leaderCache[-1][] = array();
            foreach($data as $row){
                self::$leaderCache[$row['user_id']][$row['id']] = $row;
            }
        }
    }
    
    /**
     * Caches the resultset of the theme leaders
     */
    static function generateThemeLeaderCache(){
        if(empty(self::$themeLeaderCache)){
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
        if(empty(self::$universityCache)){
            $sql = "SELECT user_id, university_name, department, position, end_date
                    FROM grand_user_university uu, grand_universities u, grand_positions p 
                    WHERE u.university_id = uu.university_id
                    AND uu.position_id = p.position_id
                    ORDER BY REPLACE(end_date, '0000-00-00 00:00:00', '9999-12-31 00:00:00') DESC";
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                if(!isset(self::$universityCache[$row['user_id']])){
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
     * Caches the resultset of the product authors
     */
    static function generateAuthorshipCache(){
        if(empty(self::$authorshipCache)){
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
        if(empty(self::$allPeopleCache)){
            $me = Person::newFromWgUser();
            $data = DBFunctions::select(array('mw_user'),
                                        array('user_id'),
                                        array('deleted' => NEQ(1),
                                              'candidate' => NEQ(1),
                                              WHERE_OR('user_id') => EQ($me->getId())),
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
     * @return array The People who currently have at least the Staff role
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
            $pls = self::getAllPeople(PL);
            $merged = array_merge($ars, $cis, $pls);
            $people = array();
            foreach($merged as $person){
                $people[$person->getName()] = $person;
            }
            return $people;
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
                    if($filter == PL || $filter == APL){
                        $skip = true;
                        foreach($person->leadership(true) as $proj){
                            if(!$proj->isDeleted()){
                                // Don't skip if atleast 1 project is active
                                $skip = false;
                            }
                        }
                        if($skip){
                            continue;
                        }
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
            else if($filter == null || $filter == "all" || isset(self::$rolesCache[$row])){
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
        $startRange = cleanDate($startRange);
        $endRange = cleanDate($endRange);
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
            $pls = self::getAllCandidates(PL);
            $merged = array_merge($ars, $cis, $pls);
            $people = array();
            foreach($merged as $person){
                $people[$person->getName()] = $person;
            }
            return $people;
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
    
    /**
     * Returns an array of People of the type $filter and are also candidates
     * @param string $filter The role to filter by
     * @return array The array of People of the type $filter
     */
    static function getAllCandidatesDuring($filter=null, $startDate=false, $endDate=false){
        if($filter == NI){
            $ars = self::getAllCandidatesDuring(AR, $startDate, $endDate);
            $cis = self::getAllCandidatesDuring(CI, $startDate, $endDate);
            $pls = self::getAllCandidatesDuring(PL, $startDate, $endDate);
            $merged = array_merge($ars, $cis, $pls);
            $people = array();
            foreach($merged as $person){
                $people[$person->getName()] = $person;
            }
            return $people;
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
            if($person->getName() != "WikiSysop" && ($filter == null || $filter == "all" || $person->isRoleDuring($filter.'-Candidate', $startDate, $endDate))){
                if($me->isLoggedIn() || $person->isRoleAtLeastDuring(NI, $startDate, $endDate)){
                    $people[] = $person;
                }
            }
        }
        return $people;
    }
    
    /**
     * Returns whether atleast one user with the role exists
     * @param string $role The role to check
     */
    static function peopleWithRoleExists($role){
        $dbRole = DBFunctions::execSQL("SELECT role
                                        FROM `grand_roles` r, mw_user u
                                        WHERE r.user_id = u.user_id
                                        AND role = '$role'
                                        AND u.deleted = 0
                                        AND u.candidate = 0
                                        LIMIT 1");
        return !empty($dbRole);
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
            $this->employeeId = @$data[0]['employee_id'];
            $this->email = @$data[0]['user_email'];
            $this->phone = @$data[0]['phone'];
            $this->gender = @$data[0]['user_gender'];
            $this->pronouns = @$data[0]['user_pronouns'];
            $this->birthDate = @$data[0]['user_birth_date'];
            $this->indigenousStatus = @$data[0]['user_indigenous_status'];
            $this->disabilityStatus = @$data[0]['user_disability_status'];
            $this->ethnicity = @$data[0]['user_ethnicity'];
            $this->minorityStatus = @$data[0]['user_minority_status'];
            $this->nationality = @$data[0]['user_nationality'];
            $this->stakeholder = @$data[0]['user_stakeholder'];
            $this->earlyCareerResearcher = @$data[0]['user_ecr'];
            $this->agencies = @json_decode($data[0]['user_agencies']);
            $this->mitacs = @$data[0]['user_mitacs'];
            $this->canadaResearchChair = @json_decode($data[0]['user_crc'], true);
            $this->university = false;
            $this->twitter = @$data[0]['user_twitter'];
            $this->website = @$data[0]['user_website'];
            $this->linkedin = @$data[0]['user_linkedin'];
            $this->googleScholar = @$data[0]['user_google_scholar'];
            $this->orcid = @$data[0]['user_orcid'];
            $this->scopus = @$data[0]['user_scopus'];
            $this->researcherId = @$data[0]['user_researcherid'];
            $this->office = @$data[0]['user_office'];
            $this->publicProfile = @$data[0]['user_public_profile'];
            $this->privateProfile = @$data[0]['user_private_profile'];
            $this->extra = @json_decode($data[0]['user_extra'], true);
            if($this->extra == null){
                $this->extra = array();
            }
            $this->hqps = null;
            $this->historyHqps = null;
            $this->candidate = @$data[0]['candidate'];
        }
    }
    
    function toSimpleArray(){
        $json = array('id' => $this->getId(),
                      'name' => $this->getName(),
                      'realName' => $this->getRealName(),
                      'fullName' => $this->getNameForForms(),
                      'reversedName' => $this->getReversedName(),
                      'url' => $this->getUrl());
        return $json;
    }
    
    function toSimpleJSON(){
        return json_encode($this->toSimpleArray());
    }
    
    function toArray(){
        global $wgUser, $config;
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
                      'pronouns' => $this->getPronouns(),
                      'birthDate' => $this->getBirthDate(),
                      'indigenousStatus' => $this->getIndigenousStatus(),
                      'minorityStatus' => $this->getMinorityStatus(),
                      'disabilityStatus' => $this->getDisabilityStatus(),
                      'ethnicity' => $this->getEthnicity(),
                      'nationality' => $this->getNationality(),
                      'stakeholder' => $this->getStakeholder(),
                      'earlyCareerResearcher' => $this->getEarlyCareerResearcher(),
                      'agencies' => $this->getAgencies(),
                      'mitacs' => $this->getMitacs(),
                      'canadaResearchChair' => $this->getCanadaResearchChair(),
                      'twitter' => $this->getTwitter(),
                      'website' => $this->getWebsite(),
                      'linkedin' => $this->getLinkedIn(),
                      'googleScholar' => $this->getGoogleScholar(),
                      'orcid' => $this->getOrcid(),
                      'scopus' => $this->getScopus(),
                      'researcherId' => $this->getResearcherId(),
                      'office' => $this->getOffice(),
                      'photo' => $this->getPhoto(),
                      'cachedPhoto' => $this->getPhoto(true),
                      'university' => $this->getUni(),
                      'department' => $this->getDepartment(),
                      'faculty' => $this->getFaculty(),
                      'position' => $this->getPosition(),
                      'roles' => $roles,
                      'keywords' => $this->getKeywords(),
                      'publicProfile' => $publicProfile,
                      'privateProfile' => $privateProfile,
                      'extra' => $this->getExtra(),
                      'url' => $this->getUrl(),
                      'candidate' => ($this->isCandidate() == 1));
        if($config->getValue('networkName') == 'FES'){
            $deptUrlMap = array('Engineering' => 'https://www.engineering.ualberta.ca/',
                                'Science' => 'https://www.ualberta.ca/science',
                                'Agricultural, Life & Environmental Sciences' => 'https://www.ualberta.ca/agriculture-life-environment-sciences',
                                'School of Business' => 'https://www.ualberta.ca/business/',
                                'Arts' => 'https://www.ualberta.ca/arts/',
                                'Native Studies' => 'https://www.ualberta.ca/native-studies',
                                'Law' => 'https://www.ualberta.ca/law/',
                                'Augustana Campus', 'https://www.ualberta.ca/augustana/',
                                'Campus St. Jean' => 'https://www.ualberta.ca/en/campus-saint-jean');
            if(isset($deptUrlMap[$this->getDepartment()])){
                $json['departmentUrl'] = $deptUrlMap[$this->getDepartment()];
            }
            else{
                $json['departmentUrl'] = "";
            }
        }
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
                                    array('employee_id' => $this->getEmployeeId(),
                                          'user_twitter' => $this->getTwitter(),
                                          'user_website' => $this->getWebsite(),
                                          'user_linkedin' => $this->getLinkedIn(),
                                          'user_google_scholar' => $this->getGoogleScholar(),
                                          'user_orcid' => $this->getOrcid(),
                                          'user_scopus' => $this->getScopus(),
                                          'user_researcherid' => $this->getResearcherId(),
                                          'user_office' => $this->getOffice(),
                                          'user_gender' => $this->getGender(),
                                          'user_nationality' => $this->getNationality(),
                                          'user_stakeholder' => $this->getStakeholder(),
                                          'user_ecr' => $this->getEarlyCareerResearcher(),
                                          'user_agencies' => json_encode($this->getAgencies()),
                                          'user_mitacs' => $this->getMitacs(),
                                          'user_crc' => json_encode($this->getCanadaResearchChair()),
                                          'user_extra' => json_encode($this->extra),
                                          'user_public_profile' => $this->getProfile(false),
                                          'user_private_profile' => $this->getProfile(true)),
                                    array('user_name' => EQ($this->getName())));
            if($status && ($this->isMe() || $me->isRoleAtLeast(STAFF))){
                $status = DBFunctions::update('mw_user',
                                        array('user_gender' => $this->getGender()),
                                        array('user_name' => EQ($this->getName())));     
            }
            if($status && $me->isAllowedToEditDemographics($this)){
                $status = DBFunctions::update('mw_user',
                                        array('user_pronouns' => $this->getPronouns(),
                                              'user_birthDate' => $this->getBirthDate(),
                                              'user_indigenous_status' => $this->getIndigenousStatus(),
                                              'user_minority_status' => $this->getMinorityStatus(),
                                              'user_disability_status' => $this->getDisabilityStatus(),
                                              'user_ethnicity' => $this->getEthnicity()),
                                        array('user_name' => EQ($this->getName())));      
            }
            DBFunctions::commit();
            Cache::delete("rolesCache");
            Person::$cache = array();
            Person::$namesCache = array();
            Person::$aliasCache = array();
            Person::$idsCache = array();
            Cache::delete("idsCache_{$this->getId()}");
            $person = Person::newFromName($_POST['wpName']);
            if($person->exists()){
                MailingList::subscribeAll($person);
                return $status;
            }
        }
        return false;
    }
    
    function update(){
        $me = Person::newFromWgUser();
        if($me->isAllowedToEdit($this)){
            $candidate = DBFunctions::select(array('mw_user'),
                                             array('candidate'),
                                             array('user_id' => $this->getId()));
            $candidateBefore = $this->candidate;
            $this->candidate = $candidate[0]['candidate'];
            MailingList::unsubscribeAll($this);
            $this->candidate = $candidateBefore;
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
                                          'employee_id' => $this->getEmployeeId(),
                                          'user_twitter' => $this->getTwitter(),
                                          'user_website' => $this->getWebsite(),
                                          'user_linkedin' => $this->getLinkedIn(),
                                          'user_google_scholar' => $this->getGoogleScholar(),
                                          'user_orcid' => $this->getOrcid(),
                                          'user_scopus' => $this->getScopus(),
                                          'user_researcherid' => $this->getResearcherId(),
                                          'user_office' => $this->getOffice(),
                                          'user_nationality' => $this->getNationality(),
                                          'user_stakeholder' => $this->getStakeholder(),
                                          'user_ecr' => $this->getEarlyCareerResearcher(),
                                          'user_agencies' => json_encode($this->getAgencies()),
                                          'user_mitacs' => $this->getMitacs(),
                                          'user_crc' => json_encode($this->getCanadaResearchChair()),
                                          'user_extra' => json_encode($this->extra),
                                          'user_public_profile' => $this->getProfile(false),
                                          'user_private_profile' => $this->getProfile(true)),
                                    array('user_id' => EQ($this->getId())));
            if($status && $me->isRoleAtLeast(STAFF)){
                $status = DBFunctions::update('mw_user',
                                              array('candidate' => $this->candidate),
                                              array('user_id' => EQ($this->getId())));
                if($candidate[0]['candidate'] != $this->candidate){
                    if(!$this->candidate){
                        Notification::addNotification($me, Person::newFromId(0), "Candidate Changed", "<b>{$this->getNameForForms()}</b> is no longer a <b>Candidate</b>", "{$this->getUrl()}");
                    }
                    else{
                        Notification::addNotification($me, Person::newFromId(0), "Candidate Changed", "<b>{$this->getNameForForms()}</b> is now a <b>Candidate</b>", "{$this->getUrl()}");
                    }
                }
            }
            if($status && ($this->isMe() || $me->isRoleAtLeast(STAFF))){
                $status = DBFunctions::update('mw_user',
                                              array('user_gender' => $this->getGender()),
                                              array('user_id' => EQ($this->getId())));      
            }
            if($status && $me->isAllowedToEditDemographics($this)){
                $status = DBFunctions::update('mw_user',
                                              array('user_pronouns' => $this->getPronouns(),
                                                    'user_birth_date' => $this->getBirthDate(),
                                                    'user_indigenous_status' => $this->getIndigenousStatus(),
                                                    'user_minority_status' => $this->getMinorityStatus(),
                                                    'user_disability_status' => $this->getDisabilityStatus(),
                                                    'user_ethnicity' => $this->getEthnicity()),
                                              array('user_id' => EQ($this->getId())));      
            }
            $this->getUser()->invalidateCache();
            Person::$cache = array();
            Person::$namesCache = array();
            Person::$aliasCache = array();
            Person::$idsCache = array();
            Cache::delete("nameCache_{$this->getId()}");
            Cache::delete("idsCache_{$this->getId()}");
            MailingList::subscribeAll($this);
            DBFunctions::commit();
            return $status;
        }
        return false;
    }
    
    function delete(){
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(MANAGER)){
            Cache::delete("nameCache_{$this->getId()}");
            Cache::delete("idsCache_{$this->getId()}");
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
     * @return Person Whether or not this Person is allowed to edit the specified Person
     */
    function isAllowedToEdit($person){
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            return false;
        }
        if($person->isMe()){
            // User is themselves
            return true;
        }
        if($this->isRoleAtLeast(STAFF)){
            // User is at least Staff
            return true;
        }
        if($this->isRole(NI) && !$person->isRoleAtLeast(COMMITTEE)){
            // User is NI, therefore can edit anyone who is not in a committee or higher
            return true;
        }
        if($this->isRole(PA)){
            // User is a Project Assistant, therefore can edit anyone who is on their project
            foreach($person->getProjects() as $project){
                // Allow Project Assistants to edit
                if($this->isRole(PA, $project)){
                    return true;
                }
            }
        }
        if($this->isRole(PL) && (!$person->isRoleAtLeast(COMMITTEE) || $person->isRole(NI) || $person->isRole(HQP))){
            // User is a Project Leader, therefore can edit anyone who is not in a committee or higher unless they are also an NI or HQP
            return true;
        }
        if(($this->isThemeCoordinator() || $this->isThemeLeader()) && (!$person->isRoleAtLeast(COMMITTEE) || $person->isRole(NI) || $person->isRole(HQP))){
            // User is a Theme Leader, therefore can edit anyone who is not in a committee or higher unless they are also an NI or HQP
            return true;
        }
        if($this->isRoleAtLeast(COMMITTEE) && !$person->isRoleAtLeast(STAFF)){
            // User is in a committee or higher, therefore can edit anyone who is not at least Staff
            return true;
        }
        if($this->relatedTo($person, SUPERVISES)){
            // User supervises the Person
            return true;
        }
        foreach($person->getCreators() as $creator){
            if($creator->getId() == $this->getId()){
                // User created the Person
                return true;
            }
        }
        return false;
    }
    /**
     * Returns whether or not this Person is allowed to edit the specified Person's demographics
     * @param Person $person The Person to edit
     * @return Person Whether or not this Person is allowed to edit the specified Person
     */
    function isAllowedToEditDemographics($person){
        if($person->isMe()){
            // User is themselves
            return true;
        }
        if($this->isRoleAtLeast(MANAGER)){
            // User is at least Manager
            return true;
        }
        return false;
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
     * Returns when the User last user activity occured
     * @return string Returns when the User last user activity occured
     */
    function getTouched(){
        $data = DBFunctions::select(array('mw_user'),
                                    array('user_touched'),
                                    array('user_id' => $this->id));
        if($data[0]['user_touched'] == 0){
            return $this->getRegistration();
        }
        return $data[0]['user_touched'];
    }
    
    function isAuthenticated(){
        $data = DBFunctions::select(array('mw_user'),
                                    array('user_email_authenticated', 'user_email_token'),
                                    array('user_id' => $this->id));
        if(isset($data[0])){
            $row = $data[0];
            return ($row['user_email_token'] == "" || $row['user_email_authenticated'] != "");
        }
        return false;
    }
      
    /**
     * Returns whether this Person is a member of the given Project or not
     * @param Project $project The Project to check
     * @return boolean Whether or not this Person is currently a member of the given Project
     */
    function isMemberOf($project){
        if($project == null){
            return false;
        }
        if(!$project->clear){
            foreach($project->getPreds() as $pred){
                if($this->isMemberOf($pred)){
                    return true;
                }
            }
        }
        $projects = $this->getProjects(false, true);
        if(count($projects) > 0){
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
        if($project == null){
            return false;
        }
        if(!$project->clear){
            foreach($project->getPreds() as $pred){
                if($this->isMemberOfDuring($pred, $start, $end)){
                    return true;
                }
            }
        }
        $projects = $this->getProjectsDuring($start, $end);
        if(count($projects) > 0){
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
    
    function isThemeLeaderDuring($startRange, $endRange){
        $startRange = cleanDate($startRange);
        $endRange = cleanDate($endRange);
        $sql = "SELECT *
                FROM grand_theme_leaders l
                WHERE l.user_id = '{$this->id}'
                AND l.coordinator != 'True' 
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
    
    function isThemeCoordinatorDuring($startRange, $endRange){
        $startRange = cleanDate($startRange);
        $endRange = cleanDate($endRange);
        $sql = "SELECT *
                FROM grand_theme_leaders l
                WHERE l.user_id = '{$this->id}'
                AND (l.coordinator = 'True' OR l.coordinator = '')
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
     * Returns whether or not this Person is a theme leader of the given Project
     * @param Project $project The Project to check
     * @return boolean Whether or not this Person is a theme leader of the given Project
     */
    function isThemeLeaderOf($project){
        if($project == null){
            return false;
        }
        $themes = $this->getLeadThemes();
        if($project instanceof Theme){
            $challenge = $project;
            foreach($themes as $theme){
                if($challenge->getId() == $theme->getId()){
                    return true;
                }
            }
        }
        else {
            foreach($project->getChallenges() as $challenge){
                foreach($themes as $theme){
                    if($challenge->getId() == $theme->getId()){
                        return true;
                    }
                }
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
        if($project == null){
            return false;
        }
        $themes = $this->getCoordThemes();
        if($project instanceof Theme){
            $challenge = $project;
            foreach($themes as $theme){
                if($challenge->getId() == $theme->getId()){
                    return true;
                }
            }
        }
        else {
            foreach($project->getChallenges() as $challenge){
                foreach($themes as $theme){
                    if($challenge->getId() == $theme->getId()){
                        return true;
                    }
                }
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
        return $this->name;
    }
    
    /**
     * Returns the real name of this Person
     * @return string The real name of this Person
     */
    function getRealName(){
        return $this->realname;
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
     * Returns the email address of this Person
     * @return string The email address of this Person
     */
    function getEmployeeId(){
        if($this->employeeId == 0){
            return "";
        }
        return sprintf("%07d", $this->employeeId);
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
        global $config;
        if(!$config->getValue("genderEnabled")){
            return "";
        }
        $me = Person::newFromWgUser();
        if($this->isMe() || $me->isRoleAtLeast(STAFF)){
            return $this->gender;
        }
        else{
            // Check Project Leadership
            foreach($this->getProjects(true) as $project){
                if($me->isRole(PL, $project)){
                    return $this->gender;
                }
            }
        }
        return "";
    }
    
    /**
     * Returns the nationality of this Person
     * @return string The nationality of this Person
     */
    function getNationality(){
        global $config;
        if(!$config->getValue("nationalityEnabled")){
            return "";
        }
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            if($config->getValue('nationalityAll')){
                return $this->nationality;
            }
            else if($this->nationality != ""){
                if($this->nationality == "Canadian" || 
                   $this->nationality == "Landed Immigrant"){
                    return "Canadian";
                }
                else{
                    return "Foreign";
                }
            }
        }
        return "";
    }
    
    /**
     * Returns the stakeholder category of this Person
     * @return string The stakeholder category of this Person
     */
    function getStakeholder(){
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            return $this->stakeholder;
        }
        return "";
    }
    
    /**
     * Returns whether this Person is a stakeholder
     * @return string Whether this Person is a stakeholder
     */
    function isStakeholder(){
        return ($this->getStakeholder() != "");
    }
    
    /**
     * Returns the earlyCareerResearcher status of this Person
     * @return string The earlyCareerResearcher status of this Person
     */
    function getEarlyCareerResearcher(){
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            return $this->earlyCareerResearcher;
        }
        return "";
    }
    
    /**
     * Returns the agencieis array for this Person
     * @return string The agencies array for this Person
     */
    function getAgencies($delim=null){
        $me = Person::newFromWgUser();
        $agencies = array();
        if($me->isLoggedIn()){
            if(is_array($this->agencies)){
                $agencies = $this->agencies;
            }
        }
        if($delim != null){
            return implode($delim, $agencies);
        }
        return $agencies;
    }
    
    /**
     * Returns the MITACS status of this Person
     * @return string The MITACS status of this Person
     */
    function getMitacs(){
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            return $this->mitacs;
        }
        return "";
    }
    
    /**
     * Returns the canada research chair status of this Person
     * @return string The canada research chair status of this Person
     */
    function getCanadaResearchChair(){
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            return $this->canadaResearchChair;
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
     * Returns the url of this Person's website
     * @return string The url of this Person's website
     */
    function getLinkedIn(){
        if (preg_match("#https?://#", $this->linkedin) === 0) {
            $this->linkedin = 'http://'.$this->linkedin;
        }
        return $this->linkedin;
    }
    
    /**
     * Returns the url of this Person's google scholar
     * @return string The url of this Person's google scholar
     */
    function getGoogleScholar(){
        if (preg_match("#https?://#", $this->googleScholar) === 0) {
            $this->googleScholar = 'https://'.$this->googleScholar;
        }
        return $this->googleScholar;
    }
    
    function getOrcid(){
        return $this->orcid;
    }
    
    function getScopus(){
        return $this->scopus;
    }
    
    function getResearcherId(){
        return $this->researcherId;
    }
    
    function getOffice(){
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            return "{$this->office}";
        }
        return "";
    }
    
    /**
     * Returns the url of this Person's profile page
     * @return string The url of this Person's profile page
     */
    function getUrl(){
        global $wgServer, $wgScriptPath, $config;
        $me = Person::newFromWgUser();
        if($this->id > 0 && ($me->isLoggedIn() || $config->getValue('hqpIsPublic') ||$this->isRoleAtLeast(NI)) && (!isset($_GET['embed']) || $_GET['embed'] == 'false')){
            return "{$wgServer}{$wgScriptPath}/index.php/{$this->getType()}:{$this->getName()}";
        }
        else if($this->id > 0 && ($me->isLoggedIn() || $config->getValue('hqpIsPublic') || $this->isRoleAtLeast(NI)) && isset($_GET['embed'])){
            return "{$wgServer}{$wgScriptPath}/index.php/{$this->getType()}:{$this->getName()}?embed";
        }
        return "";
    }
    
    /**
     * Returns the path to a photo of this Person if it exists
     * @param boolen $cached Whether or not to use a cached version
     * @return string The path to a photo of this Person
     */
    function getPhoto($cached=false){
        global $wgServer, $wgScriptPath, $config;
        if($this->photo == null || !$cached){
            if(file_exists("Photos/".str_ireplace(".", "_", $this->name).".jpg")){
                $this->photo = "$wgServer$wgScriptPath/Photos/".str_ireplace(".", "_", $this->name).".jpg";
                if(!$cached){
                    return $this->photo."?".microtime(true);
                }
            }
            else {
                if(file_exists("{$config->getValue('iconPathHighlighted')}face.png")){
                    $this->photo = "$wgServer$wgScriptPath/{$config->getValue('iconPathHighlighted')}face.png";
                }
                else{
                    $this->photo = "$wgServer$wgScriptPath/skins/face.png";
                }
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
        return strtr($this->name, $repl);
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
        global $config;
        $first = $this->getFirstName();
        $middle = $this->getMiddleName();
        $last = $this->getLastName();
        if($first != ""){
            if($config->getValue('includeMiddleName') && $middle != "")
                return "{$last}, {$first} {$middle}";
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
        global $config;
        if($config->getValue('includeMiddleName') && $this->getMiddleName() != "")
            return str_replace("\"", "<span class='noshow'>&quot;</span>", trim("{$this->getFirstName()} {$this->getMiddleName()} {$this->getLastName()}"));
        else if (!empty($this->realname))
            return str_replace("\"", "<span class='noshow'>&quot;</span>", str_replace("&nbsp;", " ", ucfirst($this->realname)));
        else
            return str_replace("\"", "<span class='noshow'>&quot;</span>", trim($this->getFirstName()." ".$this->getLastName()));
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

    function getNameForProduct($format=null){
        global $config;
        /*if(strstr($this->getNameForForms(), "<span class='noshow'>&quot;</span>") !== false){
            return $this->getNameForForms();
        }*/
        $regex = "/\{.*?\}/";
        if($format == null){
            $format = $config->getValue("nameFormat");
        }
        $format = strtolower($format);
        $format = preg_replace_callback($regex,"self::formatName",$format);
        $format = str_replace("\"", "<span class='noshow'>&quot;</span>", $format);
        return $format;
    }

    /**
     * Returns an array of aliases for this Person
     * @return array This Person's aliases
     */
    function getAliases(){
        $data = DBFunctions::select(array('mw_user_aliases'),
                                    array('*'),
                                    array('user_id' => $this->id));
        $aliases = array();
        foreach($data as $row){
            $aliases[] = $row['alias'];
        }
        return $aliases;
    }
    
    /**
     * Updates the Person's aliases
     * @param array $aliases The array of aliases
     * @return array This Person's aliases
     */
    function setAliases($aliases){
        $me = Person::newFromWgUser();
        if($me->isAllowedToEdit($this)){
            DBFunctions::delete('mw_user_aliases',
                                array('user_id' => $this->id));
            foreach($aliases as $alias){
                DBFunctions::insert('mw_user_aliases',
                                    array('user_id' => $this->id,
                                          'alias' => $alias));
            }
        }
        return $aliases;
    }
    
    // Returns the user's profile.
    // If $private is true, then it grabs the private version, otherwise it gets the public
    /**
     * Returns the text from this Person's profile
     * @param boolean $private If tru, then it grabs the private version, otherwise it gets the public
     * @return string This Person's profile text
     */
    function getProfile($private=false){
        global $config;
        if($config->getValue("publicProfileOnly")){
            // Disregard the $private parameter, always return publicProfile
            return $this->publicProfile;
        }
        else{
            if($private){
                return $this->privateProfile;
            }
            else{
                return $this->publicProfile;
            }
        }
    }
    
    /**
     * Returns the moved on row for when this HQP was inactivated
     * @return array An array of key/value pairs representing the DB row
     */
    function getMovedOn(){
        if($this->movedOn == null){
            $sql = "SELECT *
                    FROM `grand_movedOn`
                    WHERE `user_id` = '{$this->getId()}'
                    ORDER BY `effective_date` DESC";
            $data = DBFunctions::execSQL($sql);
            if(DBFunctions::getNRows() > 0){
                $this->movedOn = $data[0];
            }
            else{
                $this->movedOn = array("where" => "",
                                       "studies" => "",
                                       "employer" => "",
                                       "city" => "",
                                       "country" => "",
                                       "employment_type" => "",
                                       "effective_date" => "");
            }
        }
        return $this->movedOn;
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
        $startRange = cleanDate($startRange);
        $endRange = cleanDate($endRange);
        $sql = "SELECT `user_id`
                FROM `grand_movedOn`
                WHERE effective_date BETWEEN '$startRange' AND '$endRange'";
        $data = DBFunctions::execSQL($sql);
        $people = array();
        foreach($data as $row){
            $people[$row['user_id']] = Person::newFromId($row['user_id']);
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
        if($this->degreeStartDate === null){
            $this->degreeStartDate = "";
            $data = DBFunctions::select(array('grand_relations'),
                                        array('start_date'),
                                        array('user2' => EQ($this->getId())),
                                        array('start_date' => 'ASC'));
            if(DBFunctions::getNRows() > 0){
                $this->degreeStartDate = $data[0]['start_date'];
            }
        }
        return $this->degreeStartDate;
    }

    /**
     * Returns when this Person's degree ended (NOTE: This is a guesstimate)
     * @return string The date that this Person's degree ended
     */
    function getDegreeReceivedDate($guess = true){
        if($this->degreeReceived === null){
            $this->degreeReceived = "";
            $data = DBFunctions::select(array('grand_relations'),
                                        array('end_date'),
                                        array('user2' => EQ($this->getId()),
                                              'type' => EQ('Supervises')),
                                        array('end_date' => 'ASC'));
            if(DBFunctions::getNRows() > 0){
                $this->degreeReceived = $data[0]['end_date'];
            }
        }
        return $this->degreeReceived;
    }
    
    /**
     * Returns the current University that this Person is at
     * @return array The current University this Person is at
     */ 
    function getUniversity(){
        if($this->university !== false){
            return $this->university;
        }
        if(!Cache::exists("user_university_{$this->id}")){
            self::generateUniversityCache();
            $this->university = @self::$universityCache[$this->id];
            Cache::store("user_university_{$this->id}", $this->university);
        }
        else{
            $this->university = Cache::fetch("user_university_{$this->id}");
        }
        if($this->university === null){
            $this->university = array("university" => "",
                                      "department" => "",
                                      "position"   => "",
                                      "date"       => "");
        }
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
     * Tries to map the department to a faculty
     * @return string The name of the faculty
     */
    function getFaculty(){
        $department = $this->getDepartment();
        if(isset(Person::$facultyMap[$department])){
            return Person::$facultyMap[$department];
        }
        return "";
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
        $startRange = cleanDate($startRange);
        $endRange = cleanDate($endRange);
        $data = $this->getUniversitiesDuring($startRange, $endRange);
        if(isset($data[0])){
            return $data[0];
        }
        return array("university" => "",
                     "department" => "",
                     "position"   => "",
                     "date"       => "");
    }
    
    /*
     * Returns an array of Universities that this Person is currently at
     * @return array The current Universities this Person is at
     */
    function getCurrentUniversities(){
        $unis = $this->getUniversitiesDuring(date("Y-m-d H:i:s"), date("Y-m-d H:i:s"));
        if(count($unis) == 0){
            $unis[] = $this->getUniversity();
        }
        return $unis;
    }
    
    /**
     * Returns all the Universities that this Person was at between the given range
     * @param string $startRange The start date to look at
     * @param string $endRange The end date to look at
     * @return array The Universities that this Person was at between the given range
     */ 
    function getUniversitiesDuring($startRange, $endRange){
        $startRange = cleanDate($startRange);
        $endRange = cleanDate($endRange);
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
                                                "position"   => $row['position'],
                                                "start" => $row['start_date'],
                                                "end" => $row['end_date']);
                    }
                }
            }
            $this->universityDuring[$startRange.$endRange] = $universities;
        }
        return $this->universityDuring[$startRange.$endRange];
    }
    
    function getFirstUniversity(){
        $universities = $this->getUniversitiesDuring("0000-00-00", "2100-01-01");
        if(count($universities) > 0){
            usort($universities, function($a, $b){
                return ($a['start'] > $b['start']);
            });
            return $universities[0];
        }
        // None found, use the 'default' values
        return array("university" => $this->getUni(),
                     "department" => $this->getDepartment(),
                     "position"   => $this->getPosition(),
                     "start" => "",
                     "end" => "");
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
     * Returns all the keywords for this Person
     * @param string $delim An optional delimiter
     * @return array All the keywords for this Person as an array, or a string if a delimiter is specified
     */
    function getKeywords($delim=null){
        $cacheId = "keywords_{$this->getId()}";
        if(Cache::exists($cacheId)){
            $keywords = Cache::fetch($cacheId);
        }
        else{
            $data = DBFunctions::select(array('grand_person_keywords'),
                                        array('keyword'),
                                        array('user_id' => $this->getId()));
            $keywords = array();
            foreach($data as $row){
                $keywords[] = $row['keyword'];
            }
            Cache::store($cacheId, $keywords);
        }
        if($delim != null){
            return implode($delim, $keywords);
        }
        return $keywords;
    }
    
    /**
     * Updates the keywords for this Person
     * @param array $keywords The array of keywords
     */
    function setKeywords($keywords){
        $cacheId = "keywords_{$this->getId()}";
        DBFunctions::delete('grand_person_keywords',
                            array('user_id' => $this->getId()));
        
        foreach($keywords as $keyword){
            DBFunctions::insert('grand_person_keywords',
                                array('user_id' => $this->getId(),
                                      'keyword' => $keyword));
        }
        Cache::delete($cacheId);
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
        GrandAccess::setupGrandAccess($user, $user->mRights);
        return $user->mRights;
    }
    
    /**
     * Returns one of the roles that this person is
     * @return string One of the roles that this person is
     */
    function getType(){
        global $wgRoleValues;
        $roles = $this->getRoles();
        $maxRole = null;
        $maxRoleValue = 0;
        if(count($roles) > 0){
            foreach($roles as $role){
                if(!$role->isAlias() || count($roles) == 1){
                    if($wgRoleValues[$role->getRole()] >= $maxRoleValue){
                        $maxRoleValue = $wgRoleValues[$role->getRole()];
                        $maxRole = $role->getRole();
                    }
                }
            }
        }
        if($maxRole == INACTIVE){
            if($this->isRole(PL)){
                return PL;
            }
            if($this->isThemeLeader()){
                return TL;
            }
            if($this->isThemeCoordinator()){
                return TC;
            }
        }
        return $maxRole;
    }
    
    /**
     * Returns a string containing the full role information
     * @return string The full role information for this Person
     */
    function getRoleString(){
        global $config;
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn() && !$this->isRoleAtLeast(NI) && !$config->getValue('hqpIsPublic')){
            return "";
        }
        $roles = $this->getRoles();
        $roleNames = array();
        foreach($roles as $role){
            $roleNames[$role->getRole()] = $role->getRole();
        }
        foreach($roleNames as $key => $role){
            if($role == INACTIVE){
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
    function getRole($role, $history=false){
        foreach($this->getRoles($history) as $r){
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
                // All History
                if($this->roleHistory === null){
                    $data = DBFunctions::select(array('grand_roles'),
                                                array('*'),
                                                array('user_id' => $this->id),
                                                array('end_date' => 'DESC'));
                    $this->roleHistory = array();
                    foreach($data as $row){
                        $this->roleHistory[] = new Role(array($row));
                    }
                }
                return $this->roleHistory;
            }
            else{
                // History at a specific date
                $sql = "SELECT *
                        FROM grand_roles
                        WHERE user_id = '{$this->id}'
                        AND start_date <= '{$history}'
                        AND (end_date >= '{$history}' OR end_date = '0000-00-00 00:00:00')";
                $data = DBFunctions::execSQL($sql);
                $roles = array();
                if(count($data) > 0){
                    foreach($data as $row){
                        $roles[] = new Role(array($row));
                    }
                }
                return $roles;
            }
        }
        self::generateRolesCache();
        if($this->roles == null && $this->id != null){
            $this->roles = array();
            if($this->isThemeLeader()){
                $this->roles[] = new Role(array(0 => array('id' => -1,
                                                           'user_id' => $this->id,
                                                           'role' => TL,
                                                           'title' => '',
                                                           'start_date' => '0000-00-00 00:00:00',
                                                           'end_date' => '0000-00-00 00:00:00',
                                                           'comment' => '')));
            }
            if($this->isThemeCoordinator()){
                $this->roles[] = new Role(array(0 => array('id' => -1,
                                                           'user_id' => $this->id,
                                                           'role' => TC,
                                                           'title' => '',
                                                           'start_date' => '0000-00-00 00:00:00',
                                                           'end_date' => '0000-00-00 00:00:00',
                                                           'comment' => '')));
            }
            if(isset(self::$rolesCache[$this->id])){
                foreach(self::$rolesCache[$this->id] as $row){
                    $this->roles[] = new Role(array(0 => $row));
                }
            }
            else if(count($this->roles) == 0){
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
        if($this->isRoleOn(AR, $year, $project) && !$this->isRoleOn(PL, $year, $project)){
            return AR;
        }
        else if($this->isRoleOn(CI, $year, $project) && !$this->isRoleOn(PL, $year, $project)){
            return CI;
        }
        else if($this->isRoleOn(HQP, $year, $project)){
            return HQP;
        }
        else if($aliases && $this->isRoleOn("FAKENI", $year, $project)){
            return "FAKENI";
        }
        else {
            if(count($this->getRoles()) > 0){
                foreach($this->getRoles() as $role){
                    if(!isset($committees[$role->getRole()]) && $this->isRoleOn($role->getRole(), $year, $project)){
                        return $role->getRole();
                    }
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
        if($this->isCandidate()){
            return array();
        }
        $maxValue = 0;
        $roles = array();
        $roleNames = array();
        foreach($this->getRoles() as $role){
            $roleNames[] = $role->getRole();
        }
        if($this->isThemeLeader()){
            $roleNames[] = TL;
        }
        if($this->isThemeCoordinator()){
            $roleNames[] = TC;
        }
        if(is_array($this->getRoles())){
            foreach($roleNames as $role){
                $maxValue = max($maxValue, $wgRoleValues[$role]);
            }
            foreach($wgRoleValues as $role => $value){
                if($value <= $maxValue && array_search($role, $wgRoles) !== false){
                    $roles[$role] = $role;
                }
            }
        }
        if(!$this->isRoleAtLeast(STAFF) && isset($roles[PL])){
            unset($roles[PL]);
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
            $projects[$project->getId()] = $project->getName();
        }
        foreach($this->leadership() as $project){
            $projects[$project->getId()] = $project->getName();
        }
        foreach($this->getThemeProjects() as $project){
            $projects[$project->getId()] = $project->getName();
        }
        if($this->isRoleAtLeast(STAFF)){
            foreach(Project::getAllProjectsEver(true) as $project){
                $projects[$project->getId()] = $project->getName();
            }
        }
        natsort($projects);
        //asort($projects);
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
                    $this->wasLastRole(PL));
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
                    $this->wasLastRoleAtLeast(PL));
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
                    $this->wasLastRoleAtMost(PL));
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
        
        $startRange = cleanDate($startRange);
        $endRange = cleanDate($endRange);
        
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
        $date = cleanDate($date);
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
    
    static function generateSubRolesCache(){
        if(empty(self::$subRolesCache)){
            $data = DBFunctions::select(array('grand_role_subtype'),
                                        array('user_id', 'sub_role'),
                                        array());
            if(count($data) > 0){
                foreach($data as $row){
                    @self::$subRolesCache[$row['user_id']][] = $row['sub_role'];
                }
            }
            else{
                self::$subRolesCache[0] = array();
            }
        }
    }

    /**
     * Returns an array of the subRoles that this Person is in
     * @return array The subRoles that this Person is in
     */
    function getSubRoles(){
        self::generateSubRolesCache();
        if(isset(self::$subRolesCache[$this->getId()])){
            return self::$subRolesCache[$this->getId()];
        }
        return array();
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
    
    function isSubRoleBefore($subRole, $date){
        $data = DBFunctions::select(array('grand_role_subtype'),
                                    array('sub_role'),
                                    array('user_id' => EQ($this->getId()),
                                          'changed' => LT($date)));
        return (count($data) > 0);
    }
    
    function isSubRoleSince($subRole, $date){
        $data = DBFunctions::select(array('grand_role_subtype'),
                                    array('sub_role'),
                                    array('user_id' => EQ($this->getId()),
                                          'changed' => GT($date)));
        return (count($data) > 0);
    }

    /*
     * Returns an array of 'PersonProjects' (used for Backbone API)
     * @return array
     */
    function getPersonProjects(){
        $projects = array();
        $data = DBFunctions::select(array('grand_roles' => 'r', 'grand_role_projects' => 'rp'),
                                    array('rp.role_id', 'rp.project_id', 'r.start_date', 'r.end_date', 'r.comment'),
                                    array('r.user_id' => EQ($this->id),
                                          'r.id' => EQ(COL('rp.role_id'))),
                                    array('end_date' => 'DESC'));
        foreach($data as $row){
            $project = Project::newFromId($row['project_id']);
            if($project != null && !$project->isSubProject() && !$project->isDeleted()){
                $projects[] = array(
                    'id' => "{$row['role_id']}-{$row['project_id']}",
                    'projectId' => $project->getId(),
                    'personId' => $this->getId(),
                    'startDate' => $row['start_date'],
                    'endDate' => $row['end_date'],
                    'name' => $project->getName(),
                    'comment' => $row['comment']
                );
            }
        }
        return $projects;
    }
    
    /*
     * Returns an array of 'PersonThemes' (used for Backbone API)
     * @return array
     */
    function getPersonThemes(){
        $themes = array();
        $data = DBFunctions::select(array('grand_theme_leaders'),
                                    array('id', 'theme', 'co_lead', 'coordinator', 'start_date', 'end_date', 'comment'),
                                    array('user_id' => EQ($this->id)),
                                    array('end_date' => 'DESC'));
        foreach($data as $row){
            $theme = Theme::newFromId($row['theme']);
            $themes[] = array(
                'id' => $row['id'],
                'themeId' => $theme->getId(),
                'personId' => $this->getId(),
                'coLead' => $row['co_lead'],
                'coordinator' => $row['coordinator'],
                'startDate' => $row['start_date'],
                'endDate' => $row['end_date'],
                'name' => $theme->getAcronym(),
                'comment' => $row['comment']
            );
        }
        return $themes;
    }
    
    /*
     * Returns an array of 'PersonUniversities' (used for Backbone API)
     * @return array
     */
    function getPersonUniversities(){
        $universities = array();
        $data = DBFunctions::select(array('grand_user_university' => 'uu',
                                          'grand_universities' => 'u',
                                          'grand_positions' => 'p'),
                                    array('uu.id', 'uu.user_id', 'u.university_name', 'uu.department', 'p.position', 'uu.start_date', 'uu.end_date'),
                                    array('uu.user_id' => EQ($this->id),
                                          'u.university_id' => EQ(COL('uu.university_id')),
                                          'p.position_id' => EQ(COL('uu.position_id'))),
                                    array('end_date' => 'DESC'));
        foreach($data as $row){
            $universities[] = array(
                'id' => $row['id'],
                'university' => $row['university_name'],
                'personId' => $this->getId(),
                'department' => $row['department'],
                'position' => $row['position'],
                'startDate' => $row['start_date'],
                'endDate' => $row['end_date']
            );
        }
        return $universities;
    }

    /**
     * Returns an array of Projects that this Person is a part of
     * @param boolean $history Whether or not to include the full history
     * @param boolean $allowProposed Whether or not to include proposed projects
     * @return array The Projects that this Person is a part of
     */
    function getProjects($history=false, $allowProposed=false){
        $projects = array();
        if((($this->projects === null && $history === false) || 
            (!isset($this->projectCache["{$history}"]) && $history !== false)) && $this->id != null){
            $sql = "SELECT p.name
                    FROM grand_roles r, grand_role_projects rp, grand_project p
                    WHERE r.user_id = '{$this->id}'
                    AND r.id = rp.role_id
                    AND rp.project_id = p.id \n";
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
                $project = Project::newFromName($row['name']);
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
            if($history === false){
                $this->projects = $projects;
            }
            else{
                $this->projectCache["{$history}"] = $projects;
            }
        }
        
        if($history === false){
            if($this->projects == null){
                $this->projects = $projects;
            }
            return $this->projects;
        }
        else {
            return $this->projectCache["{$history}"];
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
     * Returns the Relationships this Person has between the given dates
     * @param string $type The type of Relationship
     * @param string $startRange The start date
     * @param string $endRange The end date
     * @return array The Relationships this Person has
     */
    function getRelationsDuring($type='all', $startRange, $endRange){
        $type = DBFunctions::escape($type);
        $startRange = cleanDate($startRange);
        $endRange = cleanDate($endRange);
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
    
    /*
     * Returns an array of People that this Person manages
     * @return array The People that this Person manages
     */
    function getManagedPeople(){
        $people = array();
        $data = DBFunctions::select(array('grand_managed_people'),
                                    array('managed_id'),
                                    array('user_id' => EQ($this->getId())));
        foreach($data as $row){
            $person = Person::newFromId($row['managed_id']);
            if($person->getId() != 0){
                $people[$person->getReversedName()] = $person;
            }
        }
        return $people;
    }

    /**
     * Returns the Relationships this Person has
     * @param string $type The type of Relationship
     * @param boolean $history Whether or not to include the full history of Relationships
     * @param boolean $inverse Changes which user 'this Person' is referring to (user1/user2).  This only works when logged in at least Staff
     * @return array The Relationships this Person has
     */
    function getRelations($type='all', $history=false, $inverse=false){
        $me = Person::newFromWgUser();
        $relations = array();
        if($inverse && ($me->isRoleAtLeast(STAFF) || $me->isRole(PL) || $me->isRole(PA))){
            $where = "WHERE user2 = '{$this->id}'";
        }
        else{
            $where = "WHERE user1 = '{$this->id}'";
        }
        if($type == "all"){
            $sql = "SELECT id, type
                    FROM grand_relations, mw_user u1, mw_user u2
                    $where
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
                $relations[$row['type']][$row['id']] = Relationship::newFromId($row['id']);
            }
            return $relations;
        }
        else if($type == "public"){
            $sql = "SELECT id, type
                    FROM grand_relations, mw_user u1, mw_user u2
                    $where
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
                $relations[$row['type']][$row['id']] = Relationship::newFromId($row['id']);
            }
            return $relations;
        }
        $relations[$type] = array();
        $sql = "SELECT id, type
                FROM grand_relations, mw_user u1, mw_user u2
                $where
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
            $relations[$row['type']][$row['id']] = Relationship::newFromId($row['id']);
        }
        return $relations[$type];
    }
    
    /**
     * Returns an array of People who are 'similar' to this one
     * @return array People who are 'similar' to this one
     */
    function getSimilarPeople(){
        $text = $this->getKeywords(", ")."\n";
        $text .= $this->getProfile()."\n";
        $products = $this->getPapersAuthored("all", "1900-01-01 00:00:00", "2100-01-01 00:00:00", false);
        foreach($products as $product){
            $text .= $product->getTitle()."\n";
            $text .= $product->getDescription()."\n";
        }
        CommonWords::$commonWords[] = strtolower($this->getFirstName());
        CommonWords::$commonWords[] = strtolower($this->getLastName());
        $data = Wordle::createDataFromText($text);
        $data = array_slice($data, 0, 10);
        
        $similarPeople = array();
        $people = Person::getAllPeople();
        foreach($people as $person){
            if($person->getId() == $this->getId()){
                continue;
            }
            $text = $person->getKeywords(", ")."\n";
            $text .= $person->getProfile()."\n";
            $products = $person->getPapersAuthored("all", "1900-01-01 00:00:00", "2100-01-01 00:00:00", false);
            foreach($products as $product){
                $text .= $product->getTitle()."\n";
                $text .= $product->getDescription()."\n";
            }
            $text = strtolower($text);
            
            $found = 0;
            foreach($data as $word){
                if(strstr($text, strtolower($word['word'])) !== false){
                    $found += $word['freq'];
                }
            }
            
            if($found){
                $similarPeople["{$person->getId()}"] = $found;
            }
        }
        asort($similarPeople);
        
        $similarPeople = array_reverse($similarPeople, true);
        $newPeople = array();
        foreach($similarPeople as $key => $found){
            $newPeople[] = Person::newFromId($key);
        }
        return $newPeople;
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
     * Returns whether this Person is a Message Board Moderator
     * @return boolean Whether this Person is a Message Board Moderator
     */
    function isBoardMod(){
        global $config;
        foreach($config->getValue('boardMods') as $role){
            if($this->isRole($role)){
                return true;
            }
        }
        return false;
    }

    /**
     * Returns whether this Person is the given role (on the given optional project)
     * @param string $role The role of this Person
     * @param Project $project The Project the Person is a role on
     * @return boolean Whether or not the Person is the given role
     */
    function isRole($role, $project=null){
        if($project != null){
            // Check Project type
            if($project instanceof Project){
                $project = $project;
            }
            else if(is_string($project)){
                $project = Project::newFromHistoricName($project);
            }
            else{
                // Not a valid type (ie. Theme)
                return false;
            }
        }
        if($role == NI){
            return ($this->isRole(AR, $project) || 
                    $this->isRole(CI, $project) ||
                    $this->isRole(PL, $project));
        }
        if($role == NI.'-Candidate'){
            return ($this->isRole(AR.'-Candidate', $project) || 
                    $this->isRole(CI.'-Candidate', $project) ||
                    $this->isRole(PL.'-Candidate', $project));
        }
        if($role == 'Former-'.NI){
            return ($this->isRole('Former-'.AR, $project) || 
                    $this->isRole('Former-'.CI, $project) ||
                    $this->isRole('Former-'.PL, $project));
        }
        if($role == APL){
            $leadership = $this->leadership(false, true, 'Administrative');
            if($project != null){
                foreach($leadership as $proj){
                    if($proj == $project->getId()){
                        return true;
                    }
                }
            }
            else if(count($leadership) > 0){
                return true;
            }
            return false;
        }
        if($role == TL){
            return ($project != null) ? $this->isThemeLeaderOf($project) : $this->isThemeLeader();
        }
        if($role == TC){
            return ($project != null) ? $this->isThemeCoordinatorOf($project) : $this->isThemeCoordinator();
        }
        if($role == EVALUATOR){
            return $this->isEvaluator();
        }
        $roles = array();
        $role_objs = $this->getRoles();
        
        if(strstr($role, "Former-") !== false){
            return ($this->isRoleDuring(str_replace("Former-", "", $role), "0000-00-00", date('Y-m-d'), $project) && !$this->isRole(str_replace("Former-", "", $role), $project));
        }
        
        if(count($role_objs) > 0){
            $defaultSkip = false;
            foreach($role_objs as $r){
                if($project != null && count($r->getProjects()) > 0){
                    $defaultSkip = true;
                }
            }
            foreach($role_objs as $r){
                $skip = $defaultSkip;
                if($project != null && count($r->getProjects()) > 0){
                    $skip = true;
                    foreach($r->getProjects() as $p){
                        if($p != null && $p->getId() == $project->getId()){
                            $skip = false;
                            break;
                        }
                        foreach($p->getAllPreds() as $pred){
                            if($pred->getId() == $project->getId()){
                                $skip = false;
                                break 2;
                            }
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
                    $this->isRoleOn(PL, $date, $project));
        }
        if($role == NI.'-Candidate'){
            return ($this->isRoleOn(AR.'-Candidate', $date, $project) || 
                    $this->isRoleOn(CI.'-Candidate', $date, $project) ||
                    $this->isRoleOn(PL.'-Candidate', $date, $project));
        }
        $roles = array();
        $role_objs = $this->getRolesOn($date);
        if(count($role_objs) > 0){
            $defaultSkip = false;
            foreach($role_objs as $r){
                if($project != null && count($r->getProjects()) > 0){
                    $defaultSkip = true;
                }
            }
            foreach($role_objs as $r){
                $skip = $defaultSkip;
                if($project != null && count($r->getProjects()) > 0){
                    $skip = true;
                    foreach($r->getProjects() as $p){
                        if($p->getId() == $project->getId()){
                            $skip = false;
                            break;
                        }
                        foreach($p->getAllPreds() as $pred){
                            if($pred->getId() == $project->getId()){
                                $skip = false;
                                break 2;
                            }
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
                    $this->isRoleDuring(PL, $startRange, $endRange, $project));
        }
        if($role == NI.'-Candidate'){
            return ($this->isRoleDuring(AR.'-Candidate', $startRange, $endRange, $project) || 
                    $this->isRoleDuring(CI.'-Candidate', $startRange, $endRange, $project) ||
                    $this->isRoleDuring(PL.'-Candidate', $startRange, $endRange, $project));
        }
        $roles = array();
        $role_objs = $this->getRolesDuring($startRange, $endRange);
        if($role == TL){
            if($this->isThemeLeaderDuring($startRange, $endRange)){
                $roles[] = TL;
            }
        }
        if($role == TC){
            if($this->isThemeCoordinatorDuring($startRange, $endRange)){
                $roles[] = TC;
            }
        }
        if(count($role_objs) > 0){
            $defaultSkip = false;
            foreach($role_objs as $r){
                if($project != null && count($r->getProjects()) > 0){
                    $defaultSkip = true;
                }
            }
            foreach($role_objs as $r){
                $skip = $defaultSkip;
                if($project != null && count($r->getProjects()) > 0){
                    $skip = true;
                    foreach($r->getProjects() as $p){
                        if($p->getId() == $project->getId()){
                            $skip = false;
                            break;
                        }
                        foreach($p->getAllPreds() as $pred){
                            if($pred->getId() == $project->getId()){
                                $skip = false;
                                break 2;
                            }
                        }
                    }
                }
                if(!$skip){
                    $roles[] = $r->getRole();
                }
            }
        }
        $year = substr($startRange, 0, 4);
        if($role == EVALUATOR && $this->isEvaluator($year)){
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
                    $this->isRoleAtLeastDuring(PL, $startRange, $endRange));
        }
        if($role == NI.'-Candidate'){
            return ($this->isRoleAtLeastDuring(AR.'-Candidate', $startRange, $endRange) || 
                    $this->isRoleAtLeastDuring(CI.'-Candidate', $startRange, $endRange) ||
                    $this->isRoleAtLeastDuring(PL.'-Candidate', $startRange, $endRange));
        }
        if($this->isCandidate() && strstr($role, "-Candidate") === false){
            return false;
        }
        else{
            $role = str_replace("-Candidate", "", $role);
        }
        if($role == INACTIVE && !$this->isActive()){
            return true;
        }
        $roles = $this->getRolesDuring($startRange, $endRange);
        if($roles != null){
            foreach($roles as $r){
                if($r->getRole() != "" && $wgRoleValues[$r->getRole()] >= $wgRoleValues[$role]){
                    return true;
                }
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
                    $this->isRoleAtLeast(PL));
        }
        if($role == NI.'-Candidate'){
            return ($this->isRoleAtLeast(AR.'-Candidate') || 
                    $this->isRoleAtLeast(CI.'-Candidate') ||
                    $this->isRoleAtLeast(PL.'-Candidate'));
        }
        $me = Person::newFromWgUser();
        if($this->isCandidate() && strstr($role, "-Candidate") === false){
            return false;
        }
        else{
            $role = str_replace("-Candidate", "", $role);
        }
        if($role == INACTIVE && !$this->isActive()){
            return true;
        }
        if($this->getRoles() != null){
            foreach($this->getRoles() as $r){
                if($r->getRole() != "" && $wgRoleValues[$r->getRole()] >= $wgRoleValues[$role]){
                    return true;
                }
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
                    $this->isRoleAtMost(PL));
        }
        if($role == NI.'-Candidate'){
            return ($this->isRoleAtMost(AR.'-Candidate') || 
                    $this->isRoleAtMost(CI.'-Candidate') ||
                    $this->isRoleAtMost(PL.'-Candidate'));
        }
        if($this->isCandidate() && strstr($role, "-Candidate") === false){
            return false;
        }
        else{
            $role = str_replace("-Candidate", "", $role);
        }
        if($role == INACTIVE && !$this->isActive()){
            return true;
        }
        foreach($this->getRoles() as $r){
            if($r->getRole() != "" && $wgRoleValues[$r->getRole()] > $wgRoleValues[$role]){
                return false;
            }
        }
        return true;
    }
    
    /**
     * Returns whether or not this Person is an EPIC HQP (for AGE-WELL)
     * @return boolean Whether or not this Person is an EPIC HQP
     */
    function isEpic(){
        $position = strtolower($this->getPosition());
        return ($position == "graduate student - doctoral" ||
                $position == "graduate student - master's" ||
                $position == "post-doctoral fellow" ||
                $position == "medical student" ||
                $this->isSubRole("Affiliate HQP") || 
                $this->isSubRole("Project Funded HQP") ||
                $this->isSubRole("WP/CC Funded HQP") ||
                $this->isSubRole("SIP/CAT HQP") ||
                $this->isSubRole("Alumni HQP") ||
                $this->isSubRole("EPIC grad"));
    }
    
    function isEpic2(){
        $date = "2020-09-01";
        $university = $this->getUniversity();
        $position = $university['position'];
        $uniDate = $university['date'];
        if($this->isSubRoleBefore("Affiliate HQP", $date) || 
           $this->isSubRoleBefore("Project Funded HQP", $date) ||
           $this->isSubRoleBefore("WP/CC Funded HQP", $date) ||
           $this->isSubRoleBefore("SIP/CAT HQP", $date) ||
           $this->isSubRoleBefore("Award HQP", $date) ||
           $this->isSubRoleBefore("Alumni HQP", $date) ||
           $this->isSubRoleBefore("EPIC grad", $date)){
            return false;
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
            $person = Person::newFromName($row['wpName']);
            if($person->getId() > 0){
                $members[] = $person;
            }
        }
        return $members;
    }
    
    function getPeopleRelatedTo($type){
        $people = array();
        $data = DBFunctions::select(array('grand_relations'),
                                    array('user2'),
                                    array('user1' => EQ($this->id),
                                          WHERE_AND('user1') => NEQ(COL('user2')),
                                          WHERE_AND('type') => EQ($type)));
        foreach($data as $row){
            $person = Person::newFromId($row['user2']);
            if($person != null && $person->getId() != 0){
                $people[$person->getId()] = Person::newFromId($row['user2']);
            }
        }
        return $people;
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
        if($this->hqps !== null){
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
     * Returns this Person's HQP during the given dates
     * @param string $startRange The start date
     * @param string $endRange The end date
     * @return array This Person's HQP
     */
    function getHQPDuring($startRange, $endRange){
        $startRange = cleanDate($startRange);
        $endRange = cleanDate($endRange);
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
        $startRange = cleanDate($startRange);
        $endRange = cleanDate($endRange);
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
        if($person instanceof Person){
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
     * Returns an array of start/end dates for when the given supervisor supervised this Person
     * @param Person $supervisor The Person that supervised this Person
     * @return array The start/end dates of the relation(s)
     */
    function getSupervisorDates($supervisor){
        $dates = array();
        $relations = $supervisor->getRelations(SUPERVISES, true);
        foreach($relations as $relation){
            if($relation->getUser2()->getId() == $this->getId()){
                $dates[] = array('start'  => $relation->getStartDate(), 
                                 'end'    => $relation->getEndDate());
            }
        }
        return $dates;
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
        global $config;
        $me = Person::newFromWgUser();
        self::generateAuthorshipCache();
        $processed = array();
        $papersArray = array();
        $papers = array();
        if($config->getValue("includeHQPProducts")){
            foreach($this->getHQP($history) as $hqp){
                $ps = $hqp->getPapers($category, $history, $grand, $onlyPublic, $access);
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
            if($paper->getId() !== $pId){
                continue;
            }
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
        global $config;
        self::generateAuthorshipCache();
        $processed = array();
        $papersArray = array();
        $papers = array();
        if($includeHQP && $config->getValue("includeHQPProducts")){
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
     * Returns an array of this Person's Bibliographies
     * @return array The array of this Person's Bibliographies
     */
    function getBibliographies(){
        $data = DBFunctions::select(array('grand_bibliography'),
                                    array('id'),
                                    array('person_id' => $this->getId()));
        $bibs = array();
        foreach($data as $row){
            $bibs[] = Bibliography::newFromId($row['id']);
        }
        return $bibs;
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
            $res = DBFunctions::execSQL("SELECT rp.project_id as id
                                         FROM grand_roles r, grand_role_projects rp
                                         WHERE rp.role_id = r.id
                                         AND r.role = '".PL."'
                                         AND r.user_id = '{$this->id}'");
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
        $startRange = cleanDate($startRange);
        $endRange = cleanDate($endRange);
        if(isset($this->leadershipCache[$startRange.$endRange])){
            return $this->leadershipCache[$startRange.$endRange];
        }
        $sql = "SELECT DISTINCT rp.project_id
                FROM grand_roles r, grand_role_projects rp
                WHERE rp.role_id = r.id
                AND r.role = '".PL."'
                AND r.user_id = '{$this->id}'
                AND ( 
                ( (r.end_date != '0000-00-00 00:00:00') AND
                (( r.start_date BETWEEN '$startRange' AND '$endRange' ) || ( r.end_date BETWEEN '$startRange' AND '$endRange' ) || (r.start_date <= '$startRange' AND r.end_date >= '$endRange') ))
                OR
                ( (r.end_date = '0000-00-00 00:00:00') AND
                ((r.start_date <= '$endRange')))
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
                if($project != null){
                    $projects[$project->getName()] = $project;
                }
            }
            ksort($projects);
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
        return $alloc;
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
                $project = Project::newFromId($row['sub_id']);
                if($project != null){
                    $subs[] = $project;
                }
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
        
        $sql = "SELECT DISTINCT sub_id, sub2_id
                FROM grand_eval
                WHERE type = '$type'
                AND year = '{$year}'";
        $data = DBFunctions::execSQL($sql);
        $subs = array();
        foreach($data as $row){
            if($type != "Project" && 
               $type != "SAB" && $class != "Project"){
                $sub = Person::newFromId($row['sub_id']);
                if($sub != null && $sub->getId() != 0){
                    $subs[] = array($sub, $row['sub2_id']);
                }
            }
            else{
                $sub = Project::newFromId($row['sub_id']);
                if($sub != null && $sub->getId() != 0){
                    $subs[] = array($sub, $row['sub2_id']);
                }
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
                if($project != null && $project->getId() != 0){
                    $subs[$project->getName()."_".$row['sub2_id']] = array($project, $row['sub2_id']);
                }
            }
            else{
                $person = Person::newFromId($row['sub_id']);
                if($person != null && $person->getId() != 0){
                    $subs[$person->getReversedName()."_".$row['sub2_id']] = array($person, $row['sub2_id']);
                }
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
     * @param string $projId The id of the project (optional)
     * @return array The list of People who are evaluating this Person
     */
    function getEvaluators($year = YEAR, $type='Researcher', $projId=0){
        $data = DBFunctions::select(array('grand_eval'),
                                    array('*'),
                                    array('sub_id' => EQ($this->id),
                                          'sub2_id' => EQ($projId),
                                          'type' => EQ($type),
                                          'year' => EQ($year)));
        $subs = array();
        foreach($data as $row){
            $subs[] = Person::newFromId($row['user_id']);
        }
        return $subs;
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
                        if ($con_pers == $this->id){
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
    
    function getMetrics(){
        $metrics = PersonMetrics::getUserMetric($this->id);
        return $metrics;
    }
    
    /**
     * Returns the birth date of this Person
     * @return string The birth date of this Person
     */
    function getBirthDate(){
        $me = Person::newFromWgUser();
        if($me->isAllowedToEditDemographics($this)){
            return $this->birthDate;
        }
        return "";
    }
    
    /**
     * Returns This Person's pronouns
     * @return string This Person's pronouns
     */
    function getPronouns(){
        $me = Person::newFromWgUser();
        if($me->isAllowedToEditDemographics($this)){
            return $this->pronouns;
        }
        return "";
    }
    
    /**
     * Returns whether the Person identifies as indigenous 
     * @return string The indigenous status of this person
     */
    function getIndigenousStatus(){
        $me = Person::newFromWgUser();
        if($me->isAllowedToEditDemographics($this)){
            return $this->indigenousStatus;
        }
        return "";
    }
    
    /**
     * Returns the if this Person has a disability
     * @return string The disability status of this Person
     */
    function getDisabilityStatus(){
        $me = Person::newFromWgUser();
        if($me->isAllowedToEditDemographics($this)){
            return $this->disabilityStatus;
        }
        return "";
    }
    
    /**
     * Returns the if this Person is a visible minority
     * @return string The minority status of this Person
     */
    function getMinorityStatus(){
        $me = Person::newFromWgUser();
        if($me->isAllowedToEditDemographics($this)){
            return $this->minorityStatus;
        }
        return "";
    }
    
    /**
     * Returns This Person's ethnicity
     * @return string This Person's ethnicity
     */
    function getEthnicity(){
        $me = Person::newFromWgUser();
        if($me->isAllowedToEditDemographics($this)){
            return $this->ethnicity;
        }
        return "";
    }
    
    /**
     * Returns This Person's extra
     * @return string This Person's extra
     * TODO: This should probably be expanded to also include other fields
     * like the crc, ecr etc. to slim down the number of random fields in the user model
     */
    function getExtra($field=null, $default=""){
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            if($field != null){
                return isset($this->extra[$field]) ? $this->extra[$field] : $default;
            }
            return $this->extra;
        }
        else{
            return array();
        }
    }

    /**
     * Returns all resources that are clipped by user
     * @return json of resources
     */
    function getClipboard(){
        if($this->clipboard == null){
            $clipboard = array();
            $data = DBFunctions::select(array('grand_clipboard'),
                                        array('*'),
                                        array('user_id' => EQ($this->id)));
            if(count($data) > 0){
                $clipboard['id'] = $data[0]['id'];
                $clipboard['user_id'] = $data[0]['user_id'];
                $clipboard['objs'] = json_decode($data[0]['json_objs'], TRUE);
                $clipboard['date'] = $data[0]['date_created'];
            }
            else {
                return array();
            }
        }
        $this->clipboard = $clipboard;
        return $this->clipboard;
    }

    function saveClipboard($arr){
        $data = DBFunctions::select(array('grand_clipboard'),
                                    array('*'),
                                    array('user_id' => EQ($this->id)));
        if(count($data)==0){
            DBFunctions::insert('grand_clipboard',
                                array('user_id' => $this->id,
                                      'json_objs' => json_encode($arr)));
        }
        else{
            $status = DBFunctions::update('grand_clipboard',
                                          array('json_objs' => json_encode($arr)),
                                          array('user_id' => EQ($this->id)));
        }
        DBFunctions::commit();
        return true;
    }

}
if(isset($facultyMap)){
    Person::$facultyMap = array_flip(array_flatten($facultyMap));
    foreach(Person::$facultyMap as $key => $val){
        $exploded = explode(".", $val);
        Person::$facultyMap[$key] = $exploded[0];
    }
}
?>
