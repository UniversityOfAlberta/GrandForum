<?php

/**
 * Class SopAnnotationAPI
 * API class for interacting with individual SOP annotations
 */
class SopAnnotationAPI extends RESTAPI {

  /**
   * doGET handler for get request method
   * @return mixed|string
   */
    function doGET(){
        if($this->getParam('annotation_id') != ""){
            $me = Person::newFromWgUser();
            $sop = SOP_Annotation::newFromId($this->getParam('annotation_id'));
            if(!$me->isLoggedIn()){
                $this->throwError("You must be logged in to view this sop");
            }
            return json_encode($sop);
        }
        else if($this->getParam('sop_id') != ""){
            $me = Person::newFromWgUser();
            $sops = new Collection(SOP_Annotation::getAllSOPAnnotations($this->getParam('sop_id')));
            if(!$me->isLoggedIn()){
                $this->throwError("You must be logged in to view this sop");
            }
            return $sops->toJSON();
        } else {
            $sops = new Collection(SOP_Annotation::getAllSOPAnnotations());
            return $sops->toJSON();
        }
    }

  /**
   * doPOST handler for post request method
   * @return bool|string
   */
    function doPOST(){
        $me = Person::newFromWgUser();
        $data = json_decode(file_get_contents('php://input'), true);

        $annotation = new SOP_Annotation();
        $annotation->setUserId($me->getId());
        $annotation->setSopId($this->getParam('sop_id'));

        $annotation->setContent($data['content']);
        $annotation->setText($data['text']);
        $annotation->setRanges($data['ranges']);
        $annotation->setTags($data['tags']);
        $annotation->setQuote($data['quote']);

        $status = $annotation->create();
        if($status === false){
            $this->throwError("The annotation could not be created");
            return false;
        }
                header("HTTP/1.0: 204 NO CONTENT");
                return "";
        return json_encode($status);
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
   * @return bool|string
   */
    function doDELETE(){
        if($this->getParam('annotation_id') != ""){
            $me = Person::newFromWgUser();
            if(!$me->isLoggedIn()){
                $this->throwError("You must be logged in to view this sop");
            }
            $sop = SOP_Annotation::newFromId($this->getParam('annotation_id'));
            if($sop == null || $sop->getQuote() == ""){
                $this->throwError("This annotation does not exist");
            }
            $status = $sop->delete();
            if($status){
                header("HTTP/1.0: 204 NO CONTENT");
                return "";
            }
        }
        return false;
    }

}

?>
