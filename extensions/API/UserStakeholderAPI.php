<?php

class UserStakeholderAPI extends API{

    function UserNationalityAPI(){
        $this->addPOST("stakeholder", true, "The stakeholder category for this person", "Caregiver");
    }

    function processParams($params){
        if(isset($_POST['stakeholder']) && $_POST['stakeholder'] != ""){
            $_POST['stakeholder'] = str_replace("'", "&#39;", str_replace("#", "", $_POST['stakeholder']));
        }
    }

	function doAction($noEcho=false){
        $person = Person::newFromName($_POST['user_name']);
        DBFunctions::update('mw_user',
                            array('user_stakeholder' => $_POST['stakeholder']),
                            array('user_id' => EQ($person->getId())));
        $person->getUser()->invalidateCache();
        if(!$noEcho){
            echo "Stakeholder added\n";
        }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
