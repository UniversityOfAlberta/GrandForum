<?php

    // necessary code for commandline use
    require_once('commandLine.inc');
    global $wgUser;
    
    // Grabs excel style date for given term (Number of days since Jan 1, 1900, use https://www.timeanddate.com/date/durationresult.html
    function getStartEndDate($term){
    
        $startEndDate = array("start" => 0, "end" => 0);
    
        if ($term == "Fall2011"){ $startEndDate["start"] = 40791; $startEndDate["end"]  = 40882;}        //Fall2011:   Sep 7, 2011 - Dec 7, 2011
        else if ($term == "Winter2012"){ $startEndDate["start"] = 40915; $startEndDate["end"]  = 41010;} //Winter2012: Jan 9, 2012 - Apr 13, 2012
        else if ($term == "Spring2012"){ $startEndDate["start"] = 41034; $startEndDate["end"]  = 41071;} //Spring2012: May 7, 2012 - Jun 13, 2012
        else if ($term == "Summer2012"){ $startEndDate["start"] = 41097; $startEndDate["end"]  = 41134;} //Summer2012: Jul 9, 2012 - Aug 15, 2012

        else if ($term == "Fall2012"){ $startEndDate["start"] = 41155; $startEndDate["end"]  = 41246;}   //Fall2012:   Sep 5, 2012 - Dec 5, 2012
        else if ($term == "Winter2013"){ $startEndDate["start"] = 41279; $startEndDate["end"]  = 41374;} //Winter2013: Jan 7, 2013 - Apr 12, 2013
        else if ($term == "Spring2013"){ $startEndDate["start"] = 41398; $startEndDate["end"]  = 41435;} //Spring2013: May 6, 2013 - Jun 12, 2013
        else if ($term == "Summer2013"){ $startEndDate["start"] = 41461; $startEndDate["end"]  = 41498;} //Summer2013: Jul 8, 2013 - Aug 14, 2013        
        
        else if ($term == "Fall2013"){ $startEndDate["start"] = 41519; $startEndDate["end"]  = 41610;}   //Fall2013:   Sep 4, 2013 - Dec 4, 2013
        else if ($term == "Winter2014"){ $startEndDate["start"] = 41643; $startEndDate["end"]  = 41736;} //Winter2014: Jan 6, 2014 - Apr 9, 2014
        else if ($term == "Spring2014"){ $startEndDate["start"] = 41761; $startEndDate["end"]  = 41799;} //Spring2014: May 5, 2014 - Jun 11, 2014
        else if ($term == "Summer2014"){ $startEndDate["start"] = 41825; $startEndDate["end"]  = 41862;} //Summer2014: Jul 7, 2014 - Aug 13, 2014      

        else if ($term == "Fall2014"){ $startEndDate["start"] = 41883; $startEndDate["end"]  = 41974;}   //Fall2014:   Sep 3, 2014 - Dec 3, 2014
        else if ($term == "Winter2015"){ $startEndDate["start"] = 42007; $startEndDate["end"]  = 42102;} //Winter2015: Jan 5, 2015 - Apr 10, 2015
        else if ($term == "Spring2015"){ $startEndDate["start"] = 42126; $startEndDate["end"]  = 42163;} //Spring2015: May 4, 2015 - Jun 10, 2015
        else if ($term == "Summer2015"){ $startEndDate["start"] = 42189; $startEndDate["end"]  = 42226;} //Summer2015: Jul 6, 2015 - Aug 12, 2015

        else if ($term == "Fall2015"){ $startEndDate["start"] = 42246; $startEndDate["end"]  = 42343;}   //Fall2015:   Sep 1, 2015 - Dec 7, 2015
        else if ($term == "Winter2016"){ $startEndDate["start"] = 42371; $startEndDate["end"]  = 42466;} //Winter2016: Jan 4, 2016 - Apr 8, 2016
        else if ($term == "Spring2016"){ $startEndDate["start"] = 42497; $startEndDate["end"]  = 42534;} //Spring2016: May 9, 2016 - Jun 15, 2016
        else if ($term == "Summer2016"){ $startEndDate["start"] = 42553; $startEndDate["end"]  = 42590;} //Summer2016: Jul 4, 2016 - Aug 10, 2016

        else if ($term == "Fall2016"){ $startEndDate["start"] = 42612; $startEndDate["end"]  = 42709;}   //Fall2016:   Sep 1, 2016 - Dec 7, 2016
        else if ($term == "Winter2017"){ $startEndDate["start"] = 42742; $startEndDate["end"]  = 42835;} //Winter2017: Jan 9, 2017 - Apr 12, 2017
        else if ($term == "Spring2017"){ $startEndDate["start"] = 42861; $startEndDate["end"]  = 42867;} //Spring2017: May 8, 2017 - Jun 14, 2017
        else if ($term == "Summer2017"){ $startEndDate["start"] = 42924; $startEndDate["end"]  = 42961;} //Summer2017: Jul 10, 2017 - Aug 16, 2017
        
        else if ($term == "Fall2017"){ $startEndDate["start"] = 42981; $startEndDate["end"]  = 43075;}   //Fall2017:   Sep 5, 2017 - Dec 8, 2017
        else if ($term == "Winter2018"){ $startEndDate["start"] = 43106; $startEndDate["end"]  = 43201;} //Winter2018: Jan 8, 2018 - Apr 13, 2018
        else if ($term == "Spring2018"){ $startEndDate["start"] = 43225; $startEndDate["end"]  = 43262;} //Spring2018: May 7, 2018 - Jun 13, 2018
        else if ($term == "Summer2018"){ $startEndDate["start"] = 43288; $startEndDate["end"]  = 43325;} //Summer2018: Jul 9, 2018 - Aug 15, 2018
                    
        return $startEndDate; 
    }
    
    //Start Timer
    $start = microtime(true);
    
    // clean DB
    //DBFunctions::execSQL("DELETE FROM grand_courses WHERE id > 6056", true);
    //DBFunctions::execSQL("DELETE FROM grand_user_courses WHERE id > 6056", true);  
    
    $dataDir = "csv/";
    $courseDescrFile = "allCoursesDescription.csv";
    //$dataDir = dirname(__FILE__).'/csv_test/'; // if csv in maintanence
    
    $dir = new DirectoryIterator($dataDir);
    
    // Generate a map of course title and description under key of subject+catalog
    $filename = $dataDir . $courseDescrFile;
    $descrMap = array_map("str_getcsv", file($filename)); 
    $map = array(); // map to be used like $map = array(key => array(('title' => $title, 'descr' => $descr)))
    
    foreach($descrMap as $rowIndex => $rowValues){
        $key = trim($rowValues[0]) . trim($rowValues[1]);
        $title = trim($rowValues[2]);
        $descr = trim($rowValues[3]);
        //echo $key . " " . $title . " " . $descr . "\n";
        $map[$key] = array('title' => $title, 'descr' => $descr);
    }

    
    $auto_increment = DBFunctions::execSQL("SELECT `AUTO_INCREMENT`
                                            FROM  INFORMATION_SCHEMA.TABLES
                                            WHERE TABLE_SCHEMA = '{$config->getValue('dbName')}'
                                            AND   TABLE_NAME   = 'grand_courses'");
    $courseID = $auto_increment[0]['AUTO_INCREMENT'] - 1; // needs to be out of the whole loop.
    foreach($dir as $file) {
    
        $filename = $file->getFilename();
        
        if ($dir->isDot()){ continue;} // skip "." and ".." directories.
        if ($filename == $courseDescrFile){ continue; } // skip coursesDescr file
        
        $termString = rtrim($filename, ".csv"); // filename is the term (ex. Fall2011)
        $date = getStartEndDate($termString);
        $array = array_map("str_getcsv", file($dataDir.$file));
        
        $grandUserCourses = array();
        $grandCourses = array();
        
        
        $grandCoursesData = array();
        
        $rowsParsed = 0;
        foreach ($array as $rowIndex => $rowValues){
            if ($rowIndex > 0) {
                
                $rowsParsed++;

                $acadOrg = DBFunctions::escape(trim($rowValues[1]));
                $term = DBFunctions::escape(trim($rowValues[0]));
                $role = DBFunctions::escape(trim($rowValues[3]));
                $classNbr = DBFunctions::escape(trim(ltrim($rowValues[11], '0')));
                $subject = DBFunctions::escape(trim($rowValues[9]));
                $catalog = DBFunctions::escape(trim($rowValues[10]));
                $component = DBFunctions::escape(trim($rowValues[12]));
                $sect = DBFunctions::escape(trim($rowValues[8]));
                $crsStatus = DBFunctions::escape(trim($rowValues[13]));
                $facilID = DBFunctions::escape(trim(ltrim($rowValues[15], '0')));
                $startDate = DBFunctions::escape(trim($date["start"]));
                $endDate = DBFunctions::escape(trim($date["end"]));
                $hrsFrom = DBFunctions::escape(trim($rowValues[16]));
                $hrsTo = DBFunctions::escape(trim($rowValues[17]));
                $totEnrl = DBFunctions::escape(trim($rowValues[14]));
                $campus = DBFunctions::escape(trim($rowValues[19]));
                $note = DBFunctions::escape(trim($rowValues[18]));   
                $employeeID = DBFunctions::escape(trim(ltrim($rowValues[2], '0')));
                $userID = Person::newFromEmployeeId($employeeID)->getId();
                
 
                // key: Term + Class Nbr + Component + Sect + Employee Id
                $key = $term . $classNbr . $component . $sect . $employeeID;
                
                // skip if key exists OR user is not a Faculty of Science Member
                if (isset($grandCourses[$key]) || $userID == 0 || ($role != "PI" && $role != "CO")){
                    continue;
                }

                // set course title and description
                $mapKey = $subject . $catalog;
                $title = @DBFunctions::escape($map[$mapKey]['title']); // some courses may not exist in the map
                $descr = @DBFunctions::escape($map[$mapKey]['descr']); // @ gets rid of warnings
                
                
                //if ($userID == 337){ echo "yoooooooooo" . $subject . " " . $catalog . "\n"; }
                
                if($userID == 410){
                    $courseID++; // # of total insertions
                                                   
                    // set the key to values string
                    
                    $grandCourses[$key] = "('{$acadOrg}','{$term}','{$termString}',
                                            '{$classNbr}','{$subject}','{$catalog}',
                                            '{$component}','{$sect}','{$crsStatus}',
                                            '{$facilID}','{$startDate}','{$endDate}',
                                            '{$hrsFrom}','{$hrsTo}','{$totEnrl}',
                                            '{$campus}','{$note}', '{$title}', '{$descr}')";
                    
                   // set grandUserCourses         
                   $grandUserCourses[] = "('{$userID}','{$courseID}')";
               }
            }
        }
        
        if(count($grandUserCourses) > 0 && count($grandCourses) > 0){
            $insertSQLGC = "INSERT INTO `grand_courses` 
                                   (`Acad Org`, `Term`, `term_string`,
                                    `Class Nbr`, `Subject`, `Catalog`, 
                                    `Component`, `Sect`, `Crs Status`,
                                    `Facil ID`, `Start Date`, `End Date`,
                                    `Hrs From`, `Hrs To`, `Tot Enrl`,
                                    `Campus`, `Note`, `Descr`, `Course Descr`) VALUES ";
            
            $insertSQLGUC = "INSERT INTO `grand_user_courses` (`user_id`, `course_id`) VALUES "; 
                   
            DBFunctions::execSQL($insertSQLGC . implode(", ", $grandCourses), true);
            DBFunctions::execSQL($insertSQLGUC . implode(", ", $grandUserCourses), true);
        }
               
        echo "Number of Rows parsed (not necessarily inserted) for " . $termString . " is " . $rowsParsed . "\n\n";    
    }
    
    //End Timer
    $time_elapsed_secs = microtime(true) - $start;
    
    echo "\n\n\nProcess Successfully Completed in " . $time_elapsed_secs . " seconds.\n\n\n";


//ROW: 0 Term , 1 Acad Group, 2 ID, 3 Role, 4 Access, 5 Name, 6 Last, 7 First Name, 
//     8 Section, 9 Subject, 10 Catalog, 11 CLass Nbr, 12 Component, 13 Class Stat
//     14 Tot Enrl, 15 Facil ID, 16 Mtg Start, 17 Mtg End, 18 Pat, 19 Campus

// http://php.net/manual/en/function.in-array.php
// https://stackoverflow.com/questions/779986/insert-multiple-rows-via-a-php-array-into-mysql
// Paper.php    => true : write  , rollback
// DBFunctions.php

?>

