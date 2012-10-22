<?php

class AllPeopleReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $allPeople = Person::getAllPeople();
        foreach($allPeople as $person){
            $tuple = self::createTuple();
            $tuple['person_id'] = $person->getId();
            $data[] = $tuple;
        }
        return $data;
    }
}

?>
