<?php

class RequestUserAPI extends API{

    function RequestUserAPI(){
        $this->addPOST("wpName", true, "The User Name of the user to add", "UserName");
        $this->addPOST("wpEmail", true, "The User's email address", "me@email.com");
        $this->addPOST("wpSendEmail", true, "Whether or not to send a registration email", "true");
        $this->addPOST("wpRealName", false, "The User's real name", "Real Name");
        $this->addPOST("wpFirstName", false, "The User's first name", "First Name");
        $this->addPOST("wpMiddleName", false, "The User's middle name", "Middle Name");
        $this->addPOST("wpLastName", false, "The User's last name", "Last Name");
        $this->addPOST("wpUserType", true, "The User Roles Must be in the form \"Role1, Role2, ...\"", "HQP, RMC");
        $this->addPOST("wpUserSubType", false, "The User Sub Roles Must be in the form \"Role1, Role2, ...\"", "HQP, RMC");
        $this->addPOST("wpNS", false, "The list of projects that the user is a part of.  Must be in the form\"Project1, Project2, ...\"", "MEOW, NAVEL");
        $this->addPOST("candidate", false, "Whether or not this person is a candidate user or not", "");
        $this->addPOST("university",false, "", "");
        $this->addPOST("department",false, "", "");
        $this->addPOST("position",false, "", "");
        $this->addPOST("nationality",false, "", "");
        $this->addPOST("employment",false, "", "");
        $this->addPOST("recruitment",false, "", "");
        $this->addPOST("recruitmentCountry",false,"","");
        $this->addPOST("start_date",false, "", "");
        $this->addPOST("end_date",false, "", "");
    }

    function processParams($params){
        // DO NOTHING
    }

	function doAction($doEcho=true){
		global $wgRequest, $wgUser, $wgOut, $wgMessage, $wgServer, $wgScriptPath, $config, $wgAdditionalMailParams;
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
		if(!preg_match("/^[À-Ÿa-zA-Z0-9\- ]+\.[À-Ÿa-zA-Z0-9\- ]+$/", $name)){
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
		$wpFirstName = isset($_POST['wpFirstName']) ? $_POST['wpFirstName'] : "";
		$wpMiddleName = isset($_POST['wpMiddleName']) ? $_POST['wpMiddleName'] : "";
		$wpLastName = isset($_POST['wpLastName']) ? $_POST['wpLastName'] : "";
		$wpUserType = isset($_POST['wpUserType']) ? $_POST['wpUserType'] : "";
		$wpUserSubType = isset($_POST['wpUserSubType']) ? $_POST['wpUserSubType'] : "";
		$wpNS = isset($_POST['wpNS']) ? $_POST['wpNS'] : "";
		$relation = @($_POST['relUser'] != "" || $_POST['relType'] != "") ? "{$_POST['relUser']}:{$_POST['relType']}" : "";
		$university = isset($_POST['university']) ? $_POST['university'] : "";
		$faculty = isset($_POST['faculty']) ? $_POST['faculty'] : "";
		$department = isset($_POST['department']) ? $_POST['department'] : "";
		$position = isset($_POST['position']) ? $_POST['position'] : "";
		$nationality = isset($_POST['nationality']) ? $_POST['nationality'] : "";
		$employment = isset($_POST['employment']) ? $_POST['employment'] : "";
		$recruitment = isset($_POST['recruitment']) ? $_POST['recruitment'] : "";
		$recruitmentCountry = isset($_POST['recruitmentCountry']) ? $_POST['recruitmentCountry'] : "";
		$startDate = isset($_POST['start_date']) ? $_POST['start_date'] : "";
		$endDate = isset($_POST['end_date']) ? $_POST['end_date'] : "";
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
		                          'wpFirstName' => $wpFirstName,
		                          'wpMiddleName'=> $wpMiddleName,
		                          'wpLastName' => $wpLastName,
		                          'wpUserType' => $wpUserType,
		                          'wpUserSubType' => $wpUserSubType,
		                          'wpNS' => $wpNS,
		                          'relation' => $relation,
		                          'university' => $university,
		                          'faculty' => $faculty,
		                          'department' => $department,
		                          'position' => $position,
		                          'nationality' => $nationality,
		                          'employment' => $employment,
		                          'recruitment' => $recruitment,
		                          'recruitment_country' => $recruitmentCountry,
		                          'start_date' => $startDate,
		                          'end_date' => $endDate,
		                          'candidate' => $candidate,
		                          'created' => 0));
		
		$me = Person::newFromId($requesting_user);
		if($config->getValue('networkName') == "FES"){
		    $headers = "From: {$config->getValue('supportEmail')}\r\n".
		               "Reply-To: {$config->getValue('supportEmail')}\r\n".
		               "X-Mailer: PHP/".phpversion();
		    mail("fesadmin@ualberta.ca", "User Requested", "A new user '{$wpName}' has been requested by {$me->getNameForForms()}\n\n{$wgServer}{$wgScriptPath}/index.php/Special:AddMember?action=view.", $headers, $wgAdditionalMailParams);
		}
		else if($config->getValue('networkName') == "MtS"){
		    $headers = "From: {$config->getValue('supportEmail')}\r\n".
		               "Reply-To: {$config->getValue('supportEmail')}\r\n".
		               "X-Mailer: PHP/".phpversion();
		    mail("pravinah@yorku.ca,aolaniyi@yorku.ca", "User Requested", "A new user '{$wpName}' has been requested by {$me->getNameForForms()}\n\n{$wgServer}{$wgScriptPath}/index.php/Special:AddMember?action=view.", $headers, $wgAdditionalMailParams);
		}
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
