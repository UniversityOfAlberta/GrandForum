<?php

class NIReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $people = array_merge(Person::getAllPeopleDuring('PNI', REPORTING_CYCLE_START, REPORTING_CYCLE_END),
                              Person::getAllPeopleDuring('CNI', REPORTING_CYCLE_START, REPORTING_CYCLE_END));
        $finalPeople = array();
        foreach($people as $person){
            $finalPeople[$person->getName()] = $person;
        }
        ksort($finalPeople);
        foreach($finalPeople as $person){
            $tuple = self::createTuple();
            $tuple['person_id'] = $person->getId();
            $data[] = $tuple;
        }
        return $data;
    }

}

?>
