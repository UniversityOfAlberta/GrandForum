<?php

class CreateProjectAPI extends API{

    function __construct(){
        $this->addPOST("acronym",true,"The name of the project","MEOW");
	    $this->addPOST("fullName",true,"The full name of the project","Media Enabled Organizational Workflow");
	    $this->addPOST("status",true,"The status of this project","Proposed");
	    $this->addPOST("type",true,"The type of this project","Research");
	    $this->addPOST("bigbet",true,"Whether this is a big bet project or not","No");
	    $this->addPOST("phase", true, "The phase of this project", "1");
	    $this->addPOST("effective_date", true, "The date that this action should take place", "2012-10-15");
	    $this->addPOST("description",false,"The overview for this project","MEOW is great");
	    $this->addPOST("long_description",false,"The long description for this project","MEOW is great");
	    // $this->addPOST("theme1",false,"The percent value for theme 1","20");
	    // $this->addPOST("theme2",false,"The percent value for theme 2","20");
	    // $this->addPOST("theme3",false,"The percent value for theme 3","20");
	    // $this->addPOST("theme4",false,"The percent value for theme 4","20");
	    // $this->addPOST("theme5",false,"The percent value for theme 5","20");
	    $this->addPOST("challenge",false,"Primary Challenge","0");
	    $this->addPOST("parent_id",false,"Parent Project ID","0");
    }

    function processParams($params){
        $_POST['acronym'] = @$_POST['acronym'];
        $_POST['fullName'] = @$_POST['fullName'];
        $_POST['status'] = @$_POST['status'];
        $_POST['type'] = @$_POST['type'];
        $_POST['bigbet'] = @$_POST['bigbet'];
        $_POST['phase'] = @$_POST['phase'];
        $_POST['effective_date'] = @$_POST['effective_date'];
        $_POST['description'] = @$_POST['description'];
        $_POST['long_description'] = @$_POST['long_description'];
        // $_POST['theme1'] = @$_POST['theme1'];
        // $_POST['theme2'] = @$_POST['theme2'];
        // $_POST['theme3'] = @$_POST['theme3'];
        // $_POST['theme4'] = @$_POST['theme4'];
        // $_POST['theme5'] = @$_POST['theme5'];
    }

	function doAction($noEcho=false){
	    global $wgUser, $wgMessage;
	    $me = Person::newFromUser($wgUser);
	    $parent_id = (isset($_POST['parent_id'])) ? $_POST['parent_id'] : 0;
	    $parentProj = Project::newFromId($parent_id);
	    $_POST['acronym'] = @$_POST['acronym'];
	    if(!preg_match("/^[0-9À-Ÿa-zA-Z\-\. ]+$/", $_POST['acronym'])){
	        $wgMessage->addError("The project acronym cannot contain any special characters");
	        return false;
	    }
	    if(!$me->isRoleAtLeast(STAFF) && !$me->leadershipOf($parentProj)){
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
		$data = DBFunctions::select(array('mw_an_extranamespaces'),
		                            array('MAX(nsId)' => 'nsId'));
	    $nsId = 0;
	    if(DBFunctions::getNRows() > 0){
	        $row = $data[0];
	        $nsId = ($row['nsId'] % 2 == 1) ? $row['nsId'] + 1 : $row['nsId'] + 2;
	    }
	    $challenge = (isset($_POST['challenge'])) ? $_POST['challenge'] : 0;
	    
	    $status = (isset($_POST['status'])) ? $_POST['status'] : 'Proposed';
	    $type = (isset($_POST['type'])) ? $_POST['type'] : 'Research';
	    $bigbet = (isset($_POST['bigbet']) && $_POST['bigbet'] == "Yes") ? 1 : 0;
	    $phase = (isset($_POST['phase'])) ? $_POST['phase'] : PROJECT_PHASE;
	    $effective_date = (isset($_POST['effective_date'])) ? $_POST['effective_date'] : COL('CURRENT_TIMESTAMP');
	    // It is important not to get the database into an unstable state, so start a transaction
	    DBFunctions::begin();
	    $data = DBFunctions::select(array('mw_an_extranamespaces'),
	                               array('nsId'),
	                               array('nsName' => EQ(str_replace(' ', '_', $_POST['acronym']))));
	    $stat = true;
	    if(count($data) > 0){
	        $nsId = $data[0]['nsId'];
	    }
	    else{
	        $stat = DBFunctions::insert('mw_an_extranamespaces',
	                                    array('nsId' => $nsId,
	                                          'nsName' => str_replace(' ', '_', $_POST['acronym']),
	                                          'public' => '1'),
	                                    true);
	    }
	    if($stat){
	        $stat = DBFunctions::insert('grand_project',
	                                    array('id' => $nsId,
	                                          'parent_id' => $parent_id,
	                                          'name' => $_POST['acronym'],
	                                          'phase' => $phase),
	                                    true);
	    }
	    if($stat){
	        $stat = DBFunctions::insert('grand_project_evolution',
	                                    array('last_id' => '-1',
	                                          'project_id' => '-1',
	                                          'new_id' => $nsId,
	                                          'action' => 'CREATE',
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
	                                          'type' => $type,
	                                          'bigbet' => $bigbet),
	                                    true);
	    }
	    if($stat){
	        $last_challenge_data = DBFunctions::select(array('grand_project_challenges'),
	                                                   array('MAX(id)' => 'max_id'),
	                                                   array('project_id' => EQ($nsId)));
	    	if(count($last_challenge_data) > 0 && isset($last_challenge_data[0]['max_id'])){
	    		$last_challenge_id = $last_challenge_data[0]['max_id'];
	    		DBFunctions::update('grand_project_challenges',
	    		                    array('end_date' => EQ(COL('CURRENT_TIMESTAMP'))),
	    		                    array('id' => EQ($last_challenge_id)),
	    		                    array(),
	    		                    true);
	    	}
	    	DBFunctions::insert('grand_project_challenges',
	    	                    array('project_id' => $nsId,
	    	                          'challenge_id' => $challenge,
	    	                          'start_date' => EQ(COL('CURRENT_TIMESTAMP'))),
	    	                    true);
	    }
	    if($stat){
	        Project::$cache = array();
	        $project = Project::newFromId($nsId);
	        $_POST['project'] = $_POST['acronym'];
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
