<?php

/**
 * @package GrandObjects
 */
class SOP extends AbstractSop{

    function getColumns() {return array();}

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
                        AND year = '{$year}'
                        AND rp_item = 'Q13'
                        AND proj_id =".$gsms->getId();

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
   * getContent Gets the SOP Content
   * @return array with answers to questions
   */
    function getContent($asString=false){
        $year = ($this->year != "") ? $this->year : YEAR;
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
                        AND year = ".$year."
                        AND user_id = {$this->getUser()}";

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
            $this->content = $this->questions;


            if($asString){ //if want to return for html purposes
             $string = "";
             foreach($this->content as $question => $answer){
                $string = $string."<b>". $question."</b>"."<br /><br />".$answer."<br /><br />";
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
                    return "{$wgServer}{$wgScriptPath}/index.php?action=api.getUserPdf&last=true&year={$this->year}&user=".$this->user_id;
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
        $year = ($this->year != "") ? $this->year : YEAR;
        $data = DBFunctions::select(array('grand_pdf_report'),
                                    array('pdf'),
                                    array('user_id' => EQ($this->user_id),
                                          'type' => 'RPTP_OT',
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
        $year = ($this->year != "") ? $this->year : YEAR;
        $data = DBFunctions::select(array('grand_pdf_report'),
                                    array('*'),
                                    array('user_id' => EQ($this->user_id),
                                          'type' => 'RPTP_OT',
                                          'year' => $year));

        if(count($data) > 0){
            $pdf_data = $data[0]['pdf'];
            if($pdf_data != ""){
                global $wgServer, $wgScriptPath;
                return "{$wgServer}{$wgScriptPath}/index.php?action=api.getSopPdf&year={$year}&last=true&user=".$this->user_id;
            }
        }
        return false;
    }
   /**
    * returns string if SOP was suggested to be admitted or not by the user specified in argument.
    * @return $string either 'Admit', 'Not Admit' or 'Undecided' based on answer of PDF report.
    */
    function getAdmitResult($user){
        $year = ($this->year != "") ? $this->year : YEAR;
        $hqp = Person::newFromId($this->user_id);
        $gsms = $hqp->getGSMS($this->year);
        $blob = new ReportBlob(BLOB_TEXT, $year, $user, $gsms->getId());
        $blob_address = ReportBlob::create_address('RP_OTT', 'OT_REVIEW', 'Q13', $gsms->getId());
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

    function getWantToSupervise($user){
        return false;
    }

    function getWillingToSupervise($user){
        return false;
    }

    function getReviewComments($user){
        $year = ($this->year != "") ? $this->year : YEAR;
        $comments = array();
        $hqp = Person::newFromId($this->user_id);
        $gsms = $hqp->getGSMS($this->year);
        $blob = new ReportBlob(BLOB_TEXT, $year, $user, $gsms->getId());
        $blob_address = ReportBlob::create_address('RP_OTT', 'OT_REVIEW', 'Q9', $gsms->getId());
        $blob->load($blob_address);
        $comments['documents'] = $blob->getData();
        $blob_address = ReportBlob::create_address('RP_OTT', 'OT_REVIEW', 'Q112_comments', $gsms->getId());
        $blob->load($blob_address);
        $comments['special_consideration'] = $blob->getData();
        $blob_address = ReportBlob::create_address('RP_OTT', 'OT_REVIEW', 'Q12', $gsms->getId());
        $blob->load($blob_address);
        $comments['recommendation'] = $blob->getData();
        return $comments;
   }

}

?>
