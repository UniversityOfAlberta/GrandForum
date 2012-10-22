<?php
    require_once('commandLine.inc');
    $pg = "http://forum.grand-nce.ca/index.php/Special:Report";
    //$pg = "http://hypatia.cs.ualberta.ca/~dwt/grand_forum_test/index.php/Special:Report";
    $people = array_merge(Person::getAllPeople(PNI), Person::getAllPeople(CNI));
    $hqps = Person::getAllPeople(HQP);
    $updated = array();
    foreach($people as $creator){
	    $sd = new SessionData($creator->getId(), $pg, SD_REPORT);
	    $dt = $sd->fetch();
        foreach($hqps as $person){
            if(array_search($person->getId(), $updated) !== false){
                continue;
            }
		    if(isset($dt['IIq7'.str_replace(".", "_", $person->getName())])){
		        $status = $dt['IIq7'.str_replace(".", "_", $person->getName())];
		        if($status == "yes"){
		            $text = str_replace("'", "&#39", $dt['IIq8'.str_replace(".", "_", $person->getName())]);
		            $sql = "UPDATE grand_roles
		                    SET end_date = CURRENT_TIMESTAMP,
		                    comment = '$text'
		                    WHERE user = '{$person->getId()}'
		                    AND role = 'HQP'";
		            execSQLStatement($sql, true);
		            echo "{$person->getName()} Updated\n";
		            $updated[] = $person->getId();
		        }
		    }
		}
    }
    
    function execSQLStatement($sql, $update=false) {
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
