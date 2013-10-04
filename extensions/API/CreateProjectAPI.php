<?php

class CreateProjectAPI extends API{

    function CreateProjectAPI(){
        $this->addPOST("acronym",true,"The name of the project","MEOW");
	    $this->addPOST("fullName",true,"The full name of the project","Media Enabled Organizational Workflow");
	    $this->addPOST("status",true,"The status of this project","Proposed");
	    $this->addPOST("type",true,"The type of this project","Research");
	    $this->addPOST("effective_date", true, "The date that this action should take place", "2012-10-15");
	    $this->addPOST("description",false,"The description for this project","MEOW is great");
	    // $this->addPOST("theme1",false,"The percent value for theme 1","20");
	    // $this->addPOST("theme2",false,"The percent value for theme 2","20");
	    // $this->addPOST("theme3",false,"The percent value for theme 3","20");
	    // $this->addPOST("theme4",false,"The percent value for theme 4","20");
	    // $this->addPOST("theme5",false,"The percent value for theme 5","20");
	    $this->addPOST("challenge",false,"Primary Challenge","0");
	    $this->addPOST("parent_id",false,"Parent Project ID","0");
    }

    function processParams($params){
        $_POST['acronym'] = @mysql_real_escape_string($_POST['acronym']);
        $_POST['fullName'] = @mysql_real_escape_string($_POST['fullName']);
        $_POST['status'] = @mysql_real_escape_string($_POST['status']);
        $_POST['type'] = @mysql_real_escape_string($_POST['type']);
        $_POST['effective_date'] = @mysql_real_escape_string($_POST['effective_date']);
        $_POST['description'] = @mysql_real_escape_string($_POST['description']);
        
        // $_POST['theme1'] = @mysql_real_escape_string($_POST['theme1']);
        // $_POST['theme2'] = @mysql_real_escape_string($_POST['theme2']);
        // $_POST['theme3'] = @mysql_real_escape_string($_POST['theme3']);
        // $_POST['theme4'] = @mysql_real_escape_string($_POST['theme4']);
        // $_POST['theme5'] = @mysql_real_escape_string($_POST['theme5']);
    }

	function doAction($noEcho=false){
	    global $wgUser;
	    $me = Person::newFromUser($wgUser);
	    if(!$me->isRoleAtLeast(MANAGER)){
	        return;
	    }
		$project = Project::newFromName($_POST['acronym']);
		if($project != null && $project->getName() != ""){
		    if(!$noEcho){
		        echo "This project already exists";
		        exit;
		    }
		    return;
		}
		$sql = "SELECT MAX(nsId) as nsId FROM `mw_an_extranamespaces`";
	    $data = DBFunctions::execSQL($sql);
	    $nsId = 0;
	    if(DBFunctions::getNRows() > 0){
	        $row = $data[0];
	        $nsId = ($row['nsId'] % 2 == 1) ? $row['nsId'] + 1 : $row['nsId'] + 2;
	    }
	    // $theme1 = (isset($_POST['theme1'])) ? $_POST['theme1'] : 0;
	    // $theme2 = (isset($_POST['theme2'])) ? $_POST['theme2'] : 0;
	    // $theme3 = (isset($_POST['theme3'])) ? $_POST['theme3'] : 0;
	    // $theme4 = (isset($_POST['theme4'])) ? $_POST['theme4'] : 0;
	    // $theme5 = (isset($_POST['theme5'])) ? $_POST['theme5'] : 0;
	    $challenge = (isset($_POST['challenge'])) ? $_POST['challenge'] : 0;
	    $parent_id = (isset($_POST['parent_id'])) ? $_POST['parent_id'] : 0;
	    
	    $status = (isset($_POST['status'])) ? $_POST['status'] : 'Proposed';
	    $type = (isset($_POST['type'])) ? $_POST['type'] : 'Research';
	    $effective_date = (isset($_POST['effective_date'])) ? $_POST['effective_date'] : 'CURRENT_TIMESTAMP';
	    // It is important not to get the database into an unstable state, so start a transaction
	    DBFunctions::begin();
	    $sql = "SELECT nsId
	            FROM `mw_an_extranamespaces`
	            WHERE nsName = '{$_POST['acronym']}'";
	    $data = DBFunctions::execSQL($sql);
	    $stat = true;
	    if(count($data) > 0){
	        $nsId = $data[0]['nsId'];
	    }
	    else{
	        $sql = "INSERT INTO `mw_an_extranamespaces` (`nsId`,`nsName`,`public`)
	                VALUES ('{$nsId}','{$_POST['acronym']}','1')";
	        $stat = DBFunctions::execSQL($sql, true, true);
	    }
	    if($stat){
	        $sql = "INSERT INTO `grand_project` (`id`,`parent_id`,`name`)
	                VALUES ('{$nsId}','{$parent_id}','{$_POST['acronym']}')";
	        $stat = DBFunctions::execSQL($sql, true, true);
	    }
	    if($stat){
	        $sql = "INSERT INTO `grand_project_evolution` (`last_id`,`project_id`,`new_id`,`action`,`effective_date`)
	                VALUES ('-1','-1','{$nsId}','CREATE','{$effective_date}')";
	        $stat = DBFunctions::execSQL($sql, true, true);
	    }
	    if($stat){
	        $sql = "INSERT INTO `grand_project_status` (`evolution_id`,`project_id`,`status`,`type`)
	                VALUES ((SELECT MAX(id) FROM grand_project_evolution),'{$nsId}','{$status}','{$type}')";
	        $sql = DBFunctions::execSQL($sql, true, true);
	    }
	    if($stat){
	    	$sql = "SELECT MAX(id) as max_id FROM `grand_project_challenges` WHERE project_id = '{$nsId}'";
	    	$last_challenge_data = DBFunctions::execSQL($sql);
	    	if(count($last_challenge_data) > 0 && isset($last_challenge_data[0]['max_id'])){
	    		$last_challenge_id = $last_challenge_data[0]['max_id'];
	    		$sql = "UPDATE `grand_project_challenges` SET end_date=CURRENT_TIMESTAMP WHERE id='{$last_challenge_id}'";
	    		DBFunctions::execSQL($sql, true);
	    	}
	        $sql = "INSERT INTO `grand_project_challenges` (`project_id`,`challenge_id`,`start_date`)
	                VALUES ('{$nsId}','{$challenge}', CURRENT_TIMESTAMP)";
	        $sql = DBFunctions::execSQL($sql, true, true);
	    }
	    if($stat){
	        Project::$cache = array();
	        $project = Project::newFromId($nsId);
	        $_POST['project'] = $_POST['acronym'];
	        //$_POST['themes'] = "{$theme1},{$theme2},{$theme3},{$theme4},{$theme5}";
	        APIRequest::doAction('ProjectDescription', true);
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
