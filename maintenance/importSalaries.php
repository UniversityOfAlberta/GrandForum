<?php

    require_once('commandLine.inc');
    global $wgUser;
    $wgUser = User::newFromId(1);

    $file = file_get_contents("salaries.csv");
    $lines = explode("\n", $file);
    $year = YEAR;
    DBFunctions::delete('grand_user_salaries',
                        array('year' => $year));
    foreach($lines as $line){
        $csv = str_getcsv($line);
        if(count($csv) <= 1){ continue; }
        $empId = sprintf("%07d", $csv[5]);
        $name = $csv[6];
        $salary = $csv[13];
        $inc = $csv[23];
        $reas = $csv[24];
        
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
        if($person->getId() == 0){
            echo "NOT FOUND: {$name}\n";
            continue;
        }
        DBFunctions::update('grand_user_salaries',
                            array('increment' => $inc),
                            array('user_id' => $person->getId(),
                                  'year' => $year-1));
        DBFunctions::insert('grand_user_salaries',
                            array('user_id' => $person->getId(),
                                  'year' => $year,
                                  'salary' => $salary));
        echo "{$name} ({$empId}): {$salary} : {$inc}\n";
    }
    
?>
