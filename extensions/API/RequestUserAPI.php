<?php

class RequestUserAPI extends API{

    function RequestUserAPI(){
        $this->addPOST("wpName", true, "The User Name of the user to add", "UserName");
        $this->addPOST("wpEmail", true, "The User's email address", "me@email.com");
        $this->addPOST("wpRealName", false, "The User's real name", "Real Name");
        $this->addPOST("wpUserType", true, "The User Roles Must be in the form \"Role1, Role2, ...\"", "HQP, RMC");
        $this->addPOST("wpNS", false, "The list of projects that the user is a part of.  Must be in the form\"Project1, Project2, ...\"", "MEOW, NAVEL");
    }

    function processParams($params){
        // DO NOTHING
    }

	function doAction($doEcho=true){
		global $wgRequest, $wgUser, $wgOut, $wgMessage;
		$me = Person::newFromUser($wgUser);
		if(!isset($_POST['wpName']) || $_POST['wpName'] == null){
			if($doEcho){
			    echo "A User Name must be provided.\n";
		    	exit;
		    }
		    else{
		        $message = "A User Name must be provided.";
			    $wgMessage->addError($message);
			    return $message;
		    }
		}
		$name = $_POST['wpName'];
		if(!preg_match("/^[a-zA-Z\-]+\.[a-zA-Z\-]+$/", $name)){
		    if($doEcho){
		        echo "This User Name is not in the format 'FirstName.LastName'.\n";
		        exit;
		    }
		    else{
		        $message = "This User Name is not in the format 'FirstName.LastName'.";
			    $wgMessage->addError($message);
			    return $message;
		    }
		}
		$person = Person::newFromName($name);
		if($person != null && $person->getName() != null){
		    if($doEcho){
		        echo "A user by the name of '{$person->getName()}' already exists.\n";
		        exit;
		    }
		    else{
		        $message = "A user by the name of '{$person->getName()}' already exists.";
			    $wgMessage->addError($message);
			    return $message;
		    }
		}
		if(!isset($_POST['wpEmail']) || $_POST['wpEmail'] == null){
			if($doEcho){
			    echo "An email address must be provided.\n";
			    exit;
			}
			else{
			    $message = "An email address must be provided.";
			    $wgMessage->addError($message);
			    return $message;
		    }
		}
		$email = $_POST['wpEmail'];
		if(!User::isValidEmailAddr($email)){
		    if($doEcho){
		        echo "A valid email address must be provided.\n";
		        exit;
		    }
		    else{
		        $message = "A valid email address must be provided.";
			    $wgMessage->addError($message);
			    return $message;
		    }
		}
		if(!$me->isRoleAtLeast(MANAGER) && (!isset($_POST['wpUserType']) || $_POST['wpUserType'] == null)){
		    if($doEcho){
			    echo "User Roles must be provided\n";
			    exit;
			}
			else{
			    $message = "At least one User Role must be provided";
			    $wgMessage->addError($message);
			    return $message;
		    }
		}
		// Finished manditory checks
		// Add a request for a user to be created
		$requesting_user = isset($_POST['user_name']) ? $_POST['user_name'] : "";
		$wpName = isset($_POST['wpName']) ? $_POST['wpName'] : "";
		$wpEmail = isset($_POST['wpEmail']) ? $_POST['wpEmail'] : "";
		$wpRealName = isset($_POST['wpRealName']) ? $_POST['wpRealName'] : "";
		$wpUserType = isset($_POST['wpUserType']) ? $_POST['wpUserType'] : "";
		$wpNS = isset($_POST['wpNS']) ? $_POST['wpNS'] : "";
		
		$sql = "INSERT INTO mw_user_create_request (`requesting_user`, `wpName`, `wpEmail`, `wpRealName`, `wpUserType`, `wpNS`, `created`)
			VALUES ('$requesting_user', '$wpName', '$wpEmail', '$wpRealName', '$wpUserType', '$wpNS', 'false')";
		DBFunctions::execSQL($sql, true);
		
		$me = Person::newFromName($requesting_user);
		Notification::addNotification("", $me, "User Creation Pending", "User '{$wpName}' has been requested.  Once an Admin sees this request, the user will be accepted, or if there is a problem they will email you", "");
		if($doEcho){
		    echo "User Creation Request Submitted.  Once an Admin sees this request, the user will be accepted, or if there is a problem they will email you.\n";
		}
		else{
		    $message = "User Creation Request Submitted.  Once an Admin sees this request, the user will be accepted, or if there is a problem they will email you.";
		    $wgMessage->addSuccess($message);
		    return $message;
		}
        
	}
	
	function isLoginRequired(){
		return false;
	}
}
?>
