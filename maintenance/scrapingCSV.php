<?php
    require_once( "commandLine.inc" );
    require_once( "scrapeCSV.php" );




    if(file_exists("eleni.csv")){
	$scraper = new Scraper;	
	print_r("Reading in data");
        $lines = file_get_contents("eleni.csv");
	$scraper->setCsvData($lines);
	$additionals = $scraper->csvPresentations;
        print_r($additionals);
	/*foreach($additionals as $key=>$additional){
            switch($key){
                case 'leaves':
                    if($additional[0]['id'] == 'null'){
                        break;
                    }
                    $dateArray = explode("-", $additional[0]['created_at']);
                    $year = $dateArray[0];
                    $leaves_array = array();
                    $data = DBFunctions::select(array('grand_report_blobs'),
                                                    array('data'),
                                                    array('user_id' => 34,
                                                          'rp_type' => 'RP_FEC',
                                                          'rp_section' => 'FEC_INFORMATION',
                                                          'rp_item' => 'FEC_INFO_LEAVES',
                                                          'year' => $year));
		    $leaves =unserialize($data[0]['data']);
		    print_r($leaves);
		    break;
	     }
	}*/
	//print_r($data);
    }
    else{
	print_r("error reading file");
    }

?>
