<?php

class FacultyPeopleReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $start = $this->getAttr("start", REPORTING_CYCLE_START);
        $end = $this->getAttr("end", REPORTING_CYCLE_END);
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
            if($person->getId() == 68 ||
               $person->getId() == 298){
                // Handle special cases
                continue;
            }
            if($person->isRoleDuring(DEAN, $start, $end)){
                // Don't show Deans
                continue;
            }
            if(isset($fec[$row['user_id']]) && $person->getFECType($end) != ""){
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
