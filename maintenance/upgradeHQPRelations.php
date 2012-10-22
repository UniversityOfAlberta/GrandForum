<?php
    require_once('commandLine.inc');
    $pg = "http://forum.grand-nce.ca/index.php/Special:Report";
    $people = array_merge(Person::getAllPeople(PNI), Person::getAllPeople(CNI));
    $hqps = Person::getAllPeople(HQP);
    $updated = array();
    foreach($people as $person){
	    $sd = new SessionData($person->getId(), $pg, SD_REPORT);
	    $dt = $sd->fetch();
        if(isset($dt['hqp'])){
            foreach($dt['hqp'] as $name){
                $hqp = Person::newFromNameLike($name);
                if($hqp != null && $hqp->getName() != null){
                    if($hqp->isRole(HQP)){
                        $sql = "INSERT INTO grand_relations (`user1`,`user2`,`type`,`start_date`)
                                VALUES ('{$person->getId()}','{$hqp->getId()}','Supervises',CURRENT_TIMESTAMP)";
                        execSQLStatement($sql, true);
                    }
                    else{
                        foreach($hqp->getRoles(true) as $role){
                            if($role->getRole() == HQP){
                                $sql = "INSERT INTO grand_relations (`user1`,`user2`,`type`,`start_date`,`end_date`)
                                VALUES ('{$person->getId()}','{$hqp->getId()}','Supervises','{$role->getStartDate()}','{$role->getEndDate()}')";
                                execSQLStatement($sql, true);
                                break;
                            }
                        }
                    }
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
