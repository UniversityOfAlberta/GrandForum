<?php

require_once('commandLine.inc');

$contents = explode("\n", file_get_contents("cat_applicants.csv"));
$wgUser = User::newFromId(1);

function saveBlobValue($blobSection, $blobItem, $person, $value){
    $year = 2018;
    $personId = $person->getId();
    $projectId = 0;
    
    $blb = new ReportBlob(BLOB_TEXT, $year, $personId, $projectId);
    $addr = ReportBlob::create_address("RP_CATALYST_APPLICATION", $blobSection, $blobItem, 0, $personId);
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
        $fileNumber = trim($csv[0]);
        $title = trim($csv[1]);
        $firstName = trim($csv[2]);
        $lastName = trim($csv[3]);
        $email = trim($csv[4]);
        $institution = trim($csv[5]);
        $phone = trim($csv[6]);
        
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
        }
        
        if(!$person->isSubRole("CAT2018Applicant")){
            DBFunctions::insert('grand_role_subtype',
                                array('user_id' => $person->getId(),
                                      'sub_role' => "CAT2018Applicant"));
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
