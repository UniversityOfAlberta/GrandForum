<?php
class ThreadAPI extends RESTAPI {

    function doGET(){
        if($this->getParam('id') != ""){
            $me = Person::newFromWgUser();
            $thread = Thread::newFromId($this->getParam('id'));
            if(!$thread->canView()){
                $this->throwError("You must be logged in to view this thread");
            }
            return $thread->toJSON();
        }
    }

    function doPOST(){
        $me = Person::newFromWgUser();
        $thread = new Thread(array());
        $thread->board_id = $this->POST('board_id');
        if($me->isBoardMod()){
            $thread->stickied = $this->POST('stickied');
        }
        $thread->setTitle($this->POST('title'));
        $thread->setUserId($this->POST('author')->id);
        $thread->setUsers($this->POST('authors'));
        if(!in_array($this->POST('roles'),$me->getAllowedRoles()) && $this->POST('roles') != ""){
            $this->throwError("You cannot use select that role");
        }
        $thread->setRoles($this->POST('roles'));
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
        if(!in_array($this->POST('roles'),$me->getAllowedRoles()) && $this->POST('roles') != ""){
            $this->throwError("You cannot use select that role");
        }
        if($thread == null || $thread->getTitle() == ""){
            $this->throwError("This thread does not exist");
        }
        elseif(!$thread->canEdit()){
            $this->throwError("You are not allowed to edit this thread");
        }
        if(count($this->POST('authors')) == 0){
            $thread->setUsers(array($me));
        }
        $thread->board_id = $this->POST('board_id');
        if($me->isBoardMod()){
            $thread->stickied = $this->POST('stickied');
        }
        $thread->setRoles($this->POST('roles'));
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

?>
