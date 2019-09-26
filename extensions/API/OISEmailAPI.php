<?php

class OISEmailAPI extends API{

    function OISEmailAPI(){
        $this->addPOST("oisID", true, "The profile text for this account.  Can be either 'public' or 'private'", "This is my profile");
    }

    function processParams($params){
        $_POST['oisID'] = @$_POST['oisID'];
    }

	function doAction($noEcho=false){
	    global $wgUser, $config;
	    $wgUser = User::newFromId(1);
	    if($_POST['oisID'] == ""){
	        $this->addError("A non-empty oisID must be provided");
	    }
        $gsms = GsmsData::newFromOisId($_POST['oisID']);
        if($gsms->user_id == ""){
            $this->addError("A valid oisID must be provided");
        }
        $student = Person::newFromId($gsms->user_id);
        $message = "Your interview has been completed!<br />
                    <br />
                    Thank you for applying. Best of luck!";
        // Always set content-type when sending HTML email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

        // More headers
        $headers .= "From: <{$config->getValue('supportEmail')}>" . "\r\n";

        mail($student->getEmail(), "Interview Completed", $message, $headers);
        $wgUser = null;
	}
	
	function isLoginRequired(){
		return false;
	}
}
?>
