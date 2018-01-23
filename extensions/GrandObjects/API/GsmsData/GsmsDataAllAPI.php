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
        $folders = explode(",", $this->getParam('folder'));
        $programs = explode(",", $this->getParam('program'));
        $gsms = GsmsData::getAllVisibleGsms();
        $newGsms = array();
        foreach($gsms as $g){
            $found = false;
            foreach($folders as $folder){
                if($g->folder == $folder || $folder == 'all'){
                    foreach($programs as $program){
                        if($program == '' || strstr($g->getProgramName(true), $program) !== false){
                            $newGsms[] = $g;
                            $found = true;
                            break;
                        }
                    }
                    if($found){
                        break;
                    }
                }
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
