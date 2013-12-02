<?php

class UpdateProjectAcronymAPI extends API{

    function UpdateProjectAcronymAPI(){
        $this->addPOST("old_acronym",true,"The old acronym for the project","NewProj");
	    $this->addPOST("new_acronym",true,"The new acronym for the projeect","NewProject");
    }

    function processParams($params){
        $_POST['old_acronym'] = @$_POST['old_acronym'];
        $_POST['new_acronym'] = @$_POST['new_acronym'];
    }

	function doAction($noEcho=false){
	    global $wgUser, $wgMessage;
	    $me = Person::newFromUser($wgUser);
	    $_POST['new_acronym'] = str_replace(" ", "-", $_POST['new_acronym']);
	    $project = Project::newFromName($_POST['old_acronym']);
	    $newProj = Project::newFromName($_POST['new_acronym']);
	    if(!preg_match("/^[0-9À-Ÿa-zA-Z\-]+$/", $_POST['new_acronym'])){
	        $wgMessage->addError("The project acronym cannot contain any special characters");
	        return false;
	    }
	    if($project == null){
	        return false;
	    }
	    $parentProj = $project->getParent();
	    if(!$me->isRoleAtLeast(STAFF) && !($me->leadershipOf($parentProj) || $me->leadershipOf($project))){
	        return false;
	    }
		
		if($_POST['old_acronym'] == $_POST['new_acronym']){
		    return false;
		}
		
		if($newProj == null){
		    DBFunctions::begin();
		    DBFunctions::update('grand_project',
		                        array('name' => $_POST['new_acronym']),
		                        array('id' => $project->getId()),
		                        array(),
		                        true);
		    DBFunctions::update('mw_an_extranamespaces',
		                        array('nsName' => $_POST['new_acronym']),
		                        array('nsId' => $project->getId()),
		                        array(),
		                        true);
		    DBFunctions::commit();
		    Project::$cache = array();
		    $project->name = $_POST['new_acronym'];
		    $wgMessage->addSuccess("The project acronym was changed to '{$_POST['new_acronym']}'");
		    return true;
		}
		else{
		    $wgMessage->addError("A project with the name '{$_POST['new_acronym']}' already exists");
		    return false;
		}
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
