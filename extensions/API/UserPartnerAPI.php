<?php

class UserPartnerAPI extends API{

    function UserPartnerAPI(){
        $this->addPOST("partner", true, "The name of the partner", "IBM");
        $this->addPOST("title", true, "The name of the title", "IBM");
        $this->addPOST("department", true, "The name of the department", "IBM");
    }

    function processParams($params){
        $_POST['partner'] = @$_POST['partner'];
        $_POST['title'] = @$_POST['title'];
        $_POST['department'] = @$_POST['department'];
    }

	function doAction($noEcho=false){
        $person = Person::newFromName($_POST['user_name']);
        $data = DBFunctions::select(array('grand_champion_partners'),
                                    array('*'),
                                    array('user_id' => EQ($person->getId())));
        if(count($data) > 0){
            DBFunctions::update('grand_champion_partners',
                                array('partner' => $_POST['partner'],
                                      'title' => $_POST['title'],
                                      'department' => $_POST['department']),
                                array('user_id' => EQ($person->getId())));
        }
        else{
            DBFunctions::insert('grand_champion_partners',
                                array('user_id' => $person->getId(),
                                      'partner' => $_POST['partner'],
                                      'title' => $_POST['title'],
                                      'department' => $_POST['department']));
        }
        if(!$noEcho){
            echo "Partner added\n";
        }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
