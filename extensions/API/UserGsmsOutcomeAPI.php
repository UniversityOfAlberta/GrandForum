<?php

class UserGsmsOutcomeAPI extends API{

    function UserGsmsOutcomeAPI(){
    }

    function processParams($params){
        if(isset($_POST['gpa']) && $_POST['gpa'] != ""){
            $_POST['gpa'] = str_replace("'", "&#39;", $_POST['gpa']);
        }
    }

    function doAction($noEcho=false){
        $person = Person::newFromName($_POST['user_name']);
	$gsms_array = array();
	$gsms_array['academic_year'] = $_POST['academic_year'];
	$gsms_array['term'] = $_POST['term'];
        $gsms_array['program'] = $_POST['program'];
        $gsms_array['degree'] = $_POST['degree'];
        $gsms_array['folder'] = $_POST['folder'];
        $gsms_array['decision_response'] = $_POST['decision_response'];
	
	$data = DBFunctions::select(array('grand_person_gsms'),
			    array('user_id'),
			    array('user_id'=> EQ($person->getId())));
	if(count($data)==0){
            DBFunctions::insert('grand_person_gsms',
                                array('user_id' => $person->getId()),
                                true);
	}
        DBFunctions::update('grand_person_gsms',
                            array('final_gsms' => serialize($gsms_array)),
                            array('user_id' => EQ($person->getId())));
        $person->getUser()->invalidateCache();
        if(!$noEcho){
            echo "User's Gsms Outcome updated \n";
        }
        }

        function isLoginRequired(){
                return true;
        }
}
?>
