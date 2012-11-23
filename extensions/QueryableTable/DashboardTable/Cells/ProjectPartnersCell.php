<?php

class ProjectPartnersCell extends DashboardCell {
    
    function ProjectPartnersCell($cellType, $params, $cellValue, $rowN, $colN, $table){
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
            $project = $table->obj;
            $person = Person::newFromId($params[2]);
            if($person != null && $person->getName() != null){
                $this->obj = $person;
                $contributions = $project->getContributions();
                $values = array();
                foreach($contributions as $contribution){
                    if($contribution->getYear() >= $start && $contribution->getYear() <= $end){
                        $people = $contribution->getPeople();
                        foreach($people as $p){
                            if($p instanceof Person){
                                if($p instanceof Person && $p->getId() == $person->getId()){
                                    foreach($contribution->getPartners() as $partner){
                                        if($partner->getId() != ""){
                                            $values['Partner'][] = array('type' => 'Partner', 'id' => $partner->getId());
                                        }
                                        else{
                                            $values['Partner'][] = array('type' => 'Partner', 'organization' => $partner->getOrganization());
                                        }
                                    }
                                    break;
                                }
                            }
                        }
                    }
                }
                $this->setValues($values);
            }
        }
        else{
            $project = $table->obj;
            $contributions = $project->getContributions();
            $values = array();
            foreach($contributions as $contribution){
                if($contribution->getYear() >= $start && $contribution->getYear() <= $end){
                    foreach($contribution->getPartners() as $partner){
                        if($partner->getId() != ""){
                            $values['All'][] = array('type' => 'Partner', 'id' => $partner->getId());
                        }
                        else{
                            $values['All'][] = array('type' => 'Partner', 'organization' => $partner->getOrganization());
                        }
                    }
                }
            }
            $this->setValues($values);
        }
    }
    
    function rasterize(){
        return array(PROJECT_PARTNERS, $this);
    }
    
    function toString(){
        return $this->value;
    }
    
    function getHeaders(){
        return array("Name");
    }
    
    function detailsRow($item){
        global $wgServer, $wgScriptPath;
        $details = "<td></td>";
        $type = $item['type'];
        if($type == "Partner"){
            $organization = "";
            if(isset($item['id'])){
                $partner = Partner::newFromId($item['id']);
                $organization = $partner->getOrganization();
            }
            else if(isset($item['organization'])){
                $organization = $item['organization'];
            }
            $details = "<td>{$organization}</td>";
        }
        return $details;
    }
}

?>
