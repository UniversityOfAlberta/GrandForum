<?php

class GsmsData extends AbstractGsmsData {
    
    var $questions;
    var $content;
    
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
    
}

?>
