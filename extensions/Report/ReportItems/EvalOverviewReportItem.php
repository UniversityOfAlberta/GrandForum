<?php

class EvalOverviewReportItem extends AbstractReportItem {

	function render(){
	    global $wgOut;
        $details = $this->getTableHTML();
        $item = "$details";
        $item = $this->processCData($item);
		$wgOut->addHTML($item);
        $this->setSeenOverview();
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
        $text_question2= EVL_OTHERCOMMENTSAFTER;
	    //$rating_map = array("Exceptional"=>'E', "Strong"=>'S', "Satisfactory"=>'S', "Unsatisfactory"=>'U');
        $html =<<<EOF
            <style type='text/css'>
                div.details_sub{
                    margin-top: 20px;
                    display: none;
                }
                div.overview_table_heading {
                    text-decoration: underline;
                    font-size: 16px;
                    padding: 10px 0;
                }
                .qtipStyle{
                    font-size: 14px;
                    line-height: 120%;
                    padding: 5px;
                }
                tr.purple_row{
                    background-color: #F3EBF5;
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
                $('.comment_dialog').dialog({ autoOpen: false, width: 400, height: 200 });
                
                function openDialog(sub_id, num){
                    $('#dialog'+num+'-'+sub_id).dialog("open");
                }

                function expandSubDetails(sub_id){
                    $('#overview_table tr').removeClass('purple_row');
                    $('#row-'+sub_id).addClass('purple_row');
                    
                    $('.details_sub').hide();
                    $('#details_sub-'+sub_id).show();
                    //$('html, body').animate({
                    //    scrollTop: $('#details_sub-'+sub_id).offset().top
                    //}, 400);
                }
            </script>
EOF;
        $html .=<<<EOF
        <div class="overview_table_heading"></div>
        <table id="overview_table" class="dashboard" style="width:100%;background:#ffffff;border-style:solid; text-align:center;" cellspacing="1" cellpadding="3" frame="box" rules="all">
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
            	$sub_row .= "<tr id='row-{$sub_id}'>";
                if($wgUser->getId() != $ev_id){
            	   $sub_row .= "<td align='left'>{$ev_name}</td>";
                }else{
                    $sub_row .= "<td align='left'><a href='#details_sub-{$sub_id}' onclick='expandSubDetails(\"{$sub_id}\"); return false;' >{$sub_name}</a></td>";
                }
                $q8 = $this->blobValue($ev_id, $text_question, $sub_id);
                $q8_2 = $this->blobValue($ev_id, $text_question2, $sub_id);
                
                //$q8 = htmlentities($q8, ENT_QUOTES);
                $sub_row .= "<td align='left'>";
                if(!empty($q8)){
                    $sub_row .= "<a href='#' onclick='openDialog(\"{$sub_id}\", 1); return false;'>See Original Comment</a><div id='dialog1-{$sub_id}' class='comment_dialog' title='Original Comment on {$sub_name_straight}'>{$q8}</div><br />";
            	}
                else{
                    $sub_row .= "No Original Comment</br>";
                }
                if(!empty($q8_2)){
                    $sub_row .= "<a href='#' onclick='openDialog(\"{$sub_id}\", 2); return false;'>See Changed Comment</a><div id='dialog2-{$sub_id}' class='comment_dialog' title='Changed Comment on {$sub_name_straight}'>{$q8_2}</div>";
                }
                else{
                    $sub_row .= "No Changed Comment";
                }

                $sub_row .= "</td>";
                
                $i = 0;   
                foreach ($radio_questions as $blobItem){
                    $comm = "";
                    $comm_short = array();

                    if($i < 6){
                        $comm = $this->blobValue($ev_id, $stock_comments[$i], $sub_id);
                        
                        if(!empty($comm)){
                            
                            foreach($comm as $key=>$c){
                                if(strlen($c)>3){
                                    $comm_short[] = substr($c, 0, 3);
                                }
                            }
                        }
                    }
                    $comm_short = implode(", ", $comm_short);
                    $response_orig = $response = $this->blobValue($ev_id, $blobItem, $sub_id);
            		if($response_orig){
            			$response = substr($response, 0, 1);
                        if(!empty($comm)){
                            $response .= "; ".$comm_short;
                            $comm = implode("<br />", $comm);
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

            $sub_table_html =<<<EOF
                <div id='details_sub-{$sub_id}' class='details_sub'>
                <div class='overview_table_heading'></div>
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
EOF;

            if(empty($sub_table)){
                $sub_table_html .=<<<EOF
                <tbody>
                <tr class='purple_row'><td colspan='10'>There are no other reviewers assigned to review {$sub_name_straight}</td></tr>
                </tbody>
                </table>
                </div>
EOF;
            }
            else{
                $sub_table_html .=<<<EOF
                <tbody>
                {$sub_table}
                </tbody>
                </table>
                </div>
EOF;
            }
            $sub_details .= $sub_table_html;
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

    function setSeenOverview(){
        global $wgUser, $wgImpersonating;
        if($wgImpersonating){
            return;
        }
        
        $evaluator_id = $this->personId;
        $blob = new ReportBlob(BLOB_TEXT, $this->getReport()->year, $evaluator_id, $this->projectId);
        $blob_address = ReportBlob::create_address($this->getReport()->reportType, SEC_NONE, EVL_SEENOTHERREVIEWS, 0);
        $blob->load($blob_address);
        $data = $blob->getData();
        if(!empty($data)){
            return;
        }

        $data = "Yes";
        $blob->store($data, $blob_address);
        
        $person = Person::newFromId($this->personId);
        $subs = $person->getEvaluatePNIs();
        foreach($subs as $sub){
            $sub_id = $sub->getId();
            $blob = new ReportBlob(BLOB_TEXT, $this->getReport()->year, $evaluator_id, $this->projectId);
            $blob_address_from = ReportBlob::create_address($this->getReport()->reportType, SEC_NONE, EVL_OTHERCOMMENTS, $sub_id);
            $blob->load($blob_address_from);

            if($orig_data = $blob->getData()){    
                $blob_address_to = ReportBlob::create_address($this->getReport()->reportType, SEC_NONE, EVL_OTHERCOMMENTSAFTER, $sub_id);
                $blob->store($orig_data, $blob_address_to);
            }
        }
    }
}
?>
