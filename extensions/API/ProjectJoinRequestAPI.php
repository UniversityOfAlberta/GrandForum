<?php

class ProjectJoinRequestAPI extends API{

    function __construct(){
        $this->addPOST("project", true, "The name of the project to join", "Project1");
        $this->addPOST("reason", false, "The reason for joining", "Because I deserve it");
    }

    function processParams($params){
        // DO NOTHING
    }

	function doAction($doEcho=true){
		global $wgRequest, $wgUser, $wgOut, $wgMessage;
		$me = Person::newFromWgUser();
		$project = Project::newFromName(@$_POST['project']);
		if($project == null || $project->getId() == 0){
			$this->addError("A valid Project must be provided.");
		    return false;
		}
		
		$_POST['reason'] = (!isset($_POST['reason'])) ? "" : $_POST['reason'];
		// Finished manditory checks
		
		$p_current = array();
		$p_new = array();
        $projects = $me->getProjects(false, true);
        foreach($projects as $proj){
            $p_current[] = $proj->getId();
            $p_new[] = $proj->getId();
        }
        $p_new[] = $project->getId();
        $p_current = implode(", ", $p_current);
        $p_new = implode(", ", $p_new);
		
		DBFunctions::insert('grand_role_request',
		                    array('requesting_user' => $me->getId(),
		                          'current_role'    => $p_current,
		                          'role'            => $p_new,
                                  'comment'         => "{$project->getName()}::{$_POST['reason']}",
                                  'user'            => $me->getId(),
                                  'type'            => "PROJECT",
		                          'created' => 0));
		
		$this->addMessage("Join Request Submitted.  Once an Admin sees this request, you will be accepted into the project, or if there is a problem they will email you.");
        return true;
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
