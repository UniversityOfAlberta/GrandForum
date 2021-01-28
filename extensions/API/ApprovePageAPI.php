<?php

class ApprovePageAPI extends API{

    function __construct(){
        $this->addPOST("id",true,"The id of page to approve.","id");
    }

    function processParams($params){
        // DO NOTHING
    }

    function doAction($doEcho=true){
	if(isset($_POST['id'])){
	    DBFunctions::update('grand_page_approved',
                                    array('approved' => 1),
                                    array('page_id' => EQ(COL($_POST['id']))));
	}
    }
    
    function isLoginRequired(){
        return true;
    }
}
?>
