<?php

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

	var $name;
	var $email;
	var $nationality;
	var $gender;
	var $photo;
	var $twitter;
	var $publicProfile;
	var $privateProfile;
	var $realname;
	var $projects;
	var $university;
	var $isProjectLeader;
	var $isProjectCoLeader;
	var $groups;
	var $roles;
	var $isEvaluator = null;
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
	var $hqpCache = array();
	
	// Returns a new Person from the given id
	static function newFromId($id){
	    global $wgUser;
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
	
	// Returns a new Person from the given name
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
	
	// Returns a new Person from the given email (null if not found)
	// In the event of a collision, the first user is returned
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
	
	// Creates a new Person from the given Mediawiki User
	static function newFromUser($user){
	    return Person::newFromId($user->getId());
	}
	
	// Creates a new Person from the current $wgUser User
	static function newFromWgUser(){
	    global $wgUser;
	    return Person::newFromId($wgUser->getId());
	}
	
	// Returns a new Person from the given name
	static function newFromNameLike($name){
	    global $wgSitename;
	    $tmpPerson = Person::newFromName(str_replace(" ", ".", $name));
	    if($tmpPerson->getName() != ""){
	        return $tmpPerson;
	    }
	    $name = str_replace(".", ".*", $name);
        $name = str_replace(" ", ".*", $name);
	    if(isset(Person::$cache[$name])){
	        return Person::$cache[$name];
	    }
	    self::generateNamesCache();
		$data = array();
		if(function_exists('apc_exists') && apc_exists($wgSitename.'person_name'.$name)){
		    $possibleNames = unserialize(apc_fetch($wgSitename.'person_name'.$name));
		}
		else{
		    $possibleNames = preg_grep("/.*$name.*/i", array_keys(self::$namesCache));
		    if(function_exists('apc_store')){
		        apc_store($wgSitename.'person_name'.$name, serialize($possibleNames), 60*60);
		    }
		}
		foreach($possibleNames as $possible){
		    if(isset(self::$namesCache[$possible])){
		        $data[] = self::$namesCache[$possible];
		        break;
		    }
		}
		$person = new Person($data);
		if(isset(self::$cache[$person->id]) && $person->id != ""){
		    $person = self::$cache[$person->id];
		    self::$cache[$person->name] = &$person;
            self::$cache[$name] = &$person;
		}
		else{
            self::$cache[$person->id] = &$person;
            self::$cache[$person->name] = &$person;
            self::$cache[$name] = &$person;
        }
		return $person;
	}

	/// Returns a new Person instance from the given alias, if found and
	/// the respective user ID is valid (ie, non-zero).
	/// NOTE: if the alias is not unique, an exception is thrown instead.
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
	
	// Caches the resultset of the alias table for superfast access
	static function generateAliasCache(){
	    if(count(self::$aliasCache) == 0){
			$uaTable = getTableName("user_aliases");
			$uTable = getTableName("user");
			$sql = "SELECT ua.alias, u.user_id, u.user_name, u.user_real_name, u.user_email, u.user_twitter, user_public_profile, user_private_profile, user_nationality, user_gender
				FROM {$uaTable} as ua, {$uTable} as u 
				WHERE ua.user_id = u.user_id
				AND u.deleted != '1'";
			$data = DBFunctions::execSQL($sql);
			foreach($data as $row){
			    self::$aliasCache[$row['alias']] = array(0 => $row);
			}
	    }
	}
	
	// Caches the resultset of the user table for superfast access
	static function generateNamesCache(){
	    if(count(self::$namesCache) == 0){
		    $uTable = getTableName("user");
		    $sql = "SELECT `user_id`,`user_name`,`user_real_name`,`user_email`,`user_twitter`,`user_public_profile`,`user_private_profile`,`user_nationality`,`user_gender`
			    FROM $uTable u
			    WHERE `deleted` != '1'";
		    $data = DBFunctions::execSQL($sql);
		    foreach($data as $row){
		        self::$namesCache[$row['user_name']] = $row;
		        self::$idsCache[$row['user_id']] = $row;
		        if(trim($row['user_real_name']) != '' && $row['user_name'] != trim($row['user_real_name'])){
		            self::$namesCache[str_replace("&nbsp;", " ", $row['user_real_name'])] = $row;
		        }
		    }
		}
	}
	
	// Caches the resultset of the user roles table
	// NOTE: This only caches the current roles, not the history
	static function generateRolesCache(){
	    if(count(self::$rolesCache) == 0){
	        $sql = "SELECT *
                    FROM grand_roles
                    WHERE end_date = '0000-00-00 00:00:00'
                    OR end_date > CURRENT_TIMESTAMP";
            $data = DBFunctions::execSQL($sql);
            if(count($data) > 0){
                foreach($data as $row){
                    if(!isset(self::$rolesCache[$row['user']])){
                        self::$rolesCache[$row['user']] = array();
                    }
                    self::$rolesCache[$row['user']][] = $row;
                }
            }
            else{
                $this->id = $data[0]['id'];
			    $this->user = $data[0]['user'];
			    $this->role = $data[0]['role'];
			    $this->startDate = $data[0]['start_date'];
			    $this->endDate = $data[0]['end_date'];
			    $this->comment = $data[0]['comment'];
                self::$rolesCache[$this->id][] = array(0 => array('id' => '-1',
                                                                  'user' => $this->id,
                                                                  'role' => INACTIVE,
                                                                  'start_date' => '0000-00-00 00:00:00',
                                                                  'end_date' => '0000-00-00 00:00:00',
                                                                  'comment' => ''));
            }
	    }
	}
	
	// Caches the resultset of the co leaders
	static function generateCoLeaderCache(){
	    if(count(self::$coLeaderCache) == 0){
	        $sql = "SELECT *
	                FROM grand_project_leaders, grand_project p
	                WHERE co_lead = 'True'
	                AND p.id = project_id
	                AND (end_date = '0000-00-00 00:00:00'
                         OR end_date > CURRENT_TIMESTAMP)";
	        $data = DBFunctions::execSQL($sql);
	        foreach($data as $row){
	            self::$coLeaderCache[$row['user_id']][] = $row;
	        }
	    }
	}
	
	// Caches the resultset of the leaders
	static function generateLeaderCache(){
	    if(count(self::$leaderCache) == 0){
	        $sql = "SELECT *
	                FROM grand_project_leaders, grand_project p
	                WHERE co_lead = 'False'
	                AND p.id = project_id
	                AND (end_date = '0000-00-00 00:00:00'
                         OR end_date > CURRENT_TIMESTAMP)";
	        $data = DBFunctions::execSQL($sql);
	        foreach($data as $row){
	            self::$leaderCache[$row['user_id']][] = $row;
	        }
	    }
	}
	
	static function generateUniversityCache(){
        if(count(self::$universityCache) == 0){
            $sql = "SELECT * 
                    FROM mw_user_university uu, mw_universities u
                    WHERE u.university_id = uu.university_id
                    ORDER BY uu.id DESC";
            $data = DBFunctions::execSQL($sql);
            if(DBFunctions::getNRows() > 0){
                foreach($data as $row){
                    if(!isset(self::$universityCache[$row['user_id']])){
                        self::$universityCache[$row['user_id']] = 
                            array("university" => str_replace("&", "&amp;", $row['university_name']),
                                  "department" => str_replace("&", "&amp;", $row['department']),
                                  "position"   => str_replace("&", "&amp;", $row['position']));
                    }
                }
            }
        }
    }
    
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
	
	// Returns an array of all Univeristy names
	static function getAllUniversities(){
	    //TODO: This should eventually be extracted to a new Class
	    $sql = "SELECT * FROM `mw_universities`";
	    $data = DBFunctions::execSQL($sql);
	    $universities = array();
	    foreach($data as $row){
	        $universities[] = $row['university_name'];
	    }
	    return $universities;
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
	
	// Returns an array of People of the type $filter, and have at least one project
	// If $filter='all' then, even people with no projects are included.
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
	        //$projects = $person->getProjects();
	        if($person->getName() != "WikiSysop" && ($filter == null || $filter == "all" || $person->isRole($filter))){
	            $people[] = $person;
	        }
	    }
	    return $people;
	}
    
    // Returns an array of People of the type $filter, and have at least one project
    // If $filter='all' then, even people with no projects are included.
    static function getAllPeopleDuring($filter=null, $startRange = false, $endRange = false){
        $data = DBFunctions::select(array('mw_user'),
	                                array('user_id', 'user_name'),
	                                array('deleted' => NEQ(1)),
	                                array('user_name' => 'ASC'));
        $people = array();
        foreach($data as $row){
            $rowA = array();
            $rowA[0] = $row;
            $person = Person::newFromId($rowA[0]['user_id']);
            //$projects = $person->getProjects();
            if($person->getName() != "WikiSysop" && ($filter == null || $filter == "all" || $person->isRoleDuring($filter, $startRange, $endRange))){
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
			$filterout[] = 4; // Admin
			$filterout[] = 150; // Adrian.Sheppard
			$filterout = implode(',', $filterout);
		}
		if (strlen($filterout) > 0)
			$filterout = "WHERE eval_id NOT IN ({$filterout})";

		$ret = array();
		$data = DBFunctions::execSQL("SELECT DISTINCT eval_id FROM mw_eval {$filterout};");
		foreach ($data as &$q){
			$ret[$q['eval_id']] = Person::newFromId($q['eval_id']);
		}
		return $ret;
	}

	static function getAllProjectManagers() {
		
		$ret = array();
		$sql = "SELECT pl.user_id FROM grand_project_leaders pl, mw_user u
				WHERE pl.user_id NOT IN (4, 150)
				AND pl.manager = '1'
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
	    $json = array('id' => $this->getId(),
	                  'name' => $this->getName(),
	                  'realName' => $this->getRealName(),
	                  'fullName' => $this->getNameForForms(),
	                  'reversedName' => $this->getReversedName(),
	                  'email' => $this->getEmail(),
	                  'gender' => $this->getGender(),
	                  'nationality' => $this->getNationality(),
	                  'twitter' => $this->getTwitter(),
	                  'photo' => $this->getPhoto(),
	                  'cachedPhoto' => $this->getPhoto(true),
	                  'university' => $this->getUni(),
	                  'department' => $this->getDepartment(),
	                  'position' => $this->getPosition(),
	                  'publicProfile' => $publicProfile,
	                  'privateProfile' => $publicProfile,
	                  'url' => $this->getURL());
	    return $json;
	}
	
	function create(){
	    global $wgRequest;
	    $me = Person::newFromWGUser();
	    if($me->isRoleAtLeast(STAFF)){
	        $wgRequest->setVal('wpCreateaccountMail', true);
	        $wgRequest->setSessionData('wsCreateaccountToken', 'true');
	        $wgRequest->setVal('wpCreateaccountToken', 'true');
	        $wgRequest->setVal('wpName', $this->name);
	        $wgRequest->setVal('wpEmail', $this->email);
	        $_POST['wpCreateaccountMail'] = 'true';
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
	    $me = Person::newFromWGUser();
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
	    $me = Person::newFromWGUser();
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
	    return User::newFromId($this->id);
	}
	
	// Returns whether or not this Person is logged in or not
	function isLoggedIn(){
	    $user = $this->getUser();
	    return $user->isLoggedIn();
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
	    $projects = $this->getProjects();
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
	function isMemberOfDuring($project, $start=false, $end=false){
	    if( $start === false || $end === false ){
	        $start = date(REPORTING_CYCLE_START);
	        $end = date(REPORTING_CYCLE_END);
	    }
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
	
	// Returns whether or not this Person is a member of the give project name or not.
	function is_member($proj) {
	    if(DEBUG){
	        trigger_error("Deprecated function 'is_member()' called.", E_USER_NOTICE);
	    }
	    return isMemberOf($proj);
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
	
    // Returns the name of this Person's twitter account
	function getTwitter(){
		return $this->twitter;
	}
	
	// Returns the url of this Person's profile page
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
	    if($this->photo == null || $cached){
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
	    if($last != ""){
	        return "{$last}, {$first}";
	    }
	    else{
	        return "{$first}";
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
	        return array("studies" => "",
	                     "city" => "",
	                     "works" => "",
	                     "employer" => "",
	                     "country" => "");
	    }
	}

	// Returns the moved on row for when HQPs are inactivated
	// Returns an array of key/value pairs representing the DB row
	function getAllMovedOnDuring( $startRange = false, $endRange = false ){
		 //If no range end are provided, assume it's for the current year.
	    if( $startRange === false || $endRange === false ){
	        $startRange = date(REPORTING_YEAR."-01-01 00:00:00");
	        $endRange = date(REPORTING_YEAR."-12-31 23:59:59");
	        //$endRange = date("2012-08-31 23:59:59");
	    }

	    $sql = "SELECT `user_id`
	            FROM `grand_movedOn`
	            WHERE date BETWEEN '$startRange' AND '$endRange'";

	    $data = DBFunctions::execSQL($sql);
	    $people = array();
	    foreach($data as $row){
	    	$people[] = Person::newFromId($row['user_id']);
	    }

	    return $people;
	}
	
	// Returns the reported thesis for when HQPs are inactivated
	function getThesis(){
	    $sql = "SELECT *
	            FROM `grand_theses`
	            WHERE `user_id` = '{$this->getId()}'";
	    $data = DBFunctions::execSQL($sql);
	    $paper = null;
	    if(DBFunctions::getNRows() > 0){
	        $paper = Paper::newFromId($data[0]['publication_id']);
	        if($paper->getId() == 0){
	            $paper = null;
	        }
	    }
	    
	    //Not in theses table, try to find a publication
	    if(is_null($paper)){
	    	$name = str_replace('.', ' ', $this->getName());
	    	$name = mysql_real_escape_string($name);
	    	$sql = "SELECT * FROM grand_products
	    	 		WHERE authors LIKE '%$name%'
        			AND category = 'Publication' 
        			AND type IN('Masters Thesis','PhD Thesis') 
        			LIMIT 1";
    		$data = DBFunctions::execSQL($sql);
    		if(isset($data[0])){
    			$paper = Paper::newFromId($data[0]['id']);
    		}
        	
	    }
	    return $paper;
	}
	
	// Returns the biography of the Person
	function getBiography(){
        if(DEBUG){
	        trigger_error("Deprecated function 'getBiography()' called.", E_USER_NOTICE);
        }
        $ns = 0;
        if($this->isPNI()){
            $ns = NS_GRAND_NI;
        }
        else if($this->isCNI()){
            $ns = NS_GRAND_CR;
        }
        else if($this->isHQP()){
            $ns = NS_STUDENT;
        }
        $title = Title::newFromText($this->getName(), $ns);
        $article = Article::newFromId($title->getArticleId());
        if($article != null){
            $text = $article->getRawText();
            $bio = preg_replace("/}}$/", "", $text);
            $bio = preg_replace("/\|events.*/s", "", $bio);
            $bio = preg_replace("/\|biography_public.*/s", "", $bio);
            $bio = preg_replace("/^.*\|biography = /s", "", $bio);
            $bio = preg_replace("/\&#39;/", "'", $bio);
            $bio = preg_replace("/\&quot;/", "\"", $bio);
            $bio = preg_replace("/\&/", "&amp;", $bio);
            return $bio;
        }
        else{
            return "";
        }
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

    function getUni(){
        $university = $this->getUniversity();
        return (isset($university['university'])) ? $university['university'] : "";
    }

    function getDepartment(){
        $university = $this->getUniversity();
        return (isset($university['department'])) ? $university['department'] : "";
    }

    function getPosition(){
        $university = $this->getUniversity();
        return (isset($university['position'])) ? $university['position'] : "";
    }
	
	/**
	 * Returns the last University that this Person was at between the given range
	 * @param string $startRange The start date to look at (default start of the current reporting year)
	 * @param string $endRange The end date to look at (default end of the current reporting year)
	 * @return array The last University that this Person was at between the given range
	 */ 
	function getUniversityDuring($startRange=false, $endRange=false){
	    if( $startRange === false || $endRange === false ){
	        $startRange = date(REPORTING_YEAR."-01-01 00:00:00");
	        $endRange = date(REPORTING_YEAR."-12-31 23:59:59");
	    }
        $uTable = getTableName("universities");
        $uuTable = getTableName("user_university");
        $sql = "SELECT * 
	            FROM $uuTable uu, $uTable u
	            WHERE uu.user_id = '{$this->id}'
	            AND u.university_id = uu.university_id
	            AND ( 
                ( (end_date != '0000-00-00 00:00:00') AND
                (( start_date BETWEEN '$startRange' AND '$endRange' ) || ( end_date BETWEEN '$startRange' AND '$endRange' ) || (start_date <= '$startRange' AND end_date >= '$endRange') ))
                OR
                ( (end_date = '0000-00-00 00:00:00') AND
                ((start_date <= '$endRange')))
                )
				ORDER BY uu.id DESC";
	    $data = DBFunctions::execSQL($sql);
        if(DBFunctions::getNRows() > 0){
            return array("university" => str_replace("&", "&amp;", $data[0]['university_name']),
	                     "department" => str_replace("&", "&amp;", $data[0]['department']),
	                     "position"   => str_replace("&", "&amp;", $data[0]['position']));
        }
        else{
            return null;
        }
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
	 * @param string $startRange The start date to look at (default start of the current reporting year)
	 * @param string $endRange The end date to look at (default end of the current reporting year)
	 * @param boolean $checkLater Whether or not to check the current Discipline if the range specified does not return any results
	 * @return string The name of the discipline that this Person belongs to during the specified dates
	 */
	function getDisciplineDuring($startRange=false, $endRange=false, $checkLater=false){
	    self::generateDisciplineMap();
	    if( $startRange === false || $endRange === false ){
	        $startRange = date(REPORTING_YEAR."-01-01 00:00:00");
	        $endRange = date(REPORTING_YEAR."-12-31 23:59:59");
	    }
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
	
	// Returns an array of roles that the user is a part of
	// If history is set to true, then all the roles regardless of date are included
	function getRoles($history=false){
	    if($history !== false && $this->id != null){
			$this->roles = array();
			if($history === true){
			    $data = DBFunctions::select(array('grand_roles'),
			                                array('*'),
			                                array('user' => $this->id),
			                                array('end_date' => 'DESC'));
            }
            else{
                $sql = "SELECT *
                        FROM grand_roles
                        WHERE user = '{$this->id}'
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
		                                                   'user' => $this->id,
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
		                                               'user' => $this->id,
		                                               'role' => "PL",
		                                               'start_date' => '0000-00-00 00:00:00',
		                                               'end_date' => '0000-00-00 00:00:00',
		                                               'comment' => '')));
		}
		if($this->isProjectCoLeader() && !$pm){
		    $roles[] = new Role(array(0 => array('id' => -1,
		                                               'user' => $this->id,
		                                               'role' => "COPL",
		                                               'start_date' => '0000-00-00 00:00:00',
		                                               'end_date' => '0000-00-00 00:00:00',
		                                               'comment' => '')));
		}
		if($pm){
		    $roles[] = new Role(array(0 => array('id' => -1,
		                                               'user' => $this->id,
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
		                                               'user' => $this->id,
		                                               'role' => "PL",
		                                               'start_date' => '0000-00-00 00:00:00',
		                                               'end_date' => '0000-00-00 00:00:00',
		                                               'comment' => '')));
		}
		if($this->isProjectCoLeaderDuring($startDate, $endDate) && !$pm){
		    $roles[] = new Role(array(0 => array('id' => -1,
		                                               'user' => $this->id,
		                                               'role' => "COPL",
		                                               'start_date' => '0000-00-00 00:00:00',
		                                               'end_date' => '0000-00-00 00:00:00',
		                                               'comment' => '')));
		}
		if($pm){
		    $roles[] = new Role(array(0 => array('id' => -1,
		                                               'user' => $this->id,
		                                               'role' => "PM",
		                                               'start_date' => '0000-00-00 00:00:00',
		                                               'end_date' => '0000-00-00 00:00:00',
		                                               'comment' => '')));
		}
		return $roles;
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
	// During a given range. If no range is provided, the range for the current year is given
	function getRolesDuring($startRange = false, $endRange = false){
	    if($this->id == 0){
	        return array();
	    }
	    //If no range end are provided, assume it's for the current year.
	    if( $startRange === false || $endRange === false ){
	        $startRange = date(REPORTING_YEAR."-01-01 00:00:00");
	        $endRange = date(REPORTING_YEAR."-12-31 23:59:59");
	    }
	    
	    $sql = "SELECT *
                FROM grand_roles
                WHERE user = '{$this->id}'
                AND ( 
                ( (end_date != '0000-00-00 00:00:00') AND
                (( start_date BETWEEN '$startRange' AND '$endRange' ) || ( end_date BETWEEN '$startRange' AND '$endRange' ) || (start_date <= '$startRange' AND end_date >= '$endRange') ))
                OR
                ( (end_date = '0000-00-00 00:00:00') AND
                ((start_date <= '$endRange')))
                )";
        $data = DBFunctions::execSQL($sql);
		$roles = array();
		foreach($data as $row){
			$roles[] = new Role(array(0 => $row));
		}
		return $roles;        
	}    
	
	// Returns an array of Projects that this Person is a part of
	// If history is set to true, then all the Projects regardless of date are included
	function getProjects($history=false){
		if($this->projects == null && $this->id != null){
			$this->projects = array();
			$sql = "SELECT u.project_id
                    FROM grand_user_projects u
                    WHERE user = '{$this->id}' \n";
            if($history === false){
                $sql .= "AND (end_date = '0000-00-00 00:00:00'
                         OR end_date > CURRENT_TIMESTAMP)\n";
            }
            else if($history !== true){
                $sql .= "AND start_date <= '{$history}'
                         AND (end_date >= '{$history}' OR (end_date = '0000-00-00 00:00:00'))\n";
            }
            $sql .= "ORDER BY project_id";
			$data = DBFunctions::execSQL($sql);
			$projectNames = array();
			foreach($data as $row){
			    $project = Project::newFromId($row['project_id']);
			    if($project != null && $project->getName() != ""){
			        if(!isset($projectNames[$project->getName()])){
			            if(!$project->isDeleted() || ($project->isDeleted() && $history)){
			                // Make sure that the project is not being added twice
			                $projectNames[$project->getName()] = true;
				            $this->projects[] = $project;
				        }
				    }
				}
			}
		}
		//else{
		//    $this->projects = array();
		//}
		return $this->projects;
	}
	
	// Returns an array of Projects that this Person is a part of
	// TODO: This might be slow.
	function getProjectsDuring($start=REPORTING_CYCLE_START, $end=REPORTING_CYCLE_END){
	    $projectsDuring = array();
	    $projects = $this->getProjects(true);
	    if(count($projects) > 0){
	        foreach($projects as $project){
	            if(!$project->isDeleted() || ($project->isDeleted() && 
	                                          !(strcmp($project->effectiveDate, $end) < 0 && 
	                                            strcmp($project->effectiveDate, $start) > 0))){
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
	    return $projectsDuring;
	}
	
	// Returns the name of the partner of this user
	function getPartnerName(){
	    $sql = "SELECT *
	            FROM `grand_champion_partners`
	            WHERE `user_id` = '{$this->id}'";
	    $data = DBFunctions::execSQL($sql);
	    if(DBFunctions::getNRows() > 0){
	        return $data[0]['partner'];
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
	            $this->contributions[] = Contribution::newFromId($row['id']);
	        }
	    }
	    return $this->contributions;
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
	    $sql = "SELECT *
	            FROM `grand_recordings`
	            WHERE person = '{$this->id}'";
	    $data = DBFunctions::execSQL($sql);
	    $array = array();
	    foreach($data as $row){
	        $events = json_decode($row['story']);
	        $story = (object)'a';
	        $story->id = $row['id'];
	        $story->person = $row['person'];
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
            if($role == INACTIVE){
                return false;
            }
            else{
                return true;
            }
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
	    if($this->isHQP()){
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
    
    // Returns whether this Person is of type $role or not during a specific period
    function isRoleDuring($role, $startRange = false, $endRange = false){
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
	    $requestTable = getTableName("user_create_request");
	    $sql = "SELECT DISTINCT requesting_user 
	            FROM $requestTable
	            WHERE wpName = '{$this->name}'";
	    $data = DBFunctions::execSQL($sql);
	    $creators = array();
		foreach($data as $row){
			$creators[] = Person::newFromName($row['requesting_user']);
		}
		return $creators;
	}
	
	function getRequestedMembers(){
	    $requestTable = getTableName("user_create_request");
	    $sql = "SELECT DISTINCT wpName
	            FROM $requestTable
	            WHERE requesting_user = '{$this->name}'
	            AND created = 'true'";
	    $data = DBFunctions::execSQL($sql);
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
    
    function getChampionsDuring($startRange = false, $endRange = false){
        if($startRange === false || $endRange === false ){
            $startRange = date(REPORTING_YEAR."-01-01 00:00:00");
            $endRange = date(REPORTING_YEAR."-12-31 23:59:59");
        }
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
    
    function getHQPDuring($startRange = false, $endRange = false){
        if( $startRange === false || $endRange === false ){
            $startRange = date(REPORTING_YEAR."-01-01 00:00:00");
            $endRange = date(REPORTING_YEAR."-12-31 23:59:59");
        }
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
				$people[] = Person::newFromId($row['user1']);
			}
			return $people;
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
		    $people[] = $person;
		}
		return $people;
    }
    
    function getSupervisorsDuring($startRange = false, $endRange = false){
        if( $startRange === false || $endRange === false ){
            $startRange = date(REPORTING_YEAR."-01-01 00:00:00");
            $endRange = date(REPORTING_YEAR."-12-31 23:59:59");
        }
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
	 * @return array Returns an array of Paper(s) authored or co-authored by this Person _or_ their HQP
	 */ 
	function getPapers($category="all", $history=false, $grand='grand'){
	    self::generateAuthorshipCache();
        $processed = array();
        $papersArray = array();
        $papers = array();
        foreach($this->getHQP($history) as $hqp){
            $ps = $hqp->getPapers();
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
	                $papers[] = $id;
	            }
	        }
	    }
	    
	    foreach($papers as $pId){
	        $paper = Paper::newFromId($pId);
	        if(!$paper->deleted && ($category == 'all' || $paper->getCategory() == $category) &&
	           count($paper->getProjects()) > 0){
	            $papersArray[] = $paper;
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
    function getPapersAuthored($category="all", $startRange = false, $endRange = false, $includeHQP=false){
        if( $startRange === false || $endRange === false ){
	        $startRange = date(REPORTING_YEAR."-01-01 00:00:00");
	        $endRange = date(REPORTING_YEAR."-12-31 23:59:59");
	    }
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
	    
	    foreach($papers as $pId){
	        $paper = Paper::newFromId($pId);
	        $date = $paper->getDate();
	        if(!$paper->deleted && ($category == 'all' || $paper->getCategory() == $category) &&
	           count($paper->getProjects()) > 0 &&
	           (strcmp($date, $startRange) >= 0 && strcmp($date, $endRange) <= 0 )){
	            $papersArray[] = $paper;
	        }
	    }
	    return $papersArray;
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
	
	// Returns an array of projects that this person is a leader or co-leader.
	function leadership($history=false) {
		$ret = array();
		if(!$history){
		    if(isset($this->leadershipCache['current'])){
		        return $this->leadershipCache['current'];
		    }
		    $res = DBFunctions::execSQL("SELECT p.name AS project_name 
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
	        $res = DBFunctions::execSQL("SELECT p.name AS project_name 
		                                 FROM grand_project_leaders l, grand_project p
		                                 WHERE l.project_id = p.id
										 AND l.user_id = '{$this->id}'");
	    }
		foreach ($res as &$row) {
		    $project = Project::newFromName($row['project_name']);
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
	function leadershipDuring($startRange = false, $endRange = false){
	    //If no range end are provided, assume it's for the current year.
	    if( $startRange === false || $endRange === false ){
	        $startRange = date(REPORTING_YEAR."-01-01 00:00:00");
	        $endRange = date(REPORTING_YEAR."-12-31 23:59:59");
	    }
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
			$projects[] = Project::newFromId($row['project_id']);
		}
		$this->leadershipCache[$startRange.$endRange] = $projects;
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
        foreach($p->getPreds() as $pred){
	        if($this->leadershipOf($pred)){
	            return true;
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
	
	function isProjectLeaderDuring($startRange = false, $endRange = false){
	    //If no range end are provided, assume it's for the current year.
	    if( $startRange === false || $endRange === false ){
	        $startRange = date(REPORTING_YEAR."-01-01 00:00:00");
	        $endRange = date(REPORTING_YEAR."-12-31 23:59:59");
	    }
	    $sql = "SELECT p.id
                FROM grand_project_leaders, grand_project p
                WHERE manager = '0'
                AND co_lead <> 'True'
                AND p.id = project_id
                AND user_id = '{$this->id}' 
                AND ( 
                ( (end_date != '0000-00-00 00:00:00') AND
                (( start_date BETWEEN '$startRange' AND '$endRange' ) || ( end_date BETWEEN '$startRange' AND '$endRange' ) || (start_date <= '$startRange' AND end_date >= '$endRange') ))
                OR
                ( (end_date = '0000-00-00 00:00:00') AND
                ((start_date <= '$endRange')))
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
                    FROM grand_project_leaders, grand_project p
                    WHERE manager = '1'
                    AND p.id = project_id
                    AND user_id = '{$this->id}' 
                    AND (end_date = '0000-00-00 00:00:00'
                         OR end_date > CURRENT_TIMESTAMP)";
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
	
	function isProjectManagerDuring($startRange = false, $endRange = false){
	    //If no range end are provided, assume it's for the current year.
	    if( $startRange === false || $endRange === false ){
	        $startRange = date(REPORTING_YEAR."-01-01 00:00:00");
	        $endRange = date(REPORTING_YEAR."-12-31 23:59:59");
	    }
	    $sql = "SELECT p.id
                FROM grand_project_leaders, grand_project p
                WHERE manager = '1'
                AND p.id = project_id
                AND user_id = '{$this->id}' 
                AND ( 
                ( (end_date != '0000-00-00 00:00:00') AND
                (( start_date BETWEEN '$startRange' AND '$endRange' ) || ( end_date BETWEEN '$startRange' AND '$endRange' ) || (start_date <= '$startRange' AND end_date >= '$endRange') ))
                OR
                ( (end_date = '0000-00-00 00:00:00') AND
                ((start_date <= '$endRange')))
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
		                             AND l.manager = '1'
		                             AND (l.end_date = '0000-00-00 00:00:00'
                                          OR l.end_date > CURRENT_TIMESTAMP)");
	   
        if(DBFunctions::getNRows() > 0){
            return true;
        }
	    foreach($p->getPreds() as $pred){
	        if($this->managementOf($pred)){
	            return true;
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
	
	function isProjectCoLeaderDuring($startRange = false, $endRange = false){
	    //If no range end are provided, assume it's for the current year.
	    if( $startRange === false || $endRange === false ){
	        $startRange = date(REPORTING_YEAR."-01-01 00:00:00");
	        $endRange = date(REPORTING_YEAR."-12-31 23:59:59");
	    }
	    $sql = "SELECT p.id
                FROM grand_project_leaders, grand_project p
                WHERE manager = '0'
                AND co_lead = 'True'
                AND p.id = project_id
                AND user_id = '{$this->id}' 
                AND ( 
                ( (end_date != '0000-00-00 00:00:00') AND
                (( start_date BETWEEN '$startRange' AND '$endRange' ) || ( end_date BETWEEN '$startRange' AND '$endRange' ) || (start_date <= '$startRange' AND end_date >= '$endRange') ))
                OR
                ( (end_date = '0000-00-00 00:00:00') AND
                ((start_date <= '$endRange')))
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
	            AND l.co_lead <> 'True'
	            AND l.manager = '0'\n";
	    if(!$history){
	        $sql .= "AND (l.end_date = '0000-00-00 00:00:00'
                          OR l.end_date > CURRENT_TIMESTAMP)";
	    }
	    $data = DBFunctions::execSQL($sql);
	    $projects = array();
	    foreach($data as $row){
	        $projects[] = Project::newFromId($row['project_id']);
	    }
	    return $projects;
	}
	
	function getCoLeadProjects($history=false){
	    $sql = "SELECT *
	            FROM grand_project_leaders l
	            WHERE l.user_id = '{$this->id}'
	            AND l.co_lead = 'True'
	            AND l.manager = '0'\n";
	    if(!$history){
	        $sql .= "AND (end_date = '0000-00-00 00:00:00'
                          OR end_date > CURRENT_TIMESTAMP)";
	    }
	    $data = DBFunctions::execSQL($sql);
	    $projects = array();
	    foreach($data as $row){
	        $projects[] = Project::newFromId($row['project_id']);
	    }
	    return $projects;
	}
	
	function getLeadAndCoLeadProjects($history=false){
	    $sql = "SELECT *
	            FROM grand_project_leaders l
	            WHERE l.user_id = '{$this->id}'
	            AND l.manager = '0'\n";
	    if(!$history){
	        $sql .= "AND (end_date = '0000-00-00 00:00:00'
                          OR end_date > CURRENT_TIMESTAMP)";
	    }
	    $data = DBFunctions::execSQL($sql);
	    $projects = array();
	    foreach($data as $row){
	        $projects[] = Project::newFromId($row['project_id']);
	    }
	    return $projects;
	}
	
	function getManagerProjects($history=false){
	    $sql = "SELECT l.*
	            FROM grand_project_leaders l
	            WHERE l.user_id = '{$this->id}'
	            AND l.manager = '1'\n";
	    if(!$history){
	        $sql .= "AND (l.end_date = '0000-00-00 00:00:00'
                          OR l.end_date > CURRENT_TIMESTAMP)";
	    }
	    $data = DBFunctions::execSQL($sql);
	    $projects = array();
	    foreach($data as $row){
	        $projects[] = Project::newFromId($row['project_id']);
	    }
	    return $projects;
	}
	
	function getLeadThemes($history=false){
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
	        $themes[$row['theme']] = $row['theme'];
	    }
	    return $themes;
	}
	
	function getCoLeadThemes($history=false){
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
	        $themes[$row['theme']] = $row['theme'];
	    }
	    return $themes;
	}
	
	function getBudget($year){
	    global $wgServer,$wgScriptPath;
	    $index = 'b'.$year;
	    if(isset($this->budgets[$index])){
	        return unserialize($this->budgets[$index]);
	    }
	    $pg = "Special:Report";
	    if($year != 2010){
	        $sd = new SessionData($this->id, $pg, SD_BUDGET_EXCEL);
	    }
	    else{
	        $sd = new SessionData($this->id, $pg, SD_BUDGET_CSV);
	    }
	    $data = $sd->fetch(false);
	    $lastChanged = $sd->last_update();
	    $fileName = CACHE_FOLDER."personBudget{$this->id}_$index";
	    if(file_exists($fileName)){
		    $contents = unserialize(implode("", gzfile($fileName)));
		    if(strcmp($contents[0], $lastChanged) == 0){
		        $this->budgets[$index] = serialize($contents[1]);
		        return unserialize($this->budgets[$index]);
		    }
		}
	    if (! empty($data)) {
	        if($year != 2010){
		        $this->budgets[$index] = new Budget("XLS", REPORT_STRUCTURE, $data);
		    }
		    else {
		        $data = $sd->fetch(false);
		        $this->budgets[$index] = new Budget("CSV", REPORT_STRUCTURE, $data);
		    }
            if($this->budgets[$index]->nRows()*$this->budgets[$index]->nCols() > 1 && 
               isset($this->budgets[$index]->xls[0][2]) && 
               $this->budgets[$index]->xls[0][1]->getValue() == ""){
                $this->budgets[$index]->xls[0][1]->setValue($this->name);
            }
            if(is_writable(CACHE_FOLDER)){
                $contents = array($lastChanged, $this->budgets[$index]);
                $zp = gzopen($fileName, "w9");
			    gzwrite($zp, serialize($contents));
			    gzclose($zp);
            }
            $this->budgets[$index] = serialize($this->budgets[$index]);
		    return unserialize($this->budgets[$index]);
	    }
	    else{
	        return null;
	    }
	}
	
	function getAllocatedBudget($year){
	    global $wgServer,$wgScriptPath;
	    $index = 's'.$year;
	    if(isset($this->budgets[$index])){
	        return unserialize($this->budgets[$index]);
	    }
	    $pg = "Special:SupplementalReport";
	    if($year != 2010){
	        return $this->getRequestedBudget($year, RES_ALLOC_BUDGET);
	    }
	    else{
	        $sd = new SessionData($this->id, $pg, SD_SUPPL_BUDGET);
	    }
	    $data = $sd->fetch(false);
	    $lastChanged = $sd->last_update();
	    $fileName = CACHE_FOLDER."personBudget{$this->id}_$index";
	    if(file_exists($fileName)){
		    $contents = unserialize(implode("", gzfile($fileName)));
		    if(strcmp($contents[0], $lastChanged) == 0){
		        $this->budgets[$index] = serialize($contents[1]);
		        return unserialize($this->budgets[$index]);
		    }
		}
	    if (! empty($data)) {
	        if($year != 2010){
	            if($this->isRoleDuring(CNI, $year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH)){
		            $this->budgets[$index] = new Budget("XLS", REPORT2_STRUCTURE, $data);
		        }
		        else{
		            $this->budgets[$index] = new Budget("XLS", SUPPLEMENTAL_STRUCTURE, $data);
		        }
		    }
		    else {
		        $data = $sd->fetch(false);
		        $this->budgets[$index] = new Budget("XLS", SUPPLEMENTAL_STRUCTURE, $data);
		    }
            if($this->budgets[$index]->nRows()*$this->budgets[$index]->nCols() > 1){
                $names = $this->splitName();
                $this->budgets[$index]->xls[0][1]->setValue($names['last'].', '.$names['first']);
            }
            if(is_writable(CACHE_FOLDER)){
                $contents = array($lastChanged, $this->budgets[$index]);
                $zp = gzopen($fileName, "w9");
			    gzwrite($zp, serialize($contents));
			    gzclose($zp);
            }
		    $this->budgets[$index] = serialize($this->budgets[$index]);
            return unserialize($this->budgets[$index]);
	    }
	    else{
	        $fileName2 = "data/supplemental_budget_2011.xls";
	        $data = file_get_contents($fileName2);
	        $this->budgets[$index] = new Budget("XLS", SUPPLEMENTAL_STRUCTURE, $data);
	        $names = $this->splitName();
            $this->budgets[$index]->xls[0][1]->setValue($names['last'].', '.$names['first']);
            if(is_writable(CACHE_FOLDER)){
                $contents = array($lastChanged, $this->budgets[$index]);
                $zp = gzopen($fileName, "w9");
			    gzwrite($zp, serialize($contents));
			    gzclose($zp);
            }
            $this->budgets[$index] = serialize($this->budgets[$index]);
            return unserialize($this->budgets[$index]);
	    }
	}
	
	function getRequestedBudget($year, $type=RES_BUDGET){
	    global $wgServer,$wgScriptPath, $reporteeId;
	    if($type == RES_BUDGET){
	        $index = 'r'.$year;
	    }
	    else{
	        $index = 's'.$year;
	    }
	    if(isset($this->budgets[$index])){
	        return unserialize($this->budgets[$index]);
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
		if(file_exists($fileName)){
		    $contents = unserialize(implode("", gzfile($fileName)));
		    if(strcmp($contents[0], $lastChanged) == 0){
		        $this->budgets[$index] = serialize($contents[1]);
		        return unserialize($this->budgets[$index]);
		    }
		}
		$data = $budget_blob->getData();
	    if (! empty($data)) {
	        if($year != 2010 && $type == RES_BUDGET){
		        $this->budgets[$index] = new Budget("XLS", REPORT2_STRUCTURE, $data);
		    }
		    else {
		        if($type == RES_ALLOC_BUDGET && $this->isRoleDuring(CNI, $year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH)){
		            $this->budgets[$index] = new Budget("XLS", REPORT2_STRUCTURE, $data);
		        }
		        else{
		            $this->budgets[$index] = new Budget("XLS", SUPPLEMENTAL_STRUCTURE, $data);
		        }
		    }
            if($this->budgets[$index]->nRows()*$this->budgets[$index]->nCols() > 1){
                $this->budgets[$index]->xls[0][1]->setValue($this->getReversedName());
            }
            if(is_writable(CACHE_FOLDER)){
                $contents = array($lastChanged, $this->budgets[$index]);
                $zp = gzopen($fileName, "w9");
			    gzwrite($zp, serialize($contents));
			    gzclose($zp);
            }
            $this->budgets[$index] = serialize($this->budgets[$index]);
		    return unserialize($this->budgets[$index]);
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
	function isEvaluator($year = REPORTING_YEAR){
	    if($this->isEvaluator === null){
	        $eTable = getTableName("eval");
	        $sql = "SELECT *
	                FROM $eTable
	                WHERE eval_id = '{$this->id}'
	                AND year = '{$year}'";
	        $data = DBFunctions::execSQL($sql);
	        if(count($data) > 0){
	            $this->isEvaluator = true;
	        }
	        else {
	            $this->isEvaluator = false;
	        }
	    }
	    return $this->isEvaluator;
	}
	
	// Returns the list of Evaluation Submissions for this person
	function getEvaluateSubs($year = REPORTING_YEAR){
	    $eTable = getTableName("eval");
	    $sql = "SELECT *
	            FROM $eTable
	            WHERE eval_id = '{$this->id}'
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
        return $subs;
	}
	
	static function getAllEvaluates($type, $year = REPORTING_YEAR){
	    $type = mysql_real_escape_string($type);
	    $eTable = getTableName("eval");
	    
	    $sql = "SELECT DISTINCT sub_id 
	            FROM $eTable
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


	function getEvaluates($type, $year = REPORTING_YEAR){
	    $type = mysql_real_escape_string($type);
	    $eTable = getTableName("eval");
	    $sql = "SELECT *
	            FROM $eTable
	            WHERE eval_id = '{$this->id}'
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
            else if($row['type'] == "LOI" || $row['type'] == "OPT_LOI" || $row['type'] == "LOI_REV2"){
            	$subs[] = LOI::newFromId($row['sub_id']);
            }
        }
        return $subs;
	}

	function getEvaluatePNIs($year = REPORTING_YEAR){
	    $eTable = getTableName("eval");
	    $sql = "SELECT *
	            FROM $eTable
	            WHERE eval_id = '{$this->id}'
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
	function getEvaluateCNIs($year = REPORTING_YEAR){
	    $eTable = getTableName("eval");
	    $sql = "SELECT *
	            FROM $eTable
	            WHERE eval_id = '{$this->id}'
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
	
	function getEvaluateProjects($year = REPORTING_YEAR){
	    $eTable = getTableName("eval");
	    $sql = "SELECT *
	            FROM $eTable
	            WHERE eval_id = '{$this->id}'
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
	function getEvaluators($type='Researcher', $year = REPORTING_YEAR){
	    $eTable = getTableName("eval");
	    $sql = "SELECT *
	            FROM $eTable
	            WHERE sub_id = '{$this->id}'
	            AND type = '{$type}'
	            AND year = '{$year}'";
	    $data = DBFunctions::execSQL($sql);
	    $subs = array();
        foreach($data as $row){
            $subs[] = Person::newFromId($row['eval_id']);
        }
        return $subs;
	}

	/// Returns the allocation for this person  for year #year,
	/// or empty array if allocation not found in grand_review_results.
	function getAllocation($year = REPORTING_YEAR) {
		
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
    
    function isAuthorOf($paper){
        if($paper instanceof Paper){
            $paper_authors = $paper->getAuthors();
            
            $im_author = false;    
            foreach ($paper_authors as $auth){
                if( $auth->getName() == $this->name ){
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
    
    // Returns whether or not this person is waiting to be inactivated or not
    function isPendingInactivation(){
        $sql = "SELECT *
	            FROM `grand_role_request`
	            WHERE `created` = 'pending'
	            AND `user` = '{$this->getName()}'
	            ORDER BY `id` DESC LIMIT 1";
	    $data = DBFunctions::execSQL($sql);
	    if(count($data) > 0){
	        return true;
	    }
	    else{
	        return false;
	    }
    }

	/// Returns a new array of user IDs based on #arr, but sorted by the
	/// last name of the user (guessed from username).
	static function sortIdsByLastName($arr) {
		if (is_array($arr))
			$arr = implode(',', $arr);
		$ret = array();
		$res = DBFunctions::execSQL("SELECT user_id FROM mw_user WHERE user_id IN ({$arr}) ORDER BY SUBSTRING_INDEX(user_name, '.', -1), user_name;");
		foreach ($res as $r) {
			$ret[] = $r['user_id'];
		}
		return $ret;
	}
}
?>
