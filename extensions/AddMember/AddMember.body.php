<?php
require_once("AddMember.php");

$userCreate = new UserCreate();

$wgHooks['AddNewAccount'][] = array($userCreate, 'afterCreateUser');

$notificationFunctions[] = 'UserCreate::createNotification';

class UserCreate {

	static function createNotification(){
		global $notifications, $wgUser, $wgServer, $wgScriptPath;
		$groups = $wgUser->getGroups();
		if($wgUser->isLoggedIn()){
		    $me = Person::newFromId($wgUser->getId());
		    if($me->isRoleAtLeast(STAFF)){
		        $data = DBFunctions::select(array('grand_user_request'),
		                                    array('requesting_user', 'wpName'),
		                                    array('created' => EQ(0),
		                                          '`ignore`' => EQ(0)));
			    if(count($data) > 0){
				    $notifications[] = new Notification("User Creation Request", "There is at least one user creation request pending.", "$wgServer$wgScriptPath/index.php/Special:AddMember?action=view");
			    }
		    }
		}
	}
	
	function afterCreateUser($wgUser, $byEmail=true){
		global $wgLocalTZoffset, $wgOut;
		
		$mUserType = $_POST['wpUserType'];
		$id = $wgUser->getId();
		
		DBFunctions::commit();
		DBFunctions::begin();
		
		if(isset($_POST['wpUserType'])){
		    if($_POST['wpUserType'] != ""){
			    foreach($_POST['wpUserType'] as $role){
			        if($role == ""){
			            continue;
			        }
				    //Add Role to DB
				    DBFunctions::insert('mw_user_groups',
				                        array('ug_user' => $id,
				                              'ug_group' => $role));
				    DBFunctions::insert('grand_roles',
				                        array('user_id' => $id,
				                              'role' => $role,
				                              'start_date' => EQ(COL('CURRENT_TIMESTAMP'))));
		            if($role == PNI || $role == CNI){
		                $person = Person::newFromId($wgUser->getId());
		                $command = "echo \"{$wgUser->mEmail}\" | /usr/lib/mailman/bin/add_members --admin-notify=n --welcome-msg=n -r - grand-forum-researchers";
		                exec($command);
		            }
		            else if($role == HQP){
		                $person = Person::newFromId($wgUser->getId());
		                $command = "echo \"{$wgUser->mEmail}\" | /usr/lib/mailman/bin/add_members --admin-notify=n --welcome-msg=n -r - grand-forum-hqps";
		                exec($command);
		            }
			    }
			}
		}
		
		if(isset($_POST['wpNS'])){
			$box = $_POST['wpNS'];
			while (list ($key,$val) = @each ($box)) {
			    if($val != null && $val != ""){
					$project = Project::newFromName($val);
					DBFunctions::insert('mw_user_groups',
					                    array('ug_user' => $id,
					                          'ug_group' => $val));
					DBFunctions::insert('grand_project_members',
					                    array('user_id' => $id,
					                          'project_id' => $project->getId(),
					                          'start_date' => EQ(COL('CURRENT_TIMESTAMP'))));
		        }
			}
		}
		
		$continue = UserCreate::addNewUserPage($wgUser);
		
		// Add User MailingList
		$user = User::newFromId($wgUser->getId());
		$email = $wgUser->mEmail;
		if($email != null){
			foreach($user->getGroups() as $group){
				$listname = str_replace("Project_", "", $group);
				$command =  "echo \"$email\" | /usr/lib/mailman/bin/add_members --welcome-msg=n -r - $listname";
				exec($command);
				$rows = DBFunctions::select(array('wikidev_projects'),
				                            array('projectid'),
				                            array('projectname' => EQ($listname)));
				@$row = $rows[0];
				if($row['projectid'] != null){
				    DBFunctions::insert('wikidev_projectroles',
				                        array('projectid' => $row['projectid'],
				                              'userid' => $user->getName()));
				}
			}
		}
		DBFunctions::commit();
		return true;
	}
	
	static function addNewUserPage($wgUser){
        //Do Nothing
		return true;
	}   
}

?>
