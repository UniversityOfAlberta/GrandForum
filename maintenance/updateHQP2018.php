<?php

    require_once('commandLine.inc');
    global $wgUser;

    function addUserUniversity($person, $university, $department, $title, $startDate="", $endDate="0000-00-00 00:00:00"){
        $_POST['university'] = $university;
        $_POST['department'] = $department;
        $_POST['startDate'] = $startDate;
        $_POST['endDate'] = $endDate;
        $_POST['researchArea'] = "";
        $_POST['position'] = $title;
        $api = new PersonUniversitiesAPI();
        $api->params['id'] = $person->getId();
        $api->doPOST();
    }

    $wgUser = User::newFromId(1);
    $cs_hqp = explode("\n", file_get_contents("cs_hqp.csv"));
    
    foreach($cs_hqp as $hqp){
        $csv = str_getcsv($hqp);
        if($csv[0] == "graddb"){
            $person = Person::newFromId($csv[1]);
            // First delete previous University Information
            DBFunctions::delete('grand_user_university',
                                array('user_id' => $person->getId()));
        }
    }
    
    foreach($cs_hqp as $hqp){
        $csv = str_getcsv($hqp);
        if($csv[0] == "graddb"){
            $person = Person::newFromId($csv[1]);
            echo "=== {$csv[2]} {$csv[4]} ===\n";
            
            DBFunctions::update('mw_user',
                                array('first_name' => $csv[2],
                                      'middle_name' => $csv[3],
                                      'last_name' => $csv[4]),
                                array('user_id' => $person->getId()));
                                
            if($person->getEmployeeId() == ""){
                DBFunctions::update('mw_user',
                                    array('employee_id' => $csv[5]),
                                    array('user_id' => $person->getId()));
            }

            $title = str_replace("Masters" , "Master's", $csv[7]);

            addUserUniversity($person, 
                              "University of Alberta",
                              "Computing Science",
                              $title,
                              $csv[8],
                              $csv[9]);
            
        }
    }

?>
