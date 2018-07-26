<?php

class DeansPeopleReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $roles = explode("|",$this->getAttr("roles", ""));
        $start = $this->getAttr("start", REPORTING_CYCLE_START);
        $end = $this->getAttr("end", REPORTING_CYCLE_END);
        $allPeople = Person::getAllPeople();

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
            foreach($roles as $role){
                if($person->isRoleDuring($role, $start, $end)){
                    $found = true;
                    break;
                }
            }
            if($found){
                $tuple = self::createTuple();
                $index = $fec[$person->getId()];
                $fecType = $person->getFECType();
                $tuple['person_id'] = $person->getId();
                $tuple['extra'] = "<b>{$person->getFECType()}</b>".str_pad($index, 3, "0", STR_PAD_LEFT);
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
