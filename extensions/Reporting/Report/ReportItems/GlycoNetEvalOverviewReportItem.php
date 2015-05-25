<?php

class GlycoNetEvalOverviewReportItem extends AbstractReportItem {

    function render(){
        global $wgOut;
        $details = $this->getTableHTML();
        $item = "$details";
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
        
        if(isset($_GET['seenReport']) && !empty($_GET['seenReport']) && date("Y-m-d H:i:s") >= REPORTING_YEAR."-05-24 00:00:00"){
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
        global $wgUser, $wgServer, $wgScriptPath, $config;
        $type = $this->getAttr('subType', 'PNI');
        $person = Person::newFromId($this->personId);
        $section_url = "";

        $subs = $person->getEvaluateProjects();
        $report_url = "RMCProjectReview";
        $section_url = "Overview";
        $radio_questions = array(EVL_EXCELLENCE, EVL_STRATEGIC, EVL_INTEG, EVL_NETWORKING, EVL_KNOWLEDGE, EVL_HQPDEVELOPMENT, EVL_REPORTQUALITY, EVL_OVERALLSCORE, EVL_CONFIDENCE);
        $stock_comments = array(EVL_EXCELLENCE_COM, EVL_STRATEGIC_COM, EVL_INTEG_COM, EVL_NETWORKING_COM, EVL_KNOWLEDGE_COM, EVL_HQPDEVELOPMENT_COM, EVL_REPORTQUALITY_COM, 0, 0);
        $other_comments = array(EVL_EXCELLENCE_OTHER, EVL_STRATEGIC_OTHER, EVL_INTEG_OTHER, EVL_NETWORKING_OTHER, EVL_KNOWLEDGE_OTHER, EVL_HQPDEVELOPMENT_OTHER, EVL_REPORTQUALITY_OTHER, 0, 0);
        
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
                    background-color: #EEEEEE;
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
                $('.opened_comment_dialog').dialog("destroy").remove();
                
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
        <p>You can see the comments of other reviewers by clicking on the project name after May 24, {$this->getReport()->year} and as long as you have completed your own review.</p>
        <table id="overview_table" class="dashboard" style="width:100%;background:#ffffff;border-style:solid; text-align:center;" cellspacing="1" cellpadding="3" frame="box" rules="all">
EOF;

        if($type == "Project"){
            $html .=<<<EOF
            <tr>
            <th width="15%" align="left">Project Name</th>
            <th width="5%"></th>
            <th title="Excellence of the Research Program">Q1</th>
            <th title="Alignment with GlycoNet Strategic Plan">Q2</th>
            <th title="Interdisciplinarity and Integration">Q3</th>
            <th title="Networking and Partnerships">Q4</th>
            <th title="Knowledge and Technology Exchange and Exploitation">Q5</th>
            <th title="Development of HQP">Q6</th>
            <th title="Rating for Quality of Report">Q7</th>
            <th title="Overall Score">Q8</th>
            <th title="Confidence Level of Evaluator ">Q9</th>
            </tr>
EOF;
        }

        $sub_details = "";

        foreach($subs as $sub){
            $sub_id = $sub->getId();
            if($type == "Project"){
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
                   $sub_row .= "<td rowspan='3' align='left' style='background-color: #EEEEEE;'>{$ev_name}</td></tr>";
                }else{
                    if(date("Y-m-d H:i:s") >= REPORTING_YEAR."-05-24 00:00:00"){
                        $sub_row .= "<td rowspan='3' align='left'><a href='#details_sub-{$sub_id}' onclick='expandSubDetails(\"{$sub_id}\"); return false;' >{$sub_name}</a></td></tr>";
                    }
                    else{
                        $sub_row .= "<td rowspan='3' align='left'>{$sub_name}</td></tr>";
                    }
                }

                $sub_row .= "<tr><td>Original</td>";
                $sub_row2 = "<tr><td>Revised</td>";

                $i = 0;   
                foreach ($radio_questions as $blobItem){
                    $comment = "";
                    $comments = "";
                    
                    $comm = "";
                    $comm_short = array();

                    $comm2 = "";
                    $comm_short2 = array();

                    if(isset($other_comments[$i]) && $other_comments[$i] != 0){
                        $comment = nl2br($this->blobValue(BLOB_TEXT, $ev_id, $other_comments[$i], $sub_id));
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
                    if(isset($other_comments[$i]) && $other_comments[$i] != 0){
                        $diff2 = array_merge(array_diff(array_filter($comm), array_filter($comm2)), 
                                             array_diff(array_filter($comm2), array_filter($comm)));
                    }
                    
                    $response = $response_orig;
                    
                    if($response_orig){
                        $response = substr($response, 0, 1);
                        if(!empty($comm)){
                            $response .= "; ".$comm_short;
                            $comm = "<li>".implode("</li><li>", $comm)."</li>";
                        } 
                        else{
                            $comm = "";
                        }
                        if($comment != ""){
                            $comments = "<div title='Other Comments' id='{$sub_id}_{$ev_id}_{$i}' style='display:none;' class='comment_dialog'>$comment</div>";
                            $response = "<b>$response</b>";
                        }
                        $sub_row .= "<td><span class='q8_tip' title='<b>{$response_orig}</b><ul>{$comm}</ul>'><a style='cursor:pointer;' onClick='$(\"#{$sub_id}_{$ev_id}_{$i}\").dialog({width:\"600px\"}).addClass(\"opened_comment_dialog\");'>{$response}</a></span>{$comments}</td>";
                    }else{
                        $response = "";
                        $sub_row .= "<td>{$response}</td>";
                        if($wgUser->getId() == $ev_id){
                            $incomplete = true;
                        }
                    }

                    if($response_rev && ($diff != 0 || !empty($diff2))){
                        $response2 = substr($response2, 0, 1);
                        if(!empty($comm2)){
                            $response2 .= "; ".$comm_short2;
                            $comm2 = "<li>".implode("</li><li>", $comm2)."</li>";
                        }
                        else{
                            $comm2 = "";
                        }
                        if($comment != ""){
                            $response2 = "<b>$response2</b>";
                        }
                        $sub_row2 .= "<td><span class='q8_tip' title='<b>{$response_rev}</b><ul>{$comm2}</ul>'><a style='cursor:pointer;' onClick='$(\"#{$sub_id}_{$ev_id}_{$i}\").dialog({width:\"600px\"}).addClass(\"opened_comment_dialog\");'>{$response2}</a></span></td>";
                    }else{
                        $response2 = "";
                        $sub_row2 .= "<td>{$response2}</td>";
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

            $sub_table_html =<<<EOF
            <div id='details_sub-{$sub_id}' class='details_sub'>
            <div class='overview_table_heading'></div>
            <table class="dashboard" style="width:100%;background:#ffffff;border-style:solid;text-align:center;" cellspacing="1" cellpadding="3" frame="box" rules="all">
            <thead>
                <tr>
                <th width="15%" align='left'>Evaluator Name</th>
                <th width="5%"></th>
                <th>Q1</th>
                <th>Q2</th>
                <th>Q3</th>
                <th>Q4</th>
                <th>Q5</th>
                <th>Q6</th>
                <th>Q7</th>
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
            $sub_details .= $sub_table_html;
        }

        $html .= "</table>";
        $html .= $sub_details;
        $html .= $jscript;

        return $html;
    }

    function blobValue($blob_type, $evaluator_id, $blobItem, $blobSubItem){
        $project_id = $blobSubItem;
        $blob = new ReportBlob($blob_type, $this->getReport()->year, $evaluator_id, $project_id);
        $blob_address = ReportBlob::create_address($this->getReport()->reportType, SEC_NONE, $blobItem, 0);
        $blob->load($blob_address);
        $blob_data = $blob->getData();
        return $blob_data;
    }

    function setSeenOverview($reportSubItem = null){
        global $wgUser, $wgImpersonating;
        if($wgImpersonating || is_null($reportSubItem)){
            return;
        }
        
        $evaluator_id = $this->personId;
        $project_id = 0;
        $type = $this->getAttr('subType', 'PNI');
        $person = Person::newFromId($evaluator_id);

        $questions = array();
        if($type == "Project"){
            $subs = $person->getEvaluateProjects();
            $questions = array(EVL_EXCELLENCE, EVL_STRATEGIC, EVL_INTEG, EVL_NETWORKING, EVL_KNOWLEDGE, EVL_HQPDEVELOPMENT, EVL_REPORTQUALITY, EVL_OVERALLSCORE, EVL_CONFIDENCE);
            $questions2 = array(EVL_INTEG_COM, EVL_NETWORKING_COM, EVL_KNOWLEDGE_COM, EVL_HQPDEVELOPMENT_COM, EVL_REPORTQUALITY_COM, EVL_EXCELLENCE_COM, EVL_STRATEGIC_COM);
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
                $sub_id = $reportSubItem; //$sub->getId();
                foreach(array_merge($questions, $questions2) as $q){
                    $this->setRevised(BLOB_ARRAY, $evaluator_id, $q, $sub_id);
                }    
                $data = "Yes";
                $blob->store($data, $blob_address);
            }
        }   
    }

    function setRevised($blob_type, $evaluator_id, $blobItem, $blobSubItem){
        $project_id = $blobSubItem;
        $blob = new ReportBlob($blob_type, $this->getReport()->year, $evaluator_id, $project_id);
        $blob_address = ReportBlob::create_address($this->getReport()->reportType, SEC_NONE, $blobItem, 0);

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
