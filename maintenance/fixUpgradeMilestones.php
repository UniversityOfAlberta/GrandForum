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
        
        $i=0;
        $k = "PLq1b{$project->getName()}";
        if(isset($data["title_$k$i"]) && $project->getName() != "NEUROGAM" && 
            $project->getName() != "GRNCTY" && 
            $project->getName() != "INCLUDE"){
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
                    VALUES ('".($max + 1)."','{$project->getId()}','$title','New','$description','$assessment','2010-11-01','$end_date')";
            execSQLStatement($sql, true);
            echo "Milestone #".($max + 1)." Added\n";
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
