<?php

/**
 * @package GrandObjects
 */
abstract class AbstractSop extends BackboneModel{
    
    static $cache = array();
    static $idsCache = array();
    
    var $id;
    var $user_id;
    var $date_created;
    var $visible = false;
    var $year;

    /* other */
    var $annotations = array(); 
    var $pdf;    


  /**
   * getContent Gets the SOP Content
   * @return array with answers to questions
   */
    abstract function checkGSMS();
    abstract function getSopPdf();
    abstract function getGSMSUrl();
    abstract function getSopUrl();
    abstract function checkSop(); 
    abstract function getReviewers();
    abstract function getAdmitResult($user);
    abstract function getColumns();
    
    function getReviewRanking() {
        return '--';
    }
    
    function getHiddenStatus(){
        return false;
    }

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
    function AbstractSOP($data){
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
        $nationality = array();
        $nationality[] = @($gsms['indigenous'] == "Yes") ? "Indigenous" : "";
        $nationality[] = @($gsms['canadian'] == "Yes") ? "Canadian" : "";
        $nationality[] = @($gsms['saskatchewan'] == "Yes") ? "Saskatchewan" : "";
        $nationality[] = @($gsms['international'] == "Yes") ? "International" : "";

        $nationality_note = "";
        foreach($nationality as $note){
            if($note != ""){
                $nationality_note .= $note.',<br />';
            }
        }
        $reviewers = array();
        $student = Person::newFromId($this->user_id);
        $reviewer_array = $student->getEvaluators(YEAR,"sop"); 
        foreach($reviewer_array as $reviewer){
            $person = $reviewer;
            $reviewers[] = array('id' => $person->getId(),
                                 'name' => $person->getNameForForms(),
                                 'url' => $person->getUrl(),
                                 'decision' => $this->getAdmitResult($reviewer->getId()));
        }

        $json = array('id' => $this->getId(),
                      'user_id' => $this->getUser(),
                      'date_created' => $this->getDateCreated(),
                      'url' => $this->getUrl(),
                      'author' => $author,
                      'gsms' => $gsms,
                      'admit' => $this->getFinalAdmit(),
                      'nationality_note' => $nationality_note,
                      'reviewers' => $reviewers,
                      'annotations' => $this->annotations,
                      'gsms_data' => $this->checkGSMS(),
                      'sop_check' => $this->checkSOP(),
                      'sop_url' => $this->getSopUrl(),
                      'gsms_url' => $this->getGSMSUrl());

        // Get from Config which forum we are looking at to add extra columns
        $json = array_merge($json, $this->getColumns());
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
    * returns content of SOP only with answers of user to send for analysis, also filtered to be able to
    * send as JSON object.
    * @return $string string version of all answers to be sent as string through http response.
    */
    function getContentToSend(){
        $content = $this->getContent();
        $string = "";
        foreach($content as $question => $answer){
            $string = $string.$answer;
        }
        $string = utf8_encode(htmlspecialchars_decode($string, ENT_QUOTES));
        $string = preg_replace('/[^A-Za-z.,\']/', ' ',$string);
        return $string;
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
    * returns string if SOP was suggested to be admitted or not by the user specified in argument.
    * @return $string either 'Admit', 'Not Admit' or 'Undecided' based on answer of PDF report.
    */
    function getFinalAdmit(){
        $hqp = Person::newFromId($this->getUser());
        $gsms = $hqp->getGSMS($this->year);
        $dec = $gsms->getAdditional('folder');
        if(strstr($dec, "Evaluator") !== false || // Need to handle some extra folders from FGSR (gross!)
           strstr($dec, "Coder") !== false ||
           strstr($dec, "Offer Accepted") !== false ||
           strstr($dec, "Waiting for Response") !== false ||
           strstr($dec, "Incoming") !== false){
            $dec = "Admit";   
        }
        if(strstr($dec, "Ready for Decision") !== false){ // Need to handle some extra folders from FGSR (gross!)
            $dec = "Reject";   
        }
        if ((strtolower($dec) == "admit") || (strtolower($dec) == "reject") || (strtolower($dec) == "waitlist")) {
            return $dec;
        } else {
            return "Undecided";
        }
    }
    
    /**
    * returns string if SOP was suggested to be admitted or not by the user specified in argument.
    * @return $string either 'Admit', 'Not Admit' or 'Undecided' based on answer of PDF report.
    */
    function getFinalComments(){
        $year = ($this->year != "") ? $this->year : YEAR;
        $hqp = Person::newFromId($this->getUser());
        $gsms = $hqp->getGSMS($year);
        $blob = new ReportBlob(BLOB_TEXT, $year, 0, $gsms->getId());
        $blob_address = ReportBlob::create_address('RP_COM', 'OT_COM', 'Q2', $gsms->getId());
        $blob->load($blob_address);
        $data = $blob->getData();
        return $data;
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

    /**
     * getUrl Returns the url of this Paper's page
     * @return string The url of this Paper's page
     */
    function getUrl(){
        global $wgServer, $wgScriptPath;
        if(!isset($_GET['embed']) || $_GET['embed'] == 'false'){
            return "{$wgServer}{$wgScriptPath}/index.php/Special:Sops#/{$this->getId()}/edit";
        }
        return "{$wgServer}{$wgScriptPath}/index.php/Special:Sops?embed#/{$this->getId()}/edit";
    }

}
require_once("SopModels/".$config->getValue("networkName")."/SOP.php");
?>
