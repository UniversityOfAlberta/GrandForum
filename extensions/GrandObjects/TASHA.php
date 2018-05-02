<?php

/**
 * @package GrandObjects
 */
class TASHA {
    
    var $content; 
  
   /**
    * sends content to TASHA server and returns result
    * @return result|array of curl http result
    */
    function sendContent($url_extension){
        global $config;
        $tasha_url = $config->getValue('tashaUrl');
        $tasha_user = $config->getValue('tashaUser');
        $tasha_password = $config->getValue('tashaPassword');

        $curl_url = "{$tasha_url}/{$url_extension}";
        $curl_post_fields_array = array('content'=>$this->getContentToSend());
        $curl_post_fields = json_encode($curl_post_fields_array);
        $curl_header = array('Content-Type: application/json');
        $curl_array = array(
            CURLOPT_URL => $curl_url,
            CURLOPT_HTTPHEADER => $curl_header,
            CURLOPT_POSTFIELDS =>$curl_post_fields,
            CURLOPT_RETURNTRANSFER =>true,
            CURLOPT_POST => true,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => "{$tasha_user}:{$tasha_password}"
        );
        $curl = curl_init();
        curl_setopt_array($curl, $curl_array);
        $data = curl_exec($curl);
        $result = '';
        if ($error = curl_error($curl)){
            $result = $error;
        }
        if(empty($error)){
            $result = $data;
        }
        curl_close($curl);
        $result = json_decode($result, true);
        return $result;
    }

   /**
    * returns content of TASHA object
    * @return $string string of content
    */
    function getContentToSend(){
        $content = $this->content;
        $string = utf8_encode(htmlspecialchars_decode($content, ENT_QUOTES));
        $string = preg_replace('/[^A-Za-z.,\']/', ' ',$string);
        return $string;
    }

  /**
   * getReadabilityScore Calls TASHA and returns readablility score for the content
   * @return mixed|array
   */
    function getReadabilityScore(){
        $curl_extension = "readability_score";
        $result = $this->sendContent($curl_extension);
        if(@$result["status"] == "ERROR"){
            return $result;
        }

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
        $reading_ease = $result["flesch"]["reading_ease"];
        $word_count = $result["flesch"]["scores"]["word_count"];
        $sentlen_ave = $result["dalechall"]["scores"]["sentlen_average"];
        $wordletter_ave = $result["dalechall"]["scores"]["wordlen_average"];

        $readability_score
            = ($ari_grade + $colemanliau_grade + $dalechall_grade +
               $fleschkincaid_grade + $smog_grade)
              / 5;
        $min_age
            = ($ari_age + $colemanliau_age + $dalechall_age +
               $fleschkincaid_age + $smog_age)
              / 5;

        $result_array = array("readability_score" => $readability_score, 
                              "min_age" => $min_age, 
                              "reading_ease" => $reading_ease, 
                              "ari_grade" => $ari_grade,
                              "ari_age" => $ari_age,
                              "colemanliau_grade" => $colemanliau_grade,
                              "colemanliau_age" => $colemanliau_age, 
                              "dalechall_index" => $dalechall_index,
                              "dalechall_grade" => $dalechall_grade, 
                              "dalechall_age" => $dalechall_age,
                              "fleschkincaid_grade" => $fleschkincaid_grade,
                              "fleschkincaid_age" => $fleschkincaid_grade,
                              "smog_grade" => $smog_grade,  
                              "smog_age" => $smog_age,
                              "word_count" => $word_count, 
                              "sentlen_ave" => $sentlen_ave,
                              "wordletter_ave" => $wordletter_ave);

        return $result_array;
    }

  /**
   * getSentimentScore gets the sentiment score
   * @return mixed|array
   */
    function getSentimentScore(){
        $curl_extension = "sentiment";
        $result = $this->sendContent($curl_extension);
        if(@$result["status"] == "ERROR"){
            return $result;
        }
        $sentiment_val = $result['docSentiment']['score'];
        $sentiment_type = $result['docSentiment']['type'];
        $result_array = array("sentiment_type" => $sentiment_type,
                              "sentiment_val" => $sentiment_val);
        return $result_array;
    }

  /**
   * getEmotionsScore Gets the TASHA Emotion Score
   * @return mixed|array
   */
    function getEmotionsScore(){
        $curl_extension = "emotions";
        $result = $this->sendContent($curl_extension);
        if(@$result["status"] == "ERROR"){
            return $result;
        }
        $result_array = array();
        $emotions_array['anger'] = $result['docEmotions']['anger'];
        $emotions_array['disgust'] = $result['docEmotions']['disgust'];
        $emotions_array['fear'] = $result['docEmotions']['fear'];
        $emotions_array['joy'] = $result['docEmotions']['joy'];
        $emotions_array['sadness'] = $result['docEmotions']['sadness'];

        return $result_array;
    }

    /**
    * getEmotionsScore Gets the TASHA personality score
    * @return mixed|string
    */
    function getPersonalityScore(){
        $curl_extension = "personality";
        $result = $this->sendContent($curl_extension);
        return $result;
    }


    /**
    * getSyntaxErrorCount Gets the content syntax error count
    * @return mixed|string
    */
    function getSyntaxErrorCount(){
        $curl_extension = "syntac_error";
        $result = $this->sendContent($curl_extension);
        if(@$result["status"] == "ERROR"){
            return $result;
        }
        $errors = $result['errors']; 
        return $errors; 
    }

    /**
     * runAll Runs all TASHA analsys statistics
     */
    function runAll(){
      $this->getReadabilityScore();
      $this->getSentimentScore();
      $this->getEmotionsScore();
      $this->getPersonalityScore();
    }

}

?>
