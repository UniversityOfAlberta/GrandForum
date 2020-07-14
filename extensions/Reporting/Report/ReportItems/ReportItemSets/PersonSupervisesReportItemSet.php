<?php

class PersonSupervisesReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $person = Person::newFromId($this->getAttr("personId", $this->personId));
        $positions = array_filter(explode("|", $this->getAttr('pos', "")));
        $subType = $this->getAttr('subType', $this->getAttr('subRole', ""));
        $project = Project::newFromHistoricName($this->getAttr('project', ""));
        $start = $this->getAttr("startDate", $this->getReport()->year."-04-01 00:00:00");
        $end = $this->getAttr("endDate", ($this->getReport()->year+1)."-03-31 23:59:59");
        foreach($person->getHQPDuring($start, $end) as $hqp){
            if((count($positions) == 0 || array_search($hqp->getPosition(), $positions) !== false) &&
               ($subType == "" || $hqp->isSubRole($subType))){
                if($this->getAttr('project', "") == "" || ($project != null && $project->getId() != 0 && $hqp->isRoleDuring(HQP, $start, $end, $project))){
                    $tuple = self::createTuple();
                    $tuple['person_id'] = $hqp->getId();
                    $data[] = $tuple;
                }
            }
        }
        return $data;
    }   
    
}

?>
