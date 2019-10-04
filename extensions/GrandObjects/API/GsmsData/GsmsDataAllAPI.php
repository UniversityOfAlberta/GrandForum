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
                if($g->getAdditional('folder') == $folder || 
                   ($folder == "Admit" && (strstr($g->getAdditional('folder'), "Evaluator") !== false || // Need to handle some extra folders from FGSR (gross!)
                                           strstr($g->getAdditional('folder'), "Coder") !== false ||
                                           strstr($g->getAdditional('folder'), "Offer Accepted") !== false ||
                                           strstr($g->getAdditional('folder'), "Waiting for Response") !== false ||
                                           strstr($g->getAdditional('folder'), "Incoming") !== false)) || 
                   ($folder == "Rejected Apps" && (strstr($g->getAdditional('folder'), "Ready for Decision") !== false)) || 
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
