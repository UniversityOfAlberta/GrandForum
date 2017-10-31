<?php

/**
 * @package GrandObjects
 */
class SOP extends AbstractSop{
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
                                    array('user_id' => EQ($this->user_id()),
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
}

?>
