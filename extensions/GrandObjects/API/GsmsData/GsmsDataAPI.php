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
            $info_sheet = InfoSheet::newFromUserId($this->getParam('id'));
            if(!$me->isLoggedIn()){
                $this->throwError("You must be logged in to view this sop");
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
        $info_sheet = InfoSheet::newFromUserId($this->getParam('user_id'));
        header('Content-Type: application/json');        
        $info_sheet->gpa60 = $this->POST('gpa60');
        $info_sheet->gpafull = $this->POST('gpafull');
        $info_sheet->gpafull_credits = $this->POST('gpafull_credits');
        $info_sheet->gpafull2 = $this->POST('gpafull2');
        $info_sheet->notes = $this->POST('notes');
        $info_sheet->anatomy = $this->POST('anatomy');
        $info_sheet->stats = $this->POST('stats');
        $info_sheet->institution = $this->POST('institution');
        $info_sheet->failures = $this->POST('failures');
        $info_sheet->withdrawals = $this->POST('withdrawals');
        $info_sheet->canadian = $this->POST('canadian');
        $info_sheet->international = $this->POST('international');
        $info_sheet->indigenous = $this->POST('indigenous');
        $info_sheet->saskatchewan = $this->POST('saskatchewan');
        $info_sheet->degrees = $this->POST('degrees');

        $status = $info_sheet->update();
        if(!$status){
            $this->throwError("The info_sheet could not be updated");
        }
        $info_sheet = GsmsData::newFromUserId($this->getParam('user_id'));
        return true;
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
