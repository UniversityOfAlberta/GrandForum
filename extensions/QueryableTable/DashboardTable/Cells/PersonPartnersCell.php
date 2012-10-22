<?php

class PersonPartnersCell extends DashboardCell {
    
    function PersonPartnersCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $this->label = "Partners";
        $start = "0000";
        $end = "2100";
        if(count($params) == 1){
            $params[2] = $params[0];
        }
        else{
            if(isset($params[0])){
                // Start
                $start = substr($params[0], 0, 4);
            }
            if(isset($params[1])){
                // End
                $end = substr($params[1], 0, 4);
            }
        }
        if(isset($params[2])){
            $project = Project::newFromName($params[2]);
            if($project != null && $project->getName() != null){
                $this->obj = $project;
                $person = $table->obj;
                $contributions = $person->getContributions();
                $values = array();
                foreach($contributions as $contribution){
                    if($contribution->belongsToProject($project) && $contribution->getYear() >= $start && $contribution->getYear() <= $end){
                        foreach($contribution->getPartners() as $partner){
                            $values['All'][$partner->getId()] = $partner->getId();
                        }
                    }
                }
                $this->setValues($values);
            }
        }
        else{
            $person = $table->obj;
            $contributions = $person->getContributions();
            $values = array();
            foreach($contributions as $contribution){
                if($contribution->getYear() >= $start && $contribution->getYear() <= $end){
                    foreach($contribution->getPartners() as $partner){
                        $values['All'][$partner->getId()] = $partner->getId();
                    }
                }
            }
            $this->setValues($values);
        }
    }
    
    function rasterize(){
        return array(PERSON_PARTNERS, $this);
    }
    
    function toString(){
        return $this->value;
    }
    
    function getHeaders(){
        return array("Organization");
    }
    
    function detailsRow($item){
        global $wgServer, $wgScriptPath;
        $partner = Partner::newFromId($item);
        $details = "<td>{$partner->getOrganization()}</td>";
        return $details;
    }
}

?>
