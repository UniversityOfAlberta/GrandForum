<?php

class EvalOverviewReportItem extends AbstractReportItem {

	function render(){
	    global $wgOut;
        $details = $this->getTableHTML();
        $item = "$details";
        $item = $this->processCData($item);
		$wgOut->addHTML($item);
        //$this->setSeenOverview();
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
        $type = $this->getAttr('subType', 'PNI');
	    $person = Person::newFromId($this->personId);
        if($type == "PNI"){
	       $subs = $person->getEvaluatePNIs();
        }
        else if($type == "CNI"){
           $subs = $person->getEvaluateCNIs();
        }
        else if($type == "Project"){
            $subs = $person->getEvaluateProjects();
        }

	    $radio_questions = array(EVL_OVERALLSCORE, EVL_CONFIDENCE, EVL_EXCELLENCE, EVL_HQPDEVELOPMENT, EVL_NETWORKING, EVL_KNOWLEDGE, EVL_MANAGEMENT, EVL_REPORTQUALITY);
        $stock_comments = array(0,0, EVL_EXCELLENCE_COM, EVL_HQPDEVELOPMENT_COM, EVL_NETWORKING_COM, EVL_KNOWLEDGE_COM, EVL_MANAGEMENT_COM, EVL_REPORTQUALITY_COM);
	    $text_question = EVL_OTHERCOMMENTS;
        $text_question2= EVL_OTHERCOMMENTSAFTER;
	    //$rating_map = array("Exceptional"=>'E', "Strong"=>'S', "Satisfactory"=>'S', "Unsatisfactory"=>'U');
        $jscript =<<<EOF
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
                $('.comment_dialog').dialog( "destroy" );
                $('.comment_dialog').dialog({ autoOpen: false, width: 400, height: 200 });
                
                function openDialog(ev_id, sub_id, num){
                    $('#dialog'+num+'-'+ev_id+'-'+sub_id).dialog("open");
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

        $html =<<<EOF
        <div class="overview_table_heading"></div>
        <table id="overview_table" class="dashboard" style="width:100%;background:#ffffff;border-style:solid; text-align:center;" cellspacing="1" cellpadding="3" frame="box" rules="all">
EOF;
       

        $html .=<<<EOF
        	<tr>
        	<th width="20%" align="left">NI Name</th>
            <th width="10%">Q7 (Comments)</th>
        	<th width="10%">Q8</th>
        	<th width="10%">Q9</th>
        	<th style="border-left: 5px double #8C529D;">Q1</th>
        	<th>Q2</th>
        	<th>Q3</th>
        	<th>Q4</th>
        	<th>Q5</th>
        	<th>Q6</th>
        	</tr>
EOF;
        $sub_details = "";

        foreach($subs as $sub){
            $sub_id = $sub->getId();
            if($type == "PNI" || $type == "CNI"){
                $sub_name = $sub->getReversedName();
                $sub_name_straight = $sub->getFirstName(). " " .$sub->getLastName();
                $evals = $sub->getEvaluators($type);
            }
            else if($type == "Project"){
                $sub_name = $sub_name_straight = $sub->getName();
                $evals = $sub->getEvaluators();
            }
            
            $sub_table = "";
            $incomplete = false;
            foreach($evals as $ev){
                $sub_row = "";
            	$ev_id = $ev->getId();
                //echo $ev_id."<>";
            	$ev_name = $ev->getReversedName();
                $ev_name_straight = $ev->getFirstName(). " " .$ev->getLastName();

            	$sub_row .= "<tr id='row-{$sub_id}'>";
                if($wgUser->getId() != $ev_id){
            	   $sub_row .= "<td align='left'>{$ev_name}</td>";
                }else{
                    //$sub_row .= "<td align='left'><a href='#details_sub-{$sub_id}' onclick='expandSubDetails(\"{$sub_id}\"); return false;' >{$sub_name}</a></td>";
                    $sub_row .= "<td align='left'>{$sub_name}</td>";
                }

                $q8 = $this->blobValue(BLOB_TEXT, $ev_id, $text_question, $sub_id);
                //var_dump($q8);
                //$q8_2 = $this->blobValue(BLOB_TEXT, $ev_id, $text_question2, $sub_id);
               
                //$q8 = htmlentities($q8, ENT_QUOTES);
                $sub_row .= "<td>";
                if(!empty($q8)){
                    $sub_row .= "<a href='#' onclick='openDialog(\"{$ev_id}\", \"{$sub_id}\", 1); return false;'>Original</a><div id='dialog1-{$ev_id}-{$sub_id}' class='comment_dialog' title='Original Comment by {$ev_name_straight} on {$sub_name_straight}'>{$q8}</div><br />";
            	}
                else{
                    $sub_row .= "Original";
                    if($wgUser->getId() == $ev_id){ //Only set it for myself
                        $incomplete = true;
                    }
                }
                /*if(!empty($q8_2)){
                    $sub_row .= "<br /><a href='#' onclick='openDialog(\"{$ev_id}\", \"{$sub_id}\", 2); return false;'>Revised</a><div id='dialog2-{$ev_id}-{$sub_id}' class='comment_dialog' title='Revised Comment by {$ev_name_straight} on {$sub_name_straight}'>{$q8_2}</div>";
                }
                else{
                    $sub_row .= "Revised";
                }*/

                $sub_row .= "</td>";
                
                $i = 0;   
                foreach ($radio_questions as $blobItem){
                    $comm = "";
                    $comm_short = array();

                    if($i>1){
                        $comm = $this->blobValue(BLOB_ARRAY, $ev_id, $stock_comments[$i], $sub_id);
                        //var_dump($comm);

                        if(!empty($comm)){
                            
                            foreach($comm as $key=>$c){
                                if(strlen($c)>1){
                                    $comm_short[] = substr($c, 0, 1);
                                }
                            }
                        }
                    }
                    $comm_short = implode(", ", $comm_short);
                    $response_orig = $response = $this->blobValue(BLOB_TEXT, $ev_id, $blobItem, $sub_id);
            		
                    $double_border = '';
                    if($i==2){
                        $double_border = ' style="border-left: 5px double #8C529D;"';
                    }
                    //$sub_row .= "<td>";
                    if($response_orig){
            			$response = substr($response, 0, 1);
                        if(!empty($comm)){
                            $response .= "; ".$comm_short;
                            $comm = implode("<br />", $comm);
                        } 
            		    $sub_row .= "<td{$double_border}><span class='q8_tip' title='{$response_orig}<br />{$comm}'><a>{$response}</a></span></td>";
                    }else{
            			$response = "";
                        $sub_row .= "<td{$double_border}>{$response}</td>";
                        if($wgUser->getId() == $ev_id){
                            $incomplete = true;
                        }
            		}

            		
                    $i++;
            	
                }
            	$sub_row .= "</tr>";
                if($wgUser->getId() == $ev_id){
                    $html .= $sub_row;
                }else{
                    //$sub_table .= $sub_row;
                }
        	}

            $sub_table_html =<<<EOF
                <div id='details_sub-{$sub_id}' class='details_sub'>
                <div class='overview_table_heading'></div>
                <table class="dashboard" style="width:100%;background:#ffffff;border-style:solid;text-align:center;" cellspacing="1" cellpadding="3" frame="box" rules="all">
                <thead>
                    <tr>
                    <th width="20%" align='left'>Evaluator Name</th>
                    <th width="15%">Q7 (Comments)</th>
                    <th>Q1</th>
                    <th>Q2</th>
                    <th>Q3</th>
                    <th>Q4</th>
                    <th>Q5</th>
                    <th>Q6</th>
                    <th>Q8</th>
                    <th>Q9</th>
                    </tr>
                </thead>
EOF;

            if($incomplete){
                $sub_table_html .=<<<EOF
                <tbody>
                <tr class='purple_row'><td colspan='10'>Please complete your review of {$sub_name_straight} before you can see the feedback from other evaluators.</td></tr>
                </tbody>
                </table>
                </div>
EOF;
            }
            else if(empty($sub_table)){
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
            //$sub_details .= $sub_table_html;
        }

        $html .= "</table>";
        $html .= $sub_details;
        $html .= $jscript;

        return $html;
	}

	function blobValue($blob_type, $evaluator_id, $blobItem, $blobSubItem){
        $project_id = 0;
        if($this->getReport()->reportType == RP_EVAL_PROJECT){
            $project_id = $blobSubItem;
        }
 		$blob = new ReportBlob($blob_type, $this->getReport()->year, $evaluator_id, $project_id);
	    $blob_address = ReportBlob::create_address($this->getReport()->reportType, SEC_NONE, $blobItem, $blobSubItem);
		$blob->load($blob_address);
	    $blob_data = $blob->getData();
        //$addr = "BlobType=".$blob_type."; Year=". $this->getReport()->year ."; PersonID=". $evaluator_id."; ProjectID=". $this->projectId."<br />";
        //$addr .= "ReportType=".$this->getReport()->reportType."; Section=". SEC_NONE ."; BlobItem=". $blobItem ."; SubItem=". $blobSubItem ."<br /><br>";
        //echo $addr;
	    return $blob_data;
	}

    function setSeenOverview(){
        global $wgUser, $wgImpersonating;
        if($wgImpersonating){
            return;
        }
        

        $evaluator_id = $this->personId;
        $blob = new ReportBlob(BLOB_TEXT, $this->getReport()->year, $evaluator_id, 0);
        $blob_address = ReportBlob::create_address($this->getReport()->reportType, SEC_NONE, EVL_SEENOTHERREVIEWS, 0);
        
        /*
        $blob->load($blob_address);
        $data = $blob->getData();
        if(!empty($data)){
            return;
        }
        */

        $data = "Yes";
        $blob->store($data, $blob_address);
        
        /*
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
        */
    }
}
?>
