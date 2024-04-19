<?php

class FacultyPeopleReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $dept = $this->getAttr("department", "");
        $uni = $this->getAttr("university", "University of Alberta");
        $start = $this->getAttr("start", REPORTING_CYCLE_START);
        $end = $this->getAttr("end", (YEAR)."-07-01");
        $atsec = (strtolower($this->getAttr("atsec", "false")) == "true");
        if($atsec){
            $allPeople = Person::getAllPeopleDuring("ATS", $start, $end);
        }
        else {
            $allPeople = Person::getAllPeopleDuring(NI, $start, $end);
        }
        
        $allPeople = Person::filterFaculty($allPeople);
        
        $includeDean = (strtolower($this->getAttr("includeDean", "false")) == "true");
        $includeDD = (strtolower($this->getAttr("includeDD", "true")) == "true");
        $excludeMe = (strtolower($this->getAttr("excludeMe", "false")) == "true");
        $me = Person::newFromWgUser();
        
        $data = array();
        foreach($allPeople as $person){
            /*if(!$person->isSubRole("SPECIAL2020") &&
               !$me->isRole(DEAN) &&
               !$me->isRole(DEANEA) &&
               !$me->isRole(VDEAN) && 
               !$me->isRole(HR) &&
               !$me->isRole(ADMIN)){
                // Special rule for covid. Only certain people will be evaluated
                continue;
            }
            */
            if(!$includeDD && $person->isSubRole('DD') &&
               $person->getName() != "Elizabeth.Hodges"){ // TODO: Get rid of this
                continue;
            }
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
                !$me->isRole(ADMIN) &&
                $person->getName() != "Elizabeth.Hodges"){ // TODO: Get rid of this
                // Don't show DD people unless user is Dean, Vice Dean, HR
                continue;
            }
            if($excludeMe && ($person->isMe()) && 
                !$me->isRole(DEAN) &&
                !$me->isRole(DEANEA) &&
                !$me->isRole(VDEAN) && 
                !$me->isRole(HR) &&
                !$me->isRole(ADMIN) &&
                $person->getName() != "Elizabeth.Hodges"){ // TODO: Get rid of this){
                // Don't show DD people unless user is Dean, Vice Dean, HR
                continue;
            }
            if(($person->isRoleDuring(CHAIR, REPORTING_CYCLE_START, REPORTING_CYCLE_END) || $person->isRole(CHAIR)) && 
                !$me->isRole(DEAN) &&
                !$me->isRole(DEANEA) &&
                !$me->isRole(VDEAN) && 
                !$me->isRole(HR) &&
                !$me->isRole(ADMIN)){
                // Secondary check for Chairs.  Chairs should only show up for Dean, Vice Dean, HR
                continue;
            }
            $person->getFecPersonalInfo();
            if($dept != "" && @$person->departments[0] != $dept){
                // If department is specified, only inlclude people from that department
                continue;
            }
            
            // SPECIAL CASES BELOW
            if($me->getName() == "Anthony.Singhal" && $person->getName() == "Deanna.Singhal"){
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
