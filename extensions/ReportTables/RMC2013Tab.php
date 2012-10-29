<?php

class RMC2013Tab extends AbstractTab {

    function RMC2013Tab(){
        global $wgOut;
        parent::AbstractTab("2013");
        $wgOut->setPageTitle("Evaluation Tables: RMC");
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath, $wgOut, $foldscript;
        
        $this->showContentsTable();

        if(ArrayUtils::get_string($_GET, 'year') == "2013"){
        switch (ArrayUtils::get_string($_GET, 'summary')) {
        case 'question':
            $this->html .= "<a id='Q_Summary'></a>";
            $this->html .= "<h2>Summary of 1-9</h2>";
            $this->html .= "<a id='".PNI."_Summary'></a>";
            $this->showEvalTableFor(PNI);
            $this->html .= "<a id='".CNI."_Summary'></a>";
            $this->showEvalTableFor(CNI);
            $this->html .= "<a id='Project_Summary'></a>";
            $this->showEvalTableFor("Project");
            break;

        case 'budget':
            $this->html .= "<a id='Budget_Summary'></a>";
            $this->html .= "<h2>Budget Summaries</h2>";
            $this->html .= "<a id='".PNI."_Budget_Summary'></a>";
            $this->showBudgetTableFor(PNI);
            $this->html .= "<a id='".CNI."_Budget_Summary'></a>";
            $this->showBudgetTableFor(CNI);
            $this->html .= "<a id='Project_Budget_Summary'></a>";
            $this->showBudgetTableFor("Project");
            $this->html .= "<a id='Full_Budget_Summary'></a>";
            $this->showBudgetTableFor("Full");
            break;

        case 'productivity':
            // Project productivity.
            $wgOut->addScript($foldscript);
            $this->html .= "<h2>Other</h2><h3>Project Productivity</h3><a id='Project_Productivity'></a>";
            self::showProjectProductivity();
            break;

        case 'researcher':
            // Researcher productivity.
            $wgOut->addScript($foldscript);
            $this->html .= "<h2>Other</h2><h3>Researcher Productivity</h3><a id='Researcher_Productivity'></a>";
            self::showResearcherProductivity();
            break;

        case 'contributions':
            $wgOut->addScript($foldscript);
            $this->html .= "<h2>Other</h2><h3>Contributions by University</h3><a id='Uni_Contributions'></a>";
            //self::showOtherContributions();
            self::getUniContributionStats();
            break;

        case 'distribution':
            // HQP distribution.
            $this->html .= "<h2>Other</h2><h3>".HQP." Distribution</h3><a id='Distribution'></a>";
            self::showDistribution();
            break;

        case 'themes':
            $this->html .= "<h2>Other</h2><h3>Project Themes</h3><a id='Themes'></a>";
            self::showThemes();
            break;
        }
        }
        
        return $this->html;
    }

    function showContentsTable(){
        global $wgServer, $wgScriptPath;
        $this->html .=<<<EOF
            <table class='toc' summary='Contents'>
            <tr><td>
            <div id='toctitle'><h2>Contents</h2></div>
            <ul>
            <li class='toclevel-1'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2013&summary=question#Q_Summary'><span class='tocnumber'>1</span> <span class='toctext'>Summary of 1-7</span></a>
                <ul>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2013&summary=question#PNI_Summary'><span class='tocnumber'>1.1</span> <span class='toctext'>PNI Summary of Questions 1-9</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2013&summary=question#CNI_Summary'><span class='tocnumber'>1.2</span> <span class='toctext'>CNI Summary of Questions 1-9</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2013&summary=question#Project_Summary'><span class='tocnumber'>1.3</span> <span class='toctext'>Project Summary of Questions 1-8</span></a></li>
                </ul>
            </li>
            <li class='toclevel-1'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2013&summary=budget#Budget_Summary'><span class='tocnumber'>2</span> <span class='toctext'>Budget Summaries</span></a>
                <ul>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2013&summary=budget#PNI_Budget_Summary'><span class='tocnumber'>2.1</span> <span class='toctext'>PNI Budget Summary</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2013&summary=budget#CNI_Budget_Summary'><span class='tocnumber'>2.2</span> <span class='toctext'>CNI Budget Summary</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2013&summary=budget#Project_Budget_Summary'><span class='tocnumber'>2.3</span> <span class='toctext'>Project Budget Summary</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2013&summary=budget#Full_Budget_Summary'><span class='tocnumber'>2.4</span> <span class='toctext'>Full Budget Summary</span></a></li>
                </ul>
            </li>
            <li class='toclevel-1'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2013&summary=productivity#Other'><span class='tocnumber'>3</span> <span class='toctext'>Other</span></a>
                <ul>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2013&summary=productivity#Project_Productivity'><span class='tocnumber'>3.1</span> <span class='toctext'>Project Productivity</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2013&summary=researcher#Researcher_Productivity'><span class='tocnumber'>3.2</span> <span class='toctext'>Researcher Productivity</span></a></li>
                    <!li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2013&summary=contributions#Uni_Contributions'><span class='tocnumber'>3.3</span> <span class='toctext'>Contributions by University</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2013&summary=distribution#Distribution'><span class='tocnumber'>3.4</span> <span class='toctext'>HQP Distribution</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2013&summary=themes#Themes'><span class='tocnumber'>3.5</span> <span class='toctext'>Project Themes</span></a></li>
                </ul>
            </li>
            </td></tr>
         </table>
EOF;

    }

    function showEvalTableFor($type){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $foldscript, $reporteeId, $getPerson;
        $people = Person::getAllPeople();
        //$people = array_merge($people, Person::getAllStaff());
        $peopleTiers = array();
        $projectTiers = array();
        $pg = "{$wgServer}{$wgScriptPath}/index.php/Special:Evaluate";
        foreach($people as $person){
            if($person->isEvaluator()){
                $reporteeId = $person->getId();
                if($type == CNI){
                    $subs = $person->getEvaluateCNIs();
                }
                else{
                    $subs = $person->getEvaluateSubs();
                }
                foreach($subs as $sub){
                    $id = "";
                    if($sub instanceof Person && ($type == PNI && $sub->isRole(PNI)) ){
                        $id = "person";
                        $ctype = "p";
                        $rtype = RP_EVAL_RESEARCHER;
                        $array = isset($peopleTiers[$sub->getName()]) ? $peopleTiers[$sub->getName()] : array();
                    }
                    else if($sub instanceof Person && ($type == CNI && $sub->isRole(CNI)) ){
                        $id = "person";
                        $ctype = "p";
                        $rtype = RP_EVAL_CNI;
                        $array = isset($peopleTiers[$sub->getName()]) ? $peopleTiers[$sub->getName()] : array();
                    }
                    else if($sub instanceof Project && $type == "Project"){
                        $id = "project";
                        $ctype = "r";
                        $rtype = RP_EVAL_PROJECT;
                        $array = isset($projectTiers[$sub->getName()]) ? $projectTiers[$sub->getName()] : array();
                    }
                    if($id == ""){
                        continue;
                    }
                    $array["1_1"] = isset($array["1_1"]) ? $array["1_1"] : 0;
                    $array["1_2"] = isset($array["1_2"]) ? $array["1_2"] : 0;
                    $array["1_3"] = isset($array["1_3"]) ? $array["1_3"] : 0;
                    $array["2_1"] = isset($array["2_1"]) ? $array["2_1"] : 0;
                    $array["2_2"] = isset($array["2_2"]) ? $array["2_2"] : 0;
                    $array["2_3"] = isset($array["2_3"]) ? $array["2_3"] : 0;
                    $array["2_4"] = isset($array["2_4"]) ? $array["2_4"] : 0;
                    $array["3_1"] = isset($array["3_1"]) ? $array["3_1"] : 0;
                    $array["3_2"] = isset($array["3_2"]) ? $array["3_2"] : 0;
                    $array["3_3"] = isset($array["3_3"]) ? $array["3_3"] : 0;
                    $array["3_4"] = isset($array["3_4"]) ? $array["3_4"] : 0;
                    $array["q1Rat"] = isset($array["q1Rat"]) ? $array["q1Rat"] : "";
                    $array["q2Rat"] = isset($array["q2Rat"]) ? $array["q2Rat"] : "";
                    $array["q3Rat"] = isset($array["q3Rat"]) ? $array["q3Rat"] : "";
                    $array["q4Rat"] = isset($array["q4Rat"]) ? $array["q4Rat"] : "";
                    $array["q5Rat"] = isset($array["q5Rat"]) ? $array["q5Rat"] : "";
                    $array["q6Rat"] = isset($array["q6Rat"]) ? $array["q6Rat"] : "";
                    $array["q7Rat"] = isset($array["q7Rat"]) ? $array["q7Rat"] : "";
                    $array["q8Rat"] = isset($array["q8Rat"]) ? $array["q8Rat"] : "";
                    $array["q9Rat"] = isset($array["q9Rat"]) ? $array["q9Rat"] : "";
                    $array["q1Fed"] = isset($array["q1Fed"]) ? $array["q1Fed"] : "";
                    $array["q2Fed"] = isset($array["q2Fed"]) ? $array["q2Fed"] : "";
                    $array["q3Fed"] = isset($array["q3Fed"]) ? $array["q3Fed"] : "";
                    $array["q4Fed"] = isset($array["q4Fed"]) ? $array["q4Fed"] : "";
                    $array["q5Fed"] = isset($array["q5Fed"]) ? $array["q5Fed"] : "";
                    $array["q6Fed"] = isset($array["q6Fed"]) ? $array["q6Fed"] : "";
                    $array["q7Fed"] = isset($array["q7Fed"]) ? $array["q7Fed"] : "";
                    $array["q8Fed"] = isset($array["q8Fed"]) ? $array["q8Fed"] : "";
                    $array["q9Fed"] = isset($array["q9Fed"]) ? $array["q9Fed"] : "";
                    $array["nQ1"] = isset($array["nQ1"]) ? $array["nQ1"] : 0;
                    $array["nQ2"] = isset($array["nQ2"]) ? $array["nQ2"] : 0;
                    $array["nQ3"] = isset($array["nQ3"]) ? $array["nQ3"] : 0;
                    $array["nQ4"] = isset($array["nQ4"]) ? $array["nQ4"] : 0;
                    $array["nQ5"] = isset($array["nQ5"]) ? $array["nQ5"] : 0;
                    $array["nQ6"] = isset($array["nQ6"]) ? $array["nQ6"] : 0;
                    $array["nQ7"] = isset($array["nQ7"]) ? $array["nQ7"] : 0;
                    $array["nQ8"] = isset($array["nQ8"]) ? $array["nQ8"] : 0;
                    $array["nQ9"] = isset($array["nQ9"]) ? $array["nQ9"] : 0;
                    $array["nRatings"] = isset($array["nRatings"]) ? $array["nRatings"] : 0;

                    $post = Evaluate_Form::getData('', $rtype, EVL_EXCELLENCE, $sub, 2011);
                    $array = self::generateRow(1, $array, $post, $person);
                    
                    $post = Evaluate_Form::getData('', $rtype, EVL_HQPDEVELOPMENT, $sub, 2011);
                    $array = self::generateRow(2, $array, $post, $person);

                    $post = Evaluate_Form::getData('', $rtype, EVL_NETWORKING, $sub, 2011);
                    $array = self::generateRow(3, $array, $post, $person);

                    $post = Evaluate_Form::getData('', $rtype, EVL_KNOWLEDGE, $sub, 2011);
                    $array = self::generateRow(4, $array, $post, $person);

                    $post = Evaluate_Form::getData('', $rtype, EVL_MANAGEMENT, $sub, 2011);
                    $array = self::generateRow(5, $array, $post, $person);

                    $post = Evaluate_Form::getData('', $rtype, EVL_OVERALLSCORE, $sub, 2011);
                    $array = self::generateRow(6, $array, $post, $person);

                    $post = Evaluate_Form::getData('', $rtype, EVL_OTHERCOMMENTS, $sub, 2011);
                    $array = self::generateRow(7, $array, $post, $person);
                    
                    $post = Evaluate_Form::getData('', $rtype, EVL_REPORTQUALITY, $sub, 2011);
                    $array = self::generateRow(8, $array, $post, $person);
                    
                    $post = Evaluate_Form::getData('', $rtype, EVL_CONFIDENCE, $sub, 2011);
                    $array = self::generateRow(9, $array, $post, $person);
                    
                    @$array["nRatings"] += 1;
                    if($sub instanceof Person && (($type == PNI && $sub->isRole(PNI)) || ($type == CNI && $sub->isRole(CNI)))){
                        $peopleTiers[$sub->getName()] = $array;
                    }
                    else if($sub instanceof Project && $type == "Project"){
                        $projectTiers[$sub->getName()] = $array;
                    }
                }
            }
        }
        if($type == "Project"){
            $this->html .= "<h3>$type Summary of Questions 1-8</h3>";
        }
        else{
            $this->html .= "<h3>$type Summary of Questions 1-9</h3>";
        }
        $W1 = isset($_POST['w1']) ? min(99, $_POST['w1']) : 3;
        $W2 = isset($_POST['w2']) ? min(99, $_POST['w2']) : 1;
        $W3 = isset($_POST['w3']) ? min(99, $_POST['w3']) : 0;
        if($getPerson == null){
            $this->html .= "<form method='post' action='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?summary=question'>
                                <b>Tier 1 Weight:</b> <input type='text' name='w1' value='$W1' size='3' /><br />
                                <b>Tier 2 Weight:</b> <input type='text' name='w2' value='$W2' size='3' /><br />
                                <b>Tier 3 Weight:</b> <input type='text' name='w3' value='$W3' size='3' /><br />
                                <input type='submit' value='Reload' /><br /><br />
                             </form>";
        }
        $this->html .= "<table class='wikitable sortable' cellspacing='1' cellpadding='2' style='background: #000000;' width='100%'>"; 
        $rppg = "$wgServer$wgScriptPath/index.php/Special:Report";
        $person = Person::newFromName(key($peopleTiers));
        $repi = new ReportIndex($person);
        
        // Check for a download.
        $action = ArrayUtils::get_string($_GET, 'getpdf');
        if ($action !== "") {
            $p = Person::newFromId($wgUser->getId());
            $sto = new ReportStorage($p);
            $wgOut->disable();
            return $sto->trigger_download($action, "{$action}.pdf", false);
        }
        $subNames = array();
        if($getPerson !== null){
            foreach($getPerson->getEvaluateSubs() as $sub){
                $subNames[] = $sub->getName();
            }
        }
        if(!empty($peopleTiers)){
            $this->html .= "<tr>
                                <th style='background: #EEEEEE;'>$type</th><th style='background: #EEEEEE;'>Weighted&nbsp;Average (Q6)</th><th style='background: #EEEEEE; min-width:400px;' width='45%'>Comments on NCE Criteria</th><th style='background: #EEEEEE; min-width:400px;' width='45%'>Other Comments</th>
                            </tr>";
            $wgOut->addScript($foldscript);
            while ($personTiers = current($peopleTiers)) {
                $rppg = "$wgServer$wgScriptPath/index.php/Special:Report";
                $person = Person::newFromName(key($peopleTiers));
                if($getPerson != null && array_search($person->getName(), $subNames) === false){
                    next($peopleTiers);
                    continue;
                }
                
                $download1 = Evaluate::getPNIPDF($person);
                $download2 = "";
                foreach($person->leadership() as $project){
                    $download2 .= Evaluate::getProjectLeaderPDF($project)."<br />";
                }
                if($download2 == ""){
                    $download2 = "No&nbsp;PDF";
                }
                $tierSum1 = ($W1*$personTiers["1_1"] + $W2*$personTiers["1_2"] + $W3*$personTiers["1_3"])/$personTiers["nRatings"];
                $tierSum2 = ($W1*$personTiers["2_1"] + $W2*$personTiers["2_2"] + $W3*$personTiers["2_3"])/max(1, $personTiers["nQ6"]);
                if($tierSum2 < 10){
                    $tierSum2 = "0".number_format(round($tierSum2, 2), 2);
                }
                else{
                    $tierSum2 = number_format(round($tierSum2, 2), 2);
                }
                $this->html .= "<tr>
                                    <td style='background: #FFFFFF;' valign='top' align='center'><b><u>".key($peopleTiers)."</u></b><br />
                                        <table>
                                            <tr>
                                                <td align='right' valign='top'><b>Researcher&nbsp;PDF:</b></td>
                                                <td algin='left' valign='top'>$download1</td>
                                            </tr>
                                            <tr>
                                                <td align='right' valign='top'><b>Project&nbsp;Leader&nbsp;PDF:</b></td>
                                                <td algin='left' valign='top'>$download2</td>
                                            </tr>
                                        </table>    
                                    </td>
                                    <td style='background: #FFFFFF;' valign='top' align='center'>$tierSum2 ({$personTiers["nQ6"]})</td>
                                    <td style='background: #FFFFFF;' valign='top'>";
                $this->html .= "<a href=\"javascript:ShowOrHide('2".key($peopleTiers)."','')\">Show/Hide Comments for Committee</a>
                                    <div id='2".key($peopleTiers)."' style='display:none'>
                                        <center><h3>Comments for Q1</h3></center>
                                        <table>".$personTiers["q1Rat"]."</table>
                                        <center><h3>Comments for Q2</h3></center>
                                        <table>".$personTiers["q2Rat"]."</table>
                                        <center><h3>Comments for Q3</h3></center>
                                        <table>".$personTiers["q3Rat"]."</table>
                                        <center><h3>Comments for Q4</h3></center>
                                        <table>".$personTiers["q4Rat"]."</table>
                                        <center><h3>Comments for Q5</h3></center>
                                        <table>".$personTiers["q5Rat"]."</table>
                                    </div>
                                    <hr />
                                    <a href=\"javascript:ShowOrHide('22".key($peopleTiers)."','')\">Show/Hide Feedback for Investigator</a>
                                    <div id='22".key($peopleTiers)."' style='display:none;background:#EEEEEE;'>
                                        <center><h3>Feedback for Q1</h3></center>
                                        <table>".$personTiers["q1Fed"]."</table>
                                        <center><h3>Feedback for Q2</h3></center>
                                        <table>".$personTiers["q2Fed"]."</table>
                                        <center><h3>Feedback for Q3</h3></center>
                                        <table>".$personTiers["q3Fed"]."</table>
                                        <center><h3>Feedback for Q4</h3></center>
                                        <table>".$personTiers["q4Fed"]."</table>
                                        <center><h3>Feedback for Q5</h3></center>
                                        <table>".$personTiers["q5Fed"]."</table>
                                    </div>
                                </td>
                                <td style='background: #FFFFFF;' valign='top'>";
                $this->html .= "<a href=\"javascript:ShowOrHide('1".key($peopleTiers)."','')\">Show/Hide Comments for Committee</a>
                                    <div id='1".key($peopleTiers)."' style='display:none'>
                                        <center><h3>Comments for Q6</h3></center>
                                        <table>".$personTiers["q6Rat"]."</table>
                                        <center><h3>Comments for Q7</h3></center>
                                        <table>".$personTiers["q7Rat"]."</table>
                                        <center><h3>Comments for Q8</h3></center>
                                        <table>".$personTiers["q8Rat"]."</table>
                                        <center><h3>Comments for Q9</h3></center>
                                        <table>".$personTiers["q9Rat"]."</table>
                                    </div>
                                    <hr />
                                    <a href=\"javascript:ShowOrHide('11".key($peopleTiers)."','')\">Show/Hide Feedback for Investigator</a>
                                    <div id='11".key($peopleTiers)."' style='display:none;background:#EEEEEE;'>
                                        <center><h3>Feedback for Q6</h3></center>
                                        <table>".$personTiers["q6Fed"]."</table>
                                        <center><h3>Feedback for Q7</h3></center>
                                        <table>".$personTiers["q7Fed"]."</table>
                                        <center><h3>Feedback for Q8</h3></center>
                                        <table>".$personTiers["q8Fed"]."</table>
                                    </div>
                                </td>
                             </tr>";
                next($peopleTiers);
            }
        }
        else if(!empty($projectTiers)){
           $this->html .= "<tr>
                                <th style='background: #EEEEEE;'>$type</th><th style='background: #EEEEEE;'>Weighted&nbsp;Average (Q5)</th><th style='background: #EEEEEE; min-width:400px;' width='45%'>Comments on NCE Criteria</th><th style='background: #EEEEEE; min-width:400px;' width='45%'>Other Comments</th>
                            </tr>";
                            
            while ($pTiers = current($projectTiers)) {
                $rppg = "$wgServer$wgScriptPath/index.php/Special:Report";
                $project = Project::newFromName(key($projectTiers));
                if($getPerson != null && array_search($project->getName(), $subNames) === false){
                    next($projectTiers);
                    continue;
                }
                $leader = $project->getLeader();
                if($leader != null){
                    $repi = new ReviewerIndex($leader, Person::newFromId($wgUser->getId()));
                    $ls = $repi->list_reports($project);
                }
                else{
                    $ls = array();
                }
                $none = true;
                $download2 = Evaluate::getProjectLeaderPDF($project)."<br />";
                $tierSum1 = ($W1*$pTiers["1_1"] + $W2*$pTiers["1_2"] + $W3*$pTiers["1_3"])/$pTiers["nRatings"];
                $tierSum2 = ($W1*$pTiers["2_1"] + $W2*$pTiers["2_2"] + $W3*$pTiers["2_3"])/max(1, $pTiers["nQ6"]);
                if($tierSum2 < 10){
                    $tierSum2 = "0".number_format(round($tierSum2, 2), 2);
                }
                else{
                    $tierSum2 = number_format(round($tierSum2, 2), 2);
                }
                $this->html .= "<tr>
                                    <td style='background: #FFFFFF;' valign='top' align='center'><b><u>".key($projectTiers)."</u></b><br />
                                        <table>
                                            <tr>
                                                <td align='right' valign='top'><b>Project&nbsp;Leader&nbsp;PDF:</b></td>
                                                <td algin='left' valign='top'>$download2</td>
                                            </tr>
                                        </table>    
                                    </td>
                                    <td style='background: #FFFFFF;' valign='top' align='center'>$tierSum2({$pTiers["nQ6"]})</td>
                                    <td style='background: #FFFFFF;' valign='top'>";
                $this->html .= "<a href=\"javascript:ShowOrHide('2".key($projectTiers)."','')\">Show/Hide Comments for Committee</a>
                                    <div id='2".key($projectTiers)."' style='display:none'>
                                        <center><h3>Comments for Q1</h3></center>
                                        <table>".$pTiers["q1Rat"]."</table>
                                        <center><h3>Comments for Q2</h3></center>
                                        <table>".$pTiers["q2Rat"]."</table>
                                        <center><h3>Comments for Q3</h3></center>
                                        <table>".$pTiers["q3Rat"]."</table>
                                        <center><h3>Comments for Q4</h3></center>
                                        <table>".$pTiers["q4Rat"]."</table>
                                    </div>
                                    <hr />
                                    <a href=\"javascript:ShowOrHide('22".key($projectTiers)."','')\">Show/Hide Feedback for Project Leader</a>
                                    <div id='22".key($projectTiers)."' style='display:none;background:#EEEEEE;'>
                                        <center><h3>Feedback for Q1</h3></center>
                                        <table>".$pTiers["q1Fed"]."</table>
                                        <center><h3>Feedback for Q2</h3></center>
                                        <table>".$pTiers["q2Fed"]."</table>
                                        <center><h3>Feedback for Q3</h3></center>
                                        <table>".$pTiers["q3Fed"]."</table>
                                        <center><h3>Feedback for Q4</h3></center>
                                        <table>".$pTiers["q4Fed"]."</table>
                                    </div>
                                </td>
                                <td style='background: #FFFFFF;' valign='top'>";
                $this->html .= "<a href=\"javascript:ShowOrHide('1".key($projectTiers)."','')\">Show/Hide Comments for Committee</a>
                                    <div id='1".key($projectTiers)."' style='display:none'>
                                        <center><h3>Rationale for Q5</h3></center>
                                        <table>".$pTiers["q6Rat"]."</table>
                                        <center><h3>Rationale for Q6</h3></center>
                                        <table>".$pTiers["q7Rat"]."</table>
                                        <center><h3>Rationale for Q7</h3></center>
                                        <table>".$pTiers["q8Rat"]."</table>
                                        <center><h3>Rationale for Q8</h3></center>
                                        <table>".$pTiers["q9Rat"]."</table>
                                    </div>
                                    <hr />
                                    <a href=\"javascript:ShowOrHide('11".key($projectTiers)."','')\">Show/Hide Feedback for Project Leader</a>
                                    <div id='11".key($projectTiers)."' style='display:none;background:#EEEEEE;'>
                                        <center><h3>Feedback for Q5</h3></center>
                                        <table>".$pTiers["q6Fed"]."</table>
                                        <center><h3>Feedback for Q6</h3></center>
                                        <table>".$pTiers["q7Fed"]."</table>
                                        <center><h3>Feedback for Q7</h3></center>
                                        <table>".$pTiers["q8Fed"]."</table>
                                    </div>
                                </td>
                             </tr>";
                next($projectTiers);
            }
            
        }
        $this->html .= "</table><br />";
    }

    // Generates the rows for the table.  Returns the array of values ($array)
    static function generateRow($questionNumber, $array, $post, $person){
        global $getPerson;
        $array["q{$questionNumber}Rat"] .= "<tr>
                                 <td valign='top'><b>{$person->getName()}&nbsp;(Tier&nbsp;".self::getValueOf($post['rating'])."):</b> {$post["comment"]}</td>
                             </tr>";
        $array["q{$questionNumber}Fed"] .= "<tr>
                                 <td valign='top'>";
        $array["q{$questionNumber}Fed"] .= "<b>{$person->getName()}&nbsp;(Tier&nbsp;".self::getValueOf($post['rating'])."):</b>";
        $array["q{$questionNumber}Fed"] .= "{$post["feedback"]}</td>
                             </tr>";
        $catNumber = 1;
        if($questionNumber >= 6){
            if($questionNumber >= 7){
                $catNumber = 3;
            }
            else{
                $catNumber = 2;
            }
        }
        if(self::getValueOf($post['rating']) > 0){
            $array["{$catNumber}_".self::getValueOf($post['rating'])] += 1;
            $array["nQ{$questionNumber}"] += 1;
        }
        return $array;
    }

    function showBudgetTableFor($type){
        global $wgOut, $wgScriptPath, $wgServer, $pl_language_years;
        $this->html .= "<h3>$type Budget Summary</h3>";
        $fullBudget = array();
        if($type == PNI || $type == CNI){
            $fullBudget[] = new Budget(array(array(HEAD, HEAD, HEAD, HEAD)), array(array($type, "Number of Projects", "Total Request", "Project Requests")));
            foreach(Person::getAllPeopleDuring($type, "2012-01-01 00:00:00", "2013-01-01 00:00:00") as $person){
                $budget = $person->getRequestedBudget(2012);
                if($budget != null){
                    $error = false;
                    if($budget->isError()){
                        $error = true;
                    }
                    $projects = $budget->copy()->where(HEAD1, array("Project Name:"))->select(V_PROJ);
                    $projectTotals = $budget->copy()->rasterize()->where(HEAD1, array("TOTALS for April 1, 2013, to March 31, 2014"));
                    $budgetProjects = array();
                    
                    $budgetProjects[] = $budget->copy()->where(V_PERS_NOT_NULL)->limit(0, 1)->select(V_PERS_NOT_NULL);
                    $budgetProjects[] = $projects->copy()->count();
                    $budgetProjects[] = $projectTotals->copy()->select(ROW_TOTAL);
                    if($error){
                        $budgetProjects[0]->xls[0][1]->error = "There is a problem with budget for ".$budgetProjects[0]->xls[0][1]->value;
                    }
                    
                    for($i = 0; $i < 6; $i++){
                        if($projectTotals->nCols() > 0 && isset($projects->xls[1][$i + 1])){
                            $budgetProjects[] = @$budget->copy()->where(HEAD1, array("Project Name:"))->select(V_PROJ, array($projects->xls[1][$i + 1]->getValue()))->join(
                                        new Budget(array(array(MONEY)), array(array($projectTotals->xls[22][$i + 1])))
                                    )->concat();
                        }
                        else{
                            $budgetProjects[] = new Budget();
                        }
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
                $budget = $project->getRequestedBudget(2012);
                if($budget != null){
                    $people = $budget->copy()->where(HEAD1, array("Name of network investigator submitting request:"))->select(V_PERS_NOT_NULL);
                
                    $budgetPeople = array();
                    $budgetPeople[] = new Budget(array(array(READ)), array(array($project->getName())));
                    $budgetPeople[] = $people->copy()->count();
                    $budgetPeople[] = $budgetTotal = $budget->copy()->where(CUBE_TOTAL)->select(CUBE_TOTAL);
                    
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
            $fullBudget = Budget::union_tables($fullBudget);
            $this->html .= $fullBudget->render(true);
        }
        else if($type == "Full"){
            $fullBudget = new Budget(array(array(HEAD, HEAD, HEAD)), array(array("Categories for April 1, 2013, to March 31, 2014", PNI."s", CNI."s")));
            
            $pniTotals = array();
            $cniTotals = array();
            foreach(Person::getAllPeople(PNI) as $person){
                $budget = $person->getRequestedBudget(2012);
                if($budget != null){
                    $pniTotals[] = $budget->copy()->limit(8, 14)->rasterize()->select(ROW_TOTAL);
                }
            }
            foreach(Person::getAllPeople(CNI) as $person){
                $budget = $person->getRequestedBudget(2012);
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
                $hqps = Dashboard::getHQP($project, 0, $pos, "2012-01-01 00:00:00", "2013-01-01 00:00:00");
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
                    <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
                    $hqp_num
                    </a>
                    <div style="display: none;" id="$div_id" class="cell_details_div">
                        <p><span class="label">$p_name / HQP / $pos:</span> 
                        <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
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
            $papers = Dashboard::getPapers($project, '', "Publication", "2012-01-01 00:00:00", "2013-01-01 00:00:00");
            $paper_num = count($papers);
            $paper_details = Dashboard::paperDetails($papers);
            
            //Merge for totals array, will eleminate duplicates later.
            $totals['Publications'] = array_merge($totals['Publications'], $papers);

            $lnk_id = "lnk_public_" . $p_id;
            $div_id = "div_public_" . $p_id;
            
            $chunk .= "<td align='right'>";
            if($paper_num > 0){
                $chunk .=<<<EOF
                    <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
                    $paper_num
                    </a>
                    <div style="display: none;" id="$div_id" class="cell_details_div">
                        <p><span class="label">$p_name / Publications:</span> 
                        <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
                        <ul>$paper_details</ul>
                    </div>
                    </td>
EOF;
            }
            else{
                $chunk .= "0</td>";
            }
            

            // Artifacts.
            $papers = Dashboard::getPapers($project, '', "Artifact", "2012-01-01 00:00:00", "2013-01-01 00:00:00");
            $paper_num = count($papers);
            $paper_details = Dashboard::paperDetails($papers);
            
            //Merge for totals array, will eleminate duplicates later.
            $totals['Artifacts'] = array_merge($totals['Artifacts'], $papers);

            $lnk_id = "lnk_artif_" . $p_id;
            $div_id = "div_artif_" . $p_id;
            
            $chunk .= "<td align='right'>";
            if($paper_num > 0){
                $chunk .=<<<EOF
                    <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
                    $paper_num
                    </a>
                    <div style="display: none;" id="$div_id" class="cell_details_div">
                        <p><span class="label">$p_name / Artifacts:</span> 
                        <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
                        <ul>$paper_details</ul>
                    </div>
                    </td>
EOF;
            }
            else{
                $chunk .= "0</td>";
            }
            

            // Contributions
            $contributions = Dashboard::getContributions($project, '', '2012');
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
                    <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
                    $contribution_sum
                    </a>
                    <div style="display: none;" id="$div_id" class="cell_details_div">
                        <p><span class="label">$p_name / Contributions:</span> 
                        <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
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
                    <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
                    $num
                    </a>
                    <div style="display: none;" id="$div_id" class="cell_details_div">
                        <p><span class="label">Total {$key}:</span> 
                        <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
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
                    <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
                    $sum
                    </a>
                    <div style="display: none;" id="$div_id" class="cell_details_div">
                        <p><span class="label">Total {$key}:</span> 
                        <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
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
                    <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
                    $contribution_sum
                    </a>
                    <div style="display: none;" id="$div_id" class="cell_details_div">
                        <p><span class="label">Contributions / $readable:</span> 
                        <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
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
                <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
                $contribution_sum
                </a>
                <div style="display: none;" id="$div_id" class="cell_details_div">
                    <p><span class="label">Contributions / Total:</span> 
                    <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
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
                $hqps = Dashboard::getHQP(0, $person, 'all', "2012-01-01 00:00:00", "2013-01-01 00:00:00");
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
                        <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
                        $hqp_num
                        </a>
                        <div style="display: none;" id="$div_id" class="cell_details_div">
                            <p><span class="label">$pname_read / HQP / $pos:</span> 
                            <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
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
                $papers = Dashboard::getPapers(0, $person, "Publication", "2012-01-01 00:00:00", "2013-01-01 00:00:00");
                $paper_num = count($papers);
                $paper_details = Dashboard::paperDetails($papers);
                
                //Merge for totals array, will eleminate duplicates later.
                $totals['Publications'] = array_merge($totals['Publications'], $papers);

                $lnk_id = "lnk_public_" . $p_id;
                $div_id = "div_public_" . $p_id;
                
                $chunk .= "<td align='right'>";
                if($paper_num > 0){
                    $chunk .=<<<EOF
                        <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
                        $paper_num
                        </a>
                        <div style="display: none;" id="$div_id" class="cell_details_div">
                            <p><span class="label">$pname_read / Publications:</span> 
                            <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
                            <ul>$paper_details</ul>
                        </div>
                        </td>
EOF;
                }
                else{
                    $chunk .= "0</td>";
                }
                

                // Artifacts.
                $papers = Dashboard::getPapers(0, $person, "Artifact", "2012-01-01 00:00:00", "2013-01-01 00:00:00");
                $paper_num = count($papers);
                $paper_details = Dashboard::paperDetails($papers);
                
                //Merge for totals array, will eleminate duplicates later.
                $totals['Artifacts'] = array_merge($totals['Artifacts'], $papers);

                $lnk_id = "lnk_artif_" . $p_id;
                $div_id = "div_artif_" . $p_id;
                
                $chunk .= "<td align='right'>";
                if($paper_num > 0){
                    $chunk .=<<<EOF
                        <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
                        $paper_num
                        </a>
                        <div style="display: none;" id="$div_id" class="cell_details_div">
                            <p><span class="label">$pname_read / Artifacts:</span> 
                            <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
                            <ul>$paper_details</ul>
                        </div>
                        </td>
EOF;
                }
                else{
                    $chunk .= "0</td>";
                }
                
                // Contributions
                $contributions = Dashboard::getContributions(0, $person, '2012');
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
                        <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
                        $contribution_sum
                        </a>
                        <div style="display: none;" id="$div_id" class="cell_details_div">
                            <p><span class="label">$pname_read / Contributions:</span> 
                            <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
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
                    <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
                    $num
                    </a>
                    <div style="display: none;" id="$div_id" class="cell_details_div">
                        <p><span class="label">Total {$key}:</span> 
                        <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
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
                    <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
                    $sum
                    </a>
                    <div style="display: none;" id="$div_id" class="cell_details_div">
                        <p><span class="label">Total {$key}:</span> 
                        <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
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

        $cnis = Person::getAllPeopleDuring(CNI, "2012-01-01 00:00:00", "2013-01-01 00:00:00");
        $pnis = Person::getAllPeopleDuring(PNI, "2012-01-01 00:00:00", "2013-01-01 00:00:00");

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
    
            $contributions = Dashboard::getContributions(0, $hqp, '2012');

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
            $contr_cash = Dashboard::filterContributions($uni_contr, 'cash');
            $contribution_sum = Dashboard::contributionSum($contr_cash);
            $contribution_details = Dashboard::contributionDetailsXLS($contr_cash);
            $html .= "<td align='right'>";
            if($contribution_sum > 0){
                $contribution_sum = self::dollar_format( $contribution_sum );
            
                $html .=<<<EOF
                    <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
                    $contribution_sum
                    </a>
                    <div style="display: none;" id="$div_id" class="cell_details_div">
                        <p><span class="label">{$uni} / Cash Contributions:</span> 
                        <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
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
            $contr_inki = Dashboard::filterContributions($uni_contr, 'inki');
            $contribution_sum = Dashboard::contributionSum($contr_inki);
            $contribution_details = Dashboard::contributionDetailsXLS($contr_inki);
            $html .= "<td align='right'>";
            if($contribution_sum > 0){
                $contribution_sum = self::dollar_format( $contribution_sum );
            
                $html .=<<<EOF
                    <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
                    $contribution_sum
                    </a>
                    <div style="display: none;" id="$div_id" class="cell_details_div">
                        <p><span class="label">{$uni} / In-Kind Contributions:</span> 
                        <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
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
                    <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
                    $contribution_sum
                    </a>
                    <div style="display: none;" id="$div_id" class="cell_details_div">
                        <p><span class="label">{$uni} / All Contributions:</span> 
                        <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
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

        $distr = Project::getHQPDistributionDuring("2012-01-01 00:00:00", "2013-01-01 00:00:00");
        $chunk = "
<table class='wikitable sortable' cellspacing='1' cellpadding='2' frame='box' rules='all' width='100%'>
<tr><th>Number of Projects
<th>HQP Associated
";
        foreach ($distr as $k => $v) {
            $chunk .= "<tr><td align='center'>{$k}<td align='right'>{$v}";
        }
        $chunk .= "</table>\n";

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
        $themes = Project::getDefaultThemeNames();
        foreach ($themes as $tname) {
            $chunk .= "<th>{$tname}";
        }

        // First pass: grab data.
        $projects = Project::getAllProjects();
        foreach ($projects as $project) {
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
                $chunk .= "<td align='center'><b>" . $project->getTheme($ind) .
                    '</b> (' . ArrayUtils::get_string($oldv, $ind) . ')</td>';
            }
            
            
            

            $chunk .= '</tr>';
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
