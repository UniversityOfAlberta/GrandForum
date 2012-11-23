<?php

class PersonPartnersCell extends DashboardCell {
    
    function PersonPartnersCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        $this->label = "Partners/Champions";
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
                            if($partner->getId() != ""){
                                $values['Partner'][] = array('type' => 'Partner', 'id' => $partner->getId());
                            }
                            else{
                                $values['Partner'][] = array('type' => 'Partner', 'organization' => $partner->getOrganization());
                            }
                        }
                    }
                }
                $champions = $person->getChampionsDuring($start.REPORTING_CYCLE_START_MONTH, $end.REPORTING_CYCLE_END_MONTH);
                foreach($champions as $champ){
                    if($champ->isMemberOfDuring($project, $start, $end)){
                        $values['Champion'][] = array('type' => 'Champion', 'id' => $champ->getId());
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
                        if($partner->getId() != ""){
                            $values['All'][] = array('type' => 'Partner', 'id' => $partner->getId());
                        }
                        else{
                            $values['All'][] = array('type' => 'Partner', 'organization' => $partner->getOrganization());
                        }
                    }
                }
            }
            $champions = $person->getChampionsDuring($start.REPORTING_CYCLE_START_MONTH, $end.REPORTING_CYCLE_END_MONTH);
            foreach($champions as $champ){
                $values['All'][] = array('type' => 'Champion', 'id' => $champ->getId());
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
        return array("Type", "Name");
    }
    
    function detailsRow($item){
        global $wgServer, $wgScriptPath;
        $details = "<td></td><td></td>";
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
            $details = "<td>Partner<span class='pdfOnly'><br /></span></td><td>{$organization}</td>";
        }
        else if($type == "Champion"){
            $champion = Person::newFromId($item['id']);
            $details = "<td>Champion<span class='pdfOnly'><br /></span></td><td>{$champion->getName()}</td>";
        }
        return $details;
    }
}

?>
