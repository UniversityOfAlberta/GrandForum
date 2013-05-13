<?php

require_once('../commandLine.inc');
require_once($dir . '../../Classes/PHPExcel/IOFactory.php');

if(count($args) > 0){
    if($args[0] == "help"){
        showHelp();
        exit;
    }
}

exportProjects();
//getNIs();
//getSurveyNIs();

function getNIs(){
	$cnis = Person::getAllPeopleDuring('CNI', "2012-01-01 00:00:0", "2013-03-10 23:59:59");
	$pnis = Person::getAllPeopleDuring('PNI', "2012-01-01 00:00:0", "2013-03-10 23:59:59");
	$nis = array_merge($cnis, $pnis);
	$ni_names = array();
	foreach($nis as $ni){
		if(!in_array($ni->getName(), $ni_names)){
			$ni_names[] = $ni->getName();
		}
	}
	
	print_r($ni_names);
	echo "\n". count($ni_names) ."\n";
}

function getSurveyNIs(){
	$sql = "SELECT sr.* FROM survey_results sr";
    $data = execSQLStatement($sql);
    $count = 0;
    foreach($data as $row){
        $user_id = $row['user_id'];
        $person = Person::newFromId($user_id);

        if(!($person->isRoleDuring('CNI', "2012-01-01 00:00:0", "2013-03-10 23:59:59") || $person->isRoleDuring('PNI', "2012-01-01 00:00:0", "2013-03-10 23:59:59")) ){
        	continue;
        }
        
        $submitted = ($row['submitted'] == 1)? "Yes" : "No";
	    if ($submitted == "No"){
	    	continue;
	    }
	    
        $count++;
    }
    echo $count ."\n";
}

function exportProjects(){

    //EXCEL
    $phpExcel = new PHPExcel();
	$styleArray = array('font' => array('bold' => true));

	$cnis = Person::getAllPeopleDuring('CNI', "2012-01-01 00:00:0", "2013-03-10 23:59:59");
	$pnis = Person::getAllPeopleDuring('PNI', "2012-01-01 00:00:0", "2013-03-10 23:59:59");
	$nis = array_merge($cnis, $pnis);
	$ni_names = array();
	foreach($nis as $ni){
		$ni_names[$ni->getId()] = $ni->getReversedName();
	}
	asort($ni_names);
	

	$sheets = array("NI Projects"=>"project_q1");

	$sheetId = 0;
	foreach($sheets as $sheet=>$var_name){

		$phpExcel->createSheet(NULL, $sheetId);
		$phpExcel->setActiveSheetIndex($sheetId);
		$phpExcel->getActiveSheet()->setTitle($sheet);
		$sheetId++;

		$foo = $phpExcel->getActiveSheet();
		$foo->setCellValueByColumnAndRow(0, 1, "Name");
		$foo->setCellValueByColumnAndRow(1, 1, "Projects");

		$row_count = 2;

	    foreach($ni_names as $user_id => $user_name){
	    	
		    $person = Person::newFromId($user_id);

		    if(!$person->isCNI() && !$person->isPNI()){
		    	continue;
		    }

		   
		    $f_name = $person->getFirstName();
		    $l_name = $person->getLastName();
		    $name = $l_name."_".$f_name;

		    $projects = $person->getProjectsDuring("2012-01-01 00:00:0", "2013-03-10 23:59:59");
		    $project_names = array();
		    foreach($projects as $p){
		    	$project_names[] = $p->getName();
		    }
		    $person_projects = implode(', ', $project_names);

		    //Add the column
			$foo->setCellValueByColumnAndRow(0, $row_count, $name);
	     
			$foo->setCellValueByColumnAndRow(1, $row_count, $person_projects);
		   	
		   	$row_count++;
	   }
		
	}

	$phpExcel->setActiveSheetIndex(0);
	$objWriter = PHPExcel_IOFactory::createWriter($phpExcel, "Excel5");
	$objWriter->save('NI-Projects.xls');
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