<?php

require_once('commandLine.inc');

$contents = explode("\n", file_get_contents("show_applicants.csv"));
$wgUser = User::newFromId(1);

function saveBlobValue($blobSection, $blobItem, $person, $value){
    $year = 2018;
    $personId = $person->getId();
    $projectId = 0;
    
    $blb = new ReportBlob(BLOB_TEXT, $year, $personId, $projectId);
    $addr = ReportBlob::create_address("RP_SHOW_APPLICATION", $blobSection, $blobItem, 0, $personId);
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
    if(count($csv) == 6){
        $fileNumber = $csv[0];
        $title = $csv[1];
        $firstName = $csv[2];
        $lastName = $csv[3];
        $email = $csv[4];
        $institution = $csv[5];
        
        
        $username = str_replace(" ", "", $firstName).".".str_replace(" ", "", $lastName);
        $person = Person::newFromName($username);
        if($person == null || $person->getId() == 0){
            $person = Person::newFromEmail($email);
        }
        
        $pdf = file_get_contents("SHOW_PDF/{$fileNumber}.pdf");
        $hdata = sha1("");
        $hpdf = sha1($pdf);
        
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
        
        if(count($person->getUniversities()) == 0){
            addUserUniversity($person, trim($institution), "", "");
        }
        
        saveBlobValue("INFORMATION", "FILE_NUMBER", $person, $fileNumber);
        saveBlobValue("INFORMATION", "TITLE", $person, $title);
        saveBlobValue("INFORMATION", "FIRST_NAME", $person, $firstName);
        saveBlobValue("INFORMATION", "LAST_NAME", $person, $lastName);
        saveBlobValue("INFORMATION", "EMAIL", $person, $email);
        saveBlobValue("INFORMATION", "INSTITUTION", $person, $institution);
        
        $tok = md5($person->getId() . $person->getName() . time() . $hdata . $hpdf);
        
        DBFunctions::insert('grand_pdf_report',
                            array('user_id' => $person->getId(),
                                  'generation_user_id' => $person->getId(),
                                  'submission_user_id' => $person->getId(),
                                  'year' => 2018,
                                  'type' => 'RPTP_SHOW_APPLICATION',
                                  'submitted' => 1,
                                  'token' => $tok,
                                  'hash_data' => $hdata,
                                  'hash_pdf' => $hpdf,
                                  'pdf' => $pdf));
    }
}

?>
