<?php

class StoryAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            $me = Person::newFromWgUser();
            $story = Story::newFromId($this->getParam('id'));
            if(!$story->canView()){
                permissionError();
            }
            return $story->toJSON();
        }
    }

    function doPOST(){
        global $wgServer, $wgScriptPath;
        $story = new Story(array());
        $me = Person::newFromWgUser();
        if(trim($this->POST('title')) == ""){
            $this->throwError("A title must be provided");
        }
        $story->user = $me->getId();
        $story->title = $this->POST('title');
        $story->story = $this->POST('story');
        $status = $story->create();
        if($status === false){
            $this->throwError("The story <i>{$story->getTitle()}</i> could not be created");
        }
        if(!$me->isRoleAtLeast(MANAGER)){
            $people = Person::getAllPeople();
            foreach($people as $person){
		        if($person->isRoleAtLeast(MANAGER)){
		            Notification::addNotification($me,$person,"New Discussion Room Story", "{$me->getNameForForms()} has made a new discussion room story which needs to be approved", "{$wgServer}{$wgScriptPath}/index.php/Special:ApproveStory?action=view", true);
		        }
            }
        }
        /*if($me->isRoleAtLeast(MANAGER)){
            $story->approve();
	        $people = Person::getAllPeople();
	        foreach($people as $person){
	        	//Notification::addNotification($me,$person,"New Post by Manager: {$story->getTitle()}", "{$me->getNameForForms()} has made a new Admin post", "{$story->getUrl()}");
                //Notification::addNotification($me,$person,"New Post by Manager: {$story->getTitle()}", "{$me->getNameForForms()} has made a new Admin post", "{$story->getUrl()}", true);
	        }
	    }*/
        return $story->toJSON();
    }

    function doPUT(){
        $story = Story::newFromId($this->getParam('id'));
        if($story == null || $story->getId() == 0){
            $this->throwError("This story does not exist");
        }
        elseif(!$story->canEdit()){
            $this->throwError("You are not allowed to edit this thread");
        }
        if(trim($this->POST('title')) == ""){
            $this->throwError("A title must be provided");
        }
        $story->id = $this->POST('id');
        $story->rev_id = $this->POST('rev_id');
        $story->user = $this->POST('user');
        $story->title = $this->POST('title');
        $story->story = $this->POST('story');
        $story->date_submitted = $this->POST('date_submitted');
        $status = $story->update();
        if(!$status){
            $this->throwError("The story could not be updated");
        }
        $story = Story::newFromId($this->getParam('id'));
        return $story->toJSON();
    }
    
    function doDELETE(){
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(MANAGER)){
            $thread = Story::newFromId($this->getParam('id'));
            $thread = $thread->delete();
            if($thread === false){
                $this->throwError("The story could not be deleted");
            }
            return $thread->toJSON();
        }
        return false;
    }
}

?>
