<?php
    require_once('commandLine.inc');
    $people = Person::getAllPeople('all');
    
    foreach($people as $person){
        $sql = "SELECT * 
                FROM mw_user_groups g, mw_user u
                WHERE u.user_id = g.ug_user
                AND u.user_id = '{$person->getID()}'";
        $data = execSQLStatement($sql);
        foreach($data as $row){
            switch($row['ug_group']){
                case CNI:
                case PNI:
                case HQP:
                case GOV:
                case CHAMP:
                case BOD:
                case RMC:
                    $sql = "INSERT INTO grand_roles (`user`, `role`, `start_date`)
                            VALUES ('{$person->getID()}', '{$row['ug_group']}', CURRENT_TIMESTAMP)";
                    if(execSQLStatement($sql, true)){
                        echo "{$person->getName()} added to {$row['ug_group']}\n";
                    }
                    else {
                        echo "{$person->getName()} not added to {$row['ug_group']}\n";
                    }
                    
                    if($row['ug_group'] == PNI || $row['ug_group'] == CNI){
                        $command = "echo \"{$person->getEmail()}\" | /usr/lib/mailman/bin/add_members --admin-notify=n --welcome-msg=n -r - grand-forum-researchers";
		                exec($command);
                    }
                    else if($row['ug_group'] == HQP){
                        $command = "echo \"{$person->getEmail()}\" | /usr/lib/mailman/bin/add_members --admin-notify=n --welcome-msg=n -r - grand-forum-hqps";
		                exec($command);
                    }
                    break;
                case "AESTHVIS":
                case "AFEVAL":
                case "AMBAID":
                case "BELIEVE":
                case "CAPSIM":
                case "CPRM":
                case "DIGLT":
                case "DINS":
                case "ENCAD":
                case "EOVW":
                case "GAMFIT":
                case "HCTSL":
                case "HDVID":
                case "HLTHSIM":
                case "HSCEG":
                case "INCLUDE":
                case "MCSIG":
                case "MEOW":
                case "MOTION":
                case "NAVEL":
                case "NEWS":
                case "NGAIA":
                case "PERUI":
                case "PLATFORM":
                case "PLAYPR":
                case "PRIVNM":
                case "PROMO":
                case "SHRDSP":
                case "SIMUL":
                case "SKETCH":
                case "VIRTPRES":
                case "DIGILAB":
                case "GRNCTY":
                case "NEUROGAM":
                    $project = Project::newFromName($row['ug_group']);
                    $sql = "INSERT INTO grand_user_projects (`user`, `project_id`, `start_date`)
                            VALUES ('{$person->getID()}','{$project->getId()}', CURRENT_TIMESTAMP)";
                    if(execSQLStatement($sql, true)){
                        echo "{$person->getName()} added to {$row['ug_group']}\n";
                    }
                    else {
                        echo "{$person->getName()} not added to {$row['ug_group']}\n";
                    }
                    break;
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
