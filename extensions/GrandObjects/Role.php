<?php

/**
 * @package GrandObjects
 */

class Role extends BackboneModel {

    static $cache = array();

	var $id;
	var $user;
    var $role;
    var $startDate;
    var $endDate;
    var $comment;
	
	// Returns a new Role from the given id
	static function newFromId($id){
	    if(isset(self::$cache[$id])){
	        return self::$cache[$id];
	    }
	    $cacheId = "role_{$id}";
	    if(Cache::exists($cacheId)){
	        $data = Cache::fetch($cacheId);
	    }
	    else{
	        $data = DBFunctions::select(array('grand_roles'),
	                                    array('*'),
	                                    array('id' => $id));
	        Cache::store($cacheId, $data);
	    }
		$role = new Role($data);
        self::$cache[$role->id] = &$role;
		return $role;
	}
	
	// Constructor
	function __construct($data){
		if(count($data) > 0){
			$this->id = $data[0]['id'];
			$this->user = $data[0]['user_id'];
			$this->role = $data[0]['role'];
			$this->startDate = ZERO_DATE($data[0]['start_date']);
			$this->endDate = ZERO_DATE($data[0]['end_date']);
			$this->comment = $data[0]['comment'];
		}
	}
	
	function toArray(){
	    $json = array('id' => $this->getId(),
	                  'userId' => $this->user,
	                  'name' => $this->getRole(),
	                  'fullName' => $this->getRoleFullName(),
	                  'comment' => $this->getComment(),
	                  'startDate' => substr($this->getStartDate(), 0, 10),
	                  'endDate' => substr($this->getEndDate(), 0, 10));
	    return $json;
	}
	
	function create(){
	    $me = Person::newFromWgUser();
	    $person = $this->getPerson();
	    $status = DBFunctions::insert('grand_roles',
	                                  array('user_id'    => $this->user,
	                                        'role'       => $this->getRole(),
	                                        'start_date' => ZERO_DATE($this->getStartDate(), zull),
	                                        'end_date'   => ZERO_DATE($this->getEndDate(), zull),
	                                        'comment'    => $this->getComment()));
	    $id = DBFunctions::insertId();
	    Cache::delete("personRolesDuring".$this->getPerson()->getId(), true);
	    Cache::delete("rolesCache");
	    Role::$cache = array();
	    Person::$rolesCache = array();
	    $this->getPerson()->roles = null;
	    if($status && php_sapi_name() != "cli"){
            $this->id = $id;
            Cache::delete($this->getCacheId());
            Notification::addNotification($me, $person, "Role Added", "Effective {$this->getStartDate()} you assume the role '{$this->getRole()}'", "{$person->getUrl()}");
            $supervisors = $person->getSupervisors();
            if(count($supervisors) > 0){
                foreach($supervisors as $supervisor){
                    Notification::addNotification($me, $supervisor, "Role Added", "Effective {$this->getStartDate()} {$person->getReversedName()} assumes the role '{$this->getRole()}'", "{$person->getUrl()}");
                }
            }
        }
	    return $status;
	}
	
	function update(){
	    $status = DBFunctions::update('grand_roles',
	                                  array('role'       => $this->getRole(),
	                                        'start_date' => ZERO_DATE($this->getStartDate(), zull),
	                                        'end_date'   => ZERO_DATE($this->getEndDate(), zull),
	                                        'comment'    => $this->getComment()),
	                                  array('id' => EQ($this->getId())));
	    Cache::delete("personRolesDuring".$this->getPerson()->getId(), true);
	    Cache::delete("rolesCache");
	    Cache::delete($this->getCacheId());
	    Role::$cache = array();
	    Person::$rolesCache = array();
	    $this->getPerson()->roles = null;
	    return $status;
	}
	
	function delete(){
	    $me = Person::newFromWgUser();
	    $person = $this->getPerson();
	    $status = DBFunctions::delete('grand_roles',
	                                  array('id' => EQ($this->getId())));
	    Cache::delete("personRolesDuring".$this->getPerson()->getId(), true);
	    Cache::delete("rolesCache");
	    Cache::delete($this->getCacheId());
	    Role::$cache = array();
	    Person::$rolesCache = array();
	    $this->getPerson()->roles = null;
	    if($status){
	        Notification::addNotification($me, $person, "Role Removed", "You are no longer '{$this->getRole()}'", "{$person->getUrl()}");
	        $supervisors = $person->getSupervisors();
            if(count($supervisors) > 0){
                foreach($supervisors as $supervisor){
                    Notification::addNotification($me, $supervisor, "Role Removed", "{$person->getReversedName()} is no longer '{$this->getRole()}'", "{$person->getUrl()}");
                }
            }
	    }
	    return false;
	}
	
	function exists(){
	
	}
	
	function getCacheId(){
	    return "role_{$this->getId()}";
	}
	
	// Returns all distinct roles
	static function getDistinctRoles(){
	    global $config;
	    return $config->getValue('roleDefs');
	}

	// Returns whether this Role is still active or not
	function isStillActive(){
	    return($this->startDate > $this->endDate);
	}
	
	// Returns the Person who this Role belongs to
	function getUser(){
	    return Person::newFromId($this->user);
	}
	
	// Alias for getUser()
	function getPerson(){
	    return $this->getUser();
	}
	
	// Returns the name of this Role
	function getRole(){
	    return $this->role;
	}
	
	function isAlias(){
	    global $config;
	    $aliases = $config->getValue('roleAliases');
	    return (isset($aliases[$this->getRole()]));
	}
	
	// Returns the full name of this Role
	function getRoleFullName(){
	    global $config;
	    return $config->getValue('roleDefs', $this->getRole());
	}
	
	// Returns the startDate for this Role
	function getStartDate(){
	    return $this->startDate;
	}
	
	// Returns the endDate for this Role
	function getEndDate(){
	    return $this->endDate;
	}
	
	// Returns the comment for this Role
	function getComment(){
	    return $this->comment;
	}

}
?>
