<?php

require_once('commandLine.inc');

$wgUser = User::newFromId(1);

function saveBlobValue($blobSection, $blobItem, $person, $value, $blobType=BLOB_TEXT){
    $year = 2017;
    $personId = $person->getId();
    $projectId = 0;
    
    $blb = new ReportBlob(BLOB_TEXT, $year, $personId, $projectId);
    $addr = ReportBlob::create_address("RP_IFP_APPLICATION", $blobSection, $blobItem, 0, $personId);
    $blb->store($value, $addr);
}

$people = Person::getAllEvaluates('IFP-ETC', 2017);

foreach($people as $person){
    if(file_exists("docs/{$person->getLastName()}.zip")){
        $contents = file_get_contents("docs/{$person->getLastName()}.zip");
        $magic = MediaWiki\MediaWikiServices::getInstance()->getMimeAnalyzer();
        $name = "{$person->getLastName()}.zip";
        $size = strlen($contents);
        $mime = $magic->guessMimeType("docs/{$person->getLastName()}.zip", false);
        $contents = base64_encode($contents);
        $hash = md5($contents);
        $data = array('name' => $name,
                      'type' => $mime,
                      'size' => $size,
                      'hash' => $hash,
                      'file' => $contents);
        saveBlobValue("DOCS", "DOCS", $person, json_encode($data), BLOB_RAW);
    }
    else{
        echo "{$person->getName()} Not Found!\n";
    }
}

?>
