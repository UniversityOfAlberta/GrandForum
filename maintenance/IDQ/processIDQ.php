<?php

    require_once("../commandLine.inc");
    $wgUser = User::newFromId(1);
    
    $small  = array_slice(explode("\n", file_get_contents("science_".YEAR."_10to35.csv")), 17);
    $medium = array_slice(explode("\n", file_get_contents("science_".YEAR."_36to100.csv")), 17);
    $large  = array_slice(explode("\n", file_get_contents("science_".YEAR."_100plus.csv")), 17);
    
    $evals = array_merge($small, $medium, $large);
    
    $facultyPeople = new FacultyPeopleReportItemSet();
    $facultyPeople->attributes['start'] = (YEAR-1)."-07-01";
    $facultyPeople->attributes['end'] = (YEAR)."-07-01";
    
    $faculty = array();
    
    foreach($facultyPeople->getData() as $row){
        $person = Person::newFromId($row['person_id']);
        $person->extra = strip_tags($row['extra']);
        $faculty[$row['person_id']] = $person;
    }
    
    $dd = array();
    $fec = array();
    $nonfec = array();
    
    foreach($evals as $eval){
        $csv = str_getcsv($eval);
        if(count($csv) == 1 || trim($csv[6]) == "") continue;
        $person = Person::newFromEmployeeId($csv[2]);
        if($person->getName() == "" || !$person->isRoleDuring(NI, "1900-01-01", "2100-01-01")) continue;
        $isFEC = isset($faculty[$person->getId()]);
        $isDD = $person->isSubRole("DD");
        if($isFEC){
            $fac = $faculty[$person->getId()];
            if($isDD){
                // Dean's Decision
                $dd[$fac->extra.$csv[5]] = '"'.implode('","', array($fac->extra, utf8_encode($csv[3]), $csv[5], $csv[6], $csv[7], 
                                                 $csv[8], $csv[9], $csv[10], $csv[11], $csv[12], $csv[13], $csv[14], $csv[15], $csv[16], $csv[17])).'"';
            }
            else{
                // FEC
                $fec[$fac->extra.$csv[5]] = '"'.implode('","', array($fac->extra, utf8_encode($csv[3]), $csv[5], $csv[6], $csv[7], 
                                                  $csv[8], $csv[9], $csv[10], $csv[11], $csv[12], $csv[13], $csv[14], $csv[15], $csv[16], $csv[17])).'"';
            }
        }
        else{
            // Non-FEC
            $nonfec[] = '"'.implode('","', array("", utf8_encode($csv[3]), $csv[5], $csv[6], $csv[7], 
                                                 $csv[8], $csv[9], $csv[10], $csv[11], $csv[12], $csv[13], $csv[14], $csv[15], $csv[16], $csv[17])).'"';
        }
    }
    
    ksort($fec);
    ksort($dd);

    file_put_contents("fec.csv", implode("\n", $fec));
    file_put_contents("dd.csv", implode("\n", $dd));
    file_put_contents("nonfec.csv", implode("\n", $nonfec));

?>
