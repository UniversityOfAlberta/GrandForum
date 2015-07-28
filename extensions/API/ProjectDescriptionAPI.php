<?php

class ProjectDescriptionAPI extends API{

    function ProjectDescriptionAPI(){
        $this->addPOST("project",true,"The name of the project","MEOW");
	    $this->addPOST("description",true,"The short overview for this project","MEOW is great");
	    $this->addPOST("long_description",true,"The long description for this project","MEOW is great");
	    $this->addPOST("themes",false,"The theme distribution of this project", "10,20,30,20,20");
	    $this->addPOST("fullName",false,"The full name of the project", "Media Enabled Organizational Workflow");
    }

    function processParams($params){
        if(isset($_POST['description']) && $_POST['description'] != ""){
            $_POST['description'] = str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['description']));
        }
        if(isset($_POST['long_description']) && $_POST['long_description'] != ""){
            $_POST['long_description'] = str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['long_description']));
        }
        if(isset($_POST['project']) && $_POST['project'] != ""){
            $_POST['project'] = str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['project']));
        }
        if(isset($_POST['fullName']) && $_POST['fullName'] != ""){
            $_POST['fullName'] = str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['fullName']));
        }
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
		
		if(isset($_POST['themes'])){
		    $themes = explode(",", $_POST['themes']);
		}
		else{
		    $themes = array($project->getTheme(1), $project->getTheme(2), $project->getTheme(3), $project->getTheme(4), $project->getTheme(5));
		}
		
		if(isset($_POST['fullName'])){
		    $fullName = $_POST['fullName'];
		}
		else{
		    $fullName = $project->getFullName();
		}
        DBFunctions::begin();
        DBFunctions::update('grand_project_descriptions',
                            array('end_date' => EQ(COL('CURRENT_TIMESTAMP'))),
                            array('project_id' => EQ($project->getId()),
                                  'id' => EQ($project->getLastHistoryId())),
                            array(),
                            true);
        DBFunctions::insert('grand_project_descriptions',
                            array('project_id' => $project->getId(),
                                  'evolution_id' => $project->evolutionId,
                                  'full_name' => $fullName,
                                  'themes' => "{$themes[0]}\n{$themes[1]}\n{$themes[2]}\n{$themes[3]}\n{$themes[4]}",
                                  'description' => $_POST['description'],
                                  'long_description' => @$_POST['long_description'],
                                  'start_date' => 'CURRENT_TIMESTAMP'),
                            true);
        DBFunctions::commit();
        if(!$noEcho){
            echo "Project description updated\n";
        }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
