<?php

    require_once('commandLine.inc');
    $wgHooks['AddNewAccount'] = array();

    global $wgUser;
    $wgUser = User::newFromId(1);

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
        $ccid = $csv[2];
        $lastName = $csv[3];
        $firstName = $csv[4];
        $academicDept = $csv[5];
        $academicDeptCode = $csv[6];
        $hrDeptId = $csv[7];
        $hrDept = $csv[8];
        
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
                                          'start_date' => COL('CURRENT_TIMESTAMP')));
            }
            /*
            if(count($person->getUniversities()) == 0){
                $uni = "University of Alberta";
                $dept = (isset($deptCodes[$hrDeptId])) ? $deptCodes[$hrDeptId] : "Unknown";
                switch($type){
                    case "Faculty":
                        $pos = "Faculty";
                        break;
                    case "Undergraudate":
                    case "Undergraduate":
                        $pos = "Undergraduate";
                        break;
                    case "Post-Doctoral Fellows":
                        $pos = "Post-Doctoral Fellow";
                        break;
                    case "Graduate":
                        $pos = "Graduate Student";
                        break;
                    case "Staff";
                        $pos = "Staff";
                        break;
                    default:
                        $pos = "Unknown";
                        break;
                }
                $api = new PersonUniversitiesAPI();
                $api->params['id'] = $person->getId();
                $_POST['university'] = $uni;
                $_POST['department'] = $dept;
                $_POST['position'] = $pos;
                $_POST['startDate'] = date('Y-m-d');
                $api->doPOST();
            }
            */
        }
    }
    
?>
