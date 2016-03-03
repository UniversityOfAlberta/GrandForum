<?php
/**
 * @package GrandObjects
 */
class Story extends BackboneModel{

	var $id;
	var $rev_id;
	var $user;
	var $title;
	var $story;
    	var $date_submitted;
    	var $approved = false;

//-----Static Functions/Constructor---//
        // Constructor
        function Story($data){
            if(count($data) > 0){
                $this->id = $data[0]['id'];
                $this->rev_id = $data[0]['rev_id'];
                $this->title = $data[0]['title'];
                $this->user = $data[0]['user_id'];
                $this->story = $data[0]['story'];
                $this->date_submitted = $data[0]['date_submitted'];
                $this->approved = $data[0]['approved'];
            }
        }

    	/**
     	* Returns a new Story from the given id
     	* @param id $id The id of the Paper
     	* @return Story The Story with the given id
     	*/	
	static function newFromId($id){
	    $data = DBFunctions::select(array('grand_user_stories'),
	                                array('*'),
	                                array('rev_id' => $id));
	    $story = new Story($data);
	    return $story;
	}
	
        static function getAllUserStories(){
            $stories = array();
            $me = Person::newFromWgUser();
	    if($me->isRoleAtLeast(MANAGER)){
            	$data = DBFunctions::select(array('grand_user_stories'),
                	                    array('rev_id'));
	    }
	    else{
                $data = DBFunctions::select(array('grand_user_stories'),
                                            array('rev_id'),
					    array('user_id'=>$me->getId()));
	    }
            if(count($data) >0){
                foreach($data as $storyId){
                    $story = Story::newFromId($storyId['rev_id']);
                    $stories[] = $story;
                }
            }
            return $stories;
        }

        static function getAllUnapprovedStories(){
            $stories = array();
            $data = DBFunctions::select(array("grand_user_stories"),
                                        array("rev_id"),
                                        array("approved"=>EQ(COL(0))));
            foreach($data as $row){
                $stories[] = Story::newFromId($row['rev_id']);
            }

            return $stories;
        }

//-----Getters----//	
	// Returns the id of this Story
	function getId(){
	    return $this->id;
	}

        function getRevId(){
            return $this->rev_id;
        }

        function getUser(){
	    $person = "";
	    if($this->user != ""){
	    	$person = Person::newFromId($this->user);
	    }
            return $person;
        }

        function getTitle(){
            return $this->title;
        }

        function getStory(){
            return $this->story;
        }

        function getDateSubmitted(){
            return $this->date_submitted;
        }

        function getApproved(){
            return $this->approved;
        }

//-----Setters----//
        function setId($id){
            return $this->id = $id;
        }

        function setRevId($id){
            return $this->rev_id = $id;
        }   

        function setUser($id){
            return $this->user = $id;
        }

        function setTitle($title){
            return $this->title = $title;
        }   

        function setStory($story){
            return $this->story = $story;
        }   

        function setDateSubmitted($date){
            return $this->date_submitted = $date;
        }   

        function setApproved($bool){
            return $this->approved = $bool;
        }
//-----Db methods----//
	
	function create(){
            $me = Person::newFromWgUser();
	    if($this->getUser() == ""){
		$this->user = $me->getId();
	    }
	    $data = DBFunctions::select(array('grand_user_stories'),
					array('*'),
					array());
	    $id = count($data)+1;//have to manually add for now because may do revisions in the future
            if($me->isRoleAtLeast(AR)){
                DBFunctions::begin();
                $status = DBFunctions::insert('grand_user_stories',
                                              array('id' => $id,
						    'user_id' => $this->user,
						    'title' => $this->getTitle(),
                                                    'story' => $this->getStory(),
                                                    'approved' => 0),true);
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
            if($me->isRoleAtLeast(ADMIN) || ($me->getId() == $this->user && $this->getApproved() == false)){
                $status = DBFunctions::update('grand_user_stories',
                                              array('id' => $this->getId(),
                                                    'user_id' => $this->user,
						    'title' => $this->getTitle(),
                                                    'story' => $this->getStory(),
                                                    'date_submitted' => $this->getDateSubmitted(),
                                                    'approved' => 0),
                                              array('rev_id' => EQ($this->rev_id)));
                if($status){
		    DBFunctions::commit();
                    return true;
                }
            }
            return false;
	}

	function delete(){
            $me = Person::newFromWgUser();
            if($me->isRoleAtLeast(ADMIN) || ($me->getId() == $this->user && $this->getApproved() == false)){
                DBFunctions::begin();
                $status = DBFunctions::delete('grand_user_stories',
                                              array('rev_id' => EQ($this->rev_id)));
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
	    $user = Person::newFromId($this->user);
	    $author = array('id'=> $user->getId(),
			    'name' => $user->getNameForForms(),
			    'url' => $user->getUrl());
            $json = array('id' => $this->getId(),
                          'rev_id' => $this->getRevId(),
                          'user' => $this->user,
			  'author' => $author,
			  'title' => $this->getTitle(),
                          'story' => $this->getStory(),
			  'url' => $this->getUrl(),
                          'date_submitted' => $this->getDateSubmitted(),
                          'approved' => $this->getApproved());
            return $json;
        }

        function exists(){
            $story = Story::newFromId($this->getId());
            return ($story != null && $story->getId() != "");
        }

        function getCacheId(){
            global $wgSitename;
        }

	function isOwnedBy($person){
	    return ($this->getUser()->getId() === $person->getId());
	}

	function approve(){
            $me = Person::newFromWgUser();
            if($me->isRoleAtLeast(ADMIN)){
                $status = DBFunctions::update('grand_user_stories',
                                              array('approved' => $true),
                                              array('rev_id' => EQ($this->rev_id)));
                if($status){
                    DBFunctions::commit();
                    return true;
                }
            }
            return false;
	}

    /**
     * Returns the url of this Paper's page
     * @return string The url of this Paper's page
     */
    function getUrl(){
        global $wgServer, $wgScriptPath;
        if(!isset($_GET['embed']) || $_GET['embed'] == 'false'){
            return "{$wgServer}{$wgScriptPath}/index.php/Special:StoryManagePage#/{$this->getId()}";
        }
        return "{$wgServer}{$wgScriptPath}/index.php/Special:StoryManagePage?embed#/{$this->getId()}";
    }

    }
?>
