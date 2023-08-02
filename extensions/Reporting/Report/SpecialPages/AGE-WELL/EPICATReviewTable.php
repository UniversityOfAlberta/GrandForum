<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['EPICATReviewTable'] = 'EPICATReviewTable'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['EPICATReviewTable'] = $dir . 'EPICATReviewTable.i18n.php';
$wgSpecialPageGroups['EPICATReviewTable'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'EPICATReviewTable::createSubTabs';

function runEPICATReviewTable($par) {
    EPICATReviewTable::execute($par);
}

class EPICATReviewTable extends SpecialPage{

    function __construct() {
        SpecialPage::__construct("EPICATReviewTable", null, false, 'runEPICATReviewTable');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF) || $person->isRole(HQPAC) || $person->isRole(SD));
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        if(isset($_GET['download']) && isset($_GET['year']) && isset($_GET['key'])){
            header('Content-Type: data:application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="'.$_GET['key'].' Review.xls"');
            echo EPICATReviewTable::generateHTML($_GET['year'], $_GET['key']);
            exit;
        }
        $data = DBFunctions::select(array('grand_eval'),
                                    array('DISTINCT type', 'year'),
                                    array('type' => LIKE('EPIC-%')),
                                    array('type' => 'DESC'));
        $wgOut->addHTML("<div id='tabs'>");
        $wgOut->addHTML("<ul>");
        foreach($data as $row){
            $label = str_replace("EPIC-", "", $row['type']);
            $wgOut->addHTML("<li><a href='#{$row['type']}'>{$label}</a></li>");
        }
        $wgOut->addHTML("</ul>");
        foreach($data as $row){
            $wgOut->addHTML(EPICATReviewTable::generateHTML($row['year'], $row['type'], true));
        }
        $wgOut->addHTML("</div>");
        $wgOut->addHTML("<script type='text/javascript'>
            $('#tabs').tabs();
        </script>");
    }
    
    
    function generateHTML($year, $evalKey, $container=false){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config;
        $candidates = Person::getAllEvaluates($evalKey, $year);
        $html = "";
        if($container){
            $html .= "<div id='{$evalKey}' style='overflow-x: auto;'>";
            $html .= "<a class='button' href='$wgServer$wgScriptPath/index.php/Special:EPICATReviewTable?download&year={$year}&key={$evalKey}' target='_blank'>Download as Spreadsheet</a>";
        }
        $html .= "<table style='min-width: 1000px;' class='wikitable' id='EPICATReviewTable' frame='box' rules='all'>
            <thead>
                <tr>
                    <th colspan='7' style='background: #FFFFFF;'></th>";
        if($evalKey == "EPIC-2023-Special"){
            $html .= "<th colspan='2' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Objectives & Rationale</th>
                      <th colspan='2' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Deliverables & Feasibility</th>
                      <th colspan='2' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Potential Impact</th>";
        }
        else{
            $html .= "<th colspan='2' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Alignment with EPIC-AT Program Goals</th>
                      <th colspan='2' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Personal Statement</th>
                      <th colspan='2' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Scholarly Merit</th>
                      <th colspan='2' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Potential Impact and Feasibility</th>";
        }
        $html .= "</tr>
                <tr>
                    <th>HQP</th>
                    <th>University</th>
                    <th>Level</th>
                    <th>Fellowship</th>
                    <th>Application&nbsp;PDF</th>
                    <th>Reviewer</th>
                    <th>Overall Comments</th>";
        if($evalKey == "EPIC-2023-Special"){
            $html .= "<th style='border-left: 2px solid #AAAAAA;'>Ranking</th>
                      <th>Comments</th>
                      <th style='border-left: 2px solid #AAAAAA;'>Ranking</th>
                      <th>Comments</th>
                      <th style='border-left: 2px solid #AAAAAA;'>Ranking</th>
                      <th>Comments</th>";
        }
        else{
            $html .= "<th style='border-left: 2px solid #AAAAAA;'>Ranking</th>
                      <th>Comments</th>
                      <th style='border-left: 2px solid #AAAAAA;'>Ranking</th>
                      <th>Comments</th>
                      <th style='border-left: 2px solid #AAAAAA;'>Ranking</th>
                      <th>Comments</th>
                      <th style='border-left: 2px solid #AAAAAA;'>Ranking</th>
                      <th>Comments</th>";
        }
        $html .= "</tr>
            </thead>
            <tbody>";
        foreach($candidates as $key => $candidate){
            $candidate = $candidate[0];
            if($key % 2 == 0){
                $background = "#FFFFFF";
            }
            else{
                $background = "#EEEEEE";
            }
            $evaluators = $candidate->getEvaluators($year, $evalKey);
            $nEval = count($evaluators);

            $rpType = 'RP_EPIC_AT';
            if($evalKey == "EPIC-2023-Special"){
                $report = new DummyReport("EPIC-AT2023", $candidate, null, $year, true);
            }
            else{
                if($year >= 2023){
                    $rpType = 'RP_EPIC_AT2';
                }
                $report = new DummyReport("EPIC-AT", $candidate, null, $year, true);
            }
            $check = $report->getLatestPDF();
            $button = "";
            if(isset($check[0])){
                $pdf = PDF::newFromToken($check[0]['token']);
                $button = "<a class='button' href='{$pdf->getUrl()}'>Download PDF</a>";
            }
            
            $level = $this->getBlobValue($year, $candidate->getId(), 0, 'HQP_APPLICATION_STAT', BLOB_TEXT, $rpType, HQP_APPLICATION_FORM);
            $uni = $this->getBlobValue($year, $candidate->getId(), 0, HQP_APPLICATION_UNI, BLOB_TEXT, $rpType, HQP_APPLICATION_FORM);
            $lvl = $this->getBlobValue($year, $candidate->getId(), 0, HQP_APPLICATION_LVL, BLOB_ARRAY, $rpType, HQP_APPLICATION_FORM);
            $lvl = (isset($lvl['level'])) ? implode("<br style='mso-data-placement:same-cell' />", $lvl['level']) : "";
            
            foreach($evaluators as $key => $eval){
                $overall = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), HQP_REVIEW_OVERALL_COMM);
                $goals = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), HQP_REVIEW_GOALS);
                $goalsComm = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), HQP_REVIEW_GOALS_COMM);
                $deliv = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), 'HQP_REVIEW_DELIV');
                $delivComm = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), 'HQP_REVIEW_DELIV_COMM');
                $impact = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), 'HQP_REVIEW_IMPACT');
                $impactComm = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), 'HQP_REVIEW_IMPACT_COMM');
                $statement = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), 'HQP_REVIEW_STATEMENT');
                $statementComm = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), 'HQP_REVIEW_STATEMENT_COMM');
                $quality = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), HQP_REVIEW_QUALITY);
                $qualityComm = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), HQP_REVIEW_QUALITY_COMM);
                $impact = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), 'HQP_REVIEW_IMPACT');
                $impactComm = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), 'HQP_REVIEW_IMPACT_COMM');
            
                $html .= "<tr style='border-top: 2px solid #AAAAAA;background:{$background};'>";
                $html .= "<td align='right'>{$candidate->getNameForForms()}</td>";
                $html .= "<td>{$uni}</td>";
                $html .= "<td>{$level}</td>";
                $html .= "<td style='white-space:nowrap;'>{$lvl}</td>";
                $html .= "<td align='center'>{$button}</td>";
                $html .= "<td>{$eval->getNameForForms()}</td>";
                $html .= "<td valign='top'>{$overall}</td>";
                if($evalKey == "EPIC-2023-Special"){
                    $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$goals}</td>";
                    $html .= "<td valign='top'>{$goalsComm}</td>";
                    $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$deliv}</td>";
                    $html .= "<td valign='top'>{$delivComm}</td>";
                    $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$impact}</td>";
                    $html .= "<td valign='top'>{$impactComm}</td>";
                }
                else{
                    $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$goals}</td>";
                    $html .= "<td valign='top'>{$goalsComm}</td>";
                    $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$statement}</td>";
                    $html .= "<td valign='top'>{$statementComm}</td>";
                    $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$quality}</td>";
                    $html .= "<td valign='top'>{$qualityComm}</td>";
                    $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$impact}</td>";
                    $html .= "<td valign='top'>{$impactComm}</td>";
                }
                $html .= "</tr>";
            }
        }
        $html .= "</tbody></table>";
        if($container){
            $html .= "</div>";
        }
        return $html;
    }
    
    function getBlobValue($year, $evalId, $candidateId, $item, $blobType=BLOB_TEXT, $rp='RP_EPIC_REVIEW', $section=HQP_REVIEW){
        $addr = ReportBlob::create_address($rp, $section, $item, $candidateId);
        $blob = new ReportBlob($blobType, $year, $evalId, 0);
        $blob->load($addr);
        $value = $blob->getData();
        if($blobType == BLOB_TEXT){
            $value = nl2br($value);
            $value = str_replace("<br", "<br style='mso-data-placement:same-cell'", $value);
        }
        return $value;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        
        if((new self)->userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "EPICATReviewTable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("EPIC-AT Review Table", "$wgServer$wgScriptPath/index.php/Special:EPICATReviewTable", $selected);
        }
        return true;
    }

}

?>
