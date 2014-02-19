<?php

class PersonSupervisesReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $person = Person::newFromId($this->personId);
        foreach($person->getHQPDuring(REPORTING_CYCLE_START, REPORTING_CYCLE_END) as $hqp){
            $tuple = self::createTuple();
            $tuple['person_id'] = $hqp->getId();
            $data[] = $tuple;
        }
        return $data;
    }   
    
}

?>
