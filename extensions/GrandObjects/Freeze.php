<?php

/**
 * @package GrandObjects
 */
class Freeze extends BackboneModel {
    
    var $id;
    var $projectId;
    var $feature;
    
    static function newFromId($id){
        $data = DBFunctions::select(array('grand_freeze'),
                                    array('id',
                                          'project_id',
                                          'feature'),
                                    array('id' => EQ($id)));
        return new Freeze($data);
    }
    
    static function newFromProjectFeature($project, $feature){
        $data = DBFunctions::select(array('grand_freeze'),
                                    array('id',
                                          'project_id',
                                          'feature'),
                                    array('project_id' => EQ($project->getId()),
                                          'feature'    => EQ($feature)));
        return new Freeze($data);
    }
    
    static function getAllFreezes(){
        $freezes = array();
        $data = DBFunctions::select(array('grand_freeze'),
                                    array('id',
                                          'project_id',
                                          'feature'));
        foreach($data as $row){
            $freezes[] = new Freeze(array($row));
        }
        return $freezes;
    }
    
    function __construct($data) {
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->projectId = $data[0]['project_id'];
            $this->feature = $data[0]['feature'];
        }
    }
    
    function getId(){
        return $this->id;
    }
    
    function getProject(){
        return Project::newFromId($this->projectId);
    }
    
    function getFeature(){
        return $this->feature;    
    }
    
    
    function create(){
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(STAFF)){
            DBFunctions::insert('grand_freeze',
                                array('id' => $this->id,
                                      'project_id' => $this->projectId,
                                      'feature' => $this->feature));
            $id = DBFunctions::insertId();
            $this->id = $id;
        }
        return $this;
    }

    function update(){
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(STAFF)){
            DBFunctions::update('grand_freeze',
                                array('project_id' => $this->projectId,
                                      'feature' => $this->feature),
                                array('id' => EQ($this->id)));
        }
        return $this;
    }

    function delete(){
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(STAFF)){
            DBFunctions::delete('grand_freeze',
                                array('id' => EQ($this->id)));
            $this->id = "";
        }
        return $this;
    }
    
    function toArray(){
        return array('id' => $this->id,
                     'projectId' => $this->projectId,
                     'feature' => $this->feature);
    }
    
    function exists(){
        $freeze = Freeze::newFromId($this->getId());
        return ($freeze != null && $freeze->getId() != "");
    }
    
    function getCacheId(){
        return "";
    }
    
}


?>
