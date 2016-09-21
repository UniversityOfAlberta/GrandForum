<?php

class MailingListAPI extends RESTAPI {
    
    function doGET(){
        $listId = $this->getParam('listId');
        if($listId != ""){
            $list = MailingList::newFromId($listId);
            return $list->toJSON();
        }
        else {
            $lists = new Collection(MailingList::getAllMailingLists());
            return $lists->toJSON();
        }
    }
    
    function doPOST(){
        return $this->doGet();
    }
    
    function doPUT(){
        return $this->doGet();
    }
    
    function doDELETE(){
        return $this->doGet();
    }
	
}

?>
