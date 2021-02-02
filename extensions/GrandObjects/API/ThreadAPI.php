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
        if(trim($this->POST('title')) == ""){
            $this->throwError("A title must be provided");
        }
        $thread = new Thread(array());
        $thread->setTitle($this->POST('title'));
        $thread->setCategory($this->POST('category'));
        $thread->setUserId($this->POST('author')->id);
        $thread->setVisibility($this->POST('visibility'));
        $thread->setPublic($this->POST('public'));
        $thread->setApproved($this->POST('approved'));
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
        if(!$me->isRoleAtLeast(MANAGER)){
            $people = Person::getAllPeople();
            foreach($people as $person){
		        if($person->isRoleAtLeast(MANAGER)){
                            //Notification::addNotification($me,$person,"New request for expert from {$me->getNameForForms()}", "{$me->getNameForForms()} has requested for an expert", "{$thread->getUrl()}");
		            Notification::addNotification($me,$person,"New request for expert from {$me->getNameForForms()}", "{$me->getNameForForms()} has requested for an expert", "{$thread->getUrl()}", true);
		        }
            }
        }
        return $thread->toJSON();
    }

    function doPUT(){
        $me = Person::newFromWgUser();
        $thread = Thread::newFromId($this->getParam('id'));
	    $visibility = $this->POST('visibility');
        if($thread == null || $thread->getId() == 0){
            $this->throwError("This thread does not exist");
        }
        elseif(!$thread->canEdit()){
            $this->throwError("You are not allowed to edit this thread");
        }
        if(trim($this->POST('title')) == ""){
            $this->throwError("A title must be provided");
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
                foreach($authors as $a){
                    $author = Person::newFromNameLike($a->name);
                    if(!in_array($author->getId(), $ids)){
                        // This is a new author, send them a notification
                        $expert = Person::newFromId($author->getId());
                        Notification::addNotification(null, $expert, "Ask an Expert Assignment", "You have been assigned a to respond to an 'Ask an Expert'", "{$thread->getUrl()}", true);
                    }
                }
                /*if(!in_array($me->getId(), $ids)){
	                $authors[] = $me;
                }*/
            }
            $thread->setUsers($authors);
	    }
        $thread->setTitle($this->POST('title'));
        $thread->setCategory($this->POST('category'));
        $thread->setVisibility($this->POST('visibility'));
        $thread->setApproved($this->POST('approved'));

        $status = $thread->update();
        if(!$status){
            $this->throwError("The thread <i>{$thread->getTitle()}</i> could not be updated");
        }
        $thread = Thread::newFromId($this->getParam('id'));
        return $thread->toJSON();
    }

    function doDELETE(){
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(MANAGER)){
            $thread = Thread::newFromId($this->getParam('id'));
            $thread = $thread->delete();

            if($thread === false){
                $this->throwError("The thread could not be deleted");
            }
            return $thread->toJSON();
        }
        return false;
    }
}

?>
