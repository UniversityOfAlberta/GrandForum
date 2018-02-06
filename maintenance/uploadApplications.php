<?php

require_once('commandLine.inc');

$wgUser = User::newFromId(1);

function uploadApplication($project, $rpType, $section, $item, $subItem){
    if(file_exists("docs/{$project->getName()}.zip")){
        $contents = file_get_contents("docs/{$project->getName()}.zip");
        $size = strlen($contents);
        $zip = base64_encode($contents);
        $hash = md5($zip);
        $mime = "application/zip";
        $name = "{$project->getName()}.zip";
        
        $value = array('name' => $name,
                       'type' => $mime,
                       'size' => $size,
                       'hash' => $hash,
                       'file' => $zip);
        
        $json = json_encode($value);
        
        $blob = new ReportBlob(BLOB_RAW, REPORTING_YEAR, 0, $project->getId());
        $blob_address = ReportBlob::create_address($rpType, $section, $item, $subItem);
        $blob->store(utf8_decode($json), $blob_address);
    }
    else{
        echo "File Not Found: {$project->getName()}\n";
    }
}

$projects = Project::getAllProjects();
foreach($projects as $project){
    if(strstr($project->getName(), "CAT2015") !== false){
        uploadApplication($project, 'RP_CAT', 'RP_CAT_REPORT', 'RP_CAT_REPORT_UPLOAD', 0);
    } 
    else if(strstr($project->getName(), "TG2015") !== false){
        uploadApplication($project, 'RP_TG', 'RP_TG_REPORT', 'RP_TG_REPORT_UPLOAD', 0);
    }
}


?>
