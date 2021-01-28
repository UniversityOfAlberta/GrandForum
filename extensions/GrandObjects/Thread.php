<?php
/**
 * @package GrandObjects
 */



// require 'FirePHPCore/fb.php';



class Thread extends BackboneModel{

	var $id;
	var $user_id; //person who created thread
	var $users = array();
	var $title;
    var $category;
	var $posts = array();
	var $date_created;
    var $visibility;
    var $approved;
    var $public;

//-----Static Functions/Constructor---//
        // Constructor
        function __construct($data){
            if(count($data) > 0){
                $this->id = $data[0]['id'];
                $this->user_id = $data[0]['user_id'];
		        $this->users = unserialize($data[0]['users']);
                $this->title = $data[0]['title'];
                $this->category = $data[0]['category'];
                $this->date_created = $data[0]['date_created'];
		        $this->posts = $this->getPosts();
                $this->visibility = $data[0]['visibility'];
                $this->public = $data[0]['public'];
                $this->approved = $data[0]['approved'];
            }
        }

        	/**
         	* Returns a new Thread from the given id
         	* @param id $id The id of the Thread
         	* @return Thread The Thread with the given id
         	*/	
	    static function newFromId($id){
	        $data = DBFunctions::select(array('grand_threads'),
	                                    array('*'),
	                                    array('id' => $id));
	        $thread = new Thread($data);
	        return $thread;
	    }

        static function newFromTitle($title){
            $thread = new Thread(array());
            $data = DBFunctions::select(array('grand_threads'),
                                        array('*'),
                                        array('title' => $title),
                                        array('date_created' => 'DESC'));
            if(count($data)>0){
                $thread = new Thread($data);
            }
            return $thread;
        }

        /**
        * Returns all Threads available to a user
        * @return threads An Array of Threads
        */
        static function getAllThreads(){
            $threads = array();
            $me = Person::newFromWgUser();
	    $meId = ($me->getId() ?: "0");
            $meName = str_replace(".", " ", $me->getName());
            if($me->isRoleAtLeast(MANAGER)){
                $data = DBFunctions::select(array('grand_threads'),
                                            array('id'));
            }
            else{
		$statement = "SELECT * FROM `grand_threads` WHERE `users` LIKE '%\"$meId\"%' OR (`approved` = 1 AND `visibility` = 'question is visible to CAPS health care professionals')
			      OR `user_id` LIKE $meId OR `users` LIKE '%\"$meName\"%'";
                $data = DBFunctions::execSQL($statement);
            }
            if(count($data) >0){
                foreach($data as $threadId){
                    $thread = Thread::newFromId($threadId['id']);
                    $threads[] = $thread;
                }
            }
            if($me->isRoleAtLeast(Expert)){
                $statement = "SELECT * FROM `grand_threads` WHERE `users` LIKE 'a:0:{}'";
                $data = DBFunctions::execSQL($statement);
            }
            if(count($data) >0){
                foreach($data as $threadId){
                    $thread = Thread::newFromId($threadId['id']);
                    $threads[] = $thread;
                }
            }
            return $threads;
        }

    //-----Getters----//	
	    // Returns the id of this Story
	    function getId(){
	        return $this->id;
	    }

        function getThreadOwner(){
            $person = "";
            if($this->user_id != ""){
                $person = Person::newFromId($this->user_id);
            }
            return $person;
        }

        function getUsers(){
	        $people = array();
	        foreach($this->users as $pId){
		        if(is_numeric($pId)){	
	            	    $person = Person::newFromId($pId);
		        }
		    else{
		        $person= Person::newFromName($pId);
		    }
		    $people[] = $person;
	        }
                return $people;
        }

        function getTitle(){
            return $this->title;
        }

	    function getPosts(){
	        $posts = array();
	        $data = DBFunctions::select(array('grand_posts'),
					        array('*'),
					        array('thread_id'=>$this->getId()));
	        foreach($data as $row){
		    $posts[] = Post::newFromId($row['id']);
	        }
	        return $posts;
	
	    }

        function getDateCreated(){
            return $this->date_created;
        }

        function getApproved(){
            return $this->approved;
        }

	    function getCategory(){
	        return $this->category;
	    }

//-----Setters----//
        function setId($id){
            $this->id = $id;
        }

        function setUserId($id){
            $this->user_id = $id;
        }   

        function setUsers($users){
            $this->users = $users;
        }

        function setTitle($title){
            $this->title = $title;
        }   

        function setCategory($category){
            $this->category = $category;
        }

        function setDateCreated($date){
            $this->date_created = $date;
        }

        function setVisibility($visibility){
            $this->visibility = $visibility;
        }
        function setApproved($approved){
            $this->approved = $approved;
        }
        function setPublic($public){
            $this->public = $public;
        }

//-----Db methods----//
	
	function create(){
            $me = Person::newFromWgUser();
	    if($this->user_id == ""){
		    $this->user = $me->getId();
	    }
            $users = array();
            foreach($this->users as $user){
                if(isset($user->id) && $user->id != 0){
                    $users[] = $user->id;
                }
                else if(isset($user->fullname)){
                    $users[] = $user->fullname;
                }
                else{
                    $users[] = $user->name;
                }
            }
            if($me->isRoleAtLeast(EXTERNAL)){
                DBFunctions::begin();
                $status = DBFunctions::insert('grand_threads',
                                              array('user_id' => $this->user_id,
						                            'users' => serialize($users),
                                                    'title' => $this->title,
                                                    'category' => $this->category,
                                                    'approved' => $this->approved,
                                                    'public' => $this->public,
                                                    'visibility'=> $this->visibility

                                                ), true);
                $this->id = DBFunctions::insertId();
                DBFunctions::commit();
                if($status){
                    return $this;
                }
            }
            return false;
	}

	//this should be updated eventually when revisions of a story can be made
	function update(){
            $users = array();
            $me = Person::newFromWgUser();
            foreach($this->users as $user){
                if($user == null){
                    continue;
                }
                if(isset($user->id) && $user->id != 0){
                    $users[] = $user->id;
                }
                else if(isset($user->fullname)){
                    $users[] = $user->fullname;
                }
                else{
                    $users[] = $user->name;
                }
            }
            if($me->isRoleAtLeast(MANAGER) || ($me->getId() == $this->user_id)){
                $status = DBFunctions::update('grand_threads',
                                              array('users'=>serialize($users),
                                                    'user_id' => $this->user_id,
                                                    'title' => $this->getTitle(),
                                                    'category' => $this->category,
                                                    'date_created' => $this->getDateCreated(),

                                                    'approved' => $this->approved,

                                                    'public' => $this->public,
                                                    'visibility'=> $this->visibility),
                                              array('id' => EQ($this->id)));
                if($status){
		            DBFunctions::commit();
                    return true;
                }
            }
            return false;
	}

    function delete(){
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(MANAGER) || ($me->getId() == $this->user_id)){
            DBFunctions::begin();
            $status = DBFunctions::delete('grand_threads',
                                          array('id' => EQ($this->id)));
            if($status){
	            $this->id = null;
                DBFunctions::commit();
                return $this;
            }
        }
        return false;
    }
//--------General Functions-------//
        function canView(){
            $me = Person::newFromWgUser();
            $bool = false;
            $threads = Thread::getAllThreads();
            $ids = array();
            foreach($threads as $thread){
                $ids[] = $thread->getId();
            }
            if($me->isLoggedIn() && !$me->isCandidate() && 
                ($me->getId() === $this->getThreadOwner()->getId() || $me->isRoleAtLeast(MANAGER) || in_array($this->getId(), $ids)) || 
                ($this->visibility == "question is visible to CAPS health care professionals" && $this->getApproved())){
                $bool = true;
            }
            return $bool;
        }

        function canEdit(){
            $me = Person::newFromWgUser();
            $bool = false;
            if($me->isLoggedIn() && $me->isRoleAtLeast(MANAGER)){
                $bool = true;
            }
            return $bool;
        }

        function addUser($person){
            $this->users[] = $person;
        }

        function toArray(){
            global $wgUser;
            if(!$wgUser->isLoggedIn()){
                return array();
            }
            $user = Person::newFromId($this->user_id);
            $author = array('id'=> $user->getId(),
                    'name' => $user->getNameForForms(),
                    'url' => $user->getUrl());
            $authors = array();
            foreach($this->getUsers() as $user){
            $authors[] = array('id'=>$user->getId(),
                               'name' => $user->getNameForForms(),
                               'url' => $user->getUrl());
            }
            $json = array('id' => $this->getId(),
                          'author' => $author,
                          'users' => $authors,
                          'authors' => $this->getUsers(),
                          'title' => $this->getTitle(),
                          'posts' => $this->getPosts(),
                          'url' => $this->getUrl(),
                          'category' => $this->getCategory(),
                          'date_created' => $this->getDateCreated(),
                          'approved' => $this->getApproved(),
                          'visibility' => $this->visibility);
            return $json;
        }

        function exists(){
            $thread = Thread::newFromId($this->getId());
            return ($thread != null && $thread->getId() != "");
        }

        function getCacheId(){
            global $wgSitename;
        }

    /**
     * Returns the url of this Paper's page
     * @return string The url of this Paper's page
     */
    function getUrl(){
        global $wgServer, $wgScriptPath;
        if(!isset($_GET['embed']) || $_GET['embed'] == 'false'){
            return "{$wgServer}{$wgScriptPath}/index.php/Special:MyThreads#/{$this->getId()}";
        }
        return "{$wgServer}{$wgScriptPath}/index.php/Special:MyThreads?embed#/{$this->getId()}";
    }
}

?>
