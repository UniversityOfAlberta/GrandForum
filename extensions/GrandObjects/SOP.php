<?php

/**
 * @package GrandObjects
 */
define('PREFIX', "data:,");
define('LENGTH', 2000 - strlen(PREFIX)); # Internet Explorer 2KB URI limit (http://support.microsoft.com/kb/208427)

class SOP extends BackboneModel{
    
    static $cache = array();
    
    var $id;
    var $content;
    var $user_id;
    var $date_created;
    var $sentiment_val;
    var $sentiment_type;
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
    var $anger_score;
    var $disgust_score;
    var $fear_score;
    var $joy_score;
    var $sadness_score;

    var $errors;
    var $sentlen_ave;
    var $wordletter_ave;
    var $min_age;
    var $word_count;
    var $annotations = array();	
    var $pdf;    
    var $personality_stats;


  /**
   * newFromId Creates a new SOP object from a given id
   * @param $id
   * @return SOP
   */
    static function newFromId($id){
        $data = DBFunctions::select(array('grand_sop'),
                                    array('*'),
                                    array('id' => EQ($id)));
        $sop = new SOP($data);
        return $sop;
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
	    $this->personality_stats = $row['personality_stats'];
            $emotions_array = unserialize($row['emotion_stats']);
	    $this->anger_score = $emotions_array['anger'];
            $this->disgust_score = $emotions_array['disgust'];
            $this->fear_score = $emotions_array['fear'];
            $this->joy_score = $emotions_array['joy'];
            $this->sadness_score = $emotions_array['sadness'];
        }
	$this->annotations = SOP_Annotation::getAllSOPAnnotations($this->id);
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
                $sops[] = $sop;
            }
        }
        return $sops;
    }

   static function createSoPFromReports(){
        $sql = "SELECT DISTINCT(user_id)
                FROM grand_report_blobs
                WHERE user_id NOT IN (SELECT user_id FROM grand_sop) AND rp_section LIKE 'OT_EULA'";
        $data = DBFunctions::execSQL($sql);
	if(count($data)>0){
	    foreach($data as $user){
		$user_id = $user['user_id'];
		$sop = new SOP(array());
		$sop->user_id = $user_id;
		$sop->create();
	    }
	}
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
        $author = array('id'=> $user->getId(),
                        'name' => $user->getNameForForms(),
                        'url' => $user->getUrl());
	$reviewers = array();
	foreach($this->getReviewers() as $id){
	    $person = Person::newFromId($id);
	    $reviewers[] = array('id'=> $person->getId(),
			       'name' => $person->getNameForForms(),
			       'url' => $person->getUrl(),
			       'decision'=>$this->getAdmitResult($id));
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
            $json
                = array('id' => $this->getId(),
                        'content' => $this->getContent(),
			'content_string' => $this->getContentString(),
                        'user_id' => $this->getUser(),
                        'date_created' => $this->getDateCreated(),
			'url' => $this->getUrl(),
			'author' => $author,
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
			'pdf_data' => $this->getPdfAsHtml());
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

    function getPdf(){
	return unserialize($this->pdf);
    }
    function getPdfAsHtml(){
	$pdf = $this->getPdf();
	if(isset($pdf['Referees'])){
        $refs = $pdf['Referees'];
	$i = 0;
	if(is_array($refs)){
            foreach($refs as $ref){
            	$pdf['Referees'][$i]['responses'] = nl2br($ref['responses']);
	    	$i++;
            }
	}
	}
	return $pdf;
    }
    function getPersonalityStats(){
	return unserialize($this->personality_stats);
    }
  /**
   * getContent Gets the SOP Content
   * @return mixed
   */
    function getContent(){
	$data = DBFunctions::select(array('grand_report_blobs'),
                                        array('*'),
					array('user_id' => EQ($this->getUser())));
        //}
	$questions = array();
        if(count($data) >0){
	    $i = 1;
            foreach($data as $sopId){
		if($sopId['rp_item'] != "EULA"){
	 	    $questions[$sopId['rp_item']] = $sopId['data'];
		}
		$i++;
            }
        }
	$this->content = $questions;
        return $this->content;
    }

    function getContentString(){
	$content = $this->getContent();
	$string = "";
	foreach($content as $question => $answer){
	    $string = $string."<b>". $question."</b>"."<br /><br />".$answer."<br /><br />";
	}
	return $string;
    }

    function getContentToSend(){
        $content = $this->getContent();
        $string = "";
        foreach($content as $question => $answer){
            $string = $string.$answer;
        }
        return $string;
    }

    function getReviewers(){
        $sql = "SELECT DISTINCT(user_id), data
                FROM grand_report_blobs
                WHERE rp_section LIKE 'OT_REVIEW'
		AND rp_item LIKE 'Q13'
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

   function getAdmitResult($user){
	$sql = "SELECT data 
		FROM grand_report_blobs
		WHERE rp_section LIKE 'OT_REVIEW'
		AND rp_item LIKE 'Q13'
		AND proj_id =".$this->getId()."
		AND user_id =".$user;
	$data = DBFunctions::execSQL($sql);
        if(count($data)>0){
	    if($data[0]['data'] == 'Yes'){
		return "Admit";
	    }
	    elseif($data[0]['data'] == 'No'){
	        return "Not Admit";
	    }
	}	
	return "Undecided";
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
   * getReadabilityScore Get readablility score for the SOP
   * @return mixed|string
   */
    function getReadabilityScore(){
	$content = $this->getContentToSend();
	$content = utf8_encode(htmlspecialchars_decode($content, ENT_QUOTES));
        //$content = str_replace(' ', '-', $content);
        $content = preg_replace('/[^A-Za-z.,\']/', ' ',$content);
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
        //$content = str_replace(' ', '-', $content);
        $content = preg_replace('/[^A-Za-z.,\']/', ' ',$content);
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
        print_r($result);

        return $result;
    }

  /**
   * getEmotionsScore Gets the SOP Emotion Score
   * @return mixed|string
   */
    function getEmotionsScore(){
        $content = $this->getContentToSend();
        $content = utf8_encode(htmlspecialchars_decode($content, ENT_QUOTES));
        //$content = str_replace(' ', '-', $content);
        $content = preg_replace('/[^A-Za-z.,\']/', ' ',$content);
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
        $content = utf8_encode(htmlspecialchars_decode($content, ENT_QUOTES));
        //$content = str_replace(' ', '-', $content);
        $content = preg_replace('/[^A-Za-z.,\']/', ' ',$content);
        $curl_url = "http://162.246.157.115/tasha/personality";
        $content = utf8_encode(htmlspecialchars_decode($content, ENT_QUOTES));
        $content = preg_replace('/[^A-Za-z0-9\-]/', ' ', $content);
        $content = str_replace('-', ' ', $content);
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
	print_r($curl_array);
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
        $content = $this->getContentString();
        $content = str_replace(' ', '-', $content);
        $content = preg_replace('/[^A-Za-z0-9\-]/', ' ', $content);
        $content = str_replace('-', ' ', $content);
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

        return $result;
    }

  /**
   * getSyntaxErrors Gets the the sop syntax errors
   * @param bool $encode
   * @return mixed|string
   */
    function getSyntaxErrors($encode=false){
        $content = $this->getContentString();
        $content = preg_replace('/[^A-Za-z.,\']/', ' ',utf8_encode(htmlspecialchars_decode($content, ENT_QUOTES)));
        $curl_url = "http://162.246.157.115/checkDocument";
        $curl_post_fields_array = array('data'=> $content);
	$fields_string = "";
	foreach($curl_post_fields_array as $key=>$value) { 
		$fields_string .= $key.'='.$value.'&'; 
	}
	rtrim($fields_string, '&');
        $curl_header = array('Content-type: application/x-www-form-urlencoded');
        $curl_array = array(
            CURLOPT_URL => $curl_url,
            CURLOPT_HTTPHEADER => $curl_header,
            CURLOPT_POSTFIELDS =>$fields_string,
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
	if($encode){
		return $this->encode_css($result);
	}
        return $result;
    }

   function getErrorsTest($encode=false){
	$content = $this->getContentString();
	$curl_url = "http://162.246.157.115/checkDocument";
        $curl_post_fields_array = array('data'=> $content);
        $fields_string = "";
        foreach($curl_post_fields_array as $key=>$value) {
                $fields_string .= $key.'='.$value.'&';
        }
        rtrim($fields_string, '&');
        $curl_header = array('Content-type: application/x-www-form-urlencoded');
        $curl_array = array(
            CURLOPT_URL => $curl_url,
            CURLOPT_HTTPHEADER => $curl_header,
            CURLOPT_POSTFIELDS =>$fields_string,
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
        if($encode){
            echo $this->encode_css($result);
        }
	return $result;
    } 

    function getReadabilityTest(){
        $content = $this->getContentToSend();
        $content = utf8_encode(htmlspecialchars_decode($content, ENT_QUOTES));
        //$content = str_replace(' ', '-', $content);
        $content = preg_replace('/[^A-Za-z.,\']/', ' ',$content);
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
	return $ari_grade;
    }

  /**
   * updateStatistics Updates the SOP statistics
   * @return SOP
   */
    function updateStatistics(){
	$this->getSyntaxErrorCount();
	$this->getReadabilityScore();
	$this->getSentimentScore();
	$this->getEmotionsScore();
	return SOP::newFromId($this->id);
    }

  /**
   * encode_css Encodes CSS for SOP
   * @param $string
   * @return string
   */
    private function encode_css($string) {
	$quoted = rawurlencode($string);
	$out = "";
	for ($i = 0, $n = 0; $i < strlen($quoted); $i += LENGTH, $n++) {
		$out .= "#c" . $n . "{background:url(" . PREFIX . substr($quoted, $i, LENGTH) . ");}\n";
	}
	return $out;
    }

    
}

?>
