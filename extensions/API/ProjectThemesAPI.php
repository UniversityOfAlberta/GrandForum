<?php

class ProjectThemesAPI extends API{

    function ProjectMilestoneAPI(){
        $this->addPOST("project",true,"The name of the project","MEOW");
        $this->addPOST("themes",true,"The percent values of the themes in the form: Theme1,Theme2,Theme3,Theme4,Theme5","20,40,10,30,0");
    }

    function processParams($params){
        if(isset($_POST['themes']) && $_POST['themes'] != ""){
            $_POST['themes'] = @addslashes(str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['themes'])));
        }
        if(isset($_POST['project']) && $_POST['project'] != ""){
            $_POST['project'] = @addslashes(str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['project'])));
        }
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
		$themes = explode(",", $_POST['themes']);
		
        $sql = "UPDATE grand_project_themes
                SET `end_date` = CURRENT_TIMESTAMP
                WHERE project_id = '{$project->getId()}'";
        DBFunctions::execSQL($sql, true);
        $sql = "INSERT INTO grand_project_themes (`project_id`,`name`,`themes`,`start_date`)
                VALUES ('{$project->getId()}','{$project->getName()}','{$themes[0]}\n{$themes[1]}\n{$themes[2]}\n{$themes[3]}\n{$themes[4]}',CURRENT_TIMESTAMP)";
        DBFunctions::execSQL($sql, true);
        if(!$noEcho){
            echo "Project themes updated\n";
        }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
