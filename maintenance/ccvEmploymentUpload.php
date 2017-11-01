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
    
    $iterationsSoFar = 0;
    $count = iterator_count($dirIt);
    $dirIt->rewind();
    foreach($dirIt as $file){
        $filename = $file->getFilename();

        if ($filename == "." || $filename == ".."){ continue;}

        $cv = new CommonCV($dir . "/ccvData/" . $filename);        

        $personalInfo = $cv->getPersonalInfo();

        $name = $personalInfo["first_name"] . " " . $personalInfo["last_name"];
        $person = Person::newFromName($name);
        if($person->getId() != 0){
            $degrees = $cv->getDegrees();
            $employment = $cv->getEmployment();
            $hqps = $cv->getStudentsSupervised();
            UploadCCVAPI::updateDegrees($person, $degrees);
            UploadCCVAPI::updateEmployment($person, $employment);
            UploadCCVAPI::updateHQPPresentPosition($person, $hqps);
            
            $person->university = false;
        }
        show_status(++$iterationsSoFar, $count-2);
    }


?>
