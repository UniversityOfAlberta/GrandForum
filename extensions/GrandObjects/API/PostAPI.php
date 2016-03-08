<?php
class PostAPI extends RESTAPI {

    function doGET(){
        if($this->getParam('id') != ""){
            $me = Person::newFromWgUser();
            $post = Post::newFromId($this->getParam('id'));
            if(!$post->canView()){
                permissionError();
            }
            return $post->toJSON();
        }
    }

    function doPOST(){
        $me = Person::newFromWgUser();
        $post = new Post(array());
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

class PostsAPI extends RESTAPI {

    function doGET(){
        return false;
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

class PersonPostAPI extends RESTAPI {

    function doGET(){
        return false;

    }

    function doPOST(){
        return false;
    }

    function doPUT(){
        return $this->doGET();
    }

    function doDELETE(){
        return false;
    }

}
?>
