<?php
    require_once('commandLine.inc');
    

    $sql = "SELECT sr.* FROM survey_results sr";
    $data = execSQLStatement($sql);
    
    echo "First Name,Last Name,Email,Role,Submitted\n";

    foreach($data as $row){
    	$user_id = $row['user_id'];
    

	    $person = Person::newFromId($user_id);

	    $role = "Other";
	    if($person->isHQP()){
	    	$role="HQP";
	    	continue;
	    }
	    else if($person->isCNI()){
	    	$role="CNI";
	    }
	    else if($person->isPNI()){
	    	$role="PNI";
	    }

	    $name = $person->splitName();
	    $f_name = $name['first'];
	    $l_name = $name['last'];
	    $email = $person->getEmail();
	    $submitted = ($row['submitted'] == 1)? "Yes" : "No";

	    echo "$l_name,$f_name,$email,$role,$submitted\n";

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
