<?php
    require_once( "commandLine.inc" );
         $wgUser=User::newFromId("1");
	for($num=0;$num<=26;$num++){
	if(file_exists("partner".$num.".csv")){
	print_r("checking file".$num);
        $lines = explode("\n", file_get_contents("partner".$num.".csv"));
	$i = 0;
        foreach($lines as $line){
	   $cells = str_getcsv($line);
	   if($i>0 && count($cells)>1){
		$cle = $cells[0];
                $searchStatement = "SELECT * FROM grand_new_grants WHERE cle = $cle";
                $datacheck = DBFunctions::execSQL($searchStatement);
		if(count($datacheck)>0){
			$part_organization_id = str_replace("'", "''",$cells[1]);
			$part_institution = str_replace("'", "''",$cells[2]);
			$province = str_replace("'", "''",$cells[3]);
			$country = str_replace("'", "''",$cells[5]);
			$fiscal_year = $cells[7].'-01-01 00:00:00';
			$org_type = str_replace("'", "''",$cells[8]);
			$committee_name = "";
                        $statement = "INSERT INTO `grand_new_grant_partner`(`cle`, `part_organization_id`, `part_institution`, `province`, `country`, `committee_name`, `fiscal_year`, `org_type`) VALUES ($cle, '$part_organization_id', '$part_institution','$province', '$country', '$committee_name', '$fiscal_year', '$org_type')";
			//print_r($statement."\n");
			$data = DBFunctions::execSQL($statement, true);
                    if(count($data)>0){
                        DBFunctions::commit();
                        print_r("grant added");
                     }

		}
                        /*$installment_date = strtotime($cells[30]."-".$cells[9]);
                        $installment = date('Y-m-d', $installment_date);
			print_r($installment);
			print_r("\n");*/
	  }
	   $i++;
	}
	}
	}
?>
