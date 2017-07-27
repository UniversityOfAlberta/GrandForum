<?php

    // necessary code for commandline use
    require_once('commandLine.inc');
    global $wgUser;

    
    //Start Timer
    $start = microtime(true);
    
    // clean DB
    DBFunctions::execSQL("TRUNCATE grand_journals", true);
    
    $dataDir = "journal_csv/";
    
    $dir = new DirectoryIterator($dataDir);
    
    foreach($dir as $file) {
    
        $filename = $file->getFilename();
        
        if ($dir->isDot()){ continue;} // skip "." and ".." directories.
        
        $year = substr(trim($filename), -8, 4); // get year (ex. ..._2011.csv)
        
        $array = array_map("str_getcsv", file($dataDir.$file));
        
        $grandJournals = array();
        
        // Title20,ISO_ABBREV,TITLE,ISSN,CATEGORY_DESCRIPTION,CATEGORY_RANKING,IMPACT_FACTOR,CITED_HALF_LIFE,EIGENFACTOR
        $rowsParsed = 0;
        foreach ($array as $rowIndex => $rowValues){
            if ($rowIndex > 0) { // skip first row
                
                $rowsParsed++;

                $short_title = DBFunctions::escape(trim($rowValues[0]));
                $iso_abbrev = DBFunctions::escape(trim($rowValues[1]));
                $title = DBFunctions::escape(trim($rowValues[2]));
                $issn = DBFunctions::escape(trim($rowValues[3]));
                $description = DBFunctions::escape(trim($rowValues[4]));
                $ranking = explode("/", DBFunctions::escape(trim($rowValues[5])));
                //echo $ranking[0] . " " . $ranking[1];
                $ranking_num = $ranking[0];
                $ranking_denom = $ranking[1];
                $impact = DBFunctions::escape(trim($rowValues[6]));
                $half_life = DBFunctions::escape(trim(ltrim($rowValues[7], '>')));
                $eigenfactor = DBFunctions::escape(trim($rowValues[8]));
                
 
                // key: year + title + category description
                $key = $year . $title . $description;
                
                // skip if key exists OR user is not a Faculty of Science Member
                if (isset($grandJournals[$key])){
                    continue;
                }
                        
                // set the key to values string
                $grandJournals[$key] = "('{$year}','{$short_title}','{$iso_abbrev}',
                                        '{$title}','{$issn}','{$description}',
                                        '{$ranking_num}','{$ranking_denom}',
                                        '{$impact}','{$half_life}','{$eigenfactor}')";
            }
        }
        
        $insertSQL = "INSERT INTO `grand_journals` 
                               (`year`, `short_title`, `iso_abbrev`,
                                `title`, `issn`, `description`, 
                                `ranking_numerator`, `ranking_denominator`,
                                `impact_factor`, `cited_half_life`,
                                `eigenfactor`) VALUES ";
               
        DBFunctions::execSQL($insertSQL . implode(", ", $grandJournals), true);

        echo "Number of Rows parsed (not necessarily inserted) for " . $file->getfilename() . " is " . $rowsParsed . "\n\n";    
    }
    
    //End Timer
    $time_elapsed_secs = microtime(true) - $start;
    
    echo "\n\n\nProcess Successfully Completed in " . $time_elapsed_secs . " seconds.\n\n\n";


?>

