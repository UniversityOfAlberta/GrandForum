<?php

    // necessary code for commandline use
    require_once('commandLine.inc');
    global $wgUser;
    
    $dir = dirname(__FILE__);
    
    // call CCVTK
    require_once($dir."/../Classes/CCCVTK/common-cv.lib.php");
    
    
    $dataDir = "ccvData/";
    $dirIt = new DirectoryIterator($dataDir);
    
    if ($dirIt->isDot()){ continue;} // skip "." and ".." directories.
    
    foreach($dirIt as $file){
        $filename = $file->getFilename();

        if ($filename == "." || $filename == ".."){ continue;}

        // echo $filename . "\n";

        $cv = new CommonCV($dir . "/ccvData/" . $filename);        

        $personalInfo = $cv->getPersonalInfo();
        // foreach($personalInfo as $key => $val){
        //     echo $key . "\t" . $val . "\n";
        // }

        $name = $personalInfo["first_name"] . " " . $personalInfo["last_name"];
        
        $person = Person::newFromName($name);
        $employment = $cv->getEmployment();

        print_r($employment);

        $status = UploadCCVAPI::updateEmployment($person, $employment);
        if ($status){ echo "employment update for " . $name . " was successful."; }
        else { echo "employment update for " . $name . " was unsuccessful."; }
    }


?>

