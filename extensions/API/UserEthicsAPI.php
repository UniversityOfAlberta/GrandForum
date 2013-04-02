<?php

class UserEthicsAPI extends API{

    function UserEthicsAPI(){
        $this->addPOST("completed_tutorial", true, "Whether they have completed the ethics tutorial: 0 or 1");
        $this->addPOST("date", true, "Date of when the tutorial was taken.");
    }

    function processParams($params){
        if(isset($_POST['completed_tutorial']) && !empty($_POST['completed_tutorial'])){
            //$_POST['completed_tutorial'] = $_POST['completed_tutorial'];
        }
    }

	function doAction($noEcho=false){
        $person = Person::newFromName($_POST['user_name']);
        $sql = "INSERT INTO grand_ethics(user_id, completed_tutorial, date)
                VALUES ('{$person->getId()}', '{$_POST['completed_tutorial']}', '{$_POST['date']}')
                ON DUPLICATE KEY UPDATE
                `completed_tutorial` = '{$_POST['completed_tutorial']}', `date` = '{$_POST['date']}'";
        
        DBFunctions::execSQL($sql, true);
        if(!$noEcho){
            echo "User's Gender updated\n";
        }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
