<?php

class PersonPostAPI extends RESTAPI {

    function doGET(){
        return false;

    }

    function doPOST(){
        return false;
    }

    function doPUT(){
        return $this->doGET();
    }

    function doDELETE(){
        return false;
    }

}

?>
