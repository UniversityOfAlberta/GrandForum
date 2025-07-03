<?php

class Activity {
    
    static $activityCache = array();
    
    var $id;
    var $name;
    var $project;
    var $order;
    var $deleted;
    
    /**
     * Returns the Activity with the given id
     * @param integer $id The id of the Activity
     * @return Activity The Activity with the given id
     */
    static function newFromId($id){
        if(isset(self::$activityCache[$id])){
            return self::$activityCache[$id];
        }
        $data = DBFunctions::select(array('grand_activities'),
                                    array('*'),
                                    array('id' => EQ($id)));
        $activity = new Activity($data);
        self::$activityCache[$id] = $activity;
        return $activity;
    }
    
    /**
     * Returns the Activity with the given name
     * NOTE: The names need not be unique(although usually will be), so this may not return the desired row
     * @param string $id The name of the Activity
     * @return Activity The Activity with the given name
     */
    static function newFromName($name, $projectId=""){
        if($projectId != ""){
            $data = DBFunctions::select(array('grand_activities'),
                                        array('*'),
                                        array('name' => EQ($name),
                                              'project_id' => EQ($projectId)));
        }
        else{
            $data = DBFunctions::select(array('grand_activities'),
                                        array('*'),
                                        array('name' => EQ($name)));
        }
        return new Activity($data);
    }
    
    /**
     * Constructs a new Activity from the given DB resultset
     * @param array $data the DB resultset
     */
    function __construct($data){
        if(isset($data[0])){
            $this->id = $data[0]['id'];
            $this->name = $data[0]['name'];
            $this->project = Project::newFromId($data[0]['project_id']);
            $this->order = $data[0]['order'];
            $this->deleted = $data[0]['deleted'];
        }
    }
    
    /**
     * Returns the id of this Activity
     * @return integer The id of this Activity
     */
    function getId(){
        return $this->id;
    }
    
    /**
     * Returns the name of this Activity
     * @return string The name of this Activity
     */
    function getName(){
        return $this->name;
    }
    
    /**
     * Returns the Project that this Activity belongs to
     * @return Project The Project that this Activity belongs to
     */
    function getProject(){
        return $this->project;
    }
    
    /**
     * Returns the order of this Activity
     * @return integer The order of this Activity
     */
    function getOrder(){
        return $this->order;
    }
    
    /**
     * Returns whether this Activity is deleted
     * @return boolean Whether this Activity is deleted
     */
    function isDeleted(){
        return $this->deleted;
    }
    
}

?>
