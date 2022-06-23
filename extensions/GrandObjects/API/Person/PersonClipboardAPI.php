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
        return json_encode($clipboard);
    }
    
    function doPOST(){
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            $this->throwError("You must be logged in.");
        }
        $arr = $this->POST("objs");
        $status = $me->saveClipboard($arr);
        if($status){
            return $this->doGET();
        }
        else{
            $this->throwError("Error saving.");
        }
    }
    
    function doPUT(){
        return $this->doPOST();
    }
    
    function doDELETE(){
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            $this->throwError("You must be logged in.");
        }
        $status = $me->saveClipboard(array());
        if($status){
            return $this->doGET();
        }
        else{
            $this->throwError("Error saving.");
        }
    }
}

?>
