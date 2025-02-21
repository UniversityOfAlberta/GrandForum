<?php

/**
 * @package GrandObjects
 */

class Relationship extends BackboneModel {

    static $cache = array();
    var $id;
    var $user1;
    var $user2;
    var $university;
    var $type;
    var $status;
    var $thesis;
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
    function __construct($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->user1 = $data[0]['user1'];
            $this->user2 = $data[0]['user2'];
            $this->university = $data[0]['university'];
            $this->type = $data[0]['type'];
            $this->status = $data[0]['status'];
            $this->thesis = $data[0]['thesis'];
            $this->startDate = ZERO_DATE($data[0]['start_date']);
            $this->endDate = ZERO_DATE($data[0]['end_date']);
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
    
    // Returns the university id that this relation is associated with
    function getUniversity(){
        return $this->university;
    }
    
    // Returns the type of this Relationship
    function getType(){
        return $this->type;
    }
    
    // Returns the status of this Relationship
    function getStatus(){
        return $this->status;
    }
    
    function getThesis(){
        return $this->thesis;
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
                                                'university' => $this->getUniversity(),
                                                'type' => $this->getType(),
                                                'status' => $this->getStatus(),
                                                'thesis' => $this->getThesis(),
                                                'start_date' => ZERO_DATE($this->getStartDate(), zull),
                                                'end_date' => ZERO_DATE($this->getEndDate(), zull),
                                                'comment' => $this->getComment()),true);
                if($this->endDate == ""){
                    $this->endDate = ZOTT;
                }
                if($status && $this->endDate != ZOTT){
                    $status = DBFunctions::insert('grand_movedOn',
                                            array('user_id' => $this->user2,
                                                  'effective_date' => ZERO_DATE($this->endDate, zull),
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
                                                'university' => $this->getUniversity(),
                                                'type' => $this->getType(),
                                                'status' => $this->getStatus(),
                                                'thesis' => $this->getThesis(),
                                                'start_date' => ZERO_DATE($this->getStartDate(), zull),
                                                'end_date' => ZERO_DATE($this->getEndDate(), zull),
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
            $this->id = "";
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
            'university' => $this->getUniversity(),
            'type' => $this->getType(),
            'status' => $this->getStatus(),
            'thesis' => $this->getThesis(),
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
