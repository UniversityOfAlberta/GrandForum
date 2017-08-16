<?php

    // necessary code for commandline use
    require_once('commandLine.inc');
    global $wgUser;
    $wgUser = User::newFromId(1);
    $dir = dirname(__FILE__);
    
    // call CCVTK
    require_once($dir."/../Classes/CCCVTK/common-cv.lib.php");
    
    $dataDir = "ccvData/";
    $dirIt = new DirectoryIterator($dataDir);
    
    foreach($dirIt as $file){
        $filename = $file->getFilename();

        if ($filename == "." || $filename == ".."){ continue;}

        $cv = new CommonCV($dir . "/ccvData/" . $filename);        

        $personalInfo = $cv->getPersonalInfo();

        $name = $personalInfo["first_name"] . " " . $personalInfo["last_name"];
        
        $person = Person::newFromName($name);
        $employment = $cv->getEmployment();

        $status = UploadCCVAPI::updateEmployment($person, $employment);
        
        //if ($status){ echo "employment update for " . $name . " was successful.\n"; }
        //else { echo "employment update for " . $name . " was unsuccessful.\n"; }
    }


?>

