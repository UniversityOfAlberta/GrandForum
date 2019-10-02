<?php

class FacultyPeopleReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $start = $this->getAttr("start", REPORTING_CYCLE_START);
        $end = $this->getAttr("end", REPORTING_CYCLE_END);
        $allPeople = Person::getAllPeopleDuring(NI, $start, $end);
        $includeDean = (strtolower($this->getAttr("includeDean", "false")) == "true");
        $me = Person::newFromWgUser();
        
        $data = array();
        foreach($allPeople as $person){
            if($person->getCaseNumber($this->getReport()->year) == ""){
                continue;
                // Don't show if no case number
            }
            if(!$includeDean && $person->isRoleDuring(DEAN, $start, $end)){
                // Don't show Deans
                continue;
            }
            if(($person->isSubRole("DD")) && 
                !$me->isRole(DEAN) &&
                !$me->isRole(DEANEA) &&
                !$me->isRole(VDEAN) && 
                !$me->isRole(HR) &&
                !$me->isRole(ADMIN)){
                // Don't show DD people unless user is Dean, Vice Dean, HR
                continue;
            }
            if(($person->isRoleDuring(ISAC, REPORTING_CYCLE_START, REPORTING_CYCLE_END) || $person->isRole(ISAC)) && 
                !$me->isRole(DEAN) &&
                !$me->isRole(DEANEA) &&
                !$me->isRole(VDEAN) && 
                !$me->isRole(HR) &&
                !$me->isRole(ADMIN)){
                // Secondary check for Chairs.  Chairs should only show up for Dean, Vice Dean, HR
                continue;
            }
            // SPECIAL CASES BELOW
            if($person->getName() == "Douglas.Wylie" &&
               !$me->isRole(DEAN) &&
               !$me->isRole(DEANEA)){
                continue;
            }
            $index = @$fec[$person->getId()];
            $tuple = self::createTuple();
            $tuple['person_id'] = $person->getId();
            $tuple['extra'] = $person->getCaseNumber($this->getReport()->year);
            $data[] = $tuple;
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
