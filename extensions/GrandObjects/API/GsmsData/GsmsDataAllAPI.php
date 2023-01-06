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
        $decision = $this->getParam('decision');
        $year = $this->getParam('year');
        $gsms = GsmsData::getAllVisibleGsms($year);
        $newGsms = array();
        foreach($gsms as $g){
            $found = false;
            foreach($folders as $folder){
                if($g->folder == $folder || 
                   ($folder == "New Applications" && (strstr($g->folder, "New Application") !== false)) ||
                   ($folder == "Admit" && (strstr($g->folder, "Evaluator") !== false || // Need to handle some extra folders from FGSR (gross!)
                                           strstr($g->folder, "Coder") !== false ||
                                           strstr($g->folder, "Offer Accepted") !== false ||
                                           strstr($g->folder, "Waiting for Response") !== false ||
                                           strstr($g->folder, "Ready for Decision") !== false ||
                                           strstr($g->folder, "Incoming") !== false)) || 
                   ($folder == "Rejected Apps" && (strstr($g->folder, "Reject") !== false)) || 
                   ($folder == "Review in Progress" && (strstr($g->folder, "Review Complete") !== false)) || 
                   $folder == 'all'){
                    foreach($programs as $program){
                        if($program == '' || $program == 'all' || strstr($g->getProgramName(true), $program) !== false){
                            if($decision == 'all' || $g->getSOP()->getFinalAdmit() == $decision){
                                $newGsms[] = $g;
                                $found = true;
                                break;
                            }
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
