<?php

class StoryAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            $me = Person::newFromWgUser();
            $story = Story::newFromId($this->getParam('id'));
            if(!$me->isLoggedIn() || ($me->getId() != $story->user && !($me->isRoleAtLeast(MANAGER)))){
		permissionError();
            }
            return $story->toJSON();
        }
    }

    function doPOST(){
        $story = new Story(array());
        $story->user = $this->POST('user');
        $story->title = $this->POST('title');
        $story->story = $this->POST('story');
        $story->date_submitted = $this->POST('date_submitted');
        $story->approved = $this->POST('approved');
        if($story->exists()){
            $this->throwError("A story like that  already exists");
        }
        $status = $story->create();
        if(!$status){
            $this->throwError("The user <i>{$story->getName()}</i> could not be created");
        }
        $story = Story::newFromId($story->getId());
        return $story->toJSON();
    }

    function doPUT(){
        $story = Story::newFromId($this->getParam('id'));
        if($story == null || $story->getTitle() == ""){
            $this->throwError("This story does not exist");
        }
        $story->id = $this->POST('id');
        $story->user = $this->POST('user');
        $story->title = $this->POST('title');
        $story->story = $this->POST('story');
        $story->date_submitted = $this->POST('date_submitted');
        $story->approved = $this->POST('approved');
        $status = $story->update();
        if(!$status){
            $this->throwError("The story could not be updated");
        }
        $story = Story::newFromId($this->getParam('id'));
        return $story->toJSON();
    }
    function doDELETE(){
        return false;
    }

   /* 
    function doDELETE(){
        $person = Person::newFromId($this->getParam('id'));
        if($person == null || $person->getName() == ""){
            $this->throwError("This user does not exist");
        }
        $status = $person->delete();
        if(!$status){
            $this->throwError("The user <i>{$person->getName()}</i> could not be deleted");
        }
    }*/
}

class StoriesAPI extends RESTAPI {
    
    function doGET(){
        $me = Person::newFromWgUser();
        $stories = new Collection(Story::getAllUserStories());
        return $stories->toJSON();
    }
    
    function doPOST(){
        return false;
    }
    
    function doPUT(){
        return false;
    }
    
    function doDELETE(){
        return false;
    }

}
?>
