<?php

    // necessary code for commandline use
    require_once('commandLine.inc');
    global $wgUser;

    
    //Start Timer
    $start = microtime(true);
    
    // clean DB
    DBFunctions::execSQL("DELETE FROM grand_journals WHERE year = '2017'", true);
    
    $categoriesCSV = explode("\n", file_get_contents("journal_csv/Categories.csv"));
    $connectionsCSV = explode("\n", file_get_contents("journal_csv/Connections.csv"));
    $journalsCSV = explode("\n", file_get_contents("journal_csv/Journals.csv"));
    
    $categories = array();
    $journals = array();
    $connections = array();
    
    foreach($categoriesCSV as $category){
        $csv = str_getcsv($category);
        if(count($csv) <= 1) break;
        $categories[trim($csv[0])] = $csv;
        $categories[trim($csv[0])]['count'] = 0;
    }
    
    foreach($connectionsCSV as $connection){
        $csv = str_getcsv($connection);
        if(count($csv) <= 1) break;
        $issn = $csv[4];
        $connections[$issn][] = $csv;
    }
    
    $alreadyDone = array();
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
                $description = strtoupper(trim($conn[12]));
                if(isset($alreadyDone[$issn.$description])){
                    continue;
                }
                $numer = 0;
                $denom = 0;
                if(isset($categories[$description])){
                    $numer = trim(++$categories[$description]['count']);
                    $denom = trim($categories[$description][2]);
                }
                DBFunctions::insert('grand_journals',
                                array('year' => 2017,
                                      'short_title' => $short_title,
                                      'iso_abbrev' => $iso_abbrev,
                                      'title' => $title,
                                      'issn' => $issn,
                                      'description' => $description,
                                      'ranking_numerator' => $numer,
                                      'ranking_denominator' => $denom,
                                      'impact_factor' => trim($impact),
                                      'cited_half_life' => trim($half_life),
                                      'eigenfactor' => trim($eigenfactor)));
                $alreadyDone[$issn.$description] = true;
            }
        }
    }

?>

