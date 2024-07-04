<?php

    // necessary code for commandline use
    require_once('commandLine.inc');
    global $wgUser;
    
    // clean DB
    DBFunctions::execSQL("DELETE FROM grand_journals WHERE year = '2023'", true);
    
    // https://jcr.clarivate.com/JCRLandingPageAction.action
    // https://jcr2.clarivate.com/JCRLandingPageAction.action
    $categoriesCSV = explode("\n", file_get_contents("journal_csv/Categories.csv"));
    // https://jcr.clarivate.com/JCRLandingPageAction.action
    // https://jcr2.clarivate.com/JCRLandingPageAction.action
    //  - Indicators: 
    //      - JCR Abbreviated Title
    //      - Journal Impact Factor
    //      - Cited Half-Life
    //      - Eigenfactor Score
    //      - ISSN
    $journalsCSV = explode("\n", file_get_contents("journal_csv/Journals.csv"));
    
    $categories = array();
    $journals = array();
    
    foreach($categoriesCSV as $category){
        $csv = str_getcsv($category);
        if(count($csv) <= 1) break;
        if(!isset($categories[trim($csv[0])])){
            $categories[trim($csv[0]).trim($csv[1])] = $csv;
            $categories[trim($csv[0]).trim($csv[1])]['count'] = 0;
        }
    }
    
    $alreadyDone = array();
    $toBeInserted = array();
    foreach($journalsCSV as $journal){
        $csv = str_getcsv($journal);
        if(count($csv) <= 1) break;
        $title = $csv[0];
        $issn = $csv[2];
        $eissn = $csv[3];

        $short_title = $csv[1];
        $iso_abbrev = $csv[1];
        $title = $csv[0];
        $impact = $csv[6];
        if($impact == "N/A"){
            continue;
        }
        $half_life = trim(ltrim($csv[7], '>'));
        $eigenfactor = $csv[8];
        $conns = explode(";", $csv[4]);
        foreach($conns as $conn){
            $category = strtoupper(trim($conn));
            if(isset($alreadyDone[$issn.$iso_abbrev.$category])){
                continue;
            }
            $numer = 0;
            $denom = 0;
            if(isset($categories[$category])){
                $numer = trim(++$categories[$category]['count']);
            }
            else{
                $categories[$category]['count'] = 0;
                $numer = trim(++$categories[$category]['count']);
            }

            $toBeInserted[$category][] = 
                array('year' => 2023,
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

