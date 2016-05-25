<?php

class PersonSupervisorsReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $person = Person::newFromId($this->personId);
        $start = $this->getAttr("startDate", REPORTING_CYCLE_START);
        $end = $this->getAttr("endDate", REPORTING_CYCLE_END);
        foreach($person->getSupervisorsDuring($start, $end) as $sup){
            $tuple = self::createTuple();
            $tuple['person_id'] = $sup->getId();
            $data[] = $tuple;
        }
        return $data;
    }   
    
}

?>
