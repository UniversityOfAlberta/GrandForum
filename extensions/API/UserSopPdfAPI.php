<?php

class UserSopPdfAPI extends API{

    function UserSopPdfAPI(){
    }

    function processParams($params){
    }

    function doAction($noEcho=false){
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
