<?php

class ApproveStoryAPI extends API{

    function __construct(){
        $this->addPOST("id",true,"The id of story to approve.","id");
    }

    function processParams($params){
        // DO NOTHING
    }

    function doAction($doEcho=true){
        if(isset($_POST['id'])){
            $story = Story::newFromId($_POST['id']);
            DBFunctions::update('grand_user_stories',
                                array('approved' => 1),
                                array('rev_id' => EQ(COL($story->getRevId()))));
            Notification::addNotification(null, $story->getUser(), "Story Approved", "Your story \"{$story->getTitle()}\" was approved", $story->getUrl(), true);
        }
    }
    
    function isLoginRequired(){
        return true;
    }
}
?>
