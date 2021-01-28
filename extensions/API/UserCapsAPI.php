<?php

class UserCapsAPI extends API{

    function __construct(){
        $this->addPOST("alias", true, "The alias of a the user", "Admin");
        $this->addPOST("city", true, "The city of the user", "555-5555");
        $this->addPOST("province", true, "The province of the user", "555-5555");
        $this->addPOST("specialty", true, "The specialty of the user", "555-5555");
        $this->addPOST("referral", true, "if user is accepting referral", "555-5555");
        $this->addPOST("postal_code", true, "The postal code for the user", "T8N 0Z5");
    }

    function processParams($params){
    }

    function doAction($noEcho=false){
        global $wgMessage;
        $person = Person::newFromName($_POST['user_name']);
        $person->city = $_POST['city'];
        $person->province_string = $_POST['province'];
        $person->specialty = $_POST['specialty'];
        $person->postal_code = $_POST['postal_code'];
        $_POST['referral_int'] = 0;
        if($_POST['referral'] == "Yes"){
            $_POST['referral_int'] = 1;
        }
        $person->accept_referrals = $_POST['referral_int'];
        $person->alias = $_POST['alias'];
        /*$taken = Person::newFromAliasCaps($_POST['alias']);
        if($taken != null && $taken->getId() != $person->getId()){
            @$wgMessage->addError("<b>{$_POST['alias']}</b> is already taken. Please choose another alias.");
            return false;
        }*/
        $person->update();
    }

    function isLoginRequired(){
        return true;
    }
}
?>
