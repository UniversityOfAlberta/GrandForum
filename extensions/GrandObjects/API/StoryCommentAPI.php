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
	$thread = Thread::newFromId($this->POST('thread_id'));
        if(!$thread->canView()){
            permissionError();
        }
        $post->setThreadId($this->POST('thread_id'));
        $post->setUserId($me->getId());
        $post->setMessage($this->POST('message'));
        $post = $post->create();
        if($post === false){
            $this->throwError("The post could not be created");
        }
        return $post->toJSON();
    }

    function doPUT(){
        return false;
    }
    function doDELETE(){
        return false;
    }
}


?>
