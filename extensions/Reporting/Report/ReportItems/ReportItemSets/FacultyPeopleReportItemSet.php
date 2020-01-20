<?php

class FacultyPeopleReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $start = $this->getAttr("start", REPORTING_CYCLE_START);
        $end = $this->getAttr("end", REPORTING_CYCLE_END);
        $atsec = (strtolower($this->getAttr("atsec", "false")) == "true");
        if($atsec){
            $allPeople = Person::getAllPeopleDuring("ATSEC", $start, $end);
        }
        else {
            $allPeople = Person::getAllPeopleDuring(NI, $start, $end);
        }
        $includeDean = (strtolower($this->getAttr("includeDean", "false")) == "true");
        $me = Person::newFromWgUser();
        
        $data = array();
        foreach($allPeople as $person){
            $caseNumber = $person->getCaseNumber($this->getReport()->year);
            if($caseNumber == ""){
                continue;
                // Don't show if no case number
            }
            if(!$includeDean && $person->isRoleDuring(DEAN, $start, $end) || $person->isSubRole("VPR")){
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
               !$me->isRole(DEANEA) &&
               !$me->isRole(HR)){
                continue;
            }
            $index = @$fec[$person->getId()];
            $tuple = self::createTuple();
            $tuple['person_id'] = $person->getId();
            $tuple['extra'] = $caseNumber;
            if(strstr($tuple['extra'], "N1") !== false){
                // New people should show first
                $tuple['extra'] = "<span style='display:none;'>0</span>".$tuple['extra'];
            }
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
