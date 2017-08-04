<?php
    require_once( "commandLine.inc" );
         $wgUser=User::newFromId("1");
	for($num=0;$num<=26;$num++){
	if(file_exists("award".$num.".csv")){
	print_r("checking file".$num);
        $lines = explode("\n", file_get_contents("award".$num.".csv"));
	$i = 0;
        foreach($lines as $line){
	   $cells = str_getcsv($line);
	   if($i>0 && count($cells)>1){
		$fullname = $cells[1];
		$name_array = explode(",", $fullname);
		$first_name = $name_array[1];
		$last_name = $name_array[0];
		$person = Person::newFromNameLike($first_name." ".$last_name);
		if($person->getId() != ""){
			$user_id = $person->getId();
			$cle = $cells[0];
			$department = str_replace("'", "''",$cells[2]);
			$organization = str_replace("'", "''",$cells[3]);
			$institution = str_replace("'", "''",$cells[4]);
			$province = str_replace("'", "''",$cells[5]);
			$country = str_replace("'", "''",$cells[7]);
			$fiscal_year = $cells[9].'-01-01 00:00:00';
			$competition_year = $cells[10].'-01-01 00:00:00';
			$amount = $cells[11];
			$program_id = $cells[12];
			$program_name = str_replace("'", "''",$cells[13]);
			$group = str_replace("'", "''",$cells[15]);
			$committee_code = $cells[17];
			$committee_name = str_replace("'", "''",$cells[18]);
			$area_of_application_code = $cells[20];
			$area_of_application_group = str_replace("'", "''",$cells[21]);	
			$area_of_application = str_replace("'", "''",$cells[23]);
			$research_subject_code = $cells[25];
			$research_subject_group = str_replace("'", "''",$cells[26]);
			$installment_date = strtotime($cells[30]."-".$cells[9]);
			$installment = date('Y-m-d', $installment_date);
                        if($cells[30] == ""){
				$installment = "0000-00-00 00:00:00";
			}
			$partie = $cells[31];
			if($cells[31] == ""){
			    $partie = 0;
			}
			$nb_partie = $cells[32];
                        if($cells[31] == ""){
                            $nb_partie = 0;
                        }
			$application_title = str_replace("'", "''",$cells[33]);
			$keyword = str_replace("'", "''",$cells[34]);
                        if($cells[34] == ""){
                            $keyword = '';
                        }
			$application_summary = str_replace("'", "''",$cells[35]);

                        $statement = "INSERT INTO `grand_new_grants`(`user_id`, `cle`, `department`, `organization`, `institution`, `province`, `country`, `fiscal_year`, `competition_year`, `amount`, `program_id`, `program_name`, `group`, `committee_code`, `committee_name`, `area_of_application_code`, `area_of_application_group`, `area_of_application`, `research_subject_code`, `research_subject_group`, `installment`, `partie`, `nb_partie`, `application_title`, `keyword`, `application_summary`) VALUES ($user_id, $cle, '$department', $organization, '$institution', '$province', '$country', '$fiscal_year', '$competition_year', $amount, '$program_id', '$program_name', '$group', $committee_code, '$committee_name', $area_of_application_code, '$area_of_application_group','$area_of_application', $research_subject_code, '$research_subject_group', '$installment', $partie, $nb_partie, '$application_title','$keyword', '$application_summary')";
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
