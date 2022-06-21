<?php

class PersonClipboardAPI extends RESTAPI {

  /**
   * doGET handler for get request method
   * @return mixed
   */
    function doGET(){
        $me = Person::newFromWgUser();
        header('Content-Type: application/json');
        $clipboard = $me->getClipboard();
        if(!$me->isLoggedIn()){
            $this->throwError("You must be logged in.");
        }
        $clips = new Collection($clipboard);
        return $clips->toJSON();
    }
    
    function doPOST(){
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            $this->throwError("You must be logged in.");
        }
        $arr = $this->POST("clipboard");
        $status = $me->saveClipboard($arr);
        if($status){
            $clipboard = $me->getClipboard();
            $clips = new Collection($clipboard);
            return $clips->toJSON();
        }
        else{
            $this->throwError("Error saving.");
        }
    }
    
    function doPUT(){
        return doPOST();
    }
    
    function doDELETE(){
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            $this->throwError("You must be logged in.");
        }
        $status = $me->saveClipboard(array());
        if($status){
                $clipboard = $me->getClipboard();
                $clips = new Collection($clipboard);
                return $clips->toJSON();
        }
        else{
            $this->throwError("Error saving.");
        }
    }
}

?>
