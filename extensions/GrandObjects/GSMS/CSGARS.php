<?php

class GsmsData extends AbstractGsmsData {

    var $questions;
    
    function getContent($asString=false){
        if($this->questions == null){
            $qs = array('Q1');
            $qstrings = array("(Applicant's Statement of Purpose)");

            $questions = array();
            $blob = new ReportBlob(BLOB_TEXT, REPORTING_YEAR, $this->user_id, 0);
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
	    if($asString){
             $string = "";
             foreach($this->questions as $question => $answer){
                $answer = str_replace("\r", "", $answer);
                $answer = str_replace("\00", "", $answer);
                //$length = strlen(utf8_decode($answer));
                //$lengthDiff = strlen($answer) - $length;
                $string = $string."<b>". $question."</b>"."<br /><br />".nl2br(mb_substr($answer, 0, 4500))."<br /><br />";
             }
             return $string;
	    }
        return $this->questions;
    }   
    
}

?>
