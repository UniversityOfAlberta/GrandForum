<?php

class UserLdapAPI extends API{

    function UserLdapAPI(){
        $this->addPOST("ldap", true, "The url of an ldap", "http://www.mywebsite.com");
    }

    function processParams($params){
        if(isset($_POST['ldap']) && $_POST['ldap'] != ""){
            $_POST['ldap'] = str_replace("'", "&#39;", $_POST['ldap']);
        }
    }

    function doAction($noEcho=false){
        $person = Person::newFromName($_POST['user_name']);
        DBFunctions::update('mw_user',
                            array('ldap_url' => $_POST['ldap']),
                            array('user_id' => EQ($person->getId())));
        Cache::delete("idsCache_{$person->getId()}");
        if(!$noEcho){
            echo "Ldap added\n";
        }
    }
	
    function isLoginRequired(){
		return true;
    }
}
?>
