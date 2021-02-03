<?php

class AddRoleAPI extends API{

    function __construct(){
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
		if($me->isRoleAtLeast(STAFF) || ($me->isRoleAtLeast(NI) && $person->isRole(INACTIVE) && $_POST['role'] == HQP)){
            // Actually Add the Role
            $role = $_POST['role'];
            if(!$noEcho){
                if($person->getName() == null){
                    echo "There is no person by the name of '{$_POST['user']}'\n";
                    exit;
                }
            }
            if($person->isRole($role)){
		        return;
		    }
            // Add entry into grand_roles
            DBFunctions::insert('grand_roles',
                                array('user_id' => $person->getId(),
                                      'role' => $role,
                                      'start_date' => EQ(COL('CURRENT_TIMESTAMP'))));
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
                DBFunctions::insert('mw_user_groups',
                                    array('ug_user' => $person->getId(),
                                          'ug_group' => $role));
            }
            if(!$noEcho){
                echo "{$person->getReversedName()} added to $role\n";
            }
            Cache::delete("personRolesDuring".$person->getId(), true);
            Person::$rolesCache = array();
            $person->roles = null;
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
            $data = DBFunctions::select(array('grand_notifications'),
                                        array('id'),
                                        array('user_id' => EQ($creator->getId()),
                                              'message' => LIKE("%{$person->getName()}%"),
                                              'url' => EQ(''),
                                              'creator' => EQ(''),
                                              'active' => EQ('1')));
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
