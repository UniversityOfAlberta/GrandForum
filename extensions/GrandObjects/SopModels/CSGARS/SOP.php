<?php
mb_internal_encoding("UTF-8");

/**
 * @package GrandObjects
 */
class SOP extends AbstractSop{

    static $hasGsmsCache = array();

    function getColumns() {
        $year = ($this->year != "") ? $this->year : YEAR;
        $moreJson = array();
        $AoS = $this->getBlobValue(BLOB_ARRAY, $year, "RP_CS", "CS_QUESTIONS_tab1", "Q13");
        $moreJson['areas_of_study'] = @implode(", ", $AoS['q13']);
        //var_dump($moreJson['areas_of_study']);

        $blob = $this->getBlobValue(BLOB_ARRAY, $year, "RP_CS", "CS_QUESTIONS_tab1", "Q14");
        #$moreJson['supervisors'] = @implode(";\n", explode(" ", $blob['q14'])[1]);
        $supervisors = "";
        if (isset($blob['q14'])) {
          foreach ($blob['q14'] as $el) {
            $sup_array = explode(",", $el);
            foreach($sup_array as $sup){
                $sup = explode(" ", $sup);
                $supervisors[] = array("first" => @$sup[0], 
                                       "last"  => @$sup[1]);
            }
          }
        }
        $moreJson['supervisors'] = $supervisors;

        $blob = $this->getBlobValue(BLOB_ARRAY, $year, "RP_CS", "CS_QUESTIONS_tab1", "Q16");
        $moreJson['scholarships_held'] = @implode(", ", $blob['q16']);

        $blob = $this->getBlobValue(BLOB_ARRAY, $year, "RP_CS", "CS_QUESTIONS_tab1", "Q15");
        $moreJson['scholarships_applied'] = @implode(", ", $blob['q15']);

        $moreJson['gpaNormalized'] = $this->getBlobValue(BLOB_TEXT, $year, "RP_CS", "CS_QUESTIONS_tab1", "Q21");
        $moreJson['gpaManual'] = $this->getBlobValue(BLOB_TEXT, $year, "RP_CS", "CS_QUESTIONS_tab1", "Q21_MANUAL");
        $moreJson['gre1'] = $this->getBlobValue(BLOB_TEXT, $year, "RP_CS", "CS_QUESTIONS_tab1", "Q24");
        $moreJson['gre2'] = $this->getBlobValue(BLOB_TEXT, $year, "RP_CS", "CS_QUESTIONS_tab1", "Q25");
        $moreJson['gre3'] = $this->getBlobValue(BLOB_TEXT, $year, "RP_CS", "CS_QUESTIONS_tab1", "Q26");
        $moreJson['gre4'] = $this->getBlobValue(BLOB_TEXT, $year, "RP_CS", "CS_QUESTIONS_tab1", "Q27");

        // Immigration status
        $blob = $this->getBlobValue(BLOB_ARRAY, $year, "RP_CS", "CS_QUESTIONS_tab1", "qImmigrationStatus");
        $moreJson['immigration'] = $blob;

        // # of Publications
        $blob = $this->getBlobValue(BLOB_ARRAY, $year, "RP_CS", "CS_QUESTIONS_tab3", "qPublications");
        $moreJson['num_publications'] = @count($blob['qResExp2']);

        // # of awards
        $blob = $this->getBlobValue(BLOB_ARRAY, $year, "RP_CS", "CS_QUESTIONS_tab4", "qAwards");
        $moreJson['num_awards'] = @count($blob['qAwards']);

        // Courses (number of courses, number of areas)
        $blob = $this->getBlobValue(BLOB_ARRAY, $year, "RP_CS", "CS_QUESTIONS_tab6", "qCourses");
        $courses = array();
        //var_dump($blob);
        //exit;
        if (isset($blob['qEducation2'])) {
          foreach ($blob['qEducation2'] as $el) {
            $courses[] = $el['course'];
          }
        }
        $moreJson['courses'] = @implode(", ", $courses);
        //$moreJson['courses'] = @implode(", ", $blob['qEducation2'][0]);

        $moreJson['country_of_citizenship_full'] = $this->getBlobValue(BLOB_TEXT, $year, "RP_CS", "CS_QUESTIONS_tab1", "qCountry");
        $moreJson['country_of_degree'] = $this->getBlobValue(BLOB_TEXT, $year, "RP_CS", "CS_QUESTIONS_tab1", "qCountry1");
        $moreJson['current_country'] = $this->getBlobValue(BLOB_TEXT, $year, "RP_CS", "CS_QUESTIONS_tab1", "qCountry2");
        $moreJson['edi'] = $this->getBlobValue(BLOB_TEXT, $year, "RP_CS", "EDI", "EDI");

        $pdf = $this->getPdf(true);
        $moreJson['education'] = (isset($pdf['Education'])) ? $pdf['Education'] : array();
        $moreJson['referees'] = (isset($pdf['Referees'])) ? $pdf['Referees'] : array();

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
                $answer = str_replace("\r", "", $answer);
                $answer = str_replace("\00", "", $answer);
                //$length = strlen(utf8_decode($answer));
                //$lengthDiff = strlen($answer) - $length;
                $string = $string."<b>". $question."</b>"."<br /><br />".nl2br(mb_substr($answer, 0, 4500))."<br /><br />";
             }
             return $string;
	    }
        return $this->content;
    }
    
    static function generateHasGSMSCache($year=""){
        $dbyear = ($year != "" && $year != YEAR) ? "_$year" : "";
        if(count(@self::$hasGsmsCache[$year]) == 0){
            $data = DBFunctions::execSQL("SELECT DISTINCT user_id
                                          FROM grand_sop$dbyear
                                          WHERE pdf_data != ''");
            foreach($data as $row){
                self::$hasGsmsCache[$year][$row['user_id']] = true;
            }
        }
    }

    function checkGSMS(){
        $url = $this->getGSMSUrl();
        if($url != ""){
            return true;
        }
        return false;
    }

    function getGSMSUrl(){
        global $wgServer, $wgScriptPath;
        self::generateHasGSMSCache($this->year);
        if(isset(self::$hasGsmsCache[$this->year][$this->user_id])){
            return "{$wgServer}{$wgScriptPath}/index.php?action=api.getUserPdf&last=true&year={$this->year}&user={$this->user_id}";
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
        $year = ($this->year != "") ? $this->year : YEAR;
        $data = DBFunctions::select(array('grand_pdf_report'),
                                    array('pdf'),
                                    array('user_id' => EQ($this->user_id),
                                          'type' => 'RPTP_CS_FULL',
                                          'year' => $year));
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
        global $wgServer, $wgScriptPath;
        $year = ($this->year != "") ? $this->year : YEAR;
        $data = DBFunctions::select(array('grand_pdf_report'),
                                    array('report_id'),
                                    array('user_id' => EQ($this->user_id),
                                          'type' => 'RPTP_CS_FULL',
                                          'year' => $year));
        if(count($data) > 0){
            return "{$wgServer}{$wgScriptPath}/index.php?action=api.getSopPdf&year={$year}&last=true&user={$this->user_id}";
        }
        return "";
    }

   /**
    * returns an array of the faculty staff that have finished reviewing this SOP.
    * this checks only if the last question was answered which is 'admit or not admit?'
    * @return $reviewers array of the id of reviewers who have finished reviewing SOP.
    */
    function getReviewers(){
        $year = ($this->year != "") ? $this->year : YEAR;
        $hqp = Person::newFromId($this->user_id);
        $gsms = $hqp->getGSMS($this->year);
        $sql = "SELECT DISTINCT(user_id), data
                FROM grand_report_blobs
                WHERE rp_section = 'OT_REVIEW'
                        AND data != ''
                        AND year = $year
                        AND proj_id = {$gsms->id}";
        $data = DBFunctions::execSQL($sql);
        $reviewers = array();
        if(count($data)>0){
            foreach($data as $user){
                if($user['data'] != ''){
                    $reviewers[$user['user_id']] = $user['user_id'];
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
        $year = ($this->year != "") ? $this->year : YEAR;
        $hqp = Person::newFromId($this->user_id);
        $gsms = $hqp->getGSMS($this->year);
        $blob = $this->getBlobValue(BLOB_TEXT, $year, "RP_OTT", "OT_REVIEW", "CS_Review_Rank", $user, $gsms->id);
        if($blob == ''){
            return '--';
        }
        return $blob;
    }
    
    function getWantToSupervise($user){
        return $this->getAgreeToSupervise($user);
        
        $year = ($this->year != "") ? $this->year : YEAR;
        $hqp = Person::newFromId($this->user_id);
        $gsms = $hqp->getGSMS($this->year);
        $blob = $this->getBlobValue(BLOB_TEXT, $year, "RP_OTT", "OT_REVIEW", "CS_Review_SuperviseWant", $user, $gsms->id);
        return ($blob == "Yes");
    }
    
    function getWillingToSupervise($user){
        return $this->getAgreeToSupervise($user);
        
        $year = ($this->year != "") ? $this->year : YEAR;
        $hqp = Person::newFromId($this->user_id);
        $gsms = $hqp->getGSMS($this->year);
        $blob = $this->getBlobValue(BLOB_TEXT, $year, "RP_OTT", "OT_REVIEW", "CS_Review_Supervise", $user, $gsms->id);
        return ($blob == "Yes");
    }
    
    function getAgreeToSupervise($user){
        $year = ($this->year != "") ? $this->year : YEAR;
        $hqp = Person::newFromId($this->user_id);
        $gsms = $hqp->getGSMS($this->year);
        $blob = $this->getBlobValue(BLOB_ARRAY, $year, "RP_OTT", "OT_REVIEW", "CS_Review_SuperviseSalary", $user, $gsms->id);
        return (count(@$blob["q7"]) > 0);
    }

    function getReviewRanking($user) {
        $year = ($this->year != "") ? $this->year : YEAR;
        $hqp = Person::newFromId($this->user_id);
        $gsms = $hqp->getGSMS($this->year);
        $blob = $this->getBlobValue(BLOB_TEXT, $year, "RP_OTT", "OT_REVIEW", "CS_Review_Rank", $user, $gsms->id);
        $uninteresting = $this->getBlobValue(BLOB_ARRAY, $year, "RP_OTT", "OT_REVIEW", "CS_Review_Uninteresting", $user, $gsms->id);
        if ($blob == '' && isset($uninteresting['q0'][1])) { 
            return "-1";
        }
        if($blob == ''){
            return '--';
        }
        return $blob;
    }
    
    function getHiddenStatus($user){
        $year = ($this->year != "") ? $this->year : YEAR;
        $hqp = Person::newFromId($this->user_id);
        $gsms = $hqp->getGSMS($this->year);
        $uninteresting = $this->getBlobValue(BLOB_ARRAY, $year, "RP_OTT", "OT_REVIEW", "CS_Review_Uninteresting", $user, $gsms->id);
        return isset($uninteresting['q0'][1]);
    }
    
    function setHiddenStatus($user, $value=""){
        $year = ($this->year != "") ? $this->year : YEAR;
        $hqp = Person::newFromId($this->user_id);
        $gsms = $hqp->getGSMS($this->year);
        
        $blb = new ReportBlob(BLOB_ARRAY, $year, $user, $gsms->id);
        $addr = ReportBlob::create_address("RP_OTT", "OT_REVIEW", "CS_Review_Uninteresting", 0);
        if($value != ""){
            $value = array('q0' => array(1 => $value));
            $result = $blb->store($value, $addr);
        }
        else{
            $value = array('q0' => array());
            $result = $blb->store($value, $addr);
        }
    }
    
    function getFavoritedStatus($user){
        $year = ($this->year != "") ? $this->year : YEAR;
        $hqp = Person::newFromId($this->user_id);
        $gsms = $hqp->getGSMS($this->year);
        $favorite = $this->getBlobValue(BLOB_ARRAY, $year, "RP_OTT", "OT_REVIEW", "CS_Review_Favorited", $user, $gsms->id);
        return isset($favorite['q0'][1]);
    }
    
    function setFavoritedStatus($user, $value=""){
        $year = ($this->year != "") ? $this->year : YEAR;
        $hqp = Person::newFromId($this->user_id);
        $gsms = $hqp->getGSMS($this->year);
        
        $blb = new ReportBlob(BLOB_ARRAY, $year, $user, $gsms->id);
        $addr = ReportBlob::create_address("RP_OTT", "OT_REVIEW", "CS_Review_Favorited", 0);
        if($value != ""){
            $value = array('q0' => array(1 => $value));
            $result = $blb->store($value, $addr);
        }
        else{
            $value = array('q0' => array());
            $result = $blb->store($value, $addr);
        }
    }

    function getCSEducationalHistory($html_string=false){
        $year = ($this->year != "") ? $this->year : YEAR;
        $blob = $this->getBlobValue(BLOB_ARRAY, $year, "RP_CS", "CS_QUESTIONS_tab6", "qDegrees");
        $degrees = $blob['qEducation1'];
        if($html_string){
           if(count($degrees) >0){
               $html_array = array();
               foreach($degrees as $degree){
                   $html_array[] = "<b>{$degree['degree']}</b> ({$degree['university']})";
               }
               return implode("<br /><br />", $html_array);
           }
           return "";
        }
        return $degrees;
    }
    
    function getReviewComments($user){
        $year = ($this->year != "") ? $this->year : YEAR;
        $hqp = Person::newFromId($this->user_id);
        $gsms = $hqp->getGSMS($this->year);
        $comments = $this->getBlobValue(BLOB_TEXT, $year, "RP_OTT", "OT_REVIEW", "CS_Review_RankExplain", $user, $gsms->id);
        if($comments != null){
            return $comments;
        }
        return "";
   }
}

?>
