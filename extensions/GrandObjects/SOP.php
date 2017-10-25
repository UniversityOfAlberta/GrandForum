<?php

/**
 * @package GrandObjects
 */
class SOP extends BackboneModel{
    
    static $cache = array();
    static $idsCache = array();
    
    var $id;
    var $content;
    var $questions;
    var $user_id;
    var $date_created;
    var $visible = false;
    
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
   * newFromId Returns an SOP object from a given id
   * @param $id
   * @return $sop SOP object
   */
    static function newFromId($id){
        if(isset(self::$cache[$id])){
            return self::$cache[$id];
        }
        self::generateCache();
        $data = array();
        if(isset(self::$idsCache[$id])){
            $data[] = self::$idsCache[$id];
        }
        $sop = new SOP($data);
        $cache[$id] = $sop;
        return $sop;
    }

  /**
   * newFromId Returns an SOP object from a given id
   * @param $id
   * @return $sop SOP object
   */
    static function newFromUserId($id){
        $data = DBFunctions::select(array('grand_sop'),
                                    array('id'),
                                    array('user_id' => EQ($id)));
	if(count($data)>0){
	    $sop = SOP::newFromId($data[0]['id']);
	    return $sop;
	}
	return false;
    }

  /**
   * SOP constructor.
   * @param $data
   */
    function SOP($data){
        if(count($data) > 0){
            $row = $data[0];
            $this->id = $row['id'];
            $this->content = $row['content'];
            $this->user_id = $row['user_id'];
            $this->date_created = $row['date_created'];

            $this->sentiment_val = $row['sentiment_val'];
            $this->sentiment_type = $row['sentiment_type'];
            $this->personality_stats = $row['personality_stats'];
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

    function getBlobValue($blobType, $year, $reportType, $reportSection, $blobItem){
        $projectId = 0;
        
        $blb = new ReportBlob($blobType, $year, $this->user_id, $projectId);
        $addr = ReportBlob::create_address($reportType, $reportSection, $blobItem, 0);
        $result = $blb->load($addr);
        $data = $blb->getData();
        
        return $data;
    }

    function getCSColumns() {
        $moreJson = array();
        $AoS = $this->getBlobValue(BLOB_ARRAY, YEAR, "RP_CS", "CS_QUESTIONS_tab1", "Q13");
        $moreJson['areas_of_study'] = implode(", ", $AoS['q13']);
        //var_dump($moreJson['areas_of_study']);

        $blob = $this->getBlobValue(BLOB_ARRAY, 0, "RP_CS", "CS_QUESTIONS_tab1", "Q14");
        
        $moreJson['supervisors'] = implode(", ", array($blob['q14']));

        $blob = $this->getBlobValue(BLOB_ARRAY, 0, "RP_CS", "CS_QUESTIONS_tab1", "Q16");
        $moreJson['scholarships_held'] = implode(", ", array($blob['q16']));

        $blob = $this->getBlobValue(BLOB_ARRAY, 0, "RP_CS", "CS_QUESTIONS_tab1", "Q15");
        $moreJson['scholarships_applied'] = implode(", ", array($blob['q15']));

        $moreJson['gpaNormalized'] = $this->getBlobValue(BLOB_TEXT, 0, "RP_CS", "CS_QUESTIONS_tab1", "Q21");
        $moreJson['gre1'] = $this->getBlobValue(BLOB_TEXT, 0, "RP_CS", "CS_QUESTIONS_tab1", "Q24");
        $moreJson['gre2'] = $this->getBlobValue(BLOB_TEXT, 0, "RP_CS", "CS_QUESTIONS_tab1", "Q25");
        $moreJson['gre3'] = $this->getBlobValue(BLOB_TEXT, 0, "RP_CS", "CS_QUESTIONS_tab1", "Q26");
        $moreJson['gre4'] = $this->getBlobValue(BLOB_TEXT, 0, "RP_CS", "CS_QUESTIONS_tab1", "Q27");

        // # of Publications
        $blob = $this->getBlobValue(BLOB_ARRAY, 0, "RP_CS", "CS_QUESTIONS_tab3", "qPublications");
        $moreJson['num_publications'] = count($blob['qResExp2']);

        // # of awards
        $blob = $this->getBlobValue(BLOB_ARRAY, 0, "RP_CS", "CS_QUESTIONS_tab4", "qAwards");
        $moreJson['num_awards'] = count($blob['qAwards']);

        // Courses (number of courses, number of areas)
        $blob = $this->getBlobValue(BLOB_ARRAY, 0, "RP_CS", "CS_QUESTIONS_tab6", "qCourses");
        $moreJson['courses'] = implode(", ", array($blob['qEducation2']));

        return $moreJson;

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
        //if($me->isRoleAtLeast(MANAGER)){
            $data = DBFunctions::select(array('grand_sop'),
                                        array('id'));
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


  /**
   *
   */
    function create(){
        $status = DBFunctions::insert('grand_sop',
				     array('user_id' => $this->user_id),
				     true);
    }

  /**
   *
   */
    function update(){
        //TODO
    }

  /**
   *
   */
    function delete(){
        //TODO
    }

  /**
   * toArray Converts SOP object to array
   * @return array
   */
    function toArray(){
        global $wgUser;
        if(!$wgUser->isLoggedIn()){
            return array();
        }
        
        $user = Person::newFromId($this->getUser());
        $author = array('id' => $user->getId(),
                        'name' => $user->getReversedName(),
                        'url' => $user->getUrl());
        $gsms = $user->getGSMS();
	      $nationality = array();
        $nationality[] = ($gsms->indigenous == "Yes") ? "Indigenous" : "";
        $nationality[] = ($gsms->canadian == "Yes") ? "Canadian" : "";
        $nationality[] = ($gsms->saskatchewan == "Yes") ? "Saskatchewan" : "";
        $nationality[] = ($gsms->international == "Yes") ? "International" : "";

	$nationality_note = "";
	foreach($nationality as $note){
	    if($note != ""){
		$nationality_note .= $note.',<br />';
	    }
	}
        $reviewers = array();
	$student = Person::newFromId($this->user_id);
	$reviewer_array = $student->getEvaluators(YEAR,"sop"); 
        //foreach($this->getReviewers() as $id){
	foreach($reviewer_array as $reviewer){
            //$person = Person::newFromId($id);
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
        $this->getCSColumns();
        $json = array('id' => $this->getId(),
                      'content' => $this->getContent(),
                      'content_string' => $this->getContent(true),
                      'user_id' => $this->getUser(),
                      'date_created' => $this->getDateCreated(),
                      'url' => $this->getUrl(),
                      'author' => $author,
            		      'gsms' => $gsms->toArray(),
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
		      'gsms_url' => $this->getGSMSUrl());

          // Get from Config which forum we are looking at to add extra columns
          $json = array_merge($json, $this->getCSColumns());
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
	    return unserialize($this->personality_stats);
    }

  /**
   * getContent Gets the SOP Content
   * @return array with answers to questions
   */
    function getContent($asString=false){
        if($this->questions == null){
	    $qs = array('Q1', 'Q2', 'Q3', 'Q4', 'Q5');
	    $qstrings = array('(Describe how your personal background and experiences would make you a good occupational therapist)',
			      '(Tell us about your work or volunteer experiences and how that would ultimately contribute to the profession of occupational therapy)',
			      '(Tell us your academic experiences and how that has prepared you for being successful in the MScOT program at the University of Alberta)',
			      '(Outline the key way Canada\'s health care system can meet the challenges of tomorrow)',
			      '(Is there anything else you would like to tell us to help the Admissions Committee in making their decision?)');
            $questions = array();
            $blob = new ReportBlob(BLOB_TEXT, REPORTING_YEAR, $this->getUser(), 0);
	    $qnumber = 0;
            foreach($qs as $q){
                $blob_address = ReportBlob::create_address('RP_OT', 'OT_QUESTIONS', $q, 0);
	            $blob->load($blob_address);
	            $data = $blob->getData();
	            $questions[$q.' '.$qstrings[$qnumber]] = $data;
		    $qnumber++;
            }
            $this->questions = $questions;
        }
	    $this->content = $this->questions;
	    if($asString){
             $string = "";
             foreach($this->content as $question => $answer){
                $string = $string."<b>". $question."</b>"."<br /><br />".$answer."<br /><br />";
             }
             return $string;
	    }
        return $this->content;
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
    * returns an array of the faculty staff that have finished reviewing this SOP.
    * this checks only if the last question was answered which is 'admit or not admit?'
    * @return $reviewers array of the id of reviewers who have finished reviewing SOP.
    */
    function getReviewers(){
        $sql = "SELECT DISTINCT(user_id), data
                FROM grand_report_blobs
                WHERE rp_section = 'OT_REVIEW'
		        AND rp_item = 'Q13'
		        AND rp_subitem =".$this->id;
        $data = DBFunctions::execSQL($sql);
	    $reviewers = array();
        if(count($data)>0){
            foreach($data as $user){
		        if($user['data'] != ''){
                    $reviewers[] = $user['user_id'];
		        }
            }
        }
	    return $reviewers;
    }

   /**
    * returns string if SOP was suggested to be admitted or not by the user specified in argument.
    * @return $string either 'Admit', 'Not Admit' or 'Undecided' based on answer of PDF report.
    */
    function getAdmitResult($user){
        $blob = new ReportBlob(BLOB_TEXT, REPORTING_YEAR, $user, $this->getId());
	    $blob_address = ReportBlob::create_address('RP_OT', 'OT_REVIEW', 'Q13', $this->getId());
	    $blob->load($blob_address);
	    $data = $blob->getData();

        if($data == 'Yes'){
            return "Admit";
        }
        elseif($data == 'No'){
            return "Reject";
        }
	elseif($data == 'Special Consideration'){
	    return "Special Consideration";
	}
	elseif($data == 'Undecided'){
            return "Undecided";
	}
	else{
	    return "--";
	}
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
        $blob = new ReportBlob(BLOB_TEXT, REPORTING_YEAR, 0, $this->getId());
        $blob_address = ReportBlob::create_address('RP_COM', 'OT_COM', 'Q1', $this->getId());
        $blob->load($blob_address);
        $data = $blob->getData();
	if($data == "Waitlist"){
	    $number = 1;
	    $blob = new ReportBlob(BLOB_TEXT, REPORTING_YEAR, 0, $this->getId());
            $blob_address = ReportBlob::create_address('RP_COM', 'OT_COM', 'Q3', $this->getId());
            $blob->load($blob_address);
            $number = $blob->getData();
	    return $data.' '.$number;
	}	
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
        $curl_url = "http://162.246.157.115/tasha/readability_score";
        $curl_post_fields_array = array('content'=>$content);
	    $curl_post_fields = json_encode($curl_post_fields_array);
        $curl_header = array('Content-Type: application/json');
        $curl_array = array(
            CURLOPT_URL => $curl_url,
            CURLOPT_HTTPHEADER => $curl_header,
            CURLOPT_POSTFIELDS =>$curl_post_fields,
            CURLOPT_RETURNTRANSFER =>true,
            CURLOPT_POST => true,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => "cmput401:tasha"
        );
        $curl = curl_init();
        curl_setopt_array($curl, $curl_array);
        $data = curl_exec($curl);
        $result = '';
        if ($error = curl_error($curl)){
            $result = $error;
        }
        curl_close($curl);
        if(empty($error)){
            $result = $data;
        }
	    $result = json_decode($result, true);

	    $ari_grade = $result["ari"]["us_grade"];
	    $ari_age = $result["ari"]["min_age"];
	    $colemanliau_grade = $result["colemanliau"]["us_grade"];
	    $colemanliau_age = $result["colemanliau"]["min_age"];
	    $dalechall_index = $result["dalechall"]["readingindex"];
	    $dalechall_grade = $result["dalechall"]["us_grade"];
	    $dalechall_age = $result["dalechall"]["min_age"];
	    $fleschkincaid_grade = $result["fleschkincaid"]["us_grade"];
	    $fleschkincaid_age = $result["fleschkincaid"]["min_age"];
	    $smog_grade = $result["smog"]["us_grade"];
	    $smog_age = $result["smog"]["min_age"];

        $readability_score
            = ($ari_grade + $colemanliau_grade + $dalechall_grade +
               $fleschkincaid_grade + $smog_grade)
              / 5;
        $min_age
            = ($ari_age + $colemanliau_age + $dalechall_age +
               $fleschkincaid_age + $smog_age)
              / 5;

	    $reading_ease = $result["flesch"]["reading_ease"];
 
        $word_count = $result["flesch"]["scores"]["word_count"];
	    $sentlen_ave = $result["dalechall"]["scores"]["sentlen_average"];
	    $wordletter_ave = $result["dalechall"]["scores"]["wordlen_average"];

	    $sql = "UPDATE grand_sop
		    SET readability_score=$readability_score, min_age=$min_age, 
                        reading_ease=$reading_ease, ari_grade=$ari_grade,
                        ari_age=$ari_age, colemanliau_grade=$colemanliau_grade,
                        colemanliau_age=$colemanliau_age, dalechall_index=$dalechall_index,
                        dalechall_grade=$dalechall_grade, dalechall_age=$dalechall_age,
                        fleschkincaid_grade=$fleschkincaid_grade,
                        fleschkincaid_age=$fleschkincaid_grade,
                        smog_grade=$smog_grade, smog_age=$smog_age,
                        word_count=$word_count, sentlen_ave=$sentlen_ave,
                        wordletter_ave=$wordletter_ave
	            WHERE id={$this->id};";
	    $status = DBFunctions::execSQL($sql,true);
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
	    $content = utf8_encode(htmlspecialchars_decode($content, ENT_QUOTES));
        $curl_url = "http://162.246.157.115/tasha/sentiment";
        $curl_post_fields_array = array('content'=> $content);
        $curl_post_fields = json_encode($curl_post_fields_array);
        $curl_header = array('Content-Type: application/json');
        $curl_array = array(
            CURLOPT_URL => $curl_url,
            CURLOPT_HTTPHEADER => $curl_header,
            CURLOPT_POSTFIELDS =>$curl_post_fields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => "cmput401:tasha"
        );
        $curl = curl_init();
        curl_setopt_array($curl, $curl_array);
        $data = curl_exec($curl);
        $result = '';
        if ($error = curl_error($curl)){
            $result = $error;
        }
        curl_close($curl);
        if(empty($error)){
            $result = $data;
        }
        $result = json_decode($result, true);
        $sentiment_val = $result['docSentiment']['score'];
        $sentiment_type = $result['docSentiment']['type'];
        $sql = "UPDATE grand_sop
                SET sentiment_type='$sentiment_type', sentiment_val='$sentiment_val'
                WHERE id={$this->id};";

        $status = DBFunctions::execSQL($sql,true);
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
        $curl_url = "http://162.246.157.115/tasha/emotions";
        $curl_post_fields_array = array('content'=> $content);
        $curl_post_fields = json_encode($curl_post_fields_array);
        $curl_header = array('Content-Type: application/json');
        $curl_array = array(
            CURLOPT_URL => $curl_url,
            CURLOPT_HTTPHEADER => $curl_header,
            CURLOPT_POSTFIELDS =>$curl_post_fields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => "cmput401:tasha"
        );
        $curl = curl_init();
        curl_setopt_array($curl, $curl_array);
        $data = curl_exec($curl);
        $result = '';
        if ($error = curl_error($curl)){
            $result = $error;
        }
        curl_close($curl);
        if(empty($error)){
            $result = $data;
        }
        $result = json_decode($result, true);
	    $emotions_array = array();
        $emotions_array['anger'] = $result['docEmotions']['anger'];
        $emotions_array['disgust'] = $result['docEmotions']['disgust'];
        $emotions_array['fear'] = $result['docEmotions']['fear'];
        $emotions_array['joy'] = $result['docEmotions']['joy'];
        $emotions_array['sadness'] = $result['docEmotions']['sadness'];

        $sql = "UPDATE grand_sop
                SET emotion_stats='".serialize($emotions_array)."'
                WHERE id={$this->id};";

        $status = DBFunctions::execSQL($sql,true);
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
        $curl_url = "http://162.246.157.115/tasha/personality";
        $content = utf8_encode(htmlspecialchars_decode($content, ENT_QUOTES));
        $content = preg_replace('/[^A-Za-z0-9\-]/', ' ', $content);
        $curl_post_fields_array = array('content'=> $content);
        $curl_post_fields = json_encode($curl_post_fields_array);
        $curl_header = array('Content-Type: application/json');
        $curl_array = array(
            CURLOPT_URL => $curl_url,
            CURLOPT_HTTPHEADER => $curl_header,
            CURLOPT_POSTFIELDS =>$curl_post_fields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => "cmput401:tasha"
        );
        $curl = curl_init();
        curl_setopt_array($curl, $curl_array);
        $data = curl_exec($curl);
        $result = '';
        if ($error = curl_error($curl)){
            $result = $error;
        }
        curl_close($curl);
        if(empty($error)){
            $result = $data;
        }
        $result = json_decode($result, true);

        $sql = "UPDATE grand_sop
                SET personality_stats='".serialize($result)."'
                WHERE id={$this->id};";

        $status = DBFunctions::execSQL($sql,true);
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
        $curl_url = "http://162.246.157.115/tasha/syntac_error";
        $curl_post_fields_array = array('content'=> $content, 'ftype'=>'html');
        $curl_post_fields = json_encode($curl_post_fields_array);
        $curl_header = array('Content-Type: application/json');
        $curl_array = array(
            CURLOPT_URL => $curl_url,
            CURLOPT_HTTPHEADER => $curl_header,
            CURLOPT_POSTFIELDS =>$curl_post_fields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => "cmput401:tasha"
        );
        $curl = curl_init();
        curl_setopt_array($curl, $curl_array);
        $data = curl_exec($curl);
        $result = '';
        if ($error = curl_error($curl)){
            $result = $error;
        }
        curl_close($curl);
        if(empty($error)){
            $result = $data;
        }
        $result = json_decode($result, true);
	    $errors = $result['errors'];	
        $sql = "UPDATE grand_sop
                SET errors=$errors
                WHERE id={$this->id};";
        $status = DBFunctions::execSQL($sql,true);
        if($status){
            DBFunctions::commit();
        }

    }

  /**
   * updateStatistics Updates all the SOP statistics
   * @return SOP
   */
    function updateStatistics(){
    //	$this->getSyntaxErrorCount();
	    $this->getReadabilityScore();
	    $this->getSentimentScore();
	    $this->getEmotionsScore();
	    $this->getPersonalityScore();
	    return SOP::newFromId($this->id);
    }

    function checkGSMS(){
	$person = Person::newFromId($this->user_id);
	$url = $person->getGSMSPdfUrl();
	if($url != ""){
	    return true;
	}
	return false;
    }

    function getGSMSUrl(){
	if($this->checkGSMS()){
        $person = Person::newFromId($this->user_id);
        $url = $person->getGSMSPdfUrl();
	return $url;
	}
	return "";
    }
}

?>
