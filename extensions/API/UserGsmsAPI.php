<?php

class UserGsmsAPI extends API{

    function UserGsmsAPI(){
        $this->addPOST("GPA60", true, "The GPA of this user based on 60 credits", "3.2");
    }

    function processParams($params){
        if(isset($_POST['gpa']) && $_POST['gpa'] != ""){
            $_POST['gpa'] = str_replace("'", "&#39;", $_POST['gpa']);
        }
    }

        function doAction($noEcho=false){
        $person = Person::newFromName($_POST['user_name']);
	$data = DBFunctions::select(array('grand_person_gsms'),
			    array('user_id'),
			    array('user_id'=> EQ($person->getId())));
	if(count($data)==0){
            DBFunctions::insert('grand_person_gsms',
                                array('user_id' => $person->getId()),
                                true);
	}
        DBFunctions::update('grand_person_gsms',
                            array('gpa60' => $_POST['gpa'],
				  'gpafull' => $_POST['gpafull'],
				  'gpafull_credits' => $_POST['gpafull_credits'],
				  'notes' => $_POST['notes'],
				  'anatomy' => $_POST['anatomy'],
				  'stats' => $_POST['stats'],
				  'degree' => $_POST['degree'],
				  'institution' => $_POST['institution'],
				  'failures' => $_POST['failures']),
                            array('user_id' => EQ($person->getId())));
        $person->getUser()->invalidateCache();
        if(!$noEcho){
            echo "User's GPA updated \n";
        }
        }

        function isLoginRequired(){
                return true;
        }
}
?>
