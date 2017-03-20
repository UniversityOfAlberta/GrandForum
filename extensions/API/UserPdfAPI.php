<?php

class UserPdfAPI extends API{

    function UserPdfAPI(){
    }

    function processParams($params){
    }

    function doAction($noEcho=false){
	$user_id = $_GET["user"];
     $data = DBFunctions::select(array('grand_sop'),
                                    array('pdf_contents', 'pdf_data'),
                                    array('user_id' => EQ($user_id)));
	$pdf = $data[0]['pdf_contents'];
	header('Content-type: application/pdf');

	echo $pdf;
    }
    
    function isLoginRequired(){
        return true;
    }
}
?>
