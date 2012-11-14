<?php

class ProjectDescriptionAPI extends API{

    function ProjectDescriptionAPI(){
        $this->addPOST("project",true,"The name of the project","MEOW");
	    $this->addPOST("description",true,"The description for this project","MEOW is great");
	    $this->addPOST("themes",false,"The theme distribution of this project", "10,20,30,20,20");
    }

    function processParams($params){
        if(isset($_POST['description']) && $_POST['description'] != ""){
            $_POST['description'] = @addslashes(str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['description'])));
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
		
		if(isset($_POST['themes'])){
		    $themes = explode(",", $_POST['themes']);
		}
		else{
		    $themes = array($project->getTheme(1), $project->getTheme(2), $project->getTheme(3), $project->getTheme(4), $project->getTheme(5));
		}
        
        $sql = "UPDATE grand_project_descriptions
                SET `end_date` = CURRENT_TIMESTAMP
                WHERE project_id = '{$project->getId()}' AND id = '{$project->getLastHistoryId()}'";
        DBFunctions::execSQL($sql, true);
        $sql = "INSERT INTO grand_project_descriptions (`project_id`,`full_name`,`themes`,`description`,`start_date`)
                VALUES ('{$project->getId()}','{$project->getFullName()}','{$themes[0]}\n{$themes[1]}\n{$themes[2]}\n{$themes[3]}\n{$themes[4]}','{$_POST['description']}',CURRENT_TIMESTAMP)";
        DBFunctions::execSQL($sql, true);
        if(!$noEcho){
            echo "Project description updated\n";
        }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
