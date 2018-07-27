<?php

    require_once('commandLine.inc');
    global $wgUser;
    $wgUser = User::newFromId(1);

    $file = file_get_contents("salaries.csv");
    $lines = explode("\n", $file);
    $year = 2017;
    DBFunctions::delete('grand_user_salaries',
                        array('year' => $year));
    foreach($lines as $line){
        $csv = str_getcsv($line);
        if(count($csv) <= 1){ continue; }
        $empId = $csv[0];
        $name = $csv[1];
        $salary = $csv[8];
        
        $person = Person::newFromEmployeeId($empId);
        DBFunctions::insert('grand_user_salaries',
                            array('user_id' => $person->getId(),
                                  'year' => $year,
                                  'salary' => $salary));
        echo "{$name} ({$empId}): {$salary}\n";
    }
