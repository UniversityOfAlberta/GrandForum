<?php
class ThreadAPI extends RESTAPI {

    function doGET(){
        if($this->getParam('id') != ""){
            $me = Person::newFromWgUser();
            $thread = Thread::newFromId($this->getParam('id'));
            if(!$me->isLoggedIn() || ($me->getId() != $thread->getThreadOwner()->getId() && !($me->isRoleAtLeast(MANAGER)))){
                permissionError();
            }
            return $thread->toJSON();
        }
    }

    function doPOST(){
        $thread = new Thread(array());
        $thread->setTitle($this->POST('title'));
        $thread->setUserId($this->POST('author')->id);
        $thread->setUsers($this->POST('authors'));
        $status = $thread->create();
        if(!$status){
            $this->throwError("The thread <i>{$thread->getTitle()}</i> could not be created");
        }
        return true;
    }

    function doPUT(){
        $thread = Thread::newFromId($this->getParam('id'));
        if($thread == null || $thread->getTitle() == ""){
            $this->throwError("This thread does not exist");
        }
        $thread->setTitle($this->POST('title'));
        $thread->setUsers($this->POST('authors'));
        $status = $thread->update();
        if(!$status){
            $this->throwError("The thread <i>{$thread->getTitle()}</i> could not be updated");
        }
        $thread = Thread::newFromId($this->getParam('id'));
        return $thread->toJSON();
    }


    function doDELETE(){
        return false;
    }
}

class ThreadsAPI extends RESTAPI {

    function doGET(){
        $me = Person::newFromWgUser();
        $threads = new Collection(Thread::getAllThreads());
        return $threads->toJSON();
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
