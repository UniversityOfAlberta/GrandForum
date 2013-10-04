<?php

class ProjectChallengeAPI extends API{

    function ProjectChallengeAPI(){
        $this->addPOST("project",true,"The name of the project","MEOW");
	    $this->addPOST("challenge_id",true,"Primary challenge ID");
    }

    function processParams($params){
        
    }

	function doAction($noEcho=false){
		$project = Project::newFromName($_POST['project']);
		if(!$noEcho){
		    if($project == null || $project->getName() == null){
		        echo "A valid project must be provided\n";
		        exit;
		    }
		    $person = Person::newFromName($_POST['user_name']);
            $isLead = false;
            foreach($me->getLeadProjects() as $p){
                if($p->getId() == $project->getId()){
                    $isLead = true;
                    break;
                }
            }
            if(!$isLead){
                echo "You must be logged in as a project leader\n";
                exit;
            }
		}
		
		if(isset($_POST['challenge_id'])){
		    $challenge_id = $_POST['challenge_id'];
		}
		else{
		    $challenge_id = 0;
		}
		
		
        DBFunctions::begin();
        $sql = "SELECT id, challenge_id
                FROM grand_project_challenges
                WHERE project_id = '{$project->getId()}'
                ORDER BY id DESC";
        $data = DBFunctions::execSQL($sql);
        $last_chlg_id = (isset($data[0]['id']))? $data[0]['id'] : null;
        $change_made = (isset($data[0]['challenge_id']) &&  $data[0]['challenge_id'] == $challenge_id)? false : true; 
        if($change_made){
            if(isset($data[0]['id'])){
                $last_chlg_id = $data[0]['id'];
                $sql = "UPDATE grand_project_challenges
                    SET `end_date` = CURRENT_TIMESTAMP
                    WHERE project_id = '{$project->getId()}' AND id = '{$last_chlg_id}'";
                DBFunctions::execSQL($sql, true);
            }
            $sql = "INSERT INTO grand_project_challenges (`project_id`,`challenge_id`,`start_date`)
                    VALUES ('{$project->getId()}', '{$challenge_id}', CURRENT_TIMESTAMP)";
            DBFunctions::execSQL($sql, true);
        }
        if(!$noEcho){
            echo "Project challenge updated\n";
        }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
