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
			    $sql = "SELECT requesting_user, wpName
				        FROM mw_user_create_request
				        WHERE `created` = 'false'
				        AND `ignore` = 'false'";
			    $data = DBFunctions::execSQL($sql);
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
				    $sql = "INSERT INTO mw_user_groups (`ug_user`, `ug_group`) VALUES ('$id', '$role')";
		            DBFunctions::execSQL($sql, true);
                    $sql = "INSERT INTO grand_roles (`user`, `role`, `start_date`) VALUES ('$id', '$role', CURRENT_TIMESTAMP)";
		            DBFunctions::execSQL($sql, true);
		            if($role == PNI || $role == CNI){
		                $person = Person::newFromId($wgUser->getId());
		                $command = "echo \"{$person->getEmail()}\" | /usr/lib/mailman/bin/add_members --admin-notify=n --welcome-msg=n -r - grand-forum-researchers";
		                exec($command);
		            }
		            else if($role == HQP){
		                $person = Person::newFromId($wgUser->getId());
		                $command = "echo \"{$person->getEmail()}\" | /usr/lib/mailman/bin/add_members --admin-notify=n --welcome-msg=n -r - grand-forum-hqps";
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
					
				    $sql = "INSERT INTO mw_user_groups (`ug_user`, `ug_group`) VALUES ('$id', '$val')";
				    DBFunctions::execSQL($sql, true);
                    $sql = "INSERT INTO grand_project_members (`user_id`, `project_id`, `start_date`) VALUES ('$id', '{$project->getId()}', CURRENT_TIMESTAMP)";
		            DBFunctions::execSQL($sql, true);
		        }
			}
		}
		
		$continue = UserCreate::addNewUserPage($wgUser);
		
		// Add User MailingList
		$user = User::newFromId($wgUser->getId());
		$email = $user->getEmail();
		if($email != null){
			foreach($user->getGroups() as $group){
				$listname = str_replace("Project_", "", $group);
				$command =  "echo \"$email\" | /usr/lib/mailman/bin/add_members --welcome-msg=n -r - $listname";
				exec($command);
				$sql = "SELECT projectid
					FROM wikidev_projects
					WHERE projectname = '$listname'";
				$rows = DBFunctions::execSQL($sql);
				@$row = $rows[0];
				if($row['projectid'] != null){
					$sql = "INSERT INTO wikidev_projectroles (projectid, userid) VALUES ('{$row['projectid']}','{$user->getName()}')";
					DBFunctions::execSQL($sql, true);
					//$wgOut->addHTML("User subscribed to ".strtolower($listname)."@forum.grand-nce.ca<br />");
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
