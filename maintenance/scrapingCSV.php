<?php
    require_once( "commandLine.inc" );
    require_once( "scrapeCSV.php" );




    if(file_exists("Eleni.csv")){
	$scraper = new Scraper;	
	print_r("Reading in data");
        $lines = file_get_contents("Eleni.csv");
        $lines = str_replace("\n","     ", $lines);
        $regex = "/(.+?) ,,,,,,,,,,,,,,,,,,,,,,,,,/";
	preg_match_all($regex, $lines, $array);
	$sectioned = $array[1];
	$sections = array();
     	foreach($sectioned as $section){
	    $p = explode("     ", $section);
	    $sections[] = $p;
	}
	$Endarray = array();
    	foreach($sections as $section){
	    $array = array();
	    $header = "";
	    for($i=0;$i<count($section);$i++){
	        if($section[$i] == ""){
		    continue;
		}
		else{
		    $row = $scraper->deleteCsvTrailingCommas($section[$i]);
		}
		$array[] = $row;
	    }
	    $Endarray[] = $array;
	}
	$formattedArray = array();
	
	foreach($Endarray as $info){
	    $header = "";
	    $key = "";
	    $array = array();
	    for($i=0;$i<count($info);$i++){
	        if($i == 0){
		    $key = $info[$i];
		    continue;
		}
		else if($i == 1){
		    $header = str_getcsv($info[$i]);
		    continue;
		}
		else{
		    $row = array();
		     //having to change some keys to match ImportBibTex
		    if($key == 'publications'){
 		        $xrow = str_getcsv($info[$i]);
                        for($x=0;$x<count($xrow); $x++){
                            switch ($header[$x]){
				case 'refereed':
			            $row['peer_reviewed'] = $xrow[$x];
				     break;
				case 'editors':
				    $row['editor'] = $xrow[$x];
				    break;
				case 'type':
				    switch ($xrow[$x]){
					case 'Conference':
					    $xrow[$x] = 'conference';
					    break;
					case 'Journal':
					    $xrow[$x] = 'article';
					    break;
					case 'BookChapter':
					    $xrow[$x] = 'inbook';
					    break;
					case 'Other':
					    $xrow[$x] = 'misc';
					    break;	
				    }
				    $row['bibtex_type'] = $xrow[$x];
				    break;
				case 'publication_date':
				    $dateArray = explode("-", $xrow[$x]);
				    $row['year'] = $dateArray[0];
				    $row['month'] = $dateArray[1];
				    $row['day'] = $dateArray[2];
				    break;
				case 'location':
				    $row['city'] = $xrow[$x];
			        default:
				    $row[$header[$x]] = $xrow[$x];
			    }
                        }
		    }
		    else if($key == 'footnotes'){
			$row = $info[$i];
		    }
		    else{
                        $xrow = str_getcsv($info[$i]);
		        for($x=0;$x<count($xrow); $x++){
			     $row[$header[$x]] = $xrow[$x];
			}
		    }
		}
		$array[] = $row;
	    }
	    $formattedArray[$key] = $array;
	}

	$addedPubs = array();
	$newPubs = array();
    	$publications = $formattedArray['publications'];
        for($i = 0; $i<count($publications); $i++){
	     $pub = $publications[$i];
	     if(in_array($pub['title'], $addedPubs)){
		continue;
	     }
	     $addedPubs[] = $pub['title'];
	     $pub['author'][] = $pub['author_name'];
	     for($n = $i+1; $n<count($publications); $n++){
		$pub2 = $publications[$n];
		if($pub['title'] == $pub2['title']){
		    $pub['author'][] = $pub2['author_name'];
		}
	     }
	     $pub['author'] = implode(",",$pub['author']);
	      //unsetting all the rows not needed for the ImportBibtex
	     unset($pub['author_name']);
	     unset($pub['ccid']);
	     unset($pub['department']);
	     unset($pub['author_type']);
	     $newPubs[] = $pub;
        }
	$formattedArray['publications'] = $newPubs;
	$scraper->setCsvData($formattedArray);
	print_r($scraper->csvPubs);
	//print_r($formattedArray);
      //print_r($Endarray);

    }
    else{
	print_r("error reading file");
    }

?>
