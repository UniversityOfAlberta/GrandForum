<?php
mb_internal_encoding("UTF-8");

/**
 * @package GrandObjects
 */
class SOP extends AbstractSop{


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


    function getCSColumns() {
        $moreJson = array();
        $AoS = $this->getBlobValue(BLOB_ARRAY, YEAR, "RP_CS", "CS_QUESTIONS_tab1", "Q13");
        $moreJson['areas_of_study'] = @implode(", ", $AoS['q13']);
        //var_dump($moreJson['areas_of_study']);

        $blob = $this->getBlobValue(BLOB_ARRAY, 0, "RP_CS", "CS_QUESTIONS_tab1", "Q14");
        
        $moreJson['supervisors'] = @implode(", ", array($blob['q14']));

        $blob = $this->getBlobValue(BLOB_ARRAY, 0, "RP_CS", "CS_QUESTIONS_tab1", "Q16");
        $moreJson['scholarships_held'] = @implode(", ", array($blob['q16']));

        $blob = $this->getBlobValue(BLOB_ARRAY, 0, "RP_CS", "CS_QUESTIONS_tab1", "Q15");
        $moreJson['scholarships_applied'] = @implode(", ", array($blob['q15']));

        $moreJson['gpaNormalized'] = $this->getBlobValue(BLOB_TEXT, 0, "RP_CS", "CS_QUESTIONS_tab1", "Q21");
        $moreJson['gre1'] = $this->getBlobValue(BLOB_TEXT, 0, "RP_CS", "CS_QUESTIONS_tab1", "Q24");
        $moreJson['gre2'] = $this->getBlobValue(BLOB_TEXT, 0, "RP_CS", "CS_QUESTIONS_tab1", "Q25");
        $moreJson['gre3'] = $this->getBlobValue(BLOB_TEXT, 0, "RP_CS", "CS_QUESTIONS_tab1", "Q26");
        $moreJson['gre4'] = $this->getBlobValue(BLOB_TEXT, 0, "RP_CS", "CS_QUESTIONS_tab1", "Q27");

        // # of Publications
        $blob = $this->getBlobValue(BLOB_ARRAY, 0, "RP_CS", "CS_QUESTIONS_tab3", "qPublications");
        $moreJson['num_publications'] = @count($blob['qResExp2']);

        // # of awards
        $blob = $this->getBlobValue(BLOB_ARRAY, 0, "RP_CS", "CS_QUESTIONS_tab4", "qAwards");
        $moreJson['num_awards'] = @count($blob['qAwards']);

        // Courses (number of courses, number of areas)
        $blob = $this->getBlobValue(BLOB_ARRAY, 0, "RP_CS", "CS_QUESTIONS_tab6", "qCourses");
        $moreJson['courses'] = @implode(", ", array($blob['qEducation2']));

        return $moreJson;

    }

    function getContent($asString=false){
        if($this->questions == null){
	    $qs = array('Q1');
            $qstrings = array("(Applicant's Statement of Purpose)");

            $questions = array();
            $blob = new ReportBlob(BLOB_TEXT, REPORTING_YEAR, $this->getUser(), 0);
	    $qnumber = 0;
            foreach($qs as $q){
                $blob_address = ReportBlob::create_address('RP_CS', 'CS_QUESTIONS_tab2', 'QSOP', 0);
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
                //$answer = str_replace("\r", "", $answer);
                //$answer = str_replace("\00", "", $answer);\
                $length = strlen(utf8_decode($answer));
                //$lengthDiff = strlen($answer) - $length;
                $string = $string."<b>". $question."</b>"."<br /><br />".nl2br(substr($answer, 0, $length))."<br /><br />";
             }
             return $string;
	    }
        return $this->content;
    }

    function checkGSMS(){
	$url = $this->getGSMSUrl();
	if($url != ""){
	    return true;
	}
	return false;
    }

    function getGSMSUrl(){
            $data = DBFunctions::select(array('grand_sop'),
                                    array('pdf_contents', 'pdf_data'),
                                    array('user_id' => EQ($this->user_id)));
            if(count($data) > 0){
                $pdf_data = $data[0]['pdf_data'];
                if($pdf_data != ""){
                    global $wgServer, $wgScriptPath;
                    return "{$wgServer}{$wgScriptPath}/index.php?action=api.getUserPdf&last=true&user=".$this->user_id;
                }

            }
	return "";
    }

    function checkSOP(){
        $person = Person::newFromId($this->user_id);
        $url = $this->getSopUrl();
        if($url != ""){
            return true;
        }
        return false;
    }

    /**
     * Returns PDF stream of Statement of Purpose pdf 
     * @return text stream of SoP PDF
   **/
    function getSopPdf(){
        $data = DBFunctions::select(array('grand_pdf_report'),
                                    array('pdf'),
                                    array('user_id' => EQ($this->user_id),
                                          'type' => 'RPTP_CS_FULL',
                                          'year' => YEAR));
        if(count($data) > 0){
            return $data[0]['pdf'];
        }
        return false;
    }

    /**
     * Returns url of Statement of Purpose pdf 
     * @return String url of SoP pdf
   **/
    function getSopUrl(){
        $data = DBFunctions::select(array('grand_pdf_report'),
                                    array('pdf'),
                                    array('user_id' => EQ($this->user_id),
                                          'type' => 'RPTP_CS_FULL',
                                          'year' => YEAR));

        if(count($data) > 0){
            $pdf_data = $data[0]['pdf'];
            if($pdf_data != ""){
                global $wgServer, $wgScriptPath;
                return "{$wgServer}{$wgScriptPath}/index.php?action=api.getSopPdf&last=true&user=".$this->user_id;
            }
        }
        return "";
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
                        AND rp_item = 'CS_Review_Supervise'
                        AND proj_id =".$this->id;
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
        $sql = "SELECT data
                FROM grand_report_blobs
                WHERE rp_section = 'OT_REVIEW'
                        AND rp_item = 'CS_Review_Rank'
                        AND user_id = '$user'
                        AND proj_id =".$this->id;
        $data = DBFunctions::execSQL($sql);
        if(count($data) > 0){
            return $data[0]['data'];
        }
       return '--';
    }
}

?>
