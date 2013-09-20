<?php
require_once('commandLine.inc');
    

    
$current_evals = array(17,563,152,25,90,27,28,564,32,565,566,36,38,41,48,55,60,61,1263);

$cur_year = date('Y');
$sql = "SELECT DISTINCT eval_id FROM grand_eval_conflicts WHERE type='PROJECT' AND year={$cur_year}";
$data = execSQLStatement($sql);
$total_conflict_submissions = count($data);
$total_evaluators = count($current_evals);


$csv = '"Names"';    
   
$eval_pos = array();
$eval_papers = array();
$eval_hqp = array();

foreach($current_evals as $eval_id){
    $eval = Person::newFromId($eval_id);
    $eval_name = $eval->getName();
    $eval_name_prop = explode('.', $eval_name); 
    $efname = $eval_name_prop[0];
    $elname = implode(' ', array_slice($eval_name_prop, 1));
    
    //Cache Eval stuff
    $eval_pos[$eval_id] = array();
    $eval_papers[$eval_id] = array();
    $eval_hqp[$eval_id] = array();

    //Eval Organizations
    $pos = $eval->getUniversity();
    $eval_pos[$eval_id] = $pos['university'];

    //Eval Papers
    foreach($eval->getPapers("all", true) as $epaper){
        $eval_papers[$eval_id][] = $epaper->getId();
    }
    //Eval HQP
    foreach($eval->getHQP(true) as $ehqp){
        $eval_hqp[$eval_id][] = $ehqp->getId();    
    }

    $csv .= ',"'.$eval_name.'"';
}
$csv .= "\n";


$allProjects = Project::getAllProjects();
foreach($allProjects as $project){
    $project_name = $project->getName();
    $project_id = $project->getId();            
    
    $csv .= '"'.$project_name.'"';

    foreach($current_evals as $eval_id){
        $eval = Person::newFromId($eval_id);
        $eval_name = $eval->getName();
        $eval_name_prop = explode('.', $eval_name); 
        $efname = $eval_name_prop[0];
        $elname = implode(' ', array_slice($eval_name_prop, 1));

         //Get saved conflicts data if any
        $sql = "SELECT * FROM grand_eval_conflicts WHERE eval_id = '{$eval_id}' AND sub_id = '{$project_id}' AND type='PROJECT' AND year={$cur_year}";
        $data = execSQLStatement($sql);
        
        //$data = array();
        $bgcolor = "#FFFFFF";    
        if(count($data) > 0){
            $conflict = $data[0]['conflict'];
            $user_conflict = $data[0]['user_conflict'];         

            if($conflict || $user_conflict ){
                $csv .= ',"-2000"';
            }
            else{
                $conflict_number = 0;

                //Conflicts per each member
                $p_cnis = $project->getAllPeople(CNI);
                $p_pnis = $project->getAllPeople(PNI);
                foreach( array_merge($p_cnis, $p_pnis) as $p_pers ){
                    //Organization
                    $p_pers_pos = $p_pers->getUniversity();
                    $p_pers_pos = $p_pers_pos['university'];
                    if(!empty($p_pers_pos) && $p_pers_pos == $eval_pos[$eval_id]){
                        $conflict_number -= 100;
                        continue;
                    }

                    //Papers
                    $p_pers_papers = $p_pers->getPapers("all", true);
                    $co_authorship = 0;
                    foreach($p_pers_papers as $p_pers_paper){
                        if(in_array($p_pers_paper->getId(), $eval_papers[$eval_id])){
                            $co_authorship = 1;
                            break;
                        }
                    }
                    if($co_authorship){ 
                        $conflict_number -= 100;
                        continue;
                    }

                    //HQP
                    $co_supervision = 0;
                    foreach($p_pers->getHQP(true) as $hqp){
                        if(in_array($hqp->getId(), $eval_hqp[$eval_id])){
                            $co_supervision = 1;
                            break;
                        }
                    }
                    if($co_supervision){ 
                        $conflict_number -= 100;
                        continue;
                    }

                    //Work With 
                    $works_with = 0;            
                    $co_workers = array();
                    foreach($p_pers->getRelations("Works With", true) as $rel){
                        $co_workers[] = $rel->getUser2()->getId();
                    }
                    if($p_pers->relatedTo($eval, 'Works With') || $eval->relatedTo($p_pers, 'Works With') || in_array($eval_id, $co_workers)){
                        $works_with = 1;
                    }
                    if($works_with){ 
                        $conflict_number -= 100;
                        continue;
                    }
                    
                }
                $csv .= ',"'.$conflict_number.'"';

            }
            
        }
        else{
            $eval_projects = array();
            foreach($eval->getProjects() as $eproject){
                $eval_projects[] = $eproject->getName();
            }
            
            if(in_array($project_name, $eval_projects)){
                $conflict = 1;
            }
            else{
                $conflict = 0;
            }   
            $user_conflict = 0;

            
            if($conflict){
                $csv .= ',"-2000"';
            }
            else{
                $conflict_number = 0;

                //Conflicts per each member
                $p_cnis = $project->getAllPeople(CNI);
                $p_pnis = $project->getAllPeople(PNI);
                foreach( array_merge($p_cnis, $p_pnis) as $p_pers ){
                    //Organization
                    $p_pers_pos = $p_pers->getUniversity();
                    $p_pers_pos = $p_pers_pos['university'];
                    if(!empty($p_pers_pos) && $p_pers_pos == $eval_pos[$eval_id]){
                        $conflict_number -= 100;
                        continue;
                    }

                    //Papers
                    $p_pers_papers = $p_pers->getPapers("all", true);
                    $co_authorship = 0;
                    foreach($p_pers_papers as $p_pers_paper){
                        if(in_array($p_pers_paper->getId(), $eval_papers[$eval_id])){
                            $co_authorship = 1;
                            break;
                        }
                    }
                    if($co_authorship){ 
                        $conflict_number -= 100;
                        continue;
                    }

                    //HQP
                    $co_supervision = 0;
                    foreach($p_pers->getHQP(true) as $hqp){
                        if(in_array($hqp->getId(), $eval_hqp[$eval_id])){
                            $co_supervision = 1;
                            break;
                        }
                    }
                    if($co_supervision){ 
                        $conflict_number -= 100;
                        continue;
                    }

                    //Work With 
                    $works_with = 0;            
                    $co_workers = array();
                    foreach($p_pers->getRelations("Works With", true) as $rel){
                        $co_workers[] = $rel->getUser2()->getId();
                    }
                    if($p_pers->relatedTo($eval, 'Works With') || $eval->relatedTo($p_pers, 'Works With') || in_array($eval_id, $co_workers)){
                        $works_with = 1;
                    }
                    if($works_with){ 
                        $conflict_number -= 100;
                        continue;
                    }
                    
                }
                $csv .= ',"'.$conflict_number.'"';

            }
            
        }

    }
    $csv .= "\n";
   
}

$myFile = "Evaluator-Project_Conflicts.csv";
$fh = fopen('/local/data/www-root/grand_forum/data/'.$myFile, 'w');
//$fh = fopen('/Library/WebServer/Documents/grand_forum/data/'.$myFile, 'w');
fwrite($fh, $csv);
fclose($fh);
    


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
