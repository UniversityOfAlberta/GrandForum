<?php

class StoryAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            $me = Person::newFromWgUser();
            $story = Story::newFromId($this->getParam('id'));
            if(!$story->canView()){
		permissionError();
            }
            return $story->toJSON();
        }
    }

    function doPOST(){
        $story = new Story(array());
        $me = Person::newFromWgUser();
        $story->user = $me->getId();
        $story->title = $this->POST('title');
        $story->story = $this->POST('story');
        $status = $story->create();
        if(!$status){
            $this->throwError("The user <i>{$story->getName()}</i> could not be created");
        }
        $story = Story::newFromId($story->getId());
        return $story->toJSON();
    }

    function doPUT(){
        $story = Story::newFromId($this->getParam('id'));
        if($story == null || $story->getTitle() == ""){
            $this->throwError("This story does not exist");
        }
        elseif(!$story->canEdit()){
            permissionError();
        }
        $story->id = $this->POST('id');
        $story->rev_id = $this->POST('rev_id');
        $story->user = $this->POST('user');
        $story->title = $this->POST('title');
        $story->story = $this->POST('story');
        $story->date_submitted = $this->POST('date_submitted');
        $status = $story->update();
        if(!$status){
            $this->throwError("The story could not be updated");
        }
        $story = Story::newFromId($this->getParam('id'));
        return $story->toJSON();
    }
    function doDELETE(){
        return false;
    }

   /* 
    function doDELETE(){
        $person = Person::newFromId($this->getParam('id'));
        if($person == null || $person->getName() == ""){
            $this->throwError("This user does not exist");
        }
        $status = $person->delete();
        if(!$status){
            $this->throwError("The user <i>{$person->getName()}</i> could not be deleted");
        }
    }*/
}

class StoriesAPI extends RESTAPI {
    
    function doGET(){
        $me = Person::newFromWgUser();
        $stories = new Collection($me->getUserStories());
        return $stories->toJSON();
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

class PersonStoryAPI extends RESTAPI {

    function doGET(){
        // Get Authors
        $product = Story::newFromId($this->getParam('id'));
        $author = $product->getUser();
        if($author->getId()){
            $array = array('productId' => $this->getParam('id'),
                           'id' => $author->getId(),
			   'personUrl' => $author->getUrl(),
			   'authorName' => $author->getNameForForms(),
                           );
                $json = $array;
        }
    return json_encode($json);
    }

    function doPOST(){
/*        global $wgUser;
        if($wgUser->isLoggedIn()){
            $product = Paper::newFromId($this->getParam('id'));
            $person = Person::newFromId($this->getParam('personId'));
            $serializedAuthors = $product->authors;
            $authors = $product->getAuthors();
            $found = false;
            foreach($authors as $author){
                if($author->getId() == $person->getId()){
                    $found = true;
                }
            }
            if(!$found){
                $authors = unserialize($serializedAuthors);
                $authors[] = $person->getId();
                DBFunctions::update('grand_products',
                                    array('authors' => serialize($authors)),
                                    array('id' => $product->getId()));
                Paper::$cache = array();
                Paper::$dataCache = array();
            }
        }
        else{
            $this->throwError("Author was not added");
        }*/
        return $this->doGET();
    }

    function doPUT(){
        return $this->doGET();
    }

    function doDELETE(){
        /*global $wgUser;
        if($wgUser->isLoggedIn()){
            $product = Paper::newFromId($this->getParam('id'));
            $person = Person::newFromId($this->getParam('personId'));
            $serializedAuthors = $product->authors;
            $authors = $product->getAuthors();
            foreach($authors as $key => $author){
                if($author->getId() == $person->getId()){
                    $serializedAuthors = unserialize($serializedAuthors);
                    unset($serializedAuthors[$key]);
                    DBFunctions::update('grand_products',
                                        array('authors' => serialize($serializedAuthors)),
                                        array('id' => $product->getId()));
                    return;
                }
            }
        }
        else{
            $this->throwError("Author was not deleted");
        }*/
    }
}

?>
