<?php
    require_once('commandLine.inc');
    $months = array("January" => "01",
                    "February" => "02",
                    "March" => "03",
                    "April" => "04",
                    "May" => "05",
                    "June" => "06",
                    "July" => "07",
                    "August" => "08",
                    "September" => "09",
                    "October" => "10",
                    "November" => "11",
                    "December" => "12");
    
    $sql = "SELECT *
            FROM mw_milestones_history";
    $data = execSQLStatement($sql);
    foreach($data as $row){
        $sql = "INSERT INTO grand_milestones (`milestone_id`,`project_id`,`title`,`status`,`description`,`assessment`,`start_date`,`projected_end_date`)
                VALUES ('{$row['id']}','{$row['project']}','".str_replace("'", "#39;", $row['title'])."','New','".str_replace("'", "#39;", $row['description'])."','".str_replace("'", "#39;", $row['assessment'])."','{$row['timestamp']}','0000-00-00')";
        execSQLStatement($sql, true);
        echo "Milestone #{$row['id']} Upgraded\n";
    }
    
    $projects = Project::getAllProjects();
	foreach($projects as $project){
        echo "====={$project->getName()}=====\n";
        $milestones = $project->getMilestones();
        
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
        
        $k = "statusPLq1a5{$project->getName()}";
        $i=1;
        foreach($milestones as $milestone){
            if(isset($data["statusPLq1a{$i}{$project->getName()}"])){
                echo "Milestone #{$milestone->getId()} ";
                $status = $data["statusPLq1a{$i}{$project->getName()}"];
                switch($status){
                    case "Completed":
                        $comment = str_replace("'", "&#39;", $data["completePLq1a{$i}{$project->getName()}"]);
                        $sql = "UPDATE grand_milestones
                                SET end_date = CURRENT_TIMESTAMP
                                WHERE id = '{$milestone->getId()}'";
                        execSQLStatement($sql, true);
                        $sql = "INSERT INTO grand_milestones (`milestone_id`,`project_id`,`title`,`status`,`description`,`assessment`,`comment`,`start_date`,`end_date`,`projected_end_date`)
                                VALUES ('{$milestone->getId()}','{$project->getId()}','{$milestone->getTitle()}','Closed','{$milestone->getDescription()}','{$milestone->getAssessment()}','$comment',CURRENT_TIMESTAMP, CURRENT_TIMESTAMP,'{$milestone->getProjectedEndDate()}')";
                        execSQLStatement($sql, true);
                        echo "Closed\n";
                        break;
                    case "Ongoing":
                        $description = str_replace("'", "&#39;", $data["descPLq1a{$i}{$project->getName()}"]);
                        $assessment = str_replace("'", "&#39;", $data["assPLq1a{$i}{$project->getName()}"]);
                        $end_date = "{$data["yearPLq1a{$i}{$project->getName()}"]}-{$months[$data["monthPLq1a{$i}{$project->getName()}"]]}-00";
                        if($description != $milestone->getDescription() ||
                           $assessment != $milestone->getAssessment() ||
                           $end_date != $milestone->getProjectedEndDate()){
                            $sql = "UPDATE grand_milestones
                                    SET end_date = CURRENT_TIMESTAMP
                                    WHERE id = '{$milestone->getId()}'";
                            execSQLStatement($sql, true);
                            $sql = "INSERT INTO grand_milestones (`milestone_id`,`project_id`,`title`,`status`,`description`,`assessment`,`start_date`,`projected_end_date`)
                                    VALUES ('{$milestone->getId()}','{$project->getId()}','{$milestone->getTitle()}','Revised','$description','$assessment',CURRENT_TIMESTAMP,'$end_date')";
                            execSQLStatement($sql, true);
                            echo "Revised\n";
                        }
                        else{
                            $sql = "UPDATE grand_milestones
                                    SET end_date = CURRENT_TIMESTAMP
                                    WHERE id = '{$milestone->getId()}'";
                            execSQLStatement($sql, true);
                            $sql = "INSERT INTO grand_milestones (`milestone_id`,`project_id`,`title`,`status`,`description`,`assessment`,`start_date`,`projected_end_date`)
                                    VALUES ('{$milestone->getId()}','{$project->getId()}','{$milestone->getTitle()}','Continuing','$description','$assessment',CURRENT_TIMESTAMP,'$end_date')";
                            execSQLStatement($sql, true);
                            echo "Continuing\n";
                        }
                        break;
                    case "Abandoned":
                        $comment = str_replace("'", "&#39;", $data["abandonedPLq1a{$i}{$project->getName()}"]);
                        $sql = "UPDATE grand_milestones
                                SET end_date = CURRENT_TIMESTAMP
                                WHERE id = '{$milestone->getId()}'";
                        execSQLStatement($sql, true);
                        $sql = "INSERT INTO grand_milestones (`milestone_id`,`project_id`,`title`,`status`,`description`,`assessment`,`comment`,`start_date`,`end_date`,`projected_end_date`)
                                VALUES ('{$milestone->getId()}','{$project->getId()}','{$milestone->getTitle()}','Abandoned','{$milestone->getDescription()}','{$milestone->getAssessment()}','$comment',CURRENT_TIMESTAMP,CURRENT_TIMESTAMP,'{$milestone->getProjectedEndDate()}')";
                        execSQLStatement($sql, true);
                        echo "Abandoned\n";
                        break;
                }
            }
            $i++;
        }
        
        $i=1;
        $k = "PLq1b{$project->getName()}";
        while(isset($data["title_$k$i"])){
            $sql = "SELECT MAX(milestone_id) as max FROM grand_milestones";
            $d = execSQLStatement($sql);
            $max = $d[0]['max'];
            $title = str_replace("'", "&#39;", $data["title_$k$i"]);
            $description = str_replace("'", "&#39;", $data["desc_$k$i"]);
            $assessment = str_replace("'", "&#39;", $data["ass_$k$i"]);
            if(isset($data["year{$k}{$i}"])){
                $end_date = "{$data["year{$k}{$i}"]}-{$months[$data["month{$k}{$i}"]]}-00";
            }
            else{
                $end_date = "0000-00-00";
            }
            $sql = "INSERT INTO grand_milestones (`milestone_id`,`project_id`,`title`,`status`,`description`,`assessment`,`start_date`,`projected_end_date`)
                    VALUES ('".($max + 1)."','{$project->getId()}','$title','New','$description','$assessment',CURRENT_TIMESTAMP,'$end_date')";
            execSQLStatement($sql, true);
            echo "Milestone #".($max + 1)." Added\n";
            $i++;
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
