<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['EEAReviewTable'] = 'EEAReviewTable'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['EEAReviewTable'] = $dir . 'EEAReviewTable.i18n.php';
$wgSpecialPageGroups['EEAReviewTable'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'EEAReviewTable::createSubTabs';

function runEEAReviewTable($par) {
    EEAReviewTable::execute($par);
}

class EEAReviewTable extends SpecialPage{

    function EEAReviewTable() {
        SpecialPage::__construct("EEAReviewTable", null, false, 'runEEAReviewTable');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF) || $person->isRole(HQPAC) || $person->isRole(SD));
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        $this->getOutput()->setPageTitle("EEA Review Table");
        if(isset($_GET['download']) && isset($_GET['year']) && isset($_GET['key'])){
            header('Content-Type: data:application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="'.$_GET['key'].' Review.xls"');
            echo EEAReviewTable::generateHTML($_GET['year'], $_GET['key']);
            exit;
        }
        $data = DBFunctions::select(array('grand_eval'),
                                    array('DISTINCT type', 'year'),
                                    array('type' => LIKE('EEA-%')),
                                    array('type' => 'DESC'));
        $wgOut->addHTML("<div id='tabs'>");
        $wgOut->addHTML("<ul>");
        foreach($data as $row){
            $label = str_replace("EEA-", "", $row['type']);
            $wgOut->addHTML("<li><a href='#{$row['type']}'>{$label}</a></li>");
        }
        $wgOut->addHTML("</ul>");
        foreach($data as $row){
            $wgOut->addHTML(EEAReviewTable::generateHTML($row['year'], $row['type'], true));
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
            $html .= "<a class='button' href='$wgServer$wgScriptPath/index.php/Special:EEAReviewTable?download&year={$year}&key={$evalKey}' target='_blank'>Download as Spreadsheet</a>";
        }
        $html .= "<table style='min-width: 1000px;' class='wikitable' id='EEAReviewTable' frame='box' rules='all'>
            <thead>
                <tr>
                    <th colspan='4' style='background: #FFFFFF;'></th>
                    <th colspan='2' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Rationale and Approach</th>
                    <th colspan='2' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Feasibility</th>
                    <th colspan='2' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Value Proposition</th>
                    <th colspan='2' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Fit with AGE-WELL Goals and Priorities</th>
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
                    <th style='border-left: 2px solid #AAAAAA;'>Ranking</th>
                    <th>Comments</th>
                </tr>
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

            $report = new DummyReport("EEA", $candidate, null, $year, true);
            $check = $report->getLatestPDF();
            $button = "";
            if(isset($check[0])){
                $pdf = PDF::newFromToken($check[0]['token']);
                $button = "<a class='button' href='{$pdf->getUrl()}'>Download PDF</a>";
            }
            
            foreach($evaluators as $key => $eval){
                $q1 = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), "EEA_REVIEW_1");
                $q2 = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), "EEA_REVIEW_2");
                $q3 = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), "EEA_REVIEW_3");
                $q4 = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), "EEA_REVIEW_4");
                $q1Comm = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), "EEA_REVIEW_1_COMM");
                $q2Comm = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), "EEA_REVIEW_2_COMM");
                $q3Comm = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), "EEA_REVIEW_3_COMM");
                $q4Comm = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), "EEA_REVIEW_4_COMM");
                $overall = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), "EEA_OVERALL_COMM");
            
                $html .= "<tr style='border-top: 2px solid #AAAAAA;background:{$background};'>";
                $html .= "<td align='right'>{$candidate->getNameForForms()}</td>";
                $html .= "<td align='center'>{$button}</td>";
                $html .= "<td>{$eval->getNameForForms()}</td>";
                $html .= "<td>{$overall}</td>";
                $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$q1}</td>";
                $html .= "<td valign='top'>{$q1Comm}</td>";
                $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$q2}</td>";
                $html .= "<td valign='top'>{$q2Comm}</td>";
                $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$q3}</td>";
                $html .= "<td valign='top'>{$q3Comm}</td>";
                $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$q4}</td>";
                $html .= "<td valign='top'>{$q4Comm}</td>";
                $html .= "</tr>";
            }
        }
        $html .= "</tbody></table>";
        if($container){
            $html .= "</div>";
        }
        return $html;
    }
    
    function getBlobValue($year, $evalId, $candidateId, $item){
        $addr = ReportBlob::create_address('RP_EEA_REVIEW', 'EEA_REVIEW', $item, $candidateId);
        $blob = new ReportBlob(BLOB_TEXT, $year, $evalId, 0);
        $blob->load($addr);
        $value = nl2br($blob->getData());
        $value = str_replace("<br", "<br style='mso-data-placement:same-cell'", $value);
        return $value;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        
        if((new self)->userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "EEAReviewTable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("EEA Review Table", "$wgServer$wgScriptPath/index.php/Special:EEAReviewTable", $selected);
        }
        return true;
    }

}

?>
