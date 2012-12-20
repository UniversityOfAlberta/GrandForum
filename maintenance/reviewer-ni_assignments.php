<?php
require_once('commandLine.inc');
$NI_type = 'PNI';
    
$row = 1;
if (($handle = fopen("/local/data/www-root/grand_forum/data/Evaluator-Project_Conflicts.csv", "r")) !== FALSE) {
//if (($handle = fopen("/Library/WebServer/Documents/grand_forum/data/Evaluator-Project_Conflicts.csv", "r")) !== FALSE) {
    
    $eval_proj = array();
    $eval_index = array();
	$row0 = fgetcsv($handle, 1000, ",");
	for($i=1; isset($row0[$i]); $i++){
		$eval = $row0[$i];
		$eval_index[$i] = $eval;
		$eval_proj[$eval] = array();
	}
	

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    	$project = $data[0];
    	for($j=1; isset($data[$j]); $j++){
			$affinity = $data[$j];
			$eval_proj[$eval_index[$j]][$project] = $affinity;
			
		}
        
    }
    fclose($handle);
    print_r($eval_proj);


    //Let's do it
    $current_evals = array(17,563,152,25,90,27,28,564,32,565,566,36,38,41,48,55,60,61,1263);

    $allPeople = Person::getAllPeople(PNI); //array_merge(Person::getAllPeople(CNI), Person::getAllPeople(PNI));

    $csv = '"Names"';

    foreach($current_evals as $eval_id){
        $eval = Person::newFromId($eval_id);
        $eval_name = $eval->getName(); 
        
        $csv .= ',"'.$eval_name.'"';

        //cache eval papers
       // $eval_papers[$eval_id] = array();
       // foreach($eval->getPapers("all", true) as $epaper){
       //     $eval_papers[$eval_id][] = $epaper->getId();
       // }
    }
    $csv .= "\n";


    //Get conflicts from the DB
    $sql = "SELECT * FROM grand_reviewer_conflicts";
    $data = execSQLStatement($sql);
    $conflicts = array();
    foreach($data as $row){
        $eval_id = $row['reviewer_id'];
        $rev_id = $row['reviewee_id'];
        $conflict = $row['conflict'];
        $user_conflict = $row['user_conflict'];

        $inner = array("conflict"=>$conflict, 'user_conflict'=>$user_conflict);
        $conflicts[$eval_id][$rev_id] = $inner;
    }


    foreach($allPeople as $person){
        $reviewee_id = $person->getId();

        if(in_array($reviewee_id, $current_evals)){
            continue;
        }

        $person_name = $person->getName(); 
       
        //Organization
        $position = $person->getUniversity();
        $position = $position['university'];
        
        //Projects
        $projects = $person->getProjects();
        $proj_names = array();
        
        foreach($projects as $project){
            $proj_names[] = $project->getName();
        }


        $proj_names = implode(' ', $proj_names);

        $papers = $person->getPapers("all", true);
        $projects = $person->getProjects();
        $person_projects = array();
        foreach ($projects as $p){
        	$person_projects[] = $p->getName();
        }
        $person_position = $person->getUniversity();

        $csv .= '"'.$person_name.'"';

        foreach($current_evals as $eval_id){

            $eval = Person::newFromId($eval_id);
            $eval_name = $eval->getName();

            $affinity = 0;

            if($reviewee_id == $eval_id){
                $affinity -= 2000;
            }
            else if(isset($conflicts[$eval_id][$reviewee_id])){
                $data = $conflicts[$eval_id][$reviewee_id];
                $conflict = $data['conflict'];
                $user_conflict = $data['user_conflict'];

                if($conflict || $user_conflict){
                    $affinity -= 2000;
                }
            }
            else{
                //EVAL DATA
                $eval_organization = $eval->getUniversity();      
                $eval_organization = $eval_organization['university'];

                $eval_projects = array();
                foreach($eval->getProjects() as $eproject){
                    $eval_projects[] = $eproject->getName();
                }

                $eval_papers = array();
                foreach($eval->getPapers("all", true) as $epaper){
                    $eval_papers[] = $epaper->getId();
                }

                //Works With
                $eval_coworkers = array();
                foreach($eval->getRelations("Works With", true) as $erel){
                    $eval_coworkers[] = $erel->getUser2();
                }

                $eval_hqp = array();
                foreach($eval->getHQP(true) as $ehqp){
                    $eval_hqp[] = $ehqp->getId();
                }

                //NI DATA
                //Work With 
                $works_with = 0;            
                $co_workers = array();
                foreach($person->getRelations("Works With", true) as $rel){
                    $co_workers[] = $rel->getUser2()->getId();
                }
                if($person->relatedTo($eval, 'Works With') || $eval->relatedTo($person, 'Works With') || in_array($eval_id, $co_workers)){
                    $works_with = 1;
                }
                
                //Organization
                $position = $person_position['university'];
                $same_organization = 0;
                if(!empty($position) && $eval_organization == $position){
                    $same_organization = 1;
                }

                //Projects
                $same_projects = 0;
                foreach($projects as $project){
                    if(in_array($project->getName(), $eval_projects)){
                        $same_projects = 1;
                        break;
                    }
                }
               
                //Papers
                $co_authorship = 0;
                foreach($papers as $paper){
                    if(in_array($paper->getId(), $eval_papers)){
                        $co_authorship = 1;
                        break;
                    }
                }

                //HQP
                $co_supervision = 0;
                foreach($person->getHQP(true) as $hqp){
                    if(in_array($hqp->getId(), $eval_hqp)){
                        $co_supervision = 1;
                        break;
                    }
                }

                if($works_with || $same_organization || $same_projects || $co_authorship || $co_supervision ){
                    $affinity -= 2000;
                }
            }

            //Now check if evaluator have been assigned one of my projects. If so, increase by 100
            foreach($person_projects as $pers_proj){
            
            	if( isset($eval_proj[$eval_name][$pers_proj]) && $eval_proj[$eval_name][$pers_proj] > 0){
                    echo $eval_proj[$eval_name][$pers_proj]. "\n";
            		$affinity += 100;
            	}

            }
            
            $csv .= ',"'.$affinity.'"';


        }//END eval foreach
        $csv .= "\n";

    //END foreach
    }

    $myFile = "Evaluator-{$NI_type}_Conflicts.csv";
    $fh = fopen('/local/data/www-root/grand_forum/data/'.$myFile, 'w');
    //$fh = fopen('/Library/WebServer/Documents/grand_forum/data/'.$myFile, 'w');
    fwrite($fh, $csv);
    fclose($fh);


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
