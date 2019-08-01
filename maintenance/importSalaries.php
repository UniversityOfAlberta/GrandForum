<?php

    require_once('commandLine.inc');
    global $wgUser;
    $wgUser = User::newFromId(1);

    $file = file_get_contents("salaries.csv");
    $lines = explode("\n", $file);
    DBFunctions::delete('grand_user_salaries',
                        array('year' => 2018));
    DBFunctions::delete('grand_user_salaries',
                        array('year' => 2019));
    foreach($lines as $line){
        $csv = str_getcsv($line);
        if(count($csv) <= 1){ continue; }
        $empId = $csv[0];
        $name = $csv[1];
        $salary2018 = $csv[8];
        $salary2019 = $csv[12];
        
        $person = Person::newFromEmployeeId($empId);
        DBFunctions::insert('grand_user_salaries',
                            array('user_id' => $person->getId(),
                                  'year' => 2018,
                                  'salary' => $salary2018));
        DBFunctions::insert('grand_user_salaries',
                            array('user_id' => $person->getId(),
                                  'year' => 2019,
                                  'salary' => $salary2019));
        echo "{$name} ({$empId}): {$salary2018}, {$salary2019}\n";
    }
