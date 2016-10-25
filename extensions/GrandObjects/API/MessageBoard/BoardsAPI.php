<?php

class BoardsAPI extends RESTAPI {

    function doGET(){
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            $boards = new Collection(Board::getAllBoards());
            return $boards->toJSON();
        }
        else{
            $this->throwError("You must be logged in to view the boards");
        }
    }

    function doPOST(){
        return $this->doGET();
    }

    function doPUT(){
        return $this->doGET();
    }

    function doDELETE(){
        return false;
    }
}

?>
