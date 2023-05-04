<?php

/**
 * @package GrandObjects
 */

class ActionPlan extends BackboneModel {
    
    var $id;
    var $userId;
    var $date;
    var $type;
    var $fitbit;
    var $goals;
    var $barriers;
    var $plan;
    var $time;
    var $when;
    var $confidence;
    var $dates = array();
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
            $this->type = $data[0]['type'];
            $this->fitbit = json_decode($data[0]['fitbit']);
            $this->goals = $data[0]['goals'];
            $this->barriers = $data[0]['barriers'];
            $this->plan = $data[0]['plan'];
            $this->time = $data[0]['time'];
            $this->when = $data[0]['when'];
            $this->dates = json_decode($data[0]['dates']);
            $this->confidence = $data[0]['confidence'];
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
        return substr($this->date, 0, 10);
    }
    
    // Alias for getDate()
    function getStartDate(){
        return $this->getDate();
    }
    
    private function getDateAdjust($adjust){
        return substr(date('Y-m-d', strtotime($this->getStartDate()) + $adjust*24*3600), 0, 10);
    }
    
    function getEndDate(){
        return $this->getDateAdjust(6);
    }
    
    function getMon(){
        return $this->getDateAdjust(0);
    }
    
    function getTue(){
        return $this->getDateAdjust(1);
    }
    
    function getWed(){
        return $this->getDateAdjust(2);
    }
    
    function getThu(){
        return $this->getDateAdjust(3);
    }
    
    function getFri(){
        return $this->getDateAdjust(4);
    }
    
    function getSat(){
        return $this->getDateAdjust(5);
    }
    
    function getSun(){
        return $this->getDateAdjust(6);
    }
    
    function getType(){
        return $this->type;
    }
    
    function getFitbit(){
        return $this->fitbit;
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
    
    function getTime(){
        return $this->time;
    }
    
    function getWhen(){
        return $this->when;
    }
    
    function getDates(){
        return $this->dates;
    }
    
    function getConfidence(){
        return $this->confidence;
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
    
    function metGoals(){
        $tracker = (array)($this->getTracker());
        foreach($this->getDates() as $key => $date){
            if($date == 1 && (!isset($tracker[$key]) || $tracker[$key] != 1)){
                return false;
            }
        }
        return true;
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
            $date = date('Y-m-d', strtotime('last thursday +4 days'));
            DBFunctions::insert('grand_action_plan',
                                array('user_id' => $this->userId,
                                      'date' => $date,
                                      'type' => $this->type,
                                      'fitbit' => json_encode($this->fitbit),
                                      'goals' => $this->goals,
                                      'barriers' => $this->barriers,
                                      'plan' => $this->plan,
                                      '`when`' => $this->when,
                                      'time' => $this->time,
                                      'confidence' => $this->confidence,
                                      'dates' => json_encode($this->dates),
                                      'tracker' => json_encode($this->tracker),
                                      'components' => json_encode($this->components),
                                      'submitted' => $this->submitted,
                                      'created' => EQ(COL('CURRENT_TIMESTAMP'))));
            $this->id = DBFunctions::insertId();
            DBFunctions::commit();
            setcookie('lastfitbit', time(), time()-3600); // Expire this cookie
            Gamification::log("CreateActionPlan");
        }
    }
    
    function update(){
        if($this->canUserRead()){
            DBFunctions::update('grand_action_plan',
                                array('type' => $this->type,
                                      'fitbit' => json_encode($this->fitbit),
                                      'goals' => $this->goals,
                                      'barriers' => $this->barriers,
                                      'plan' => $this->plan,
                                      '`when`' => $this->when,
                                      'time' => $this->time,
                                      'confidence' => $this->confidence,
                                      'dates' => json_encode($this->dates),
                                      'tracker' => json_encode($this->tracker),
                                      'components' => json_encode($this->components),
                                      'submitted' => $this->submitted),
                                array('id' => $this->id));
            DBFunctions::commit();
            
            if($this->submitted){
                Gamification::log("SubmitActionPlan");
                if($this->metGoals()){
                    Gamification::log("MeetActionPlan");
                }
            }
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
                         'type' => $this->getType(),
                         'fitbit' => $this->getFitbit(),
                         'goals' => $this->getGoals(),
                         'barriers' => $this->getBarriers(),
                         'plan' => $this->getPlan(),
                         'dates' => $this->getDates(),
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
