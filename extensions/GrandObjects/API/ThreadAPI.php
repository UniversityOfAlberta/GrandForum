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
        if($this->getParam('search') == ""){
            $threads = new Collection(Thread::getAllThreads());
        }
        else{
            $threads = array();
            $search = DBFunctions::escape(str_replace('%', '\%', strtolower($this->getParam('search'))));
            $data = DBFunctions::execSQL("SELECT DISTINCT t.id
                                          FROM grand_posts p, grand_threads t
                                          WHERE p.thread_id = t.id
                                          AND (MATCH(p.message) AGAINST ('{$search}') OR 
                                               LOWER(t.title)   LIKE '%{$search}%')");
            foreach($data as $row){
                $thread = Thread::newFromId($row['id']);
                if($thread->canView()){
                    $threads[] = $thread;
                }
            }
            $threads = new Collection($threads);
        }
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
