<?php
class StoryCommentAPI extends RESTAPI {

    function doGET(){
        if($this->getParam('id') != ""){
            $me = Person::newFromWgUser();
            $storycomment = StoryComment::newFromId($this->getParam('id'));
        /*    if(!$storycomment->canView()){
                permissionError();
            }*/
            return $storycomment->toJSON();
        }
    }

    function doPOST(){
        $me = Person::newFromWgUser();
        $post = new StoryComment(array());
        $post->setStoryId($this->POST('story_id'));
        $post->setUserId($me->getId());
        $post->setMessage($this->POST('message'));
        $post = $post->create();
        if($post === false){
            $this->throwError("The post could not be created");
        }
        return $post->toJSON();
    }

    function doPUT(){
        $me = Person::newFromWgUser();
        $post = StoryComment::newFromId($this->getParam('id'));
        if(!$post->canEdit()){
            $this->throwError("You must be logged in to view this post");
        }
        $post->setMessage($this->POST('message'));
        $post = $post->update();
        if($post === false){
            $this->throwError("The post could not be updated");
        }
        return $post->toJSON();
    }
    
    function doDELETE(){
        $me = Person::newFromWgUser();
        $post = StoryComment::newFromId($this->getParam('id'));
        if(!$post->canEdit()){
            $this->throwError("You must be logged in to view this post");
        }
        $post = $post->delete();
        if($post === false){
            $this->throwError("The post could not be updated");
        }
        return $post->toJSON();
    }
}


?>
