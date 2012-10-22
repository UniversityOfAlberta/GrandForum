<?php

class DeleteRoleAPI extends API{

    function DeleteRoleAPI(){
        $this->addPOST("name", true, "The User Name of the user", "UserName");
        $this->addPOST("role", true, "The name of the role", "HQP");
        $this->addPOST("comment", true, "A comment for why the user is no longer in this role", "My Reason");
        $this->addPOST("effective_date", false, "The date when the role change should be made in the format YYYY-MM-DD.  If this value is not included, the current time is assumed.", "2012-10-30");
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
		if($me->isRoleAtLeast(STAFF) || ($me->isRoleAtLeast(CNI) && $person->isRole(HQP) && $_POST['role'] == HQP)){
            // Actually Add the Project Member
            $role = $_POST['role'];
            $comment = str_replace("'", "&#39;", $_POST['comment']);
            if(!$noEcho){
                if($person->getName() == null){
                    echo "There is no person by the name of '{$_POST['user']}'\n";
                    exit;
                }
            }
            if($role == PNI || $role == CNI){
                $command =  "/usr/lib/mailman/bin/remove_members -n -N grand-forum-researchers {$person->getEmail()}";
                exec($command);
            }
            if($role == HQP){
                $command =  "/usr/lib/mailman/bin/remove_members -n -N grand-forum-hqps {$person->getEmail()}";
                exec($command);
            }
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
            
            $sql = "UPDATE grand_roles
	                SET `comment` = '$comment',
	                    `end_date` = $effectiveDate
	                WHERE `role` = '$role'
	                AND user = '{$person->getId()}'
	                ORDER BY `start_date` DESC LIMIT 1";
            DBFunctions::execSQL($sql, true);
            
            $sql = "DELETE FROM mw_user_groups
                    WHERE ug_user = '{$person->getId()}'
                    AND ug_group = '$role'";
            DBFunctions::execSQL($sql, true);
            if($role == HQP){
                $sql = "UPDATE grand_relations
                        SET end_date = CURRENT_TIMESTAMP
                        WHERE user2 = '{$person->getId()}'
                        AND type = 'Supervises'
                        AND start_date > end_date";
                DBFunctions::execSQL($sql, true);
            }
            if(!$noEcho){
                echo "{$person->getReversedName()} deleted from $role\n";
            }
            if($role != INACTIVE){ // Don't send a notification if the user is removed from being INACTIVE
                Person::$rolesCache = array();
                $person->roles = null;
                Notification::addNotification($creator, $person, "Role Removed", "Effective $effectiveDate you are no longer '$role'", "{$person->getUrl()}");
                if($creator->getName() != $me->getName()){
                    Notification::addNotification($creator, $creator, "Role Removal Accepted", "Effective $effectiveDate {$person->getReversedName()} is no longer '$role'", "{$person->getUrl()}");
                }
                else{
                    Notification::addNotification("", $creator, "Role Removed", "Effective $effectiveDate {$person->getReversedName()} is no longer '$role'", "{$person->getUrl()}");
                }
                $supervisors = $person->getSupervisors();
                if(count($supervisors) > 0){
                    foreach($supervisors as $supervisor){
                        if($supervisor->getName() != $creator->getName()){
                            Notification::addNotification($creator, $supervisor, "Role Removed", "Effective $effectiveDate {$person->getReversedName()} is no longer '$role'", "{$person->getUrl()}");
                        }
                    }
                }
                $sql = "SELECT `id`
		                FROM grand_notifications
		                WHERE user = '{$creator->getId()}'
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
