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

        if($me->getName() != "Christopher.Sturdy" && !$me->isRole(CHAIR) && !$me->isRole(ACHAIR) && !$me->isRole(EA) && !$me->isRoleDuring(CHAIR, REPORTING_CYCLE_START, REPORTING_CYCLE_END)){
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
            // SPECIAL CASES FOR PEOPLE FROM OTHER DEPARTMENTS BELOW
            if(($me->getName() == "Lin.Ferguson" || $me->getName() == "Rik.Tykwinski") && $person->getName() == "Lisa.Willis"){
                // This is also a special case, but needs to be put here
                goto create;
            }
            if(($me->getName() == "Manveen.Maadhra" || $me->getName() == "Thomas.Chacko") && $person->getName() == "Jonathan.Dennis"){
                // This is also a special case, but needs to be put here
                goto create;
            }
            if(($me->getName() == "Linda.Christensen" || $me->getName() == "Tracy.Raivio") && $person->getName() == "Deanna.Singhal"){
                // This is also a special case, but needs to be put here
                goto create;
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
                if($me->isRoleDuring(EA, REPORTING_CYCLE_START, REPORTING_CYCLE_END) && ($person->isRoleDuring(CHAIR, REPORTING_CYCLE_START, REPORTING_CYCLE_END) || $person->isRole(CHAIR))){
                    // EA should not get to see Chair's Information
                    continue;
                }
                if(($person->isRoleDuring(CHAIR, REPORTING_CYCLE_START, REPORTING_CYCLE_END) || $person->isRole(CHAIR)) && !$person->isSubRole("CR")){
                    // Chairs should not show up, unless they have an explicit Chair's Recommendation
                    continue;
                }
                if($me->isRoleDuring(CHAIR, REPORTING_CYCLE_START, REPORTING_CYCLE_END) && !$me->isRole(CHAIR) && !$person->isSubRole("CR")){
                    // Previous Chair should not see any people except for those who have an explicit Chair's Recommendation
                    continue;
                }
                // SPECIAL CASES BELOW
                if($me->getName() == "Christopher.Sturdy" && $person->getName() != "Deanna.Singhal"){
                    // Sturdy should only see Deanna
                    continue;
                }
                if(($me->getName() == "Linda.Christensen" || $me->getName() == "Tracy.Raivio") && ($person->getName() == "Mark.Lewis" || 
                                                                                                   $person->getName() == "Jonathan.Dennis" ||
                                                                                                   $person->getName() == "Lisa.Willis")){
                    // Not reviewed by BioSci, only Math
                    continue;
                }
                if(($me->getName() == ".Psychair") && $person->getName() == "Anthony.Singhal"){
                    continue;
                }
                if(($me->getName() == "Anthony.Singhal" || $me->getName() == ".Psychair" || 
                    $me->getName() == "PSYCH.ExecutiveAssistant" || $me->getName() == "Llyn.Madsen") && $person->getName() == "Deanna.Singhal"){
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
