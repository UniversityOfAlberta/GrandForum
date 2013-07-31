<?php

require_once('../commandLine.inc');
require_once($dir . '../../Classes/PHPExcel/IOFactory.php');

if(count($args) > 0){
    if($args[0] == "help"){
        showHelp();
        exit;
    }
}

exportPersonalData();

function exportPersonalData(){
	$sql = "SELECT sr.* FROM survey_results sr WHERE sr.submitted=1";
    $data = execSQLStatement($sql);
    $survey_nis = array();
    foreach($data as $row){
    	$user_id = $row['user_id'];
		$survey_nis[] = $user_id;
	}


    $cnis = Person::getAllPeopleDuring('CNI', "2012-01-01 00:00:0", "2013-03-10 23:59:59");
	$pnis = Person::getAllPeopleDuring('PNI', "2012-01-01 00:00:0", "2013-03-10 23:59:59");
	$nis = array_merge($cnis, $pnis);

	$sorted_nis = array();
	foreach($nis as $ni){
		$sorted_nis[$ni->getLastName()] = $ni;
	}
	ksort($sorted_nis);
	$sorted_nis = array_values($sorted_nis);

	$ni_names = array();
	foreach($sorted_nis as $ni){
		if(!in_array($ni->getName(), $ni_names) && !in_array($ni->getId(), $survey_nis)){
			$ni_names[$ni->getId()] = $ni->getName();
		}
		else if(in_array($ni->getId(), $survey_nis)){
			echo $ni->getName() ." --- Done Survey!\n";
		}
		else if (in_array($ni->getName(), $ni_names)){
			echo $ni->getName() ." --- Already in NI Array\n";
		}
	}
	//sort($ni_names);

    //EXCEL
    $phpExcel = new PHPExcel();
	$styleArray = array('font' => array('bold' => true));

	$foo = $phpExcel->getActiveSheet();
	
	//add column headers, set the title and make the text bold
	$foo
	->setCellValue("A1", "Name")
	->setCellValue("B1", "First Name")
	->setCellValue("C1", "Last Name")
	->setCellValue("D1", "Affiliation")
	->setCellValue("E1", "GRAND Role");


	$foo->setTitle("Personal Information");
	//->getStyle("A1:B1:C1:D1:E1:F1:G1:H1:J1:K1")->applyFromArray($styleArray);

	$row_count = 2;
    foreach($ni_names as $user_id => $user_name){

	    $person = Person::newFromId($user_id);

	    $role = "Other";
	    
	    if($person->isCNI()){
	    	$role="CNI";
	    }
	    else if($person->isPNI()){
	    	$role="PNI";
	    }

	    if($role == "Other"){
	    	continue;
	    }

	    $uni = $person->getUniversity();
	    $affil = "";
	    if(!empty($uni)){
	    	$affil = $uni['university'];
	    }
	    
	    $f_name = $row['first_name'];
	    $l_name = $row['last_name'];
	    $name = $f_name."_".$l_name;

	    $cname = explode('.', $user_name); 
        $cnamef = $cname[0];
        $cnamel = implode(' ', array_slice($cname, 1));
        $cname = $cnamel."_".$cnamef;

	    $foo
	    ->setCellValue("A{$row_count}", $cname)
	    ->setCellValue("B{$row_count}", $cnamef)
	    ->setCellValue("C{$row_count}", $cnamel)
	    ->setCellValue("D{$row_count}", $affil)
	    ->setCellValue("E{$row_count}", $role);

		$row_count++;
	}

	$phpExcel->setActiveSheetIndex(0);
	$objWriter = PHPExcel_IOFactory::createWriter($phpExcel, "Excel5");
	$objWriter->save('NonSurvey_personal.xls');
}


function execSQLStatement($sql, $update=false){
	if($update == false){
		$dbr = wfGetDB(DB_SLAVE);
	}
	else {
		$dbr = wfGetDB(DB_MASTER);
		return $dbr->query($sql);
	}
	$result = $dbr->query($sql);
	$rows = null;
	if($update == false){
		$rows = array();
		while ($row = $dbr->fetchRow($result)) {
			$rows[] = $row;
		}
	}
	return $rows;
}
?>