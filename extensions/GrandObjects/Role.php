<?php

/**
 * @package GrandObjects
 */

class Role extends BackboneModel {

    static $cache = array();
    static $projectCache = null;

	var $id;
	var $user;
    var $role;
    var $title;
    var $startDate;
    var $endDate;
    var $comment;
    var $projects = null;
	
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
	
	static function generateProjectsCache(){
	    if(self::$projectCache == null){
	        $data = DBFunctions::select(array('grand_role_projects'),
	                                    array('*'));
	        self::$projectCache = array();
	        foreach($data as $row){
	            self::$projectCache[$row['role_id']][] = $row['project_id'];
	        }
	    }
	}
	
	// Constructor
	function __construct($data){
		$me = Person::newFromWgUser();
		if(count($data) > 0){
			$this->id = $data[0]['id'];
			$this->user = $data[0]['user_id'];
			$this->role = $data[0]['role'];
			$this->title = $data[0]['title'];
			$this->startDate = $data[0]['start_date'];
			$this->endDate = $data[0]['end_date'];
			$this->comment = $data[0]['comment'];
		}
	}
	
	function toArray(){
	    $json = array('id' => $this->getId(),
	                  'name' => $this->getRole(),
	                  'fullName' => $this->getRoleFullName(),
	                  'title' => $this->getTitle(),
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
	
	function getTitle(){
	    return $this->title;
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
	
	function getProjects(){
	    if($this->projects == null){
	        self::generateProjectsCache();
	        $this->projects = array();
	        if(isset(self::$projectCache[$this->getId()])){
	            foreach(self::$projectCache[$this->getId()] as $project){
	                $this->projects[] = Project::newFromId($project);
	            }
	        }
	    }
	    return $this->projects;
	}
	
	function hasProject($project){
	    $projects = $this->getProjects();
	    if(count($projects) == 0){
	        return true;
	    }
	    foreach($projects as $proj){
	        if($proj->getId() == $project->getId()){
	            return true;
	        }
	    }
	    return false;
	}
}
?>
