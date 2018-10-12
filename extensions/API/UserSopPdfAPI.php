<?php

class UserSopPdfAPI extends API{

    function UserSopPdfAPI(){
    }

    function processParams($params){
    }

    function doAction($noEcho=false){
        global $wgUser;
        $me = Person::newFromId($wgUser->getId());
	    $user_id = $_GET["user"];
	    $year = (isset($_GET["year"]) && $_GET["year"] != "") ? $_GET["year"] : "";
	    $person = Person::newFromId($user_id);
	
	    $pdf = $person->getSopPdf($year);
	    header('Content-type: application/pdf');
	    echo $pdf;
    }
    
    function isLoginRequired(){
        return true;
    }
}
?>
