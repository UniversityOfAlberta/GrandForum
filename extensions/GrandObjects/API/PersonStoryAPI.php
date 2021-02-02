<?php

class PersonStoryAPI extends RESTAPI {

    function doGET(){
        // Get Authors
        $product = Story::newFromId($this->getParam('id'));
            if(!$product->canView()){
                permissionError();
            }
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
