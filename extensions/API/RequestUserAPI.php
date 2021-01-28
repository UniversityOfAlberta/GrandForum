<?php

class RequestUserAPI extends API{

    function __construct(){
        $this->addPOST("wpName", true, "The User Name of the user to add", "UserName");
        $this->addPOST("wpEmail", true, "The User's email address", "me@email.com");
        $this->addPOST("wpRealName", false, "The User's real name", "Real Name");
        $this->addPOST("wpUserType", true, "The User Roles Must be in the form \"Role1, Role2, ...\"", "HQP, RMC");
        $this->addPOST("wpOtherRole", false, "The type of facility staff this user is", "Nurse");
        $this->addPOST("wpPostalCode", true, "The User's postal code", "t7t3m1");
        $this->addPOST("wpCity", true, "The User's city", "Edmonton");
        $this->addPOST("wpProvince", true, "The User province", "Alberta");
        $this->addPOST("wpSpecialty", true, "The physician's specialty", "Gynecologist");
        $this->addPOST("wpClinic", true, "The physician's clinic's name", "Medical Clinic");
        $this->addPOST("wpProvision", true, "If the physician has had previous abortion experience", "yes/no");
        $this->addPOST("wpDisclosure", true, "If the pharmacist has agreed to share their pharmacy locations", "I agree/I disagree");
        $this->addPOST("wpPharmacyName", true, "The pharmacists pharmacy name", "Me");
        $this->addPOST("wpPharmacyAddress", true, "The pharmacist's pharmacy address", "21345 134 ave");
        $this->addPOST("wpReference", true, "The Person who is a reference for the user", "HQP, RMC");
        $this->addPOST("wpAgreeExtra", false, "Agreement fields", "");
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
        //DO FOR LOOP HERE TO ADD USERNAME NUMBERS
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
		if(!Sanitizer::validateEmail($email)){
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
        $wpCaps = array();
        $wpFile = array();
		// Finished manditory checks
		// Add a request for a user to be created
        if($me->getId() == ""){
            $requesting_user = 1;
        }
		$requesting_user = $me->getId();
		$wpName = isset($_POST['wpName']) ? $_POST['wpName'] : "";
		$wpEmail = isset($_POST['wpEmail']) ? $_POST['wpEmail'] : "";
		$wpRealName = isset($_POST['wpRealName']) ? $_POST['wpRealName'] : "";
		$wpUserType = isset($_POST['wpUserType']) ? $_POST['wpUserType'] : "";
		$wpNS = isset($_POST['wpNS']) ? $_POST['wpNS'] : "";
		$university = isset($_POST['university']) ? $_POST['university'] : "";
		$department = isset($_POST['department']) ? $_POST['department'] : "";
		$position = isset($_POST['position']) ? $_POST['position'] : "";
		$candidate = isset($_POST['candidate']) ? $_POST['candidate'] : "0";
		$wpCaps['otherRole'] = isset($_POST['wpOtherRole']) ? $_POST['wpOtherRole'] :"";
        $wpCaps['language'] = isset($_POST['wpLanguage']) ? $_POST['wpLanguage'] :"";
        $wpCaps['postal_code'] = isset($_POST['wpPostalCode']) ? $_POST['wpPostalCode'] : "";
        $wpCaps['city'] = isset($_POST['wpCity']) ? $_POST['wpCity'] : "";
        $wpCaps['province'] = isset($_POST['wpProvince']) ? $_POST['wpProvince'] : "";
        $wpCaps['reference'] = isset($_POST['wpReference']) ? $_POST['wpReference'] : "";
        $wpCaps['clinic'] = isset($_POST['wpClinic']) ? $_POST['wpClinic'] : "";
        $wpCaps['specialty'] = isset($_POST['wpSpecialty']) ? $_POST['wpSpecialty'] : "";
        $wpCaps['provision'] = isset($_POST['wpProvision']) ? $_POST['wpProvision'] : "";
        $wpCaps['pharmacy_name'] = isset($_POST['wpPharmacyName']) ? $_POST['wpPharmacyName'] : "";
        $wpCaps['pharmacy_address'] = isset($_POST['wpPharmacyAddress']) ? $_POST['wpPharmacyAddress'] : "";
        $wpCaps['collect_demo'] = @(array_search('collect_demo', $_POST['wpAgreeExtra']) !== false) ? 1 : 0;
        $wpCaps['collect_comments'] = @(array_search('collect_comments', $_POST['wpAgreeExtra']) !== false) ? 1 : 0;
	    if(isset($_FILES['file_filed'])){
            $contents = base64_encode(file_get_contents($_FILES['file_field']['tmp_name']));
            $filename = $_FILES['file_field']['name'];
            $filesize = $_FILES['file_field']['size'];
            $filetype = $_FILES['file_field']['type'];
            $wpFile['file_data'] = array('name' => $filename,
                                         'size' => $filesize,
                                         'type' => $filetype,
                                         'file' => $contents);
	    }
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
		                          'wpRealName' => $wpRealName,
		                          'wpUserType' => $wpUserType,
		                          'wpNS' => $wpNS,
		                          'university' => $university,
		                          'department' => $department,
		                          'position' => $position,
		                          'candidate' => $candidate,
		                          'created' => 0,
                                  'extras' => serialize($wpCaps),
				   	              'proof_certification' => serialize($wpFile)));
		
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
