<?php
    /**
    * @package GrandObjects
    */

abstract class AbstractGsmsData extends BackboneModel{

    var $id;
    var $user_id;
    var $gsms_id;
    var $ois_id;
    var $student_id;
    var $year;
    var $visible;
    var $additional = array(); 
    var $gsms_url;
    var $submitted_date;
    var $annotations = array();

    abstract function checkGSMS();
    abstract function getSopPdf();
    abstract function getGSMSUrl();
    abstract function getSopUrl();
    abstract function checkSop();
    abstract function getContent($asString=false);
    abstract function getExtraColumns();
    abstract function getEducationalHistory($html_string=false);
    abstract function getReviewers();
    abstract function getAdmitResult($user);
    abstract function getReviewComments($user);
    abstract function getReviewRanking($user);
    abstract function getHiddenStatus($user);
    abstract function setHiddenStatus($user, $value="");

    function AbstractGsmsData($data){
        global $config;
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->user_id = $data[0]['user_id'];
            $this->visible = $data[0]['visible'];
            $this->student_id = $data[0]['student_id'];
            $this->gsms_id = $data[0]['gsms_id'];
            $this->ois_id = $data[0]['ois_id'];
            $this->additional = json_decode($data[0]['additional'], true);
            $sub = explode(" ",$data[0]['submitted_date']);
            $this->submitted_date = $sub[0];
            $this->annotations = SOP_Annotation::getAllSOPAnnotations($this->id);
        }
    }

    /**
    * Returns a new GsmsData from the given id
    * @param integer $id The id of the GsmsData
    * @return GsmsData The GsmsData with the given id. If no
    * gsms exists with that id, it will return an empty gsms.
    */
    static function newFromUserId($id, $year=""){
        $dbyear = ($year != "" && $year != YEAR) ? "_$year" : "";
        if(Cache::exists("gsms_user_$id{$dbyear}")){
            $data = Cache::fetch("gsms_user_$id{$dbyear}");
        }
        else{
            $data = DBFunctions::select(array("grand_gsms$dbyear"),
                                        array('id',
                                              'user_id',
                                              'gsms_id',
                                              'ois_id',
                                              'student_id',
                                              'additional',
                                              'submitted_date',
                                              'visible'),
                                        array('user_id' => EQ($id)),
                                        array('submitted_date' => 'DESC'),
                                        array(1));
            Cache::store("gsms_user_$id{$dbyear}", $data);
        }
        $gsms = new GsmsData($data, $id);
        $gsms->year = $year;
        return $gsms;
    }

    static function getAllVisibleGsms($year=""){
        global $wgRoleValues;
        $dbyear = ($year != "" && $year != YEAR) ? "_$year" : "";
        $gsms_array = array();
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(EVALUATOR)){
            $sql = "SELECT user_id, id, max(submitted_date) as date FROM grand_gsms{$dbyear} WHERE visible = 'true' GROUP BY user_id ORDER BY submitted_date";
            $data = DBFunctions::execSQL($sql);
        }
        if(count($data) >0){
            foreach($data as $gsms){
                $gsms_array[] = GsmsData::newFromId($gsms['id'], $year);
            }
        }
        return $gsms_array;
    }
   
  /**
   * newFromId Returns an Gsms object from a given id
   * @param $id
   * @return $gsms Gsms object
   */
    static function newFromId($id, $year=""){
        $dbyear = ($year != "" && $year != YEAR) ? "_$year" : "";
        if(Cache::exists("gsms_$id{$dbyear}")){
            $data = Cache::fetch("gsms_$id{$dbyear}");
        }
        else{
            $data = DBFunctions::select(array("grand_gsms$dbyear"),
                                        array('id',
                                              'user_id',
                                              'gsms_id',
                                              'ois_id',
                                              'student_id',
                                              'additional',
                                              'submitted_date',
                                              'visible'),
                                        array('id' => EQ($id)));
            Cache::store("gsms_$id{$dbyear}", $data);
        }
        $gsms = new GsmsData($data);
        $gsms->year = $year;
        return $gsms;
    }
    
    /**
   * newFromId Returns an Gsms object from a given ois id
   * @param $id
   * @return $gsms Gsms object
   */
    static function newFromOisId($id, $year=""){
        $dbyear = ($year != "" && $year != YEAR) ? "_$year" : "";
        if(Cache::exists("gsms_$id{$dbyear}")){
            $data = Cache::fetch("gsms_$id{$dbyear}");
        }
        else{
            $data = DBFunctions::select(array("grand_gsms$dbyear"),
                                        array('id',
                                              'user_id',
                                              'gsms_id',
                                              'ois_id',
                                              'student_id',
                                              'additional',
                                              'submitted_date',
                                              'visible'),
                                        array('ois_id' => EQ($id)));
            Cache::store("gsms_$id{$dbyear}", $data);
        }
        $gsms = new GsmsData($data);
        $gsms->year = $year;
        return $gsms;
    }
    
    function getId(){
        return $this->id;
    }
    
    /**
     * getUrl Returns the url of this Paper's page
     * @return string The url of this Paper's page
     */
    function getUrl(){
        global $wgServer, $wgScriptPath;
        if(!isset($_GET['embed']) || $_GET['embed'] == 'false'){
            return "{$wgServer}{$wgScriptPath}/index.php/Special:Sops#/{$this->user_id}/edit";
        }
        return "{$wgServer}{$wgScriptPath}/index.php/Special:Sops?embed#/{$this->user_id}/edit";
    }

    /**
     * Returns True if the course is saved correctly to the course table in the database
     * @return boolean True if the database accepted the new course
    */
    function create(){
        $me = Person::newFromWGUser();
        if($me->isLoggedIn()){
                DBFunctions::insert('grand_gsms',
                                    array('`user_id`' => $this->user_id,
                                          '`student_id`' => $this->student_id,
                                          '`gsms_id`' => $this->gsms_id,
                                          '`ois_id`' => $this->ois_id,
                                          '`additional`' => json_encode($this->additional),
                                          '`visible`' => $this->visible));
            Cache::delete("gsms_user_{$this->user_id}");
        }
    }

    /**
     * Returns True if the course is updated correctly to the course table in the database
     * @return boolean True if the database accepted the updated course
    */
    function update(){
        $me = Person::newFromWGUser();
        if($me->isLoggedIn()){
            $status = DBFunctions::update('grand_gsms',
                                    array('`student_id`' => $this->student_id,
                                          '`gsms_id`' => $this->gsms_id,
                                          '`ois_id`' => $this->ois_id,
                                          '`additional`' => json_encode($this->additional),
                                          '`submitted_date`' => $this->submitted_date." 00:00:00",
                                          '`visible`' => $this->visible),
                                    array('user_id' => EQ($this->user_id)));
            DBFunctions::commit();
            Cache::delete("gsms_", true);
            Cache::delete("gsms_user_", true);
        }
        return true;
    }

    /**
     * Returns an array of this object
     * @return array of object
    */
    function toArray(){
        global $wgUser, $config;
        $year = ($this->year != "") ? $this->year : YEAR;
        $student = Person::newFromId($this->user_id);
        $student_data = array('id' => $student->getId(),
                        'fname' => $student->getFirstName(),
                        'lname' => $student->getLastName(),
                        'name' => $student->getReversedName(),
                        'url' => $student->getUrl(),
                        'email' => $student->getEmail());

        $this->setAdditional("education_history", $this->getEducationalHistory(true));
        
        $json = array('id' =>$this->id,
                      'ois_id' => $this->ois_id,
                      'user_id' =>$this->user_id,
                      'year' => $year,
                      'student_data' => $student_data,
                      'gsms_id' => $this->gsms_id,
                      'student_id' => $this->student_id,
                      'additional' => $this->getAdditional(),
                      'content' => $this->getContent(),
                      'content_string' => $this->getContent(true),
                      'gsms_data' => $this->checkGSMS(),
                      'sop_check' => $this->checkSOP(),
                      'sop_pdf' => $this->getSopUrl(),
                      'gsms_pdf' => $this->getGSMSUrl(),
                      'sop_url' => $this->getUrl());

        //adding reviewers array so can have on overview table
        $reviewers = array();
        $reviewer_array = $student->getEvaluators($year,"sop");
        foreach($reviewer_array as $reviewer){
            $person = $reviewer;
            $reviewers[] = array('id' => $person->getId(),
                                 'name' => $person->getNameForForms(),
                                 'url' => $person->getUrl(),
                                 'decision' => $this->getAdmitResult($reviewer->getId()),
                                 'comments' => $this->getReviewComments($reviewer->getId()),
                                 'rank' => $this->getReviewRanking($reviewer->getId()),
                                 'hidden' => $this->getHiddenStatus($reviewer->getId()));
        }
        $json['reviewers'] = $reviewers;

        $otherReviewers = array();
        
        $other_array = $student->getOtherEvaluators($year);
        foreach($other_array as $other){
            $otherReviewers[] = array('id' => $other->getId(),
                                      'name' => $other->getNameForForms(),
                                      'url' => $other->getUrl(),
                                      'decision' => $this->getAdmitResult($other->getId()),
                                      'rank' => $this->getReviewRanking($other->getId()),
                                      'hidden' => $this->getHiddenStatus($other->getId()));
        }
        
        $json['other_reviewers'] = $otherReviewers;
        
        //adding decisions by boards
        $json['admit'] = $this->getFinalAdmit();
        $json['comments'] = $this->getFinalComments();
        $json['area'] = "";
        $json['degree'] = $this->getFinalProgram();
        $json['ftpt'] = $this->getFullTimePartTime();

        if($config->getValue('networkName') == 'GARS'){
           //adding nationality as one string
            $nationality = array();
            $nationality[] = ($this->getAdditional('indigenous') == "Yes") ? "Indigenous" : "";
            $nationality[] = ($this->getAdditional('canadian') == "Yes") ? "Canadian" : "";
            $nationality[] = ($this->getAdditional('saskatchewan') == "Yes") ? "Saskatchewan" : "";
            $nationality[] = ($this->getAdditional('international') == "Yes") ? "International" : "";

            $nationality_note = "";
            foreach($nationality as $note){
                if($note != ""){
                    $nationality_note .= $note.',<br />';
                }    
            }
            $json['nationality_note'] = $nationality_note;
        }

        $json['additional'] = array_merge($json['additional'], $this->getExtraColumns());
        $json['annotations'] = $this->annotations;
        return $json;
    }

    function getBlobValue($blobType, $year, $reportType, $reportSection, $blobItem, $userId=null, $projectId=0, $subItem=0){
        if ($userId === null) {
          $userId = $this->user_id;
        }
        $blb = new ReportBlob($blobType, $year, $userId, $projectId);
        $addr = ReportBlob::create_address($reportType, $reportSection, $blobItem, $subItem);
        $result = $blb->load($addr);
        $data = $blb->getData();

        return $data;
    }

    function getAdditional($field="", $default=""){
        if($field == ""){
            return $this->additional;
        }
        else{
            return (isset($this->additional[$field]) && $this->additional[$field] != "") ? $this->additional[$field] : $default;
        }
    }
    
    function setAdditional($field, $value){
        if(!is_array($this->additional)){
            $this->additional = array();
        }
        if($value == "" || $value === 0 || $value === "0" || $value == "0000-00-00 00:00:00"){
            // Value is 'null', unset it
            unset($this->additional[$field]);
        }
        else{
            $this->additional[$field] = $value;
        }
    }

    function delete(){
            //TODO:implement function
    }
    function exists(){
            //TODO:implement function
    }
    function getCacheId(){
            //TODO:implement function
    }

    function getFinalProgram() {
        $prog = $this->getAdditional('admission_program_name');
        if (strpos($prog, "Master of Science (Thes)") !== false) {
            return "MSc";
        } else if (strpos($prog, "Master of Science (Crse)") !== false) {
            return "MSc-C";
        } else if (strpos($prog, "Doctor of Philosophy") !== false) {
            return "PhD";
        }
        return $prog;
    }

    function getFullTimePartTime() {
        $prog = $this->getAdditional("program");
        $progSplit = explode(" - ", $prog);
        $time = isset($progSplit[1]) ? $progSplit[1] : "";
        if (strtolower($time) == "full time") {
          $time = "FT";
        } else if (strtolower($time) == "part time") {
          $time = "PT";
        }
        return $time;
    }
    
    /**
    * returns string if SOP was suggested to be admitted or not by the user specified in argument.
    * @return $string either 'Admit', 'Not Admit' or 'Undecided' based on answer of PDF report.
    */
    function getFinalAdmit(){
        $dec = $this->getAdditional('folder');
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
        $blob = new ReportBlob(BLOB_TEXT, $year, 0, $this->getId());
        $blob_address = ReportBlob::create_address('RP_COM', 'OT_COM', 'Q2', $this->getId());
        $blob->load($blob_address);
        $data = $blob->getData();
        return $data;
    }

    function getAssignedSupervisors() {
        $year = ($this->year != "") ? $year : YEAR;
        return $this->getBlobValue(BLOB_ARRAY, $year, "RP_COM", "OT_COM", "Q14", 0, $this->getId());
    }

    function getFunding() {
        $year = ($this->year != "") ? $year : YEAR;
        return $this->getBlobValue(BLOB_TEXT, $year, "RP_COM", "OT_COM", "Q4", 0, $this->id, $this->getId());
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
     * getReadabilityScore Updates and returns readablility score for the SOP
     * @return mixed|string
     */
    function getReadabilityScore(){
        $content = $this->getContentToSend();
        $tasha = new TASHA();
        $tasha->content = $content;
        $result = $tasha->getReadabilityScore();
        $this->setAdditional('readability_score', $result['readability_score']);
        $this->setAdditional('min_age', $result['min_age']);
        $this->setAdditional('reading_ease', $result['reading_ease']);
        $this->setAdditional('ari_grade', $result['ari_grade']);
        $this->setAdditional('ari_age', $result['ari_age']);
        $this->setAdditional('colemanliau_grade', $result['colemanliau_grade']);
        $this->setAdditional('colemanliau_age', $result['colemanliau_age']);
        $this->setAdditional('dalechall_index', $result['dalechall_index']);
        $this->setAdditional('dalechall_grade', $result['dalechall_grade']);
        $this->setAdditional('dalechall_age', $result['dalechall_age']);
        $this->setAdditional('fleschkincaid_grade', $result['fleschkincaid_grade']);
        $this->setAdditional('fleschkincaid_age', $result['fleschkincaid_age']);
        $this->setAdditional('smog_grade', $result['smog_grade']);
        $this->setAdditional('smog_age', $result['smog_age']);
        $this->setAdditional('word_count', $result['word_count']);
        $this->setAdditional('sentlen_ave', $result['sentlen_ave']);
        $this->setAdditional('wordletter_ave', $result['wordletter_ave']);
        return $this->update();
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
        $this->setAdditional('sentiment_type', $result['sentiment_type']);
        $this->setAdditional('sentiment_val', $result['sentiment_val']);
        return $this->update();
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
        $this->setAdditional('emotion_stats', json_encode($result));
        return $this->update();
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
        $this->setAdditional('personality_stats', json_encode($result));
        return $this->update();
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
        $this->setAdditional('errors', $result);
        return $this->update();
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
        $this->getSyntaxErrorCount();
        return $this;
    }
}

require_once("GSMS/".$config->getValue("networkName").".php");

?>
