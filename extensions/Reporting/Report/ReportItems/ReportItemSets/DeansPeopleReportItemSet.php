<?php

class DeansPeopleReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $start = $this->getAttr("start", CYCLE_START);
        $end = $this->getAttr("end", CYCLE_END);
        $me = Person::newFromWgUser();
        if(!$me->isRole(DEAN) && !$me->isRole(DEANEA) && !$me->isRoleDuring(DEAN, CYCLE_START, CYCLE_END)){
            // Person isn't a Dean/DeanEA, so don't return anyone
            return $data;
        }
        
        $allPeople = Person::getAllPeopleDuring(NI, $start, $end);
        $allPeople = Person::filterFaculty($allPeople);
        
        $data = array();
        foreach($allPeople as $person){
            if($person->getCaseNumber($this->getReport()->year) == ""){
                continue;
                // Don't show if no case number
            }
            if($person->isMe()){
                // Should not see themselves in recommendations
                continue;
            }
            $found = ($person->isSubRole("DD") || 
                      $person->isSubRole("DA") ||
                      $person->isSubRole("DR"));
            if($found){
                if($me->isRoleDuring(DEAN, CYCLE_START, CYCLE_END) && !$me->isRole(DEAN) && !$person->isSubRole("DA")){
                    // Previous Dean should not see any people except for those who have an explicit Dean's Advice
                    continue;
                }
                $tuple = self::createTuple();
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
            return strcmp($a['extra'].$A->dateOfAppointment, $b['extra'].$B->dateOfAppointment);
        });
        return $data;
    }
}

?>
