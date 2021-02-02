<?php
class PostAPI extends RESTAPI {

    function doGET(){
        if($this->getParam('id') != ""){
            $me = Person::newFromWgUser();
            $post = Post::newFromId($this->getParam('id'));
            if(!$post->canView()){
                $this->throwError("You must be logged in to view this post");
            }
            return $post->toJSON();
        }
    }

    function doPOST(){
        $me = Person::newFromWgUser();
        $post = new Post(array());
        $thread = Thread::newFromId($this->POST('thread_id'));
        if(!$thread->canView()){
            $this->throwError("You must be logged in to view this post");
        }
        $post->setThreadId($this->POST('thread_id'));
        $post->setUserId($me->getId());
        $post->setMessage($this->POST('message'));
        $post = $post->create();
        $people = array_merge(array($thread->getThreadOwner()), $thread->getUsers());
        foreach($people as $person){
            if($person->getId() != $me->getId()){
                Notification::addNotification($me,$person,"New post on thread '{$thread->getTitle()}'", "A new post has been added to '{$thread->getTitle()}'", "{$thread->getUrl()}");
            }
        }
        //add notification send here
        if($post === false){
            $this->throwError("The post could not be created");
        }
        return $post->toJSON();
    }

    function doPUT(){
        $me = Person::newFromWgUser();
        $post = Post::newFromId($this->getParam('id'));
        if(!$post->canEdit()){
            $this->throwError("You must be logged in to view this post");
        }
        $post->setMessage($this->POST('message'));
        $post = $post->update();
        if($post === false){
            $this->throwError("The post could not be created");
        }
        return $post->toJSON();
    }
    
    function doDELETE(){
        $me = Person::newFromWgUser();
        $post = Post::newFromId($this->getParam('id'));
        if(!$post->canEdit()){
            $this->throwError("You must be logged in to view this post");
        }
        $post = $post->delete();
        if($post === false){
            $this->throwError("The post could not be created");
        }
        return $post->toJSON();
    }
}

?>
