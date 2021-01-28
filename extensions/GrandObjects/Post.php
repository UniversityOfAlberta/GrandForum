<?php
/**
 * @package GrandObjects
 */
class Post extends BackboneModel{

    var $id;
    var $thread_id;
    var $user_id;
    var $message;
    var $date_created;

//-----Static Functions/Constructor---//
    // Constructor
    function __construct($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->thread_id = $data[0]['thread_id'];
            $this->user_id = $data[0]['user_id'];
            $this->message = $data[0]['message'];
            $this->date_created = $data[0]['date_created'];
        }
    }

    /**
     * Returns a new Post from the given id
     * @param id $id The id of the Post
     * @return Post The Post with the given id
     */
    static function newFromId($id){
        $data = DBFunctions::select(array('grand_posts'),
                                    array('*'),
                                    array('id' => $id));
        $post = new Post($data);
        return $post;
    }
    
//-----Getters----//
    // Returns the id of this Story
    function getId(){
        return $this->id;
    }

    function getThreadId(){
        return $this->thread_id;
    }

    function getUser(){
        $person = "";
        if($this->user_id != ""){
            $person = Person::newFromId($this->user_id);
        }
        return $person;
    }

    function getMessage(){
        return $this->message;
    }

    function getDateCreated(){
        return $this->date_created;
    }

//-----Setters----//
    function setId($id){
        return $this->id = $id;
    }

    function setThreadId($id){
        return $this->thread_id = $id;
    }   

    function setUserId($id){
        return $this->user_id = $id;
    }

    function setMessage($message){
        return $this->message = $message;
    }   

    function setDateCreated($date){
        return $this->date_created = $date;
    }   

//-----Db methods----//
    function create(){
        $me = Person::newFromWgUser();
        if($this->getUser() == ""){
            $this->user_id = $me->getId();
        }
        DBFunctions::begin();
        $status = DBFunctions::insert('grand_posts',
                                      array('thread_id' => $this->thread_id,
                                            'user_id' => $this->user_id,
                                            'message' => $this->getMessage()), true);
        $data = DBFunctions::select(array('grand_posts'),
                                    array('id'),
                                    array('thread_id' =>$this->thread_id),
                                    array('id'=>'desc'));
        if($status && count($data)>0){
            DBFunctions::commit();
            return Post::newFromId($data[0]['id']);
        }
        return false;
    }

    //this should be updated eventually when revisions of a story can be made
    function update(){
        $me = Person::newFromWgUser();
        if($this->canEdit()){
            $status = DBFunctions::update('grand_posts',
                                          array('thread_id' => $this->thread_id,
                                                'user_id' => $this->getUser()->getId(),
                                                'message' => $this->getMessage(),
                                                'date_created' => $this->getDateCreated()),
                                          array('id' => EQ($this->id)));
            if($status){
                DBFunctions::commit();
                return $this;
            }
        }
        return false;
    }

    function delete(){
        $me = Person::newFromWgUser();
        if($this->canEdit()){
            DBFunctions::begin();
            $status = DBFunctions::delete('grand_posts',
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
        $thread = Thread::newFromId($this->getThreadId());
        return $thread->canView();
    }
    
    function canEdit(){
        $me = Person::newFromWgUser();
        return ($me->isRoleAtLeast(STAFF) || ($me->getId() == $this->user_id));
    }

    function toArray(){
        global $wgUser;
        if(!$wgUser->isLoggedIn()){
            return array();
        }
        $user = Person::newFromId($this->getUser()->getId());
        $author = array('id'=> $user->getId(),
                        'name' => $user->getNameForForms(),
                        'url' => $user->getUrl(),
                        'photo' => $user->getPhoto());
        $json = array('id' => $this->getId(),
                      'thread_id' => $this->getThreadId(),
                      'user_id' => $this->getUser()->getId(),
                      'author' => $author,
                      'message' => $this->getMessage(),
                      'date_created' => $this->getDateCreated());
        return $json;
    }

    function exists(){
        $post = Post::newFromId($this->getId());
        return ($post != null && $post->getId() != "");
    }

    function getCacheId(){
        global $wgSitename;
    }
}
?>
