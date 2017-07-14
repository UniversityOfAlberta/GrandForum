<?php

class UserEmailAPI extends API{

    function UserEmailAPI(){
        $this->addPOST("email", true, "The email address for this account.", "email@mail.com");
    }

    function processParams($params){
        if(isset($_POST['email']) && $_POST['email'] != ""){
            $_POST['email'] = $_POST['email'];
        }
    }

	function doAction($noEcho=false){
	    global $wgMessage;
	    
        $person = Person::newFromName($_POST['user_name']);
        $valid = @User::isValidEmailAddr($_POST['email']);
        if(!$valid){
            @$wgMessage->addError("<b>{$_POST['email']}</b> is not a valid email address");
            return false;
        }
        // Remove the person from previous mailing lists
        MailingList::unsubscribeAll($person);
        DBFunctions::update('mw_user',
                            array('user_email' => $_POST['email']),
                            array('user_id' => EQ($person->getId())));
        $person->email = $_POST['email'];
        // Re-Add the person to the mailing lists using their new email
        Cache::delete("idsCache_{$person->getId()}");
        MailingList::subscribeAll($person);
        $person->getUser()->invalidateCache();
        if(!$noEcho){
            echo "Account email updated\n";
        }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
