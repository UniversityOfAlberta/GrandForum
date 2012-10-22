<?php
    require_once('commandLine.inc');
    $people = Person::getAllPeople('all');
    
    foreach($people as $person){
        $biography = $person->getBiography();
        $biography = str_replace("'", "&#39;", $biography);
        $sql = "UPDATE mw_user
                SET user_public_profile = '$biography', 
                user_private_profile = '$biography'
                WHERE user_id = '{$person->getId()}'";
        execSQLStatement($sql, true);
        echo "{$person->getName()} biography updated\n";
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
