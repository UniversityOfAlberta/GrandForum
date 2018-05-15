<?php

/**
 * @package GrandObjects
 */

class Relationship extends BackboneModel {

    static $cache = array();
    var $id;
    var $user1;
    var $user2;
    var $type;
    var $status;
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

    static function newFromUser1User2TypeStartDate($user1,$user2,$type,$startdate=false){
            $sql = "SELECT *
                  FROM grand_relations WHERE
                  user1 = '$user1' AND
                  user2 = '$user2' AND
                  type = '$type'";
	    if($startdate != false){
	     $sql = $sql. " AND start_date = '$startdate'";
	    }
            $data = DBFunctions::execSQL($sql);
            if(count($data) >0){
                $relation = new Relationship(array($data[0]));
                return $relation;
            }
            return new Relationship(array());
        }

    
    // Constructor
    function Relationship($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->user1 = $data[0]['user1'];
            $this->user2 = $data[0]['user2'];
            $this->type = $data[0]['type'];
            $this->status = $data[0]['status'];
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
    
    // Returns the status of this Relationship
    function getStatus(){
        return $this->status;
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
        return substr($this->startDate, 0, 10);
    }
    
    // Returns the endDate for this Relationship
    function getEndDate(){
        return substr($this->endDate, 0, 10);
    }
    
    // Returns the comment for this Relationship
    function getComment(){
        return $this->comment;
    }
    
    function create(){
        $me = Person::newFromWgUser();
        if($me->getId() == $this->user1 || $me->isRole(ADMIN)){
            DBFunctions::begin();
            $status = DBFunctions::insert('grand_relations',
                                          array('user1' => $this->user1,
                                                'user2' => $this->user2,
                                                'type' => $this->getType(),
                                                'status' => $this->getStatus(),
                                                'start_date' => $this->getStartDate(),
                                                'end_date' => $this->getEndDate(),
                                                'comment' => $this->getComment()),true);
                if($this->endDate == ""){
                    $this->endDate ="0000-00-00 00:00:00";
                }
                if($status && $this->endDate != "0000-00-00 00:00:00"){
                    $status = DBFunctions::insert('grand_movedOn',
                                            array('user_id' => $this->user2,
                                                  'effective_date' => $this->endDate,
                                                  'status' => $this->status),
                                              true);
                }

            if($status){
                DBFunctions::commit();
                $data = DBFunctions::select(array('grand_relations'),
                                            array('id'),
                                            array('user1' => EQ($this->user1),
                                                  'user2' => EQ($this->user2),
                                                  'type'  => EQ($this->getType())),
                                            array('id' => 'DESC'),
                                            array(1));
                if(count($data) > 0){
                    $this->id = $data[0]['id'];
                    Relationship::$cache = array();
                    Notification::addNotification($me, $this->getUser1(), "Relation Added", "You and {$this->getUser2()->getNameForForms()} are related through the '{$this->getType()}' relation", "{$this->getUser2()->getUrl()}");
                    Notification::addNotification($me, $this->getUser2(), "Relation Added", "You and {$this->getUser1()->getNameForForms()} are related through the '{$this->getType()}' relation", "{$this->getUser1()->getUrl()}");
                    return true;
                }
            }
        }
        return false;
    }
    
    function update(){
        $me = Person::newFromWgUser();
        if($me->getId() == $this->user1 || $me->isRole(ADMIN)){
            $status = DBFunctions::update('grand_relations',
                                          array('user1' => $this->user1,
                                                'user2' => $this->user2,
                                                'type' => $this->getType(),
                                                'status' => $this->getStatus(),
                                                'start_date' => $this->getStartDate(),
                                                'end_date' => $this->getEndDate(),
                                                'comment' => $this->getComment()),
                                          array('id' => EQ($this->id)));
            if($status){
                Relationship::$cache = array();
                return true;
            }
        }
        return false;
    }
    
    function delete(){
        $me = Person::newFromWgUser();
        if($me->getId() == $this->user1 || $me->isRole(ADMIN)){
            $status = DBFunctions::delete('grand_relations',
                                          array('id' => EQ($this->id)));
            if($status){
                Notification::addNotification($me, $this->getUser1(), "Relation Deleted", "You and {$this->getUser2()->getNameForForms()} are no longer related through the '{$this->getType()}' relation", "{$this->getUser2()->getUrl()}");
                Notification::addNotification($me, $this->getUser2(), "Relation Deleted", "You and {$this->getUser1()->getNameForForms()} are no longer related through the '{$this->getType()}' relation", "{$this->getUser1()->getUrl()}");
                Relationship::$cache = array();
                return true;
            }
        }
        return false;
    }
    
    function exists(){
        
    }
    
    function toArray(){
        return array(
            'id' => $this->getId(),
            'user1' => $this->user1,
            'user2' => $this->user2,
            'type' => $this->getType(),
            'status' => $this->getStatus(),
            'startDate' => $this->getStartDate(),
            'endDate' => $this->getEndDate(),
            'comment' => $this->getComment()
        );
    }
    
    function getCacheId(){
        return "rel_{$this->id}";
    }
}
?>
