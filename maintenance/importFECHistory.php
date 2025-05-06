<?php

    require_once('commandLine.inc');

    global $wgUser;
    $wgUser = User::newFromId(1);
    
    function updateField($person, $col, $val){
        if($val != ""){
            echo "{$person->getName()}: $col: $val\n";
            $data = DBFunctions::select(array('grand_personal_fec_info'),
                                        array('*'),
                                        array('user_id' => $person->getId()));
            if(count($data) == 0){
                DBFunctions::insert('grand_personal_fec_info',
                                    array('user_id' => $person->getId()));
            }
            DBFunctions::update('grand_personal_fec_info',
                                array($col => $val),
                                array('user_id' => $person->getId()));
        }
    }
    
    $lines = explode("\n", file_get_contents("fec_history.csv"));
    foreach($lines as $line){
        $csv = str_getcsv($line);
        if(count($csv) > 1){
            $emplid = $csv[0];
            $phd = $csv[1];
            $appointment = $csv[2];
            $assistant = $csv[3];
            $associate = $csv[4];
            $professor = $csv[5];
            $fso2 = $csv[6];
            $fso3 = $csv[7];
            $fso4 = $csv[8];
            $ats1 = $csv[9];
            $ats2 = $csv[10];
            $ats3 = $csv[11];
            $probation1 = $csv[12];
            $probation2 = $csv[13];
            $tenure = $csv[14];
            
            $person = Person::newFromEmployeeId($emplid);
            if($person instanceof FullPerson && $person->getId() != 0){
                $person->getFecPersonalInfo();
                if($person->dateOfPhd == ""){
                    updateField($person, 'date_of_phd', $phd);
                }
                if($person->dateOfAppointment == ""){
                    updateField($person, 'date_of_appointment', $appointment);
                }
                if($person->dateOfAssistant == ""){
                    updateField($person, 'date_assistant', $assistant);
                }
                if($person->dateOfAssociate == ""){
                    updateField($person, 'date_associate', $associate);
                }
                if($person->dateOfProfessor == ""){
                    updateField($person, 'date_professor', $professor);
                }
                if($person->dateFso2 == ""){
                    updateField($person, 'date_fso2', $fso2);
                }
                if($person->dateFso3 == ""){
                    updateField($person, 'date_fso3', $fso3);
                }
                if($person->dateFso4 == ""){
                    updateField($person, 'date_fso4', $fso4);
                }
                if($person->dateAtsec1 == ""){
                    updateField($person, 'date_atsec1', $ats1);
                }
                if($person->dateAtsec2 == ""){
                    updateField($person, 'date_atsec2', $ats2);
                }
                if($person->dateAtsec3 == ""){
                    updateField($person, 'date_atsec3', $ats3);
                }
                if($person->dateOfProbation1 == ""){
                    updateField($person, 'date_probation1', $probation1);
                }
                if($person->dateOfProbation2 == ""){
                    updateField($person, 'date_probation2', $probation2);
                }
                if($person->dateOfTenure == ""){
                    updateField($person, 'date_tenure', $tenure);
                }
            }
        }
    }
?>
