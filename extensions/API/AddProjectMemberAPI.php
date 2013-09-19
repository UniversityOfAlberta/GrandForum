<?php

class AddProjectMemberAPI extends API{

    function AddProjectMemberAPI(){
        $this->addPOST("name",true,"The User Name of the user to add","UserName");
        $this->addPOST("project",true,"The name of the project","Project");
        $this->addPOST("id",false,"The id of the role request(You probably should not touch this parameter unless you know exactly what you are doing)", "15");
    }
    
    function processParams($params){
        $_POST['role'] = $_POST['project'];
        $_POST['user'] = $_POST['name'];
    }

	function doAction($noEcho=false){
		global $wgRequest, $wgUser, $wgServer, $wgScriptPath;
		$groups = $wgUser->getGroups();
        $me = Person::newFromId($wgUser->getId());
		if($me->isRoleAtLeast(STAFF)){
            // Actually Add the Project Member
            $person = Person::newFromName($_POST['user']);
            $project = Project::newFromName($_POST['role']);
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
            MailingList::subscribe($project, $person);
            // Add entry into grand_projects
            $sql = "INSERT INTO grand_project_members (`user_id`,`project_id`,`start_date`)
					VALUES ('{$person->getId()}','{$project->getId()}', CURRENT_TIMESTAMP)";
            DBFunctions::execSQL($sql, true);
            
            // Add entry for user_groups
            $user = User::newFromId($person->getId());
            $groups = $user->getGroups();
            $skip = false;
            foreach($groups as $group){
                if($project->getName() == $group){
                    // Do nothing
                    $skip = true;
                    break;
                }
            }
            if(!$skip){
                $sql = "INSERT INTO mw_user_groups (`ug_user`,`ug_group`)
	                    VALUES ('{$person->getId()}','{$project->getName()}')";
                DBFunctions::execSQL($sql, true);
            }
            if(!$noEcho){
                echo "{$person->getReversedName()} added to {$project->getName()}\n";
            }
            $sql = "SELECT CURRENT_TIMESTAMP";
            $data = DBFunctions::execSQL($sql);
            $effectiveDate = "'{$data[0]['CURRENT_TIMESTAMP']}'";
            $creator = self::getCreator($me);
            
            Notification::addNotification($creator, $person, "Project Membership Added", "Effective $effectiveDate you join '{$project->getName()}'", "{$person->getUrl()}");
            Notification::addNotification($creator, $creator, "Project Membership Addition Accepted", "Effective $effectiveDate {$person->getReversedName()} joins '{$project->getName()}'", "{$person->getUrl()}");
            $supervisors = $person->getSupervisors();
            $supervisor_names = array();
            if(count($supervisors) > 0){
                foreach($supervisors as $supervisor){
                    if($creator->getName() != $supervisor->getName()){
                        Notification::addNotification($creator, $supervisor, "Project Membership Added", "Effective $effectiveDate {$person->getReversedName()} joins '{$project->getName()}'", "{$person->getUrl()}");
                    }
                    $supervisor_names[] = $supervisor->getName();
                }
            }
            $leaders = $project->getLeaders();
            if(count($leaders) > 0){
                foreach($leaders as $leader){
                    if(array_search($leader->getName(), $supervisor_names) !== false){
                        Notification::addNotification($creator, $leader, "Project Membership Added", "Effective $effectiveDate {$person->getReversedName()} joins '{$project->getName()}'", "{$person->getUrl()}");
                    }
                }
            }
            $sql = "SELECT `id`
	                FROM grand_notifications
	                WHERE user_id = '{$creator->getId()}'
	                AND message LIKE '%{$person->getName()}%'
	                AND url = ''
	                AND creator = ''
	                AND active = '1'";
	        $data = DBFunctions::execSQL($sql);
	        if(count($data) > 0){
	            // Remove the Notification that the user was sent after the request
	            Notification::deactivateNotification($data[0]['id']);
	        }
		}
		else {
		    if(!$noEcho){
			    echo "You must be a bureaucrat to use this API\n";
			}
		}
	}
	
	// Returns the creator of the role request.  
	// If the creator cannot be determined, then 'me' is returned
	function getCreator($me){
	    if(isset($_POST['id'])){
	        $sql = "SELECT `requesting_user`
	                FROM `grand_role_request`
	                WHERE `id` = '{$_POST['id']}'";
	        $data = DBFunctions::execSQL($sql);
	        if(count($data) > 0){
	            return Person::newFromName($data[0]['requesting_user']);
	        }
	    }   
	    return $me;
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
