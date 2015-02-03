<?php

class UserProfileAPI extends API{

    function UserProfileAPI(){
        $this->addPOST("type", true, "The type of profile this is.  Can be either 'public' or 'private'", "public");
        $this->addPOST("profile", true, "The profile text for this account.  Can be either 'public' or 'private'", "This is my profile");
    }

    function processParams($params){
        if(isset($_POST['profile']) && $_POST['profile'] != ""){
            $_POST['profile'] = str_replace("'", "&#39;", $_POST['profile']);
        }
    }

	function doAction($noEcho=false){
        $person = Person::newFromName($_POST['user_name']);
        $profile = mysql_real_escape_string($_POST['profile']);
        $sql = "UPDATE mw_user
                SET `user_{$_POST['type']}_profile` = '{$profile}'
                WHERE user_id = '{$person->getId()}'";
        DBFunctions::execSQL($sql, true);
        if(!$noEcho){
            echo "Account profile updated\n";
        }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
