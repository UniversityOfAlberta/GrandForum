<?php

    require_once('commandLine.inc');
    global $wgUser;
    $wgUser = User::newFromId(1);

    $file = file_get_contents("salaries.csv");
    $lines = explode("\n", $file);
    DBFunctions::delete('grand_user_salaries',
                        array('year' => 2019));
    DBFunctions::delete('grand_user_salaries',
                        array('year' => 2020));
    foreach($lines as $line){
        $csv = str_getcsv($line);
        if(count($csv) <= 1){ continue; }
        $empId = $csv[3];
        $name = $csv[4];
        $salary2019 = $csv[11];
        $salary2020 = $csv[15];
        $inc = $csv[9];
        $reas = $csv[10];
        
        $inc = @number_format($inc, 2);
        if(($inc == "0" || $inc == "0.00") && 
           (strtoupper($reas) == "A" ||
            strtoupper($reas) == "B" ||
            strtoupper($reas) == "C" ||
            strtoupper($reas) == "D")){
            $inc = "0".strtoupper($reas);
        }
        else if(trim($reas) != ""){
            $inc .= " ({$reas})";
        }
        
        $person = Person::newFromEmployeeId($empId);
        DBFunctions::insert('grand_user_salaries',
                            array('user_id' => $person->getId(),
                                  'year' => 2019,
                                  'salary' => $salary2019,
                                  'increment' => $inc));
        DBFunctions::insert('grand_user_salaries',
                            array('user_id' => $person->getId(),
                                  'year' => 2020,
                                  'salary' => $salary2020));
        echo "{$name} ({$empId}): {$salary2019} --({$inc})--> {$salary2020}\n";
    }
    
?>
