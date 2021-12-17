<?php

abstract class PersonPublicationCell extends PublicationCell {
    
    function __construct($cellType, $params, $cellValue, $rowN, $colN, $table){
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
                $this->table = $table;
                $person = $table->obj;
                $papers = $person->getPapersAuthored($this->category, $start, $end, true);
                $values = array();
                foreach($papers as $paper){
                    if($paper->belongsToProject($project)){
                        $type = str_replace("Misc: ", "", $paper->getType());
                        if($type == ""){
                            $type = " ";
                        }
                        if($paper->getCategory() == "Publication"){
                            $status = $paper->getStatus();
                            if($status == "Published"){
                                $type .= "(PB)";
                            }
                            else{
                                $type .= "(Not PB)";
                            }
                        }
                        else if($paper->getCategory() == "Artifact"){
                            $status = $paper->getStatus();
                            if($status == "Peer Reviewed"){
                                $type .= "(PR)";
                            }
                            else{
                                $type .= "(Not PR)";
                            }
                        }
                        $values[$type][] = $paper->getId();
                    }
                }
                $this->setValues($values);
            }
        }
        else{
            $person = $table->obj;
            $this->table = $table;
            $papers = $person->getPapersAuthored($this->category, $start, $end, true, false);
            $values = array();
            foreach($papers as $paper){
                $values['All'][] = $paper->getId();
            }
            $this->setValues($values);
        }
        $this->sortByStatus();
    }
}
?>
