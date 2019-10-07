<?php

/**
 * @package GrandObjects
 */
class Sop extends BackboneModel{
    
    static $cache = array();
    static $idsCache = array();
    
    var $id;
    var $user_id;
    var $date_created;
    var $year;

    /* other */
    var $annotations = array(); 
    var $pdf;    


  /**
   * getContent Gets the SOP Content
   * @return array with answers to questions
   */

  /**
   * newFromId Returns an SOP object from a given id
   * @param $id
   * @return $sop SOP object
   */
    static function newFromId($id, $year=""){
        $dbyear = ($year != "" && $year != YEAR) ? "_$year" : "";
        $data = DBFunctions::select(array("grand_sop$dbyear"),
                                    array('id',
                                          'user_id',
                                          'date_created'),
                                    array('id' => EQ($id)));
        if(count($data)>0){
            $sop = new SOP($data);
            $sop->year = $year;
            return $sop;
        }
        return new SOP(array());
    }

  /**
   * newFromId Returns an SOP object from a given id
   * @param $id
   * @return $sop SOP object
   */
    static function newFromUserId($id, $year=""){
        $dbyear = ($year != "" && $year != YEAR) ? "_$year" : "";
        $data = DBFunctions::select(array("grand_sop$dbyear"),
                                    array('id',
                                          'user_id',
                                          'date_created'),
                                    array('user_id' => EQ($id)));
        if(count($data)>0){
            $sop = new SOP($data);
            $sop->year = $year;
            return $sop;
        }
        return new SOP(array());
    }

  /**
   * SOP constructor.
   * @param $data
   */
    function SOP($data){
        if(count($data) > 0){
            $row = $data[0];
            $this->id = $row['id'];
            $this->user_id = $row['user_id'];
            $this->date_created = $row['date_created'];
        }
        $this->annotations = SOP_Annotation::getAllSOPAnnotations($this->id);
    }
    
    static function generateCache(){
        if(empty(self::$idsCache)){
            $data = DBFunctions::select(array('grand_sop'),
                                        array('*'),
                                        array());
            foreach($data as $row){
                unset($row['pdf_contents']);
                self::$idsCache[$row['id']] = $row;
            }
        }
    }

    function getBlobValue($blobType, $year, $reportType, $reportSection, $blobItem, $userId=null, $projectId=0){
        if($userId == null){
            $userId = $this->user_id;
        }
        $blb = new ReportBlob($blobType, $year, $userId, $projectId);
        $addr = ReportBlob::create_address($reportType, $reportSection, $blobItem, 0);
        $result = $blb->load($addr);
        $data = $blb->getData();
        return $data;
    }

    /**
     * getAllSOP Returns all SOP available to a user
     * @return sop An Array of SOP
     */
    static function getAllSOP(){
        global $wgRoleValues;
        $sops = array();
        $me = Person::newFromWgUser();
        $data = DBFunctions::select(array('grand_sop'),
                                        array('id'));
        if(count($data) >0){
            foreach($data as $sopId){
                $sop = SOP::newFromId($sopId['id']);
                $person = Person::newFromId($sop->getUser());
                if($person != null && $person->getName() != ""){
                    $sops[] = $sop;
                }
            }
        }
        return $sops;
    }

    function create(){
        $status = DBFunctions::insert('grand_sop',
             array('user_id' => $this->user_id),
             true);
    }

    function update(){
        return false;
    }

    function delete(){
        return false;
    }

  /**
   * toArray Converts SOP object to array
   * @return array
   */
    function toArray(){
        global $wgUser, $config;
        /* 
        if(!$wgUser->isLoggedIn()){
            return array();
        }
        */
        
        $user = Person::newFromId($this->getUser());
        $author = array('id' => $user->getId(),
                        'name' => $user->getReversedName(),
                        'url' => $user->getUrl());
        $gsms = $user->getGSMS($this->year)->getAdditional();

        $student = Person::newFromId($this->user_id);

        $json = array('id' => $this->getId(),
                      'user_id' => $this->getUser(),
                      'date_created' => $this->getDateCreated(),
                      'author' => $author,
                      'gsms' => $gsms,
                      'annotations' => $this->annotations);

        // Get from Config which forum we are looking at to add extra columns
        return $json;
    }

  /**
   *
   */
    function exists(){
        //TODO
    }

  /**
   *
   */
    function getCacheId(){
        //TODO
    }

  /**
   *
   */
    function getUsers(){
        //TODO
    }

  /**
   * getId Gets the SOP id
   * @return mixed
   */
    function getId(){
        return $this->id;
    }

  /**
   * getId Gets the SOP id
   * @return mixed
   */
    function getUserId(){
        return $this->user_id;
    }

   /**
    * returns string if SOP was suggested to be admitted or not by the user specified in argument.
    * @return $string either 'Admit', 'Not Admit' or 'Undecided' based on answer of PDF report.
    */
    function getAnnotations(){
  $annotations = array();
  foreach($this->annotations as $annotation){

  }
  return $annotations;
    }

  /**
   * getUser Gets the the SOP User id
   * @return mixed
   */
    function getUser(){
      return $this->user_id;
    }

  /**
   * getDateCreated Gets the SOP date created
   * @return mixed
   */
    function getDateCreated(){
      return $this->date_created;
    }

}


?>
