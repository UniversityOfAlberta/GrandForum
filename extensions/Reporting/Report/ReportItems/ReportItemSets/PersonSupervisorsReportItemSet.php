<?php

class PersonSupervisorsReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $person = Person::newFromId($this->personId);
        foreach($person->getSupervisorsDuring(REPORTING_CYCLE_START, REPORTING_CYCLE_END) as $sup){
            $tuple = self::createTuple();
            $tuple['person_id'] = $sup->getId();
            $data[] = $tuple;
        }
        return $data;
    }   
    
}

?>
