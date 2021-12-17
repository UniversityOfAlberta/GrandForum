<?php

class PersonSupervisorsCell extends DashboardCell {
    
    function __construct($cellType, $params, $cellValue, $rowN, $colN, $table){
        $this->label = "Supervisors";
        $start = "0000-00-00";
        $end = "2100-00-00";
        if(count($params) == 1){
            $params[2] = $params[0];
        }
        else{
            if(isset($params[0])){
                // Start
                $start = $params[0];
            }
            if(isset($params[1])){
                // End
                $end = $params[1];
            }
        }
        if(isset($params[2])){
            $project = Project::newFromName($params[2]);
            if($project != null && $project->getName() != null){
                $this->obj = $project;
                $person = $table->obj;
                $supervisors = $person->getSupervisors();
                $values = array();
                foreach($supervisors as $supervisor){
                    if($supervisor->isMemberOf($project)){
                        $uni = $supervisor->getUniversity();
                        $position = ($uni['position'] != "") ? $uni['position'] : "Other";
                        $values[$position][] = $supervisor->getId();
                    }
                }
                $this->setValues($values);
            }
        }
        else{
            $person = $table->obj;
            $supervisors = $person->getSupervisors();
            $values = array();
            foreach($supervisors as $supervisor){
                $values['All'][] = $supervisor->getId();
            }
            $this->setValues($values);
        }
    }
    
    function rasterize(){
        return array(PERSON_SUPERVISORS, $this);
    }
    
    function toString(){
        return $this->value;
    }
    
    function getHeaders(){
        return array("Full Name", "First Name", "Last Name", "University", "Title");
    }
    
    function detailsRow($item){
        global $wgServer, $wgScriptPath;
        $supervisor = Person::newFromId($item);
        $uni = $supervisor->getUniversity();
        $splitName = $supervisor->splitName();
        $details = "<td><a href='{$supervisor->getUrl()}' target='_blank'>{$supervisor->getNameForForms()}</a></td><td>{$splitName['first']}</td><td>{$splitName['last']}</td><td>{$uni['university']}</td><td>{$uni['position']}</td>";
        return $details;
    }
}

?>
