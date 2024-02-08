<?php

    require_once('commandLine.inc');
    $wgHooks['AddNewAccount'] = array();

    global $wgUser;
    $wgUser = User::newFromId(1);
    
    $startDate = date(YEAR.'-07-01');

    $deptCodes = array();
    foreach(explode("\n", file_get_contents("deptCodes.csv")) as $line){
        $csv = str_getcsv($line);
        if(count($csv) <= 1){ continue; }
        $deptCodes[$csv[1]] = $csv[2];
    }

    $lines = explode("\n", file_get_contents("peopleList.csv"));
    foreach($lines as $line){
        $csv = str_getcsv($line);
        if(count($csv) <= 1){ continue; }
        $emplid = $csv[0];
        $type = $csv[1];
        $pos = $csv[2];
        $ccid = $csv[3];
        $lastName = $csv[4];
        $firstName = $csv[5];
        $academicDept = $csv[6];
        $academicDeptCode = $csv[7];
        $hrDeptId = $csv[8];
        $hrDept = $csv[9];
        $program = $csv[10];
        
        if(strstr($hrDept, "SCI ") === false &&
           strstr($hrDept, "SC ") === false &&
           strstr($hrDept, "ART Psychology") === false &&
           strstr($hrDept, "ENG ") === false &&
           strstr($hrDept, "ALES ") === false){
            continue;
        }
        
        if($pos == "Other GS" ||
           $pos == "Staff"){
            continue;
        }
        
        $username = str_replace("'", "", str_replace(" ", "", "{$firstName}.{$lastName}"));
        $realname = "{$firstName} {$lastName}";
        
        // Check for existing User
        $person = Person::newFromEmployeeId($emplid);
        if($person == null || $person->getId() == 0){
            $person = Person::newFromEmail($ccid."@ualberta.ca");
        }
        if($person == null || $person->getId() == 0){
            $person = Person::newFromName($username);
        }
        if($person == null || $person->getId() == 0){
            $person = Person::newFromNameLike($realname);
        }
        
        // Create User
        if($person == null || $person->getId() == 0){
            $data = DBFunctions::execSQL("SELECT * 
                                          FROM mw_user
                                          WHERE employee_id = '{$emplid}'
                                          OR user_email = '$ccid@ualberta.ca'
                                          OR user_name = '{$username}'");
            if(count($data) == 0){
                echo "Need to create: {$firstName} {$lastName} <{$ccid}@ualberta.ca>\n";
                $user = User::createNew($username, array('real_name' => $realname, 
                                                         'password' => User::crypt(mt_rand()), 
                                                         'email' => $ccid."@ualberta.ca"));
                $row = DBFunctions::select(array('mw_user'),
                                           array('*'),
                                           array('user_id' => $user->getId()));
                Person::$userRows[$user->getId()] = $row[0];
                $person = Person::newFromId($user->getId());
            }
            else{
                echo "Exists, but probably deleted: {$firstName} {$lastName} <{$ccid}@ualberta.ca>\n";
                continue;
            }
        }
        else{
            echo "Already exists: {$firstName} {$lastName} <{$ccid}@ualberta.ca>\n";
        }
        
        if($person != null || $person->getId() != 0){
            DBFunctions::update('mw_user',
                                array('employee_id' => $emplid),
                                array('user_id' => $person->getId()));
                                
            if(count($person->getRoles(true)) == 0){
                $role = ($type == "Faculty") ? CI : HQP;
                DBFunctions::insert("grand_roles",
                                    array('user_id' => $person->getId(),
                                          'role' => $role,
                                          'start_date' => $startDate));
            }
            
            if(count($person->getUniversities()) == 0){
                $uni = "University of Alberta";
                $dept = (isset($deptCodes[$hrDeptId])) ? $deptCodes[$hrDeptId] : "Unknown";
                $pos = str_replace("Full Professor", "Professor", $pos);
                $pos = str_replace("Post-Doctoral Fellows", "Post-Doctoral Fellow", $pos);
                $pos = str_replace("Masters", "Graduate Student - Master's", $pos);
                $pos = str_replace("PhD", "Graduate Student - Doctoral", $pos);
                $api = new PersonUniversitiesAPI();
                $api->params['id'] = $person->getId();
                $_POST['university'] = $uni;
                $_POST['department'] = $dept;
                $_POST['position'] = $pos;
                $_POST['startDate'] = $startDate;
                $api->doPOST();
            }
            
        }
    }
    
?>
