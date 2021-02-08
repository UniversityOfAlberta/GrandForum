<?php

class EvolveProjectAPI extends API{

    function EvolveProjectAPI(){
        $this->addPOST("project",true,"The name of the project to evolve", "OLDMEOW");
        $this->addPOST("acronym",true,"The new name of the project","MEOW");
	    $this->addPOST("effective_date",true, "The date that this action should take place", "2012-10-15");
	    $this->addPOST("action",false, "What type of action this is (Default: EVOLVE)", "MERGE");
	    $this->addPOST("clear",false, "Whether or not to use fresh data for the new project, or to carry over the past data", "0");
    }

    function processParams($params){
        $_POST['acronym'] = @$_POST['acronym'];
        $_POST['effective_date'] = $_POST['effective_date'];
        $_POST['action'] = @$_POST['action'];
        $_POST['clear'] = @$_POST['clear'];
    }

	function doAction($noEcho=false){
	    global $wgUser;
	    $me = Person::newFromUser($wgUser);
	    if(!$me->isRoleAtLeast(STAFF)){
	        return;
	    }
	    $oldProject = Project::newFromName($_POST['project']);
	    
	    if(!isset($_POST['action']) || $_POST['action'] == ""){
	        $_POST['action'] = "EVOLVE";
	    }
	    $action = $_POST['action'];
	    
		$project = Project::newFromName($_POST['acronym']);
		$alreadyExists = false;
		if($project != null && $project->getName() != ""){
		    $alreadyExists = true;
		}
		
		if(!$alreadyExists){
		    return false;
	    }
	    else{
	        $nsId = $project->getId();
	    }
	    $status = $project->getStatus();
	    $type = $project->getType();
	    $clear = (isset($_POST['clear'])) ? ($_POST['clear'] == "Yes") : false;
	    if(!$clear){
	        $clear = 0;
	    }
	    else{
	        $clear = 1;
	    }
	    
	    $effective_date = (isset($_POST['effective_date'])) ? $_POST['effective_date'] : COL('CURRENT_TIMESTAMP');
	    DBFunctions::begin();
	    $stat = true;
	    if($stat){
	        $stat = DBFunctions::insert('grand_project_evolution',
	                                    array('last_id' => $oldProject->evolutionId,
	                                          'project_id' => $oldProject->getId(),
	                                          'new_id' => $nsId,
	                                          'action' => $action,
	                                          'clear' => $clear,
	                                          'effective_date' => $effective_date),
	                                    true);
	    }
	    if($stat){
	        $data = DBFunctions::select(array('grand_project_evolution'),
	                                    array('MAX(id)' => 'id'));
	        $stat = DBFunctions::insert('grand_project_status',
	                                    array('evolution_id' => $data[0]['id'],
	                                          'project_id' => $nsId,
	                                          'status' => $status,
	                                          'type' => $type),
	                                    true);
	    }
	    if($stat){
	        Project::$cache = array();
	        $project = Project::newFromId($nsId);
	        //MailingList::createMailingList($project);
	    }
	    DBFunctions::commit();
	    return $stat;
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
