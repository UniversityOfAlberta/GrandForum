<?php

class UserPartnerAPI extends API{

    function UserPartnerAPI(){
        $this->addPOST("partner", true, "The name of the partner", "IBM");
    }

    function processParams($params){
        if(isset($_POST['partner']) && $_POST['partner'] != ""){
            $_POST['partner'] = str_replace("'", "&#39;", $_POST['partner']);
        }
    }

	function doAction($noEcho=false){
        $person = Person::newFromName($_POST['user_name']);
        $sql = "SELECT *
                FROM `grand_champion_partners`
                WHERE `user_id` = '{$person->getId()}'";
        DBFunctions::execSQL($sql);
        if(DBFunctions::getNRows() > 0){
            $sql = "UPDATE `grand_champion_partners`
                    SET `partner` = '{$_POST['partner']}'
                    WHERE user_id = '{$person->getId()}'";
        }
        else{
            $sql = "INSERT INTO `grand_champion_partners` (`user_id`,`partner`)
                    VALUES ('{$person->getId()}','{$_POST['partner']}')";
        }
        DBFunctions::execSQL($sql, true);
        if(!$noEcho){
            echo "Partner added\n";
        }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
