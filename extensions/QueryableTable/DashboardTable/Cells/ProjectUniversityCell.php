<?php

class ProjectUniversityCell extends Cell{
    
    function ProjectUniversityCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $start = "0000";
        $end = "2100";
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
            $person = Person::newFromName($params[2]);
            $uni = $person->getPartnerName();
            $dept = $person->getPartnerTitle();
            
            $uni = ($uni == "") ? $person->getUni() : $uni;
            $university = University::newFromName($uni);
            $uni = $university->getShortName();
            $dept = ($dept == "") ? $person->getDepartment() : $dept;
            if($uni != "" && $dept != ""){
                $this->value = "$uni, $dept";
            }
            else if($uni != "" && $dept == ""){
                $this->value = $uni;
            }
            else if($uni == "" && $dept != ""){
                $this->value = $dept;
            }
            else{
                $this->value = "Unknown";
            }
        }
        else{
            $this->value = $cellValue;
        }
    }
    
    function rasterize(){
        return array(PROJECT_UNIVERSITY, $this);
    }
    
    function toString(){
        return $this->value;
    }
    
    function render(){
        global $wgServer, $wgScriptPath;
        if(!isset($_GET['generatePDF'])){
            $this->style = 'text-align:left;white-space:normal !important;';
        }
        else{
            $this->style = 'text-align:left;';
        }
        return "{$this->value}";
    }
}

?>
