<?php

class RequestUserAPI extends API{

    function RequestUserAPI(){
        $this->addPOST("wpName", true, "The User Name of the user to add", "UserName");
        $this->addPOST("wpEmail", true, "The User's email address", "me@email.com");
        $this->addPOST("wpSendEmail", true, "Whether or not to send a registration email", "true");
        $this->addPOST("wpRealName", false, "The User's real name", "Real Name");
        $this->addPOST("wpUserType", true, "The User Roles Must be in the form \"Role1, Role2, ...\"", "HQP, RMC");
        $this->addPOST("wpNS", false, "The list of projects that the user is a part of.  Must be in the form\"Project1, Project2, ...\"", "MEOW, NAVEL");
        $this->addPOST("candidate", false, "Whether or not this person is a candidate user or not", "");
        $this->addPOST("university",false, "", "");
        $this->addPOST("department",false, "", "");
        $this->addPOST("position",false, "", "");
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
			    return false;
		    }
		}
		$name = $_POST['wpName'];
		if(!preg_match("/^[À-Ÿa-zA-Z\-]+\.[À-Ÿa-zA-Z\-]+$/", $name)){
		    if($doEcho){
		        echo "This User Name is not in the format 'FirstName.LastName'.\n";
		        exit;
		    }
		    else{
		        $message = "This User Name is not in the format 'FirstName.LastName'.";
			    $wgMessage->addError($message);
			    return false;
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
			    return false;
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
			    return false;
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
			    return false;
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
			    return false;
		    }
		}
		// Finished manditory checks
		// Add a request for a user to be created
		$requesting_user = $me->getId();
		$wpName = isset($_POST['wpName']) ? $_POST['wpName'] : "";
		$wpEmail = isset($_POST['wpEmail']) ? $_POST['wpEmail'] : "";
		$wpSendEmail = isset($_POST['wpSendEmail']) ? $_POST['wpSendEmail'] : "";
		$wpRealName = isset($_POST['wpRealName']) ? $_POST['wpRealName'] : "";
		$wpUserType = isset($_POST['wpUserType']) ? $_POST['wpUserType'] : "";
		$wpNS = isset($_POST['wpNS']) ? $_POST['wpNS'] : "";
		$university = isset($_POST['university']) ? $_POST['university'] : "";
		$department = isset($_POST['department']) ? $_POST['department'] : "";
		$position = isset($_POST['position']) ? $_POST['position'] : "";
		$candidate = isset($_POST['candidate']) ? $_POST['candidate'] : "0";
		if($candidate == "Yes" || $candidate == "1"){
		    $candidate = 1;
		}
		else {
		    $candidate = 0;
		}
		DBFunctions::insert('grand_user_request',
		                    array('requesting_user' => $requesting_user,
		                          'wpName' => $wpName,
		                          'wpEmail' => $wpEmail,
		                          'wpSendEmail' => $wpSendEmail,
		                          'wpRealName' => $wpRealName,
		                          'wpUserType' => $wpUserType,
		                          'wpNS' => $wpNS,
		                          'university' => $university,
		                          'department' => $department,
		                          'position' => $position,
		                          'candidate' => $candidate,
		                          'created' => 0));
		
		$me = Person::newFromId($requesting_user);
		Notification::addNotification("", $me, "User Creation Pending", "User '{$wpName}' has been requested.  Once an Admin sees this request, the user will be accepted, or if there is a problem they will email you", "");
		if($doEcho){
		    echo "User Creation Request Submitted.  Once an Admin sees this request, the user will be accepted, or if there is a problem they will email you.\n";
		}
		else{
		    $message = "User Creation Request Submitted.  Once an Admin sees this request, the user will be accepted, or if there is a problem they will email you.";
		    $wgMessage->addSuccess($message);
		    return true;
		}
        
	}
	
	function isLoginRequired(){
		return false;
	}
}
?>
