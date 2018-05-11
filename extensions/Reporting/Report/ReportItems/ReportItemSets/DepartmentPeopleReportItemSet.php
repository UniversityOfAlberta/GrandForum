<?php

class DepartmentPeopleReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $dept = $this->getAttr("department", "false");
        $start = $this->getAttr("start", REPORTING_CYCLE_START);
        $end = $this->getAttr("end", REPORTING_CYCLE_END);
        $allPeople = Person::getAllPeopleDuring(NI, $start, $end);
        foreach($allPeople as $person){
            $tuple = self::createTuple();
            foreach($person->getUniversitiesDuring($start, $end) as $uni){
                if($uni['department'] == $dept){
                    $tuple['person_id'] = $person->getId();
                    $data[] = $tuple;
                    break;
                }
            }
        }
        return $data;
    }
}

?>
