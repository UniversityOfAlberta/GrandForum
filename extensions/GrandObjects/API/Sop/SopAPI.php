<?php

/**
 * Class SopAPI
 * API Class for interacting with individual SOPs
 */
class SopAPI extends RESTAPI {

  /**
   * doGET handler for get request method
   * @return mixed
   */
    function doGET(){
        if($this->getParam('id') != ""){
            $me = Person::newFromWgUser();
            $sop = SOP::newFromId($this->getParam('id'));
            if(!$me->isLoggedIn()){
                $this->throwError("You must be logged in to view this sop");
            }
            return $sop->toJSON();
        }
    }

  /**
   * doPOST handler for post request method
   * @return bool
   */
    function doPOST(){
        return false;
    }

  /**
   * doPUT handler for put request method
   * @return bool
   */
    function doPUT(){
        return false;
    }

  /**
   * doDELETE handler for delete request method
   * @return bool
   */
    function doDELETE(){
        return false;
    }
}

?>
