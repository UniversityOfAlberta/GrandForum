<?php

/**
 * Class InfoSheetAPI
 * API Class for interacting with individual GSMS infosheets
 */
class GsmsDataAPI extends RESTAPI {

  /**
   * doGET handler for get request method
   * @return mixed
   */
    function doGET(){
        if($this->getParam('id') != ""){
            $me = Person::newFromWgUser();
            $info_sheet = GsmsData::newFromUserId($this->getParam('id'));
            if(!$me->isLoggedIn()){
                $this->throwError("You must be logged in to view this GSMS data.");
            }
	    elseif($info_sheet->user_id == ""){
		$info_sheet = new GsmsData(array());
	    }
            return $info_sheet->toJSON();
        }
    }

  /**
   * doPOST handler for post request method
   * @return bool
   */
    function doPOST(){
	return false;
	//todo
    }

  /**
   * doPUT handler for put request method
   * @return bool
   */
    function doPUT(){
      if($this->getParam('id') != ""){
        $me = Person::newFromWgUser();
        $info_sheet = GsmsData::newFromUserId($this->getParam('id'));
        if($me->isRoleAtLeast(MANAGER)) {
            $info_sheet->additional = (array) $this->POST('additional');
            $info_sheet->update();
        }
      }
	    return $this->doGET();
        //todo
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
