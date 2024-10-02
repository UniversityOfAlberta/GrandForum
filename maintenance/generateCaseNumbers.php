<?php

    require_once('commandLine.inc');
    global $wgUser;
    
    $wgUser = User::newFromId(1);
    
    $start = (YEAR-1)."-07-01";
    $end = (YEAR)."-07-01";
    
    $allPeople = Person::filterFaculty(Person::getAllPeople());
    foreach($allPeople as $person){
        DBFunctions::delete("grand_case_numbers", 
                            array('year' => YEAR,
                                  'user_id' => $person->getId()));
    }
    
    $allPeople = array_merge(Person::getAllPeopleDuring(NI, $start, $end),
                             Person::getAllPeopleDuring("ATS", $start, $end));
                             
    $allPeople = Person::filterFaculty($allPeople);

    $data = DBFunctions::execSQL("SELECT `user_id`, `date_of_phd`, `date_of_appointment` 
                                  FROM `grand_personal_fec_info` 
                                  WHERE `date_retirement` >= '{$end}' OR 
                                        `date_retirement`  = '0000-00-00 00:00:00' 
                                  ORDER BY REPLACE(`date_of_phd`, '0000-00-00 00:00:00', `date_of_appointment`) DESC");
                                  
    $data2 = DBFunctions::execSQL("SELECT `user_id`, `date_of_phd`, `date_of_appointment` 
                                   FROM `grand_personal_fec_info` 
                                   WHERE `date_retirement` >= '{$end}' OR 
                                         `date_retirement`  = '0000-00-00 00:00:00' 
                                   ORDER BY `date_of_appointment` DESC");
    
    $counts = array();
    $fec = array();
    foreach($data as $row){
        // Ordered by PhD Date
        $person = Person::newFromId($row['user_id']);
        if($person == null || $person->getId() == 0 || $person->isSubRole("NoAR") || (!$person->isRoleDuring(NI, $start, $end) && !$person->isRoleDuring("ATS", $start, $end))){
            // Check to make sure the person exists, and is an Faculty
            continue;
        }
        $fecType = $person->getFECType($end);
        if($fecType == "B1" ||
           $fecType == "B2" ||
           $fecType == "C1"){
            if($row['date_of_phd'] == "0000-00-00 00:00:00"){
                echo "Missing PhD date: {$person->getNameForForms()}\n";
            }
            $index = @++$counts[$fecType];
            $fec[$row['user_id']] = $index;
        }
    }
    
    foreach($data2 as $row){
        // Ordered by Appointment Date
        $person = Person::newFromId($row['user_id']);
        if($person == null || $person->getId() == 0 || $person->isSubRole("NoAR") || (!$person->isRoleDuring(NI, $start, $end) && !$person->isRoleDuring("ATS", $start, $end))){
            // Check to make sure the person exists, and is an Faculty
            continue;
        }
        $fecType = $person->getFECType($end);
        if($fecType == "N1" ||
           $fecType == "M1" ||
           $fecType == "A1" ||
           $fecType == "D1" ||
           $fecType == "E1" ||
           $fecType == "F1" ||
           $fecType == "T1" ||
           $fecType == "T2" ||
           $fecType == "T3"){
            if($row['date_of_appointment'] == "0000-00-00 00:00:00"){
                echo "Missing Appointment date: {$person->getNameForForms()}\n";
            }
            $index = @++$counts[$fecType];
            $fec[$row['user_id']] = $index;
        }
    }
                                      
    $data = array();
    foreach($allPeople as $person){
        if(isset($fec[$person->getId()]) && $person->getFECType($end) != ""){
            // Only do this for Professors right now, but this will eventually be used for everyone
            $fecType = $person->getFECType($end);
            $index = @$fec[$person->getId()];
            $tuple = array();
            $tuple['person'] = $person;
            $tuple['case'] = "<b>{$person->getFECType($end)}</b>".str_pad($index, 3, "0", STR_PAD_LEFT);
            $data[] = $tuple;
        }
    }
    usort($data, function($a, $b){
        $A = $a['person'];
        $B = $b['person'];
        $A->getFecPersonalInfo();
        $B->getFecPersonalInfo();
        return ($a['case'].$A->dateOfAppointment > $b['case'].$B->dateOfAppointment);
    });

    foreach($data as $row){
        echo strip_tags($row['case']).": {$row['person']->getNameForForms()}\n"; // ({$row['person']->getId()})\n";
        DBFunctions::insert('grand_case_numbers',
                            array('user_id' => $row['person']->getId(),
                                  'year' => YEAR,
                                  'number' => $row['case']));
    }

?>
