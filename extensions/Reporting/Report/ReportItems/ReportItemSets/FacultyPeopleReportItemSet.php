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
                                    
        $data2 = DBFunctions::select(array('grand_personal_fec_info'),
                                    array('user_id'),
                                    array('date_retirement' => EQ('0000-00-00 00:00:00')),
                                    array('date_of_phd' => 'DESC',
                                          'date_of_appointment' => 'DESC'));
        
        $counts = array();
        
        $fec = array();                 
        foreach($data as $row){
            $fec[$row['user_id']] = count($fec) + 1;
        }
        
        $fec2 = array();
        foreach($data2 as $row){
            $fec2[$row['user_id']] = count($fec2) + 1;
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
            if(isset($fec2[$row['user_id']]) && strstr($person->getFECType($end), "C") !== false){
                // Only do this for Professors right now, but this will eventually be used for everyone
                $fecType = $person->getFECType($end);
                $index = @++$counts[$fecType];
                $indexOld = @$fec[$person->getId()];
                $tuple = self::createTuple();
                $tuple['person_id'] = $person->getId();
                $tuple['extra'] = "<b>{$person->getFECType($end)}</b>".str_pad($index, 3, "0", STR_PAD_LEFT)." <span style='color:#888888;margin-left:10px;'>(<b>{$person->getFECType($end)}</b>".str_pad($indexOld, 3, "0", STR_PAD_LEFT).")</span>";
                $data[] = $tuple;
            }
            else if(isset($fec[$row['user_id']]) && $person->getFECType($end) != ""){
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
