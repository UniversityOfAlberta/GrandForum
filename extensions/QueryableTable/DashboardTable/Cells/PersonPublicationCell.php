<?php

abstract class PersonPublicationCell extends PublicationCell {
    
    function PersonPublicationCell($cellType, $params, $cellValue, $rowN, $colN, $table){
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
            // Used to be used for projects
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
        if($this->category == "Publication" || $this->category == "Artifact"){
            $this->sortByStatus();
        }
    }
}
?>
