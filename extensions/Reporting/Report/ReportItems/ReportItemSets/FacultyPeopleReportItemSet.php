<?php

class FacultyPeopleReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $dept = $this->getAttr("department", "");
        $uni = $this->getAttr("university", "University of Alberta");
        $start = $this->getAttr("start", REPORTING_CYCLE_START);
        $end = $this->getAttr("end", (YEAR)."-07-01");
        $atsec = (strtolower($this->getAttr("atsec", "false")) == "true");
        $both = (strtolower($this->getAttr("both", "false")) == "true");
        if($both){
            $allPeople = array_merge(Person::getAllPeopleDuring(NI, $start, $end),
                                     Person::getAllPeopleDuring("ATS", $start, $end));
        }
        else if($atsec){
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
            if(!$includeDD && $person->isSubRole('DD')){
                continue;
            }
            $caseNumber = $person->getCaseNumber($this->getReport()->year);
            if($caseNumber == ""){
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
            if($excludeMe && ($person->isMe()) && 
                !$me->isRole(DEAN) &&
                !$me->isRole(DEANEA) &&
                !$me->isRole(VDEAN) && 
                !$me->isRole(HR) &&
                !$me->isRole(ADMIN)){
                // Don't show self unless user is Dean, Vice Dean, HR
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
            
            if($dept != ""){
                $person->getFecPersonalInfo();
                $depts = array_keys($person->departments);
                if(@$depts[0] != $dept){
                    // If department is specified, only inlclude people from that department
                    continue;
                }
            }
            
            // SPECIAL CASES BELOW
            if($me->getName() == "Samer.Adeeb" && $person->getName() == "Lindsey.Westover"){
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
