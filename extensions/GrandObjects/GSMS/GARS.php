<?php

class GsmsData extends AbstractGsmsData {
    
    var $questions;
    var $content;
    
    function checkGSMS(){
        $url = $this->getGSMSUrl();
        if($url != ""){
            return true;
        }
        return false;
    }

    function getGSMSUrl(){
        $data = DBFunctions::select(array('grand_gsms'),
                                    array('pdf_contents'),
                                    array('user_id' => EQ($this->user_id)));
        if(count($data) > 0){
            $pdf_contents = $data[0]['pdf_contents'];
            if($pdf_contents != ""){
                global $wgServer, $wgScriptPath;
                return "{$wgServer}{$wgScriptPath}/index.php?action=api.getUserPdf&last=true&user=".$this->user_id;
            }

        }
        return "";
    }

    function checkSOP(){
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
                                          'type' => 'RPTP_OT',
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
                                    array('*'),
                                    array('user_id' => EQ($this->user_id),
                                          'type' => 'RPTP_OT',
                                          'year' => YEAR));

        if(count($data) > 0){
            $pdf = $data[0]['pdf'];
            if($pdf != ""){
                global $wgServer, $wgScriptPath;
                return "{$wgServer}{$wgScriptPath}/index.php?action=api.getSopPdf&last=true&user=".$this->user_id;
            }
        }
        return false;
    }
    
    function getContent($asString=false){
        if($this->questions == null){
            $qs = array('Q1','Q2','Q4','Q5');
            $qstrings = array('(Describe how your personal work and volunteer experiences will contribute towards making you an effective occupational therapist.)',
                              '(Tell us about your academic experiences and how they have prepared you for being successful in the MScOT program at the University of Alberta.)',
                              '(Why are you choosing to apply to the Department of Occupational Therapy at the University of Alberta?)',
                              '(Is there anything else in terms of extenuating circumstances that you would like inform the Admissions Committee in making their decision?  An example of an extenuating circumstance would be (e.g. gaps in program, leave of absences from previous program). Note: if there are no extenuating circumstances to speak of, please leave this answer blank.)');

            $questions = array();
            $qnumber = 0;
            $qnum = 1; #In report XML The numbers skip and this causes an issue so this is an added variable...fix this in future.
            foreach($qs as $q){
                $sql = "SELECT data
                    FROM grand_report_blobs
                    WHERE rp_section = 'OT_QUESTIONS'
                        AND rp_item = '$q'
                        AND proj_id =0
                        AND year =".YEAR."
                        AND user_id = {$this->user_id}";

                $data = DBFunctions::execSQL($sql);
                if(count($data)>0){
                    $questions["Q$qnum".' '.$qstrings[$qnumber]] = $data[0]['data'];
                }
                else{
                    $questions["Q$qnum".' '.$qstrings[$qnumber]] = "";
                }
                $qnumber++;
                $qnum++;

            }
            $this->questions = $questions;
        }

        if($asString){ //if want to return for html purposes
            $string = "";
            foreach($this->questions as $question => $answer){
                $string = $string."<b>". $question."</b>"."<br /><br />".$answer."<br /><br />";
            }
            return $string;
        }
        return $this->questions;
    }
    
    function getExtraColumns(){
        return array();
    }
    
    function getEducationalHistory($html_string=false){
        if($html_string){
           return "";
        }
        return array();
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
                        AND data != ''
                        AND rp_item = 'Q13'
                        AND proj_id =".$this->getId();

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
        $blob_address = ReportBlob::create_address('RP_OTT', 'OT_REVIEW', 'Q13', $this->getId());
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
    
    function getReviewComments($user){
        $comments = array();
        $blob = new ReportBlob(BLOB_TEXT, REPORTING_YEAR, $user, $this->getId());
        $blob_address = ReportBlob::create_address('RP_OTT', 'OT_REVIEW', 'Q9', $this->getId());
        $blob->load($blob_address);
        $comments['documents'] = $blob->getData();
        $blob_address = ReportBlob::create_address('RP_OTT', 'OT_REVIEW', 'Q112_comments', $this->getId());
        $blob->load($blob_address);
        $comments['special_consideration'] = $blob->getData();
        $blob_address = ReportBlob::create_address('RP_OTT', 'OT_REVIEW', 'Q12', $this->getId());
        $blob->load($blob_address);
        $comments['recommendation'] = $blob->getData();
        return $comments;
   }
   
   function getReviewRanking($user){
        return "--";
   }
   
   function getHiddenStatus($user){
        $year = ($this->year != "") ? $this->year : YEAR;
        $hqp = Person::newFromId($this->user_id);
        $uninteresting = $this->getBlobValue(BLOB_ARRAY, $year, "RP_OTT", "OT_REVIEW", "CS_Review_Uninteresting", $user, $this->id);
        return isset($uninteresting['q0'][1]);
    }
    
    function setHiddenStatus($user, $value=""){
        $year = ($this->year != "") ? $this->year : YEAR;
        $hqp = Person::newFromId($this->user_id);
        $uninteresting = $this->getBlobValue(BLOB_ARRAY, $year, "RP_OTT", "OT_REVIEW", "CS_Review_Uninteresting", $user, $this->id);
        
        $blb = new ReportBlob(BLOB_ARRAY, $year, $user, $this->id);
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
    
}

?>
