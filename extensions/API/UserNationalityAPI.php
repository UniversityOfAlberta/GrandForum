<?php

class UserNationalityAPI extends API{

    function UserNationalityAPI(){
        $this->addPOST("nationality", true, "The nationality of the user", "Canadian");
    }

    function processParams($params){
        if(isset($_POST['nationality']) && $_POST['nationality'] != ""){
            $_POST['nationality'] = str_replace("'", "&#39;", str_replace("#", "", $_POST['nationality']));
        }
    }

	function doAction($noEcho=false){
        $person = Person::newFromName($_POST['user_name']);
        $sql = "UPDATE mw_user
                SET `user_nationality` = '{$_POST['nationality']}'
                WHERE user_id = '{$person->getId()}'";
        DBFunctions::execSQL($sql, true);
        if(!$noEcho){
            echo "Nationality added\n";
        }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
