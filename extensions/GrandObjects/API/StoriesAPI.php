<?php

class StoriesAPI extends RESTAPI {
    
    function doGET(){
        $me = Person::newFromWgUser();
        $stories = new Collection(Story::getAllUserStories());
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

?>
