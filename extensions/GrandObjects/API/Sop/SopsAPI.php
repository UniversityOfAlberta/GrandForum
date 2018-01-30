<?php

/**
 * Class SopsAPI
 * API class for interacting wiht SOPs collection
 */
class SopsAPI extends RESTAPI {

  /**
   * doGET handler for get request method
   * @return mixed
   */
    function doGET(){
	$sops = new Collection(SOP::getAllReviewSOP());
        return $sops->toJSON();
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
