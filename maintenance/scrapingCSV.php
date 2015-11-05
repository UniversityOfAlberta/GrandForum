<?php
    require_once( "commandLine.inc" );
    require_once( "scrapeCSV.php" );




    if(file_exists("Eleni.csv")){
	$scraper = new Scraper;	
	print_r("Reading in data");
        $lines = file_get_contents("Eleni.csv");
	$scraper->setCsvData($lines);
	print_r($scraper->csvCourses);
	//print_r($formattedArray);
      //print_r($Endarray);

    }
    else{
	print_r("error reading file");
    }

?>
