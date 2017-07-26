<?php

require_once('commandLine.inc');

$contents = explode("\n", file_get_contents("applicants.csv"));
$wgUser = User::newFromId(1);

function saveBlobValue($blobSection, $blobItem, $person, $value){
    $year = 2017;
    $personId = $person->getId();
    $projectId = 0;
    
    $blb = new ReportBlob(BLOB_TEXT, $year, $personId, $projectId);
    $addr = ReportBlob::create_address("RP_KT_APPLICATION", $blobSection, $blobItem, 0, $personId);
    $blb->store($value, $addr);
}

function addUserUniversity($person, $uni, $dept, $pos){
    $_POST['university'] = $uni;
    $_POST['department'] = $dept;
    $_POST['position'] = $pos;
    $_POST['startDate'] = date('Y-m-d');
    $_POST['endDate'] = '0000-00-00 00:00:00';
    $api = new PersonUniversitiesAPI();
    $api->params['id'] = $person->getId();
    $api->doPOST();
}

foreach($contents as $line){
    $csv = str_getcsv($line);
    if(count($csv) == 7){
        $fileNumber = $csv[0];
        $title = $csv[1];
        $firstName = $csv[2];
        $lastName = $csv[3];
        $email = $csv[4];
        $institution = $csv[5];
        $phone = $csv[6];
        
        $username = str_replace(" ", "", $firstName).".".str_replace(" ", "", $lastName);
        $person = Person::newFromName($username);
        echo "Person: $username\n";
        if($person == null || $person->getId() == 0){
            // Person Doesn't exist yet
            $user = User::createNew($username, array('real_name' => "$firstName $lastName", 
                                                     'password' => User::crypt(mt_rand()), 
                                                     'email' => $email));
            Person::$cache = array();
            Person::$namesCache = array();
            $person = Person::newFromUser($user);
            
            DBFunctions::update('mw_user',
                                array('candidate' => 1),
                                array('user_id' => $person->getId()));
        }
        
        if(!$person->isRole(NI) && !$person->isRole(NI."-Candidate")){
            $role = new Role(array());
            $role->user = $person->getId();
            $role->role = NI;
            $role->create();
            
            DBFunctions::insert('grand_role_subtype',
                                array('user_id' => $person->getId(),
                                      'sub_role' => "KT2017Applicant"));
        }
        
        if(count($person->getUniversities()) == 0){
            addUserUniversity($person, trim($institution), "", "");
        }
        
        saveBlobValue("INFORMATION", "FILE_NUMBER", $person, $fileNumber);
        saveBlobValue("INFORMATION", "TITLE", $person, $title);
        saveBlobValue("INFORMATION", "FIRST_NAME", $person, $firstName);
        saveBlobValue("INFORMATION", "LAST_NAME", $person, $lastName);
        saveBlobValue("INFORMATION", "EMAIL", $person, $email);
        saveBlobValue("INFORMATION", "DAY_PHONE", $person, $phone);
        saveBlobValue("INFORMATION", "INSTITUTION", $person, $institution);
    }
}

?>
