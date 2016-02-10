<?php

class PersonSupervisesReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $person = Person::newFromId($this->personId);
        $positions = array_filter(explode("|", $this->getAttr('pos', "")));
        $subType = $this->getAttr('subType', $this->getAttr('subRole', ""));
        foreach($person->getHQPDuring(REPORTING_CYCLE_START, REPORTING_CYCLE_END) as $hqp){
            if((count($positions) == 0 || array_search($hqp->getPosition(), $positions) !== false) &&
               ($subType == "" || $hqp->isSubRole($subType))){
                $tuple = self::createTuple();
                $tuple['person_id'] = $hqp->getId();
                $data[] = $tuple;
            }
        }
        return $data;
    }   
    
}

?>
