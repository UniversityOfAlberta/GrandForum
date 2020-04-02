<?php

class PersonSupervisorsReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();

        $person = Person::newFromId($this->personId);

        $university = $person->getUniversity();
        $supervisors = $person->getSupervisorsDuring($university['start'], $university['start']);
        foreach($supervisors as $sup){
            $tuple = self::createTuple();
            $tuple['person_id'] = $sup->getId();
            $data[] = $tuple;
        }

        return $data;
    }
}

?>
