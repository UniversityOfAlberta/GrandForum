<?php

class ApproveStoryAPI extends API{

    function ApproveStoryAPI(){
        $this->addPOST("id",true,"The id of story to approve.","id");
    }

    function processParams($params){
        // DO NOTHING
    }

    function doAction($doEcho=true){
	if(isset($_POST['id'])){
	    DBFunctions::update('grand_user_stories',
                                    array('approved' => 1),
                                    array('rev_id' => EQ(COL($_POST['id']))));
	}
    }
    
    function isLoginRequired(){
        return true;
    }
}
?>
