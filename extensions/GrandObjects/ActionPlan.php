<?php

/**
 * @package GrandObjects
 */

class ActionPlan extends BackboneModel {
    
    var $id;
    var $userId;
    var $date;
    var $goals;
    var $barriers;
    var $plan;
    var $tracker = array();
    var $components = array();
    var $submitted;
    var $created;
    
    /**
     * Returns a new ActionPlan using the given id
     * @param int $id The report id of the ActionPlan
     * @return ActionPlan The ActionPlan that matches the report_id
     */
    static function newFromId($id){
        $data = DBFunctions::select(array('grand_action_plan'),
                                    array('*'),
                                    array('id' => $id));
        return new ActionPlan($data);
    }
    
    static function newFromUserId($userId){
        $data = DBFunctions::select(array('grand_action_plan'),
                                    array('*'),
                                    array('user_id' => $userId),
                                    array('created' => 'DESC'));
        $array = array();
        foreach($data as $row){
            $array[] = new ActionPlan(array($row));
        }
        return $array;
    }
    
    static function getAll(){
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(STAFF)){
            $data = DBFunctions::select(array('grand_action_plan'),
                                        array('*'),
                                        array(),
                                        array('created' => 'DESC'));
            $array = array();
            foreach($data as $row){
                $array[] = new ActionPlan(array($row));
            }
            return $array;
        }
        return array();
    }
    
    static function comp2Text($comp){
        switch($comp){
            case "A": 
                return "Activity";
            case "V":
                return "Vaccinate";
            case "O":
                return "Optimize Medication";
            case "I":
                return "Interact";
            case "D":
                return "Diet & Nutrition";
            case "S":
                return "Sleep";
            case "F":
                return "Falls Prevention";
        }
        return "Other";
    }
    
    
    function __construct($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->userId = $data[0]['user_id'];
            $this->date = $data[0]['date'];
            $this->goals = $data[0]['goals'];
            $this->barriers = $data[0]['barriers'];
            $this->plan = $data[0]['plan'];
            $this->tracker = json_decode($data[0]['tracker']);
            $this->components = json_decode($data[0]['components']);
            $this->submitted = $data[0]['submitted'];
            $this->created = $data[0]['created'];
        }
    }
    
    function getId(){
        return $this->id;
    }
    
    function getUserId(){
        return $this->userId;
    }
    
    function getPerson(){
        return Person::newFromId($this->userId);
    }
    
    function getDate(){
        return $this->date;
    }
    
    function getGoals(){
        return $this->goals;
    }
    
    function getBarriers(){
        return $this->barriers;
    }
    
    function getPlan(){
        return $this->plan;
    }
    
    function getTracker(){
        return $this->tracker;
    }
    
    function getComponents(){
        if($this->components == null){
            $this->components = array();
        }
        return $this->components;
    }
    
    function getSubmitted(){
        return ($this->submitted != 0);
    }
    
    /**
     * Returns whether the current user can read the ActionPlan or not
     * @return boolean Whether or not the current user can read this ActionPlan
     */
    function canUserRead(){
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            // Not logged in?  Too bad, you can't read anything!
            return false;
        }
        else if($me->isRoleAtLeast(MANAGER)){
            // Managers should be able to see all ActionPlan
            return true;
        }
        else if($me->getId() == $this->userId){
            // I should be able to read any ActionPlan which was created by me
            return true;
        }
        else if($this->id == ""){
            return true;
        }

        return false;
    }
    
    function create(){
        if($this->canUserRead()){
            $me = Person::newFromWgUser();
            $this->userId = $me->getId();
            $date = date('Y-m-d', time() + 86400);
            DBFunctions::insert('grand_action_plan',
                                array('user_id' => $this->userId,
                                      'date' => $date,
                                      'goals' => $this->goals,
                                      'barriers' => $this->barriers,
                                      'plan' => $this->plan,
                                      'tracker' => json_encode($this->tracker),
                                      'components' => json_encode($this->components),
                                      'submitted' => $this->submitted,
                                      'created' => EQ(COL('CURRENT_TIMESTAMP'))));
            $this->id = DBFunctions::insertId();
            DBFunctions::commit();
        }
    }
    
    function update(){
        if($this->canUserRead()){
            DBFunctions::update('grand_action_plan',
                                array('goals' => $this->goals,
                                      'barriers' => $this->barriers,
                                      'plan' => $this->plan,
                                      'tracker' => json_encode($this->tracker),
                                      'components' => json_encode($this->components),
                                      'submitted' => $this->submitted),
                                array('id' => $this->id));
            DBFunctions::commit();
        }
    }
    
    function delete(){
        if($this->canUserRead()){
            DBFunctions::delete('grand_action_plan',
                                array('id' => $this->id));
            DBFunctions::commit();
        }
    }
    
    function toArray(){
        if($this->canUserRead()){
            return array('id' => $this->id,
                         'userId' => $this->getUserId(),
                         'date' => $this->getDate(),
                         'goals' => $this->getGoals(),
                         'barriers' => $this->getBarriers(),
                         'plan' => $this->getPlan(),
                         'tracker' => $this->getTracker(),
                         'components' => $this->getComponents(),
                         'submitted' => $this->getSubmitted(),
                         'created' => $this->created);
        }
        return array();
    }
    
    function exists(){
        return ($this->id != "" && $this->id != 0);
    }
    
    function getCacheId(){
        
    }
    
}

?>
