<?php

class DeleteProjectMemberAPI extends API{

    function __construct(){
        $this->addPOST("name", true, "The User Name of the user to add", "UserName");
        $this->addPOST("project", true, "The name of the project", "MEOW");
        $this->addPOST("comment", true, "A comment for why the user is no longer in this project", "My Reason");
        $this->addPOST("effective_date", false, "The date when the project change should be made in the format YYYY-MM-DD.  If this value is not included, the current time is assumed.", "2012-10-30");
        $this->addPOST("id",false,"The id of the role request(You probably should not touch this parameter unless you know exactly what you are doing)", "15");
    }

    function processParams($params){
        $_POST['role'] = $_POST['project'];
        $_POST['user'] = $_POST['name'];
    }

	function doAction($noEcho=false){
		global $wgRequest, $wgUser, $wgServer, $wgScriptPath;
		$groups = $wgUser->getGroups();
        $me = Person::newFromWgUser();
		$project = Project::newFromName($_POST['role']);
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

        // Actually Add the Project Member
        $person = Person::newFromName($_POST['user']);
        $comment = str_replace("'", "&#39;", $_POST['comment']);
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
        //MailingList::unsubscribeAll($person);
        $effectiveDate = "CURRENT_TIMESTAMP";
        if(isset($_POST['effective_date']) && $_POST['effective_date'] != ""){
            $effectiveDate = "'{$_POST['effective_date']} 00:00:00'";
        }
        else{
            $sql = "SELECT CURRENT_TIMESTAMP";
            $data = DBFunctions::execSQL($sql);
            $effectiveDate = "'{$data[0]['CURRENT_TIMESTAMP']}'";
        }
        $creator = self::getCreator($me);
        
        $sql = "UPDATE grand_project_members
            SET `comment` = '$comment',
                `end_date` = $effectiveDate
            WHERE `project_id` = '{$project->getId()}'
            AND user_id = '{$person->getId()}'
            AND (`end_date` > CURRENT_DATE OR `end_date` = '0000-00-00 00:00:00')";
        DBFunctions::execSQL($sql, true);
        
        $sql = "DELETE FROM mw_user_groups
                WHERE ug_user = '{$person->getId()}'
                AND ug_group = '{$project->getName()}'";
        DBFunctions::execSQL($sql, true);
        
        foreach($person->getRoles() as $role){
            DBFunctions::delete('grand_role_projects',
                                array('role_id' => EQ($role->getId()),
                                      'project_id' => EQ($project->getId())));
            foreach($project->getAllPreds() as $pred){
                DBFunctions::delete('grand_role_projects',
                                    array('role_id' => EQ($role->getId()),
                                          'project_id' => EQ($pred->getId())));
            }
        }
        
        foreach($project->getAllPreds() as $pred){
            $sql = "UPDATE grand_project_members
                    SET `comment` = '$comment',
                        `end_date` = $effectiveDate
                    WHERE `project_id` = '{$pred->getId()}'
                    AND user_id = '{$person->getId()}'
                    AND (`end_date` > CURRENT_DATE OR `end_date` = '0000-00-00 00:00:00')";
            DBFunctions::execSQL($sql, true);
            $sql = "DELETE FROM mw_user_groups
                    WHERE ug_user = '{$person->getId()}'
                    AND ug_group = '{$pred->getName()}'";
            DBFunctions::execSQL($sql, true);
            Cache::delete("project{$pred->getId()}_people", true);
        }
        Cache::delete("project{$project->getId()}_people", true);
        $person->projects = null;
        //MailingList::subscribeAll($person);
        if(!$noEcho){
            echo "{$person->getReversedName()} deleted from {$project->getName()}\n";
        }
        $date = date('Y-m-d H:i:s', strtotime($effectiveDate) + 3600*24);
        $projectsDuring = $person->getProjectsDuring($date, "2100-01-01");
        
        Notification::addNotification($creator, $person, "Project Membership Removed", "Effective $effectiveDate you are no longer a member of '{$project->getName()}'", "{$person->getUrl()}");
        Notification::addNotification($creator, $creator, "Project Membership Removal Accepted", "Effective $effectiveDate {$person->getReversedName()} is no longer a member of '{$project->getName()}'", "{$person->getUrl()}");
        if(count($projectsDuring) > 0){
            Notification::addNotification($creator, $person, "HQP Project Membership Notice", "Effective $effectiveDate you are no longer a member of any projects", "{$person->getUrl()}");
        }
        
        $supervisors = $person->getSupervisors();
        $supervisor_names = array();
        if(count($supervisors) > 0){
            foreach($supervisors as $supervisor){
                if($supervisor->getName() != $creator->getName()){
                    Notification::addNotification($creator, $supervisor, "Project Membership Removed", "Effective $effectiveDate {$person->getReversedName()} is no longer a member of '{$project->getName()}'", "{$person->getUrl()}");
                    
                }
                if(count($projectsDuring) > 0){
                    Notification::addNotification($creator, $supervisor, "HQP Project Membership Notice", "Effective $effectiveDate {$person->getReversedName()} is no longer a member of any projects", "{$person->getUrl()}");
                }
                $supervisor_names[] = $supervisor->getName();
            }
        }
        $leaders = $project->getLeaders();
        if(count($leaders) > 0){
            foreach($leaders as $leader){
                if(array_search($leader->getName(), $supervisor_names) !== false){
                    Notification::addNotification($creator, $leader, "Project Membership Removed", "Effective $effectiveDate {$person->getReversedName()} is no longer a member of '{$project->getName()}'", "{$person->getUrl()}");
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
	
	// Returns the creator of the role request.  
	// If the creator cannot be determined, then 'me' is returned
	function getCreator($me){
	    if(isset($_POST['id'])){
	        $data = DBFunctions::select(array('grand_role_request'),
	                                    array('requesting_user'),
	                                    array('id' => EQ($_POST['id'])));
	        if(count($data) > 0){
	            return Person::newFromId($data[0]['requesting_user']);
	        }
	    }   
	    return $me;
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
