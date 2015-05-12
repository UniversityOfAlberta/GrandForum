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
	    $data = DBFunctions::select(array('grand_roles'),
	                                array('*'),
	                                array('id' => $id));
		$role = new Role($data);
        self::$cache[$role->id] = &$role;
		return $role;
	}
	
	// Constructor
	function Role($data){
		if(count($data) > 0){
			$this->id = $data[0]['id'];
			$this->user = $data[0]['user_id'];
			$this->role = $data[0]['role'];
			$this->startDate = $data[0]['start_date'];
			$this->endDate = $data[0]['end_date'];
			$this->comment = $data[0]['comment'];
		}
	}
	
	function toArray(){
	    $json = array('id' => $this->getId(),
	                  'name' => $this->getRole(),
	                  'comment' => $this->getComment(),
	                  'startDate' => $this->getStartDate(),
	                  'endDate' => $this->getEndDate());
	    return $json;
	}
	
	function create(){
	
	}
	
	function update(){
	
	}
	
	function delete(){
	
	}
	
	function exists(){
	
	}
	
	function getCacheId(){
	
	}
	
	// Returns all distinct roles
	static function getDistinctRoles(){
		// $sql = "SELECT DISTINCT role FROM grand_roles";
        
  //       $data = DBFunctions::execSQL($sql);
  //       $roles = array();
  //       foreach($data as $row){
  //           $roles[] = $row['role'];
  //       }
        $roles = array(
        		'BOD' => 90,
               	'Manager' => 80,
               	'Champion' => 70,
               	'RMC' => 70,
               	'NI' => 60,
               	'HQP' => 40,
               	'Staff' => 30
		);

	    return $roles;
	}

	// Returns whether this Role is still active or not
	function isStillActive(){
	    return($this->startDate > $this->endDate);
	}
	
	// Returns the Person who this Role belongs to
	function getUser(){
	    return Person::newFromId($this->user);
	}
	
	// Returns the name of this Role
	function getRole(){
	    return $this->role;
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
