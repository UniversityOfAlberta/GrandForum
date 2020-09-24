<?php

    require_once('commandLine.inc');
    global $wgUser;
    $wgUser = User::newFromId(1);

    $file = file_get_contents("cna.csv");
    $lines = explode("\n", $file);
    DBFunctions::delete('grand_cna',
                        array('year' => 2020));
    foreach($lines as $line){
        $csv = str_getcsv($line);
        if(count($csv) <= 1){ continue; }
        $empId = $csv[2];
        $name = $csv[3];
        $inc = number_format($csv[19], 2, '.', '');
        $reason = $csv[21];
        
        $person = Person::newFromEmployeeId($empId);
        if($person->getId() != 0 && $reason == "CNA"){
            DBFunctions::insert('grand_cna',
                                array('user_id' => $person->getId(),
                                      'year' => 2020,
                                      'increment' => $inc));
            echo "{$name} ({$empId}): {$inc}\n";
        }
        else{
            echo "Not Found: {$name} ({$empId})\n";
        }
    }
    
?>
