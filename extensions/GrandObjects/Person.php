<?php

/**
 * @package GrandObjects
 */
 
@include_once("facultyMap.php");

class Person extends BackboneModel {

    static $userRows = array();
    static $cache = array();
    static $rolesCache = array();
    static $universityCache = array();
    static $allUniversityCache = array();
    static $allUniversityCacheDone = false;
    static $aliasCache = array();
    static $authorshipCache = array();
    static $employeeIdCache = array();
    static $disciplineMap = array();
    static $allPeopleCache = array();
    static $subRoleCache = array();
    static $fecInfoCache = array();
    static $salaryCache = array();
    static $personSalaryCache = array();
    static $facultyMap = array();
    
    static $studentPositions = array('msc'   => array("msc",
                                                      "m.sc.",
                                                      "msee",
                                                      "msc student",
                                                      "m.sc. student",
                                                      "master of science",
                                                      "graduate student - master's course",
                                                      "graduate student - master's thesis", 
                                                      "graduate student - master's", 
                                                      "graduate student - master&#39;s course",
                                                      "graduate student - master&#39;s thesis",
                                                      "graduate student - master&#39;s",
                                                      "meng",
                                                      "graduate student - master of engineering"),
                                     'msc-thesis' => array("msc",
                                                           "m.sc.",
                                                           "msee",
                                                           "msc student",
                                                           "m.sc. student",
                                                           "master of science",
                                                           "graduate student - master's thesis", 
                                                           "graduate student - master's", 
                                                           "graduate student - master&#39;s thesis",
                                                           "graduate student - master&#39;s",
                                                           "meng",
                                                           "graduate student - master of engineering"), 
                                     'msc-course' => array("graduate student - master's course",
                                                           "graduate student - master&#39;s course"),
                                     'phd'   => array("phd",
                                                      "ph.d.",
                                                      "phd student",
                                                      "ph.d. student",
                                                      "graduate student - doctoral",
                                                      "dvm"),
                                     'pdf'   => array("pdf",
                                                      "post-doctoral fellow",
                                                      "postdoctoral fellow",
                                                      "research associate"),
                                     'tech'  => array("technical assistant",
                                                      "ra",
                                                      "research assistant",
                                                      "research/technical assistant", 
                                                      "research internship",
                                                      "professional end user",
                                                      "staff"),
                                     'ugrad' => array("bsc",
                                                      "b.sc.",
                                                      "bsc student",
                                                      "b.sc. student",
                                                      "bsc thesis",
                                                      "b.sc. thesis",
                                                      "bachelors of science",
                                                      "ugrad",
                                                      "undergraduate",
                                                      "undergraduate student",
                                                      "honors thesis",
                                                      "honor's thesis",
                                                      "honor&#39;s thesis",
                                                      "bachelor of science (honours)",
                                                      "b.sc. (hons)",
                                                      "b.sc. honours",
                                                      "bsc (honours)",
                                                      "undergraduate, honours",
                                                      "diplomaed engineer (dipl.ing.)",
                                                      "diplomaed engineer"),
                                     'visiting' => array("visiting student"),
                                     'other' => array("other",
                                                      "high school student"),
                                     'committee' => array("committee")
                                    );

    var $user = null;
    var $name;
    var $employeeId;
    var $email;
    var $realname;
    var $firstName;
    var $lastName;
    var $middleName;
    var $university;
    var $universityDuring;
    var $roles;
    var $rolesDuring;
    var $aliases = false;
    var $roleHistory;
    var $splitName = array();
    var $full;
    
    /**
     * Returns a new Person from the given id
     * @param int $id The id of the person
     * @return Person The Person from the given id
     */
    static function newFromId($id){
        if(isset(self::$cache[$id])){
            return self::$cache[$id];
        }

        $data = self::getUserRow($id);
        if(isset($data['full']) && $data['full'] == true){
            $person = new FullPerson(array($data));
        }
        else {
            $person = new LimitedPerson(array($data));
        }
        self::$cache[$person->id] = $person;
        self::$cache['eId'.$person->employeeId] = $person;
        self::$cache[strtolower($person->name)] = $person;
        return $person;
    }
    
    /**
     * Returns a new Person from the given emplyee id
     * @param int $id The empoyee id of the person
     * @return Person The Person from the given id
     */
    static function newFromEmployeeId($id){
        $id = ltrim($id, '0');
        if(isset(self::$cache['eId'.$id])){
            return self::$cache['eId'.$id];
        }
        self::generateEmployeeIdCache();
        $data = array();
        if(isset(self::$employeeIdCache[$id])){
            $data[] = self::getUserRow(self::$employeeIdCache[$id]);
        }

        if(isset($data[0]['full']) && $data[0]['full'] == true){
            $person = new FullPerson($data);
        }
        else {
            $person = new LimitedPerson($data);
        }
        
        self::$cache[$person->id] = $person;
        self::$cache['eId'.$person->employeeId] = $person;
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
        $namesCache = DBFunctions::select(array('grand_names_cache'),
                                          array('user_id'),
                                          array('name' => EQ($name)));
        $data = array();
        if(count($namesCache) > 0){
            $data[] = self::getUserRow($namesCache[0]['user_id']);
        }
        
        if(isset($data[0]['full']) && $data[0]['full'] == true){
            $person = new FullPerson($data);
        }
        else {
            $person = new LimitedPerson($data);
        }
        
        self::$cache[$person->id] = $person;
        self::$cache['eId'.$person->employeeId] = $person;
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
                                    array('user_email' => strtolower($email)));
        if(count($data) > 0){
            return Person::newFromId($data[0]['user_id']);
        }
        else{
            return new LimitedPerson(array());
        }
    }
    
    /**
     * Returns a new Person from the given orcid (null if not found)
     * In the event of a collision, the first user is returned
     * NOTE: this might be slow since orcid is not a table index
     * @param string $email The email address of the Person
     * @return Person The Person from the given email
     */
    static function newFromOrcid($orcid){
        $orcid = trim(str_replace("http://orcid.org/", "", 
                      str_replace("https://orcid.org/", "", $orcid)));
        $data = DBFunctions::select(array('mw_user'),
                                    array('user_id'),
                                    array('orcid' => $orcid));
        if(count($data) > 0){
            return Person::newFromId($data[0]['user_id']);
        }
        else{
            return new LimitedPerson(array());
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
     * @param boolean $multiple Whether to return multiple people
     * @return Person the Person that matches the name
     */
    static function newFromNameLike($name, $multiple=false){
        $name = Person::cleanName($name);
        $name = unaccentChars(strtolower($name));
        if(isset(Person::$cache[$name]) && !$multiple){
            return Person::$cache[$name];
        }
        $namesCache = DBFunctions::select(array('grand_names_cache'),
                                          array('user_id'),
                                          array('name' => EQ($name)));
        if(count($namesCache) > 0){
            if(!$multiple){
                $person = Person::newFromId($namesCache[0]['user_id']);
                Person::$cache[$name] = $person;
                return $person;
            }
            else{
                $people = array();
                foreach($namesCache as $row){
                    $people[] = Person::newFromId($row['user_id']);
                }
                return $people;
            }
        }
        return array();
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

        if (isset(self::$cache[$alias])) {
             return self::$cache[$alias];
        }
        else {
            self::generateAliasCache();
            $aliases = self::$aliasCache;
            if(isset($aliases[$alias])){
                $data = array(self::getUserRow($aliases[$alias]));
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
            if (isset(self::$cache[$id])) {
                // Mark this alias too.
                self::$cache[$alias] = self::$cache[$id];
                return self::$cache[$id];
            }

            if(isset($data[0]['full']) && $data[0]['full'] == true){
                $person = new FullPerson($data);
            }
            else {
                $person = new LimitedPerson($data);
            }

            self::$cache[$alias] = &$person;
            self::$cache[$person->getId()] = &$person;
            self::$cache[$person->getName()] = &$person;
            return $person;
        default:
            throw new DomainException("Alias is not unique.");
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
        $name = str_replace(" And ", "", $name);
        $name = str_replace(" and ", "", $name);
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
     * Returns a cached user row
     */
    static function getUserRow($id){
        if($id == 0){
            return array();
        }
        if(isset(self::$userRows[$id])){
            // In the static variable
        }
        else if(Cache::exists("mw_user_{$id}")){
            // In APC
            self::$userRows[$id] = Cache::fetch("mw_user_{$id}");
        }
        else {
            // Not loaded yet
            $keys = array_keys(self::$userRows);
            self::$userRows[$id] = array(); // This is to make sure that this doesn't get called too many times
            $result = DBFunctions::rawSelect(array('mw_user'),
                                             array('user_id',
                                                   'user_name',
                                                   'user_real_name',
                                                   'first_name',
                                                   'middle_name',
                                                   'last_name',
                                                   'employee_id',
                                                   'user_email',
                                                   'user_twitter',
                                                   'user_website',
                                                   'google_scholar_url',
                                                   'sciverse_id',
                                                   'orcid',
                                                   'wos',
                                                   'alex_id',
                                                   'user_public_profile',
                                                   'profile_start_date',
                                                   'profile_end_date',
                                                   'full',
                                                   'deleted'),
                                             array('user_id' => NOT_IN($keys)));
            while($row = mysqli_fetch_array($result->result, MYSQLI_ASSOC)){
                $userId = $row['user_id'];
                if(!$row['full']){
                    unset($row['user_twitter']);
                    unset($row['user_website']);
                    unset($row['google_scholar_url']);
                    unset($row['sciverse_id']);
                    unset($row['orcid']);
                    unset($row['wos']);
                    unset($row['alex_id']);
                    unset($row['user_public_profile']);
                    unset($row['profile_start_date']);
                    unset($row['profile_end_date']);
                }
                if($row['deleted'] == 1){
                    $row = array();
                }
                Cache::store("mw_user_{$userId}", $row);
                if(!empty($row) && (php_sapi_name() === "cli" || $userId == $id)){
                    self::$userRows[$userId] = $row;
                }
            }
        }
        return (isset(self::$userRows[$id])) ? self::$userRows[$id] : array();
    }

    /**
     * Caches the resultset of the user table for superfast access
     */
    static function generateEmployeeIdCache(){
        if(empty(self::$employeeIdCache)){
            $data = DBFunctions::select(array('mw_user'),
                                        array('user_id', 'employee_id'),
                                        array('deleted' => NEQ(1)));
            foreach($data as $row){
                if($row['employee_id'] != 0 && $row['employee_id'] != ""){
                    self::$employeeIdCache[$row['employee_id']] = $row['user_id'];
                }
            }
        }
    }
    
    static function getSalaryIncrement($year, $type){
        if(!isset(self::$salaryCache["increment_{$type}_{$year}"])){
            $increment = DBFunctions::select(array('grand_salary_scales'),
                                             array("increment_$type"),
                                             array('year' => $year));
            self::$salaryCache["increment_{$type}_{$year}"] = @$increment[0]["increment_$type"];
        }
        return self::$salaryCache["increment_{$type}_{$year}"];
    }
    
    static function getMinSalary($year, $type){
        if(!isset(self::$salaryCache["min_salary_{$type}_{$year}"])){
            $increment = DBFunctions::select(array('grand_salary_scales'),
                                             array("min_salary_$type"),
                                             array('year' => $year));
            self::$salaryCache["min_salary_{$type}_{$year}"] = @$increment[0]["min_salary_$type"];
        }
        return self::$salaryCache["min_salary_{$type}_{$year}"];
    }
    
    static function getMaxSalary($year, $type){
        if(!isset(self::$salaryCache["max_salary_{$type}_{$year}"])){
            $increment = DBFunctions::select(array('grand_salary_scales'),
                                             array("max_salary_$type"),
                                             array('year' => $year));
            self::$salaryCache["max_salary_{$type}_{$year}"] = @$increment[0]["max_salary_$type"];
        }
        return self::$salaryCache["max_salary_{$type}_{$year}"];
    }
    
    function updateNamesCache(){
        $exploded = explode(".", unaccentChars($this->name));
        $firstName = ($this->firstName != "") ? unaccentChars($this->firstName) : @$exploded[0];
        $lastName = ($this->lastName != "") ? unaccentChars($this->lastName) : @$exploded[1];
        $middleName = unaccentChars($this->middleName);
        $keys = array(
            strtolower($this->name),
            strtolower(str_replace(".", " ", $this->name)),
            strtolower("$firstName $lastName"),
            strtolower("$lastName $firstName"),
            strtolower("$firstName ".substr($lastName, 0, 1)),
            strtolower("$lastName ".substr($firstName, 0, 1)),
            strtolower(substr($firstName, 0, 1)." $lastName")
        );
        $splitLastNames = explode(" ", $lastName);
        $splitFirstNames = explode(" ", $firstName);
        //if(count($splitLastNames) > 1){
            // User has multiple last names
            foreach($splitLastNames as $last){
                foreach($splitFirstNames as $first){
                    $keys[] = "$first $last";
                    $keys[] = strtolower(substr($first, 0, 1)." $last");
                }
            }
        //}
        if(trim($this->realname) != '' && $this->name != trim($this->realname)){
            $keys[] = strtolower(substr($firstName, 0, 1)." $lastName");
            $keys[] = strtolower(trim(unaccentChars($this->realname)));
        }
        if($middleName != ""){
            $keys[] = strtolower(substr($firstName, 0, 1)." $middleName $lastName");
            $keys[] = strtolower("$firstName $middleName $lastName");
            $keys[] = strtolower("$firstName ".substr($middleName, 0, 1)." $lastName");
            $keys[] = strtolower("$lastName ".substr($firstName, 0, 1).substr($middleName, 0, 1));
            $explodedMiddle = explode("-", $middleName, 2);
            if(count($explodedMiddle) > 1){
                $keys[] = strtolower("$firstName ".substr($explodedMiddle[0], 0, 1)."-".substr($explodedMiddle[1], 0, 1)." $lastName");
            }
        }
        foreach($keys as $i => $key){
            $keys[$i] = trim($key);
        }
        $keys = array_unique($keys);
        $inserts = array();
        foreach($keys as $key){
            if(trim($key) != ""){
                $inserts[] = "('".DBFunctions::escape($key)."','".DBFunctions::escape($this->getId())."')";
            }
        }
        DBFunctions::delete('grand_names_cache',
                            array('user_id' => $this->getId()));
        DBFunctions::execSQL("INSERT INTO `grand_names_cache` (`name`, `user_id`) VALUES ".implode(", ", $inserts), true);
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
                $sql = "SELECT r.id, r.user_id, r.role
                        FROM grand_roles r, mw_user u
                        WHERE (end_date = '0000-00-00 00:00:00'
                               OR end_date > CURRENT_TIMESTAMP)
                        AND start_date <= CURRENT_TIMESTAMP
                        AND r.user_id = u.user_id
                        AND u.deleted = 0";
                $data = DBFunctions::execSQL($sql);
                if(count($data) > 0){
                    foreach($data as $row){
                        self::$rolesCache[$row['user_id']][$row['id']] = $row['role'];
                    }
                }
                foreach(self::$rolesCache as $id => $roles){
                    self::$rolesCache[$id] = serialize($roles);
                }
                Cache::store("rolesCache", self::$rolesCache);
            }
        }
    }
    
    /**
     * Caches the resultset of the user universities
     */
    static function generateUniversityCache(){
        if(empty(self::$universityCache)){
            $sql = "SELECT id, user_id, university_name, department, position, start_date, end_date, research_area, `primary`
                    FROM grand_user_university uu, grand_universities u, grand_positions p 
                    WHERE u.university_id = uu.university_id
                    AND uu.position_id = p.position_id
                    ORDER BY REPLACE(end_date, '0000-00-00 00:00:00', '9999-12-31 00:00:00') DESC";
            $result = DBFunctions::execSQL($sql, false, false, true);
            while($row = mysqli_fetch_array($result->result, MYSQLI_ASSOC)){
                if(!isset(self::$universityCache[$row['user_id']]) || $row['primary'] == true){
                    self::$universityCache[$row['user_id']] = 
                        array("id"         => $row['id'],
                              "university" => $row['university_name'],
                              "department" => $row['department'],
                              "position"   => $row['position'],
                              "primary"    => $row['primary'],
                              "start"      => $row['start_date'],
                              "date"       => $row['end_date'],
                              "research_area" => $row['research_area']);
                }
            }
        }
    }
    
    /**
     * Caches the resultset of the disciplines map
     */
    static function generateDisciplineMap(){
        if(empty(self::$disciplineMap)){
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
    static function generateAuthorshipCache($id="%"){
        if(!isset(self::$authorshipCache[$id])){
            self::$authorshipCache[$id] = array();
            $data = DBFunctions::select(array('grand_product_authors'),
                                        array('author', 'product_id'),
                                        array('author' => COL("REGEXP '[0-9]+'"),
                                              'author' => LIKE($id)));
            foreach($data as $row){
                self::$authorshipCache[$row['author']][] = $row['product_id'];
            }
        }
    }
    
    /**
     * Caches the partial resultset of the mw_user table
     */
    static function generateAllPeopleCache(){
        if(empty(self::$allPeopleCache)){
            if(Cache::exists("allPeopleCache")){
                self::$allPeopleCache = Cache::fetch("allPeopleCache");
            }
            else{
                $data = DBFunctions::select(array('mw_user'),
                                            array('user_id'),
                                            array('deleted' => NEQ(1)),
                                            array('user_name' => 'ASC'));
                foreach($data as $row){
                    self::$allPeopleCache[] = $row['user_id'];
                }
                Cache::store("allPeopleCache", self::$allPeopleCache);
            }
        }
    }
    
    /**
     * Filters people to only include people within the instance's faculty
     */
    static function filterFaculty($people, $includeLimitedPeople=false){
        $fecInfo = DBFunctions::select(array('grand_personal_fec_info'),
                                       array('user_id'),
                                       array('faculty' => getFaculty()));
        $fecInfo = array_flip(flatten($fecInfo));
        $ret = array();
        foreach($people as $key => $person){
            if($person instanceof FullPerson){
                if(isset($fecInfo[$person->getId()])){
                    $ret[$key] = $person;
                }
            }
            else if($includeLimitedPeople){
                $ret[$key] = $person;
            }
        }
        return $ret;
    }
    
    function inFaculty(){
        $this->getFecPersonalInfo();
        return ($this->faculty == getFaculty() || $this->faculty == "All");
    }
    
    function isTAEligible($date=null){
        if($date == null){
            $date = date('Y-m-d');
        }
        $date = substr($date, 0, 10);
        
        $universities = $this->getUniversitiesDuring($date, $date);
        
        foreach($universities as $university){
            $uniStart = new DateTime(substr($university['start'], 0, 10));
            $datetime = new DateTime($date);
            $interval = intval($uniStart->diff($datetime)->format('%a')); // Difference in days
            if(in_array(strtolower($university['position']), Person::$studentPositions['phd']) && $interval <= 365*3){
                return true;
            }
            else if(in_array(strtolower($university['position']), Person::$studentPositions['msc']) && $interval <= 365*2){
                return true;
            }
        }
        return false;
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
                                    array('position' => NEQ('')),
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
                if(isset($row['full']) && $row['full'] == true){
                    $person = new FullPerson(array($row));
                }
                else {
                    $person = new LimitedPerson(array($row));
                }
                self::$cache[$person->getId()] = $person;
                $people[$person->getId()] = $person;
            }
        }
        return $people;
    }
    
    static function getAllFullPeople(){
        $data = DBFunctions::select(array('mw_user'),
                                    array('user_id'),
                                    array('full' => 1,
                                          'deleted' => 0));
        foreach($data as $row){
            $person = Person::newFromId($row['user_id']);
            $people[strtolower($person->getName())] = $person;
        }
        return $people;
    }

    /**
     * Returns an array of People of the type $filter
     * @param string $filter The role to filter by
     * @param boolean $idOnly Whether or not to only include the id, rather than instantiating a Person object (may result in slightly different results!)
     * @return array The array of People of the type $filter
     */
    static function getAllPeople($filter=null, $idOnly=false, $shortCircuit=false){
        if($filter == NI){
            $ars = self::getAllPeople(AR, $idOnly, $shortCircuit);
            $cis = self::getAllPeople(CI, $idOnly, $shortCircuit);
            return array_merge($ars, $cis);
        }
        $me = Person::newFromWgUser();
        self::generateAllPeopleCache();
        self::generateRolesCache();
        $people = array();
        foreach(self::$allPeopleCache as $row){
            if($shortCircuit && !empty($people)){
                break;
            }
            if($filter == INACTIVE && !isset(self::$rolesCache[$row])){
                $person = Person::newFromId($row);
                if($idOnly){
                    $people[] = $row;
                }
                else{
                    $people[strtolower($person->getName())] = $person;
                }
            }
            if($filter == null || $filter == "all" || isset(self::$rolesCache[$row])){
                if($filter != null && $filter != "all"){
                    $found = false;
                    foreach(unserialize(self::$rolesCache[$row]) as $role){
                        if($role == $filter){
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
                $people[strtolower($person->getName())] = $person;
            }
        }
        ksort($people);
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
        if($filter != null && $filter != "all"){
            $peopleWithRole = array();
            $roleSQL = "role = '".DBFunctions::escape($filter)."'";
            if($filter == NI){
                $roleSQL = "role = '".DBFunctions::escape(CI)."' OR role = '".DBFunctions::escape(AR)."'";
            }
            $data = DBFunctions::execSQL(
                "SELECT DISTINCT user_id
                FROM grand_roles
                WHERE ($roleSQL) AND
                (
                    ((end_date != '0000-00-00 00:00:00') AND
                     ((start_date BETWEEN '$startRange' AND '$endRange') || 
                      (end_date BETWEEN '$startRange' AND '$endRange') || 
                      (start_date <= '$startRange' AND end_date >= '$endRange'))
                    )
                    OR
                    ((end_date = '0000-00-00 00:00:00') AND
                     ((start_date <= '$endRange'))
                    )
                )");
            foreach($data as $row){
                $peopleWithRole[$row['user_id']] = $row['user_id'];
            }
            foreach(self::$allPeopleCache as $row){
                if(isset($peopleWithRole[$row])){
                    $person = Person::newFromId($row);
                    $people[strtolower($person->getName())] = $person;
                }
            }
        }
        else{
            foreach(self::$allPeopleCache as $row){
                $person = Person::newFromId($row);
                $people[strtolower($person->getName())] = $person;
            }
        }
        ksort($people);
        return $people;
    }
    
    static function getAllPeopleInDepartment($department, $start, $end){
        $department = DBFunctions::escape($department);
        $sql = "SELECT DISTINCT uu.user_id FROM grand_user_university uu, mw_user u
                WHERE department = '$department'
                AND (
                    ( (end_date != '0000-00-00 00:00:00') AND
                    (( start_date BETWEEN '$start' AND '$end' ) || ( end_date BETWEEN '$start' AND '$end' ) || (start_date <= '$start' AND end_date >= '$end') ))
                    OR
                    ( (end_date = '0000-00-00 00:00:00') AND
                    ((start_date <= '$end')))
                )
                AND uu.user_id = u.user_id
                AND u.deleted != 1";
        $data = DBFunctions::execSQL($sql);
        $people = array();
        foreach($data as $row){
            $people[] = Person::newFromId($row['user_id']);
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
                $people[strtolower($person->getName())] = $person;
            }
        }
        ksort($people);
        return $people;
    }
    
    /**
     * Merges two People
     * @param Person $personToKeep The Person object to keep
     * @param Person $personToDelete The Person object to delete/merge into $personToKeep
     */
    static function merge($personToKeep, $personToDelete){
        if($personToKeep->getEmail() == ""){
            DBFunctions::update('mw_user',
                                array('user_email' => $personToDelete->getEmail()),
                                array('user_id' => $personToKeep->getId()));
        }
        if($personToKeep->getEmployeeId() == ""){
            DBFunctions::update('mw_user',
                                array('employee_id' => $personToDelete->getEmployeeId()),
                                array('user_id' => $personToKeep->getId()));
        }
        
        DBFunctions::update('grand_relations',
                            array('user2' => $personToKeep->getId()),
                            array('user2' => $personToDelete->getId()));
                            
        DBFunctions::update('grand_roles',
                            array('user_id' => $personToKeep->getId()),
                            array('user_id' => $personToDelete->getId()));
                            
        DBFunctions::update('grand_user_university',
                            array('user_id' => $personToKeep->getId()),
                            array('user_id' => $personToDelete->getId()));
                            
        DBFunctions::update('mw_user',
                            array('deleted' => 1),
                            array('user_id' => $personToDelete->getId()));
        $products = $personToDelete->getPapers("all", true, 'both', false, 'Public');
        foreach($products as $product){
            $changed = false;
            $authors = unserialize($product->authors);
            foreach($authors as $key => $author){
                if($author == $personToDelete->getId()){
                    $authors[$key] = $personToKeep->getId();
                    $changed = true;
                }
            }
            if($changed){
                DBFunctions::update('grand_products',
                                    array('authors' => serialize($authors)),
                                    array('id' => $product->getId()));
            }
        }
        Person::$cache = array();
        Person::$aliasCache = array();
        Person::$employeeIdCache = array();
        Person::$fecInfoCache = array();
        Cache::delete("allPeopleCache");
        Cache::delete("mw_user_{$personToKeep->getId()}");
        Cache::delete("mw_user_{$personToDelete->getId()}");
    }

    // Constructor
    // Takes in a resultset containing the 'user id' and 'user name'
    function Person($data){
        global $wgUser;
        if(!empty($data)){
            $this->id = @$data[0]['user_id'];
            $this->name = @$data[0]['user_name'];
            $this->realname = @$data[0]['user_real_name'];
            $this->firstName = @$data[0]['first_name'];
            $this->lastName = @$data[0]['last_name'];
            $this->middleName = @$data[0]['middle_name'];
            $this->employeeId = @$data[0]['employee_id'];
            $this->email = @$data[0]['user_email'];
            $this->full = @$data[0]['full'];
            $this->university = false;
        }
    }
    
    function toSimpleArray(){
        $json = array('id' => $this->getId(),
                      'name' => $this->getName(),
                      'email' => $this->getEmail(),
                      'fullName' => $this->getNameForForms());
        return $json;
    }
    
    function toSimpleJSON(){
        return json_encode($this->toSimpleArray());
    }
    
    function toArray(){
        global $wgUser;
        $roles = array();
        foreach($this->getRoles() as $role){
            if($role->getId() != -1){
                $roles[] = array('id' => $role->getId(),
                                 'role' => $role->getRole());
            }
        }
        $universities = $this->getUniversities();
        $university = $this->getUniversity();
        $json = array('id' => $this->getId(),
                      'name' => $this->getName(),
                      'realName' => $this->getRealName(),
                      'fullName' => $this->getNameForForms(),
                      'reversedName' => $this->getReversedName(),
                      'email' => $this->getEmail(),
                      'university' => $university['university'],
                      'department' => $university['department'],
                      'position' => $university['position'],
                      'start' => $university['start'],
                      'end' => $university['date'],
                      'researchArea' => $university['research_area'],
                      'universities' => $universities,
                      'roles' => $roles,
                      'url' => $this->getUrl());
        return $json;
    }
    
    function create(){
        global $wgRequest;
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(STAFF)){
            $user = User::createNew($this->name, array('real_name' => $this->realname, 
                                                       'password' => User::crypt(mt_rand()), 
                                                       'email' => $this->email));
            $this->id = $user->getId();
            $status = DBFunctions::update('mw_user', 
                                    array('employee_id' => $this->getEmployeeId(),
                                          'full' => $this->full),
                                    array('user_name' => EQ($this->getName())));
            DBFunctions::commit();
            $this->updateNamesCache();
            Person::$cache = array();
            Person::$aliasCache = array();
            Person::$userRows = array();
            Person::$fecInfoCache = array();
            Cache::delete("rolesCache");
            Cache::delete("allPeopleCache");
            Cache::delete("mw_user_{$this->getId()}");
            return true;
        }
        return false;
    }
    
    function update(){
        global $wgImpersonating, $wgDelegating;
        $me = Person::newFromWgUser();
        if($me->isAllowedToEdit($this)){
            $status = DBFunctions::update('mw_user', 
                                    array('user_name' => $this->getName(),
                                          'user_real_name' => $this->getRealName(),
                                          'first_name' => $this->getFirstName(),
                                          'middle_name' => $this->getMiddleName(),
                                          'last_name' => $this->getLastName(),
                                          'employee_id' => $this->getEmployeeId(),
                                          'full' => $this->full),
                                    array('user_id' => EQ($this->getId())));
            $this->updateNamesCache();
            Person::$cache = array();
            Person::$aliasCache = array();
            Person::$userRows = array();
            Person::$fecInfoCache = array();
            Cache::delete("rolesCache");
            Cache::delete("allPeopleCache");
            Cache::delete("mw_user_{$this->getId()}");
            return $status;
        }
        return false;
    }
    
    function delete(){
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(MANAGER)){
            DBFunctions::delete('grand_names_cache',
                                array('user_id' => EQ($this->getId())));
            Cache::delete("allPeopleCache");
            Cache::delete("mw_user_{$this->getId()}");
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
        if($person->isMe()){
            // User is themselves
            return true;
        }
        if($this->isRoleAtLeast(STAFF)){
            // User is at least Staff
            return true;
        }
        if($this->isRelatedToDuring($person, SUPERVISES_BOTH, "0000-00-00", "2100-00-00") ||
           $this->isRelatedToDuring($person, SUPERVISORY_COMMITTEE, "0000-00-00", "2100-00-00") ||
           $this->isRelatedToDuring($person, EXAMINER, "0000-00-00", "2100-00-00") ||
           $this->isRelatedToDuring($person, COMMITTEE_CHAIR, "0000-00-00", "2100-00-00")){
            // User has supervised the Person
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
     * Returns the id of this Person.  
     * Returns 0 if the user doesn't exist or if is an HQP and the current user is not logged in 
     * @return int The id of this Person
     */
    function getId(){
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
        return $this->getReversedName();
        return $this->realname;
    }
    
    /**
     * Returns the email address of this Person
     * @return string The email address of this Person
     */
    function getEmail(){
        return $this->email;
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
     * Returns the url of this Person's profile page
     * @return string The url of this Person's profile page
     */
    function getUrl(){
        global $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if($this->id > 0 && ($me->isLoggedIn() || $this->isRoleAtLeast(NI)) && (!isset($_GET['embed']) || $_GET['embed'] == 'false')){
            return "{$wgServer}{$wgScriptPath}/index.php/{$this->getType()}:{$this->getName()}";
        }
        else if($this->id > 0 && ($me->isLoggedIn() || $this->isRoleAtLeast(NI)) && isset($_GET['embed'])){
            return "{$wgServer}{$wgScriptPath}/index.php/{$this->getType()}:{$this->getName()}?embed";
        }
        return "";
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
        if(empty($this->splitName)){
            if(!empty($this->realname) && strstr($this->realname, ",") !== false){
                 $names = explode(",", $this->realname, 2);
                 $lastname = trim($names[0]);
                 $firstname = trim($names[1]);
            }
            else if(!empty($this->realname)){
                $names = explode(" ", $this->realname);
                $lastname = ucfirst($names[count($names)-1]);
                unset($names[count($names)-1]);
                $firstname = implode(" ", $names);
            }
            else if(strstr($this->name, ",") !== false){
                $names = explode(",", $this->name, 2);
                $lastname = trim($names[0]);
                $firstname = trim($names[1]);
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
            $this->splitName = array("first" => str_replace("&nbsp;", " ", ucfirst($firstname)), "last" => str_replace("&nbsp;", " ", ucfirst($lastname)));
        }
        return $this->splitName;
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
    
    function getFullName(){
        return "{$this->getFirstName()} {$this->getLastName()}";
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
        return $this->getReversedName();
        if (!empty($this->realname))
            return str_replace("\"", "<span class='noshow'>&quot;</span>", str_replace("&nbsp;", " ", ucfirst($this->realname)));
        else
            return str_replace("\"", "<span class='noshow'>&quot;</span>", trim($this->getFirstName()." ".$this->getLastName()));
    }
    
    private function formatName($matches){
        $firstName = $this->getFirstName();
        $middleName = str_replace(".","",$this->getMiddleName());
        $lastName = $this->getLastName();
        foreach($matches as $key => $match){
            $match1 = $match;
            $match2 = $match;
            
            $match1 = str_replace("%first", $firstName, $match1);
            $match1 = str_replace("%middle", $middleName, $match1);
            $match1 = str_replace("%last", $lastName, $match1);
            $match1 = str_replace("%f", substr($firstName, 0,1), $match1);
            $match1 = str_replace("%m", substr($middleName, 0,1), $match1);
            $match1 = str_replace("%l", substr($lastName,0,1), $match1);

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
        $regex = "/\{.*?\}/";
        $format = ($format == null) ? strtolower($config->getValue("nameFormat")) : strtolower($format);
        $format = preg_replace_callback($regex,"self::formatName",$format);
        $format = str_replace("\"", "<span class='noshow'>&quot;</span>", $format);
        if(str_replace(".", "", strtolower($format)) == "et al" || str_replace(".", "", strtolower($format)) == "al, et"){
            return "et al.";
        }
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
            self::$aliasCache = array();
        }
        return $aliases;
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
                         "position" => "",
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
                   $p->getType() == 'PHD Thesis'){
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
     * @return string The date that this Person's degree ended
     */
    function getDegreeReceivedDate($guess = true){
        $data = DBFunctions::select(array('grand_relations'),
                                    array('end_date'),
                                    array('user2' => EQ($this->getId()),
                                          'type' => LIKE('%Supervises%'),),
                                    array('end_date' => 'ASC'));
        if(DBFunctions::getNRows() > 0)
            return $data[0]['end_date'];
        return null;
    }
    
    function getStories(){
        $data = DBFunctions::select(array('grand_stories'),
                                    array('*'),
                                    array('user_id' => $this->getId()));
        $array = array();
        foreach($data as $row){
            $array[] = $row['paragraph'];
        }
        return $array;
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
     * Returns the name of the Department that this Person is at
     * @return string The name of the Department
     */
    function getResearchArea(){
        $university = $this->getUniversity();
        return (isset($university['research_area'])) ? $university['research_area'] : "";
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
        $hqp_pos = $this->getMovedOn();
        if(!empty($hqp_pos)){
            if($hqp_pos['studies'] !== '')
                $pos[] = $hqp_pos['studies'];
            if($hqp_pos['employer'] !== '')
                $pos[] = $hqp_pos['employer'];
            if($hqp_pos['city'] !== '')
                $pos[] = $hqp_pos['city'];
            if($hqp_pos['country'] !== '')
                $pos[] = $hqp_pos['country'];
        }
        if(count($pos) == 0){
            $hqp_pos = $this->getUniversity();
            if($hqp_pos['position'] !== '')
                $pos[] = $hqp_pos['position'];
            if($hqp_pos['department'] !== '')
                $pos[] = $hqp_pos['department'];
            if($hqp_pos['university'] !== '')
                $pos[] = $hqp_pos['university'];
        }
        return str_replace("&#39;", "'", implode(", ", $pos));
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
    
    /*
     * Returns an array of Universities that this Person is currently at
     * @return array The current Universities this Person is at
     */
    function getCurrentUniversities(){
        return $this->getUniversitiesDuring(date("Y-m-d H:i:s"), date("Y-m-d H:i:s"));
    }
    
    /**
     * Returns all the Universities that this Person was at between the given range
     * @param string $startRange The start date to look at
     * @param string $endRange The end date to look at
     * @return array The Universities that this Person was at between the given range
     */ 
    function getUniversitiesDuring($startRange, $endRange, $id=null){
        $id = ($id == null) ? $this->getId() : $id;
        $startRange = substr($startRange,0,10);
        $endRange = substr($endRange,0,10);
        
        // Fetch the rows from the db/cache
        if(!isset(self::$allUniversityCache[$id]) && !self::$allUniversityCacheDone){
            $data = array();
            if(count(self::$allUniversityCache) <= 500){
                if(Cache::exists("user_universities_{$id}")){
                    $data = Cache::fetch("user_universities_{$id}");
                }
                else{
                    $sql = "SELECT id, user_id, university_name, department, position, research_area, SUBSTR(start_date,1,10) as start_date, SUBSTR(end_date,1,10) as end_date
                            FROM grand_user_university uu, grand_universities u, grand_positions p
                            WHERE u.university_id = uu.university_id
                            AND uu.position_id = p.position_id
                            AND uu.user_id = '{$id}'
                            ORDER BY REPLACE(end_date, '0000-00-00', '9999-99-99') DESC, start_date DESC, id DESC";
                    $data = DBFunctions::execSQL($sql);
                    Cache::store("user_universities_{$id}", $data);
                }
            }
            else{
                if(Cache::exists("user_university")){
                    $data = Cache::fetch("user_university");
                }
                else{
                    $sql = "SELECT id, user_id, university_name, department, position, research_area, SUBSTR(start_date,1,10) as start_date, SUBSTR(end_date,1,10) as end_date
                            FROM grand_user_university uu, grand_universities u, grand_positions p
                            WHERE u.university_id = uu.university_id
                            AND uu.position_id = p.position_id
                            ORDER BY REPLACE(end_date, '0000-00-00', '9999-99-99') DESC, start_date DESC, id DESC";
                    $data = DBFunctions::execSQL($sql);
                    Cache::store("user_university", $data);
                }
                self::$allUniversityCache = array();
                self::$allUniversityCacheDone = true;
            }
            foreach($data as $row){
                if($row['university_name'] != "Unknown"){
                    self::$allUniversityCache[$row['user_id']][] = array("id" => $row['id'],
                                                                         "university" => $row['university_name'],
                                                                         "department" => $row['department'],
                                                                         "position" => $row['position'],
                                                                         "research_area" => $row['research_area'],
                                                                         "start" => $row['start_date'],
                                                                         "end" => $row['end_date']);
                }
            }
        }
        
        // Return the unis in the timeframe
        $unis = array();
        if(isset(self::$allUniversityCache[$id])){
            foreach(self::$allUniversityCache[$id] as $uni){
                if(($uni['end'] != "0000-00-00" && (($uni['start'] >= $startRange && $uni['start'] <= $endRange) || 
                                                    ($uni['end']   >= $startRange && $uni['end']   <= $endRange) ||
                                                    ($uni['start'] <= $endRange   && $uni['end']   >= $endRange))) || 
                   ($uni['end'] == "0000-00-00" && $uni['start'] <= $endRange)){
                    $unis[] = $uni;
                }
            }
        }
        return $unis;
    }
    
    function isInDepartment($department, $university, $startRange, $endRange){
        $unis = $this->getUniversitiesDuring($startRange, $endRange);
        foreach($unis as $uni){
            if($uni['department'] == $department && $uni['university'] == $university){
                return true;
            }
        }
        return false;
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
                ORDER BY REPLACE(end_date, '0000-00-00 00:00:00', '9999-99-99 99:99:99') DESC, start_date DESC, id DESC";
        $data = DBFunctions::execSQL($sql);
        $array = array();
        if(count($data) > 0){
            foreach($data as $row){
                $array[] = array("id" => $row['id'],
                                 "university" => $row['university_name'],
                                 "department" => $row['department'],
                                 "position"   => $row['position'],
                                 "researchArea" => $row['research_area'],
                                 "primary" => $row['primary'],
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
        if(!is_array($keywords)){
            $keywords = explode(",", $keywords);
        }
        $cacheId = "keywords_{$this->getId()}";
        DBFunctions::delete('grand_person_keywords',
                            array('user_id' => $this->getId()));
        
        foreach($keywords as $keyword){
            DBFunctions::insert('grand_person_keywords',
                                array('user_id' => $this->getId(),
                                      'keyword' => trim($keyword)));
        }
        Cache::delete($cacheId);
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
        return $maxRole;
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
                    $roles[] = new Role(array($row));
                }
            }
            return $roles;
        }
        self::generateRolesCache();
        if($this->roles == null && $this->id != null){
            $this->roles = array();
            if(isset(self::$rolesCache[$this->id])){
                foreach(unserialize(self::$rolesCache[$this->id]) as $id => $row){
                    $this->roles[] = Role::newFromId($id);
                    //$this->roles[] = new Role(array(0 => $row));
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
        else if($this->id == null){
            $this->roles = array();
        }
        return $this->roles;
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
                    $this->wasLastRole(CI));
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
                    $this->wasLastRoleAtLeast(CI));
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
                    $this->wasLastRoleAtMost(CI));
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
    
    static function generateSubRoleCache(){
        if(empty(self::$subRoleCache)){
            self::$subRoleCache[-1] = array();
            $data = DBFunctions::select(array('grand_role_subtype'),
                                        array('user_id', 'sub_role'));
            foreach($data as $row){
                self::$subRoleCache[$row['user_id']][] = $row['sub_role'];
            }
        }
    }
    
    /**
     * Returns an array of the subRoles that this Person is in
     * @return array The subRoles that this Person is in
     */
    function getSubRoles(){
        self::generateSubRoleCache();
        $roles = array();
        if(isset(self::$subRoleCache[$this->getId()])){
            return self::$subRoleCache[$this->getId()];
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
    
    /*
     * Returns a list of roles (strings) which this Person is allowed to edit
     * @return array A list of roles (string) which this Person is allowed to edit
     */
    function getAllowedRoles(){
        global $wgRoleValues, $wgRoles;
        $maxValue = 0;
        $roles = array();
        foreach($this->getRoles() as $role){
            $maxValue = max($maxValue, $wgRoleValues[$role->getRole()]);
        }
        foreach($wgRoleValues as $role => $value){
            if($value <= $maxValue && array_search($role, $wgRoles) !== false){
                $roles[$role] = $role;
            }
        }
        sort($roles);
        return $roles;
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
                                    array('uu.id', 'uu.user_id', 'u.university_name', 'uu.department', 'p.position', 'uu.research_area', 'uu.primary', 'uu.start_date', 'uu.end_date'),
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
                'researchArea' => $row['research_area'],
                'primary' => $row['primary'],
                'startDate' => substr($row['start_date'], 0, 10),
                'endDate' => substr($row['end_date'], 0, 10)
            );
        }
        return $universities;
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
                $people[$person->getReversedName().$person->getId()] = $person;
            } 
        }
        return $people;
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
     * Returns whether this Person is the same as $wgUser
     * @return boolean Whether this Person is the same as $wgUser
     */
    function isMe(){
        global $wgUser;
        return ($wgUser->getId() == $this->getId());
    }

    /**
     * Returns whether this Person is the given role
     * @param string $role The role of this Person
     * @return boolean Whether or not the Person is the given role
     */
    function isRole($role){
        if($role == NI){
            return ($this->isRole(AR) || 
                    $this->isRole(CI));
        }
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
        return (array_search($role, $roles) !== false);
    }
    
    /**
     * Returns whether this Person is the given role on the given date
     * @param string $role The role of this Person
     * @param string $data The date of the role
     * @return boolean Whether or not the Person is the given role
     */
    function isRoleOn($role, $date){
        if($role == NI){
            return ($this->isRoleOn(AR, $date) || 
                    $this->isRoleOn(CI, $date));
        }
        $roles = array();
        $role_objs = $this->getRolesDuring("1900-01-01", "2100-01-01");
        if(count($role_objs) > 0){
            foreach($role_objs as $r){
                $startDate = substr($r->getStartDate(), 0, 10);
                $endDate = substr($r->getEndDate(), 0, 10);
                if($r->getRole() == $role && (($date >= $startDate && $date <= $endDate) || 
                                              ($date >= $startDate && $endDate == "0000-00-00"))){
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Returns whether this Person is the given role between the given dates
     * @param string $role The role of this Person
     * @param string $startRange The start date
     * @param string $endRange The end date
     * @return boolean Whether or not the Person is the given role
     */
    function isRoleDuring($role, $startRange, $endRange){
        if($role == INACTIVE){
            return $this->exists();
        }
        if($role == NI){
            return ($this->isRoleDuring(AR, $startRange, $endRange) || 
                    $this->isRoleDuring(CI, $startRange, $endRange));
        }
        $roles = array();
        $role_objs = $this->getRolesDuring($startRange, $endRange);
        if(count($role_objs) > 0){
            foreach($role_objs as $r){
                if($r->getRole() == $role){
                    return true;
                }
            }
        }
        return false;
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
        if($role == INACTIVE){
            return $this->exists();
        }
        if($role == NI){
            return ($this->isRoleAtLeastDuring(AR, $startRange, $endRange) || 
                    $this->isRoleAtLeastDuring(CI, $startRange, $endRange));
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
        global $wgRoleValues, $config;
        if($role == NI){
            return ($this->isRoleAtLeast(AR) || 
                    $this->isRoleAtLeast(CI));
        }
        $me = Person::newFromWgUser();
        if($this->getRoles() != null){
            foreach($this->getRoles() as $r){
                if($r->getRole() != "" && $wgRoleValues[$r->getRole()] >= $wgRoleValues[$role]){
                    return true;
                }
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
        global $wgRoleValues, $config;
        if($role == NI){
            return ($this->isRoleAtMost(AR) || 
                    $this->isRoleAtMost(CI));
        }
        foreach($this->getRoles() as $r){
            if($r->getRole() != "" && $wgRoleValues[$r->getRole()] <= $wgRoleValues[$role]){
                return true;
            }
        }
        return false;
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
                        AND (type = 'Supervises' OR type = 'Co-Supervises')";
            }
            else{
                $sql = "SELECT *
                        FROM grand_relations
                        WHERE user2 = '{$this->id}'
                        AND (type = 'Supervises' OR type = 'Co-Supervises')
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
                AND (type = 'Supervises' OR type = 'Co-Supervises')
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
    function getSupervisorsDuring($startRange, $endRange, $split=false){
        $sql = "SELECT *
                FROM grand_relations
                WHERE user2 = '{$this->id}'
                AND (type = 'Supervises' OR type = 'Co-Supervises')
                AND ( 
                ( (end_date != '0000-00-00 00:00:00') AND
                (( start_date BETWEEN '$startRange' AND '$endRange' ) || ( end_date BETWEEN '$startRange' AND '$endRange' ) || (start_date <= '$startRange' AND end_date >= '$endRange') ))
                OR
                ( (end_date = '0000-00-00 00:00:00') AND
                ((start_date <= '$endRange')))
                )";
    
        $data = DBFunctions::execSQL($sql);
        if($split){
            $sups = array(CO_SUPERVISES => array(),
                          SUPERVISES => array());
        }
        else{
            $sups = array();
        }
        $sups_uniq_ids = array();
        foreach($data as $row){
            $sup = Person::newFromId($row['user1']);
            if( !in_array($sup->getId(), $sups_uniq_ids) && $sup->getName() != ""){
                $sups_uniq_ids[] = $sup->getId();
                if($split){
                    $sups[$row['type']][] = $sup;
                }
                else{
                    $sups[] = $sup;
                }
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
                        AND (type = 'Supervises' OR type = 'Co-Supervises')";
            }
            else{
                $sql = "SELECT *
                        FROM grand_relations
                        WHERE user1 = '{$this->id}'
                        AND (type = 'Supervises' OR type = 'Co-Supervises')
                        AND start_date <= '{$history}'
                        AND (end_date >= '{$history}' OR end_date = '0000-00-00 00:00:00')";
            }
            $data = DBFunctions::execSQL($sql);
            return count($data);
        }
        $sql = "SELECT *
                FROM grand_relations
                WHERE user1 = '{$this->id}'
                AND (type = 'Supervises' OR type = 'Co-Supervises')
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
    
    function getStudentInfo($hqpTypes=array(), $startDate=null, $endDate=null){
        if($startDate == null || $endDate == null){
            $relations = $this->getRelationsAll();
        }
        else{
            $relations = $this->getRelationsDuring('all', $startDate, $endDate);
        }

        $merged = array();
        foreach(Person::$studentPositions as $array){
            $merged = array_merge($merged, $array);
        }
        
        $data = array();
        foreach($relations as $r){
            $universities = $this->getUniversitiesDuring("0000-00-00", "2100-00-00", $r->user2);
            $role = $r->getType();
            if(@implode($hqpTypes) == "committee" || $hqpTypes == "committee"){
                if($role == SUPERVISES || $role == CO_SUPERVISES){
                    continue;
                }
            }
            else{
                if($role == SUPERVISES){
                    $role = "Supervisor";
                }
                else if($role == CO_SUPERVISES){
                    $role = "Co-Supervisor";
                }
                else{
                    continue;
                }
            }
            
            $found = false;
            foreach($universities as $university){
                if($university['id'] == $r->getUniversity()){
                    $found = true;
                    break;
                }
            }
            
            if(!$found){
                // Relation not associated with one of the HQP's universities, try to guess which one it should belong to
                $minUniversity = null;
                $minInterval = 1000000;
                $relStart = new DateTime($r->getStartDate());
                foreach($universities as $university){
                    if(@implode($hqpTypes) == "committee" || $hqpTypes == "committee" ||
                       @in_array(strtolower($university['position']), $hqpTypes) || 
                       ($hqpTypes == "other" && !in_array(strtolower($university['position']), $merged))){
                        $uniStart = new DateTime($university['start']);
                        $startInterval = intval($uniStart->diff($relStart)->format('%a')); // Difference in days
                        $minInterval = min($minInterval, $startInterval);
                        if($minInterval == $startInterval){
                            $minUniversity = $university;
                        }
                    }
                }
                $university = $minUniversity;
            }
            
            if($university == null){
                // No university matched, skip
                continue;
            }
            
            $startDate1 = substr($r->getStartDate(), 0, 10);
            $endDate1 = substr($r->getEndDate(), 0, 10);
            $status = $r->getStatus();
            
            $uni = $university['university'];
            $research_area = $university['research_area'];
            $position = $university['position'];
            
            if(@implode($hqpTypes) != "committee" && 
               $hqpTypes != "committee" && 
               !@in_array(strtolower($position), $hqpTypes) && 
               !(@implode($hqpTypes) != "other" && $hqpTypes != "other" && !in_array(strtolower($position), $merged))){
                continue;
            }

            $end_date = ($endDate1 == '0000-00-00') ? "Current" : $endDate1;
            $start = $university['start'];
            $finish = ($university['end'] == '0000-00-00') ? "" : $university['end'];
            
            $data[$end_date.$startDate1.$position.$r->getId()] = 
                array('hqp' => $r->user2,
                      'position' => $position,
                      'start_date' => $startDate1,
                      'end_date' => $end_date,
                      'start' => $start,
                      'finish' => $finish,
                      'status' => $status,
                      'role' => $role);
        }
        return $data;
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

        if($this->isRole(EA)){
            $this->getFecPersonalInfo();
            $mydepts = array_keys($this->departments);
            foreach(Person::getAllFullPeople() as $person){
                $person->getFecPersonalInfo();
                $depts = array_keys($person->departments);
                if(!$person->isRole(CHAIR) && 
                   !$person->isRole(EA) && 
                   !$person->isRole(DEAN) && 
                   !$person->isRole(VDEAN) && 
                   !$person->isRoleAtLeast(STAFF) && 
                   @$mydepts[0] == @$depts[0]){
                    $people[] = $person;
                }
            }
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
     * @param string $exclude Whether or not to obey exclusion
     * @param boolean $nested Whether this was called recursively (so as not to keep checking HQP of HQP etc.
     * @return array Returns an array of Paper(s) authored or co-authored by this Person _or_ their HQP
     */ 
    function getPapers($category="all", $history=false, $grand='grand', $onlyPublic=true, $access='Forum', $exclude=true, $nested=false){
        $me = Person::newFromWgUser();
        self::generateAuthorshipCache($this->id);
        $processed = array();
        $papersArray = array();
        $papers = array();
        if(!$nested){
            foreach($this->getHQP($history, true) as $hqp){
                $ps = $hqp->getPapers($category, $history, $grand, $onlyPublic, $access, true, true);
                foreach($ps as $p){
                    if(!isset($processed[$p->getId()])){
                        if(!$hqp->isRoleOn(NI, $p->getAcceptanceDate()) && 
                           !$hqp->isRoleOn(NI, $p->getDate()) &&
                           !($p->getAcceptanceDate() == "0000-00-00" && $p->getDate() == "0000-00-00" && $hqp->isRole(NI))){ // Check to make sure HQP isn't a Faculty now
                            $processed[$p->getId()] = true;
                            $papersArray[] = $p;
                        }
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
            $allPapers = Paper::getAllPrivatePapers($category, $grand);
            foreach($allPapers as $paper){
                if(!isset($processed[$paper->getId()])){
                    $processed[$paper->getId()] = true;
                    $papers[] = $paper->getId();
                }
            }
        }
        foreach($papers as $pId){
            $paper = Paper::newFromId($pId);
            if(($paper->getAccess() == $access || ($paper->getAccess() == 'Forum' && $me->isLoggedIn())) &&
               !$paper->deleted && 
               $paper->getId() != 0 &&
               ($category == 'all' || $paper->getCategory() == $category)){
                if($paper->getAccessId() == 0 || !$onlyPublic){
                    $papersArray[] = $paper;
                }
            }
        }
        if($exclude){
            foreach($papersArray as $key => $paper){
                $skip = false;
                foreach($paper->getExclusions() as $exclusion){
                    if($exclusion->getId() == $this->getId()){
                        // This Person doesn't want to be associated with this Product
                        $skip = true;
                    }
                }
                if($skip){ 
                    unset($papersArray[$key]);
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
     * @param string $useReported Whether to use reported years.  If false, it will not, if set to a year then it uses that year
     * @return array Returns an array of Paper(s) authored/co-authored by this Person during the specified dates
     */
    function getPapersAuthored($category="all", $startRange = CYCLE_START, $endRange = CYCLE_START_ACTUAL, $includeHQP=false, $networkRelated=true, $useReported=false, $onlyUseStartDate=false, $exclude=true){
        global $config;
        self::generateAuthorshipCache($this->id);
        $processed = array();
        $papersArray = array();
        $papers = array();
        if($includeHQP){
            $yearAgo = strtotime("{$startRange} -2 year"); // Extend the year to 2 years ago so that publications after graduation are still counted
            $yearAgo = date('Y-m-d', $yearAgo);
            foreach($this->getHQPDuring($yearAgo, $endRange) as $hqp){
                $ps = $hqp->getPapersAuthored($category, $startRange, $endRange, false, $networkRelated, $useReported, $onlyUseStartDate);
                foreach($ps as $p){
                    if(!isset($processed[$p->getId()])){
                        if(!$hqp->isRoleOn(NI, $p->getAcceptanceDate()) && 
                           !$hqp->isRoleOn(NI, $p->getDate()) &&
                           !($p->getAcceptanceDate() == "0000-00-00" && $p->getDate() == "0000-00-00" && $hqp->isRole(NI))){ // Check to make sure HQP isn't a Faculty now
                            $processed[$p->getId()] = true;
                            $papersArray[] = $p;
                        }
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
        $structure = Product::structure();
        foreach($papers as $paper){
            $type = explode(":", $paper->getType());
            $dateLabel = @$structure['categories'][$paper->getCategory()]['types'][$type[0]]["date_label"];
            $acceptanceDateLabel = @$structure['categories'][$paper->getCategory()]['types'][$type[0]]["acceptance_date_label"];
            $reportedYear = $paper->getReportedForPerson($this->getId());
            $acceptanceDate = $paper->getAcceptanceDate();
            $date = ($onlyUseStartDate) ? $acceptanceDate : $paper->getDate();
            if(($acceptanceDateLabel == "Acceptance Date" && $acceptanceDate == "0000-00-00" && $date >= "2020-07-01")){
                // Don't allow papers without acceptance dates (starting 2020-07-01)
                continue;
            }
            if($acceptanceDate == "0000-00-00" || $acceptanceDate == ""){
                $acceptanceDate = $date;
            }
            if(!$paper->deleted && ($category == 'all' || $paper->getCategory() == $category) &&
               $paper->getId() != 0 &&
               (// Handle Products Normally
                ($date >= $startRange && $date <= $endRange ||
                ($acceptanceDate >= $startRange && $acceptanceDate <= $endRange && $acceptanceDateLabel != "")) ||
                // Handle Products where acceptance date and date behave as start and end dates
                ($acceptanceDateLabel == "Start Date" && $dateLabel == "End Date" && 
                 ($acceptanceDate >= $startRange && $date <= $endRange && $date >= $startRange ||
                  $acceptanceDate <= $startRange && $date >= $startRange ||
                  $acceptanceDate <= $endRange && $date >= $endRange ||
                  $acceptanceDate <= $endRange && $date == "0000-00-00")) ||
                // Handle Reported Products
                ($useReported && $reportedYear != "" && ($reportedYear).CYCLE_START_MONTH >= $startRange && 
                                                        ($reportedYear+1).CYCLE_END_MONTH <= $endRange) ||
                // Handle Yearly Awards
                ($paper->getData('yearly') == 1 && ($paper->getData('start_date') >= $startRange && $paper->getData('end_date') <= $endRange && $paper->getData('end_date') >= $startRange ||
                                                     $paper->getData('start_date') <= $startRange && $paper->getData('end_date') >= $startRange ||
                                                     $paper->getData('start_date') <= $endRange && $paper->getData('end_date') >= $endRange ||
                                                     $paper->getData('start_date') <= $endRange && $paper->getData('end_date') == "0000-00-00"))
                )){
                $papersArray[] = $paper;
            }
        }
        
        if($exclude){
            foreach($papersArray as $key => $paper){
                $skip = false;
                foreach($paper->getExclusions() as $exclusion){
                    if($exclusion->getId() == $this->getId()){
                        // This Person doesn't want to be associated with this Product
                        $skip = true;
                    }
                }
                if($skip){ 
                    unset($papersArray[$key]);
                }
            }
        }
        return $papersArray;
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
     * Returns the ProductHistory entries for the optionally given year and type
     * @param string $year The year of the ProductHistory
     * @param string $type The type of ProductHistory
     * @return array Returns the ProductHistory entries
     */
    function getProductHistories($year="%", $type="%"){
        $histories = array();
        $data = DBFunctions::select(array('grand_product_histories'),
                                    array('id'),
                                    array('user_id' => $this->getId(),
                                          'year' => LIKE($year),
                                          'type' => LIKE($type)));
        foreach($data as $row){
            $productHistory = ProductHistory::newFromId($row['id']);
            if($productHistory != null && $productHistory->getId() != 0){
                $histories[] = $productHistory;
            }
        }
        return $histories;
    }
    
    function getProductHistoryLastYear(){
        $histories = $this->getProductHistories();
        $year = "";
        foreach($histories as $history){
            $year = max($year, $history->getYear());
        }
        return $year;
    }
    
    function getProductHistoryEarliestYear(){
        $histories = $this->getProductHistories();
        $year = 9999;
        foreach($histories as $history){
            $year = min($year, $history->getYear());
        }
        return $year;
    }
    
    /**
     * Returns when this Person's top products were last updated
     * @return string When this Person's to products were last updated
     */
    function getTopProductsLastUpdated(){
        $data = DBFunctions::select(array('grand_top_products'),
                                    array('changed'),
                                    array('obj_id' => EQ($this->getId())),
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
                                    array('product_id'),
                                    array('obj_id' => EQ($this->getId())));
        foreach($data as $row){
            $product = Product::newFromId($row['product_id']);
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

    function getCourses(){
        $courses = Course::getUserCourses($this->id);
        return $courses;
    }
    
    function getCoursesDuring($start, $end){
        $during = array();
        $courses = Course::getUserCourses($this->id);
        foreach($courses as $course){
            $courseStart = $course->getStartDate();
            $courseEnd = $course->getEndDate();
            if(($courseStart <= $start && $courseEnd   >= $end) ||
               ($courseEnd   >= $start && $courseEnd   <= $end) ||
               ($courseStart >= $start && $courseStart <= $end)){
                $during[] = $course;
            }
        }
        return $during;
    }

    function getMetric(){
        $metric = Metric::getUserMetric($this->id);
        return $metric;
    }
    
    function getGsMetric(){
        $gsMetric = GsMetric::getUserMetric($this->id);
        return $gsMetric;
    }
    
    static function getAllServiceRoles(){
        $data = DBFunctions::select(array('grand_services'),
                                    array('*'),
                                    array(),
                                    array('start' => 'DESC',
                                          'end' => 'DESC'));
        foreach($data as $key => $row){
            $data[$key]['start'] = substr($row['start'], 0, 10);
            $data[$key]['end'] = substr($row['end'], 0, 10);
        }
        return $data;
    }
    
    /**
     * Returns an array of Service Roles
     * @return array This Person's Service Roles
     */
    function getServiceRoles(){
        $data = DBFunctions::select(array('grand_services'),
                                    array('*'),
                                    array('user_id' => EQ($this->getId())),
                                    array('start' => 'DESC',
                                          'end' => 'DESC'));
        foreach($data as $key => $row){
            $data[$key]['start'] = substr($row['start'], 0, 10);
            $data[$key]['end'] = substr($row['end'], 0, 10);
        }
        return $data;
    }

    /**
     * Returns this Person's Supervisors
     * @param mixed $history Whether or not to include all Supervisors in history (can also be a specific date)
     * @return array This Person's Supervisors
     */
    function getCommittee($history=false){
        if($this->id != null){
            if($history === false){
                $sql = "SELECT *
                        FROM grand_relations
                        WHERE user2 = '{$this->id}'
                        AND type LIKE '%Committee%'";
            }
            else{
                $sql = "SELECT *
                        FROM grand_relations
                        WHERE user2 = '{$this->id}'
                        AND type LIKE '%Committee%'
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
                AND type LIKE '%Committee%'
                AND start_date > end_date";
        $data = DBFunctions::execSQL($sql);
        $people = array();
        foreach($data as $row){
            $person = Person::newFromId($row['user1']);
            $people[$person->getId()] = $person;
        }
        return array_values($people);
    }
     
    function getRelationsAll(){
        $data = DBFunctions::execSQL("SELECT r.*
                                      FROM grand_relations r, mw_user u
                                      WHERE user1 = '{$this->id}'
                                      AND r.user2 = u.user_id
                                      AND u.deleted != '1'");
        $data = DBFunctions::select(array('grand_relations'),
                                    array('id'),
                                    array('user1' => EQ($this->id)));
        $relations = array();
        foreach($data as $row){
            $relation = Relationship::newFromId($row['id']);
            $relations[] = $relation;
        }
        usort($relations, function($a, $b){ 
            return str_replace("0000-00-00", "9999-12-31", $a->getEndDate()) < 
                   str_replace("0000-00-00", "9999-12-31", $b->getEndDate());
        });
        return $relations;
    }

    /**
     * Returns a new Person from the given email (null if not found)
     * In the event of a collision, the first user is returned
     * @param string $email The email address of the Person
     * @return Person The Person from the given email
     */
    static function newFromUniversityId($id){
        $person = new LimitedPerson(array());
        $data = DBFunctions::select(array('mw_user'),
                                    array('user_id'),
                                    array('university_id' => $id));
        if(count($data) > 0){
            return Person::newFromId($data[0]['user_id']);
        }
        return $person;
    }

    function setUniversityId($id){
        $status = DBFunctions::update('mw_user',
                                      array('university_id' => $id),
                                      array('user_name' => EQ($this->getName())));
        return $status;
    }

    function getCourseEval($course_id, $allSections=false){
        $me = Person::newFromWgUser();
        if($this->isMe() || $me->isRoleAtLeast(ADMIN)){
            $qTexts = array();
            $enrolled = array();
            $responses = array();
            if($allSections){
                $course = Course::newFromId($course_id);
                $data = DBFunctions::execSQL("SELECT uc.`course_evals`
                                              FROM `grand_courses` c, `grand_user_courses` uc
                                              WHERE c.id = uc.course_id
                                              AND uc.user_id = '{$this->getId()}'
                                              AND c.term_string = '{$course->term_string}'
                                              AND c.subject = '{$course->subject}'
                                              AND c.catalog = '{$course->catalog}'
                                              AND c.component = '{$course->component}'
                                              AND uc.percentage != '0'");
            }
            else{
                $data = DBFunctions::select(array('grand_user_courses'),
                                            array('course_evals'),
                                            array('course_id' => $course_id,
                                                  'user_id' => $this->getId()));
            }
            $ret = array();
            $tmp = array();
            foreach($data as $row){
                $eval = json_decode($row['course_evals'], true);
                if(is_array($eval)){
                    foreach($eval as $e){
                        $qTexts[$e['id']] = (isset($e['question'])) ? $e['question'] : Course::$evalMap[$e['id']];
                        $enrolled[$e['id']] = @$e['enrolled'];
                        $responses[$e['id']] = @$e['responses'];
                        foreach($e['votes'] as $i => $vote){
                            @$tmp[$e['id']][$i] += $vote;
                        }
                    }
                }
            }
            foreach($tmp as $i => $t){
                $ret[] = array('id' => $i, 
                               'enrolled' => $enrolled[$i],
                               'responses' => $responses[$i],
                               'question' => str_replace("(", "&#40;", 
                                             str_replace(")", "&#41;", 
                                             str_replace(",", "&#44;", $qTexts[$i]))), 
                               'votes' => $t);
            }
            return $ret;
        }
        return array();
    }
    
    function getCoursePercent($course_id){
        $me = Person::newFromWgUser();
        if($this->isMe() || $me->isRoleAtLeast(ADMIN)){
            $data = DBFunctions::select(array('grand_user_courses'),
                                        array('percentage'),
                                        array('course_id' => $course_id,
                                              'user_id' => $this->getId()));
            if(count($data)>0){
                if($data[0]['percentage'] != ""){
                    return $data[0]['percentage'];
                }
                else {
                    return 100;
                }
            }
            else{
                return 100;
            }
        }
        return "";
    }

    /**
     * Returns whether or not this person was ever related to another Person through a given relationship
     * @param Person $person The Person that this Person is related to
     * @param string $relationship The type of Relationship
     * @return boolean Whether or not this Person is related to another Person
     */
    function isRelatedToDuring($person, $relationship, $start_date, $end_date){
        if($person instanceof Person){
            $relations = $this->getRelationsDuring($relationship, $start_date, $end_date);
            foreach($relations as $relation){                
                if($relation->user2 == $person->getId()){
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * The following functions are used for FullPerson objects, 
     * but so things don't break they should be defined here and return default values
     */
    function getPhoto($cached=false){
        global $wgServer, $wgScriptPath;
        return "$wgServer$wgScriptPath/skins/face.png";
    }
    
    function getPhoneNumber(){ return ""; }

    function getTwitter(){ return ""; }

    function getWebsite(){ return ""; }
    
    function getGoogleScholar(){ return ""; }
    
    function getSciverseId(){ return ""; }
    
    function getOrcId(){ return ""; }
    
    function getWOS(){ return ""; }
    
    function getAlexId(){ return ""; }

    function getProfile($private=false){ return ""; }

    function getProfileStartDate(){ return ""; }

    function getProfileEndDate(){ return ""; }
    
    function getLdap(){ return ""; }
    
    function getHQP($history=false, $onlySupervises=false){ return array(); }
       
    function getHQPDuring($startRange, $endRange){ return array(); }

    function getRelationsDuring($type='all', $startRange, $endRange){ return array(); }

    function getRelations($type='all', $history=false){ return array(); }
    
    function getGrants($exclude=true){ return array(); }
    
    function getGrantsBetween($start, $end, $filter=false, $exclude=true){ return array(); }
    
    function getGrantAwards(){ return array(); }
    
    function getGrantAwardsBetween($start, $end){ return array();}
    
    function getFecPersonalInfo(){ return $this; }

    function updateFecInfo(){ return true; }
    
    function hasTenure($date=null){ return false; }
    
    function isAssistantProfessor($date){ return false; }
    
    function isAssociateProfessor($date){ return false; }
    
    function isProfessor($date){ return false; }
    
    function isFSO2($date=null){ return false; }
    
    function isFSO3($date=null){ return false; }
    
    function isFSO4($date=null){ return false; }
    
    function isATSEC1($date=null){ return false; }
    
    function isATSEC2($date=null){ return false; }
    
    function isATSEC3($date=null){ return false; }
    
    function isNew($date=null){ return false; }
    
    function isRetired($date=null){ return false; }
    
    function getFECType($date=null){ return ""; }

    function getCaseNumber($year=YEAR){ return ""; }
    
    function getSalary($year){ return ""; }
    
    function getIncrement($year){ return "N/A"; }
    
    function getCNA($year){ return ""; }
}

Person::$studentPositions['grad'] = array_merge(Person::$studentPositions['msc'], Person::$studentPositions['phd']);

class FullPerson extends Person {
    
    var $photo;
    var $phone;
    var $twitter;
    var $website;
    var $googleScholar;
    var $sciverseId;
    var $orcId;
    var $wos;
    var $alexId;
    var $publicProfile;
    var $profileStartDate;
    var $profileEndDate;
    var $relations = array();
    var $hqps;
    var $historyHqps;
    var $hqpCache = array();
    var $grants;
    
    var $faculty;
    var $departments = array();
    var $dateOfPhd;
    var $dateOfAppointment;
    var $dateOfAssistant;
    var $dateOfAssociate;
    var $dateOfProfessor;
    var $dateOfTenure;
    var $dateOfProbation1;
    var $dateOfProbation2;
    var $sabbatical = array();
    var $dateOfRetirement;
    var $dateOfLastDegree;
    var $lastDegree;
    //var $publicationHistoryRefereed;
    //var $publicationHistoryBooks;
    //var $publicationHistoryPatents;
    var $dateFso2;
    var $dateFso3;
    var $dateFso4;
    var $dateAtsec1;
    var $dateAtsec2;
    var $dateAtsec3;
    var $dateAtsAnniversary;
    
    function FullPerson($data){
        self::Person($data);
        if(!empty($data)){
            $this->twitter = @$data[0]['user_twitter'];
            $this->website = @$data[0]['user_website'];
            $this->googleScholar = @$data[0]['google_scholar_url'];
            $this->sciverseId = @$data[0]['sciverse_id'];
            $this->orcId = @$data[0]['orcid'];
            $this->wos = @$data[0]['wos'];
            $this->alexId = @$data[0]['alex_id'];
            $this->publicProfile = @$data[0]['user_public_profile'];
            $this->profileStartDate = @$data[0]['profile_start_date'];
            $this->profileEndDate = @$data[0]['profile_end_date'];
            $this->hqps = null;
            $this->historyHqps = null;
        }
        $this->full = 1;
    }
    
    /**
     * Returns the path to a photo of this Person if it exists
     * @param boolen $cached Whether or not to use a cached version
     * @return string The path to a photo of this Person
     */
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
    
    /**
     * Returns the phone number of this Person
     * @return string The phone number of this Person
     */
    function getPhoneNumber(){
        $me = Person::newFromWgUser();
        if($me->isAllowedToEdit($this)){
            $data = DBFunctions::select(array('grand_user_telephone'),
                                        array('number'),
                                        array('primary_indicator' => EQ(1),
                                              'user_id' => EQ($this->getId())));
            return @trim($data[0]['number']);
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
    
    function getGoogleScholar(){
        return $this->googleScholar;
    }
    
    function getSciverseId(){
        return $this->sciverseId;
    }
    
    function getOrcId(){
        return trim(str_replace("http://orcid.org/", "", 
                    str_replace("https://orcid.org/", "", $this->orcId)));
    }
    
    function getWOS(){
        return $this->wos;
    }
    
    function getAlexId(){
        return $this->alexId;
    }
    
    // Returns the user's profile.
    // If $private is true, then it grabs the private version, otherwise it gets the public
    /**
     * Returns the text from this Person's profile
     * @param boolean $private If tru, then it grabs the private version, otherwise it gets the public
     * @return string This Person's profile text
     */
    function getProfile($private=false){
        return $this->publicProfile;
    }
    
    /**
     * Returns the start date range for the user's profile
     * @return string This Person's start date for the user's profile
     */
    function getProfileStartDate(){
        return substr($this->profileStartDate, 0, 10);
    }
    
    /**
     * Returns the end date range for the user's profile
     * @return string This Person's end date for the user's profile
     */
    function getProfileEndDate(){
        return substr($this->profileEndDate, 0, 10);
    }
    
    function getLdap(){
        if(strstr($this->getEmail(), "ualberta.ca") !== false){
            $ccid = explode("@", $this->getEmail());
            return @"https://apps.ualberta.ca/directory/person/{$ccid[0]}";
        }
        return "";
    }
    
    /**
     * Returns this Person's HQP
     * @param mixed $history Whether or not to include all HQP in history (can also be a specific date)
     * @return array This Person's HQP
     */
    function getHQP($history=false, $onlySupervises=false){
        $extraSQL = "";
        if(!$onlySupervises){
            $extraSQL = " OR type = 'Supervisory-Committee member' OR
                             type = 'Examining-Committee member' OR
                             type = 'Examining-Committee chair'";
        }
        if($history !== false && $this->id != null){
            $this->roles = array();
            if($history === true){
                if($this->historyHqps != null){
                    return $this->historyHqps;
                }
                
                $sql = "SELECT *
                        FROM grand_relations
                        WHERE user1 = '{$this->id}'
                        AND (type = 'Supervises' OR 
                             type = 'Co-Supervises' 
                             $extraSQL)";
            }
            else{
                $sql = "SELECT *
                        FROM grand_relations
                        WHERE user1 = '{$this->id}'
                        AND (type = 'Supervises' OR 
                             type = 'Co-Supervises' 
                             $extraSQL)
                        AND start_date <= '{$history}'
                        AND (end_date >= '{$history}' OR end_date = '0000-00-00 00:00:00')";
            }
            $data = DBFunctions::execSQL($sql);
            $hqps = array();
            foreach($data as $row){
                $hqp = Person::newFromId($row['user2']);
                $hqps[strtolower($hqp->getName())] = $hqp;
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
                AND (type = 'Supervises' OR 
                     type = 'Co-Supervises' 
                     $extraSQL)
                AND start_date > end_date";
        $data = DBFunctions::execSQL($sql);
        $hqps = array();
        foreach($data as $row){
            $hqp = Person::newFromId($row['user2']);
            if($hqp->isRoleDuring(HQP, '0000-00-00 00:00:00', '2100-00-00 00:00:00')){
                $hqps[strtolower($hqp->getName())] = $hqp;
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
        if(isset($this->hqpCache[$startRange.$endRange])){
            return $this->hqpCache[$startRange.$endRange];
        }
        $sql = "SELECT *
                FROM grand_relations
                WHERE user1 = '{$this->id}'
                AND (type = 'Supervises' OR type = 'Co-Supervises')
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
                //if(!$hqp->isRoleDuring(HQP, $startRange, $endRange)){
                //    continue;
                //}
                $hqps[] = $hqp;
            }
        }
        $this->hqpCache[$startRange.$endRange] = $hqps;
        return $hqps;
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
        if(isset($this->relations["{$type}{$startRange}{$endRange}"])){
            return $this->relations["{$type}{$startRange}{$endRange}"];
        }
        $sql = "SELECT r.*
                FROM grand_relations r, mw_user u
                WHERE user1 = '{$this->id}'\n";
        if($type == "public" || $type == "all"){
            // do nothing
        }
        else{
            if($type == SUPERVISES_BOTH){
                $sql .= "AND (type = '".SUPERVISES."' OR type = '".CO_SUPERVISES."')\n";
            }
            else{
                $sql .= "AND type = '$type'\n";
            }
        }
        $sql .= "AND ( 
                ( (end_date != '0000-00-00 00:00:00') AND
                (( start_date BETWEEN '$startRange' AND '$endRange' ) || ( end_date BETWEEN '$startRange' AND '$endRange' ) || (start_date <= '$startRange' AND end_date >= '$endRange') ))
                OR
                ( (end_date = '0000-00-00 00:00:00') AND
                ((start_date <= '$endRange')))
                )
                AND u.user_id = r.user2
                AND u.deleted != 1
        ORDER BY REPLACE(end_date, '0000-00-00 00:00:00', '9999-12-31 00:00:00') DESC";
        $data = DBFunctions::execSQL($sql);
        $relations = array();
        foreach($data as $row){
            $relation = Relationship::newFromId($row['id']);
            $relations[] = $relation;
        }
        $this->relations["{$type}{$startRange}{$endRange}"] = $relations;
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
                    AND u1.user_id = user1
                    AND u2.user_id = user2
                    AND u1.deleted != '1'
                    AND u2.deleted != '1'";
            if(!$history){
                $sql .= " AND start_date >= end_date";
            }
            $sql .= " ORDER BY REPLACE(end_date, '0000-00-00 00:00:00', '9999-12-31 00:00:00') DESC";
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                $relation = Relationship::newFromId($row['id']);
                $this->relations[$row['type']][$row['id']] = $relation;
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
                    AND u2.deleted != '1'";
            if(!$history){
                $sql .= " AND start_date >= end_date";
            }
            $sql .= " ORDER BY REPLACE(end_date, '0000-00-00 00:00:00', '9999-12-31 00:00:00') DESC";
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                $relation = Relationship::newFromId($row['id']);
                $this->relations[$row['type']][$row['id']] = $relation;
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
                $sql .= " AND start_date >= end_date";
            }
            $sql .= " ORDER BY REPLACE(end_date, '0000-00-00 00:00:00', '9999-12-31 00:00:00') DESC";
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                $relation = Relationship::newFromId($row['id']);
                $this->relations[$row['type']][$row['id']] = $relation;
            }
        //}
        return $this->relations[$type];
    }
    
    function getGrants($exclude=true){
        if($this->grants == null){
            $this->grants = array();
            $data = DBFunctions::select(array('grand_grants'),
                                        array('*'),
                                        array('user_id' => EQ($this->getId()),
                                              WHERE_OR('copi') => LIKE("%\"{$this->getId()}\";%") ));
            foreach($data as $row){
                $grant = new Grant(array($row));
                if($grant != null && $grant->getId() != 0 && !$grant->deleted){
                    $this->grants[] = $grant;
                }
            }
        }
        $grants = $this->grants;
        if($exclude){
            foreach($grants as $key => $grant){
                $skip = false;
                foreach($grant->getExclusions() as $exclusion){
                    if($exclusion->getId() == $this->getId()){
                        // This Person doesn't want to be associated with this Product
                        $skip = true;
                    }
                }
                if($skip){ 
                    unset($grants[$key]);
                }
            }
        }
        return $this->grants;
    }
    
    function getGrantsBetween($start, $end, $filter=false, $exclude=true){
        $grants = array();
        foreach($this->getGrants() as $grant){
            $grantStart = $grant->getStartDate();
            $grantEnd = $grant->getEndDate();
            if(($grantStart >= $start && $grantStart <= $end) ||
               ($grantEnd >= $start && $grantEnd <= $end) ||
               ($grantStart <= $start && $grantEnd >= $end)){
                if($filter){
                    if(strtolower($grant->getSponsor()) == "university of alberta" &&
                       array_search($grant->getSeqNo(), array(0, 35)) !== false){
                        // Rule 1: inside Fac of Science and start up funds
                        continue;
                    }
                    else if(strtolower($grant->getSponsor()) == "university of alberta" &&
                            array_search($grant->getSeqNo(), array(60)) !== false){
                        // Rule 1.1: inside Fac of Science and start up funds
                        continue;
                    }
                    else if($grant->getTotal() == 0){
                        // Rule 2: blank PI names or 0 funding signal a reason to exclude
                        continue;
                    }
                    else if(strpos($grant->getProjectId(), 'Z') === 0){
                        // Rule 101: Not interesting – internal facilities support/ centre support
                        continue;
                    }
                    else if(strpos($grant->getProjectId(), 'Y224') === 0){
                        // Rule 102: AITF Centre support
                        continue;
                    }
                    else if(strpos($grant->getProjectId(), 'Y000') === 0){
                        // Rule 103: Internal Centre Funding
                        continue;
                    }
                    else if(strpos($grant->getProjectId(), 'B') === 0 ||
                            strpos($grant->getProjectId(), 'P') === 0 ||
                            strtolower($grant->getSponsor()) === "national research council of canada"){
                        // Rule 104: Project ID scan – Billing/Journal productions/services
                        continue;
                    }
                    else if(strpos($grant->getProjectId(), 'N') === 0){
                        // Rule 105: Other internally transferred research funds
                        continue;
                    }
                    else if(strpos($grant->getProjectId(), 'D') === 0 ||
                            strpos($grant->getProjectId(), 'W') === 0){
                        // Rule 106: donations
                        continue;
                    }
                    else if(strpos($grant->getProjectId(), 'G022') === 0 ||
                            strpos($grant->getProjectId(), 'G099') === 0){
                        // Rule 107: “General Research Funds”
                        continue;
                    }
                    else if(strtolower($grant->getRole()) == "student" ||
                            strstr(strtolower($grant->getProgDescription()), "fellowship") !== false ||
                            strstr(strtolower($grant->getProgDescription()), "student") !== false ||
                            strstr(strtolower($grant->getProgDescription()), "scholarship") !== false){
                        // Rule 200: awards to students seems to capture all student cases
                        continue;
                    }
                    else if(strtolower($grant->getSponsor()) == "university of alberta" &&
                        (array_search($grant->getSeqNo(), array(33, 51, 53, 55, 65, 137)) !== false ||
                         strstr(strtolower($grant->getProjectId()), "triumf") !== false
                        )){
                        // Rule 300: Miscellaneous not interesting for different kinds of reasons
                        continue;
                    }
                    else if($grant->getTotal() < 10000){
                        // Total funding < $10k
                        continue;
                    }
                }
                $grants[$grantStart.$grantEnd.$grant->getId()] = $grant;
            }
        }
        if($exclude){
            foreach($grants as $key => $grant){
                $skip = false;
                foreach($grant->getExclusions() as $exclusion){
                    if($exclusion->getId() == $this->getId()){
                        // This Person doesn't want to be associated with this Product
                        $skip = true;
                    }
                }
                if($skip){ 
                    unset($grants[$key]);
                }
            }
        }
        ksort($grants);
        $grants = array_reverse($grants);
        return $grants;
    }
    
    function getGrantAwards(){
        return GrantAward::getAllGrantAwards(0, 999999999, $this);
    }
    
    function getGrantAwardsBetween($start, $end){
        $grants = array();
        foreach($this->getGrantAwards() as $grant){
            $grantStart = $grant->start_year;
            $grantEnd = $grant->end_year;
            if(($grantStart >= $start && $grantStart <= $end) ||
               ($grantEnd >= $start && $grantEnd <= $end) ||
               ($grantStart <= $start && $grantEnd >= $end)){
                $grants[] = $grant;
            }
        }
        return $grants;
    }
    
    function getFecPersonalInfo(){
        if(!isset(self::$fecInfoCache[$this->getId()])){
            $data = DBFunctions::select(array('grand_personal_fec_info'),
                                        array('user_id', 
                                              'faculty',
                                              'departments',
                                              'date_of_phd',
                                              'date_of_appointment',
                                              'date_assistant',
                                              'date_associate',
                                              'date_professor',
                                              'date_probation1',
                                              'date_probation2',
                                              'date_tenure',
                                              'sabbatical',
                                              'date_retirement',
                                              'date_last_degree',
                                              'last_degree',
                                              //'publication_history_refereed',
                                              //'publication_history_books',
                                              //'publication_history_patents',
                                              'date_fso2',
                                              'date_fso3',
                                              'date_fso4',
                                              'date_atsec1',
                                              'date_atsec2',
                                              'date_atsec3',
                                              'date_ats_anniversary'),
                                        array('user_id' => EQ($this->getId())));
            self::$fecInfoCache[$this->getId()] = $data;
            if(count($data) >0){
                $row = $data[0];
                foreach($row as $key => $value){
                    $row[$key] = str_replace("0000-00-00 00:00:00", "", $value);
                }
                $this->faculty = $row['faculty'];
                $this->departments = json_decode($row['departments'], true);
                if(is_array($this->departments)){
                    asort($this->departments);
                    $this->departments = array_reverse($this->departments);
                }
                else{
                    $this->departments = array();
                }
                $this->dateOfPhd = $row['date_of_phd'];
                $this->dateOfAppointment = $row['date_of_appointment'];
                $this->dateOfAssistant = $row['date_assistant'];
                $this->dateOfAssociate = $row['date_associate'];
                $this->dateOfProfessor = $row['date_professor'];
                $this->dateOfProbation1 = $row['date_probation1'];
                $this->dateOfProbation2 = $row['date_probation2'];
                $this->dateOfTenure = $row['date_tenure'];
                $this->sabbatical = json_decode($row['sabbatical'], true);
                $this->dateOfRetirement = $row['date_retirement'];
                $this->dateOfLastDegree = $row['date_last_degree'];
                $this->lastDegree = $row['last_degree'];
                //$this->publicationHistoryRefereed = $row['publication_history_refereed'];
                //$this->publicationHistoryBooks = $row['publication_history_books'];
                //$this->publicationHistoryPatents = $row['publication_history_patents'];
                $this->dateFso2 = $row['date_fso2'];
                $this->dateFso3 = $row['date_fso3'];
                $this->dateFso4 = $row['date_fso4'];
                $this->dateAtsec1 = $row['date_atsec1'];
                $this->dateAtsec2 = $row['date_atsec2'];
                $this->dateAtsec3 = $row['date_atsec3'];
                $this->dateAtsAnniversary = $row['date_ats_anniversary'];
            }
        }
        return $this;
    }

    function updateFecInfo(){
        //TODO: This can be done in another file separate from this object. Did this to save time and should
        //fix in the future
        $me = Person::newFromWgUser();
        if($me->getId() == $this->getId() || $me->isRoleAtLeast(STAFF)){
            unset(self::$fecInfoCache[$this->getId()]);
            $fec = DBFunctions::select(array('grand_personal_fec_info'),
                                       array('*'),
                                       array('user_id' => EQ($this->getId())));
            if(count($fec) > 0){
                $status = DBFunctions::update('grand_personal_fec_info', 
                                        array('faculty' => $this->faculty,
                                              'departments' => json_encode($this->departments),
                                              'date_of_phd' => $this->dateOfPhd,
                                              'date_of_appointment' => $this->dateOfAppointment,
                                              'date_assistant' => $this->dateOfAssistant,
                                              'date_associate' => $this->dateOfAssociate,
                                              'date_professor' => $this->dateOfProfessor,
                                              'date_tenure' => $this->dateOfTenure,
                                              'sabbatical' => json_encode($this->sabbatical),
                                              'date_probation1' => $this->dateOfProbation1,
                                              'date_probation2' => $this->dateOfProbation2,
                                              'date_retirement' => $this->dateOfRetirement,
                                              'date_last_degree' => $this->dateOfLastDegree,
                                              'last_degree' => $this->lastDegree,
                                              //'publication_history_refereed' => $this->publicationHistoryRefereed,
                                              //'publication_history_books' => $this->publicationHistoryBooks,
                                              //'publication_history_patents' => $this->publicationHistoryPatents,
                                              'date_fso2' => $this->dateFso2,
                                              'date_fso3' => $this->dateFso3,
                                              'date_fso4' => $this->dateFso4,
                                              'date_atsec1' => $this->dateAtsec1,
                                              'date_atsec2' => $this->dateAtsec2,
                                              'date_atsec3' => $this->dateAtsec3,
                                              'date_ats_anniversary' => $this->dateAtsAnniversary),
                                        array('user_id' => EQ($this->getId())));
                if($status){
                    DBFunctions::commit();
                }
                return $status;
            }
            else{
                $status = DBFunctions::insert('grand_personal_fec_info',
                                    array('user_id' => $this->getId(),
                                          'faculty' => $this->faculty,
                                          'departments' => json_encode($this->departments),
                                          'date_of_phd' => $this->dateOfPhd,
                                          'date_of_appointment' => $this->dateOfAppointment,
                                          'date_assistant' => $this->dateOfAssistant,
                                          'date_associate' => $this->dateOfAssociate,
                                          'date_professor' => $this->dateOfProfessor,
                                          'date_tenure' => $this->dateOfTenure,
                                          'sabbatical' => json_encode($this->sabbatical),
                                          'date_probation1' => $this->dateOfProbation1,
                                          'date_probation2' => $this->dateOfProbation2,
                                          'date_retirement' => $this->dateOfRetirement,
                                          'date_last_degree' => $this->dateOfLastDegree,
                                          'last_degree' => $this->lastDegree,
                                          //'publication_history_refereed' => $this->publicationHistoryRefereed,
                                          //'publication_history_books' => $this->publicationHistoryBooks,
                                          //'publication_history_patents' => $this->publicationHistoryPatents,
                                          'date_fso2' => $this->dateFso2,
                                          'date_fso3' => $this->dateFso3,
                                          'date_fso4' => $this->dateFso4,
                                          'date_atsec1' => $this->dateAtsec1,
                                          'date_atsec2' => $this->dateAtsec2,
                                          'date_atsec3' => $this->dateAtsec3,
                                          'date_ats_anniversary' => $this->dateAtsAnniversary),
                                           true);
               if($status){
                    DBFunctions::commit();
                }
                return $status;
            }
        }
        return false;
    }
    
    function hasTenure($date=null){
        if($date == null){
            $date = date('Y-m-d');
        }
        $date = substr($date, 0, 10);
        if($this->dateOfTenure == null){
            $this->getFecPersonalInfo();
        }
        return ($this->dateOfTenure != "" && $date >= substr($this->dateOfTenure, 0, 10));
    }
    
    function isAssistantProfessor($date){
        if($date == null){
            $date = date('Y-m-d');
        }
        $date = substr($date, 0, 10);
        if($this->dateOfAssistant == null){
            $this->getFecPersonalInfo();
        }
        return ($this->dateOfAssistant != "" && $date >= substr($this->dateOfAssistant, 0, 10) && !$this->isAssociateProfessor($date) &&
                                                                                                  !$this->isProfessor($date));
    }
    
    function isAssociateProfessor($date){
        if($date == null){
            $date = date('Y-m-d');
        }
        $date = substr($date, 0, 10);
        if($this->dateOfAssociate == null){
            $this->getFecPersonalInfo();
        }
        return ($this->dateOfAssociate != "" && $date >= substr($this->dateOfAssociate, 0, 10) && !$this->isProfessor($date));
    }
    
    function isProfessor($date){
        if($date == null){
            $date = date('Y-m-d');
        }
        $date = substr($date, 0, 10);
        if($this->dateOfProfessor == null){
            $this->getFecPersonalInfo();
        }
        return ($this->dateOfProfessor != "" && $date >= substr($this->dateOfProfessor, 0, 10));
    }
    
    function isFSO2($date=null){
        if($date == null){
            $date = date('Y-m-d');
        }
        $date = substr($date, 0, 10);
        if($this->dateFso2 == null){
            $this->getFecPersonalInfo();
        }
        return ($this->dateFso2 != "" && $date >= substr($this->dateFso2, 0, 10) && !$this->isFSO3($date) &&
                                                                                    !$this->isFSO4($date));
    }
    
    function isFSO3($date=null){
        if($date == null){
            $date = date('Y-m-d');
        }
        $date = substr($date, 0, 10);
        if($this->dateFso3 == null){
            $this->getFecPersonalInfo();
        }
        return ($this->dateFso3 != "" && $date >= substr($this->dateFso3, 0, 10) && !$this->isFSO4($date));
    }
    
    function isFSO4($date=null){
        if($date == null){
            $date = date('Y-m-d');
        }
        $date = substr($date, 0, 10);
        if($this->dateFso4 == null){
            $this->getFecPersonalInfo();
        }
        return ($this->dateFso4 != "" && $date >= substr($this->dateFso4, 0, 10));
    }
    
    function isATSEC1($date=null){
        if($date == null){
            $date = date('Y-m-d');
        }
        $date = substr($date, 0, 10);
        if($this->dateAtsec1 == null){
            $this->getFecPersonalInfo();
        }
        return ($this->dateAtsec1 != "" && $date >= substr($this->dateAtsec1, 0, 10) && !$this->isATSEC2($date) &&
                                                                                        !$this->isATSEC3($date));
    }
    
    function isATSEC2($date=null){
        if($date == null){
            $date = date('Y-m-d');
        }
        $date = substr($date, 0, 10);
        if($this->dateAtsec2 == null){
            $this->getFecPersonalInfo();
        }
        return ($this->dateAtsec2 != "" && $date >= substr($this->dateAtsec2, 0, 10) && !$this->isATSEC3($date));
    }
    
    function isATSEC3($date=null){
        if($date == null){
            $date = date('Y-m-d');
        }
        $date = substr($date, 0, 10);
        if($this->dateAtsec3 == null){
            $this->getFecPersonalInfo();
        }
        return ($this->dateAtsec3 != "" && $date >= substr($this->dateAtsec3, 0, 10));
    }
    
    function isNew($date=null){
        if($date == null){
            $date = date('Y-m-d');
        }
        $date = substr($date, 0, 10);
        if($this->dateOfAppointment == null){
            $this->getFecPersonalInfo();
        }
        return ($this->dateOfAppointment != "" && $date <= substr($this->dateOfAppointment, 0, 10));
    }
    
    function isRetired($date=null){
        if($date == null){
            $date = date('Y-m-d');
        }
        $date = substr($date, 0, 10);
        if($this->dateOfRetirement == null){
            $this->getFecPersonalInfo();
        }
        return ($this->dateOfRetirement != "" && $date >= substr($this->dateOfRetirement, 0, 10));
    }
    
    /**
     * N1XXX - New assistant professor, associate professor, or professor.
     * M1XXX - New FSO2, FSO3, or FSO4.
     * A1XXX - Assistant professor.
     * B1XXX - Untenured associate professor.
     * B2XXX - Tenured associate professor.
     * C1XXX - Professor.
     * D1XXX - FSO2.
     * E1XXX - FSO3.
     * F1XXX - FSO4.
     * T1XXX - Assistant Lecturer
     * T2XXX - Associate Lecturer
     * T3XXX - Full Lecturer
     */
    function getFECType($date=null){
        if($date == null){
            $date = date('Y-m-d');
        }
        if($this->isRetired($date)){
            return "";
        }
        else if($this->isNew($date) && $this->isATSEC1("9999-99-99")){
            return "T1";                          
        }
        if($this->isRoleOn("ATS", $date) && $this->isATSEC1($date)){
            return "T1";
        }
        else if($this->isRoleOn("ATS", $date) && $this->isATSEC2($date)){
            return "T2";
        }
        else if($this->isRoleOn("ATS", $date) && $this->isATSEC3($date)){
            return "T3";
        }
        else if($this->isNew($date) && ($this->isAssistantProfessor("9999-99-99") ||
                                        $this->isAssociateProfessor("9999-99-99") ||
                                        $this->isProfessor("9999-99-99"))){
            return "N1";                          
        }
        else if($this->isNew($date) && ($this->isFSO2("9999-99-99") ||
                                        $this->isFSO3("9999-99-99") ||
                                        $this->isFSO4("9999-99-99"))){
            return "M1";
        }
        else if($this->isAssistantProfessor($date)){
            return "A1";
        }
        else if($this->isAssociateProfessor($date) && !$this->hasTenure($date)){
            return "B1";
        }
        else if($this->isAssociateProfessor($date) && $this->hasTenure($date)){
            return "B2";
        }
        else if($this->isProfessor($date)){
            return "C1";
        }
        else if($this->isFSO2($date)){
            return "D1";
        }
        else if($this->isFSO3($date)){
            return "E1";
        }
        else if($this->isFSO4($date)){
            return "F1";
        }
        return "";
    }
    
    /**
     * Returns the precomputed case number for this Person
     */
    function getCaseNumber($year=YEAR){
        if(Cache::exists("case_number{$this->getId()}_{$year}")){
            return Cache::fetch("case_number{$this->getId()}_{$year}");
        }
        else{
            $data = DBFunctions::select(array('grand_case_numbers'),
                                        array('*'),
                                        array('user_id' => $this->getId(),
                                              'year' => $year));
            if(!empty($data)){
                Cache::store("case_number{$this->getId()}_{$year}", $data[0]['number']);
                return $data[0]['number'];
            }
            Cache::store("case_number{$this->getId()}_{$year}", "");
            return "";
        }
    }
    
    function getSalary($year){
        if(!Cache::exists("salary_{$this->id}_{$year}")){
            $salary = DBFunctions::select(array('grand_user_salaries'),
                                          array('salary', 'increment'),
                                          array('user_id' => $this->getId(),
                                                'year' => $year));
            Cache::store("salary_{$this->id}_{$year}", @$salary[0]['salary']);
        }
        return Cache::fetch("salary_{$this->id}_{$year}");
    }
    
    function getIncrement($year){
        if(!Cache::exists("increment_{$this->id}_{$year}")){
            $increment = DBFunctions::select(array('grand_user_salaries'),
                                          array('salary', 'increment'),
                                          array('user_id' => $this->getId(),
                                                'year' => $year));
            Cache::store("increment_{$this->id}_{$year}", @$increment[0]['increment']);
        }
        $increment = Cache::fetch("increment_{$this->id}_{$year}");
        if($increment == ""){
            return "N/A";
        }
        return $increment;
    }
    
    function getCNA($year){
        if(!Cache::exists("cna_{$this->id}_{$year}")){
            $increment = DBFunctions::select(array('grand_cna'),
                                             array('increment'),
                                             array('user_id' => $this->getId(),
                                                    'year' => $year));
            Cache::store("cna_{$this->id}_{$year}", @$increment[0]['increment']);
        }
        return Cache::fetch("cna_{$this->id}_{$year}");
    }
    
    function create(){
        global $wgRequest;
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(STAFF)){
            parent::update();
            $status = DBFunctions::update('mw_user', 
                                    array('user_twitter' => $this->getTwitter(),
                                          'user_website' => $this->getWebsite(),
                                          'google_scholar_url' => $this->getGoogleScholar(),
                                          'sciverse_id' => $this->getSciverseId(),
                                          'orcid' => $this->getOrcId(),
                                          'wos' => $this->getWOS(),
                                          'alex_id' => $this->getAlexId(),
                                          'user_public_profile' => $this->getProfile(false),
                                          'profile_start_date' => $this->getProfileStartDate(),
                                          'profile_end_date' => $this->getProfileEndDate(),
                                          'full' => $this->full),
                                    array('user_name' => EQ($this->getName())));
            DBFunctions::commit();
            Person::$cache = array();
            Person::$aliasCache = array();
            Person::$userRows = array();
            Person::$fecInfoCache = array();
            Cache::delete("rolesCache");
            Cache::delete("allPeopleCache");
            Cache::delete("mw_user_{$this->getId()}");
            return true;
        }
        return false;
    }
    
    function update(){
        global $wgImpersonating, $wgDelegating;
        $me = Person::newFromWgUser();
        if($me->isAllowedToEdit($this)){
            parent::update();
            $status = DBFunctions::update('mw_user', 
                                          array('user_twitter' => $this->getTwitter(),
                                                'user_website' => $this->getWebsite(),
                                                'google_scholar_url' => $this->getGoogleScholar(),
                                                'sciverse_id' => $this->getSciverseId(),
                                                'orcid' => $this->getOrcId(),
                                                'wos' => $this->getWOS(),
                                                'alex_id' => $this->getAlexId(),
                                                'user_public_profile' => $this->getProfile(false)),
                                          array('user_id' => EQ($this->getId())));
            if(!$wgImpersonating && !$wgDelegating){
                DBFunctions::update('mw_user',
                                    array('profile_start_date' => $this->getProfileStartDate(),
                                          'profile_end_date' => $this->getProfileEndDate()),
                                    array('user_id' => EQ($this->getId())));
            }
            Person::$cache = array();
            Person::$aliasCache = array();
            Person::$userRows = array();
            Person::$fecInfoCache = array();
            Cache::delete("rolesCache");
            Cache::delete("allPeopleCache");
            Cache::delete("mw_user_{$this->getId()}");
            $newPerson = Person::newFromId($this->getId());
            $newPerson->getFecPersonalInfo();
            return $status;
        }
        return false;
    }
    
    function toArray(){
        $json = parent::toArray();
        $json['photo'] = $this->getPhoto();
        $json['cachedPhoto'] = $this->getPhoto(true);
        $json['phone'] = $this->getPhoneNumber();
        $json['twitter'] = $this->getTwitter();
        $json['website'] = $this->getWebsite();
        $json['googleScholarId'] = $this->getGoogleScholar();
        $json['sciverseId'] = $this->getSciverseId();
        $json['orcId'] = $this->getOrcId();
        $json['wos'] = $this->getWOS();
        $json['alexId'] = $this->getAlexId();
        $json['profile_start_date'] = $this->getProfileStartDate();
        $json['profile_end_date'] = $this->getProfileEndDate();
        $json['publicProfile'] = $this->getProfile(false);
        $json['ldap'] = $this->getLdap();
        return $json;
    }
    
}

class LimitedPerson extends Person {
    
    function LimitedPerson($data){
        self::Person($data);
        $this->full = 0;
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
