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
        $thread->setCategory($this->POST('category'));
        $thread->setUserId($this->POST('author')->id);
	    $visibility = $this->POST('visibility');
	    if($visibility == "Chosen Experts"){
	        $authors = $this->POST('authors');
	        $authors[] = $me;
                $thread->setUsers($authors);
	    }
        $status = $thread->create();
        if($status === false){
            $this->throwError("The thread <i>{$thread->getTitle()}</i> could not be created");
        }
	    $thread = Thread::newFromTitle($this->POST('title'));
        if(!$me->isRoleAtLeast(MANAGER)){
            $people = Person::getAllPeople();
            foreach($people as $person){
		        if($person->isRoleAtLeast(MANAGER)){
                            //Notification::addNotification($me,$person,"New request for expert from {$me->getNameForForms()}", "{$me->getNameForForms()} has requested for an expert", "{$thread->getUrl()}");
		            Notification::addNotification($me,$person,"New request for expert from {$me->getNameForForms()}", "{$me->getNameForForms()} has requested for an expert", "{$thread->getUrl()}");
		        }
            }
        }
        return $status->toJSON();
    }

    function doPUT(){
        $me = Person::newFromWgUser();
        $thread = Thread::newFromId($this->getParam('id'));
	    $visibility = $this->POST('visibility');
        if($thread == null || $thread->getTitle() == ""){
            $this->throwError("This thread does not exist");
        }
        elseif(!$thread->canEdit()){
            $this->throwError("You are not allowed to edit this thread");
        }
        if($visibility == "All Experts"){
            $thread->setUsers(array());
        }
	    else{
	        $authors = $this->POST('authors');
            if(count($authors)>0){
                $previous_authors = $thread->getUsers();
                $ids = array();
                foreach($previous_authors as $person){
                    $ids[] = $person->getId();
                }
                if(!in_array($me->getId(), $ids)){
	                $authors[] = $me;
                }
                $thread->setUsers($authors);
            }
	    }
        $thread->setTitle($this->POST('title'));
        $thread->setCategory($this->POST('category'));
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
