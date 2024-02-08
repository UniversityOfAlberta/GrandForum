<?php

require_once('commandLine.inc');
global $wgUser;

$wgUser = User::newFromId(1);

function getBlobValue($rp, $section, $blobId, $subId, $personId, $projectId, $type=BLOB_TEXT, $year=YEAR){
    $addr = ReportBlob::create_address($rp, $section, $blobId, $subId);
    $blb = new ReportBlob($type, $year, $personId, $projectId);
    $result = $blb->load($addr);
    if($type == BLOB_TEXT){
        return nl2br($blb->getData());
    }
    return $blb->getData();
}

function saveBlobValue($value, $rp, $section, $blobId, $subId, $personId, $projectId, $type=BLOB_TEXT, $year=YEAR){
    $addr = ReportBlob::create_address($rp, $section, $blobId, $subId);
    $blb = new ReportBlob($type, $year, $personId, $projectId);
    $blb->store(trim($value), $addr);
    return $blb->getData();
}

$lines = explode("\n", file_get_contents("variance.csv"));

foreach($lines as $line){
    $csv = str_getcsv($line);
    if(count($csv) > 1){
        $email = $csv[2];
        $from = $csv[0];
        $role = $csv[4];
        $teaching = $csv[7];
        $research = $csv[10];
        $service = $csv[11];
        $admin = $csv[12];
        $start = $csv[14];
        $end = $csv[15];
        $person = Person::newFromEmail($email);
        
        if($person->getId() != 0){
            saveBlobValue($from, "RP_LETTER5", "TABLE", "FROM", $person->getId(), 1, 0);
            saveBlobValue($role, "RP_LETTER5", "TABLE", "ROLE", $person->getId(), 1, 0);
            saveBlobValue($teaching, "RP_LETTER5", "TABLE", "TEACHING", $person->getId(), 1, 0);
            saveBlobValue($research, "RP_LETTER5", "TABLE", "RESEARCH", $person->getId(), 1, 0);
            saveBlobValue($service, "RP_LETTER5", "TABLE", "SERVICE", $person->getId(), 1, 0);
            saveBlobValue($admin, "RP_LETTER5", "TABLE", "ADMIN", $person->getId(), 1, 0);
            saveBlobValue($start, "RP_LETTER5", "TABLE", "START_DATE", $person->getId(), 1, 0);
            saveBlobValue($end, "RP_LETTER5", "TABLE", "END_DATE", $person->getId(), 1, 0);
            
            echo "{$person->getName()}\n";
        }
    }
}
   
?>
