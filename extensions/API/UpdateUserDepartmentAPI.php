<?php

class UpdateUserDepartmentAPI extends API{

    function UpdateUserDepartmentAPI(){
    }

    function processParams($params){
    }

    function doAction($noEcho=false){
        global $wgUser;
        $me = Person::newFromId($wgUser->getId());
        if(!$me->isRoleAtLeast(MANAGER)){
            return;
        }
	if(!isset($_POST['view'])){
	    $visible = false;
	}
	else{
	    $visible = $_POST['view'];
	}
	$degrees = array();
	for($i=1; $i<100; $i++){
	    $degree_name = "degree".$i;
            $institution_name = "institution".$i;

	    if(!isset($_POST[$degree_name])){
		continue;
	    }
	    $degree = array("degree"=>trim($_POST[$degree_name]),
			    "institution"=> trim($_POST[$institution_name]));
	    $degrees[] = $degree;
	}
        $person = Person::newFromName($_POST['user_name']);
	$data = DBFunctions::select(array('grand_gsms'),
			    array('user_id'),
			    array('user_id'=> EQ($person->getId())));
	if(count($data)==0){
            DBFunctions::insert('grand_gsms',
                                array('user_id' => $person->getId()),
                                true);
	}
        $gsms_array = array('gpa60' => trim($_POST['gpa']),
                                  'gpafull' => trim($_POST['gpafull']),
                                  'gpafull_credits' => trim($_POST['gpafull_credits']),
                                  'gpafull2' => trim($_POST['gpafull2']),
                                  'gpafull_credits2' => trim($_POST['gpafull_credits2']),
                                  'notes' => trim($_POST['notes']),
                                  'casper' => trim($_POST['casper']),
                                  'indigenous' => trim(@$_POST['indigenous']),
                                  'canadian' => trim(@$_POST['canadian']),
                                  'saskatchewan' => trim(@$_POST['saskatchewan']),
                                  'international' => trim(@$_POST['international']),
                                  'withdrawals' => trim($_POST['withdrawals']),
                                  'anatomy' => trim(@$_POST['anatomy']),
                                  'stats' => trim(@$_POST['stats']),
                                  'failures' => trim($_POST['failures']),
                                  'degrees' => $degrees);

        DBFunctions::update('grand_gsms',
                            array('additional' => serialize($gsms_array),
                                  'visible' => $visible),
                            array('user_id' => EQ($person->getId())));

        $person->getUser()->invalidateCache();
        
        $gsmsId = $person->getGSMS()->id;
        Cache::delete("gsms_{$gsmsId}");
        Cache::delete("gsms_user_{$person->getId()}");
        if(!$noEcho){
            echo "User's Department Information updated \n";
        }
        }

        function isLoginRequired(){
                return true;
        }
}
?>
