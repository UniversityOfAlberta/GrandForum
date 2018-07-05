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
    
    function addUserRole($person, $role, $startDate="", $endDate="0000-00-00 00:00:00"){
        $r = new Role(array());
        $r->user = $person->getId();
        $r->role = $role;
        $r->startDate = $startDate;
        $r->endDate = $endDate;
        $r->create();
    }

    $wgUser = User::newFromId(1);
    $hqps = explode("\n", file_get_contents("chem_hqp.csv"));
    
    foreach($hqps as $key => $hqp){
        $csv = str_getcsv($hqp);
        $hqp[9] = "0000-00-00 00:00:00"; // Hard-coded end date
        if($csv[0] == "change" || $csv[0] == "add"){
            $person = Person::newFromId($csv[1]);
            if($person == null || $person->getId() == 0){
                // Create First
                $username = str_replace(" ", "", "{$csv[2]}.{$csv[4]}");
                $email = "{$csv[6]}@ualberta.ca";
                $user = User::createNew($username, array('real_name' => "{$csv[4]}, {$csv[2]}", 
                                                         'password' => User::crypt(mt_rand()), 
                                                         'email' => $email));
                if($user != null){
                    $hqps[$key] = preg_replace("/,/", ",{$user->getId()}", $hqp, 1);
                    $csv[1] = $user->getId();
                    Person::$cache = array();
                    $person = Person::newFromUser($user);
                    $person->updateNamesCache();
                    addUserRole($person, HQP, $hqp[8], $hqp[9]);
                }
            }
            // First delete previous University Information
            DBFunctions::delete('grand_user_university',
                                array('user_id' => $person->getId()));
        }
    }
    
    foreach($hqps as $hqp){
        $csv = str_getcsv($hqp);
        $csv[9] = "0000-00-00 00:00:00"; // Hard-coded end date
        if($csv[0] == "change" || $csv[0] == "add"){
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
                              "Chemistry",
                              $title,
                              $csv[8],
                              $csv[9]);
            
        }
    }

?>
