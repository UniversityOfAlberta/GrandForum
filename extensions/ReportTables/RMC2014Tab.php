<?php

class RMC2014Tab extends AbstractTab {

    function RMC2014Tab(){
        global $wgOut;
        parent::AbstractTab("2014");
        //$wgOut->setPageTitle("Evaluation Tables: RMC");
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath, $wgOut, $foldscript;
        $wgOut->addStyle("../extensions/Report/style/report.css");

        $tab1 = $tab2 = $tab3 = $tab4 = $tab5 = $tab6 = $tab7 = $tab8 = $tab9 = $tab10 = $tab11 = $tab12 = $tab13 = "";
        $tabs = array(
            'question1'=>"\$tab1 = 'selectedReportTab';",
            'question2'=>"\$tab2 = 'selectedReportTab';",
            'question3'=>"\$tab3 = 'selectedReportTab';",
            'question4'=>"\$tab4 = 'selectedReportTab';",
            'budget1'=>"\$tab5 = 'selectedReportTab';",    
            'budget2'=>"\$tab6 = 'selectedReportTab';",    
            'budget3'=>"\$tab7 = 'selectedReportTab';",
            'budget4'=>"\$tab8 = 'selectedReportTab';",
            'productivity'=>"\$tab9 = 'selectedReportTab';",    
            'researcher'=>"\$tab10 = 'selectedReportTab';",
            'contributions'=>"\$tab11 = 'selectedReportTab';",
            'distribution'=>"\$tab12 = 'selectedReportTab';", 
            'themes'=>"\$tab13 = 'selectedReportTab';",
        );
        $summary = ArrayUtils::get_string($_GET, 'summary');
        $url_year = ArrayUtils::get_string($_GET, 'year');
        if(!$summary){ 
            $summary = 'question1'; 
        }
        if(!$url_year){
            $url_year = "2014";
        }
        if($url_year == "2014"){
            eval(@$tabs[$summary]);
        }

        $this->html .=<<<EOF
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
        <script type='text/javascript'>
        $(document).ready(function(){
            $('span.q_tip').qtip({
                position: {
                    corner: {
                        target: 'topRight',
                        tooltip: 'bottomLeft'
                    }
                }, 
                style: {
                    classes: 'qtipStyle'
                }
            });
            $('.comment_dialog').dialog( "destroy" );
            $('.comment_dialog').dialog({ autoOpen: false, width: 600, height: 400 });
            $('.indexTable_2014').dataTable({
                'aLengthMenu': [[-1], ['All']],
                "bFilter": true,
                "aaSorting": [[0,'asc']],
                //"bSort": false
            });
            //$('.dataTables_filter input').css('width', 250);
        });  

        function openDialog(ev_id, sub_id, num){
            $('#dialog'+num+'-'+ev_id+'-'+sub_id).dialog("open");
        }
        </script>
        <div id="outerReport">
        <div class="displayTableCell">
        <div id="aboveTabs"></div>
        <div id="reportTabs">
            
            <a class="reportTab tooltip $tab1" href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2014&summary=question1#PNI_Summary'>1.1 PNI Questions 1-9</a>
            <a class="reportTab tooltip $tab2" href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2014&summary=question2#CNI_Summary'>1.2 CNI Questions 1-9</a>
            <a class="reportTab tooltip $tab3" href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2014&summary=question3#Project_Summary'>1.3 Project Questions 1-8</a>
            <a class="reportTab tooltip $tab4" href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2014&summary=question4#Champion_Summary'>1.4 Champion Questions</a>
            
            <a class="reportTab tooltip $tab5 marginTop" href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2014&summary=budget1#PNI_Budget_Summary'>2.1 PNI Budget Summary</a>
            <a class="reportTab tooltip $tab6" href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2014&summary=budget2#CNI_Budget_Summary'>2.2 CNI Budget Summary</a>
            <a class="reportTab tooltip $tab7" href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2014&summary=budget3#Project_Budget_Summary'>2.3 Project Budget Summary</span></a>
            <a class="reportTab tooltip $tab8" href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2014&summary=budget4#Full_Budget_Summary'>2.4 Full Budget Summary</a>

            <a class="reportTab tooltip $tab9 marginTop" href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2014&summary=productivity#Project_Productivity'>3.1 Project Productivity</a>
            <a class="reportTab tooltip $tab10" href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2014&summary=researcher#Researcher_Productivity'>3.2 Researcher Productivity</a>
            <a class="reportTab tooltip $tab11" href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2014&summary=contributions#Uni_Contributions'>3.3 Contributions by University</a>
            <a class="reportTab tooltip $tab12" href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2014&summary=distribution#Distribution'>3.4 HQP Distribution</a>
            <a class="reportTab tooltip $tab13" href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2014&summary=themes#Themes'>3.5 Project Themes</a>

            <a class="reportTab tooltip marginTop" href='$wgServer$wgScriptPath/index.php/Special:ReviewResults?type=PNI'>PNI Review Results</a>
            <a class="reportTab tooltip" href='$wgServer$wgScriptPath/index.php/Special:ReviewResults?type=CNI'>CNI Review Results</a>
        </div>
        </div>

        <div id="reportMain1" class="displayTableCell" style="padding-left:20px; width:10000px; max-width:10000px;">
EOF;

    

        //$this->showContentsTable();

        if($url_year == "2014"){
        switch ($summary) {
        case 'question1':
            $this->html .= "<a id='Q_Summary'></a>";
            //$this->html .= "<h2>Summary of 1-9</h2>";
            $this->html .= "<a id='".PNI."_Summary'></a>";
            //$this->showEvalTableFor(PNI);
            //$this->exportEvalNIOverview(PNI);
            $this->showEvalNIOverview(PNI);

            break;
        
        case 'exportPNI':
            $this->exportEvalNIOverview(PNI);
            break;

        case 'question2':
            $this->html .= "<a id='Q_Summary'></a>";
            //$this->html .= "<h2>Summary of 1-9</h2>";
            $this->html .= "<a id='".CNI."_Summary'></a>";
            //$this->showEvalTableFor(CNI);
            $this->showEvalNIOverview(CNI);
            break;

        case 'question3':
            $this->html .= "<a id='Q_Summary'></a>";
            //$this->html .= "<h2>Summary of 1-9</h2>";
            $this->html .= "<a id='Project_Summary'></a>";
            //$this->showEvalTableFor("Project");
            $this->showEvalProjectOverview();
            break;
            
        case 'question4':
            $this->html .= "<a id='Q_Summary'></a>";
            //$this->html .= "<h2>Summary of 1-9</h2>";
            $this->html .= "<a id='Champion_Summary'></a>";
            //$this->showEvalTableFor("Project");
            $this->showEvalChampionOverview();
            break;

        case 'budget1':
            $this->html .= "<a id='Budget_Summary'></a>";
            //$this->html .= "<h2>Budget Summaries</h2>";
            $this->html .= "<a id='".PNI."_Budget_Summary'></a>";
            $this->showBudgetTableFor(PNI);
            break;

        case 'budget2':
            $this->html .= "<a id='Budget_Summary'></a>";
            //$this->html .= "<h2>Budget Summaries</h2>";
            $this->html .= "<a id='".CNI."_Budget_Summary'></a>";
            $this->showBudgetTableFor(CNI);
            break;

        case 'budget3':
            $this->html .= "<a id='Budget_Summary'></a>";
            //$this->html .= "<h2>Budget Summaries</h2>";
            $this->html .= "<a id='Project_Budget_Summary'></a>";
            $this->showBudgetTableFor("Project");
            break;

        case 'budget4':
            $this->html .= "<a id='Budget_Summary'></a>";
            //$this->html .= "<h2>Budget Summaries</h2>";
            $this->html .= "<a id='Full_Budget_Summary'></a>";
            $this->showBudgetTableFor("Full");
            break;

        case 'productivity':
            // Project productivity.
            $wgOut->addScript($foldscript);
            $this->html .= "<h3>Project Productivity</h3><a id='Project_Productivity'></a>";
            self::showProjectProductivity();
            break;

        case 'researcher':
            // Researcher productivity.
            $wgOut->addScript($foldscript);
            $this->html .= "<h3>Researcher Productivity</h3><a id='Researcher_Productivity'></a>";
            self::showResearcherProductivity();
            break;

        case 'contributions':
            $wgOut->addScript($foldscript);
            $this->html .= "<h3>Contributions by University</h3><a id='Uni_Contributions'></a>";
            //self::showOtherContributions();
            self::getUniContributionStats();
            break;

        case 'distribution':
            // HQP distribution.
            $this->html .= "<h3>HQP Distribution</h3><a id='Distribution'></a>";
            self::showDistribution();
            break;

        case 'themes':
            $this->html .= "<h3>Project Themes</h3><a id='Themes'></a>";
            self::showThemes();
            break;
        
        // case 'reviewresults_pni':
        //     $this->html .= "<a id='ReviewResults'></a>";
        //     self::reviewResults('PNI');
        //     break;
        }
        }

        $this->html .= "</div></div>";
        
        return $this->html;
    }

    function showContentsTable(){
        global $wgServer, $wgScriptPath;
        $this->html2 .=<<<EOF
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
                        corner: {
                            target: 'topRight',
                            tooltip: 'bottomLeft'
                        }
                    }, 
                    style: {
                        classes: 'qtipStyle'
                    }
                });
                $('.comment_dialog').dialog( "destroy" );
                $('.comment_dialog').dialog({ autoOpen: false, width: 600, height: 400 });
                $('.indexTable_2014').dataTable({
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
            <table class='toc' summary='Contents'>
            <tr><td>
            <div id='toctitle'><h2>Contents</h2></div>
            <ul>
            <li class='toclevel-1'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2014&summary=question#Q_Summary'><span class='tocnumber'>1</span> <span class='toctext'>Summary of 1-7</span></a>
                <ul>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2014&summary=question#PNI_Summary'><span class='tocnumber'>1.1</span> <span class='toctext'>PNI Summary of Questions 1-9</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2014&summary=question#CNI_Summary'><span class='tocnumber'>1.2</span> <span class='toctext'>CNI Summary of Questions 1-9</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2014&summary=question#Project_Summary'><span class='tocnumber'>1.3</span> <span class='toctext'>Project Summary of Questions 1-8</span></a></li>
                </ul>
            </li>
            <li class='toclevel-1'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2014&summary=budget#Budget_Summary'><span class='tocnumber'>2</span> <span class='toctext'>Budget Summaries</span></a>
                <ul>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2014&summary=budget#PNI_Budget_Summary'><span class='tocnumber'>2.1</span> <span class='toctext'>PNI Budget Summary</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2014&summary=budget#CNI_Budget_Summary'><span class='tocnumber'>2.2</span> <span class='toctext'>CNI Budget Summary</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2014&summary=budget#Project_Budget_Summary'><span class='tocnumber'>2.3</span> <span class='toctext'>Project Budget Summary</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2014&summary=budget#Full_Budget_Summary'><span class='tocnumber'>2.4</span> <span class='toctext'>Full Budget Summary</span></a></li>
                </ul>
            </li>
            <li class='toclevel-1'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2014&summary=productivity#Other'><span class='tocnumber'>3</span> <span class='toctext'>Other</span></a>
                <ul>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2014&summary=productivity#Project_Productivity'><span class='tocnumber'>3.1</span> <span class='toctext'>Project Productivity</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2014&summary=researcher#Researcher_Productivity'><span class='tocnumber'>3.2</span> <span class='toctext'>Researcher Productivity</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2014&summary=contributions#Uni_Contributions'><span class='tocnumber'>3.3</span> <span class='toctext'>Contributions by University</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2014&summary=distribution#Distribution'><span class='tocnumber'>3.4</span> <span class='toctext'>HQP Distribution</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2014&summary=themes#Themes'><span class='tocnumber'>3.5</span> <span class='toctext'>Project Themes</span></a></li>
                </ul>
            </li>
            </td></tr>
         </table>
EOF;

    }

    function showEvalNIOverview($type){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $foldscript, $reporteeId, $getPerson;
        if($type == CNI){
            $rtype = RP_EVAL_CNI;
        }
        else if($type == PNI){
            $rtype = RP_EVAL_RESEARCHER;
        }

        $weights = array('Top'=>4, 'Upper Middle'=>3, 'Lower Middle'=>2, 'Bottom'=>1);
        $aves = array('4'=>2, '3'=>1.5, '2'=>1, '1'=>0.5);

        // Check for a download.
        $action = ArrayUtils::get_string($_GET, 'getpdf');
        if ($action !== "") {
            $p = Person::newFromId($wgUser->getId());
            $sto = new ReportStorage($p);
            $wgOut->disable();
            return $sto->trigger_download($action, "{$action}.pdf", false);
        }

        $this->html .=<<<EOF
        <h3>$type Summary of Questions 1-9</h3>
        <table class='indexTable indexTable_2014' frame="box" rules="all">
        <thead>
        <tr>
            <th width="20%">$type</th>
            <th style='padding:0px;' width="5%">Ave. (Q7)</th>
            <th style='padding:0px;' >
            <span class='tableHeader' style="width: 19.978%;">Evaluator</span>
            <span class='tableHeader' style="width: 15.9%;">Q8</span>
            <span class='tableHeader' style="width: 7.9%;">Q7</span>
            <span class='tableHeader' style="width: 7.9%;">Q9</span>
            <span class='tableHeader' style="width: 7.83%;">Q1</span>
            <span class='tableHeader' style="width: 7.83%;">Q2</span>
            <span class='tableHeader' style="width: 7.8%;">Q3</span>
            <span class='tableHeader' style="width: 7.8%;">Q4</span>
            <span class='tableHeader' style="width: 7.8%;">Q5</span>
            <span class='tableHeader' style="width: 7.7%;">Q6</span>
            </th>
        </tr>
        </thead>
        <tbody>
EOF;
        $text_question = EVL_OTHERCOMMENTS;
        $radio_questions = array(EVL_OVERALLSCORE, EVL_CONFIDENCE, EVL_EXCELLENCE, EVL_HQPDEVELOPMENT, EVL_NETWORKING, EVL_KNOWLEDGE, EVL_MANAGEMENT, EVL_REPORTQUALITY);
        $stock_comments = array(0,0, EVL_EXCELLENCE_COM, EVL_HQPDEVELOPMENT_COM, EVL_NETWORKING_COM, EVL_KNOWLEDGE_COM, EVL_MANAGEMENT_COM, EVL_REPORTQUALITY_COM);

        $nis = Person::getAllEvaluates($type, 2013);
        $sorted_nis = array();
        foreach ($nis as $n){
            $sorted_nis[$n->getId()] = $n->getReversedName();
        }
        asort($sorted_nis);

        foreach($sorted_nis as $ni_id => $ni_name){
            $ni = Person::newFromId($ni_id);
            //$ni_id = $ni->getId();
            //$ni_name = $ni->getReversedName();
            $evaluators = $ni->getEvaluators($type, 2013);

            $rowspan = count($evaluators);
            if($rowspan == 0){
                continue;
            }
            $rowspan = $rowspan*2;

            $download1 = "Researcher PDF";
            $report = new DummyReport("NIReport", $ni, null, 2013);
            $tok = false;
            $check = $report->getPDF();
            if (count($check) > 0) {
                $tok = $check[0]['token'];
                $download1 = "<a href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$tok}'>Researcher PDF</a>";
            }

            $download2 = "PL Comments PDF";
            $report = new DummyReport("ProjectNIComments", $ni, null, 2013);
            $tok = false;
            $check = $report->getPDF();
            if (count($check) > 0) {
                $tok = $check[0]['token'];
                $download2 = "<a href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$tok}'>PL Comments PDF</a>";
            }

            $this->html .=<<<EOF
            <tr>
            <td>
            <b>{$ni_name}</b><br />
            {$download1}<br />
            {$download2}
            </td>
EOF;
            $average_score = 0;
            $sub_rows = "<table width='100%' rules='all'>";
            $div_count = 0;
            $ev_count = 0;
            foreach($evaluators as $evaluator) {
                $eval_id = $evaluator->getId();
                $eval_name = $evaluator->getReversedName();
                
                $sub_rows .= "<tr><td width='20%'>{$eval_name}</td><td style='padding:0px;'>";
                $sub_rows .= "<table width='100%' rules='all'>";

                $additional_score = 0;
                //foreach(array('original', 'revised') as $ind => $rev){
                    $sub_row1 = "<tr>";
                    $sub_row2 = "<tr>";

                    $q8 = RMC2014Tab::getData(BLOB_ARRAY, $rtype, $text_question, $ni, $eval_id, 2013);
                    $q8_O = (isset($q8['original']))? $q8['original'] : "";
                    $q8_R = (isset($q8['revised']))? $q8['revised'] : "";
                    $diff = strcmp($q8_O, $q8_R);

                    //$q8 = @$q8[$rev]; 
                    //$q8 = nl2br($q8); 
                    //$comm_label = ucfirst($rev); 
                    if(!empty($q8_O)){
                        $q8_O = nl2br($q8_O);
                        $cell1 =<<<EOF
                            <a href='#' onclick='openDialog("{$eval_id}", "{$ni_id}", "1"); return false;'>Original</a>
                            <div id='dialog1-{$eval_id}-{$ni_id}' class='comment_dialog' title='Original Comment by {$eval_name} on {$ni_name}'>
                            {$q8_O}
                            </div>
EOF;
                    }else{
                        $cell1 = "Original";
                    }
                    if(!empty($q8_R) && $diff != 0){
                        $q8_R = nl2br($q8_R);
                        $cell2 =<<<EOF
                            <a href='#' onclick='openDialog("{$eval_id}", "{$ni_id}", "2"); return false;'>Revised</a>
                            <div id='dialog2-{$eval_id}-{$ni_id}' class='comment_dialog' title='Revised Comment by {$eval_name} on {$ni_name}'>
                            {$q8_R}
                            </div>
EOF;
                    }
                    else{
                        $cell2 = "Revised";
                    }
                    
                    $sub_row1 .= "<td width='20%'>{$cell1}</td>";
                    $sub_row2 .= "<td width='20%'>{$cell2}</td>";

                    $i=0;
                    foreach($radio_questions as $q){
                        $comm = "";
                        $comm_short = array();

                        $comm2 = "";
                        $comm_short2 = array();
                        
                        if($i>1){
                            $comm = RMC2014Tab::getData(BLOB_ARRAY, $rtype, $stock_comments[$i], $ni, $eval_id, 2013);
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

                        $response = RMC2014Tab::getData(BLOB_ARRAY, $rtype,  $q, $ni, $eval_id, 2013);
                        //$response_orig = $response = @$response[$rev];
                        $response_orig = (isset($response['original']))? $response['original'] : "";
                        $response_rev = $response2 = (isset($response['revised']))? $response['revised'] : "";
                        $diff = strcmp($response_orig, $response_rev);
                        $diff2 = array();
                        if($i>1){
                            $diff2 = array_merge(array_diff(array_filter($comm), array_filter($comm2)), 
                                                 array_diff(array_filter($comm2), array_filter($comm)));
                        }
                        
                        $response = $response_orig;

                        if($response_orig){
                            $response = substr($response, 0, 1);
                            if(!empty($comm)){
                                $response .= "; ".$comm_short;
                                $comm = implode("<br />", $comm);
                            }
                            $cell1 = "<td width='10%'><span class='q_tip' title='{$response_orig}<br />{$comm}'><a href='#'>{$response}</a></span></td>";
                        }else{
                            $response = "";
                            $cell1 = "<td width='10%'>{$response}</td>";
                        }

                        if($response_rev && ($diff != 0 || !empty($diff2))){
                            $response2 = substr($response2, 0, 1);
                            if(!empty($comm2)){
                                $response2 .= "; ".$comm_short2;
                                $comm2 = implode("<br />", $comm2);
                            }
                            else{
                                $comm2 = "";
                            }
                            $cell2 = "<td width='10%'><span class='q_tip' title='{$response_rev}<br />{$comm2}'><a href='#'>{$response2}</a></span></td>";
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

                if($additional_score){
                    $average_score += $aves[$additional_score]*$additional_score;
                    $div_count++;
                }

                $sub_rows .= $sub_row1;
                $sub_rows .= $sub_row2;
                $sub_rows .= "</table>";
                $sub_rows .= "</td></tr>";
                $ev_count++;
            }

            $sub_rows .= "</table>";
            if($div_count > 0){
                $average_score = round($average_score/$div_count, 2);
            }
            else{
                $average_score = "N/A";
            }

            $this->html .=<<<EOF
                <td align='center'>{$average_score}</td>
                <td style='padding:0px;'>
                {$sub_rows}
                </td>
                </tr>
EOF;
        }
        $this->html .= "</tbody></table><br />";
    }

    function exportEvalNIOverview($type){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $foldscript, $reporteeId, $getPerson;
        if($type == CNI){
            $rtype = RP_EVAL_CNI;
        }
        else if($type == PNI){
            $rtype = RP_EVAL_RESEARCHER;
        }

        $weights = array('Top'=>4, 'Upper Middle'=>3, 'Lower Middle'=>2, 'Bottom'=>1);
        $aves = array('4'=>2, '3'=>1.5, '2'=>1, '1'=>0.5);

        // Check for a download.
        $action = ArrayUtils::get_string($_GET, 'getpdf');
        if ($action !== "") {
            $p = Person::newFromId($wgUser->getId());
            $sto = new ReportStorage($p);
            $wgOut->disable();
            return $sto->trigger_download($action, "{$action}.pdf", false);
        }
        
        $csv =<<<EOF
`$type`,`Ave. (Q7)`,`Evaluator`,`Q8`,`Q7`,`Q9`,`Q1`,`Q2`,`Q3`,`Q4`,`Q5`,`Q6`\n
EOF;

        $text_question = EVL_OTHERCOMMENTS;
        $radio_questions = array(EVL_OVERALLSCORE, EVL_CONFIDENCE, EVL_EXCELLENCE, EVL_HQPDEVELOPMENT, EVL_NETWORKING, EVL_KNOWLEDGE, EVL_MANAGEMENT, EVL_REPORTQUALITY);
        $stock_comments = array(0,0, EVL_EXCELLENCE_COM, EVL_HQPDEVELOPMENT_COM, EVL_NETWORKING_COM, EVL_KNOWLEDGE_COM, EVL_MANAGEMENT_COM, EVL_REPORTQUALITY_COM);

        $nis = Person::getAllEvaluates($type, 2013);
        $sorted_nis = array();
        foreach ($nis as $n){
            $sorted_nis[$n->getId()] = $n->getReversedName();
        }
        asort($sorted_nis);

        foreach($sorted_nis as $ni_id => $ni_name){
            $ni = Person::newFromId($ni_id);
            //$ni_id = $ni->getId();
            //$ni_name = $ni->getReversedName();
            $evaluators = $ni->getEvaluators($type, 2013);

            $rowspan = count($evaluators);
            if($rowspan == 0){
                continue;
            }
            $rowspan = $rowspan*2;

            //$this->html .=<<<EOF
            //    "{$ni_name}",
//EOF;

            $average_score = 0;
            $sub_rows = "";
            $div_count = 0;
            $ev_count = 0;
            $sub_rows1 = array();
            $sub_rows2 = array();
            foreach($evaluators as $evaluator) {
                $eval_id = $evaluator->getId();
                $eval_name = $evaluator->getReversedName();
                
                $sub_rows1[$ev_count] = "`{$eval_name}`,";
                $sub_rows2[$ev_count] = "`{$eval_name}`,";

                $additional_score = 0;
                //foreach(array('original', 'revised') as $ind => $rev){
                    $sub_row1 = "";
                    $sub_row2 = "";

                    $q8 = RMC2014Tab::getData(BLOB_ARRAY, $rtype, $text_question, $ni, $eval_id, 2013);
                    $q8_O = (isset($q8['original']))? $q8['original'] : "";
                    $q8_R = (isset($q8['revised']))? $q8['revised'] : "";
                    $diff = strcmp($q8_O, $q8_R);

                    //$q8 = @$q8[$rev]; 
                    //$q8 = nl2br($q8); 
                    //$comm_label = ucfirst($rev); 
                    if(!empty($q8_O)){
                        //$q8_O = addslashes($q8_O);
                        $cell1 =<<<EOF
`{$q8_O}`
EOF;
                    }else{
                        $cell1 = "`Original:N/A`";
                    }
                    if(!empty($q8_R) && $diff != 0){
                        //$q8_R = addslashes($q8_R);
                        $cell2 =<<<EOF
`{$q8_R}`
EOF;
                    }
                    else{
                        $cell2 = "`Revised:N/A`";
                    }
                    
                    $sub_row1 .= "{$cell1},";
                    $sub_row2 .= "{$cell2},";

                    $i=0;
                    foreach($radio_questions as $q){
                        $comm = "";
                        $comm_short = array();

                        $comm2 = "";
                        $comm_short2 = array();
                        
                        if($i>1){
                            $comm = RMC2014Tab::getData(BLOB_ARRAY, $rtype, $stock_comments[$i], $ni, $eval_id, 2013);
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

                        $response = RMC2014Tab::getData(BLOB_ARRAY, $rtype,  $q, $ni, $eval_id, 2013);
                        //$response_orig = $response = @$response[$rev];
                        $response_orig = (isset($response['original']))? $response['original'] : "";
                        $response_rev = $response2 = (isset($response['revised']))? $response['revised'] : "";
                        $diff = strcmp($response_orig, $response_rev);
                        $diff2 = array();
                        if($i>1){
                            $diff2 = array_merge(array_diff(array_filter($comm), array_filter($comm2)), 
                                                 array_diff(array_filter($comm2), array_filter($comm)));
                        }
                        
                        $response = $response_orig;

                        if($response_orig){
                            $response = substr($response, 0, 1);
                            if(!empty($comm)){
                                $response .= "; ".$comm_short;
                                $comm = "\n". implode("\n", $comm);
                            }else{
                                $comm = "";
                            } 
                            //$cell1 = "<td width='10%'><span class='q_tip' title='{$response_orig}<br />{$comm}'><a href='#'>{$response}</a></span></td>";
                            $cell1 = "`{$response_orig}{$comm}`,";
                        }else{
                            //$response = "";
                            $cell1 = "``,";
                        }

                        if($response_rev && ($diff != 0 || !empty($diff2))){
                            $response2 = substr($response2, 0, 1);
                            if(!empty($comm2)){
                                $response2 .= "; ".$comm_short2;
                                $comm2 = "\n". implode("\n", $comm2);
                            }else{
                                $comm2 = "";
                            } 

                            //$cell2 = "<td width='10%'><span class='q_tip' title='{$response_rev}<br />{$comm2}'><a href='#'>{$response2}</a></span></td>";
                            $cell2 = "`{$response_rev}{$comm2}`,";
                        }else{
                            //$response2 = "";
                            $cell2 = "``,";
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

                    //$sub_row1 .= "\n";
                    //$sub_row2 .= "\n";
                //}

                if($additional_score){
                    $average_score += $aves[$additional_score]*$additional_score;
                    $div_count++;
                }

                $sub_rows1[$ev_count] .= $sub_row1;
                $sub_rows2[$ev_count] .= $sub_row2;
               // $sub_rows .= "</table>";
               // $sub_rows .= "</td></tr>";
                $ev_count++;
            }

            //$sub_rows .= "</table>";
            if($div_count > 0){
                $average_score = round($average_score/$div_count, 2);
            }
            else{
                $average_score = "N/A";
            }

            for ($i=0; $i<$ev_count; $i++){
                $sr1 = trim($sub_rows1[$i], ',');
                $sr2 = trim($sub_rows2[$i], ',');
                
                $csv .=<<<EOF
`{$ni_name}`,`{$average_score}`,{$sr1}
`{$ni_name}`,`{$average_score}`,{$sr2}\n
EOF;
            }
            
        }
        
        //$this->html .= "</tbody></table><br />";
        $wgOut->disable();
        header('Content-Type: application/csv');
        header("Content-Disposition: attachment; filename=export-{$type}-reviews.csv");
        header('Pragma: no-cache');
        echo $csv;
    }

    function showEvalProjectOverview(){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $foldscript, $reporteeId, $getPerson;
        $me = Person::newFromWgUser();
        $type = 'Project';
        $rtype = RP_EVAL_PROJECT;

        $weights = array('Top'=>4, 'Upper Middle'=>3, 'Lower Middle'=>2, 'Bottom'=>1);
        $aves = array('4'=>2, '3'=>1.5, '2'=>1, '1'=>0.5);

        // Check for a download.
        $action = ArrayUtils::get_string($_GET, 'getpdf');
        if ($action !== "") {
            $p = Person::newFromId($wgUser->getId());
            $sto = new ReportStorage($p);
            $wgOut->disable();
            return $sto->trigger_download($action, "{$action}.pdf", false);
        }
        
        $this->html .=<<<EOF
        <h3>$type Summary of Questions 1-8</h3>
        <table class='indexTable indexTable_2014' cellspacing='1' cellpadding='3' style='border-style:solid;' width='100%' frame="box" rules="all">
        <thead>
        <tr>
            <th style='background: #EEEEEE;' width="10%">$type</th>
            <th style='background: #EEEEEE; padding:0px;' width="10%">ISAC</th>
            <th style='background: #EEEEEE; padding:0px;' width="5%">Ave. (Q6)</th>
            <th style='background: #EEEEEE; padding:0px;' >
                <span class='tableHeader' style="width: 19.978%;">Evaluator</span>
                <span class='tableHeader' style="width: 17.6%;">Q7</span>
                <span class='tableHeader' style="width: 8.7%;">Q6</span>
                <span class='tableHeader' style="width: 8.7%;">Q8</span>
                <span class='tableHeader' style="width: 8.7%;">Q1</span>
                <span class='tableHeader' style="width: 8.7%;">Q2</span>
                <span class='tableHeader' style="width: 8.7%;">Q3</span>
                <span class='tableHeader' style="width: 8.7%;">Q4</span>
                <span class='tableHeader' style="width: 8.7%;">Q5</span>
            </th>
        </tr>
        </thead>
        <tbody>
EOF;
        $text_question = EVL_OTHERCOMMENTS;
        $radio_questions = array(EVL_OVERALLSCORE, EVL_CONFIDENCE, EVL_EXCELLENCE, EVL_HQPDEVELOPMENT, EVL_NETWORKING, EVL_KNOWLEDGE, EVL_REPORTQUALITY);
        $stock_comments = array(0,0, EVL_EXCELLENCE_COM, EVL_HQPDEVELOPMENT_COM, EVL_NETWORKING_COM, EVL_KNOWLEDGE_COM, EVL_REPORTQUALITY_COM);

        $projects = Person::getAllEvaluates($type, 2013);
        $isac = Person::getAllPeople(ISAC);
        $sorted_projects = array();
        foreach ($projects as $p){
            $sorted_projects[$p->getId()] = $p->getName();
        }
        asort($sorted_projects);
        
        foreach($sorted_projects as $ni_id => $ni_name){
            $ni = Project::newFromId($ni_id);
            //$ni_id = $ni->getId();
            //$ni_name = $ni->getReversedName();
            $evaluators = $ni->getEvaluators(2013);

            $rowspan = count($evaluators);
            if($rowspan == 0){
                continue;
            }
            $rowspan = $rowspan*2;

            $download = "<br />Project PDF";
            $isac_download = "";
            $report = new DummyReport("ProjectReport", $ni, $ni, 2013);
            $tok = false;
            $check = $report->getPDF();
            if(count($check) > 0) {
                $pdf = PDF::newFromToken($check[0]['token']);
                $download = "<br /><a href='{$pdf->getUrl()}' target='_blank'>Project PDF</a>";
            }
            $isac_report = new DummyReport("ProjectISACCommentsPDF", $me, $ni);
            $data = $isac_report->getPDF();
            if(isset($data[0]['token'])){
                $pdf = PDF::newFromToken($data[0]['token']);
                $isac_download = "<br /><a href='{$pdf->getUrl()}' target='_blank'>ISAC PDF</a>";
            }
            $isac_html = "";
            foreach($isac as $i => $person){
                $id = "{$ni->getId()}_{$person->getId()}_$i";
                $addr = ReportBlob::create_address(RP_ISAC, ISAC_PHASE2, ISAC_PHASE2_COMMENT, 0);
                $blb = new ReportBlob(BLOB_TEXT, 2013, $person->getId(), $ni_id);
                $result = $blb->load($addr);
                $data = $blb->getData();
                if($data != null){
                    $isac_html .= "<a style='cursor:pointer;' onClick='$(\"div#$id\").dialog({width: 700});'>{$person->getReversedName()}</a><div id='$id' title='{$ni->getName()}: {$person->getReversedName()}' class='dialog' style='display:none;'>".nl2br($data)."</div><br />";
                }
            }

            $this->html .=<<<EOF
            <tr>
            <td>
            <b>{$ni_name}</b>
            {$download}
            {$isac_download}
            </td>
            <td>$isac_html</td>
EOF;
            $average_score = 0;
            $sub_rows = "<table style='border-collapse:collapse;' width='100%' rules='none'>";
            $div_count = 0;
            $ev_count = 0;
            foreach($evaluators as $evaluator) {
                $eval_id = $evaluator->getId();
                $eval_name = $evaluator->getReversedName();
                
                $sub_rows .= "<tr><td width='20%'>{$eval_name}</td><td style='padding:0px;'>";
                $sub_rows .= "<table style='border-collapse:collapse;' width='100%' rules='all'>";
                
                $additional_score = 0;
                //foreach(array('original', 'revised') as $ind => $rev){
                    $sub_row1 = "<tr>";
                    $sub_row2 = "<tr>";
                    
                    $q8 = RMC2014Tab::getData(BLOB_ARRAY, $rtype, $text_question, $ni, $eval_id, 2013, $ni_id);
                    //$q8 = @$q8[$rev]; 
                    $q8_O = (isset($q8['original']))? $q8['original'] : "";
                    $q8_R = (isset($q8['revised']))? $q8['revised'] : "";
                    $diff = strcmp($q8_O, $q8_R);

                    //$q8 = nl2br($q8);
                    //$comm_label = ucfirst($rev);
                    if(!empty($q8)){
                        $cell1 =<<<EOF
                            <a href='#' onclick='openDialog("{$eval_id}", "{$ni_id}", 1); return false;'>Original</a>
                            <div id='dialog1-{$eval_id}-{$ni_id}' class='comment_dialog' title='Original Comment by {$eval_name} on {$ni_name}'>
                            {$q8_O}
                            </div>
EOF;
                    }else{
                        $cell1 = "Original";
                    }
                    if(!empty($q8_R) && $diff != 0){
                        $q8_R = nl2br($q8_R);
                        $cell2 =<<<EOF
                            <a href='#' onclick='openDialog("{$eval_id}", "{$ni_id}", "2"); return false;'>Revised</a>
                            <div id='dialog2-{$eval_id}-{$ni_id}' class='comment_dialog' title='Revised Comment by {$eval_name} on {$ni_name}'>
                            {$q8_R}
                            </div>
EOF;
                    }
                    else{
                        $cell2 = "Revised";
                    }

                    $sub_row1 .= "<td width='20%'>{$cell1}</td>";
                    $sub_row2 .= "<td width='20%'>{$cell2}</td>";

                    $i=0;
                    foreach($radio_questions as $q){
                        $comm = "";
                        $comm_short = array();
                        
                        $comm2 = "";
                        $comm_short2 = array();

                        if($i>1){
                            $comm = RMC2014Tab::getData(BLOB_ARRAY, $rtype, $stock_comments[$i], $ni, $eval_id, 2013, $ni_id);
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

                        $response = RMC2014Tab::getData(BLOB_ARRAY, $rtype,  $q, $ni, $eval_id, 2013, $ni_id);
                        //$response_orig = $response = @$response[$rev]; 
                        $response_orig = (isset($response['original']))? $response['original'] : "";
                        $response_rev = $response2 = (isset($response['revised']))? $response['revised'] : "";
                        $diff = strcmp($response_orig, $response_rev);
                        $diff2 = array();
                        if($i>1){
                            $diff2 = array_merge(array_diff(array_filter($comm), array_filter($comm2)), 
                                                 array_diff(array_filter($comm2), array_filter($comm)));
                        }
                        
                        $response = $response_orig;


                        if($response_orig){
                            $response = substr($response, 0, 1);
                            if(!empty($comm)){
                                $response .= "; ".$comm_short;
                                $comm = implode("<br />", $comm);
                            } 
                            $cell1 = "<td width='10%'><span class='q_tip' title='{$response_orig}<br />{$comm}'><a href='#'>{$response}</a></span></td>";
                        }else{
                            $response = "";
                            $cell1 = "<td width='10%'>{$response}</td>";
                        }
                        if($response_rev && ($diff != 0 || !empty($diff2))){
                            $response2 = substr($response2, 0, 1);
                            if(!empty($comm2)){
                                $response2 .= "; ".$comm_short2;
                                $comm2 = implode("<br />", $comm2);
                            } 
                            $cell2 = "<td width='10%'><span class='q_tip' title='{$response_rev}<br />{$comm2}'><a href='#'>{$response2}</a></span></td>";
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

                if($additional_score){
                    $average_score += $aves[$additional_score]*$additional_score;
                    $div_count++;
                }
                
                $sub_rows .= $sub_row1;
                $sub_rows .= $sub_row2;                
                $sub_rows .= "</table>";
                $sub_rows .= "</td></tr>";
                $ev_count++;
            }

            $sub_rows .= "</table>";
            if($div_count > 0){
                $average_score = round($average_score/$div_count, 2);
            }
            else{
                $average_score = "N/A";
            }

            $this->html .=<<<EOF
                <td align='center'>{$average_score}</td>
                <td style='padding:0px;'>
                {$sub_rows}
                </td>
                </tr>
EOF;
        }
        $this->html .= "</tbody></table><br />";
    }
    
    function showEvalChampionOverview(){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $foldscript, $reporteeId, $getPerson;
        $me = Person::newFromWgUser();
        $type = 'Project';
        $rtype = RP_EVAL_PROJECT;

        $weights = array('Top'=>4, 'Upper Middle'=>3, 'Lower Middle'=>2, 'Bottom'=>1);
        $aves = array('4'=>2, '3'=>1.5, '2'=>1, '1'=>0.5);

        // Check for a download.
        $action = ArrayUtils::get_string($_GET, 'getpdf');
        if ($action !== "") {
            $p = Person::newFromId($wgUser->getId());
            $sto = new ReportStorage($p);
            $wgOut->disable();
            return $sto->trigger_download($action, "{$action}.pdf", false);
        }
        
        $this->html .=<<<EOF
        <h3>Project Champion Summary of Questions 1-6</h3>
        <table class='indexTable indexTable_2014' cellspacing='1' cellpadding='3' style='border-style:solid;' width='100%' frame="box" rules="all">
        <thead>
        <tr>
            <th style='background: #EEEEEE;' width="10%">Project</th>
            <th style='background: #EEEEEE; padding:0px;'>
                <span class='tableHeader' style="width: 15%;">Champion</span>
                <span class='tableHeader' style="width: 12.5%;">Q1</span>
                <span class='tableHeader' style="width: 12.5%;">Q2</span>
                <span class='tableHeader' style="width: 12.5%;">Q3</span>
                <span class='tableHeader' style="width: 12.5%;">Q4</span>
                <span class='tableHeader' style="width: 12.5%;">Q5</span>
                <span class='tableHeader' style="width: 20%;">Q6</span>
            </th>
        </tr>
        </thead>
        <tbody>
EOF;
        $text_question = EVL_OTHERCOMMENTS;
        $radio_questions = array(EVL_OVERALLSCORE, EVL_CONFIDENCE, EVL_EXCELLENCE, EVL_HQPDEVELOPMENT, EVL_NETWORKING, EVL_KNOWLEDGE, EVL_REPORTQUALITY);
        $stock_comments = array(0,0, EVL_EXCELLENCE_COM, EVL_HQPDEVELOPMENT_COM, EVL_NETWORKING_COM, EVL_KNOWLEDGE_COM, EVL_REPORTQUALITY_COM);

        $projects = Project::getAllProjects();
        $sorted_projects = array();
        foreach ($projects as $p){
            if($p->getPhase() == 2){
                $sorted_projects[$p->getId()] = $p->getName();
            }
        }
        asort($sorted_projects);
        
        foreach($sorted_projects as $proj_id => $proj_name){
            $project = Project::newFromId($proj_id);
            $proj_pdf = "";
            $report = new DummyReport("ProjectChampionsReportPDF", $me, $project, 2013);
            $data = $report->getPDF();
            if(isset($data[0]['token'])){
                $pdf = PDF::newFromToken($data[0]['token']);
                $proj_pdf = "<a href='{$pdf->getUrl()}' target='_blank'>Champions PDF</a>";;
            }
            $this->html .= "<tr>";
            $this->html .= "<td><b>{$proj_name}</b><br />{$proj_pdf}</td>";
            
            $champion_html = "<table style='border-collapse:collapse;' width='100%' rules='none'>";
            $champions = array();
            foreach($project->getChampionsDuring('2014'.REPORTING_PRODUCTION_MONTH, '2014'.REPORTING_RMC_MEETING_MONTH) as $champ){
                $champions[$champ['user']->getId()] = $champ;
            }
            foreach($project->getSubProjects() as $sub){
                foreach($sub->getChampionsDuring('2014'.REPORTING_PRODUCTION_MONTH, '2014'.REPORTING_RMC_MEETING_MONTH) as $champ){
                    $champions[$champ['user']->getId()] = $champ;
                }
            }
            foreach($champions as $champ){
                $result = (!$champ['user']->isChampionOfOn($project, '2014'.REPORTING_RMC_MEETING_MONTH.' 23:59:59')) ? "style='text-decoration:line-through;'" : "";
                $result = $champ['user']->isChampionOfOn($project, '2014'.REPORTING_RMC_MEETING_MONTH.' 23:59:59');
                if(!$result && !$project->isSubProject()){
                    foreach($project->getSubProjects() as $sub){
                        $result = ($result || $champ['user']->isChampionOfOn($sub, ('2014'.REPORTING_RMC_MEETING_MONTH.' 23:59:59')));
                    }
                }
                $scratched = (!$result) ? "style='color:red;text-decoration:line-through;'" : "";
                $champion_html .= "<tr>";
                $champion_html .= "<td $scratched width='15%'>{$champ['user']->getReversedName()}</td>";
                $blb = new ReportBlob(BLOB_TEXT, 2013, $champ['user']->getId(), $proj_id);
                $sections = array(CHAMP_ACTIVITY, CHAMP_ORG, CHAMP_BENEFITS, CHAMP_SHORTCOMINGS, CHAMP_CASH);
                $i = 1;
                foreach($sections as $sec){
                    $addr = ReportBlob::create_address(RP_CHAMP, CHAMP_REPORT, $sec, 0);
                    $result = $blb->load($addr);
                    $data = $blb->getData();
                    $data = preg_replace("/@\[[^-]+-([^\]]*)]/", "<b>$1</b>$2", $data);
                    $short = "";
                    if($data != ""){
                        $short = substr($data, 0, 10)."...";
                    }
                    $id = "{$project->getId()}_{$champ['user']->getId()}_$i";
                    $champion_html .= "<td width='12.5%'><a style='cursor:pointer;' onClick='$(\"div#{$id}\").dialog({width: 700});'>$short</a><div id='{$id}' title='{$project->getName()}: {$champ['user']->getReversedName()}: Q$i' style='display:none;' class='dialog'>".nl2br($data)."</div></td>";
                    $i++;
                }
                $blb = new ReportBlob(BLOB_ARRAY, 2013, $champ['user']->getId(), $proj_id);
                $addr = ReportBlob::create_address(RP_CHAMP, CHAMP_REPORT, CHAMP_RESEARCHERS, 0);
                $result = $blb->load($addr);
                $data = $blb->getData();
                $champion_html .= "<td width='20%'>";
                if(count($data) > 0){
                    foreach($data as $u_id => $message){
                        if($message['q6'] != ""){
                            $id = "{$project->getId()}_{$champ['user']->getId()}_6_{$u_id}";
                            $user = Person::newFromId($u_id);
                            $champion_html .= "<a style='cursor:pointer;' onClick='$(\"div#{$id}\").dialog({width: 700});'>{$user->getReversedName()}</a><div class='dialog' id='$id' title='{$project->getName()}: {$champ['user']->getReversedName()}: Q6({$user->getReversedName()})' style='display:none;' class='dialog'>".nl2br($message['q6'])."</div><br />";
                        }
                    }
                }
                $champion_html .= "</td>";
                $champion_html .= "</tr>";
            }
            $champion_html .= "</table>";
            
            $this->html .= "<td style='padding:0px;'>$champion_html</td>";
            $this->html .= "</tr>";
        }
        $this->html .= "</tbody></table><br />";
    }

    static function getData($blob_type, $rptype, $question, $sub, $eval_id=0, $evalYear=EVAL_YEAR, $proj_id=0){
        $addr = ReportBlob::create_address($rptype, SEC_NONE, $question, $sub->getId());
        $blb = new ReportBlob($blob_type, $evalYear, $eval_id, $proj_id);
        // echo "rptype=$rptype, section=$question, subid=".$sub->getId() .", blob_type=$blob_type, year=$evalYear, eval=$eval_id <br>";
        
        $data = "";
       
        $result = $blb->load($addr);
        
        $data = $blb->getData();
        
        return $data;
    }

    function showBudgetTableFor($type){
        global $wgOut, $wgScriptPath, $wgServer, $pl_language_years;
        $this->html .= "<h3>$type Budget Summary</h3>";
        $fullBudget = array();
        if($type == PNI || $type == CNI){
            $fullBudget[] = new Budget(array(array(HEAD, HEAD, HEAD, HEAD, HEAD)), array(array($type, "Allocated in 2013", "Number of Projects", "Total Request", "Project Requests")));
            foreach(Person::getAllPeopleDuring($type, "2014-04-01", "2015-03-31") as $person){
                $budget = $person->getRequestedBudget(2013);
                if($budget != null){
                    
                    $error = ($budget->isError())? true : false;

                    $projects = $budget->copy()->where(HEAD1, array("Project Name:"))->select(V_PROJ);
                    $pers_total = $budget->copy()->rasterize()->select(HEAD1, array("Total"))->where(ROW_TOTAL);

                    $budgetProjects = array();
                    $budgetProjects[] = $budget->copy()->where(V_PERS_NOT_NULL)->limit(0, 1)->select(V_PERS_NOT_NULL);

                    //Allocated:
                    $budget_a = $person->getAllocatedBudget(2012);
                    if($budget_a != null){
                        $pers_total_a = $budget_a->copy()->rasterize()->select(HEAD1, array("Total"))->where(ROW_TOTAL);
                        //echo $pers_total_a->render();
                        $budgetProjects[] = $pers_total_a->limit($pers_total_a->nRows()-1,1);
                    }else{
                        $budgetProjects[] = new Budget();
                    }
                    
                    $budgetProjects[] = $projects->copy()->count();
                    $budgetProjects[] = $pers_total->limit($pers_total->nRows()-1,1);

                    //echo $budget->copy()->rasterize()->render();
                    $cur_year_total = $budget->copy()->rasterize()->where(HEAD1, array("TOTALS for April 1, 2014, to March 31, 2015"));
                    

                    $i = 0;
                    foreach($projects->xls as $index => $project_arr){
                        foreach($project_arr as $project){
                            $proj_name = $project->toString();
                            $concat_budget = new Budget(array(array(READ)), array(array($proj_name)));
                            $budgetProjects[] = $concat_budget->join($budget->copy()->rasterize()->select(V_PROJ, array($proj_name))->where(COL_TOTAL)->limitCols(0,1))->concat();
                            $i++;
                        }
                    }
                    for(; $i < 6; $i++){
                        $budgetProjects[] = new Budget();
                    }    
                    
                    if($error){
                        @$budgetProjects[0]->xls[0][1]->error = "There is a problem with budget for ".@$budgetProjects[0]->xls[0][1]->value;
                    }
                    
                    if(empty($cur_year_total->xls)){
                        @$budgetProjects[0]->xls[0][1]->style = "background-color:#FFFF88 !important;";
                        @$budgetProjects[0]->xls[0][1]->error = "Last year's template is used by ".@$budgetProjects[0]->xls[0][1]->value;
                    }

                    $rowBudget = Budget::join_tables($budgetProjects);
                    $fullBudget[] = $rowBudget;
                }
            }
            $fullBudget = Budget::union_tables($fullBudget);
            if($fullBudget != null){
                $this->html .= $fullBudget->render(true);
            }
        }
        else if($type == "Project"){
            $fullBudget = array();
            $fullBudget[] = new Budget(array(array(HEAD, HEAD, HEAD, HEAD)), array(array($type, "Number of Researchers", "Total Request", "Researcher Requests")));
            foreach(Project::getAllProjects() as $project){
                if($project->getPhase() == 2){
                    $budget = $project->getRequestedBudget(2013);
                    if($budget != null){
                        $error = false;
                        if($budget->isError()){
                            $error = true;
                        }
                        $people = $budget->copy()->where(HEAD1, array("Name of network investigator submitting request:"))->select(V_PERS_NOT_NULL);
                    
                        $budgetPeople = array();
                        $budgetPeople[] = new Budget(array(array(READ)), array(array($project->getName())));
                        $budgetPeople[] = $people->copy()->count();
                        $budgetPeople[] = $budgetTotal = $budget->copy()->where(CUBE_TOTAL)->select(CUBE_TOTAL);
                        
                        if($error){
                            $budgetPeople[0]->xls[0][0]->error = "There is a problem with budget for ".$budgetPeople[0]->xls[0][0]->value;
                        }

                        $nCols = $people->nCols();
                        for($i = 0; $i < $nCols; $i++){
                            if(isset($people->xls[0][$i + 1])){
                                $budgetPeople[] = @$budget->copy()->where(V_PERS_NOT_NULL)
                                                         ->select(V_PERS_NOT_NULL, array($people->xls[0][$i + 1]->getValue()))
                                                         ->join($budget->copy()->select(V_PERS_NOT_NULL, array($people->xls[0][$i + 1]->getValue()))
                                                         ->where(CUBE_COL_TOTAL)
                                        )->concat();
                            }
                            else{
                                $budgetPeople[] = new Budget();
                            }
                        }
                        $rowBudget = @Budget::join_tables($budgetPeople);
                        $fullBudget[] = $rowBudget;
                    }
                }
            }
            $fullBudget = Budget::union_tables($fullBudget);
            $this->html .= $fullBudget->render(true);
        }
        else if($type == "Full"){
            $fullBudget = new Budget(array(array(HEAD, HEAD, HEAD)), array(array("Categories for April 1, 2014, to March 31, 2015", PNI."s", CNI."s")));
            
            $pniTotals = array();
            $cniTotals = array();
            foreach(Person::getAllPeopleDuring(PNI, "2014-04-01", "2015-03-31") as $person){
                $budget = $person->getRequestedBudget(2013);
                if($budget != null){
                    $pniTotals[] = $budget->copy()->limit(8, 14)->rasterize()->select(ROW_TOTAL);
                }
            }
            foreach(Person::getAllPeopleDuring(CNI, "2014-04-01", "2015-03-31") as $person){
                $budget = $person->getRequestedBudget(2013);
                if($budget != null){
                    $cniTotals[] = $budget->copy()->limit(8, 14)->rasterize()->select(ROW_TOTAL);
                }
            }
            
            @$pniTotals = Budget::join_tables($pniTotals);
            @$cniTotals = Budget::join_tables($cniTotals);
            
            $cubedPNI = new Budget();
            $cubedCNI = new Budget();
            if($pniTotals != null){
                $cubedPNI = @$pniTotals->cube();
            }
            if($cniTotals != null){
                $cubedCNI = @$cniTotals->cube();
            }
            
            $categoryBudget = new Budget(array(array(HEAD2),
                                               array(HEAD2),
                                               array(HEAD2),
                                               array(HEAD2),
                                               array(HEAD1),
                                               array(HEAD2),
                                               array(HEAD2),
                                               array(HEAD2),
                                               array(HEAD1),
                                               array(HEAD1),
                                               array(HEAD1),
                                               array(HEAD2),
                                               array(HEAD2),
                                               array(HEAD2)), 
                                         array(array("1) Salaries and stipends"),
                                               array("b) Postdoctoral fellows"),
                                               array("c) Technical and professional assistants"),
                                               array("d) Undergraduate students"),
                                               array("2) Equipment"),
                                               array("a) Purchase or rental"),
                                               array("b) Maintenance costs"),
                                               array("c) Operating costs"),
                                               array("3) Materials and supplies"),
                                               array("4) Computing costs"),
                                               array("5) Travel expenses"),
                                               array("a) Field trips"),
                                               array("b) Conferences"),
                                               array("c) GRAND annual conference")));
                                            
            $categoryBudget = @$categoryBudget->join($cubedPNI->select(CUBE_ROW_TOTAL)->filter(TOTAL))
                                             ->join($cubedCNI->select(CUBE_ROW_TOTAL)->filter(TOTAL));
                                             
            $fullBudget = $fullBudget->union($categoryBudget);
            $this->html .= $fullBudget->cube()->render(true);
        }
    }

    function showProjectProductivity() {
        global $wgOut;

        $projects = Project::getAllProjects();
        $chunk = "
<table class='wikitable sortable' cellspacing='1' cellpadding='2' frame='box' rules='all' width='1000px'>
<tr><th>Project
<th>".HQP.": Total
<th>".HQP.": Undergraduate
<th>".HQP.": M.Sc.
<th>".HQP.": Ph.D.
<th>".HQP.": Post Doctorate
<th>".HQP.": Technician
<th>".HQP.": Other
<th>Number of Publications
<th>Number of Artifacts
<th>Number of Contributions
<th>Volume of Contributions
";
        $pdata = array();
        foreach ($projects as $project) {
            $pdata[$project->getId()] = new ProjectProductivity($project);
        }

        $positions = array('all', 'Undergraduate', 'Masters Student', 'PhD Student', 'PostDoc', 'Technician', 'Other');
        $blank = "<td style='color: #000000' align='right'>0</td>";
        $details_div_id = "details_div";
        $did = 1000000;
        
        $unique = array('all'=>array(), 'Undergraduate'=>array(), 'Masters Student'=>array(), 'PhD Student'=>array(), 'PostDoc'=>array(), 'Technician'=>array(), 'Other'=>array(), 'Publications'=>array(), 'Artifacts'=>array(), 'Contributions1'=>array(), 'Contributions2'=>array());
        
        $totals = array('all'=>array(), 'Undergraduate'=>array(), 'Masters Student'=>array(), 'PhD Student'=>array(), 'PostDoc'=>array(), 'Technician'=>array(), 'Other'=>array(), 'Publications'=>array(), 'Artifacts'=>array(), 'Contributions'=>array());
        
        foreach ($projects as $project) {
            $p_name = $project->getName();
            $p_id = $project->getId(); 
            $chunk .= "<tr><td>{$p_name}</td>";

            // HQP stuff            
            foreach ($positions as $pos) {
                $hqps = Dashboard::getHQP($project, 0, $pos, "2013-01-01 00:00:00", "2014-01-01 00:00:00");
                $hqp_num = count($hqps);
                $hqp_details = Dashboard::hqpDetails($hqps);

                //Merge for totals array, will eleminate duplicates later.
                $totals[$pos] = array_merge($totals[$pos], $hqps);
                
                $pos_f = preg_replace('/ /', '_', $pos);
                
                $lnk_id = "lnk_hqp_$pos_f" . $p_id;
                $div_id = "div_hqp_$pos_f" . $p_id;
                
                if ($hqp_num > 0){
                    //$chunk .= "<td align='right'>{$hqp_num}</td>";
                    $chunk .=<<<EOF
                    <td align="right">
                    <a id="$lnk_id" onclick="showdiv('$div_id','$details_div_id');" href="#$details_div_id">
                    $hqp_num
                    </a>
                    <div style="display: none;" id="$div_id" class="cell_details_div">
                        <p><span class="label">$p_name / HQP / $pos:</span> 
                        <button class="hide_div" onclick="$('#$div_id').hide();return false;">x</button></p> 
                        <ul>$hqp_details</ul>
                    </div>
                    </td>
EOF;
                }
                else{
                    $chunk .= $blank;
                }
            }

            // Publications.
            $papers = Dashboard::getPapers($project, '', "Publication", "2013-01-01 00:00:00", "2014-01-01 00:00:00");
            $paper_num = count($papers);
            $paper_details = Dashboard::paperDetails($papers);
            
            //Merge for totals array, will eleminate duplicates later.
            $totals['Publications'] = array_merge($totals['Publications'], $papers);

            $lnk_id = "lnk_public_" . $p_id;
            $div_id = "div_public_" . $p_id;
            
            $chunk .= "<td align='right'>";
            if($paper_num > 0){
                $chunk .=<<<EOF
                    <a id="$lnk_id" onclick="showdiv('$div_id','$details_div_id');" href="#$details_div_id">
                    $paper_num
                    </a>
                    <div style="display: none;" id="$div_id" class="cell_details_div">
                        <p><span class="label">$p_name / Publications:</span> 
                        <button class="hide_div" onclick="$('#$div_id').hide();return false;">x</button></p> 
                        <ul>$paper_details</ul>
                    </div>
                    </td>
EOF;
            }
            else{
                $chunk .= "0</td>";
            }

            // Artifacts.
            $papers = Dashboard::getPapers($project, '', "Artifact", "2013-01-01 00:00:00", "2014-01-01 00:00:00");
            $paper_num = count($papers);
            $paper_details = Dashboard::paperDetails($papers);
            
            //Merge for totals array, will eleminate duplicates later.
            $totals['Artifacts'] = array_merge($totals['Artifacts'], $papers);

            $lnk_id = "lnk_artif_" . $p_id;
            $div_id = "div_artif_" . $p_id;
            
            $chunk .= "<td align='right'>";
            if($paper_num > 0){
                $chunk .=<<<EOF
                    <a id="$lnk_id" onclick="showdiv('$div_id','$details_div_id');" href="#$details_div_id">
                    $paper_num
                    </a>
                    <div style="display: none;" id="$div_id" class="cell_details_div">
                        <p><span class="label">$p_name / Artifacts:</span> 
                        <button class="hide_div" onclick="$('#$div_id').hide();return false;">x</button></p> 
                        <ul>$paper_details</ul>
                    </div>
                    </td>
EOF;
            }
            else{
                $chunk .= "0</td>";
            }

            // Contributions
            $contributions = Dashboard::getContributions($project, '', '2013');
            $contribution_num = count($contributions);
            $contribution_sum = Dashboard::contributionSum($contributions);
            $contribution_details = Dashboard::contributionDetailsXLS($contributions);
            $chunk .= "<td align='right'>{$contribution_num}</td>";

            //Merge for totals array, will eleminate duplicates later.
            $totals['Contributions'] = array_merge($totals['Contributions'], $contributions);
            
            $lnk_id = "lnk_contr_" . $p_id;
            $div_id = "div_contr_" . $p_id;
            
            $chunk .= "<td align='right'>";
            if($contribution_sum > 0){
                $contribution_sum = self::dollar_format( $contribution_sum );
            
                $chunk .=<<<EOF
                    <a id="$lnk_id" onclick="showdiv('$div_id','$details_div_id');" href="#$details_div_id">
                    $contribution_sum
                    </a>
                    <div style="display: none;" id="$div_id" class="cell_details_div">
                        <p><span class="label">$p_name / Contributions:</span> 
                        <button class="hide_div" onclick="$('#$div_id').hide();return false;">x</button></p> 
                        <ul>$contribution_details</ul>
                    </div> 
                    </td>           
EOF;
            }
            else{
                $chunk .= "$ 0.00</td>";
            }
            
        }

        $chunk .= "</tr><tr style='font-weight:bold;'><td>Total:</td>";

        //Now let's weed the totals and print the row
        foreach ($totals as $key => $arr) {
            $temp_unique = array();
            foreach ($arr as $obj) {
                $id = $obj->getId();
                if(!in_array($id, $temp_unique)){
                    $temp_unique[] = $id;
                    $unique[$key][] = $obj;
                }
            }

            $lnk_id = "lnk_{$key}_total";
            $div_id = "div_{$key}_total";
            if($key == "Publications" || $key == "Artifacts"){
                $details = Dashboard::paperDetails($unique[$key]);

            }
            else if($key == "Contributions" ){
                $details = Dashboard::contributionDetailsXLS($unique[$key]);

            }
            else{
                $details = Dashboard::hqpDetails($unique[$key]);
            }
            $num = count($unique[$key]);
            $chunk .=<<<EOF
                <td align='right'>
                    <a id="$lnk_id" onclick="showdiv('$div_id','$details_div_id');" href="#$details_div_id">
                    $num
                    </a>
                    <div style="display: none;" id="$div_id" class="cell_details_div">
                        <p><span class="label">Total {$key}:</span> 
                        <button class="hide_div" onclick="$('#$div_id').hide();return false;">x</button></p> 
                        <ul>$details</ul>
                    </div> 
                    </td>
                </td>
EOF;
            if($key == "Contributions"){
                $sum = self::dollar_format(Dashboard::contributionSum($unique[$key]));
                $lnk_id = "lnk_{$key}_total2";
                $div_id = "div_{$key}_total2";
                $chunk .=<<<EOF
                <td align='right'>
                    <a id="$lnk_id" onclick="showdiv('$div_id','$details_div_id');" href="#$details_div_id">
                    $sum
                    </a>
                    <div style="display: none;" id="$div_id" class="cell_details_div">
                        <p><span class="label">Total {$key}:</span> 
                        <button class="hide_div" onclick="$('#$div_id').hide();return false;">x</button></p> 
                        <ul>$details</ul>
                    </div> 
                    </td>
                </td>
EOF;
            }
        }

        $chunk .= "</tr></table>";
        $chunk .= "<div class='pdf_hide' id='$details_div_id' style='display: none;'></div><br />";
        
        $this->html .= $chunk;
    }

    function showOtherContributions(){
        global $wgOut;

        $projects = Project::getAllProjects();
        $chunk =<<<EOF
        <table class='wikitable sortable' cellspacing='1' cellpadding='2' frame='box' rules='all' width='1000px'>
        <tr>
        <th>Type</th>
        <th>Number of Contributions</th>
        <th>Volume of Contributions</th>
        </tr>
EOF;
        
        $totals = array();
        $details_div_id = "other_contr_table_details";

        $types = array('cash'=>"Cash", 'inki'=>"In-Kind", 'caki'=>"Cash and In-Kind",'work'=>"Workshop Hosting",'conf'=>"Conference Organization",'talk'=>"Invited Talk",'equi'=>"Equipment Donation",'soft'=>"Software",'othe'=>"Other",'none'=>"Unknown");
        $year = EVAL_YEAR;
        foreach($types as $t=>$readable){
            $chunk .= "<tr><td>$readable</td>";
            
            $contributions = Contribution::getContributionsDuring($t, $year);
            $contribution_num = count($contributions);
            $chunk .= "<td align='right'>{$contribution_num}</td>";

            $contribution_sum = Dashboard::contributionSum($contributions);
            $contribution_details = Dashboard::contributionDetailsXLS($contributions);

            $totals = array_merge($totals, $contributions);

            $lnk_id = "lnk_contr_" . $t;
            $div_id = "div_contr_" . $t;
            
            $chunk .= "<td align='right'>";
            if($contribution_sum > 0){
                $contribution_sum = self::dollar_format( $contribution_sum );
            
                $chunk .=<<<EOF
                    <a id="$lnk_id" onclick="showdiv('$div_id','$details_div_id');" href="#$details_div_id">
                    $contribution_sum
                    </a>
                    <div style="display: none;" id="$div_id" class="cell_details_div">
                        <p><span class="label">Contributions / $readable:</span> 
                        <button class="hide_div" onclick="$('#$div_id').hide();return false;">x</button></p> 
                        <ul>$contribution_details</ul>
                    </div> 
                    </td>           
EOF;
            }
            else{
                $chunk .= "$ 0.00</td>";
            }

            $chunk .= "</tr>";
        }

        //Totals Row
        $chunk .= "<tr><td><strong>Total:</strong></td>";
            
        $contributions = $totals;
        $contribution_num = count($contributions);
        $chunk .= "<td align='right'>{$contribution_num}</td>";

        $contribution_sum = Dashboard::contributionSum($contributions);
        $contribution_details = Dashboard::contributionDetailsXLS($contributions);

        $totals = array_merge($totals, $contributions);

        $lnk_id = "lnk_contr_other_total";
        $div_id = "div_contr_other_total";
        
        $chunk .= "<td align='right'>";
        if($contribution_sum > 0){
            $contribution_sum = self::dollar_format( $contribution_sum );
        
            $chunk .=<<<EOF
                <a id="$lnk_id" onclick="showdiv('$div_id','$details_div_id');" href="#$details_div_id">
                $contribution_sum
                </a>
                <div style="display: none;" id="$div_id" class="cell_details_div">
                    <p><span class="label">Contributions / Total:</span> 
                    <button class="hide_div" onclick="$('#$div_id').hide();return false;">x</button></p> 
                    <ul>$contribution_details</ul>
                </div> 
                </td>           
EOF;
        }
        else{
            $chunk .= "$ 0.00</td>";
        }

        $chunk .= "</tr>";
        
        $chunk .= "</table>";
        $chunk .= "<div class='pdf_hide' id='$details_div_id' style='display: none;'></div><br />";
        $this->html .= $chunk;
    }

    function showResearcherProductivity() {
        global $wgOut;

        $pnis = Person::getAllPeople(PNI);
        $cnis = Person::getAllPeople(CNI);

        $chunk = "
<table class='wikitable sortable' cellspacing='1' cellpadding='2' frame='box' rules='all' width='1000px'>
<tr><th>Researcher
<th>HQP: Total
<th>HQP: Undergraduate
<th>HQP: M.Sc.
<th>HQP: Ph.D.
<th>HQP: Post Doctorate
<th>HQP: Technician
<th>HQP: Other
<th>Number of Publications
<th>Number of Artifacts
<th>Number of Contributions
<th>Volume of Contributions
";
        
        $blank = "<td style='color: #000000' align='right'>0</td>";
        $positions = array('all', 'Undergraduate', 'Masters Student', 'PhD Student', 'PostDoc', 'Technician', 'Other');
        $details_div_id = "details_div";
        $did = 1;

        $unique = array('all'=>array(), 'Undergraduate'=>array(), 'Masters Student'=>array(), 'PhD Student'=>array(), 'PostDoc'=>array(), 'Technician'=>array(), 'Other'=>array(), 'Publications'=>array(), 'Artifacts'=>array(), 'Contributions1'=>array(), 'Contributions2'=>array());
        
        $totals = array('all'=>array(), 'Undergraduate'=>array(), 'Masters Student'=>array(), 'PhD Student'=>array(), 'PostDoc'=>array(), 'Technician'=>array(), 'Other'=>array(), 'Publications'=>array(), 'Artifacts'=>array(), 'Contributions'=>array());

        foreach (array($pnis, $cnis) as $groups) {
            foreach ($groups as $person) {
                $pname = $person->getName();
                $p_id = $person->getId();
                $pname_read = preg_split('/\./', $pname, 2);
                $pname_read = $pname_read[1].", ".$pname_read[0];
                $chunk .= "<tr><td>$pname_read <small>({$person->getType()})</small></td>";
                
                // HQP stuff.
                $hqps = Dashboard::getHQP(0, $person, 'all', "2013-01-01 00:00:00", "2014-01-01 00:00:00");
                foreach ($positions as $pos) {
                    $pos_hqps = Dashboard::filterHQP($hqps, $pos);
                    $hqp_num = count($pos_hqps);
                    $hqp_details = Dashboard::hqpDetails($pos_hqps);
                    
                    //Merge for totals array, will eleminate duplicates later.
                    $totals[$pos] = array_merge($totals[$pos], $pos_hqps);

                    $pos_f = preg_replace('/ /', '_', $pos);
                    
                    $lnk_id = "lnk_hqp_$pos_f" . $p_id;
                    $div_id = "div_hqp_$pos_f" . $p_id;
                    
                    if ($hqp_num > 0){
                        //$chunk .= "<td align='right'>{$hqp_num}</td>";
                        $chunk .=<<<EOF
                        <td align="right">
                        <a id="$lnk_id" onclick="showdiv('$div_id','$details_div_id');" href="#$details_div_id">
                        $hqp_num
                        </a>
                        <div style="display: none;" id="$div_id" class="cell_details_div">
                            <p><span class="label">$pname_read / HQP / $pos:</span> 
                            <button class="hide_div" onclick="$('#$div_id').hide();return false;">x</button></p> 
                            <ul>$hqp_details</ul>
                        </div>
                        </td>
EOF;
                    }
                    else{
                        $chunk .= $blank;
                    }
                }
                
                // Publications.
                $papers = Dashboard::getPapers(0, $person, "Publication", "2013-01-01 00:00:00", "2014-01-01 00:00:00");
                $paper_num = count($papers);
                $paper_details = Dashboard::paperDetails($papers);
                
                //Merge for totals array, will eleminate duplicates later.
                $totals['Publications'] = array_merge($totals['Publications'], $papers);

                $lnk_id = "lnk_public_" . $p_id;
                $div_id = "div_public_" . $p_id;
                
                $chunk .= "<td align='right'>";
                if($paper_num > 0){
                    $chunk .=<<<EOF
                        <a id="$lnk_id" onclick="showdiv('$div_id','$details_div_id');" href="#$details_div_id">
                        $paper_num
                        </a>
                        <div style="display: none;" id="$div_id" class="cell_details_div">
                            <p><span class="label">$pname_read / Publications:</span> 
                            <button class="hide_div" onclick="$('#$div_id').hide();return false;">x</button></p> 
                            <ul>$paper_details</ul>
                        </div>
                        </td>
EOF;
                }
                else{
                    $chunk .= "0</td>";
                }

                // Artifacts.
                $papers = Dashboard::getPapers(0, $person, "Artifact", "2013-01-01 00:00:00", "2014-01-01 00:00:00");
                $paper_num = count($papers);
                $paper_details = Dashboard::paperDetails($papers);
                
                //Merge for totals array, will eleminate duplicates later.
                $totals['Artifacts'] = array_merge($totals['Artifacts'], $papers);

                $lnk_id = "lnk_artif_" . $p_id;
                $div_id = "div_artif_" . $p_id;
                
                $chunk .= "<td align='right'>";
                if($paper_num > 0){
                    $chunk .=<<<EOF
                        <a id="$lnk_id" onclick="showdiv('$div_id','$details_div_id');" href="#$details_div_id">
                        $paper_num
                        </a>
                        <div style="display: none;" id="$div_id" class="cell_details_div">
                            <p><span class="label">$pname_read / Artifacts:</span> 
                            <button class="hide_div" onclick="$('#$div_id').hide();return false;">x</button></p> 
                            <ul>$paper_details</ul>
                        </div>
                        </td>
EOF;
                }
                else{
                    $chunk .= "0</td>";
                }
                
                // Contributions
                $contributions = Dashboard::getContributions(0, $person, '2013');
                $contribution_num = count($contributions);
                $contribution_sum = Dashboard::contributionSum($contributions);
                $contribution_details = Dashboard::contributionDetailsXLS($contributions);
                $chunk .= "<td align='right'>{$contribution_num}</td>";
                
                //Merge for totals array, will eleminate duplicates later.
                $totals['Contributions'] = array_merge($totals['Contributions'], $contributions);

                $lnk_id = "lnk_contr_" . $p_id;
                $div_id = "div_contr_" . $p_id;
                
                $chunk .= "<td align='right'>";
                if($contribution_sum > 0){
                    $contribution_sum = self::dollar_format( $contribution_sum );
                
                    $chunk .=<<<EOF
                        <a id="$lnk_id" onclick="showdiv('$div_id','$details_div_id');" href="#$details_div_id">
                        $contribution_sum
                        </a>
                        <div style="display: none;" id="$div_id" class="cell_details_div">
                            <p><span class="label">$pname_read / Contributions:</span> 
                            <button class="hide_div" onclick="$('#$div_id').hide();return false;">x</button></p> 
                            <ul>$contribution_details</ul>
                        </div> 
                        </td>           
EOF;
                }
                else{
                    $chunk .= "$ 0.00</td>";
                }               
            }
        }

        $chunk .= "</tr><tr style='font-weight:bold;'><td>Total:</td>";

        //Now let's weed the totals and print the row
        foreach ($totals as $key => $arr) {
            $temp_unique = array();
            foreach ($arr as $obj) {
                $id = $obj->getId();
                if(!in_array($id, $temp_unique)){
                    $temp_unique[] = $id;
                    $unique[$key][] = $obj;
                }
            }

            $lnk_id = "lnk_{$key}_total3";
            $div_id = "div_{$key}_total3";
            if($key == "Publications" || $key == "Artifacts"){
                $details = Dashboard::paperDetails($unique[$key]);

            }
            else if($key == "Contributions" ){
                $details = Dashboard::contributionDetailsXLS($unique[$key]);

            }
            else{
                $details = Dashboard::hqpDetails($unique[$key]);
            }
            $num = count($unique[$key]);
            $chunk .=<<<EOF
                <td align='right'>
                    <a id="$lnk_id" onclick="showdiv('$div_id','$details_div_id');" href="#$details_div_id">
                    $num
                    </a>
                    <div style="display: none;" id="$div_id" class="cell_details_div">
                        <p><span class="label">Total {$key}:</span> 
                        <button class="hide_div" onclick="$('#$div_id').hide();return false;">x</button></p> 
                        <ul>$details</ul>
                    </div> 
                    </td>
                </td>
EOF;
            if($key == "Contributions"){
                $sum = self::dollar_format(Dashboard::contributionSum($unique[$key]));
                $lnk_id = "lnk_{$key}_total4";
                $div_id = "div_{$key}_total4";
                $chunk .=<<<EOF
                <td align='right'>
                    <a id="$lnk_id" onclick="showdiv('$div_id','$details_div_id');" href="#$details_div_id">
                    $sum
                    </a>
                    <div style="display: none;" id="$div_id" class="cell_details_div">
                        <p><span class="label">Total {$key}:</span> 
                        <button class="hide_div" onclick="$('#$div_id').hide();return false;">x</button></p> 
                        <ul>$details</ul>
                    </div> 
                    </td>
                </td>
EOF;
            }
        }

        $chunk .= "</tr></table>";
        $chunk .= "<div class='pdf_hide' id='$details_div_id' style='display: none;'></div><br />";
        
        $this->html .= $chunk;
    }

    function getUniContributionStats(){

        $cnis = Person::getAllPeopleDuring(CNI, "2013-01-01 00:00:00", "2014-01-01 00:00:00");
        $pnis = Person::getAllPeopleDuring(PNI, "2013-01-01 00:00:00", "2014-01-01 00:00:00");

        $nis = array();
        $unique_ids = array();
        foreach($cnis as $n){
            $nid = $n->getId();
            if(!in_array($nid, $unique_ids)){
                $unique_ids[] = $nid;
                $nis[] = $n;
            }
        }
        foreach($pnis as $n){
            $nid = $n->getId();
            if(!in_array($nid, $unique_ids)){
                $unique_ids[] = $nid;
                $nis[] = $n;
            }
        }

        //Setup the table structure
        $universities = array();
        $unknown = array("uniq"=>array(), "contr"=>array());

        //Fill the table
        foreach ($nis as $hqp){
            $uid = $hqp->getId();
    
            $contributions = Dashboard::getContributions(0, $hqp, '2013');

            $uniobj = $hqp->getUniversity();
            $uni = (isset($uniobj['university']))? $uniobj['university'] : "Unknown";
        
            if($uni != "Unknown" && !array_key_exists($uni, $universities)){
                $universities[$uni] = array("uniq"=>array(), "contr"=>array());
            }

            foreach($contributions as $contr){
                $contr_id = $contr->getId();

                if($uni == "Unknown"){
                    if(!in_array($contr_id, $unknown["uniq"])){
                        $unknown["uniq"][] = $contr_id;
                        $unknown["contr"][] = $contr;
                    }
                }
                else{
                    if(!in_array($contr_id, $universities[$uni]["uniq"])){
                        $universities[$uni]["uniq"][] = $contr_id;
                        $universities[$uni]["contr"][] = $contr;
                    }
                }
            }
        }

        //Render the table
        ksort($universities);
        $universities["Unknown"] = $unknown;

        $details_div_id = "ni_uni_contr_details";
        $html =<<<EOF
         <table class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all' width='100%'>
         <tr>
         <th>University</th>
         <th>Cash</th>
         <th>In-Kind</th>
         <th>Total</th>
         </tr>
EOF;
        foreach ($universities as $uni=>$data){
            $html .=<<<EOF
                <tr>
                <th align="left">{$uni}</th>
EOF;
            
            $uni_contr = $data["contr"];
            
            $uni_id = preg_replace('/[^A-Za-z0-9-]/', '', $uni);

            //CASH
            $lnk_id = "lnk_contr_cash_" . $uni_id;
            $div_id = "div_contr_cash_" . $uni_id;
            $contr_cash = array_merge(Dashboard::filterContributions($uni_contr, 'cash'), Dashboard::filterContributions($uni_contr, 'caki'));
            $contribution_sum = Dashboard::contributionSumCash($contr_cash);
            $contribution_details = Dashboard::contributionDetailsXLS($contr_cash);
            $html .= "<td align='right'>";
            if($contribution_sum > 0){
                $contribution_sum = self::dollar_format( $contribution_sum );
            
                $html .=<<<EOF
                    <a id="$lnk_id" onclick="showdiv('$div_id','$details_div_id');" href="#$details_div_id">
                    $contribution_sum
                    </a>
                    <div style="display: none;" id="$div_id" class="cell_details_div">
                        <p><span class="label">{$uni} / Cash Contributions:</span> 
                        <button class="hide_div" onclick="$('#$div_id').hide();return false;">x</button></p> 
                        <ul>$contribution_details</ul>
                    </div> 
                    </td>           
EOF;
            }
            else{
                $html .= "$ 0.00</td>";
            }            
            
            //IN-KIND
            $lnk_id = "lnk_contr_inki_" . $uni_id;
            $div_id = "div_contr_inki_" . $uni_id;
            $contr_inki = array_merge(Dashboard::filterContributions($uni_contr, 'inki'), Dashboard::filterContributions($uni_contr, 'caki'));
            $contribution_sum = Dashboard::contributionSumKind($contr_inki);
            $contribution_details = Dashboard::contributionDetailsXLS($contr_inki);
            $html .= "<td align='right'>";
            if($contribution_sum > 0){
                $contribution_sum = self::dollar_format( $contribution_sum );
            
                $html .=<<<EOF
                    <a id="$lnk_id" onclick="showdiv('$div_id','$details_div_id');" href="#$details_div_id">
                    $contribution_sum
                    </a>
                    <div style="display: none;" id="$div_id" class="cell_details_div">
                        <p><span class="label">{$uni} / In-Kind Contributions:</span> 
                        <button class="hide_div" onclick="$('#$div_id').hide();return false;">x</button></p> 
                        <ul>$contribution_details</ul>
                    </div> 
                    </td>           
EOF;
            }
            else{
                $html .= "$ 0.00</td>";
            } 

            //TOTAL
            $lnk_id = "lnk_contr_total_" . $uni_id;
            $div_id = "div_contr_total_" . $uni_id;
            //$contr_inki = Dashboard::filterContributions($uni_contr, 'inki');
            $contribution_sum = Dashboard::contributionSum($uni_contr);
            $contribution_details = Dashboard::contributionDetailsXLS($uni_contr);
            $html .= "<td align='right'>";
            if($contribution_sum > 0){
                $contribution_sum = self::dollar_format( $contribution_sum );
            
                $html .=<<<EOF
                    <a id="$lnk_id" onclick="showdiv('$div_id','$details_div_id');" href="#$details_div_id">
                    $contribution_sum
                    </a>
                    <div style="display: none;" id="$div_id" class="cell_details_div">
                        <p><span class="label">{$uni} / All Contributions:</span> 
                        <button class="hide_div" onclick="$('#$div_id').hide();return false;">x</button></p> 
                        <ul>$contribution_details</ul>
                    </div> 
                    </td>           
EOF;
            }
            else{
                $html .= "$ 0.00</td>";
            }      
            $html .= "</tr>";
        }
        $html .= "</table>";
        $html .= "<div class='pdf_hide details_div' id='$details_div_id' style='display: none;'></div><br />";
        
        $this->html .= $html;
    }

    function showDistribution() {
        global $wgOut;

        $distr = Project::getHQPDistributionDuring("2013-01-01 00:00:00", "2014-01-01 00:00:00");
        $chunk =<<<EOF
        <table class='wikitable sortable' cellspacing='1' cellpadding='2' frame='box' rules='all' width='100%'>
        <tr>
        <th>Number of Main Projects</th>
        <th>HQP Associated</th>
        </tr>
EOF;
        foreach ($distr as $k => $v) {
            $chunk .= "<tr><td align='center'>{$k}</td><td align='right'>{$v}</td></tr>";
        }
        $chunk .= "</table>";

        $this->html .= $chunk;
    }

    function showThemes() {
        global $wgOut;

        // Brief instructions.
        $chunk = "<p>In the following table, the new percentage value for themes are bolded, whereas the old values are shown in parentheses as they are defined in the database for the project. The new values are extracted from the respective project-leader report.</p>\n";

        // Output (nn is the new theme value, yy the old value):
        // Project  Theme1  Theme2  Theme3  Theme4  Theme5
        // Pname1   nn (yy) nn (yy) nn (yy) nn (yy) nn (yy)
        // ...
        $chunk .= "<table class='wikitable sortable' cellspacing='1' cellpadding='2' frame='box' rules='all' width='100%'>\n<tr><th>Project";

        // Print theme names.
        $themes = Theme::getAllThemes(1);
        foreach ($themes as $theme) {
            $chunk .= "<th>{$theme->getAcronym()}";
        }

        // First pass: grab data.
        $projects = Project::getAllProjects();
        foreach ($projects as $project) {
            if($project->getPhase() == 1){
                $th = new Themes($project);
                $pn = $project->getName();
                $data = $th->get_metric();
                $p_leader = $project->getLeader();
                $leader_name = ($p_leader instanceof Person)? $p_leader->getNameForForms() : "";
                
                // Render project/leader pair.
                $oldv = ArrayUtils::get_array($data, 'values');
                $reps = ArrayUtils::get_array($data, 'data');
                
                $chunk .= "\n<tr><td align='center'>{$pn}<br /><small>{$leader_name}</small></td>";
                foreach (array_keys($themes) as $ind) {
                    $chunk .= "<td align='center'><b>" . $project->getTheme($ind+1) .
                        '</b> (' . ArrayUtils::get_string($oldv, $ind) . ')</td>';
                }
                $chunk .= '</tr>';
            }
        }

        $chunk .= "</table>\n";
        $this->html .= $chunk;
    }

    static function dollar_format($val) {
        return '$&nbsp;' . number_format($val, 2);
    }
    
    static function getValueOf($value){
        switch($value){
            case "exceptional":
                $newValue = 1;
                break;
            case "satisfactory":
                $newValue = 2;
                break;
            case "unsatisfactory":
                $newValue = 3;
                break;
            case "top tier":
                $newValue = 1;
                break;
            case "middle tier":
                $newValue = 2;
                break;
            case "lower tier":
                $newValue = 3;
                break;
            case "excellent":
                $newValue = 1;
                break;
            case "good":
                $newValue = 2;
                break;
            case "fair":
                $newValue = 3;
                break;
            case "poor":
                $newValue = 4;
                break;
            default:
                $newValue = 0;
                break;
        }
        return $newValue;
    }
}    
    
?>
