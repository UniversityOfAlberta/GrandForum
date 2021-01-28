<?php

class Activity {
    
    var $id;
    var $name;
    var $project;
    
    /**
     * Returns the Activity with the given id
     * @param integer $id The id of the Activity
     * @return Activity The Activity with the given id
     */
    function newFromId($id){
        $data = DBFunctions::select(array('grand_activities'),
                                    array('id', 'name', 'project_id'),
                                    array('id' => EQ($id)));
        return new Activity($data);
    }
    
    /**
     * Returns the Activity with the given name
     * NOTE: The names need not be unique(although usually will be), so this may not return the desired row
     * @param string $id The name of the Activity
     * @return Activity The Activity with the given name
     */
    function newFromName($name, $projectId=""){
        if($projectId != ""){
            $data = DBFunctions::select(array('grand_activities'),
                                        array('id', 'name', 'project_id'),
                                        array('name' => EQ($name),
                                              'project_id' => EQ($projectId)));
        }
        else{
            $data = DBFunctions::select(array('grand_activities'),
                                        array('id', 'name', 'project_id'),
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
    
}

?>
