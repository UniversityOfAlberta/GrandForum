<?php
    require_once('commandLine.inc');
    $projects = Project::getAllProjects();
    
    foreach($projects as $project){
        $sql = "INSERT INTO grand_project (`id`,`name`, `fullName`)
                VALUES ('{$project->getId()}','{$project->getName()}','{$project->getFullName()}')";
        execSQLStatement($sql);
        $sql = "INSERT INTO grand_project_themes (`project_id`, `name`, `themes`, `start_date`)
                VALUES ('{$project->getId()}','{$project->getName()}','{$project->getTheme(1)}\n{$project->getTheme(2)}\n{$project->getTheme(3)}\n{$project->getTheme(4)}\n{$project->getTheme(5)}',CURRENT_TIMESTAMP)";
        execSQLStatement($sql);
        echo "{$project->getName()} Upgraded\n";
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
