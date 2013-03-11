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
	$sql = "SELECT sr.* FROM survey_results sr";
    $data = execSQLStatement($sql);

    //EXCEL
    $phpExcel = new PHPExcel();
	$styleArray = array('font' => array('bold' => true));

	$foo = $phpExcel->getActiveSheet();
	
	//add column headers, set the title and make the text bold
	$foo
	->setCellValue("A1", "Name")
	->setCellValue("B1", "First Name")
	->setCellValue("C1", "Last Name")
	->setCellValue("D1", "Primary Funding Agency")
	->setCellValue("E1", "Secondary Funding Agency")
	->setCellValue("F1", "Primary Discipline")
	->setCellValue("G1", "Area of Specialization")
	->setCellValue("H1", "Affiliation")
	->setCellValue("I1", "Location")
	->setCellValue("J1", "GRAND Role")
	->setCellValue("K1", "GRAND_networking")
	->setCellValue("L1", "GRAND_communication")
	->setCellValue("M1", "GRAND_admin")
	->setCellValue("N1", "GRAND_report")
	->setCellValue("O1", "GRAND_funding")
	->setCellValue("P1", "GRAND_research")
	->setCellValue("Q1", "GRAND_career")
	->setCellValue("R1", "GRAND_comments")
	->setCellValue("S1", "Receive Survey Results")
	->setCellValue("T1", "Additional Comments")
	->setCellValue("U1", "GRAND_Net_Networking")
	->setCellValue("V1", "GRAND_Net_Individual")
	->setCellValue("W1", "GRAND_Net_Share")
	->setCellValue("X1", "GRAND_Net_Collaborate")
	->setCellValue("Y1", "GRAND_Net_Comments");

	$allprojs = Project::getAllProjectsDuring();
	$eovw = Project::newFromHistoricName('EOVW');
	$allprojs[] = $eovw;
	$existing_projs = array();
	foreach($allprojs as $proj){
		$proj_name = $proj->getName();
		$query = "SELECT * FROM survey_results WHERE experience LIKE '%{$proj_name}%'";
		$proj_res = execSQLStatement($query);
		if(count($proj_res) > 0){
			$existing_projs[] = $proj_name;
		}
	}
	
	$alphas1 = range('A', 'Z');
	$alphas2 = range('A', 'Z');

	$column_map = array();
	foreach ($existing_projs as $proj_name) {
		$a1 = current($alphas1);
		$a2 = current($alphas2);	
		$foo->setCellValue("{$a1}{$a2}1", "{$proj_name}_team_coordinate");
		$column_map["{$proj_name}_team_coordinate"] = "{$a1}{$a2}";

		$a2 = next($alphas2);
		if($a2 === false){
			$a2 = reset($alphas2);
			$a1 = next($alphas1);
		}
		$foo->setCellValue("{$a1}{$a2}1", "{$proj_name}_team_work");
		$column_map["{$proj_name}_team_work"] = "{$a1}{$a2}";

		$a2 = next($alphas2);
		if($a2 === false){
			$a2 = reset($alphas2);
			$a1 = next($alphas1);
		}
		$foo->setCellValue("{$a1}{$a2}1", "{$proj_name}_team_efficiency");
		$column_map["{$proj_name}_team_efficiency"] = "{$a1}{$a2}";

		$a2 = next($alphas2);
		if($a2 === false){
			$a2 = reset($alphas2);
			$a1 = next($alphas1);
		}
		$foo->setCellValue("{$a1}{$a2}1", "{$proj_name}_comments");
		$column_map["{$proj_name}_comments"] = "{$a1}{$a2}";

		$a2 = next($alphas2);
		if($a2 === false){
			$a2 = reset($alphas2);
			$a1 = next($alphas1);
		}
	}

	$foo->setTitle("Personal Information");
	//->getStyle("A1:B1:C1:D1:E1:F1:G1:H1:J1:K1")->applyFromArray($styleArray);

	$row_count = 2;
    foreach($data as $row){
    	$user_id = $row['user_id'];

	    $person = Person::newFromId($user_id);

	    $role = "Other";
	    if($person->isHQP()){
	    	$role="HQP";
	    }
	    else if($person->isCNI()){
	    	$role="CNI";
	    }
	    else if($person->isPNI()){
	    	$role="PNI";
	    }

	    if($role == "HQP" || $role == "Other"){
	    	continue;
	    }

	    $uni = $person->getUniversity();
	    $affil = "";
	    $location = "";
	    if(!empty($uni)){
	    	$affil = $uni['university'];
	    }
	    
	    $f_name = $row['first_name'];
	    $l_name = $row['last_name'];
	    $name = $f_name."_".$l_name;

	    $discipline = ($row['discipline'])? $row['discipline'] : "";
        $disciplines = json_decode($discipline, true);
       
        $d_level1a = (isset($disciplines["d_level1a"]))? $disciplines["d_level1a"] : "";
        $d_level1b = (isset($disciplines["d_level1b"]))? $disciplines["d_level1b"] : "";
        $d_level2  = (isset($disciplines["d_level2"]))? $disciplines["d_level2"] : "";
        if(preg_match('/please specify/', $d_level2)){
            $strarr = preg_split('/\|/', $d_level2);
            $d_level2 = @$strarr[1];
        }

        $d_level3  = (isset($disciplines["d_level3"]))? $disciplines["d_level3"] : "";

        //Section GRAND
        $experience = json_decode($row['experience2'], true);
        $experience = ($experience)? $experience : array(); 
        $e_val=array("strongly_disagree"=>1, "disagree"=>2, "undecided"=>3, "agree"=>4, "strongly_agree"=>5, "dont_know"=>-1);

        $GRAND_networking = (isset($experience['grand_q1']))?  $experience['grand_q1'] : "dont_know";
        $GRAND_networking =  (isset($e_val[$GRAND_networking]))? $e_val[$GRAND_networking] : -1;

        $GRAND_communication = (isset($experience['grand_q2']))?  $experience['grand_q2'] : "dont_know";
        $GRAND_communication =  (isset($e_val[$GRAND_communication]))? $e_val[$GRAND_communication] : -1;

        $GRAND_admin = (isset($experience['grand_q3']))?  $experience['grand_q3'] : "dont_know";
        $GRAND_admin =  (isset($e_val[$GRAND_admin]))? $e_val[$GRAND_admin] : -1;

        $GRAND_report = (isset($experience['grand_q4']))?  $experience['grand_q4'] : "dont_know";
        $GRAND_report =  (isset($e_val[$GRAND_report]))? $e_val[$GRAND_report] : -1;

        $GRAND_funding = (isset($experience['grand_q5']))?  $experience['grand_q5'] : "dont_know";
        $GRAND_funding =  (isset($e_val[$GRAND_funding]))? $e_val[$GRAND_funding] : -1;

        $GRAND_research = (isset($experience['grand_q6']))?  $experience['grand_q6'] : "dont_know";
        $GRAND_research =  (isset($e_val[$GRAND_research]))? $e_val[$GRAND_research] : -1;

        $GRAND_career = (isset($experience['grand_q7']))?  $experience['grand_q7'] : "dont_know";
        $GRAND_career =  (isset($e_val[$GRAND_career]))? $e_val[$GRAND_career] : -1;

        $GRAND_comments = (isset($experience['grand_comments']))?  htmlspecialchars(urldecode($experience['grand_comments'])) : "";
        
        $receive_results = (isset($row['receive_results']))? (($row['receive_results'] == 1)? "Yes" : "No") : "N/A";
        $additional_comments = (isset($row['additional_comments']))? htmlspecialchars($row['additional_comments']) : "";

        //Section Networking
        $Net_Networking = (isset($experience['network_q1']))?  $experience['network_q1'] : "dont_know";
        $Net_Networking =  (isset($e_val[$Net_Networking]))? $e_val[$Net_Networking] : -1;

        $Net_Individual = (isset($experience['network_q2']))?  $experience['network_q2'] : "dont_know";
        $Net_Individual =  (isset($e_val[$Net_Individual]))? $e_val[$Net_Individual] : -1;

        $Net_Share = (isset($experience['network_q3']))?  $experience['network_q3'] : "dont_know";
        $Net_Share =  (isset($e_val[$Net_Share]))? $e_val[$Net_Share] : -1;

        $Net_Collaborate = (isset($experience['network_q4']))?  $experience['network_q4'] : "dont_know";
        $Net_Collaborate =  (isset($e_val[$Net_Collaborate]))? $e_val[$Net_Collaborate] : -1;

        $Net_Comments = (isset($experience['network_comments']))?  htmlspecialchars(urldecode($experience['network_comments'])) : "";

	    $foo
	    ->setCellValue("A{$row_count}", $name)
	    ->setCellValue("B{$row_count}", $f_name)
	    ->setCellValue("C{$row_count}", $l_name)
	    ->setCellValue("D{$row_count}", $d_level1a)
	    ->setCellValue("E{$row_count}", $d_level1b)
	    ->setCellValue("F{$row_count}", $d_level2)
	    ->setCellValue("G{$row_count}", $d_level3)
	    ->setCellValue("H{$row_count}", $affil)
	    ->setCellValue("I{$row_count}", $location)
	    ->setCellValue("J{$row_count}", $role)
	    ->setCellValue("K{$row_count}", $GRAND_networking)
	    ->setCellValue("L{$row_count}", $GRAND_communication)
	    ->setCellValue("M{$row_count}", $GRAND_admin)
	    ->setCellValue("N{$row_count}", $GRAND_report)
	    ->setCellValue("O{$row_count}", $GRAND_funding)
	    ->setCellValue("P{$row_count}", $GRAND_research)
	    ->setCellValue("Q{$row_count}", $GRAND_career)
	    ->setCellValue("R{$row_count}", $GRAND_comments)
		->setCellValue("S{$row_count}", $receive_results)
	    ->setCellValue("T{$row_count}", $additional_comments)
	    ->setCellValue("U{$row_count}", $Net_Networking)
	    ->setCellValue("V{$row_count}", $Net_Individual)
	    ->setCellValue("W{$row_count}", $Net_Share)
	    ->setCellValue("X{$row_count}", $Net_Collaborate)
	    ->setCellValue("Y{$row_count}", $Net_Comments);

	    //Section Projects
	    $pexperience = json_decode($row['experience'], true);
        $pexperience = ($pexperience)? $pexperience : array();

        $proj_ques_map = array('project_q1'=>'team_coordinate','project_q2'=>'team_work','project_q3'=>'team_efficiency','project_comments'=>'comments');
        foreach($pexperience as $pex_i => $pex_v){
        	$pex_i = preg_split('/_/', $pex_i, 2);
        	
        	$proj = @$pex_i[0];
        	$ques = @$pex_i[1];
        	$column_lbl = "";
        	if(!empty($proj) && !empty($ques)){
        		$ques = $proj_ques_map[$ques];
        		$column_lbl = $column_map["{$proj}_{$ques}"];
        	}
        	
        	if($ques != 'comments'){
        		$pex_v = (isset($e_val[$pex_v]))? $e_val[$pex_v] : -1;
        	}
        	else{
        		$pex_v = htmlspecialchars(urldecode($pex_v));
        	}

        	if($column_lbl){
        		$foo->setCellValue("{$column_lbl}{$row_count}", $pex_v);
        	}
        }

        //break;
		$row_count++;

	    //echo "$l_name,$f_name,$email,$role,$consent,$submitted\n";

	}

	$phpExcel->setActiveSheetIndex(0);
	$objWriter = PHPExcel_IOFactory::createWriter($phpExcel, "Excel5");
	$objWriter->save('Survey-personal.xls');
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