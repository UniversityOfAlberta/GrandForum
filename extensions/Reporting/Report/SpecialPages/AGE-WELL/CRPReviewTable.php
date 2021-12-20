<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['CRPReviewTable'] = 'CRPReviewTable'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['CRPReviewTable'] = $dir . 'CRPReviewTable.i18n.php';
$wgSpecialPageGroups['CRPReviewTable'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'CRPReviewTable::createSubTabs';

function runCRPReviewTable($par) {
    CRPReviewTable::execute($par);
}

class CRPReviewTable extends SpecialPage{

    function CRPReviewTable() {
        SpecialPage::__construct("CRPReviewTable", null, false, 'runCRPReviewTable');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF) || $person->isRole(SD));
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        $this->getOutput()->setPageTitle("CRP Review Table");
        if(isset($_GET['download']) && isset($_GET['year']) && isset($_GET['key'])){
            header('Content-Type: data:application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="'.$_GET['key'].' Review.xls"');
            echo CRPReviewTable::generateHTML($_GET['year'], $_GET['key']);
            exit;
        }
        $data = DBFunctions::select(array('grand_eval'),
                                    array('DISTINCT type', 'year'),
                                    array('type' => LIKE('CRP-%')),
                                    array('type' => 'DESC'));
        $wgOut->addHTML("<div id='tabs'>");
        $wgOut->addHTML("<ul>");
        foreach($data as $row){
            $label = str_replace("CRP-", "", $row['type']);
            $wgOut->addHTML("<li><a href='#{$row['type']}'>{$label}</a></li>");
        }
        $wgOut->addHTML("</ul>");
        foreach($data as $row){
            $wgOut->addHTML(CRPReviewTable::generateHTML($row['year'], $row['type'], true));
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
            $html .= "<a class='button' href='$wgServer$wgScriptPath/index.php/Special:CRPReviewTable?download&year={$year}&key={$evalKey}' target='_blank'>Download as Spreadsheet</a>";
        }
        $html .= "<table style='min-width: 1000px;' class='wikitable' id='CRPReviewTable' frame='box' rules='all'>
            <thead>
                <tr>
                    <th colspan='7' style='background: #FFFFFF;'></th>
                    <th colspan='2' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Alignment to the Request for Proposals</th>
                    <th colspan='2' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Scientific Excellence</th>
                    <th colspan='2' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Networking and Partnerships</th>
                    <th colspan='2' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Knowledge and Technology Exchange and Exploitation</th>
                    <th colspan='2' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Development of Highly Qualified Personnel (HQP)</th>
                    <th colspan='2' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Team and Project Management</th>
                    <th colspan='2' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Budget</th>
                    <th colspan='2' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Overall Comments</th>
                </tr>
                <tr>
                    <th>Applicant</th>
                    <th>Title</th>
                    <th>Primary</th>
                    <th>Secondary</th>
                    <th>Application&nbsp;PDF</th>
                    <th>Supporting Documents</th>
                    <th>Reviewer</th>
                    <th style='border-left: 2px solid #AAAAAA;'>Score</th>
                    <th>Comments</th>
                    <th style='border-left: 2px solid #AAAAAA;'>Score</th>
                    <th>Comments</th>
                    <th style='border-left: 2px solid #AAAAAA;'>Score</th>
                    <th>Comments</th>
                    <th style='border-left: 2px solid #AAAAAA;'>Score</th>
                    <th>Comments</th>
                    <th style='border-left: 2px solid #AAAAAA;'>Score</th>
                    <th>Comments</th>
                    <th style='border-left: 2px solid #AAAAAA;'>Score</th>
                    <th>Comments</th>
                    <th style='border-left: 2px solid #AAAAAA;'>Score</th>
                    <th>Comments</th>
                    <th style='border-left: 2px solid #AAAAAA;'>Score</th>
                    <th>Comments</th>
                </tr>
            </thead>
            <tbody>";
        foreach($candidates as $key => $candidate){
            $projectId = $candidate[1];
            $candidate = $candidate[0];
            if($key % 2 == 0){
                $background = "#FFFFFF";
            }
            else{
                $background = "#EEEEEE";
            }
            $evaluators = $candidate->getEvaluators($year, $evalKey, $projectId);
            $nEval = count($evaluators);
            $project = null;
            if($projectId != 0){
                $project = new Project(array());
                $project->id = $projectId;
            }
            $report = new DummyReport("RP_CRP", $candidate, $project, $year, true);
            $check = $report->getPDF();
            $button = "";
            if(isset($check[0])){
                $pdf = PDF::newFromToken($check[0]['token']);
                $button = "<a class='button' href='{$pdf->getUrl()}'>Download PDF</a>";
            }

            $title = self::getApplicationBlobValue($year, $candidate->getId(), $projectId, BLOB_TEXT, 'COVER', 'TITLE');
            $primary = self::getApplicationBlobValue($year, $candidate->getId(), $projectId, BLOB_TEXT, 'COVER', 'PRIMARY');
            $secondary = self::getApplicationBlobValue($year, $candidate->getId(), $projectId, BLOB_TEXT, 'COVER', 'SECONDARY');
            $merged = self::getApplicationBlobMD5($year, $candidate->getId(), $projectId, BLOB_RAW, 'PART3', 'MERGED');
            
            foreach($evaluators as $key => $eval){
                $alignment      = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), $projectId, 'ALIGNMENT');
                $alignmentComm  = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), $projectId, 'ALIGNMENT_COMMENT');
                $excellence     = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), $projectId, 'EXCELLENCE');
                $excellenceComm = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), $projectId, 'EXCELLENCE_COMMENT');
                $networking     = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), $projectId, 'NETWORKING');
                $networkingComm = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), $projectId, 'NETWORKING_COMMENT');
                $ktee           = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), $projectId, 'KTEE');
                $kteeComm       = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), $projectId, 'KTEE_COMMENT');
                $hqp            = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), $projectId, 'HQP');
                $hqpComm        = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), $projectId, 'HQP_COMMENT');
                $team           = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), $projectId, 'TEAM');
                $teamComm       = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), $projectId, 'TEAM_COMMENT');
                $budget         = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), $projectId, 'BUDGET');
                $budgetComm     = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), $projectId, 'BUDGET_COMMENT');
                $overall        = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), $projectId, 'OVERALL');
                $overallComm    = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), $projectId, 'OVERALL_COMMENT');
                
                $html .= "<tr style='border-top: 2px solid #AAAAAA;background:{$background};'>";
                $html .= "<td align='right'>{$candidate->getNameForForms()}</td>";
                $html .= "<td>{$title}</td>";
                $html .= "<td>{$primary}</td>";
                $html .= "<td>{$secondary}</td>";
                $html .= "<td align='center'>{$button}</td>";
                $html .= "<td align='center'><a href='{$wgServer}{$wgScriptPath}/index.php?action=downloadBlob&id={$merged}' class='button'>Download</a></td>";
                $html .= "<td>{$eval->getNameForForms()}</td>";
                $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$alignment}</td>";
                $html .= "<td valign='top'>{$alignmentComm}</td>";
                $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$excellence}</td>";
                $html .= "<td valign='top'>{$excellenceComm}</td>";
                $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$networking}</td>";
                $html .= "<td valign='top'>{$networkingComm}</td>";
                $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$ktee}</td>";
                $html .= "<td valign='top'>{$kteeComm}</td>";
                $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$hqp}</td>";
                $html .= "<td valign='top'>{$hqpComm}</td>";
                $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$team}</td>";
                $html .= "<td valign='top'>{$teamComm}</td>";
                $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$budget}</td>";
                $html .= "<td valign='top'>{$budgetComm}</td>";
                $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$overall}</td>";
                $html .= "<td valign='top'>{$overallComm}</td>";
                $html .= "</tr>";
            }
        }
        $html .= "</tbody></table>";
        if($container){
            $html .= "</div>";
        }
        return $html;
    }
    
    function getBlobValue($year, $evalId, $candidateId, $projId, $item){
        $addr = ReportBlob::create_address('RP_CRP_REVIEW', 'CRP_REVIEW', $item, $candidateId);
        $blob = new ReportBlob(BLOB_TEXT, $year, $evalId, $projId);
        $blob->load($addr);
        $value = nl2br($blob->getData());
        $value = str_replace("<br", "<br style='mso-data-placement:same-cell'", $value);
        return $value;
    }
    
    static function getApplicationBlobValue($year, $userId, $projId, $type, $section, $item){
        $addr = ReportBlob::create_address('RP_CRP', $section, $item, 0);
        $blob = new ReportBlob($type, $year, $userId, $projId);
        $blob->load($addr);
        $data = $blob->getData();
        return $data;
    }
    
    static function getApplicationBlobMD5($year, $userId, $projId, $type, $section, $item){
        $addr = ReportBlob::create_address('RP_CRP', $section, $item, 0);
        $blob = new ReportBlob($type, $year, $userId, $projId);
        $blob->load($addr);
        $data = $blob->getMD5();
        return $data;
    }
       
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        
        if((new self)->userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "CRPReviewTable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("CRP Review Table", "$wgServer$wgScriptPath/index.php/Special:CRPReviewTable", $selected);
        }
        return true;
    }

}

?>
