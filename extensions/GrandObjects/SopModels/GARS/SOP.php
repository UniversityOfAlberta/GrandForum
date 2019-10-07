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
        $hqp = Person::newFromId($this->user_id);
        $gsms = $hqp->getGSMS();
        $sql = "SELECT DISTINCT(user_id), data
                FROM grand_report_blobs
                WHERE rp_section = 'OT_REVIEW'
                        AND data != ''
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

    function checkGSMS(){
        $url = $this->getGSMSUrl();
        if($url != ""){
            return true;
        }
        return false;
    }

    function getGSMSUrl(){
            $data = DBFunctions::select(array('grand_sop'),
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
   /**
    * returns string if SOP was suggested to be admitted or not by the user specified in argument.
    * @return $string either 'Admit', 'Not Admit' or 'Undecided' based on answer of PDF report.
    */
    function getAdmitResult($user){
        $hqp = Person::newFromId($this->user_id);
        $gsms = $hqp->getGSMS();
        $blob = new ReportBlob(BLOB_TEXT, REPORTING_YEAR, $user, $gsms->getId());
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


    function getReviewComments($user){
        $comments = array();
        $hqp = Person::newFromId($this->user_id);
        $gsms = $hqp->getGSMS();
        $blob = new ReportBlob(BLOB_TEXT, REPORTING_YEAR, $user, $gsms->getId());
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
