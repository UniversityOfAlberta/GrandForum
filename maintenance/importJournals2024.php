<?php

    // necessary code for commandline use
    require_once('commandLine.inc');
    global $wgUser;
    
    // clean DB
    DBFunctions::execSQL("DELETE FROM grand_journals WHERE year = '2024'", true);
    
    // https://guides.library.ualberta.ca/az/incites (https://incites.clarivate.com)
    //
    // - Indicators: 
    //      - Publication Source Name
	//      - ISSN
	//      - eISSN
	//      - Journal Impact Factor
	//      - Eigenfactor
	//      - Cited Half Life
	//      - WoS Categories
	//      - JCI Rank

    $journalsCSV = explode("\n", file_get_contents("journal_csv/Journals.csv"));
    
    $categories = array();
    $journals = array();
    
    $alreadyDone = array();
    $toBeInserted = array();
    foreach($journalsCSV as $journal){
        $csv = str_getcsv($journal);
        if(count($csv) <= 1) break;
        $title = $csv[0];
        $issn = str_replace("n/a", "", $csv[1]);
        $eissn = str_replace("n/a", "", $csv[2]);

        $short_title = $csv[0];
        $iso_abbrev = $short_title;
        $title = $csv[0];
        $impact = $csv[3];
        $half_life = trim(ltrim($csv[5], '>'));
        $eigenfactor = $csv[4];
        $conns = explode(";", $csv[6]);
        
        if($impact == "N/A" || $title == "Name" || $csv[6] == ""){
            continue;
        }
        
        foreach($conns as $conn){
            $category = strtoupper(trim($conn));
            if(isset($alreadyDone[$issn.$iso_abbrev.$category])){
                continue;
            }
            $numer = 0;
            if(!isset($categories[$category])){
                $categories[$category] = array();
                $categories[$category]['count'] = 0;
                $categories[$category]['actual'] = 0;
                $categories[$category]['last'] = 100000;
            }
            $categories[$category]['actual']++;
            if($categories[$category]['last'] > $impact){
                $categories[$category]['count'] = $categories[$category]['actual'];
            }
            $numer = $categories[$category]['count'];
            $categories[$category]['last'] = $impact;

            $toBeInserted[$category][] = 
                array('year' => 2024,
                      'short_title' => $short_title,
                      'iso_abbrev' => $iso_abbrev,
                      'title' => $title,
                      'issn' => $issn,
                      'eissn' => $eissn,
                      'description' => $category,
                      'ranking_numerator' => $numer,
                      'impact_factor' => trim($impact),
                      'cited_half_life' => trim($half_life),
                      'eigenfactor' => trim($eigenfactor));
            
            $alreadyDone[$issn.$iso_abbrev.$category] = true;
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

