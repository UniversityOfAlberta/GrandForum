<?php

class AddHQPThesisAPI extends API{

    function AddHQPThesisAPI(){
        $this->addPOST("name",true,"The User Name of the user","UserName");
        $this->addPOST("thesis",true,"The id of the thesis","231");
    }

    function processParams($params){
        $_POST['user'] = $_POST['name'];
    }

	function doAction($noEcho=false){
		global $wgRequest, $wgUser;
		$groups = $wgUser->getGroups();
		$me = Person::newFromId($wgUser->getId());

		$person = Person::newFromName($_POST['user']);
		if(!$noEcho){
            if($person->getName() == null){
                echo "There is no person by the name of '{$_POST['user']}'\n";
                exit;
            }
        }
		$supervisors = $person->getSupervisors(true);
		$isSupervisor = false;
		foreach($supervisors as $supervisor){
		    if($supervisor->getName() == $me->getName()){
		        $isSupervisor = true;
		    }
		}
		if($me->isRole(STAFF) || $me->isRole(MANAGER) || count($me->leadership()) > 0 || $isSupervisor || $me->getId() == $person->getId()){
            // Actually Add the Project Member
            $sql = "SELECT * FROM `grand_theses`
                    WHERE `user_id` = '{$person->getId()}'";
            DBFunctions::execSQL($sql);
            if(DBFunctions::getNRows() > 0){
                DBFunctions::execSQL("UPDATE `grand_theses`
                                      SET `publication_id` = '{$_POST['thesis']}'
                                      WHERE `user_id` = '{$person->getId()}'", true);
            }
            else{
                DBFunctions::execSQL("INSERT INTO grand_theses
                                  (`user_id`,`publication_id`)
                                  VALUES ('{$person->getId()}','{$_POST['thesis']}')", true);
            }
            if(!$noEcho){
                echo "{$person->getName()} thesis added\n";
            }
		}
		else {
		    if(!$noEcho){
			    echo "You do not have the correct permissions to edit this user\n";
			}
		}
	}
	
	function isLoginRequired(){
		return true;
	}
}

?>
