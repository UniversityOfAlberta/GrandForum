<?php
require_once('commandLine.inc');
$NI_type = 'PNI';
    
$row = 1;
if (($handle = fopen("/local/data/www-root/grand_forum/data/nis-with-budgets-sorted.csv", "r")) !== FALSE) {
//if (($handle = fopen("/Library/WebServer/Documents/grand_forum/data/nis-with-budgets-sorted.csv", "r")) !== FALSE) {
	$people = array();
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    	$name = $data[0];
        //echo $name;
    	$person = Person::newFromName($name);
        if($person->getId()){
            $people[] = $person->getName();
            /*$p_id = $person->getId();
            $check_budget = "SELECT * FROM grand_report_blobs WHERE year=2012 AND user_id=$p_id AND rp_type=1 AND rp_section=8 AND rp_item=0";
            $res = execSQLStatement($check_budget);
            //$budget = $person->getRequestedBudget(2012);
            if( count($res) > 0 ){
                echo "YES -> ".$name."\n";
            }else{
                echo "--NO ->".$name."\n";
            }*/
	}else{
            echo "Could not find $name \n";
        }
        
    }
    fclose($handle);
//    print_r($eval_proj);


    $allPeople = Person::getAllPeople(CNI); //array_merge(Person::getAllPeople(CNI), Person::getAllPeople(PNI));
	foreach($allPeople as $person){
		//$budget = $person->getRequestedBudget(2012);
		$p_id = $person->getId();
            	$check_budget = "SELECT * FROM grand_report_blobs WHERE year=2012 AND user_id=$p_id AND rp_type=1 AND rp_section=8 AND rp_item=0";
           	$res = execSQLStatement($check_budget);
		if( count($res) <= 0 ){
                	//echo $name.": Yes\n";
            	}else{
			if(!in_array($person->getName(), $people)){
                		echo $person->getName() . " ---> With budget\n";
            		}
		}
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

