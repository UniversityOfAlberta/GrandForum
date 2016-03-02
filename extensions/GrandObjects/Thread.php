<?php
/**
 * @package GrandObjects
 */
class Thread extends BackboneModel{

	var $id;
	var $user_id; //person who created thread
	var $users = array();
	var $title;
	var $posts = array();
	var $date_created;

//-----Static Functions/Constructor---//
        // Constructor
        function Thread($data){
            if(count($data) > 0){
                $this->id = $data[0]['id'];
                $this->user_id = $data[0]['user_id'];
		$this ->users = $data[0]['users'];
                $this->title = $data[0]['title'];
                $this->date_created = $data[0]['date_created'];
		$this->posts = $this->getPosts();
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

        /**
        * Returns all Threads available to a user
        * @return threads An Array of Threads
        */
        static function getAllThreads(){
            $threads = array();
            $me = Person::newFromWgUser();
	    $meId = ($me->getId() ?: "0");
            $meName = $me->getNameForForms();
            if($me->isRoleAtLeast(MANAGER)){
                $data = DBFunctions::select(array('grand_threads'),
                                            array('id'));
            }
            else{
                /*$data = DBFunctions::select(array('grand_user_threads'),
                                            array('id'),
                                            array('user_id'=>$me->getId()));*/

		$statement = "SELECT * FROM `grand_threads` WHERE `users` LIKE '%\"$meId\"%'
			      OR `user_id` LIKE $meId OR `users` LIKE '%\"$meName\"%'";
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
	    foreach(unserialize($this->users) as $pId){
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

        function setDateCreated($date){
            $this->date_created = $date;
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
            if($me->isRoleAtLeast(AR)){
                DBFunctions::begin();
                $status = DBFunctions::insert('grand_threads',
                                              array('user_id' => $this->user_id,
						    'users' => serialize($users),
                                                    'title' => $this->title), true);
                if($status){
                    DBFunctions::commit();
                    return true;
                }
            }
            return false;
	}

	//this should be updated eventually when revisions of a story can be made
	function update(){
            $me = Person::newFromWgUser();
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
            if($me->isRoleAtLeast(ADMIN) || ($me->getId() == $this->user_id)){
                $status = DBFunctions::update('grand_threads',
                                              array('users'=>serialize($users),
                                                    'user_id' => $this->user_id,
						    'title' => $this->getTitle(),
                                                    'date_created' => $this->getDateCreated()),
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
            if($me->isRoleAtLeast(ADMIN) || ($me->getId() == $this->user_id)){
                DBFunctions::begin();
                $status = DBFunctions::delete('grand_threads',
                                              array('id' => EQ($this->rev_id)));
                if($status){
		    DBFunctions::commit();
                    return true;
                }
            }
            return false;
	}
//--------General Functions-------//

        function toArray(){
            global $wgUser;
            if(!$wgUser->isLoggedIn()){
		return array();
            }
	    $user = Person::newFromId($this->user_id);
	    $author = array('id'=> $user->getId(),
			    'name' => $user->getNameForForms(),
			    'url' => $user->getUrl());
            $json = array('id' => $this->getId(),
			  'author' => $author,
			  'authors' => $this->getUsers(),
			  'title' => $this->getTitle(),
			  'posts' => $this->getPosts(),
                          'url' => $this->getUrl(),
                          'date_created' => $this->getDateCreated());
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
