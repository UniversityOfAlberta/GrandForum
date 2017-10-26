<?php

/**
 * Class SopsAPI
 * API class for interacting wiht SOPs collection
 */
class GsmsDataAllAPI extends RESTAPI {

  /**
   * doGET handler for get request method
   * @return mixed
   */
    function doGET(){
	$gsms = new Collection(GsmsData::getAllVisibleGsms());
        return $gsms->toJSON();
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
