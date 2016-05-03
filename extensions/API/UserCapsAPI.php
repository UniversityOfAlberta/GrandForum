<?php

class UserCapsAPI extends API{

    function UserCapsAPI(){
        $this->addPOST("city", true, "The city of the user", "555-5555");
        $this->addPOST("province", true, "The province of the user", "555-5555");
        $this->addPOST("specialty", true, "The specialty of the user", "555-5555");
        $this->addPOST("referral", true, "if user is accepting referral", "555-5555");

    }

    function processParams($params){
    }

    function doAction($noEcho=false){
        $person = Person::newFromName($_POST['user_name']);
	$person->city = $_POST['city'];
	$person->province_string = $_POST['province'];
	$person->specialty = $_POST['specialty'];
        $_POST['referral_int'] = 0;
        if($_POST['referral'] == "Yes"){
            $_POST['referral_int'] = 1;
        }
	$person->accept_referrals = $_POST['referral_int'];
	$person->update();
    }

        function isLoginRequired(){
                return true;
        }
}
?>
