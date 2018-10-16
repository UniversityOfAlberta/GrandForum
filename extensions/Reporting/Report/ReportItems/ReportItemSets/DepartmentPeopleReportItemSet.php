<?php

class DepartmentPeopleReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $dept = $this->getAttr("department", "");
        $uni = $this->getAttr("university", "University of Alberta");
        $start = $this->getAttr("start", REPORTING_CYCLE_START);
        $end = $this->getAttr("end", REPORTING_CYCLE_END);
        $includeDeansPeople = (strtolower($this->getAttr("includeDeansPeople", "false")) == "true");
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
            $found = false;
            if($includeDeansPeople){
                $found = ($person->isSubRole("DD") || 
                          $person->isSubRole("DA") ||
                          $person->isSubRole("DR"));
            }
            if(($dept == "") || 
               ($person->isInDepartment($dept, $uni, $start, $end) && $person->getFECType($end) != "") || 
               ($found)){
                if($excludeMe && $person->isMe()){
                    continue;
                }
                if(($person->getId() == 243 && $dept == "Biological Sciences") ||
                   ($person->getId() == 81)){
                    // Handle special cases
                    continue;
                }
                if($person->isRoleDuring(DEAN, $start, $end) || $person->isSubRole("VPR")){
                    continue;
                }
                $index = @$fec[$person->getId()];
                $fecType = $person->getFECType($end);
                $tuple = self::createTuple();
                $tuple['person_id'] = $person->getId();
                $tuple['extra'] = "<b>{$person->getFECType($end)}</b>".str_pad($index, 3, "0", STR_PAD_LEFT);
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
