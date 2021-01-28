<?php

/**
 * @package GrandObjects
 */

class Relationship{

    static $cache = array();

	var $id;
	var $user1;
	var $user2;
    var $type;
    var $projects;
    var $projectsWaiting;
    var $startDate;
    var $endDate;
    var $comment;
	
	// Returns a new Relationship from the given id
	static function newFromId($id){
	    if(isset(self::$cache[$id])){
	        return self::$cache[$id];
	    }
	    $data = DBFunctions::select(array('grand_relations'),
	                                array('*'),
	                                array('id' => $id));
		$Relationship = new Relationship($data);
        self::$cache[$Relationship->id] = &$Relationship;
		return $Relationship;
	}
	
	// Constructor
	function __construct($data){
		if(count($data) > 0){
			$this->id = $data[0]['id'];
			$this->user1 = $data[0]['user1'];
			$this->user2 = $data[0]['user2'];
			$this->type = $data[0]['type'];
			$this->projects = $data[0]['projects'];
			$this->projectsWaiting = true;
			$this->startDate = $data[0]['start_date'];
			$this->endDate = $data[0]['end_date'];
			$this->comment = $data[0]['comment'];
		}
	}
	
	// Returns the id of this Relationship
	function getId(){
	    return $this->id;
	}
	
	// Returns whether this Relationship is still active or not
	function isStillActive(){
	    return($this->startDate > $this->endDate);
	}
	
	// Returns the Person who is related to user2
	function getUser1(){
	    return Person::newFromId($this->user1);
	}
	
	// Returns the Person who is related to user1
	function getUser2(){
	    return Person::newFromId($this->user2);
	}
	
	// Returns the type of this Relationship
	function getType(){
	    return $this->type;
	}
	
	// Returns an array of Project objects for this Relationship
	function getProjects(){
	    if($this->projectsWaiting){
	        $projects = $this->projects;
	        $this->projects = array();
	        if($projects != ""){
	            $projects = unserialize($projects);
			    foreach($projects as $project){
			        $proj = Project::newFromId($project);
	                $this->projects[] = $proj;
			    }
			}
			$this->projectsWaiting = false;
	    }
	    return $this->projects;
	}
	
	// Returns the startDate for this Relationship
	function getStartDate(){
	    return $this->startDate;
	}
	
	// Returns the endDate for this Relationship
	function getEndDate(){
	    return $this->endDate;
	}
	
	// Returns the comment for this Relationship
	function getComment(){
	    return $this->comment;
	}
}
?>
