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
        global $wgUser;
	    $person = Person::newFromId($this->personId);
	    $subs = $person->getEvaluatePNIs();
	    $radio_questions = array(EVL_EXCELLENCE, EVL_HQPDEVELOPMENT, EVL_NETWORKING, EVL_KNOWLEDGE, EVL_MANAGEMENT, EVL_REPORTQUALITY, EVL_OVERALLSCORE, EVL_CONFIDENCE);
        $stock_comments = array(EVL_EXCELLENCE_COM, EVL_HQPDEVELOPMENT_COM, EVL_NETWORKING_COM, EVL_KNOWLEDGE_COM, EVL_MANAGEMENT_COM, EVL_REPORTQUALITY_COM);
	    $text_question = EVL_OTHERCOMMENTS;
	    $rating_map = array("Exceptional"=>'E', "Good"=>'G', "Satisfactory"=>'S', "Unsatisfactory"=>'U');
        $html =<<<EOF
            <style type='text/css'>
                div.details_sub{
                    margin-top: 20px;
                    display: none;
                }
                div.overview_table_heading {
                    text-decoration: underline;
                    font-size: 16px;
                    padding: 10px 0 20px 0;
                }
                .qtipStyle{
                    font-size: 14px;
                    line-height: 120%;
                    padding: 5px;
                }
            </style>
            <script type='text/javascript'>
                $('span.q8_tip').qtip({
                    position: {
                        corner: {
                            target: 'center',
                            tooltip: 'center'
                        }
                    }, 
                    style: {
                        classes: 'qtipStyle'
                    }
                });
                function expandSubDetails(sub_id){
                    console.log('#details_sub-'+sub_id);
                    $('.details_sub').hide();
                    $('#details_sub-'+sub_id).show();
                    $('html, body').animate({
                        scrollTop: $('#details_sub-'+sub_id).offset().top
                    }, 400);
                }
            </script>
EOF;
        $html .=<<<EOF
        <div class="overview_table_heading">Your Reviews</div>
        <table class="dashboard" style="width:100%;background:#ffffff;border-style:solid; text-align:center;" cellspacing="1" cellpadding="3" frame="box" rules="all">
EOF;
       

        $html .=<<<EOF
        	<tr>
        	<th width="20%" align="left">NI Name</th>
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
        $sub_details = "";

        foreach($subs as $sub){
            $sub_id = $sub->getId();
            $sub_name = $sub->getReversedName();
            $sub_name_straight = $sub->getFirstName(). " " .$sub->getLastName();
            $evals = $sub->getEvaluators('PNI');
            
            
            $sub_table = "";
            foreach($evals as $ev){
                $sub_row = "";
            	$ev_id = $ev->getId();
            	$ev_name = $ev->getReversedName();
            	$sub_row .= "<tr>";
                if($wgUser->getId() != $ev_id){
            	   $sub_row .= "<td align='left'>{$ev_name}</td>";
                }else{
                    $sub_row .= "<td align='left'><a href='#details_sub-{$sub_id}' onclick='expandSubDetails(\"{$sub_id}\"); return false;' >{$sub_name}</a></td>";
                }
                $q8 = $this->blobValue($ev_id, $text_question, $sub_id);
                if(is_string($q8) && strlen($q8) > 25){
                    $q8_short = substr($q8, 0, 25) . "...";
                }
                else{
                    $q8_short = $q8;
                }
                $q8 = htmlentities($q8, ENT_QUOTES);
                if(!empty($q8)){
                    $sub_row .= "<td><span class='q8_tip' title='{$q8}'><a>See Comment</a></span></td>";
            	}
                else{
                    $sub_row .= "<td>No Comment</td>";
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
            		    $sub_row .= "<td><span class='q8_tip' title='{$response_orig}<br />{$comm}'><a>{$response}</a></span></td>";
                    }else{
            			$response = "N/A";
                        $sub_row .= "<td>{$response}</td>";
            		}

            		
                    $i++;
            	
                }
            	$sub_row .= "</tr>";
                if($wgUser->getId() == $ev_id){
                    $html .= $sub_row;
                }else{
                    $sub_table .= $sub_row;
                }
        	}

            if(empty($sub_table)){
                $sub_table = "<div id='details_sub-{$sub_id}' class='details_sub'>
                <div class='overview_table_heading'>Reviews of {$sub_name_straight} by other reviewers</div>
                <p>There are no other reviewers assigned to review {$sub_name_straight}</p></div>";
            }
            else{
                $sub_table =<<<EOF
                <div id='details_sub-{$sub_id}' class='details_sub'>
                <div class='overview_table_heading'>Reviews of {$sub_name_straight} by other evaluators</div>
                <table class="dashboard" style="width:100%;background:#ffffff;border-style:solid;text-align:center;" cellspacing="1" cellpadding="3" frame="box" rules="all">
                <thead>
                    <tr>
                    <th width="20%" align='left'>Evaluator Name</th>
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
                </thead>
                <tbody>
                {$sub_table}
                </tbody>
                </table>
                </div>
EOF;
            }
            $sub_details .= $sub_table;
        }

        $html .= "</table>";
        $html .= $sub_details;


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
