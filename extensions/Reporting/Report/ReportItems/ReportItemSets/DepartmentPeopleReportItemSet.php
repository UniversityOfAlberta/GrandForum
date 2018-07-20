<?php

class DepartmentPeopleReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $dept = $this->getAttr("department", "false");
        $uni = $this->getAttr("university", "%");
        $start = $this->getAttr("start", REPORTING_CYCLE_START);
        $end = $this->getAttr("end", REPORTING_CYCLE_END);
        $excludeMe = (strtolower($this->getAttr("excludeMe", "false")) == "true");
        $allPeople = Person::getAllPeopleDuring(NI, $start, $end);
        $fecTypes = array();
        foreach($allPeople as $person){
            if($person->isInDepartment($dept, $uni) && $person->getFECType() != ""){
                if($excludeMe && $person->isMe()){
                    continue;
                }
                $fecType = $person->getFECType();
                $tuple = self::createTuple();
                $tuple['person_id'] = $person->getId();
                @$fecTypes[$fecType]++;
                $tuple['extra'] = $person->getFECType().str_pad($fecTypes[$fecType], 2, "0", STR_PAD_LEFT);
                $data[] = $tuple;
            }
        }
        usort($data, function($a, $b){
            $A = Person::newFromId($a['person_id']);
            $B = Person::newFromId($b['person_id']);
            $A->getFecPersonalInfo();
            $B->getFecPersonalInfo();
            return ($a['extra'].$A->dateOfAppointment > $b['extra'].$B->dateOfAppointment);
        });
        return $data;
    }
}

?>
