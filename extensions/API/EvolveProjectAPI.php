<?php

class EvolveProjectAPI extends API{

    function EvolveProjectAPI(){
        $this->addPOST("project",true,"The name of the project to evolve", "OLDMEOW");
        $this->addPOST("acronym",true,"The new name of the project","MEOW");
	    $this->addPOST("fullName",true,"The full name of the project","Media Enabled Organizational Workflow");
	    $this->addPOST("status",true,"The status of this project","Proposed");
	    $this->addPOST("type",true,"The type of this project","Research");
	    $this->addPOST("effective_date", "The date that this action should take place", "2012-10-15");
    }

    function processParams($params){
        $_POST['acronym'] = @mysql_real_escape_string($_POST['acronym']);
        $_POST['fullName'] = @mysql_real_escape_string($_POST['fullName']);
        $_POST['status'] = @mysql_real_escape_string($_POST['status']);
        $_POST['type'] = @mysql_real_escape_string($_POST['type']);
        $_POST['effective_date'] = @mysql_real_escape_string($_POST['effective_date']);
    }

	function doAction($noEcho=false){
	    $oldProject = Project::newFromName($_POST['project']);
	    $theme1 = $oldProject->getTheme(1);
	    $theme2 = $oldProject->getTheme(2);
	    $theme3 = $oldProject->getTheme(3);
	    $theme4 = $oldProject->getTheme(4);
	    $theme5 = $oldProject->getTheme(5);
	    
		$project = Project::newFromName($_POST['acronym']);
		$alreadyExists = false;
		if($project != null && $project->getName() != ""){
		    $alreadyExists = true;
		}
		
		if(!$alreadyExists){
		    $sql = "SELECT MAX(nsId) as nsId FROM `mw_an_extranamespaces`";
	        $data = DBFunctions::execSQL($sql);
	        $nsId = 0;
	        if(DBFunctions::getNRows() > 0){
	            $row = $data[0];
	            $nsId = ($row['nsId'] % 2 == 1) ? $row['nsId'] + 1 : $row['nsId'] + 2;
	        }
	    }
	    else{
	        $nsId = $project->getId();
	    }
	    $status = (isset($_POST['status'])) ? $_POST['status'] : 'Proposed';
	    
	    $action = "EVOLVE";
	    if($status == "Completed"){
	        $action = "DELETE";
	    }
	    
	    $type = (isset($_POST['type'])) ? $_POST['type'] : 'Research';
	    $effective_date = (isset($_POST['effective_date'])) ? $_POST['effective_date'] : 'CURRENT_TIMESTAMP';
	    if(!$alreadyExists){
	        $sql = "INSERT INTO `mw_an_extranamespaces` (`nsId`,`nsName`,`public`)
	                VALUES ('{$nsId}','{$_POST['acronym']}','1')";
	        DBFunctions::execSQL($sql, true);
	        $sql = "INSERT INTO `grand_project` (`id`,`name`)
	                VALUES ('{$nsId}','{$_POST['acronym']}')";
	        DBFunctions::execSQL($sql, true);
	    }
	    $sql = "INSERT INTO `grand_project_status` (`project_id`,`status`,`type`)
	            VALUES ('{$nsId}','{$status}','{$type}')";
	    DBFunctions::execSQL($sql, true);
	    $sql = "INSERT INTO `grand_project_evolution` (`project_id`,`new_id`,`action`,`effective_date`)
	            VALUES ('{$oldProject->getId()}','{$nsId}','{$action}','{$effective_date}')";
	    DBFunctions::execSQL($sql, true);
	    Project::$cache = array();
	    $project = Project::newFromId($nsId);
	    $_POST['project'] = $_POST['acronym'];
	    $_POST['description'] = @mysql_real_escape_string($oldProject->getDescription());
	    $_POST['themes'] = "{$theme1},{$theme2},{$theme3},{$theme4},{$theme5}";
	    APIRequest::doAction('ProjectDescription', true);
	    //MailingList::createMailingList($project);
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
