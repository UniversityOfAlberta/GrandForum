<?php
/**
 * @package GrandObjects
 */
class Thread extends BackboneModel {

    var $id;
    var $board_id;
    var $stickied;
    var $user_id; //person who created thread
    var $users = array();
    var $roles = array();
    var $title;
    var $posts = array();
    var $date_created;

//-----Static Functions/Constructor---//
        // Constructor
        function __construct($data){
            if(count($data) > 0){
                $this->id = $data[0]['id'];
                $this->board_id = $data[0]['board_id'];
                $this->stickied = $data[0]['stickied'];
                $this->user_id = $data[0]['user_id'];
                $this->users = unserialize($data[0]['users']);
                $this->roles = unserialize($data[0]['roles']);
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
    static function getAllThreads($board_id=0){
        global $wgRoleValues;
        $threads = array();
        $me = Person::newFromWgUser();
        $meId = ($me->getId() ?: "0");
        $meName = $me->getNameForForms();
        if($me->isRoleAtLeast(MANAGER)){
            $data = DBFunctions::select(array('grand_threads'),
                                        array('id'));
        }
        else{
            $statement = "SELECT * FROM `grand_threads` WHERE `users` LIKE '%\"$meId\"%'";
            //      OR `users` LIKE '%\"$meName\"%'";     //used if we want to add individual people to each thread
            $roles = $me->getAllowedRoles();
            foreach($roles as $role){
                $statement .= " OR `roles` LIKE '%\"{$role}\"%'";
            }
            $statement .= " OR `roles` LIKE '%\"\"%'";
            $data = DBFunctions::execSQL($statement);
        }
        if(count($data) >0){
            foreach($data as $threadId){
                $thread = Thread::newFromId($threadId['id']);
                if($thread->getBoardId() == $board_id){
                    $threads[] = $thread;
                }
            }
        }
        return $threads;
    }

//-----Getters----//
    // Returns the id of this Story
    function getId(){
        return $this->id;
    }
    
    function getBoardId(){
        return $this->board_id;
    }
    
    function getBoard(){
        return Board::newFromId($this->getBoardId());
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

    function getRole(){
        return $this->roles[0];
    }

    function getTitle(){
        return $this->title;
    }

    function getPosts(){
        $posts = array();
        $data = DBFunctions::select(array('grand_posts'),
                                    array('id'),
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
    
    function setRoles($data){
        $this->roles = array($data);
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
        DBFunctions::begin();
        $status = DBFunctions::insert('grand_threads',
                                      array('board_id' => $this->board_id,
                                            'stickied' => $this->stickied,
                                            'user_id' => $this->user_id,
                                            'users' => serialize($users),
                                            'roles' => serialize($this->roles),
                                            'title' => $this->title), true);
        if($status){
            $this->id = DBFunctions::insertId();
            DBFunctions::commit();
            return Thread::newFromId($this->id);
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
        if($this->canEdit() || $me->getId() == $this->user_id){
            $status = DBFunctions::update('grand_threads',
                                          array('board_id' => $this->board_id,
                                                'stickied' => $this->stickied,
                                                'users'=>serialize($users),
                                                'roles' =>serialize($this->roles),
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
        if($this->canEdit() || ($me->getId() == $this->user_id)){
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

    function canView(){
        $me = Person::newFromWgUser();
        $bool = false;
        $threads = Thread::getAllThreads();
        $ids = array();
        foreach($threads as $thread){
            $ids[] = $thread->getId();
        }
        if($me->isLoggedIn() && ($me->getId() === $this->getThreadOwner()->getId() ||
                                 $me->isRoleAtLeast(MANAGER) ||
                                 $me->isBoardMod() ||
                                 in_array($this->getId(), $ids) ||
                                 in_array($this->getRole(), $me->getAllowedRoles()) ||
                                 $this->getRole() == "")){
            $bool = true;
        }
        return $bool;
    }

    function canEdit(){
        $me = Person::newFromWgUser();
        $bool = false;
        if($me->isLoggedIn() && in_array($this->getRole(), $me->getAllowedRoles()) || $me->isBoardMod()){
            $bool = true;
        }
        return true;
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
        $posts = array();
        foreach($this->getPosts() as $post){
            $posts[] = array('id' => $post->getId());
        }
        $json = array('id' => $this->getId(),
                      'board_id' => $this->getBoardId(),
                      'stickied' => $this->stickied,
                      'author' => $author,
                      'users' => $authors,
                      'authors' => $authors,
                      'roles' => $this->getRole(),
                      'title' => $this->getTitle(),
                      'posts' => $posts,
                      'board' => $this->getBoard()->toArray(),
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
            return "{$wgServer}{$wgScriptPath}/index.php/Special:MyThreads#/{$this->getBoardId()}/{$this->getId()}";
        }
        return "{$wgServer}{$wgScriptPath}/index.php/Special:MyThreads?embed#/{$this->getBoardId()}/{$this->getId()}";
    }
}

?>
