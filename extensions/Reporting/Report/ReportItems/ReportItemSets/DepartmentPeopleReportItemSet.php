<?php

class DepartmentPeopleReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $dept = $this->getAttr("department", "false");
        $uni = $this->getAttr("university", "University of Alberta");
        $start = $this->getAttr("start", REPORTING_CYCLE_START);
        $end = $this->getAttr("end", REPORTING_CYCLE_END);
        $excludeMe = (strtolower($this->getAttr("excludeMe", "false")) == "true");
        $allPeople = Person::getAllPeopleDuring(NI, $start, $end);
        
        $data = DBFunctions::select(array('grand_personal_fec_info'),
                                    array('user_id'),
                                    array(),
                                    array('date_of_appointment' => 'DESC'));
        $fec = array();                 
        foreach($data as $row){
            $fec[$row['user_id']] = count($fec) + 1;
        }
        
        $data = array();
        foreach($allPeople as $person){
            if($person->isInDepartment($dept, $uni, $start, $end) && $person->getFECType() != ""){
                if($excludeMe && $person->isMe()){
                    continue;
                }
                if($person->isRole(DEAN)){
                    continue;
                }
                $index = $fec[$person->getId()];
                $fecType = $person->getFECType();
                $tuple = self::createTuple();
                $tuple['person_id'] = $person->getId();
                $tuple['extra'] = "<b>{$person->getFECType()}</b>".str_pad($index, 3, "0", STR_PAD_LEFT);
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
