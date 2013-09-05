<?php

class AddRoleAPI extends API{

    function AddRoleAPI(){
        $this->addPOST("name",true,"The User Name of the User","UserName");
        $this->addPOST("role",true,"The name of the role","Role");
        $this->addPOST("id",false,"The id of the role request(You probably should not touch this parameter unless you know exactly what you are doing)", "15");
    }
    
    function processParams($params){
        $_POST['user'] = $_POST['name'];
    }

	function doAction($noEcho=false){
		global $wgRequest, $wgUser, $wgServer, $wgScriptPath;
		$groups = $wgUser->getGroups();
		$me = Person::newFromId($wgUser->getId());
		$person = Person::newFromName($_POST['user']);
		if($me->isRoleAtLeast(STAFF) || ($me->isRoleAtLeast(CNI) && $person->isRole(INACTIVE) && $_POST['role'] == HQP)){
            // Actually Add the Project Member
            $role = $_POST['role'];
            if(!$noEcho){
                if($person->getName() == null){
                    echo "There is no person by the name of '{$_POST['user']}'\n";
                    exit;
                }
            }
            if($role == PNI || $role == CNI){
                $command =  "echo \"{$person->getEmail()}\" | /usr/lib/mailman/bin/add_members --welcome-msg=n --admin-notify=n -r - grand-forum-researchers";
		        exec($command);
		    }
		    if($role == HQP){
                $command =  "echo \"{$person->getEmail()}\" | /usr/lib/mailman/bin/add_members --welcome-msg=n --admin-notify=n -r - grand-forum-hqps";
		        exec($command);
		    }
            // Add entry into grand_roles
            $sql = "INSERT INTO grand_roles (`user`,`role`,`start_date`)
	                VALUES ('{$person->getId()}','$role', CURRENT_TIMESTAMP)";
            DBFunctions::execSQL($sql, true);
            
            // Add entry for user_groups
            $user = User::newFromId($person->getId());
            $groups = $user->getGroups();
            $skip = false;
            foreach($groups as $group){
                if($role == $group){
                    // Do nothing
                    $skip = true;
                    break;
                }
            }
            if(!$skip){
                $sql = "INSERT INTO mw_user_groups (`ug_user`,`ug_group`)
	                    VALUES ('{$person->getId()}','$role')";
                DBFunctions::execSQL($sql, true);
            }
            if(!$noEcho){
                echo "{$person->getReversedName()} added to $role\n";
            }
            $sql = "SELECT CURRENT_TIMESTAMP";
            $data = DBFunctions::execSQL($sql);
            $effectiveDate = "'{$data[0]['CURRENT_TIMESTAMP']}'";
            $creator = self::getCreator($me);
            
            Notification::addNotification($creator, $person, "Role Added", "Effective $effectiveDate you assume the role '$role'", "{$person->getUrl()}");
            Notification::addNotification($creator, $creator, "Role Addition Accepted", "Effective $effectiveDate {$person->getReversedName()} assumes the role '$role'", "{$person->getUrl()}");
            $supervisors = $person->getSupervisors();
            if(count($supervisors) > 0){
                foreach($supervisors as $supervisor){
                    if($supervisor->getName() != $creator->getName()){
                        Notification::addNotification($creator, $supervisor, "Role Added", "Effective $effectiveDate {$person->getReversedName()} assumes the role '$role'", "{$person->getUrl()}");
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
