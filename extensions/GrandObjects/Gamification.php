<?php

class Gamification {
    
    /**
     * Frequency: 
     *      0 = No limit
     */
    static function addAction($action, $points, $text, $frequency=0, $maximum=10000){
        self::$actions[$action] = array('points' => $points,
                                        'text' => $text,
                                        'frequency' => $frequency,
                                        'maximum' => $maximum);
    }
    
    static $actions = array();
    
    var $id;
    var $person;
    var $action;
    var $date;
    
    static function newFromUserId($userId, $action=""){
        $data = DBFunctions::select(array('grand_gamification'),
                                    array('*'),
                                    array('user_id' => EQ($userId),
                                          'action' => LIKE("%{$action}%")));
        $points = array();
        foreach($data as $row){
            $points[] = new Gamification(array($row));
        }
        return $points;
    }
    
    static function newFromId($id){
        $data = DBFunctions::select(array('grand_gamification'),
                                    array('*'),
                                    array('id' => EQ($id)));
        return new Gamification($data);
    }
    
    static function calculatePoints($person){
        $actions = self::newFromUserId($person->getId());
        $points = array();
        foreach($actions as $act){
            $action = $act->getAction();
            
            $exploded = explode("/", $action);
            $action = @$exploded[0];
            
            @$points[$action] = min($points[$action] + $act->getPoints(), self::$actions[$action]['maximum']);
        }
        return array_sum($points);
    }
    
    static function log($action){
        global $wgMessage, $wgServer, $wgScriptPath;
        
        $me = Person::newFromWgUser();
        if(!$me->isSubRole("Gamification")){
            return; // TODO: Gamification is disabled for non-Gamification users
        }
        
        $date = date('Y-m-d h:i:s');
        
        $exploded = explode("/", $action);
        $action = @$exploded[0];
        $subAction = @$exploded[1];
        
        $fullAction = ($subAction != "") ? "{$action}/{$subAction}" : $action;
        
        if(!isset(self::$actions[$action])){
            $wgMessage->addError("Gamification action '{$action}' not found");
            return;
        }
        
        $create = true;
        $actions = self::newFromUserId($me->getId(), $fullAction);
        if(count($actions) > 0){
            $act = $actions[count($actions)-1];
            $diff = @date_diff(date_create($act->getDate()), date_create('today'))->d;
            $create = ($diff >= self::$actions[$action]['frequency']) ? true : false;
        }
        
        if($create){
            DBFunctions::insert('grand_gamification',
                                array('user_id' => $me->getId(),
                                      'action' => $fullAction));
            $id = DBFunctions::insertId();
            DBFunctions::commit();
            $actions = self::newFromUserId($me->getId(), $fullAction);
            $points = array_reduce($actions, function($sum, $action){ return $sum + $action->getPoints(); }, 0);
            if($points <= self::$actions[$action]['maximum']){
                // Only notify user if within maximum points
                $gamification = self::newFromId($id);
                $array = (isset($_COOKIE['gamification'])) ? json_decode($_COOKIE['gamification']) : array();
                $array[] = $gamification->toArray();
                setcookie('gamification', json_encode($array), time()+3600, $wgScriptPath);
                $_COOKIE['gamification'] = json_encode($array);
            }
        }
    }
    
    function __construct($data) {
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->person = Person::newFromId($data[0]['user_id']);
            $this->action = $data[0]['action'];
            $this->date = $data[0]['date'];
        }
    }
    
    /**
     * Returns the id of the Gamification
     * @return integer The id of the Gamification
     */
    function getId(){
        return $this->id;
    }
    
    /**
     * Returns the Person that this Gamification belongs to
     * @return Person The Person that this Gamification belongs to
     */
    function getPerson(){
        return $this->person;
    }
    
    /**
     * Returns the action of Gamification this is
     * @return string The action of Gamification this is
     */
    function getAction(){
        return $this->action;
    }
    
    function getText(){
        $exploded = explode("/", $this->getAction());
        $action = @$exploded[0];
        return self::$actions[$action]['text'];
    }
    
    /**
     * Returns the date of this Gamification
     * @return string The date of this Gamification
     */
    function getDate(){
        return $this->date;
    }
    
    function getPoints(){
        $exploded = explode("/", $this->getAction());
        $action = @$exploded[0];
        return self::$actions[$action]['points'];
    }
    
    function toArray(){
        return array(
            'action' => $this->getAction(),
            'text' => $this->getText(),
            'points' => $this->getPoints(),
            'date' => $this->getDate()
        );
    }
}

Gamification::addAction('HealthAssessment', 20, 'Completed Healthy Aging Assessment', 9999);
Gamification::addAction('3MonthFollowup', 10, 'Completed 3 month follow up assessment', 9999);
Gamification::addAction('OpenReport', 5, 'Opened Report', 9999);
Gamification::addAction('EducationModule', 5, 'Completed Education Module', 9999, 40);
Gamification::addAction('EducationResource', 2, 'Clicked on an educational resource', 9999, 9999);
Gamification::addAction('CreateActionPlan', 2, 'Created weekly action plan', 1, 24);
Gamification::addAction('SubmitActionPlan', 2, 'Submited a weekly action plan', 1, 36);
Gamification::addAction('MeetActionPlan', 3, 'Met your action plan goal', 1, 48);
Gamification::addAction('ActionPlanConsistency', 2, '10 weeks minimum of action plans submitted consistency bonus', 70);
Gamification::addAction('CreateClipBoard', 1, 'Created a clip board of community programs', 9999);
Gamification::addAction('LoginConsistency', 2, 'Logged in 5+ times per week consistency bonus', 7);
Gamification::addAction('SignUpProgram', 5, 'Signed up for an AVOID Program', 0);
Gamification::addAction('SignAskAnExpert', 5, 'Signed up for Ask an Expert', 9999);
Gamification::addAction('SubmitCommunityProgram', 5, 'Submitted a community program', 9999);
Gamification::addAction('5CommunitySupports', 10, 'Looked into 5 community supports', 9999);

?>
