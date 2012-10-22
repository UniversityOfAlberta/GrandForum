<?php

class AddProjectLeaderAPI extends API{

    function AddProjectLeaderAPI(){
        $this->addPOST("name",true,"The User Name of the user to add","UserName");
        $this->addPOST("project",true,"The name of the project","Project");
        $this->addPOST("co_lead", false,"Whether or not this user should be a co leader or not.  If not provided, 'False' is assumed", "False");
        $this->addPOST("manager", false,"Whether or not this user should be a manager or not.  If not provided, 'False' is assumed", "False");
    }
    
    function processParams($params){
        $_POST['role'] = str_replace("'", "", $_POST['project']);
        $_POST['user'] = str_replace("'", "", $_POST['name']);
        $_POST['co_lead'] = str_replace("'", "", $_POST['co_lead']);
        $_POST['manager'] = str_replace("'", "", $_POST['manager']);
    }

	function doAction($noEcho=false){
		global $wgRequest, $wgUser, $wgServer, $wgScriptPath;
		$groups = $wgUser->getGroups();
        $me = Person::newFromId($wgUser->getId());
		if($me->isRoleAtLeast(STAFF)){
            // Actually Add the Project Member
            $person = Person::newFromName($_POST['user']);
            $project = Project::newFromName($_POST['role']);
            if(!isset($_POST['co_lead']) || ($_POST['co_lead'] != "False" && $_POST['co_lead'] != "True")){
                $_POST['co_lead'] = 'False';
            }
            if(!isset($_POST['manager']) || ($_POST['manager'] != "True")){
                $_POST['manager'] = 0;
            }
            else{
                $_POST['manager'] = 1;
            }
            
            if(!$noEcho){
                if($person->getName() == null){
                    echo "There is no person by the name of '{$_POST['user']}'\n";
                    exit;
                }
                else if($project->getName() == null){
                    echo "There is no project by the name of '{$_POST['role']}'\n";
                    exit;
                }
            }
            // Add entry into grand_projects
            $sql = "INSERT INTO grand_project_leaders (`user_id`,`project_id`,`co_lead`,`manager`,`start_date`)
					VALUES ('{$person->getId()}','{$project->getId()}','{$_POST['co_lead']}','{$_POST['manager']}', CURRENT_TIMESTAMP)";
            DBFunctions::execSQL($sql, true);

            if(!$noEcho){
                echo "{$person->getReversedName()} is now a project leader of {$project->getName()}\n";
            }
            
            $sql = "SELECT CURRENT_TIMESTAMP";
            $data = DBFunctions::execSQL($sql);
            $effectiveDate = "'{$data[0]['CURRENT_TIMESTAMP']}'";
            $type = "leader";
            if($_POST['co_lead'] == "True"){
                $type = 'co-leader';
            }
            if($_POST['manager'] == "True"){
                $type = 'manager';
            }
            Notification::addNotification($me, $person, "Project Leader Added", "Effective $effectiveDate you are a project {$type} of'{$project->getName()}'", "{$person->getUrl()}");
            $leaders = $project->getLeaders();
            if(count($leaders) > 0){
                foreach($leaders as $leader){
                    if($leader->getName() != $person->getName()){
                        Notification::addNotification($me, $leader, "Project Leader Added", "Effective $effectiveDate {$person->getReversedName()} is a {$type} of '{$project->getName()}'", "{$person->getUrl()}");
                    }
                }
            }
		}
		else {
		    if(!$noEcho){
			    echo "You must be a bureaucrat to use this API\n";
			}
		}
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
