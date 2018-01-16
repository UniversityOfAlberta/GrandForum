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
        $folder = $this->getParam('folder');
        $gsms = GsmsData::getAllVisibleGsms();
        $newGsms = array();
        foreach($gsms as $g){
            if($g->folder == $folder || $folder == 'all'){
                $newGsms[] = $g;            
            }
        }
        $gsms = new Collection($newGsms);
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
