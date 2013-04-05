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
	$sql = "SELECT sr.* FROM survey_results sr";
    $data = execSQLStatement($sql);

    //EXCEL
    $phpExcel = new PHPExcel();
	$styleArray = array('font' => array('bold' => true));

	$projects = Project::getAllProjectsDuring("2012-01-01 00:00:0", "2013-03-10 23:59:59");
	$proj_names = array();
	foreach($projects as $p){
		$proj_names[]= $p->getName();
	}
	sort($proj_names);

	$com_weights = array(
	    "strongly_agree"=>5, 
	    "agree"=>4, 
	    "undecided"=>3, 
	    "disagree"=>2, 
	    "strongly_disagree"=>1,
	    "dont_know"=>0
	);

	$sheets = array(
	 	"Team Coordination"=>"project_q1", 
	 	"Team Efficiency"=>"project_q2",
	 	"Team Work"=>"project_q3");

	$sheetId = 0;
	foreach($sheets as $sheet=>$var_name){

		$phpExcel->createSheet(NULL, $sheetId);
		$phpExcel->setActiveSheetIndex($sheetId);
		$phpExcel->getActiveSheet()->setTitle($sheet);
		$sheetId++;

		$foo = $phpExcel->getActiveSheet();
		
		$row_count = 2;
	    foreach($data as $row){
	    	$user_id = $row['user_id'];
		    $person = Person::newFromId($user_id);

		    if(!$person->isCNI() && !$person->isPNI()){
		    	continue;
		    }

		    $submitted = ($row['submitted'] == 1)? "Yes" : "No";
		    if ($submitted == "No"){
		    	continue;
		    }

		    $f_name = $row['first_name'];
		    $l_name = $row['last_name'];
		    $name = $l_name."_".$f_name;

		    //Add the column
			//$foo->setCellValueByColumnAndRow($col_count, 1, $name);		
			$foo->setCellValueByColumnAndRow(0, $row_count, $name);

		    $experience = json_decode($row['experience'], true);
	        $experience = ($experience)? $experience : array(); 
	      	
	      	$col_count = 1;
	      	foreach($proj_names as $p_name){	

	      		$key = $p_name.'_'.$var_name;
	      		$weight = "";
	      		if(isset($experience[$key])){
	      			$val = $experience[$key];
	      			$weight = isset($com_weights[$val])? $com_weights[$val] : "";
	      		}
	            
		        $foo->setCellValueByColumnAndRow($col_count, 1, $p_name);
			    $foo->setCellValueByColumnAndRow($col_count, $row_count, $weight);
				$col_count++;
		   	}
		   	$row_count++;

		}
	}

	$phpExcel->setActiveSheetIndex(0);
	$objWriter = PHPExcel_IOFactory::createWriter($phpExcel, "Excel5");
	$objWriter->save('Survey-projects.xls');
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