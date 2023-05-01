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
        foreach($actions as $action){
            @$points[$action->getAction()] = min($points[$action->getAction()] + $action->getPoints(),
                                                 self::$actions[$action->getAction()]);
        }
        return array_sum($points);
    }
    
    static function log($action){
        global $wgMessage;
        $me = Person::newFromWgUser();
        $date = date('Y-m-d h:i:s');
        
        if(!isset(self::$actions[$action])){
            $wgMessage->addError("Gamification action '{$action}' not found");
            return;
        }
        
        $create = true;
        $actions = self::newFromUserId($me->getId(), $action);
        if(count($actions) > 0){
            $create = false;
            foreach($actions as $act){
                $diff = @date_diff(date_create($act->getDate()), date_create('today'))->d;
                if($diff >= self::$actions[$action]['frequency']){
                    $create = true;
                }
            }
        }
        
        if($create){
            DBFunctions::insert('grand_gamification',
                                array('user_id' => $me->getId(),
                                      'action' => $action));
            DBFunctions::commit();
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
    
    /**
     * Returns the country code of this Gamification
     * @return string The country code of this Gamification
     */
    function getDate(){
        return $this->date;
    }
    
    function getPoints(){
        return self::$actions[$this->getAction()]['points'];
    }
}

Gamification::addAction('HealthAssessment', 4, 'Health Assessment', 0);
Gamification::addAction('OpenReport', 5, 'Open Report', 9999);
Gamification::addAction('EducationModule', 5, 'Open Report', 0, 40);
Gamification::addAction('CreateActionPlan', 2, 'Create weekly action plan', 0, 24);
Gamification::addAction('SubmitActionPlan', 2, 'Submit a weekly action plan', 0, 36);
Gamification::addAction('MeetActionPlan', 3, 'Meet your action plan goal', 0, 48);
Gamification::addAction('ActionPlanConsistency', 2, '10 weeks minimum of action plans submitted consistency bonus', 70);
Gamification::addAction('CreateClipBoard', 1, 'Create a clip board of community programs', 0);
Gamification::addAction('3MonthFollowup', 10, '3 month follow up assessment', 0);
Gamification::addAction('LoginConsistency', 2, 'log in 5+ times per week consistency bonus', 7);
Gamification::addAction('SignUpProgram', 5, 'Sign up for an AVOID Program', 0);
Gamification::addAction('SignAskAnExpert', 5, 'Sign up for Ask an Expert', 0);
Gamification::addAction('5CommunitySupports', 10, 'Looked into 5 community supports', 0);

?>
