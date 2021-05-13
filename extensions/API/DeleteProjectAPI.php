<?php

class DeleteProjectAPI extends API{

    function DeleteProjectAPI(){
        $this->addPOST("project",true,"The name of the project to delete", "MEOW");
	    $this->addPOST("effective_date",true, "The date that this action should take place", "2012-10-15");
    }

    function processParams($params){
        $_POST['project'] = @DBFunctions::escape($_POST['project']);
        $_POST['effective_date'] = @DBFunctions::escape($_POST['effective_date']);
    }

	function doAction($noEcho=false){
	    global $wgUser;
	    $me = Person::newFromUser($wgUser);
	    $project = Project::newFromName($_POST['project']);
	    if(!$me->isRoleAtLeast(STAFF) && 
	       (!$me->isRole(PL, $project) ||
	        ($project->isSubProject() && !$me->isRole(PL, $project->getParent()))
	       )){
	        return;
	    }
		
	    $nsId = $project->getId();
	        
	    $status = $project->getStatus();
	    $startDate = $project->getStartDate();
	    $endDate = $project->getEndDate();
	    
	    $type = $project->getType();
	    $effective_date = (isset($_POST['effective_date'])) ? $_POST['effective_date'] : 'CURRENT_TIMESTAMP';
	    DBFunctions::begin();
	    $stat = true;
	    $sql = "INSERT INTO `grand_project_evolution` (`last_id`,`project_id`,`new_id`,`action`,`effective_date`)
	            VALUES ('{$project->evolutionId}','{$project->getId()}','{$nsId}','DELETE','{$effective_date}')";
	    $stat = DBFunctions::execSQL($sql, true, true);
	    if($stat){
	        $evoId = DBFunctions::insertId();
	        $sql = "INSERT INTO `grand_project_status` (`evolution_id`,`project_id`,`status`,`start_date`,`end_date`,`type`)
	                VALUES ('{$evoId}','{$nsId}','Ended','{$startDate}','{$endDate}','{$type}')";
	        $stat = DBFunctions::execSQL($sql, true, true);
	    }
	    if($stat){
	        Project::$cache = array();
	        Project::$projectDataCache = array();
	        $project = Project::newFromId($nsId);
	        $_POST['project'] = $project->getName();
	        $_POST['description'] = $project->getDescription();
	        $_POST['long_description'] = $project->getLongDescription();
	        APIRequest::doAction('ProjectDescription', true);
	        //MailingList::removeMailingList($project);
	    }
	    DBFunctions::commit();
	    return $stat;
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
