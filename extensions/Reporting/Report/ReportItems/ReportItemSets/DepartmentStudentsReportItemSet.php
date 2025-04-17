<?php

class DepartmentStudentsReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $dept = $this->getAttr("department", "");
        $uni = $this->getAttr("university", "University of Alberta");
        $hqpType = $this->getAttr('hqpType', 'grad');
        $start = $this->getAttr("start", CYCLE_START);
        $end = $this->getAttr("end", CYCLE_END);
        $me = Person::newFromWgUser();
        
        $allPeople = Person::getAllPeopleInDepartment($dept, $start, $end);
        foreach($allPeople as $person){
            if($person->isRoleDuring(HQP, $start, $end)){
                $universities = $person->getUniversitiesDuring($start, $end);
                foreach($universities as $university){
                    if(in_array(strtolower($university['position']), Person::$studentPositions[$hqpType]) &&
                       $university['department'] == $dept && 
                       $university['university'] == $uni){
                        $tuple = self::createTuple();
                        $tuple['person_id'] = $person->getId();
                        $data[] = $tuple;
                        break;
                    }
                }
            }
        }
        return $data;
    }
}

?>
