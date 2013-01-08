<?php

class EvalOverviewReportItem extends AbstractReportItem {

	function render(){
	    global $wgOut;
        $details = $this->getTableHTML();
        $item = "$details";
        $item = $this->processCData($item);
		$wgOut->addHTML($item);

	}
	
	function renderForPDF(){
	    global $wgOut;
        $details = $this->getTableHTML();
        $item = "$details";
        $item = $this->processCData($item);
		$wgOut->addHTML($item);
	}
	
	function getTableHTML(){
	    $person = Person::newFromId($this->personId);
	    $subs = $person->getEvaluatePNIs();
	    $radio_questions = array(EVL_EXCELLENCE, EVL_HQPDEVELOPMENT, EVL_NETWORKING, EVL_KNOWLEDGE, EVL_MANAGEMENT, EVL_REPORTQUALITY, EVL_OVERALLSCORE, EVL_CONFIDENCE);
        $stock_comments = array(EVL_EXCELLENCE_COM, EVL_HQPDEVELOPMENT_COM, EVL_NETWORKING_COM, EVL_KNOWLEDGE_COM, EVL_MANAGEMENT_COM, EVL_REPORTQUALITY_COM);
	    $text_question = EVL_OTHERCOMMENTS;
	    $rating_map = array("Exceptional"=>'E', "Good"=>'G', "Satisfactory"=>'S', "Unsatisfactory"=>'U');
        $html = "<script type='text/javascript'>$('span.q8_tip').qtip();</script>";
        $html .= '<table class="dashboard" style="width:100%;background:#ffffff;border-style:solid;" cellspacing="1" cellpadding="3" frame="box" rules="all">';
       

        $html .=<<<EOF
        	<tr>
        	<th width="20%">NI Name</th>
            <th>Q8</th>
        	<th>Q1</th>
        	<th>Q2</th>
        	<th>Q3</th>
        	<th>Q4</th>
        	<th>Q5</th>
        	<th>Q6</th>
        	<th>Q9</th>
        	<th>Q10</th>
        	</tr>
EOF;

        foreach($subs as $sub){
            $sub_id = $sub->getId();
            $sub_name = $sub->getReversedName();
            $evals = $sub->getEvaluators('PNI');
            
            foreach($evals as $ev){
            	$ev_id = $ev->getId();
            	$ev_name = $ev->getReversedName();
            	$html .= "<tr>";
            	$html .= "<td><b>{$ev_name}</b><br /><i>{$sub_name}</i></td>";
                $q8 = $this->blobValue($ev_id, $text_question, $sub_id);
                if(is_string($q8) && strlen($q8) > 25){
                    $q8_short = substr($q8, 0, 25) . "...";
                }
                else{
                    $q8_short = $q8;
                }
                $q8 = htmlentities($q8, ENT_QUOTES);
                if(!empty($q8)){
                    $html .= "<td><span class='q8_tip' title='{$q8}'><a>See Comment</a></span></td>";
            	}
                else{
                    $html .= "<td>No Comment</td>";
                }
                $i = 0;   
                foreach ($radio_questions as $blobItem){
            		$comm = "";
                    $comm_short = "";
                    if($i < 6){
                        $comm = $this->blobValue($ev_id, $stock_comments[$i], $sub_id);
                        if($comm){
                            $comm_short = substr($comm, 0, 3);
                        }
                    }
                    $response_orig = $response = $this->blobValue($ev_id, $blobItem, $sub_id);
            		if($response_orig){
            			$response = $rating_map[$response];
                        if($comm){
                            $response .= " | ".$comm_short;
                        } 
            		    $html .= "<td><span class='q8_tip' title='{$response_orig}<br />{$comm}'><a>{$response}</a></span></td>";
                    }else{
            			$response = "N/A";
                        $html .= "<td>{$response}</td>";
            		}

            		
                    $i++;
            	
                }

            	$html .= "</tr>";
        	}
        }
        $html .= "</table>";
        

        return $html;
	}

	function blobValue($evaluator_id, $blobItem, $blobSubItem){
		$blob = new ReportBlob(BLOB_TEXT, $this->getReport()->year, $evaluator_id, $this->projectId);
	    $blob_address = ReportBlob::create_address($this->getReport()->reportType, SEC_NONE, $blobItem, $blobSubItem);
		$blob->load($blob_address);
	    $blob_data = $blob->getData();
	    return $blob_data;
	}
}
?>
