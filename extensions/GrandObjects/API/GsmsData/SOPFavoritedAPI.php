<?php

/**
 * Class InfoSheetAPI
 * API Class for interacting with individual GSMS infosheets
 */
class SOPFavoritedAPI extends RESTAPI {

  /**
   * doGET handler for get request method
   * @return mixed
   */
    function doGET(){
        return true;
    }

  /**
   * doPOST handler for post request method
   * @return bool
   */
    function doPOST(){
        return $this->doPUT();
    }

  /**
   * doPUT handler for put request method
   * @return bool
   */
    function doPUT(){
        // This is only for updating favorited status (right now)
        if($this->getParam('id') != ""){
            $me = Person::newFromWgUser();
            $year = $this->getParam('year');
            $info_sheet = GsmsData::newFromUserId($this->getParam('id'), $year);
            if(!$me->isLoggedIn()){
                $this->throwError("You must be logged in to view this GSMS data.");
            }
            $info_sheet->getSOP()->setFavoritedStatus($me, ($this->POST('favorited') == "true"));
            return $info_sheet->toJSON();
        }
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
