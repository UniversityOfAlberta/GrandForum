<?php

    require_once('commandLine.inc');
    global $wgUser;
    $wgUser = User::newFromId(1);
    
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
    
    $hqps = DBFunctions::select(array('bddEfec2_development.graduates'),
                                array('*'));
    $iterationsSoFar = 0;
    
    $deptMap = array(
        "Chemistry" => "Chemistry",
        "Mathematical & Statistical Sci" => "Mathematical And Statistical Sciences",
        "Earth & Atmospheric Sciences" => "Earth And Atmospheric Sciences",
        "Biological Sciences" => "Biological Sciences",
        "Physics" => "Physics",
        "Psychology" => "Psychology",
        "Computing Science" => "Computing Science"
    );
    
    $titleMap = array(
        '' => "",
        'phd' => "Graduate Student - Doctoral",
        'bsc' => "Undergraduate",
        'msc' => "Graduate Student - Master's Thesis",
        'research/technical assistant' => "Research/Technical Assistant",
        'pdf' => "Post-Doctoral Fellow",
        'research associate' => "Research Associate",
        'honors thesis' => "Honors Thesis",
        'ma' => "Graduate Student - Master's Thesis",
        'high school' => "High School Student",
        'meng' => "Graduate Student - Master's Thesis",
        'Masters Course' => "Graduate Student - Master's Course",
        "Masters Thesis" => "Graduate Student - Master's Thesis",
        "Doctoral Program" => "Graduate Student - Doctoral"
    );
    
    $dupes = array();
    foreach($hqps as $key => $hqp){
        if(isset($dupes["{$hqp['FIRST_NAME']} {$hqp['LAST_NAME']}"]) && $dupes["{$hqp['FIRST_NAME']} {$hqp['LAST_NAME']}"] != $hqp['EMPLID']){
            $hqps[$key]['DUPE'] = true;
            $dupes["{$hqp['FIRST_NAME']} {$hqp['LAST_NAME']}"]['DUPE'] = true;
        }
        else{
            $dupes["{$hqp['FIRST_NAME']} {$hqp['LAST_NAME']}"] = &$hqp;
        }
    }
    
    foreach($hqps as $hqp){
        $person1 = Person::newFromEmployeeId($hqp['EMPLID']);
        $person2 = Person::newFromNameLike("{$hqp['FIRST_NAME']} {$hqp['LAST_NAME']}");
        
        $date = $hqp['DEGR_CONFER_DT'];
        if(strstr($date, "/") !== false){
            $dates = explode("/", $date);
            $date = "{$dates[2]}-{$dates[1]}-{$dates[0]} 00:00:00";
        }
        else {
            $date = "{$date} 00:00:00";
        }

        if(isset($hqp['DUPE'])){
            // Abiguous user: Do Nothing
        }
        else if($person1->getId() == 0 && $person2->getId() != 0 && $person2->getDepartment() == @$deptMap[$hqp['ACADPLNDESCR']]){
            // Person's Employee Id is not found, but names matched: Update Employee Id for this Person
            echo "UPDATE: {$person2->getName()}\n";
            $person2->employeeId = $hqp['EMPLID'];
            $person2->update();
        }
        else if($person1->getId() == 0 && $person2->getId() == 0){
            // Person Not found: Create them
            $username = str_replace("'", "",
                        str_replace(" ", "", "{$hqp['FIRST_NAME']}.{$hqp['LAST_NAME']}"));
            $realName = "{$hqp['FIRST_NAME']} {$hqp['LAST_NAME']}";
            $user = User::createNew($username, array('real_name' => $realName, 
                                                     'password' => User::crypt(mt_rand())));
            if($user == null){
                continue;
            }
            // Clear Cache
            Person::$cache = array();
            Person::$namesCache = array();
            Person::$aliasCache = array();
            Person::$idsCache = array();
            Person::$employeeIdsCache = array();
            
            DBFunctions::update('mw_user',
                                array('employee_id' => $hqp['EMPLID'],
                                      'first_name' => $hqp['FIRST_NAME'],
                                      'middle_name' => $hqp['MIDDLE_NAME'],
                                      'last_name' => $hqp['LAST_NAME']),
                                array('user_id' => EQ($user->getId())));
            $person = Person::newFromId($user->getId());
            $dept = (isset($deptMap[$hqp['ACADPLNDESCR']])) ? $deptMap[$hqp['ACADPLNDESCR']] : $hqp['ACADPLNDESCR'];
            
            addUserRole($person, HQP, "0000-00-00 00:00:00", $date);
            addUserUniversity($person, "University of Alberta", $dept, $titleMap[strtolower($hqp['DEGREE'])], "0000-00-00 00:00:00", $date);
            echo "CREATE: {$hqp['FIRST_NAME']}.{$hqp['LAST_NAME']}\n";
        }
        else {
            // Employee Id Matched: Do Nothing
            //echo "FOUND: {$person1->getName()}\n";
        }
        //show_status(++$iterationsSoFar, count($hqps));
    }
