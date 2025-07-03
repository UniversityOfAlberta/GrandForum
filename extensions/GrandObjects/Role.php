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
	    $projs = $this->getProjects();
	    $projects = array();
	    foreach($projs as $proj){
	        $projects[] = array('id'   => $proj->getId(),
	                            'name' => $proj->getName());
	    }
	    $json = array('id' => $this->getId(),
	                  'userId' => $this->user,
	                  'name' => $this->getRole(),
	                  'fullName' => $this->getRoleFullName(),
	                  'title' => $this->getTitle(),
	                  'comment' => $this->getComment(),
	                  'projects' => $projects,
	                  'startDate' => $this->getStartDate(),
	                  'endDate' => $this->getEndDate());
	    return $json;
	}
	
	function create(){
	    global $config, $wgScriptPath, $wgAdditionalMailParams;
	    $me = Person::newFromWgUser();
	    $person = $this->getPerson();
	    MailingList::unsubscribeAll($this->getPerson());
	    $status = DBFunctions::insert('grand_roles',
	                                  array('user_id'    => $this->user,
	                                        'role'       => $this->getRole(),
	                                        'start_date' => $this->getStartDate(),
	                                        'end_date'   => $this->getEndDate(),
	                                        'comment'    => $this->getComment()));
	    $id = DBFunctions::insertId();
	    Role::$cache = array();
	    Person::$rolesCache = array();
	    Person::$leaderCache = array();
	    $this->getPerson()->roles = null;
	    $this->getPerson()->projectCache = array();
        Cache::delete("personRolesDuring{$this->getPerson()->getId()}*", true);
        Cache::delete("rolesCache");
        foreach($this->getPerson()->getProjects(true) as $project){
            Cache::delete("project{$project->getId()}_people*", true);
        }
	    if($status){
            $this->id = $id;
            if(is_array($this->projects)){
                foreach($this->projects as $project){
                    $p = Project::newFromName($project->name);
                    DBFunctions::insert('grand_role_projects',
                                        array('role_id' => $this->getId(),
                                              'project_id' => $p->getId()));
                    DBFunctions::commit();
                    Cache::delete("project{$p->getId()}_people*", true);
                }
            }
            Notification::addNotification($me, Person::newFromId(0), "Role Added", "Effective {$this->getStartDate()} <b>{$person->getNameForForms()}</b> assumes the role <b>{$this->getRole()}</b>", "{$person->getUrl()}");
            if($config->getValue("networkName") == "CFN" && ($this->getPerson()->isRoleDuring(HQP, "1900-01-01", "2100-01-01") || $this->getRole() == "HQP") && $wgScriptPath == ""){
                mail("training@cfn-nce.ca", "Role Added", "Effective {$this->getStartDate()} <b>{$person->getNameForForms()}</b> assumes the role <b>{$this->getRole()}</b>", implode("\r\n", array('Content-type: text/html; charset=iso-8859-1',"From: {$config->getValue('supportEmail')}")), $wgAdditionalMailParams);
            }
            Notification::addNotification($me, $person, "Role Added", "Effective {$this->getStartDate()} you assume the role <b>{$this->getRole()}</b>", "{$person->getUrl()}");
            $supervisors = $person->getSupervisors();
            if(count($supervisors) > 0){
                foreach($supervisors as $supervisor){
                    Notification::addNotification($me, $supervisor, "Role Added", "Effective {$this->getStartDate()} <b>{$person->getNameForForms()}</b> assumes the role <b>{$this->getRole()}</b>", "{$person->getUrl()}");
                }
            }
        }
        MailingList::subscribeAll($this->getPerson());
	    return $status;
	}
	
	function update(){
	    global $config, $wgScriptPath, $wgAdditionalMailParams;
	    $me = Person::newFromWgUser();
	    MailingList::unsubscribeAll($this->getPerson());
	    $status = DBFunctions::update('grand_roles',
	                                  array('role'       => $this->getRole(),
	                                        'start_date' => $this->getStartDate(),
	                                        'end_date'   => $this->getEndDate(),
	                                        'comment'    => $this->getComment()),
	                                  array('id' => EQ($this->getId())));
	    foreach($this->getPerson()->getProjects(true) as $project){
            Cache::delete("project{$project->getId()}_people*", true);
        }
	    DBFunctions::delete('grand_role_projects',
	                        array('role_id' => EQ($this->getId())));
	    foreach($this->projects as $project){
	        $p = Project::newFromName($project->name);
	        DBFunctions::insert('grand_role_projects',
	                            array('role_id' => $this->getId(),
	                                  'project_id' => $p->getId()));
	        DBFunctions::commit();
            Cache::delete("project{$p->getId()}_people*", true);
	    }
	    Role::$cache = array();
	    Person::$rolesCache = array();
	    Person::$leaderCache = array();
	    $this->getPerson()->projectCache = array();
	    $this->getPerson()->roles = null;
	    Cache::delete("personRolesDuring{$this->getPerson()->getId()}*", true);
	    Cache::delete("rolesCache");
	    Notification::addNotification($me, Person::newFromId(0), "Role Changed", "The role ({$this->getRole()}) of <b>{$this->getPerson()->getNameForForms()}</b> has been changed", "{$this->getPerson()->getUrl()}");
	    if($config->getValue("networkName") == "CFN" && ($this->getPerson()->isRoleDuring(HQP, "1900-01-01", "2100-01-01") || $this->getRole() == "HQP") && $wgScriptPath == ""){
            mail("training@cfn-nce.ca", "Role Changed", "The role ({$this->getRole()}) of <b>{$this->getPerson()->getNameForForms()}</b> has been changed", implode("\r\n", array('Content-type: text/html; charset=iso-8859-1',"From: {$config->getValue('supportEmail')}")), $wgAdditionalMailParams);
        }
        MailingList::subscribeAll($this->getPerson());
	    return $status;
	}
	
	function delete(){
	    global $config, $wgScriptPath, $wgAdditionalMailParams;
	    $me = Person::newFromWgUser();
	    $person = $this->getPerson();
	    MailingList::unsubscribeAll($this->getPerson());
	    $status = DBFunctions::delete('grand_roles',
	                                  array('id' => EQ($this->getId())));
	    if($status){
	        $status = DBFunctions::delete('grand_role_projects',
	                                      array('role_id' => EQ($this->getId())));
	    }
	    Role::$cache = array();
	    Person::$rolesCache = array();
	    Person::$leaderCache = array();
	    $this->getPerson()->projectCache = array();
	    $this->getPerson()->roles = null;
	    foreach($this->getPerson()->getProjects(true) as $project){
            Cache::delete("project{$project->getId()}_people*", true);
        }
        Cache::delete("personRolesDuring{$this->getPerson()->getId()}*", true);
	    Cache::delete("rolesCache");
	    if($status){
	        Notification::addNotification($me, Person::newFromId(0), "Role Removed", "<b>{$person->getNameForForms()}</b> is no longer <b>{$this->getRole()}</b>", "{$person->getUrl()}");
	        if($config->getValue("networkName") == "CFN" && ($this->getPerson()->isRoleDuring(HQP, "1900-01-01", "2100-01-01") || $this->getRole() == "HQP") && $wgScriptPath == ""){
                mail("training@cfn-nce.ca", "Role Removed", "<b>{$person->getNameForForms()}</b> is no longer <b>{$this->getRole()}</b>", implode("\r\n", array('Content-type: text/html; charset=iso-8859-1',"From: {$config->getValue('supportEmail')}")), $wgAdditionalMailParams);
            }
	        Notification::addNotification($me, $person, "Role Removed", "You are no longer <b>{$this->getRole()}</b>", "{$person->getUrl()}");
	        $supervisors = $person->getSupervisors();
            if(count($supervisors) > 0){
                foreach($supervisors as $supervisor){
                    Notification::addNotification($me, $supervisor, "Role Removed", "<b>{$person->getNameForForms()}</b> is no longer <b>{$this->getRole()}</b>", "{$person->getUrl()}");
                }
            }
	    }
        MailingList::subscribeAll($this->getPerson());
	    return false;
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
	                $project = Project::newFromId($project);
	                if($project != null){
	                    $this->projects[] = $project;
	                }
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
