<?php

    require_once('commandLine.inc');
    global $wgUser;
    
    $wgUser = User::newFromId(1);
    
    $year = YEAR;
    
    $start = ($year-1)."-07-01";
    $end = ($year)."-06-30";
    $end1 = ($year)."-07-01";
    
    $allPeople = Person::filterFaculty(Person::getAllPeople());
    foreach($allPeople as $person){
        DBFunctions::delete("grand_case_numbers", 
                            array('year' => $year,
                                  'user_id' => $person->getId()));
    }
    
    $allPeople = array_merge(Person::getAllPeopleDuring(NI, $start, $end1),
                             Person::getAllPeopleDuring("ATS", $start, $end1));
                             
    $allPeople = Person::filterFaculty($allPeople);
    
    $counts = array();
    $fec = array();
    
    function isValid($person){
        global $start, $end, $end1;
        return !($person == null || $person->getId() == 0 || $person->isSubRole("NoAR") || (!$person->isRoleDuring(NI, $start, $end1) && !$person->isRoleDuring("ATS", $start, $end1)));
    }
    
    if(getFaculty() == "Engineering"){
        // Sort by Salary
        $data = DBFunctions::execSQL("SELECT `user_id`
                                      FROM `grand_user_salaries`
                                      WHERE `year` = '$year'
                                      ORDER BY salary ASC");
        foreach($data as $row){
            $person = Person::newFromId($row['user_id']);
            if(!isValid($person)){
                // Check to make sure the person exists, and is an Faculty
                continue;
            }
            $fecType = $person->getFECType($end);
            $index = @++$counts[$fecType];
            $fec[$row['user_id']] = $index;
        }
        foreach($allPeople as $person){
            $fecType = $person->getFECType($end);
            if(!isset($fec[$person->getId()]) && isValid($person) && $fecType != ""){
                echo "Missing salary information: {$person->getNameForForms()}\n";
                $index = @++$counts[$fecType];
                $fec[$person->getId()] = $index;
            }
        }
    }
    else{
        // Sort by most recent date
        $data = DBFunctions::execSQL("SELECT `user_id`
                                      FROM `grand_personal_fec_info` 
                                      WHERE `date_retirement` >= '{$end}' OR 
                                            `date_retirement` IS NULL
                                      ORDER BY GREATEST(IF(`date_of_phd` <= '{$end}', `date_of_phd`, NULL), 
                                                        IF(`date_of_appointment` <= '{$end}', `date_of_appointment`, NULL),
                                                        IF(`date_assistant` <= '{$end}', `date_assistant`, NULL),
                                                        IF(`date_associate` <= '{$end}', `date_associate`, NULL),
                                                        IF(`date_professor` <= '{$end}', `date_professor`, NULL), 
                                                        IF(`date_fso2` <= '{$end}', `date_fso2`, NULL),
                                                        IF(`date_fso3` <= '{$end}', `date_fso3`, NULL),
                                                        IF(`date_fso4` <= '{$end}', `date_fso4`, NULL),
                                                        IF(`date_atsec1` <= '{$end}', `date_atsec1`, NULL), 
                                                        IF(`date_atsec2` <= '{$end}', `date_atsec2`, NULL), 
                                                        IF(`date_atsec3` <= '{$end}', `date_atsec3`, NULL), 
                                                        IF(`date_tenure` <= '{$end}', `date_tenure`, NULL)) DESC");

        foreach($data as $row){
            // Ordered by PhD Date
            $person = Person::newFromId($row['user_id']);
            if(!isValid($person)){
                // Check to make sure the person exists, and is an Faculty
                continue;
            }
            $fecType = $person->getFECType($end);
            if($fecType != ""){
                $index = @++$counts[$fecType];
                $fec[$row['user_id']] = $index;
            }
        }
    }
                                      
    $data = array();
    foreach($allPeople as $person){
        if(isset($fec[$person->getId()]) && $person->getFECType($end) != ""){
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
                                  'year' => $year,
                                  'number' => $row['case']));
    }

?>
