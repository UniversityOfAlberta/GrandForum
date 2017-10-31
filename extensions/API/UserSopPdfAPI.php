<?php

class UserSopPdfAPI extends API{

    function UserSopPdfAPI(){
    }

    function processParams($params){
    }

    function doAction($noEcho=false){
        global $wgUser;
        $me = Person::newFromId($wgUser->getId());
        if(!$me->isRoleAtLeast(EVALUATOR)){
            return;
        }
	$user_id = $_GET["user"];
	$person = Person::newFromId($user_id);
	
	$pdf = $person->getSopPdf();
	header('Content-type: application/pdf');

	echo $pdf;
    }
    
    function isLoginRequired(){
        return true;
    }
}
?>
