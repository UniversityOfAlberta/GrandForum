<?php

class DeleteRoleAPI extends API{

    function __construct(){
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
		if($me->isRoleAtLeast(STAFF) || ($me->isRoleAtLeast(NI) && $person->isRole(HQP) && $_POST['role'] == HQP)){
            // Actually Add the Project Member
            $role = $_POST['role'];
            $comment = str_replace("'", "&#39;", $_POST['comment']);
            if(!$noEcho){
                if($person->getName() == null){
                    echo "There is no person by the name of '{$_POST['user']}'\n";
                    exit;
                }
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
	                AND user_id = '{$person->getId()}'
	                AND (`end_date` > CURRENT_DATE OR `end_date` = '0000-00-00 00:00:00')";
            DBFunctions::execSQL($sql, true);
            
            DBFunctions::delete('mw_user_groups',
                                array('ug_user' => EQ($person->getId()),
                                      'ug_group' => EQ($role)));
            if($role == HQP){
                DBFunctions::update('grand_relations',
                                    array('end_date' => EQ(COL('CURRENT_TIMESTAMP'))),
                                    array('user2' => EQ($person->getId()),
                                          'type' => EQ('Supervises'),
                                          'start_date' => GT(COL('end_date'))));
            }
            Cache::delete("personRolesDuring".$person->getId(), true);
            Person::$rolesCache = array();
            $person->roles = null;
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
