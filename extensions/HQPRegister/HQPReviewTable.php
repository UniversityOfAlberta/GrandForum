<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['HQPReviewTable'] = 'HQPReviewTable'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['HQPReviewTable'] = $dir . 'HQPReviewTable.i18n.php';
$wgSpecialPageGroups['HQPReviewTable'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'HQPReviewTable::createSubTabs';

function runHQPReviewTable($par) {
    HQPReviewTable::execute($par);
}

class HQPReviewTable extends SpecialPage{

    function HQPReviewTable() {
        SpecialPage::__construct("HQPReviewTable", null, false, 'runHQPReviewTable');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF) || $person->isRole(HQPAC));
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        if(isset($_GET['download']) && isset($_GET['year']) && isset($_GET['key'])){
            header('Content-Type: data:application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="HQP Review.xls"');
            echo HQPReviewTable::generateHTML($_GET['year'], $_GET['key']);
            exit;
        }
        $wgOut->addHTML("<a class='button' href='$wgServer$wgScriptPath/index.php/Special:HQPReviewTable?download&year=2015&key=HQP-2015-07-10' target='_blank'>Downlaod as Spreadsheet</a>");
        $wgOut->addHTML("<div style='overflow-x: auto;'>");
        $wgOut->addHTML(HQPReviewTable::generateHTML(2015, "HQP-2015-07-10"));
        $wgOut->addHTML("</div>");
    }
    
    
    function generateHTML($year, $evalKey){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config;
        $candidates = Person::getAllEvaluates($evalKey, $year);
        $html = "";
        $html .= "<table style='min-width: 1000px;' class='wikitable' id='HQPReviewTable' frame='box' rules='all'>
            <thead>
                <tr>
                    <th colspan='4' style='background: #FFFFFF;'></th>
                    <th colspan='2' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Scholarly Merit & Quality of Proposed Research</th>
                    <th colspan='2' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Fit with AGE-WELL Goals and Priorities</th>
                    <th colspan='2' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Quality of Training Environment</th>
                </tr>
                <tr>
                    <th>HQP</th>
                    <th>Application&nbsp;PDF</th>
                    <th>Reviewer</th>
                    <th>Overall Comments</th>
                    <th style='border-left: 2px solid #AAAAAA;'>Ranking</th>
                    <th>Comments</th>
                    <th style='border-left: 2px solid #AAAAAA;'>Ranking</th>
                    <th>Comments</th>
                    <th style='border-left: 2px solid #AAAAAA;'>Ranking</th>
                    <th>Comments</th>
                </tr>
            </thead>
            <tbody>";
        foreach($candidates as $key => $candidate){
            if($key % 2 == 0){
                $background = "#FFFFFF";
            }
            else{
                $background = "#EEEEEE";
            }
            $evaluators = $candidate->getEvaluators($evalKey, $year);
            $nEval = count($evaluators);

            $report = new DummyReport("HQPApplication", $candidate, null, $year);
            $check = $report->getLatestPDF();
            $button = "";
            if(isset($check[0])){
                $pdf = PDF::newFromToken($check[0]['token']);
                $button = "<a class='button' href='{$pdf->getUrl()}'>Download PDF</a>";
            }
            
            $html .= "<tr style='border-top: 2px solid #AAAAAA;background:{$background};'>";
            $html .= "<td rowspan='$nEval' align='right'>{$candidate->getNameForForms()}</td>";
            $html .= "<td rowspan='$nEval' align='center'>{$button}</td>";
            foreach($evaluators as $key => $eval){
                if($key != 0){
                    $html .= "<tr style='background:{$background};'>";
                }
                
                $overall = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), HQP_REVIEW_OVERALL_COMM);
                $quality = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), HQP_REVIEW_QUALITY);
                $qualityComm = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), HQP_REVIEW_QUALITY_COMM);
                $goals = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), HQP_REVIEW_GOALS);
                $goalsComm = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), HQP_REVIEW_GOALS_COMM);
                $train = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), HQP_REVIEW_TRAIN);
                $trainComm = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), HQP_REVIEW_TRAIN_COMM);
            
                $html .= "<td>{$eval->getNameForForms()}</td>";
                $html .= "<td valign='top'>{$overall}</td>";
                $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$quality}</td>";
                $html .= "<td valign='top'>{$qualityComm}</td>";
                $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$goals}</td>";
                $html .= "<td valign='top'>{$goalsComm}</td>";
                $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$train}</td>";
                $html .= "<td valign='top'>{$trainComm}</td>";
                $html .= "</tr>";
            }
        }
        $html .= "</tbody></table>";
        return $html;
    }
    
    function getBlobValue($year, $evalId, $candidateId, $item){
        $addr = ReportBlob::create_address(RP_HQP_REVIEW, HQP_REVIEW, $item, $candidateId);
        $blob = new ReportBlob(BLOB_TEXT, $year, $evalId, 0);
        $blob->load($addr);
        return nl2br($blob->getData());
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        
        if(self::userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "HQPReviewTable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("HQP Review Table", "$wgServer$wgScriptPath/index.php/Special:HQPReviewTable", $selected);
        }
        return true;
    }

}

?>
