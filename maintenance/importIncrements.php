<?php

    require_once('commandLine.inc');
    global $wgUser;
    
    $increments = explode("\n", file_get_contents("Increments.csv"));
    
    foreach($increments as $increment){
        $cells = str_getcsv($increment);
        if(count($cells) > 0){
            $person = Person::newFromEmployeeId($cells[0]);
            if($person->getId() != 0){
                echo $person->getName()."\n";
                
                $incs = array();
                $reas = array();
                
                $incs[2012] = $cells[1];
                $reas[2012] = $cells[2];
                
                $incs[2013] = $cells[3];
                $reas[2013] = $cells[4];
                
                $incs[2014] = $cells[5];
                $reas[2014] = $cells[6];
                
                $incs[2015] = $cells[7];
                $reas[2015] = $cells[8];
                
                $incs[2016] = $cells[9];
                $reas[2016] = $cells[10];
                
                $incs[2017] = $cells[11];
                $reas[2017] = $cells[12];
                
                foreach($incs as $year => $inc){
                    $inc = @number_format($inc, 2);
                    if(($inc == "0" || $inc == "0.00") && 
                       (strtolower($reas[$year]) == "a" ||
                        strtolower($reas[$year]) == "b" ||
                        strtolower($reas[$year]) == "c" ||
                        strtolower($reas[$year]) == "d")){
                        $inc .= strtolower($reas[$year]);
                    }
                    else if(trim($reas[$year]) != ""){
                        $inc .= " ({$reas[$year]})";
                    }
                    DBFunctions::update("grand_user_salaries",
                                        array('increment' => $inc),
                                        array('user_id' => $person->getId(),
                                              'year' => $year));
                }
            }
        }
    }
    
?>
