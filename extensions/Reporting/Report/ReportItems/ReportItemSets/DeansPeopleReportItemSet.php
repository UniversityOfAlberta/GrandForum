<?php

class DeansPeopleReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $start = $this->getAttr("start", REPORTING_CYCLE_START);
        $end = $this->getAttr("end", REPORTING_CYCLE_END);
        $allPeople = Person::getAllPeopleDuring(NI, $start, $end);
        $me = Person::newFromWgUser();
        
        $data = array();
        foreach($allPeople as $person){
            $found = ($person->isSubRole("DD") || 
                      $person->isSubRole("DA") ||
                      $person->isSubRole("DR"));
            if($found){
                if($me->isRoleDuring(DEAN, REPORTING_CYCLE_START, REPORTING_CYCLE_END) && !$me->isRole(DEAN) && !$person->isSubRole("DA")){
                    // Previous Dean should not see any people except for those who have an explicit Dean's Advice
                    continue;
                }
                if(!$me->isRoleDuring(DEAN, REPORTING_CYCLE_START, REPORTING_CYCLE_END) && $person->isSubRole("DA")){
                    // Don't show DA if the current Dean wasn't the Dean during the reporting period
                    continue;
                }
                $tuple = self::createTuple();
                $fecType = $person->getFECType($end);
                $tuple['person_id'] = $person->getId();
                $tuple['extra'] = $person->getCaseNumber($this->getReport()->year);
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
