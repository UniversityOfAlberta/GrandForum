<?php
	require_once( 'commandLine.inc' );
	echo "\n";
	$projects = Project::getAllProjects();
	foreach($projects as $project){
        echo "====={$project->getName()}=====\n";
        $leader = $project->getLeader();
        if($leader == null){
            continue;
        }
        $sto = new ReportStorage($leader);
        $reps = $sto->list_reports($leader->getId(), 1);

        $pdf = Evaluate::getPLProjectPDF($project);
        $t = $pdf['tok'];
        $repo = new ReportStorage($leader);
        $repo->select_report($t);
        $data = $repo->fetch_data($t);

        $k = "PLqpre{$project->getName()}";
        if(isset($data[$k])){
            echo "  Report Found\n";
            $newText = $data[$k];
            $newText = str_replace("&", "&amp;", $newText);
            $newText = str_replace("'", "&#39;", $newText);
		    $newText = str_replace("•", "\*", $newText);
		    $newText = str_replace("  ", " ", $newText);
		    $newText = str_replace("\r", "", $newText);
		    $newText = str_replace("\n ", "\n", $newText);
		    $newText = str_replace("\n ", "\n", $newText);
		    $newText = str_replace("\n", "\n\n", $newText);
            $sql = "UPDATE grand_project_descriptions
                    SET `end_date`=CURRENT_TIMESTAMP
                    WHERE name='{$project->getName()}'";
            execSQLStatement($sql, true);
            $sql = "INSERT INTO grand_project_descriptions (`project_id`,`name`,`description`,`start_date`)
                    VALUES('{$project->getId()}','{$project->getName()}','$newText',CURRENT_TIMESTAMP)";
            execSQLStatement($sql, true);
            echo "Report Description Updated\n";
        }
        else{
            echo "  No Report Found\n";
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
