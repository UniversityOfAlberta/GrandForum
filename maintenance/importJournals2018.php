<?php

    // necessary code for commandline use
    require_once('commandLine.inc');
    global $wgUser;

    
    //Start Timer
    $start = microtime(true);
    
    // clean DB
    DBFunctions::execSQL("DELETE FROM grand_journals WHERE year = '2018'", true);
    
    $categoriesCSV = explode("\n", file_get_contents("journal_csv/Categories.csv"));
    $connectionsCSV = explode("\n", file_get_contents("journal_csv/Connections.csv"));
    $journalsCSV = explode("\n", file_get_contents("journal_csv/Journals.csv"));
    
    $categories = array();
    $journals = array();
    $connections = array();
    
    foreach($categoriesCSV as $category){
        $csv = str_getcsv($category);
        if(count($csv) <= 1) break;
        if(!isset($categories[trim($csv[0])])){
            $categories[trim($csv[0]).trim($csv[1])] = $csv;
            $categories[trim($csv[0]).trim($csv[1])]['count'] = 0;
        }
    }
    
    foreach($connectionsCSV as $connection){
        $csv = str_getcsv($connection);
        if(count($csv) <= 1) continue;
        $issn = $csv[2];
        $connections[$issn][] = $csv;
    }
    
    $alreadyDone = array();
    $toBeInserted = array();
    foreach($journalsCSV as $journal){
        $csv = str_getcsv($journal);
        if(count($csv) <= 1) break;
        $issn = $csv[2];
        if(isset($connections[$issn])){
            $short_title = $csv[1];
            $iso_abbrev = $csv[1];
            $title = $csv[0];
            $impact = $csv[3];
            $half_life = trim(ltrim($csv[4], '>'));
            $eigenfactor = $csv[5];
            foreach($connections[$issn] as $conn){
                $type = "";
                if(trim($conn[6]) == "Yes"){
                    $type = "SCIE";
                }
                if(trim($conn[7]) == "Yes"){
                    $type = "SSCI";
                }
                /*if(trim($conn[9]) == "Yes"){
                    $type = "AHCI";
                }
                if(trim($conn[10]) == "Yes"){
                    $type = "ESCI";
                }*/
                $eissn = $conn[3];
                $description = strtoupper(trim($conn[5]));
                if(isset($alreadyDone[$issn.$description.$type]) || $type == ""){
                    continue;
                }
                $numer = 0;
                $denom = 0;
                if(isset($categories[$description.$type])){
                    $numer = trim(++$categories[$description.$type]['count']);
                }
                else{
                    $categories[$description.$type]['count'] = 0;
                    $numer = trim(++$categories[$description.$type]['count']);
                }

                $toBeInserted[$description.$type][] = 
                    array('year' => 2018,
                          'short_title' => $short_title,
                          'iso_abbrev' => $iso_abbrev,
                          'title' => $title,
                          'issn' => $issn,
                          'eissn' => $eissn,
                          'description' => $description,
                          'ranking_numerator' => $numer,
                          'impact_factor' => trim($impact),
                          'cited_half_life' => trim($half_life),
                          'eigenfactor' => trim($eigenfactor));
                
                $alreadyDone[$issn.$description.$type] = true;
            }
        }
    }
    
    foreach($toBeInserted as $descriptions){
        $ranking_denominator = count($descriptions);
        foreach($descriptions as $desc){
            $desc['ranking_denominator'] = $ranking_denominator;
            DBFunctions::insert('grand_journals',
                                $desc);
        }
    }

?>

