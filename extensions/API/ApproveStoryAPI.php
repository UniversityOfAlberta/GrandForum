<?php

class ApproveStoryAPI extends API{

    function __construct(){
        $this->addPOST("id",true,"The id of story to approve.","id");
    }

    function processParams($params){
        // DO NOTHING
    }

    function doAction($doEcho=true){
        $me = Person::newFromWgUser();
        if(isset($_POST['id']) && $me->isRoleAtLeast(STAFF)){
            $story = Story::newFromId($_POST['id']);
            DBFunctions::update('grand_user_stories',
                                array('approved' => 1),
                                array('id' => EQ(COL($story->getId()))));
            Notification::addNotification(null, $story->getUser(), "Story Approved", "Your story \"{$story->getTitle()}\" was approved", $story->getUrl(), true);
        }
    }
    
    function isLoginRequired(){
        return true;
    }
}
?>
