<?php

class ProjectChallengeAPI extends API{

    function __construct(){
        $this->addPOST("project",true,"The name of the project","MEOW");
        $this->addPOST("challenge_id",true,"Primary challenge ID","1");
    }

    function processParams($params){
        
    }

    function doAction($noEcho=false){
        $me = Person::newFromWgUser();
		$project = Project::newFromName($_POST['project']);
		$error = "";
		if($project == null || $project->getName() == null){
	        $error = "A valid project must be provided";
	    }
        if(!$project->userCanEdit()){
            $error = "You must be logged in as a project leader";
        }
		if(!$noEcho && $error != ""){
		    echo "$error\n";
		    exit;
		}
		if($error != ""){
		    return $error;
		}
        
        if(isset($_POST['challenge_id'])){
            $challenge_id = $_POST['challenge_id'];
        }
        else{
            $challenge_id = 0;
        }
        
        DBFunctions::begin();
        $data = DBFunctions::select(array('grand_project_challenges'),
                                    array('id', 'challenge_id'),
                                    array('project_id' => EQ($project->getId())),
                                    array('id' => 'DESC'));
        $last_chlg_id = (isset($data[0]['id']))? $data[0]['id'] : null;
        $change_made = (isset($data[0]['challenge_id']) && $data[0]['challenge_id'] === $challenge_id)? false : true; 
        if($change_made){
            if(isset($data[0]['id'])){
                $last_chlg_id = $data[0]['id'];
                DBFunctions::update('grand_project_challenges',
                                    array('end_date' => EQ(COL('CURRENT_TIMESTAMP'))),
                                    array('project_id' => EQ($project->getId()),
                                          'id' => EQ($last_chlg_id)));
            }
            DBFunctions::insert('grand_project_challenges',
                                array('project_id' => $project->getId(),
                                      'challenge_id' => $challenge_id,
                                      'start_date' => EQ(COL('CURRENT_TIMESTAMP'))));
        }
        DBFunctions::commit();
        if(!$noEcho){
            echo "Project challenge updated\n";
        }
    }
    
    function isLoginRequired(){
        return true;
    }
}
?>
