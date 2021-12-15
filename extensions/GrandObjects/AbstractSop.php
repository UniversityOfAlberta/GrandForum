<?php

/**
 * @package GrandObjects
 */
abstract class AbstractSop extends BackboneModel{
    
    static $cache = array();
    static $idsCache = array();
    
    var $id;
    var $content;
    var $questions;
    var $user_id;
    var $date_created;
    var $visible = false;
    var $year;
    
    /* watson values */
    var $sentiment_val;
    var $sentiment_type;
    var $anger_score;
    var $disgust_score;
    var $fear_score;
    var $joy_score;
    var $sadness_score;
    var $personality_stats = array();

    /* readability scores */
    var $readability_score;
    var $reading_ease;
    var $ari_grade;
    var $ari_age;
    var $colemanliau_grade;
    var $colemanliau_age;
    var $dalechall_index;
    var $dalechall_grade;
    var $dalechall_age;
    var $fleschkincaid_grade;
    var $fleschkincaid_age;
    var $smog_grade;
    var $smog_age;
    var $errors;
    var $sentlen_ave;
    var $wordletter_ave;
    var $min_age;
    var $word_count;

    /* other */
    var $annotations = array(); 
    var $pdf;    


  /**
   * getContent Gets the SOP Content
   * @return array with answers to questions
   */
    abstract function getContent($asString=false);
    abstract function checkGSMS();
    abstract function getSopPdf();
    abstract function getGSMSUrl();
    abstract function getSopUrl();
    abstract function checkSop(); 
    abstract function getReviewers();
    abstract function getAdmitResult($user);
    abstract function getWantToSupervise($user);
    abstract function getWillingToSupervise($user);
    abstract function getColumns();
    
    function getReviewRanking() {
        return '--';
    }
    
    function getHiddenStatus(){
        return false;
    }
    
    function getFavoritedStatus(){
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
                                          'content',
                                          'date_created',
                                          'sentiment_val',
                                          'sentiment_type',
                                          'readability_score',
                                          'reading_ease',
                                          'ari_grade',
                                          'ari_age',
                                          'colemanliau_grade',
                                          'colemanliau_age',
                                          'dalechall_index',
                                          'dalechall_grade',
                                          'dalechall_age',
                                          'fleschkincaid_grade',
                                          'fleschkincaid_age',
                                          'smog_grade',
                                          'smog_age',
                                          'errors',
                                          'sentlen_ave',
                                          'wordletter_ave',
                                          'min_age',
                                          'word_count',
                                          'emotion_stats',
                                          'personality_stats',
                                          'pdf_data',
                                          'reviewer'),
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
                                          'content',
                                          'date_created',
                                          'sentiment_val',
                                          'sentiment_type',
                                          'readability_score',
                                          'reading_ease',
                                          'ari_grade',
                                          'ari_age',
                                          'colemanliau_grade',
                                          'colemanliau_age',
                                          'dalechall_index',
                                          'dalechall_grade',
                                          'dalechall_age',
                                          'fleschkincaid_grade',
                                          'fleschkincaid_age',
                                          'smog_grade',
                                          'smog_age',
                                          'errors',
                                          'sentlen_ave',
                                          'wordletter_ave',
                                          'min_age',
                                          'word_count',
                                          'emotion_stats',
                                          'personality_stats',
                                          'pdf_data',
                                          'reviewer'),
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
            $this->content = $row['content'];
            $this->user_id = $row['user_id'];
            $this->date_created = $row['date_created'];

            $this->sentiment_val = $row['sentiment_val'];
            $this->sentiment_type = $row['sentiment_type'];
            $this->personality_stats = unserialize($row['personality_stats']);
            $emotions_array = unserialize($row['emotion_stats']);
            $this->anger_score = $emotions_array['anger'];
            $this->disgust_score = $emotions_array['disgust'];
            $this->fear_score = $emotions_array['fear'];
            $this->joy_score = $emotions_array['joy'];
            $this->sadness_score = $emotions_array['sadness'];

            $this->readability_score = $row['readability_score'];
            $this->reading_ease = $row["reading_ease"];
            $this->ari_grade = $row["ari_grade"];
            $this->ari_age = $row["ari_age"];
            $this->colemanliau_grade = $row["colemanliau_grade"];
            $this->colemanliau_age = $row["colemanliau_age"];
            $this->dalechall_index = $row["dalechall_index"];
            $this->dalechall_grade = $row["dalechall_grade"];
            $this->dalechall_age = $row["dalechall_age"];
            $this->fleschkincaid_grade = $row["fleschkincaid_grade"];
            $this->fleschkincaid_age = $row["fleschkincaid_age"];
            $this->smog_grade = $row["smog_grade"];
            $this->smog_age = $row["smog_age"];
            $this->errors = $row['errors'];
            $this->sentlen_ave = $row['sentlen_ave'];
            $this->wordletter_ave = $row['wordletter_ave'];
            $this->min_age = $row['min_age'];
            $this->word_count = $row['word_count'];

            $this->pdf = $row['pdf_data'];
            $this->visible = $row['reviewer'];
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

    function getOTColumns() {

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

    /**
     * getAllSOP Returns all SOP available to a user
     * @return sop An Array of SOP
     */
    static function getAllReviewSOP(){
        global $wgRoleValues;
        $sops = array();
        $me = Person::newFromWgUser();
        //if($me->isRoleAtLeast(MANAGER)){
        $data = DBFunctions::select(array('grand_sop'),
                                    array('id'),
                                    array('reviewer' => "true"));
        //}
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
        $personality = $this->getPersonalityStats();
        $openness = 0;
        $conscientiousness = 0;
        $extraversion = 0;
        $agreeableness = 0;
        $neurotism = 0;

        if(isset($personality['personality'])){
             $openness = $personality['personality'][0]['percentile'];
             $conscientiousness = $personality['personality'][1]['percentile'];
             $extraversion = $personality['personality'][2]['percentile'];
             $agreeableness = $personality['personality'][3]['percentile'];
             $neurotism = $personality['personality'][4]['percentile'];
        }
        $json = array('id' => $this->getId(),
                      'content' => $this->getContent(),
                      'content_string' => $this->getContent(true),
                      'user_id' => $this->getUser(),
                      'date_created' => $this->getDateCreated(),
                      'url' => $this->getUrl(),
                      'author' => $author,
                      'gsms' => $gsms,
                      'admit' => $this->getFinalAdmit(),
                      'nationality_note' => $nationality_note,
                      'reviewers' => $reviewers,
                      'sentiment_val' => round($this->sentiment_val,2),
                      'sentiment_type' => $this->sentiment_type,
                      'openness' => $openness,
                      'conscientiousness' => $conscientiousness,
                      'extraversion' => $extraversion,
                      'agreeableness' => $agreeableness,
                      'neurotism' => $neurotism,
                      'readability_score' => number_format($this->readability_score,2,'.',','),
                      'reading_ease' => number_format($this->reading_ease,2,'.',','),
                      'ari_grade' => number_format($this->ari_grade,2,'.',','),
                      'ari_age' => number_format($this->ari_age,2,'.',','),
                      'colemanliau_grade' => number_format($this->colemanliau_grade,2,'.',','),
                      'colemanliau_age' => number_format($this->colemanliau_age,2,'.',','),
                      'dalechall_index' => number_format($this->dalechall_index,2,'.',','),
                      'dalechall_grade' => number_format($this->dalechall_grade,2,'.',','),
                      'dalechall_age' => number_format($this->dalechall_age,2,'.',','),
                      'fleschkincaid_grade' => number_format($this->fleschkincaid_grade,2,'.',','),
                      'fleschkincaid_age' => number_format($this->fleschkincaid_age,2,'.',','),
                      'smog_grade' => number_format($this->smog_grade,2,'.',','),
                      'smog_age' => number_format($this->smog_age,2,'.',','),
                      'anger_score' => number_format($this->anger_score,2,'.',','),
                      'disgust_score' => number_format($this->disgust_score,2,'.',','),
                      'fear_score' => number_format($this->fear_score,2,'.',','),
                      'joy_score' => number_format($this->joy_score,2,'.',','),
                      'errors' => $this->errors,
                      'sentlen_ave' => number_format($this->sentlen_ave,2,'.',','),
                      'wordletter_ave' => number_format($this->wordletter_ave,2,'.',','),
                      'min_age' => number_format($this->min_age,2,'.',','),
                      'word_count' => $this->word_count,
                      'annotations' => $this->annotations,
                      'pdf_data' => $this->getPdf(true),
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
     * Returns information taken from PDF uploaded as an array
     * @param bool $asHtml if response should replace new lines with <br />
     * @return $pdf the array of information previously parsed from PDF upload
     */
    function getPdf($asHtml=false){
      $pdf = unserialize($this->pdf);
      if($asHtml && isset($pdf['Referees'])){
                $refs = $pdf['Referees'];
                $i = 0;
                if(is_array($refs)){
                    foreach($refs as $ref){
                        $pdf['Referees'][$i]['responses'] = @nl2br($ref['responses']);
                        $i++;
                    }
                }
      }
      return $pdf;
    }

   /**
    * returns array that is returned from Watson personality analysis
    * @return array
    */
    function getPersonalityStats(){
      return $this->personality_stats;
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
        $dec = $gsms->folder;
        if(strstr($dec, "Evaluator") !== false || // Need to handle some extra folders from FGSR (gross!)
           strstr($dec, "Coder") !== false ||
           strstr($dec, "Offer Accepted") !== false ||
           strstr($dec, "Waiting for Response") !== false ||
           strstr($dec, "Ready for Decision") !== false ||
           strstr($dec, "Incoming") !== false){
            $dec = "Admit";   
        }
        if(strstr($dec, "Rejected Apps")){
            $dec = "Reject";
        }
        //if(strstr($dec, "Ready for Decision") !== false){ // Need to handle some extra folders from FGSR (gross!)
        //    $dec = "Reject";   
        //}
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


  /**
   * getReadabilityScore Updates and returns readablility score for the SOP
   * @return mixed|string
   */
    function getReadabilityScore(){
      $content = $this->getContentToSend();
      $tasha = new TASHA();
      $tasha->content = $content;
      $result = $tasha->getReadabilityScore();
      $sql = "UPDATE grand_sop
              SET readability_score={$result['readability_score']}, min_age={$result['min_age']}, 
                        reading_ease={$result['reading_ease']}, ari_grade={$result['ari_grade']},
                        ari_age={$result['ari_age']}, colemanliau_grade={$result['colemanliau_grade']},
                        colemanliau_age={$result['colemanliau_age']}, dalechall_index={$result['dalechall_index']},
                        dalechall_grade={$result['dalechall_grade']}, dalechall_age={$result['dalechall_age']},
                        fleschkincaid_grade={$result['fleschkincaid_grade']},
                        fleschkincaid_age={$result['fleschkincaid_grade']},
                        smog_grade={$result['smog_grade']}, smog_age={$result['smog_age']},
                        word_count={$result['word_count']}, sentlen_ave={$result['sentlen_ave']},
                        wordletter_ave={$result['wordletter_ave']}
              WHERE id={$this->id};";
        $status = false;
        if($status){
            DBFunctions::commit();
        }

      return $result;
    }

  /**
   * getSentimentScore Gets the SOP sentiment score
   * @return mixed|string
   */
    function getSentimentScore(){
        $content = $this->getContentToSend();
        $tasha = new TASHA();
        $tasha->content = $content;
        $result = $tasha->getSentimentScore();
        $sql = "UPDATE grand_sop
                SET sentiment_type='{$result['sentiment_type']}', sentiment_val='{$result['sentiment_val']}'
                WHERE id={$this->id};";

        $status = false;
        if($status){
            DBFunctions::commit();
        }

        return $result;
    }

  /**
   * getEmotionsScore Gets the SOP Emotion Score
   * @return mixed|string
   */
    function getEmotionsScore(){
        $content = $this->getContentToSend();
        $tasha = new TASHA();
        $tasha->content = $content;
        $result = $tasha->getEmotionsScore();
        $sql = "UPDATE grand_sop
                SET emotion_stats='".serialize($result)."'
                WHERE id={$this->id};";

        $status = false;

        if($status){
            DBFunctions::commit();
        }

        return $result;
    }

  /**
   * getEmotionsScore Gets the SOP Emotion Score
   * @return mixed|string
   */
    function getPersonalityScore(){
        $content = $this->getContentToSend();
        $tasha = new TASHA();
        $tasha->content = $content;
        $result = $tasha->getPersonalityScore();
        $sql = "UPDATE grand_sop
                SET personality_stats='".serialize($result)."'
                WHERE id={$this->id};";

        $status = false;

        if($status){
            DBFunctions::commit();
        }

        return $result;
    }


  /**
   * getSyntaxErrorCount Gets the SOP syntax error count
   * @return mixed|string
   */
    function getSyntaxErrorCount(){
        $content = $this->getContentToSend();
        $tasha = new TASHA();
        $tasha->content = $content;
        $result = $tasha->getSyntaxErrorCount();
        $sql = "UPDATE grand_sop
                SET errors=$result
                WHERE id={$this->id};";
        $status = false;

        if($status){
            DBFunctions::commit();
        }

    }

  /**
   * updateStatistics Updates all the SOP statistics
   * @return SOP
   */
    function updateStatistics(){
      $this->getReadabilityScore();
      $this->getSentimentScore();
      $this->getEmotionsScore();
      $this->getPersonalityScore();
      return SOP::newFromId($this->id);
    }
}
require_once("SopModels/".$config->getValue("networkName")."/SOP.php");
?>
