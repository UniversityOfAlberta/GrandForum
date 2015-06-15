<?php

class RMC2015Tab extends AbstractTab {

    function RMC2015Tab(){
        global $wgOut;
        parent::AbstractTab("2015");
        //$wgOut->setPageTitle("Evaluation Tables: RMC");
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath, $wgOut, $foldscript;
        $this->html =<<<EOF
            <style type="text/css">
                .ui-tabs-panel, .ui-tabs {
                    padding-left: 0px !important;
                }
            </style>
            <style type='text/css'>
                .qtipStyle{
                    font-size: 14px;
                    line-height: 120%;
                    padding: 5px;
                }
                a.reportTab {
                    padding-left: 10px !important;
                    margin-left: 10px !important;
                }
                div#outerReport{
                    min-height: 550px;
                }
                div#aboveTabs {
                    width: 215px !important;
                }
                div#reportTabs {
                    width: 215px;
                }
                a.marginTop {
                    margin-top: 15px;
                }
                th span.tableHeader {
                    display: inline-block;
                    height: 100%;
                    line-height: 340%;
                    float: left;
                }
                th span.borderLeft {
                    border-left: 1px solid #808080;
                }
            </style>
            <style type='text/css'>
                .qtipStyle{
                    font-size: 14px;
                    line-height: 120%;
                    padding: 5px;
                }
            </style>
            <script type='text/javascript'>
            $(document).ready(function(){
                $('span.q_tip').qtip({
                    position: {
                        my: 'topRight',
                        at: 'bottomLeft'
                    }, 
                    style: {
                        classes: 'qtipStyle'
                    }
                });
                $('.comment_dialog').dialog( "destroy" );
                $('.comment_dialog').dialog({ autoOpen: false, width: 600, height: 400 });
                $('.indexTable_2015').dataTable({
                    'aLengthMenu': [[-1], ['All']],
                    "bFilter": true,
                    "aaSorting": [[1,'desc']]
                    //"bSort": false
                });
                //$('.dataTables_filter input').css('width', 250);
            });  

            function openDialog(ev_id, sub_id, num){
                alert('#dialog'+num+'-'+ev_id+'-'+sub_id);
                $('#dialog'+num+'-'+ev_id+'-'+sub_id).dialog("open");
            }
            </script>
EOF;
        $this->showEvalProjectOverview();
        return $this->html;
    }

    function showEvalProjectOverview(){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $foldscript, $reporteeId, $getPerson;
        $me = Person::newFromWgUser();
        $type = 'Project';
        $rtype = RP_EVAL_PROJECT;

        $weights = array('Top'=>4, 'Upper Middle'=>3, 'Lower Middle'=>2, 'Bottom'=>1);
        $aves = array('4'=>2, '3'=>1.5, '2'=>1, '1'=>0.5);

        // Check for a download.
        $action = @$_GET['getpdf'];
        if ($action != "") {
            $p = Person::newFromId($wgUser->getId());
            $sto = new ReportStorage($p);
            $wgOut->disable();
            return $sto->trigger_download($action, "{$action}.pdf", false);
        }
        
        $this->html .=<<<EOF
        <h3>$type Summary of Questions 1-9</h3>
        <table class='indexTable indexTable_2015' cellspacing='1' cellpadding='3' style='border-style:solid;' width='100%' frame="box" rules="all">
        <thead>
        <tr>
            <th style='background: #EEEEEE;' width="10%">$type</th>
            <th style='background: #EEEEEE; padding:0px;'>
                <span class='tableHeader' style="width: 14.978%;">Evaluator</span>
                <span class='tableHeader' style="width: 8.5%;">&nbsp;</span>
                <span class='tableHeader' style="width: 8.5%;">Q1</span>
                <span class='tableHeader' style="width: 8.5%;">Q2</span>
                <span class='tableHeader' style="width: 8.5%;">Q3</span>
                <span class='tableHeader' style="width: 8.5%;">Q4</span>
                <span class='tableHeader' style="width: 8.5%;">Q5</span>
                <span class='tableHeader' style="width: 8.5%;">Q6</span>
                <span class='tableHeader' style="width: 8.5%;">Q7</span>
                <span class='tableHeader' style="width: 8.5%;">Q8</span>
                <span class='tableHeader' style="width: 8.5%;">Q9</span>
            </th>
        </tr>
        </thead>
        <tbody>
EOF;

        $radio_questions = array(EVL_EXCELLENCE, EVL_STRATEGIC, EVL_INTEG, EVL_NETWORKING, EVL_KNOWLEDGE, EVL_HQPDEVELOPMENT, EVL_REPORTQUALITY, EVL_OVERALLSCORE, EVL_CONFIDENCE);
        $stock_comments = array(EVL_EXCELLENCE_COM, EVL_STRATEGIC_COM, EVL_INTEG_COM, EVL_NETWORKING_COM, EVL_KNOWLEDGE_COM, EVL_HQPDEVELOPMENT_COM, EVL_REPORTQUALITY_COM, 0, 0);
        $other_comments = array(EVL_EXCELLENCE_OTHER, EVL_STRATEGIC_OTHER, EVL_INTEG_OTHER, EVL_NETWORKING_OTHER, EVL_KNOWLEDGE_OTHER, EVL_HQPDEVELOPMENT_OTHER, EVL_REPORTQUALITY_OTHER, 0, 0);

        $projects = Person::getAllEvaluates($type, 2015);
        $sorted_projects = array();
        foreach ($projects as $p){
            $sorted_projects[$p->getId()] = $p->getName();
        }
        asort($sorted_projects);
        
        foreach($sorted_projects as $ni_id => $ni_name){
            $ni = Project::newFromId($ni_id);
            //$ni_id = $ni->getId();
            //$ni_name = $ni->getReversedName();
            $evaluators = $ni->getEvaluators(2015);

            $rowspan = count($evaluators);
            if($rowspan == 0){
                continue;
            }
            $rowspan = $rowspan*2;

            $this->html .=<<<EOF
            <tr>
            <td>
            <b>{$ni_name}</b>
            </td>
EOF;
            $sub_rows = "<table style='border-collapse:collapse;' width='100%' rules='none'>";
            $div_count = 0;
            $ev_count = 0;
            foreach($evaluators as $evaluator) {
                $eval_id = $evaluator->getId();
                $eval_name = $evaluator->getReversedName();
                
                $sub_rows .= "<tr><td width='15%'>{$eval_name}</td><td style='padding:0px;'>";
                $sub_rows .= "<table style='border-collapse:collapse;' width='100%' rules='all'>";
                
                $additional_score = 0;
                //foreach(array('original', 'revised') as $ind => $rev){
                    $sub_row1 = "<tr><td width='10%' align='center'>Original</td>";
                    $sub_row2 = "<tr><td idth='10%' align='center'>Revised</td>";

                    $i=0;
                    foreach($radio_questions as $q){
                        $comment = "";
                        $comments = "";
                        
                        $comm = "";
                        $comm_short = array();
                        
                        $comm2 = "";
                        $comm_short2 = array();

                        if(isset($other_comments[$i]) && $other_comments[$i] != 0){
                            $comment = nl2br(RMC2015Tab::getData(BLOB_TEXT, $rtype, $other_comments[$i], $ni, $eval_id, 2015, $ni_id));
                            $comm = RMC2015Tab::getData(BLOB_ARRAY, $rtype, $stock_comments[$i], $ni, $eval_id, 2015, $ni_id);
                            //$comm = @$comm[$rev]; 
                            $comm2 = (isset($comm['revised']))? $comm['revised'] : array();
                            $comm = (isset($comm['original']))? $comm['original'] : array();
                            
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

                        $response = RMC2015Tab::getData(BLOB_ARRAY, $rtype,  $q, $ni, $eval_id, 2015, $ni_id);
                        //$response_orig = $response = @$response[$rev]; 
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
                                $comments = "<br /><div title='Other Comments' id='{$ni_id}_{$eval_id}_{$i}' style='display:none;'>$comment</div>";
                                $response = "<b>$response</b>";
                            }
                            $cell1 = "<td width='10%'><span class='q_tip' title='<b>{$response_orig}</b><ul>{$comm}</ul>'><a style='cursor:pointer;' onClick='$(\"#{$ni_id}_{$eval_id}_{$i}\").dialog({width:\"600px\"});'>{$response}</a></span>{$comments}</td>";
                        }else{
                            $response = "";
                            $cell1 = "<td width='10%'>{$response}</td>";
                        }
                        if($response_rev && ($diff != 0 || !empty($diff2))){
                            $response2 = substr($response2, 0, 1);
                            if(!empty($comm2)){
                                $response2 .= "; ".$comm_short2;
                                $comm2 = "<li>".implode("</li><li>", $comm2)."</li>";
                            }else{
                                $comm2 = "";
                            }
                            if($comment != ""){
                                $response2 = "<b>$response2</b>";
                            }
                            $cell2 = "<td width='10%'><span class='q_tip' title='<b>{$response_rev}</b><ul>{$comm2}</ul>'><a style='cursor:pointer;' onClick='$(\"#{$ni_id}_{$eval_id}_{$i}\").dialog({width:\"600px\"});'>{$response2}</a></span></td>";
                        }else{
                            $response2 = "";
                            $cell2 = "<td width='10%'>{$response2}</td>";
                        }
                        
                        if($q == EVL_OVERALLSCORE && $response_rev && isset($weights[$response_rev])){
                            $additional_score = $weights[$response_rev];
                        }
                        else if($q == EVL_OVERALLSCORE && $response_orig && isset($weights[$response_orig])){
                            $additional_score = $weights[$response_orig];
                        }

                        $sub_row1 .= $cell1;
                        $sub_row2 .= $cell2;

                        $i++;
                    }

                    $sub_row1 .= "</tr>";
                    $sub_row2 .= "</tr>";
                //}
                
                $sub_rows .= $sub_row1;
                $sub_rows .= $sub_row2;                
                $sub_rows .= "</table>";
                $sub_rows .= "</td></tr>";
                $ev_count++;
            }

            $sub_rows .= "</table>";

            $this->html .=<<<EOF
                <td style='padding:0px;'>
                {$sub_rows}
                </td>
                </tr>
EOF;
        }
        $this->html .= "</tbody></table><br />";
    }
    
    static function getData($blob_type, $rptype, $question, $sub, $eval_id=0, $evalYear=EVAL_YEAR, $proj_id=0){
        $addr = ReportBlob::create_address($rptype, SEC_NONE, $question, 0);
        $blb = new ReportBlob($blob_type, $evalYear, $eval_id, $proj_id);
        // echo "rptype=$rptype, section=$question, subid=".$sub->getId() .", blob_type=$blob_type, year=$evalYear, eval=$eval_id <br>";
        
        $data = "";
       
        $result = $blb->load($addr);
        
        $data = $blb->getData();
        
        return $data;
    }

}    
    
?>
