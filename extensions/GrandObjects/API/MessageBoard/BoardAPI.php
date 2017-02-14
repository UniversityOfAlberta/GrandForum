<?php

class BoardAPI extends RESTAPI {

    function doGET(){
        if($this->getParam('id') != ""){
            $board = Board::newFromId($this->getParam('id'));
            if(!$board->canView()){
                $this->throwError("You must be logged in to view this board");
            }
            return $board->toJSON();
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
