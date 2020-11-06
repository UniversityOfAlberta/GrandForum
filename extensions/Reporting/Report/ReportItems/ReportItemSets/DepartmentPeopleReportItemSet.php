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
        $me = Person::newFromWgUser();

        if($me->getName() != "Christopher.Sturdy" && !$me->isRole(ISAC) && !$me->isRole(ACHAIR) && !$me->isRole(IAC) && !$me->isRoleDuring(ISAC, REPORTING_CYCLE_START, REPORTING_CYCLE_END)){
            // Person isn't a Chair/EA, so don't return anyone
            return $data;
        }

        $atsec = (strtolower($this->getAttr("atsec", "false")) == "true");
        
        if($me->isRole(ACHAIR)){
            // Associate Chair should only see ATS
            $atsec = true;
        }
        
        if($atsec){
            $allPeople = Person::getAllPeopleDuring("ATS", $start, $end);
        }
        else {
            $allPeople = Person::getAllPeopleDuring(NI, $start, $end);
        }
        
        foreach($allPeople as $person){
            if($person->getCaseNumber($this->getReport()->year) == ""){
                continue;
                // Don't show if no case number
            }
            $found = false;
            if($includeDeansPeople){
                $found = ($person->isSubRole("DD") || 
                          $person->isSubRole("DA") ||
                          $person->isSubRole("DR"));
            }
            if(($dept == "") || 
               ($person->isInDepartment($dept, $uni, $start, $end)) || 
               ($found)){
                if(($me->getName() == "PSYCH.ExecutiveAssistant" || $me->getName() == "Jannie.Boulter") && $person->getName() == "Anthony.Singhal"){
                    // This is also a special case, but needs to be put here
                    goto create;
                }
                if($excludeMe && $person->isMe()){
                    // Should not see themselves in recommendations
                    continue;
                }
                if($person->isRoleDuring(DEAN, REPORTING_CYCLE_START, REPORTING_CYCLE_END) || $person->isRole(DEAN) || $person->isSubRole("VPR")){
                    // Dean should not be in recommendations
                    continue;
                }
                if($me->isRoleDuring(IAC, REPORTING_CYCLE_START, REPORTING_CYCLE_END) && ($person->isRoleDuring(ISAC, REPORTING_CYCLE_START, REPORTING_CYCLE_END) || $person->isRole(ISAC))){
                    // EA should not get to see Chair's Information
                    continue;
                }
                if(($person->isRoleDuring(ISAC, REPORTING_CYCLE_START, REPORTING_CYCLE_END) || $person->isRole(ISAC)) && !$person->isSubRole("CR")){
                    // Chairs should not show up, unless they have an explicit Chair's Recommendation
                    continue;
                }
                if($me->isRoleDuring(ISAC, REPORTING_CYCLE_START, REPORTING_CYCLE_END) && !$me->isRole(ISAC) && !$person->isSubRole("CR")){
                    // Previous Chair should not see any people except for those who have an explicit Chair's Recommendation
                    continue;
                }
                // SPECIAL CASES BELOW
                if($me->getName() == "Christopher.Sturdy" && $person->getName() != "Deanna.Singhal"){
                    // Sturdy should only see Deanna
                    continue;
                }
                if(($me->getName() == "Linda.Christensen" || $me->getName() == "David.Coltman") && $person->getName() == "Mark.Lewis"){
                    // Not reviewed by BioSci, only Math
                    continue;
                }
                if(($me->getName() == ".Psychair") && $person->getName() == "Anthony.Singhal"){
                    continue;
                }
                if(($me->getName() == "Anthony.Singhal" || $me->getName() == ".Psychair") && $person->getName() == "Deanna.Singhal"){
                    continue;
                }
                if(($me->getName() == "Linda.Christensen" || $me->getName() == "David.Coltman") && $person->getName() == "Douglas.Wylie"){
                    continue;
                }
                create:
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
            return ($a['extra'].$A->dateOfAppointment > $b['extra'].$B->dateOfAppointment);
        });
        return $data;
    }
}

?>
