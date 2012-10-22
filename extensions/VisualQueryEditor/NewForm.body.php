<?php

require_once("VQE.php");
/*
$VQE = new VQE();

class UserCreate {

	static function createNotification(){
		global $notifications, $wgUser, $wgServer, $wgScriptPath;
		$groups = $wgUser->getGroups();
		if(array_search("bureaucrat", $groups) !== false){
			$sql = "SELECT requesting_user, wpName
				FROM mw_user_create_request
				WHERE `created` = 'false'
				AND `ignore` = 'false'";
			$dbr = wfGetDB(DB_READ);
			$result = $dbr->query($sql);
			$rows = array();
			while($row = $dbr->fetchRow($result)){
				$rows[] = $row;
			}
			
			if(count($rows) > 0){
				$notifications[] = new Notification("User Creation Request", "There is at least one user creation request pending.", "$wgServer$wgScriptPath/index.php/Special:UserCreationRequest?action=view");
			}
		}
	}
	
	function afterCreateUser($wgUser, $byEmail=true){
		global $wgLocalTZoffset, $wgOut;
		
		$mUserType = $_POST['wpUserType'];
		$id = $wgUser->getId();
		
		$dbw = wfGetDB(DB_WRITE);
		$dbr = wfGetDB(DB_READ);
		$dbw->commit();
		$dbw->begin();
		$sql = "INSERT INTO mw_user_groups (`ug_user`, `ug_group`) VALUES ('$id', '$mUserType')";
		$dbw->query($sql);
		if(isset($_POST['wpNS'])){
			$box = $_POST['wpNS'];
			while (list ($key,$val) = @each ($box)) {
				$sql = "INSERT INTO mw_user_groups (`ug_user`, `ug_group`) VALUES ('$id', '$val')";
				$dbw->query($sql);
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
				$result = $dbr->query($sql);
				$row = $dbr->fetchRow($result);
				if($row['projectid'] != null){
					$sql = "INSERT INTO wikidev_projectroles (projectid, userid) VALUES ('{$row['projectid']}','{$user->getName()}')";
					$dbw->query($sql);
					$wgOut->addHTML("User subscribed to ".strtolower($listname)."@forum.grand-nce.ca<br />");
				}
			}
		}
		if($continue == true){
			$wgOut->addHTML("<br /><a href='index.php/{$_POST['wpUserType']}:{$wgUser->getName()}'>Click Here</a> to view {$wgUser->getName()}'s user page.");
		}
		$dbw->commit();
		return true;
	}
	
	static function addNewUserPage($wgUser){
	    $mUserType = $_POST['wpUserType'];
	    $dbw = wfGetDB(DB_WRITE);
		$dbr = wfGetDB(DB_READ);
	    // Getting Project Information
	    $newPerson = Person::newFromName($wgUser->getName());
	    $projects = $newPerson->getProjects();
	    
	    $projects_s = "";
	    foreach($projects as $project){
	        $projects_s .= "* [[{$project->getName()}:Main | {$project->getFullName()}]] \n";
	    }
		
		$ns = 0;
		// Adding the new page to the DB
		$articleText = "";
		$continue = true;
		if($mUserType == "NI" || $mUserType == "CR"){
			$articleText = 
"{{".$mUserType."
|Name = ".str_replace(".", " ", $wgUser->getName())."
|Contact_Information = 
|grand_projects = \n$projects_s
|biography = 
}}";
			if($mUserType == "NI"){
				$ns = NS_GRAND_NI;
			}
			else{
				$ns = NS_GRAND_CR;
			}
		}
		else if($mUserType == "HQP"){
		    // Get Professor Information
		    $professors_s = "";
			foreach($newPerson->getCreators() as $p){
			    $name = $p->getName();
			    $type = $p->getType();
			    if($type != null){
			        $professors_s .= "* [[$type:$name | ".str_replace(".", " ", $name)."]]\n";
			    }
			}
			
			$articleText = 
"{{".$mUserType."
|name = ".str_replace(".", " ", $wgUser->getName())."
|professors = \n$professors_s
|projects = \n$projects_s
|biography = 
|events = 
}}
";
			$ns = NS_STUDENT;
		}
		else if($mUserType == "Gov"){
			$continue = false;
		}
		if($continue = true){
			$newTitle = Title::newFromText($wgUser->getName(), $ns);
			$article = new Article($newTitle);
			$article->doEdit($articleText, "", EDIT_NEW);
		}
		return $continue;
	}   
}



*/

?>
