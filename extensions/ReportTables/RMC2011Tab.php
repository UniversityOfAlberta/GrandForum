<?php

class RMC2011Tab extends AbstractTab {

    function RMC2011Tab(){
        global $wgOut;
        parent::AbstractTab("2011");
        $wgOut->setPageTitle("Evaluation Tables: RMC");
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath, $wgOut, $foldscript;
        
        $this->showContentsTable();

        if(ArrayUtils::get_string($_GET, 'year') == "2011"){
            $wgOut->addScript("<script type='text/javascript'>$(document).ready(function(){ $('#tabs_rmc').tabs('select', 1); });</script>");
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

        case 'nominations':
            // CR nominations
            $this->html .= "<h2>Other</h2><h3>".CNI." Nominations</h3><a id='Nominations'></a>";
            self::showNominations();
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
            <li class='toclevel-1'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2011&summary=question#Q_Summary'><span class='tocnumber'>1</span> <span class='toctext'>Summary of 1-7</span></a>
                <ul>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2011&summary=question#PNI_Summary'><span class='tocnumber'>1.1</span> <span class='toctext'>PNI Summary of Questions 1-7</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2011&summary=question#CNI_Summary'><span class='tocnumber'>1.2</span> <span class='toctext'>CNI Summary of Questions 1-7</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2011&summary=question#Project_Summary'><span class='tocnumber'>1.3</span> <span class='toctext'>Project Summary of Questions 1-7</span></a></li>
                </ul>
            </li>
            <li class='toclevel-1'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2011&summary=budget#Budget_Summary'><span class='tocnumber'>2</span> <span class='toctext'>Budget Summaries</span></a>
                <ul>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2011&summary=budget#PNI_Budget_Summary'><span class='tocnumber'>2.1</span> <span class='toctext'>PNI Budget Summary</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2011&summary=budget#CNI_Budget_Summary'><span class='tocnumber'>2.2</span> <span class='toctext'>CNI Budget Summary</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2011&summary=budget#Project_Budget_Summary'><span class='tocnumber'>2.3</span> <span class='toctext'>Project Budget Summary</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2011&summary=budget#Full_Budget_Summary'><span class='tocnumber'>2.4</span> <span class='toctext'>Full Budget Summary</span></a></li>
                </ul>
            </li>
            <li class='toclevel-1'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2011&summary=productivity#Other'><span class='tocnumber'>3</span> <span class='toctext'>Other</span></a>
                <ul>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2011&summary=productivity#Project_Productivity'><span class='tocnumber'>3.1</span> <span class='toctext'>Project Productivity</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2011&summary=researcher#Researcher_Productivity'><span class='tocnumber'>3.2</span> <span class='toctext'>Researcher Productivity</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2011&summary=nominations#Nominations'><span class='tocnumber'>3.3</span> <span class='toctext'>CNI Nominations</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2011&summary=distribution#Distribution'><span class='tocnumber'>3.4</span> <span class='toctext'>HQP Distribution</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC&year=2011&summary=themes#Themes'><span class='tocnumber'>3.5</span> <span class='toctext'>Project Themes</span></a></li>
                </ul>
            </li>
            </td></tr>
            </table>
EOF;

    }


    function showEvalTableFor($type){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $foldscript;
        $people = Person::getAllPeople();
        $peopleTiers = array();
        $projectTiers = array();
        foreach($people as $person){
            $sql = "SELECT *
                    FROM mw_session_data
                    WHERE (page = 'Special:Evaluate'
                    OR page = 'http://forum.grand-nce.ca/index.php/Special:Evaluate')
                    AND user_id = '{$person->getId()}'";
            $data = DBFunctions::execSQL($sql);
            
            if(DBFunctions::getNRows() > 0 && $person->getName() != "Admin" && $person->getName() != "Adrian.Sheppard"){
                $subData = unserialize($data[0]['data']);
                $subs = array();
                foreach($subData as $key => $row){
                    if(strpos($key, "Eval_I_person") === 0){
                        $subs[] = Person::newFromId(str_replace("Eval_I_person", "", $key));
                    }
                    else if(strpos($key, "Eval_I_project") === 0){
                        $subs[] = Project::newFromId(str_replace("Eval_I_project", "", $key));
                    }
                }
                $pg = str_replace("https", "http", "{$wgServer}{$wgScriptPath}/index.php/Special:Evaluate");
                $session = new SessionData($person->getId(), $pg, SD_REPORT);
                $post = $session->fetch();
                foreach($subs as $sub){
                    $id = "";
                    if($sub instanceof Person && $sub->isRoleDuring($type, "2010-01-01", "2011-01-01")){
                        $id = "person";
                        $array = isset($peopleTiers[$sub->getName()]) ? $peopleTiers[$sub->getName()] : array();
                    }
                    else if($sub instanceof Project && $type == "Project"){
                        $id = "project";
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
                    $array["nRatings"] = isset($array["nRatings"]) ? $array["nRatings"] : 0;
                    $k = "Eval_I_{$id}{$sub->getId()}";
                    @$array["q1Rat"] .= "<tr><td align='right' valign='top'><b>{$person->getName()}&nbsp;(Tier&nbsp;".self::getValueOf($post[$k])."):</b></td><td align='left' valign='top'>{$post["r$k"]}</td></tr>";
                    @$array["1_".self::getValueOf($post[$k])] += 1;
                    $k = "Eval_II_{$id}{$sub->getId()}";
                    @$array["q2Rat"] .= "<tr><td align='right' valign='top'><b>{$person->getName()}&nbsp;(Tier&nbsp;".self::getValueOf($post[$k])."):</b></td><td align='left' valign='top'>{$post["r$k"]}</td></tr>";
                    @$array["1_".self::getValueOf($post[$k])] += 1;
                    $k = "Eval_III_{$id}{$sub->getId()}";
                    @$array["q3Rat"] .= "<tr><td align='right' valign='top'><b>{$person->getName()}&nbsp;(Tier&nbsp;".self::getValueOf($post[$k])."):</b></td><td align='left' valign='top'>{$post["r$k"]}</td></tr>";
                    @$array["1_".self::getValueOf($post[$k])] += 1;
                    $k = "Eval_IV_{$id}{$sub->getId()}";
                    @$array["q4Rat"] .= "<tr><td align='right' valign='top'><b>{$person->getName()}&nbsp;(Tier&nbsp;".self::getValueOf($post[$k])."):</b></td><td align='left' valign='top'>{$post["r$k"]}</td></tr>";
                    @$array["1_".self::getValueOf($post[$k])] += 1;
                    $k = "Eval_V_{$id}{$sub->getId()}";
                    @$array["q5Rat"] .= "<tr><td align='right' valign='top'><b>{$person->getName()}&nbsp;(Tier&nbsp;".self::getValueOf($post[$k])."):</b></td><td align='left' valign='top'>{$post["r$k"]}</td></tr>";
                    @$array["1_".self::getValueOf($post[$k])] += 1;
                    $k = "Eval_VI_{$id}{$sub->getId()}";
                    @$array["q6Rat"] .= "<tr><td align='right' valign='top'><b>{$person->getName()}&nbsp;(Tier&nbsp;".self::getValueOf($post[$k])."):</b></td><td align='left' valign='top'>{$post["r$k"]}</td></tr>";
                    @$array["2_".self::getValueOf($post[$k])] += 1;
                    $k = "Eval_VII_{$id}{$sub->getId()}";
                    @$array["q7Rat"] .= "<tr><td align='right' valign='top'><b>{$person->getName()}: </b></td><td align='left' valign='top'>{$post["r$k"]}</td></tr>";
                    $k = "Eval_VIII_{$id}{$sub->getId()}";
                    @$array["3_".self::getValueOf($post[$k])] += 1;
                    $k = "Eval_IX_{$id}{$sub->getId()}";
                    @$array["3_".self::getValueOf($post[$k])] += 1;
                    @$array["nRatings"] += 1;
                    if($sub instanceof Person && $sub->isRoleDuring($type, "2010-01-01", "2011-01-01")){
                        $peopleTiers[$sub->getName()] = $array;
                    }
                    else if($sub instanceof Project && $type == "Project"){
                        $projectTiers[$sub->getName()] = $array;
                    }
                }
            }
        }
        $this->html .= "<h3>$type Summary of Questions 1-7</h3>";
        $W1 = isset($_POST['w1']) ? $_POST['w1'] : 3;
        $W2 = isset($_POST['w2']) ? $_POST['w2'] : 1;
        $W3 = isset($_POST['w3']) ? $_POST['w3'] : 0;
        $this->html .= "<form method='post' action='$wgServer$wgScriptPath/index.php/Special:EvaluationTable'>
                            Tier 1 Weight: <input type='text' name='w1' value='$W1' size='3' /><br />
                            Tier 2 Weight: <input type='text' name='w2' value='$W2' size='3' /><br />
                            Tier 3 Weight: <input type='text' name='w3' value='$W3' size='3' /><br />
                            <input type='submit' value='Reload' /><br /><br />
                         </form>
                         <table class='wikitable sortable' cellspacing='1' cellpadding='2' style='background: #000000;' width='100%'>
                            <tr>
                                <th style='background: #EEEEEE;'>$type</th><th style='background: #EEEEEE;'>Weighted&nbsp;Average (Q6)</th><th style='background: #EEEEEE; min-width:400px;' width='45%'>Rationale (Q1 - Q5)</th><th style='background: #EEEEEE; min-width:400px;' width='45%'>Rationale (Q6 + Q7)</th>
                            </tr>";
                            
        $rppg = "Special:Report";
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

        if(!empty($peopleTiers)){
            // Obtain a listing of users with data in handle 0.
            $users_h0 = SessionData::list_users_in($rppg, SD_REPORT);
            if (count($users_h0) <= 0) {
                $this->html .= "<i>No data available.</i>";
                return true;
            }
            
            // Build an array of those users (reindex).
            $users = array();
            foreach ($users_h0 as $u) {
                $add = false;
                foreach($people as $person){
                    if($person instanceof Person){
                        if($u['user_id'] == $person->getId()){
                            $add = true;
                        }
                    }
                }
                if($add){
                    $users[$u['user_id']] = $u;
                }
            }
            
            // Obtain a listing of reports done by these users (if anything), submitted
            // but not special reports.
            $submreps = ReportStorage::list_latest_reports(array_keys($users), 1, 0);
            $submusers = array();
            foreach ($submreps as $u) {
                $submusers[$u['user_id']] = $u;
            }

            // Obtain a listing of reports generated but not submitted.
            $nsubmreps = ReportStorage::list_latest_reports(array_keys($users), 0, 0);
            $nsubmusers = array();
            foreach ($nsubmreps as $u) {
                $nsubmusers[$u['user_id']] = $u;
            }

            // Supporting reports (which are not submitted).
            $supreps = ReportStorage::list_latest_reports(array_keys($users), NOTSUBM, 0, RPTP_SUPPORTING);
            $supusers = array();
            foreach ($supreps as $u) {
                $supusers[$u['user_id']] = $u;
            }
            $wgOut->addScript($foldscript);
            while ($personTiers = current($peopleTiers)) {
                $rppg = "$wgServer$wgScriptPath/index.php/Special:Report";
                $person = Person::newFromName(key($peopleTiers));
                
                $sarr = ArrayUtils::get_array($submusers, $person->getId());
                $narr = ArrayUtils::get_array($nsubmusers, $person->getId());
                $suarr = ArrayUtils::get_array($supusers, $person->getId());
            
                $tok = ArrayUtils::get_string($sarr, 'token', ArrayUtils::get_string($narr, 'token'));
                $download1 = "No&nbsp;PDF";
                if (! empty($tok)) {
                    $download1 = "<a href='{$pg}?getpdf={$tok}'>Download&nbsp;PDF</a>";
                }
            
                $tok = ArrayUtils::get_string($suarr, 'token');
                $download2 = "No&nbsp;PDF";
                if (! empty($tok)) {
                    $download2 = "<a href='{$pg}?getpdf={$tok}'>Download&nbsp;PDF</a>";
                }
                $download3 = "";
                foreach($person->getEvaluators() as $evaluator){
                    $evind = new EvaluatorIndex($evaluator);
                    $ls = $evind->list_reports($person);
                    foreach ($ls as &$row) {
                        $download3 .= "<a href='{$pg}?getpdf={$row['token']}'>Download&nbsp;PDF</a><br />";
                    }
                }
                if($download3 == ""){
                    $download3 = "No&nbsp;PDF";
                }   
            
                $tierSum1 = ($W1*$personTiers["1_1"] + $W2*$personTiers["1_2"] + $W3*$personTiers["1_3"])/$personTiers["nRatings"];
                $tierSum2 = ($W1*$personTiers["2_1"] + $W2*$personTiers["2_2"] + $W3*$personTiers["2_3"])/$personTiers["nRatings"];
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
                                            <tr>
                                                <td align='right' valign='top'><b>Evaluator&nbsp;PDFs:</b></td>
                                                <td algin='left' valign='top'>$download3</td>
                                            </tr>
                                        </table>    
                                    </td>
                                    <td style='background: #FFFFFF;' valign='top' align='center'>".number_format(round($tierSum2, 2), 2)."</td>
                                    <td style='background: #FFFFFF;' valign='top'>
                                        <a href=\"javascript:ShowOrHide('2".key($peopleTiers)."','')\">Show/Hide Rationale</a>
                                        <div id='2".key($peopleTiers)."' style='display:none'>
                                            <center><h3>Rationale for Q1</h3></center>
                                            <table>".$personTiers["q1Rat"]."</table>
                                            <center><h3>Rationale for Q2</h3></center>
                                            <table>".$personTiers["q2Rat"]."</table>
                                            <center><h3>Rationale for Q3</h3></center>
                                            <table>".$personTiers["q3Rat"]."</table>
                                            <center><h3>Rationale for Q4</h3></center>
                                            <table>".$personTiers["q4Rat"]."</table>
                                            <center><h3>Rationale for Q5</h3></center>
                                            <table>".$personTiers["q5Rat"]."</table>
                                        </div>
                                    </td>
                                    <td style='background: #FFFFFF;' valign='top'>
                                        <a href=\"javascript:ShowOrHide('1".key($peopleTiers)."','')\">Show/Hide Rationale</a>
                                        <div id='1".key($peopleTiers)."' style='display:none'>
                                            <center><h3>Rationale for Q6</h3></center>
                                            <table>".$personTiers["q6Rat"]."</table>
                                            <center><h3>Rationale for Q7</h3></center>
                                            <table>".$personTiers["q7Rat"]."</table>
                                        </div>
                                    </td>
                                 </tr>";
                next($peopleTiers);
            }
        }
        else if(!empty($projectTiers)){
            while ($pTiers = current($projectTiers)) {
                $rppg = "$wgServer$wgScriptPath/index.php/Special:Report";
                $project = Project::newFromName(key($projectTiers));
                
                $leader = $project->getLeader();
                if($leader != null){
                    $repi = new ReviewerIndex($leader, Person::newFromId($wgUser->getId()));
                    $ls = $repi->list_reports($project);
                }
                else{
                    $ls = array();
                }
                $none = true;
                $download1 = "";
                foreach ($ls as &$row) {
                    $none = false;
                    $download1 = "<a href='{$pg}?getpdf={$row['token']}'>Download&nbsp;PDF</a></td>";
                    break;
                }
                if($none){
                    $download1 .= "No&nbsp;PDF";
                }
            
                $pdf = self::getPLProjectPDF($project);
                if($pdf['download'] != null){
                    $download2 = "<a href='{$pdf['download']}'>Download&nbsp;PDF</a>";
                }
                else if($pdf['tok'] === false){
                    $download2 = "No&nbsp;PDF";
                }
                else{
                    $download2 = "<a href='{$pg}?getpdf={$pdf['tok']}'>Download&nbsp;PDF</a>";
                }

                $download3 = "";
                foreach($project->getEvaluators() as $evaluator){
                    $evind = new EvaluatorIndex($evaluator);
                    $ls = $evind->list_reports($project);
                    foreach ($ls as &$row) {
                        $download3 .= "<a href='{$pg}?getpdf={$row['token']}'>Download&nbsp;PDF</a><br />";
                    }
                }
                if($download3 == ""){
                    $download3 = "No&nbsp;PDF";
                }   
            
                $tierSum1 = ($W1*$pTiers["1_1"] + $W2*$pTiers["1_2"] + $W3*$pTiers["1_3"])/$pTiers["nRatings"];
                $tierSum2 = ($W1*$pTiers["2_1"] + $W2*$pTiers["2_2"] + $W3*$pTiers["2_3"])/$pTiers["nRatings"];
                
                $this->html .= "<tr>
                                    <td style='background: #FFFFFF;' valign='top' align='center'><b><u>".key($projectTiers)."</u></b><br />
                                        <table>
                                            <tr>
                                                <td align='right' valign='top'><b>Researcher&nbsp;PDF:</b></td>
                                                <td algin='left' valign='top'>$download1</td>
                                            </tr>
                                            <tr>
                                                <td align='right' valign='top'><b>Project&nbsp;Leader&nbsp;PDF:</b></td>
                                                <td algin='left' valign='top'>$download2</td>
                                            </tr>
                                            <tr>
                                                <td align='right' valign='top'><b>Evaluator&nbsp;PDFs:</b></td>
                                                <td algin='left' valign='top'>$download3</td>
                                            </tr>
                                        </table>    
                                    </td>
                                    <td style='background: #FFFFFF;' valign='top' align='center'>".number_format(round($tierSum2, 2), 2)."</td>
                                    <td style='background: #FFFFFF;' valign='top'>
                                        <a href=\"javascript:ShowOrHide('2".key($projectTiers)."','')\">Show/Hide Rationale</a>
                                        <div id='2".key($projectTiers)."' style='display:none'>
                                            <center><h3>Rationale for Q1</h3></center>
                                            <table>".$pTiers["q1Rat"]."</table>
                                            <center><h3>Rationale for Q2</h3></center>
                                            <table>".$pTiers["q2Rat"]."</table>
                                            <center><h3>Rationale for Q3</h3></center>
                                            <table>".$pTiers["q3Rat"]."</table>
                                            <center><h3>Rationale for Q4</h3></center>
                                            <table>".$pTiers["q4Rat"]."</table>
                                            <center><h3>Rationale for Q5</h3></center>
                                            <table>".$pTiers["q5Rat"]."</table>
                                        </div>
                                    </td>
                                    <td style='background: #FFFFFF;' valign='top'>
                                        <a href=\"javascript:ShowOrHide('1".key($projectTiers)."','')\">Show/Hide Rationale</a>
                                        <div id='1".key($projectTiers)."' style='display:none'>
                                            <center><h3>Rationale for Q6</h3></center>
                                            <table>".$pTiers["q6Rat"]."</table>
                                            <center><h3>Rationale for Q7</h3></center>
                                            <table>".$pTiers["q7Rat"]."</table>
                                        </div>
                                    </td>
                                 </tr>";
                next($projectTiers);
            }
        }
        $this->html .= "</table><br />";
    }

    function getPLProjectPDF($pj){
        // TODO: This is a horrible hack which came to be since we only considered one to one relations between PLs and Projects.
        // The reality is that it is a one to many relation.
        global $wgServer, $wgScriptPath;
        $pg = "$wgServer$wgScriptPath/index.php/Special:Evaluate";
        $leader = $pj->getLeader();
        $tst = "";
        $len = 0;
        $sub = "";
        $tok = "";
        $download = null;
        if ($pj->getName() == "ENCAD") {
            $download = "http://forum.grand-nce.ca/exception/Robert_Woodbury_ENCAD.pdf";
            $tst = "2011-01-26 12:00:00";
        }
        else if($pj->getName() == "GRNCTY"){
            $download = "http://forum.grand-nce.ca/exception/Robert_Woodbury_GRNCTY.pdf";
            $tst = "2011-01-26 12:00:00";
        }
        else if($pj->getName() == "HCTSL"){
            $download = "http://forum.grand-nce.ca/exception/Robert_Woodbury_HCTSL.pdf";
            $tst = "2011-01-26 12:00:00";
        }
        else if($pj->getName() == "GAMFIT"){
            $download = "http://forum.grand-nce.ca/exception/Nicholas_Graham_GAMFIT.pdf";
            $tst = "2011-01-26 12:00:00";
        }
        /*
        else if($pj->getName() == "HSCEG"){
            $download = "http://forum.grand-nce.ca/exception/Nicholas_Graham_HSCEG.pdf";
        }
*/
        else if($pj->getName() == "NEUROGAM"){
            $download = "http://forum.grand-nce.ca/exception/Nicholas_Graham_NEUROGAM.pdf";
            $tst = "2011-01-26 12:00:00";
        }
        else{
            $sto = new ReportStorage($leader);
            $check = $sto->list_reports($leader->getId(), SUBM, 1, 0, RPTP_LEADER);
            if (count($check) <= 0) {
                // Try unsubmitted report.
                $check = $sto->list_reports($leader->getId(), NOTSUBM, 1, 0, RPTP_LEADER);
            }
            if (count($check) > 0) {
                $tok = $sto->select_report($check[0]['token']);
            }
            else{
                $tok = false;
            }
            if($tok !== false) {
                $tst = $sto->metadata('timestamp');
                $len = $sto->metadata('len_pdf');
                $sub = $sto->metadata('submitted');
            }
        }
        return array("download" => $download, "tok" => $tok, "tst" => $tst, "len" => $len, "sub" => $sub);
    }

    function showBudgetTableFor($type){
        global $wgOut, $wgScriptPath, $wgServer, $pl_language_years;
        $this->html .= "<h3>$type Budget Summary</h3>";
        $fullBudget = array();
        if($type == PNI || $type == CNI){
            $fullBudget[] = new Budget(array(array(HEAD, HEAD, HEAD, HEAD)), array(array($type, "Number of Projects", "Total Request", "Project Requests")));
            foreach(Person::getAllPeople($type) as $person){
                $budget = $person->getSupplementalBudget(2010);
                if($budget != null){
                    $projects = $budget->copy()->where(HEAD1, array("Project Name:"))->select(V_PROJ);
                    $projectTotals = $budget->copy()->rasterize()->where(HEAD1, array("TOTALS for April 1, 2011, to March 31, 2012"));
                    
                    $budgetProjects = array();
                    $budgetProjects[] = $budget->copy()->where(V_PERS_NOT_NULL)->limit(0, 1)->select(V_PERS_NOT_NULL);
                    $budgetProjects[] = $projects->copy()->count();
                    $budgetProjects[] = $projectTotals->copy()->select(ROW_TOTAL);

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
                $budget = $project->getSupplBudget(2010);
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
            $fullBudget = new Budget(array(array(HEAD, HEAD, HEAD)), array(array("Categories for April 1, 2011, to March 31, 2012", PNI."s", CNI."s")));
            
            $pniTotals = array();
            $cniTotals = array();
            foreach(Person::getAllPeople(PNI) as $person){
                $budget = $person->getBudget(2010);
                if($budget != null){
                    $pniTotals[] = $budget->copy()->limit(8, 14)->rasterize()->select(ROW_TOTAL);
                }
            }
            foreach(Person::getAllPeople(CNI) as $person){
                $budget = $person->getBudget(2010);
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
<table class='wikitable sortable' cellspacing='1' cellpadding='2' frame='box' rules='all' width='100%'>
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
        foreach ($projects as $project) {
            $p_name = $project->getName();
            $p_id = $project->getId(); 
            $chunk .= "<tr><td>{$p_name}</td>";

            // HQP stuff            
            foreach ($positions as $pos) {
                $hqps = Dashboard::getHQP($project, 0, $pos, "2010-01-01 00:00:00", "2010-12-31 23:59:59");
                $hqp_num = count($hqps);
                $hqp_details = Dashboard::hqpDetails($hqps);
                
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
            $papers = Dashboard::getPapers($project, '', "Publication", "2010-01-01 00:00:00", "2010-12-31 23:59:59");
            $paper_num = count($papers);
            $paper_details = Dashboard::paperDetails($papers);
            
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
            $papers = Dashboard::getPapers($project, '', "Artifact", "2010-01-01 00:00:00", "2010-12-31 23:59:59");
            $paper_num = count($papers);
            $paper_details = Dashboard::paperDetails($papers);
            
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
            $contributions = Dashboard::getContributions($project, '', '2010');
            $contribution_num = count($contributions);
            $contribution_sum = Dashboard::contributionSum($contributions, 'caki');
            $contribution_details = Dashboard::contributionDetails($contributions, 'caki');
            $chunk .= "<td align='right'>{$contribution_num}</td>";
            
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
        $chunk .= "</table>";
        $chunk .= "<div class='pdf_hide' id='$details_div_id' style='display: none;'></div><br />";
        
        $this->html .= $chunk;
    }

    function showResearcherProductivity() {
        global $wgOut;
        $pnis = Person::getAllPeople(PNI);
        $cnis = Person::getAllPeople(CNI);

        $chunk = "
<table class='wikitable sortable' cellspacing='1' cellpadding='2' frame='box' rules='all' width='100%'>
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
        foreach (array($pnis, $cnis) as $groups) {
            foreach ($groups as $person) {
                $pname = $person->getName();
                $p_id = $person->getId();
                $pname_read = preg_split('/\./', $pname, 2);
                $pname_read = $pname_read[1].", ".$pname_read[0];
                $chunk .= "<tr><td>$pname_read <small>({$person->getType()})</small></td>";
                
                // HQP stuff.
                $hqps = Dashboard::getHQP(0, $person, 'all', "2010-01-01 00:00:00", "2010-12-31 23:59:59");
                foreach ($positions as $pos) {
                    $pos_hqps = Dashboard::filterHQP($hqps, $pos);
                    $hqp_num = count($pos_hqps);
                    $hqp_details = Dashboard::hqpDetails($pos_hqps);
                    
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
                $papers = Dashboard::getPapers(0, $person, "Publication", "2010-01-01 00:00:00", "2010-12-31 23:59:59");
                $paper_num = count($papers);
                $paper_details = Dashboard::paperDetails($papers);
                
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
                $papers = Dashboard::getPapers(0, $person, "Artifact", "2010-01-01 00:00:00", "2010-12-31 23:59:59");
                $paper_num = count($papers);
                $paper_details = Dashboard::paperDetails($papers);
                
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
                $contributions = Dashboard::getContributions(0, $person, '2010');
                $contribution_num = count($contributions);
                $contribution_sum = Dashboard::contributionSum($contributions, 'caki');
                $contribution_details = Dashboard::contributionDetails($contributions, 'caki');
                $chunk .= "<td align='right'>{$contribution_num}</td>";
                
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

        $chunk .= "</table>";
        $chunk .= "<div class='pdf_hide' id='$details_div_id' style='display: none;'></div><br />";


        $this->html .= $chunk;
    }

    function showNominations() {
        global $wgOut;

        $nom = new Nominations();
        $nominations = $nom->get_metric();

        $chunk = "
<table class='wikitable sortable' cellspacing='1' cellpadding='2' frame='box' rules='all' width='100%'>
<tr><th>".CNI."
<th>Number of Projects
<th>Projects
<th>Number of Nominations
<th>Nominations From
<th>Percentage
";

        foreach ($nominations as $cr => $nomination) {
            $p = Person::newFromId($cr);
            $projs = $p->getProjects();

            $nprojs = count($projs);
            $chunk .= "<tr><td>{$p->getNameForForms()}<td align='right'>{$nprojs}";

            $pnames = array();
            foreach ($projs as $proj) {
                $pnames[] = $proj->getName();
            }
            sort($pnames);
            $chunk .= "<td>" . implode(', ', $pnames);

            $chunk .= "<td align='right'>" . count($nomination);

            $from = array_keys($nomination);
            sort($from);
            $chunk .= "<td>" . implode(', ', $from);

            $pct = round(((float)(count($from) / (float)$nprojs)) * 100.0, 1);
            $chunk .= "<td align='right'>{$pct}%";
        }
        $chunk .= "</table>";

        $this->html .= $chunk;
    }

    function showDistribution() {
        global $wgOut;

        $distr = Project::getHQPDistributionDuring("2010-01-01 00:00:00", "2010-12-31 23:59:59");
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
