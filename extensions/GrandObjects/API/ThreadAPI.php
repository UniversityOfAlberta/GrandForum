<?php
class ThreadAPI extends RESTAPI {

    function doGET(){
        if($this->getParam('id') != ""){
            $me = Person::newFromWgUser();
            $thread = Thread::newFromId($this->getParam('id'));
            if(!$thread->canView()){
                permissionError();
            }
            return $thread->toJSON();
        }
    }

    function doPOST(){
        $me = Person::newFromWgUser();
        $thread = new Thread(array());
        $thread->setTitle($this->POST('title'));
        $thread->setUserId($this->POST('author')->id);
	if($me->isRoleAtLeast(MANAGER)){
            $thread->setUsers($this->POST('authors'));
	}
        $me = Person::newFromWgUser();
	if(count($this->POST('authors')) == 0){
	    $thread->setUsers(array($me));
	}
        $status = $thread->create();
        if($status === false){
            $this->throwError("The thread <i>{$thread->getTitle()}</i> could not be created");
        }
        return $status->toJSON();
    }

    function doPUT(){
        $me = Person::newFromWgUser();
        $thread = Thread::newFromId($this->getParam('id'));
        if($thread == null || $thread->getTitle() == ""){
            $this->throwError("This thread does not exist");
        }
	elseif(!$thread->canEdit()){
            permissionError();
	}
        if(count($this->POST('authors')) == 0){
            $thread->setUsers(array($me));
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
