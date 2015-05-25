<?php

class EvalOverviewReportItem extends AbstractReportItem {

    function render(){
        global $wgOut;
        $details = $this->getTableHTML();
        $item = "$details";
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
        
        if(isset($_GET['seenReport']) && !empty($_GET['seenReport']) && date("Y-m-d H:i:s") >= REPORTING_RMC_REVISED){
            $sub_id = $_GET['seenReport'];
            $this->setSeenOverview($sub_id);
        }
    }
    
    function renderForPDF(){
        global $wgOut;
        $details = $this->getTableHTML();
        $item = "$details";
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
    }
    
    function getTableHTML(){
        global $wgUser, $wgServer, $wgScriptPath;
        $type = $this->getAttr('subType', 'NI');
        $person = Person::newFromId($this->personId);
        $section_url = "";

        $radio_questions = array(EVL_OVERALLSCORE, EVL_CONFIDENCE, EVL_EXCELLENCE, EVL_HQPDEVELOPMENT, EVL_NETWORKING, EVL_KNOWLEDGE, EVL_MANAGEMENT, EVL_REPORTQUALITY);
        $stock_comments = array(0,0, EVL_EXCELLENCE_COM, EVL_HQPDEVELOPMENT_COM, EVL_NETWORKING_COM, EVL_KNOWLEDGE_COM, EVL_MANAGEMENT_COM, EVL_REPORTQUALITY_COM);
        $text_question = EVL_OTHERCOMMENTS;

        if($type == "NI"){
            $subs = $person->getEvaluateNIs();
            $report_url = "EvalNIReport";
            $section_url = "NI+Overview";
        }
        else if($type == "Project"){
            $subs = $person->getEvaluateProjects();
            $report_url = "EvalProjectReport";
            $section_url = "Project+Overview";
            $radio_questions = array(EVL_OVERALLSCORE, EVL_CONFIDENCE, EVL_EXCELLENCE, EVL_HQPDEVELOPMENT, EVL_NETWORKING, EVL_KNOWLEDGE, EVL_REPORTQUALITY);
            $stock_comments =array(0,0, EVL_EXCELLENCE_COM, EVL_HQPDEVELOPMENT_COM, EVL_NETWORKING_COM, EVL_KNOWLEDGE_COM, EVL_REPORTQUALITY_COM);
        }
        
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
                        my: 'bottom left',
                        at: 'top right',
                    }, 
                    style: {
                        classes: 'qtipStyle'
                    }
                });
                $('#overview_table th').qtip({
                    position: {
                        my: 'bottom center',
                        at: 'top center',
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
                    $.ajax({
                        type: "GET",
                        url: "{$wgServer}{$wgScriptPath}/index.php/Special:Report?report={$report_url}&section={$section_url}&seenReport="+sub_id,
                    });
                }
            </script>
EOF;

        $html =<<<EOF
        <div class="overview_table_heading"></div>
        <table id="overview_table" class="dashboard" style="width:100%;background:#ffffff;border-style:solid; text-align:center;" cellspacing="1" cellpadding="3" frame="box" rules="all">
EOF;
       

        if($type == "Project"){
            $html .=<<<EOF
            <tr>
            <th width="20%" align="left">NI Name</th>
            <th width="10%" title="Evaluator Comments">Q7 (Comments)</th>
            <th width="10%" title="Overall Score">Q6</th>
            <th width="10%" title="Confidence Level of Evaluator">Q8</th>
            <th style="border-left: 5px double #8C529D;" title="Excellence of the Research Program">Q1</th>
            <th title="Development of HQP">Q2</th>
            <th title="Networking and Partnerships">Q3</th>
            <th title="Knowledge and Technology Exchange and Exploitation">Q4</th>
            <th title="Rating for Quality of Report">Q5</th>
            </tr>
EOF;
        }
        else{
            $html .=<<<EOF
            <tr>
            <th width="20%" align="left">NI Name</th>
            <th width="10%" title="Evaluator Comments">Q8 (Comments)</th>
            <th width="10%" title="Overall Score">Q7</th>
            <th width="10%" title="Confidence Level of Evaluator">Q9</th>
            <th style="border-left: 5px double #8C529D;" title="Excellence of the Research Program">Q1</th>
            <th title="Development of HQP">Q2</th>
            <th title="Networking and Partnerships">Q3</th>
            <th title="Knowledge and Technology Exchange and Exploitation">Q4</th>
            <th title="Management of the Network">Q5</th>
            <th title="Rating for Quality of Report">Q6</th>
            </tr>
EOF;
        }
        $sub_details = "";

        foreach($subs as $sub){
            $sub_id = $sub->getId();
            if($type == "NI"){
                $sub_name = $sub->getReversedName();
                $sub_name_straight = $sub->getFirstName(). " " .$sub->getLastName();
                $evals = $sub->getEvaluators($type, $this->getReport()->year);
            }
            else if($type == "Project"){
                $sub_name = $sub_name_straight = $sub->getName();
                $evals = $sub->getEvaluators($this->getReport()->year);
            }
            
            $sub_table = "";
            $incomplete = false;
            foreach($evals as $ev){
                $sub_row = "";
                $ev_id = $ev->getId();
                
                $ev_name = $ev->getReversedName();
                $ev_name_straight = $ev->getFirstName(). " " .$ev->getLastName();

                $sub_row .= "<tr id='row-{$sub_id}'>";
                if($wgUser->getId() != $ev_id){
                   $sub_row .= "<td rowspan='3' align='left' style='background-color: #F3EBF5;'>{$ev_name}</td></tr>";
                }else{
                    if(date("Y-m-d H:i:s") >= REPORTING_RMC_REVISED){
                        $sub_row .= "<td rowspan='3' align='left'><a href='#details_sub-{$sub_id}' onclick='expandSubDetails(\"{$sub_id}\"); return false;' >{$sub_name}</a></td></tr>";
                    }
                    else{
                        $sub_row .= "<td rowspan='3' align='left'>{$sub_name}</td></tr>";
                    }
                }

                //Actual Answers
                //foreach(array(0,20) as $add){
                $q8 = $this->blobValue(BLOB_ARRAY, $ev_id, $text_question, $sub_id);
                
                $sub_row .= "<tr><td>";
                $sub_row2 = "<tr><td>";
                if(!empty($q8) && is_array($q8)){
                    $q8_O = (isset($q8['original']))? $q8['original'] : "";
                    $q8_R = (isset($q8['revised']))? $q8['revised'] : "";
                    $diff = strcmp($q8_O, $q8_R);

                    if(!empty($q8_O)){
                        $q8_O = nl2br($q8_O);
                        $sub_row .= "<a href='#' onclick='openDialog(\"{$ev_id}\", \"{$sub_id}\", 1); return false;'>Original</a><div id='dialog1-{$ev_id}-{$sub_id}' class='comment_dialog' title='Original Comment by {$ev_name_straight} on {$sub_name_straight}'>{$q8_O}</div><br />";
                    }
                    else{
                        $sub_row .= "Original";
                    }

                    if(!empty($q8_R) && $diff != 0){
                        $q8_R = nl2br($q8_R);
                        $sub_row2 .= "<a href='#' onclick='openDialog(\"{$ev_id}\", \"{$sub_id}\", 2); return false;'>Revised</a><div id='dialog2-{$ev_id}-{$sub_id}' class='comment_dialog' title='Revised Comment by {$ev_name_straight} on {$sub_name_straight}'>{$q8_R}</div><br />";
                    }
                    else{
                        $sub_row2 .= "Revised";
                    }
                }
                else{
                    $sub_row .= "Original";
                    $sub_row2 .= "Revised";
                    if($wgUser->getId() == $ev_id){ //Only set it for myself
                        $incomplete = true;
                    }
                }

                $sub_row .= "</td>";
                $sub_row2 .= "</td>";
                
                $i = 0;   
                foreach ($radio_questions as $blobItem){
                    $comm = "";
                    $comm_short = array();

                    $comm2 = "";
                    $comm_short2 = array();

                    if($i>1){
                        $comm = $this->blobValue(BLOB_ARRAY, $ev_id, $stock_comments[$i], $sub_id);
                        $comm2 = (isset($comm['revised']))? $comm['revised'] : array();
                        $comm = (isset($comm['original']))? $comm['original'] : array();
                        //$diff = array_diff($comm, $comm2);
                        
                        if(!empty($comm)){
                            foreach($comm as $key=>$c){
                                if(strlen($c)>1){
                                    $comm_short[] = substr($c, 0, 1);
                                }
                            }
                        }
                        if(!empty($comm2)){
                            foreach($comm2 as $key=>$c){
                                if(strlen($c)>1){
                                    $comm_short2[] = substr($c, 0, 1);
                                }
                            }
                        }
                    }
                    $comm_short = implode(", ", $comm_short);
                    $comm_short2 = implode(", ", $comm_short2);

                    $response = $this->blobValue(BLOB_ARRAY, $ev_id, $blobItem, $sub_id);
                    $response_orig = (isset($response['original']))? $response['original'] : "";
                    $response_rev = $response2 = (isset($response['revised']))? $response['revised'] : "";
                    $diff = strcmp($response_orig, $response_rev);
                    $diff2 = array();
                    if($i>1){
                        $diff2 = array_merge(array_diff(array_filter($comm), array_filter($comm2)), 
                                             array_diff(array_filter($comm2), array_filter($comm)));
                    }
                    
                    $response = $response_orig;
                    
                    $double_border = '';
                    if($i==2){
                        $double_border = ' style="border-left: 5px double #8C529D;"';
                    }
                    
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

                    if($response_rev && ($diff != 0 || !empty($diff2))){
                        $response2 = substr($response2, 0, 1);
                        if(!empty($comm2)){
                            $response2 .= "; ".$comm_short2;
                            $comm2 = implode("<br />", $comm2);
                        } 
                        $sub_row2 .= "<td{$double_border}><span class='q8_tip' title='{$response_rev}<br />{$comm2}'><a>{$response2}</a></span></td>";
                    }else{
                        $response2 = "";
                        $sub_row2 .= "<td{$double_border}>{$response2}</td>";
                    }

                    
                    $i++;
                
                }
                $sub_row .= "</tr>";
                $sub_row2 .= "</tr>";

                if($wgUser->getId() == $ev_id){
                    $html .= $sub_row;
                    $html .= $sub_row2;
                }else{
                    $sub_table .= $sub_row;
                    $sub_table .= $sub_row2;
                }
            }

            if($type == "Project"){
                $sub_table_html =<<<EOF
                <div id='details_sub-{$sub_id}' class='details_sub'>
                <div class='overview_table_heading'></div>
                <table class="dashboard" style="width:100%;background:#ffffff;border-style:solid;text-align:center;" cellspacing="1" cellpadding="3" frame="box" rules="all">
                <thead>
                    <tr>
                    <th width="20%" align='left'>Evaluator Name</th>
                    <th width="10%">Q7 (Comments)</th>
                    <th width="10%">Q6</th>
                    <th width="10%">Q8</th>
                    <th style="border-left: 5px double #8C529D;">Q1</th>
                    <th>Q2</th>
                    <th>Q3</th>
                    <th>Q4</th>
                    <th>Q5</th>
                    </tr>
                </thead>
EOF;
            }
            else{
                $sub_table_html =<<<EOF
                <div id='details_sub-{$sub_id}' class='details_sub'>
                <div class='overview_table_heading'></div>
                <table class="dashboard" style="width:100%;background:#ffffff;border-style:solid;text-align:center;" cellspacing="1" cellpadding="3" frame="box" rules="all">
                <thead>
                    <tr>
                    <th width="20%" align='left'>Evaluator Name</th>
                    <th width="10%">Q8 (Comments)</th>
                    <th width="10%">Q7</th>
                    <th width="10%">Q9</th>
                    <th style="border-left: 5px double #8C529D;">Q1</th>
                    <th>Q2</th>
                    <th>Q3</th>
                    <th>Q4</th>
                    <th>Q5</th>
                    <th>Q6</th>
                    </tr>
                </thead>
EOF;
            }

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
            $sub_details .= $sub_table_html;
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

    function setSeenOverview($reportSubItem = null){
        global $wgUser, $wgImpersonating;
        if($wgImpersonating || is_null($reportSubItem)){
            return;
        }
        
        $evaluator_id = $this->personId;
        $project_id = 0;
        $type = $this->getAttr('subType', 'NI');
        $person = Person::newFromId($evaluator_id);

        $questions = array();
        if($type == "NI"){
            $subs = $person->getEvaluateNIs();
            $questions = array(EVL_OVERALLSCORE, EVL_CONFIDENCE, EVL_EXCELLENCE, EVL_HQPDEVELOPMENT, EVL_NETWORKING, EVL_KNOWLEDGE, EVL_MANAGEMENT, EVL_REPORTQUALITY, EVL_OTHERCOMMENTS);
            $questions2 = array(EVL_EXCELLENCE_COM, EVL_HQPDEVELOPMENT_COM, EVL_NETWORKING_COM, EVL_KNOWLEDGE_COM, EVL_MANAGEMENT_COM, EVL_REPORTQUALITY_COM);
        }
        else if($type == "Project"){
            $subs = $person->getEvaluateProjects();
            $questions = array(EVL_OVERALLSCORE, EVL_CONFIDENCE, EVL_EXCELLENCE, EVL_HQPDEVELOPMENT, EVL_NETWORKING, EVL_KNOWLEDGE, EVL_REPORTQUALITY, EVL_OTHERCOMMENTS);
            $questions2 = array(EVL_EXCELLENCE_COM, EVL_HQPDEVELOPMENT_COM, EVL_NETWORKING_COM, EVL_KNOWLEDGE_COM, EVL_REPORTQUALITY_COM);
            $project_id = $reportSubItem;
        }

         //Determine if own review was completed.
        $complete = true;
        //foreach ($subs as $sub){
            $sub_id = $reportSubItem; //$sub->getId();
            foreach($questions as $q){
                $val = $this->blobValue(BLOB_ARRAY, $evaluator_id, $q, $sub_id);
                if(empty($val['original'])){
                    $complete = false;
                    //echo "QUESTION $q  INCOMPLETE<br>";
                    break;
                }
            }
        //}

        if($complete){

            //Check if seenother flag is already set
            $blob = new ReportBlob(BLOB_TEXT, $this->getReport()->year, $evaluator_id, $project_id);
            $blob_address = ReportBlob::create_address($this->getReport()->reportType, SEC_NONE, EVL_SEENOTHERREVIEWS, $reportSubItem);
            $blob->load($blob_address);
            $seeonotherreviews = $blob->getData();
            if(!$seeonotherreviews){

                //foreach ($subs as $sub){
                    $sub_id = $reportSubItem; //$sub->getId();
                    foreach(array_merge($questions, $questions2) as $q){
                        $this->setRevised(BLOB_ARRAY, $evaluator_id, $q, $sub_id);
                    }    
                //}

                $data = "Yes";
                $blob->store($data, $blob_address);
            }
            
            
        }   
    }

    function setRevised($blob_type, $evaluator_id, $blobItem, $blobSubItem){
        $project_id = 0;
        if($this->getReport()->reportType == RP_EVAL_PROJECT){
            $project_id = $blobSubItem;
        }
        $blob = new ReportBlob($blob_type, $this->getReport()->year, $evaluator_id, $project_id);
        $blob_address = ReportBlob::create_address($this->getReport()->reportType, SEC_NONE, $blobItem, $blobSubItem);

        $blob->load($blob_address);
        $blob_data = $blob->getData();
        $orig_data = (isset($blob_data['original']))? $blob_data['original'] : "";
      
        //copy over the data if the 'AFTER' blob does not yet exist              
        if(isset($blob_data['original']) && empty($blob_data['revised'])){
            $blob_data['revised'] = $orig_data;
            $blob->store($blob_data, $blob_address);
        }    
    }

}
?>
