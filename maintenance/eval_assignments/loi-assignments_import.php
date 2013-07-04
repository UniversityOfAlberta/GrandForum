<?php
require_once('../commandLine.inc');

    
$assignments = array();

//if (($handle = fopen("/local/data/www-root/grand_forum/data/Evaluator-Project_Conflicts-v3.csv", "r")) !== FALSE) {
if (($handle = fopen("/Library/WebServer/Documents/grand_forum/maintenance/eval_assignments/LOI_Assignments.csv","r")) !== FALSE) {
    
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    	$evaluator = $data[0];
        $assignments["{$evaluator}"] = array();
    	for($j=1; isset($data[$j]); $j++){
			$loi = $data[$j];
			$assignments["{$evaluator}"][] = $loi;
		}
        
    }
    fclose($handle);
    print_r($assignments);
   
}
    
$sql = "INSERT INTO mw_eval(eval_id, sub_id, type, year) VALUES(%d, %d, '%s', %d)";


foreach ($assignments as $eval_name => $lois) {
    $evaluator = Person::newFromName($eval_name);
    $eval_id = $evaluator->getId();
    if($eval_id){

        foreach($lois as $loi_name){
            $loi = LOI::newFromName($loi_name);
            if(is_null($loi)){
                echo "Something is wrong with assignment {$eval_name} to {$loi_name}\n";
                break;
            }

            $loi_id = $loi->getId();
            $insert_q = sprintf($sql, $eval_id, $loi_id, "LOI", 2013);
            $res = execSQLStatement($insert_q, true);
            
        }

        
    }
    else{
        echo "Something is wrong with {$eval_name}\n";
    }
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
