<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['SIPReviewTable'] = 'SIPReviewTable'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['SIPReviewTable'] = $dir . 'SIPReviewTable.i18n.php';
$wgSpecialPageGroups['SIPReviewTable'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'SIPReviewTable::createSubTabs';

function runSIPReviewTable($par) {
    SIPReviewTable::execute($par);
}

class SIPReviewTable extends SpecialPage{

    function SIPReviewTable() {
        SpecialPage::__construct("SIPReviewTable", null, false, 'runSIPReviewTable');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF) || $person->isRole(SD));
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        $this->getOutput()->setPageTitle("SIP Review Table");
        if(isset($_GET['download']) && isset($_GET['year']) && isset($_GET['key'])){
            header('Content-Type: data:application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="'.$_GET['key'].' Review.xls"');
            echo SIPReviewTable::generateHTML($_GET['year'], $_GET['key']);
            exit;
        }
        $data = DBFunctions::select(array('grand_eval'),
                                    array('DISTINCT type', 'year'),
                                    array('type' => LIKE('SIP-%')),
                                    array('type' => 'DESC'));
        $wgOut->addHTML("<div id='tabs'>");
        $wgOut->addHTML("<ul>");
        foreach($data as $row){
            $label = str_replace("SIP-", "", $row['type']);
            $wgOut->addHTML("<li><a href='#{$row['type']}'>{$label}</a></li>");
        }
        $wgOut->addHTML("</ul>");
        foreach($data as $row){
            $wgOut->addHTML(SIPReviewTable::generateHTML($row['year'], $row['type'], true));
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
            $html .= "<a class='button' href='$wgServer$wgScriptPath/index.php/Special:SIPReviewTable?download&year={$year}&key={$evalKey}' target='_blank'>Download as Spreadsheet</a>";
        }
        $html .= "<table style='min-width: 1000px;' class='wikitable' id='SIPReviewTable' frame='box' rules='all'>
            <thead>
                <tr>
                    <th colspan='11' style='background: #FFFFFF;'></th>
                    <th colspan='6' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Projects Applying for Renewal</th>
                    <th colspan='4' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Relevance</th>
                    <th colspan='6' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Value Proposition and Uniqueness</th>
                    <th colspan='6' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Project Opportunity</th>
                    <th colspan='4' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Intellectual Property</th>
                    <th colspan='10' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Project Opportunity</th>
                    <th colspan='4' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Team</th>
                    <th colspan='6' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Partner and Contributions</th>
                    <th colspan='6' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Budget, Feasibility and Deliverables</th>
                    <th colspan='8' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Potential Impact</th>
                    <th colspan='6' style='border-left: 2px solid #AAAAAA; white-space:nowrap;'>Recommendation</th>
                </tr>
                <tr>
                    <th>Applicant</th>
                    <th>Institution</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Theme</th>
                    <th>Partners</th>
                    <th>Cash</th>
                    <th>In-Kind</th>
                    <th>Related AGE-WELL project</th>
                    <th>Application&nbsp;PDF</th>
                    <th>Reviewer</th>
                    <th style='border-left: 2px solid #AAAAAA;'>QA</th>
                    <th>Comments</th>
                    <th>QB</th>
                    <th>Comments</th>
                    <th>QC</th>
                    <th>Comments</th>
                    <th style='border-left: 2px solid #AAAAAA;'>Q1</th>
                    <th>Comments</th>
                    <th>Q2</th>
                    <th>Comments</th>
                    <th style='border-left: 2px solid #AAAAAA;'>Q3</th>
                    <th>Comments</th>
                    <th>Q4</th>
                    <th>Comments</th>
                    <th>Q5</th>
                    <th>Comments</th>
                    <th style='border-left: 2px solid #AAAAAA;'>Q6</th>
                    <th>Comments</th>
                    <th>Q7</th>
                    <th>Comments</th>
                    <th>Q8</th>
                    <th>Comments</th>
                    <th style='border-left: 2px solid #AAAAAA;'>Q9</th>
                    <th>Comments</th>
                    <th>Q10</th>
                    <th>Comments</th>
                    <th style='border-left: 2px solid #AAAAAA;'>Q11</th>
                    <th>Comments</th>
                    <th>Q12</th>
                    <th>Comments</th>
                    <th>Q13</th>
                    <th>Comments</th>
                    <th>Q14</th>
                    <th>Comments</th>
                    <th>Q15</th>
                    <th>Comments</th>
                    <th style='border-left: 2px solid #AAAAAA;'>Q16</th>
                    <th>Comments</th>
                    <th>Q17</th>
                    <th>Comments</th>
                    <th style='border-left: 2px solid #AAAAAA;'>Q18</th>
                    <th>Comments</th>
                    <th>Q19</th>
                    <th>Comments</th>
                    <th>Q20</th>
                    <th>Comments</th>
                    <th style='border-left: 2px solid #AAAAAA;'>Q21</th>
                    <th>Comments</th>
                    <th>Q22</th>
                    <th>Comments</th>
                    <th>Q23</th>
                    <th>Comments</th>
                    <th style='border-left: 2px solid #AAAAAA;'>Q24</th>
                    <th>Comments</th>
                    <th>Q25</th>
                    <th>Comments</th>
                    <th>Q26</th>
                    <th>Comments</th>
                    <th>Q27</th>
                    <th>Comments</th>
                    <th style='border-left: 2px solid #AAAAAA;'>Total</th>
                    <th>Recommend?</th>
                    <th>Funding</th>
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
            $report = new DummyReport('RP_SIP_ACC_'.str_replace("SIP-", "", $evalKey), $candidate, $project, $year, true);
            $check = $report->getPDF();
            $button = "";
            if(isset($check[0])){
                $pdf = PDF::newFromToken($check[0]['token']);
                $button = "<a class='button' href='{$pdf->getUrl()}'>Download PDF</a>";
            }

            $title = self::getApplicationBlobValue($year, $candidate->getId(), $projectId, BLOB_TEXT, 'COVER_SHEET', 'PROJECT', str_replace("SIP-", "", $evalKey));
            $type = self::getApplicationBlobValue($year, $candidate->getId(), $projectId, BLOB_TEXT, 'COVER_SHEET', 'TYPE', str_replace("SIP-", "", $evalKey));
            $theme = self::getApplicationBlobValue($year, $candidate->getId(), $projectId, BLOB_TEXT, 'COVER_SHEET', 'WP', str_replace("SIP-", "", $evalKey));
            $partners = self::getApplicationBlobValue($year, $candidate->getId(), $projectId, BLOB_TEXT, 'COVER_SHEET', 'PARTNERS', str_replace("SIP-", "", $evalKey));
            $cash = self::getApplicationBlobValue($year, $candidate->getId(), $projectId, BLOB_TEXT, 'COVER_SHEET', 'CASH', str_replace("SIP-", "", $evalKey));
            $inki = self::getApplicationBlobValue($year, $candidate->getId(), $projectId, BLOB_TEXT, 'COVER_SHEET', 'INKI', str_replace("SIP-", "", $evalKey));
            $previous = self::getApplicationBlobValue($year, $candidate->getId(), $projectId, BLOB_TEXT, 'COVER_SHEET', 'PREVIOUS', str_replace("SIP-", "", $evalKey));
            
            foreach($evaluators as $key => $eval){
                $qA             = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), $projectId, 'A', str_replace("SIP-", "", $evalKey));
                $qAComm         = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), $projectId, 'A_COMMENT', str_replace("SIP-", "", $evalKey));
                $qB             = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), $projectId, 'B', str_replace("SIP-", "", $evalKey));
                $qBComm         = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), $projectId, 'B_COMMENT', str_replace("SIP-", "", $evalKey));
                $qC             = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), $projectId, 'C', str_replace("SIP-", "", $evalKey));
                $qCComm         = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), $projectId, 'C_COMMENT', str_replace("SIP-", "", $evalKey));
            
                $q = array();
                $qComm = array();
                $total = 0;
                for($i = 1; $i <= 27; $i++){
                    $q[$i]     = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), $projectId, $i, str_replace("SIP-", "", $evalKey));
                    $qComm[$i] = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), $projectId, $i.'_COMMENT', str_replace("SIP-", "", $evalKey));
                    $total += intval($q[$i]);
                }
                
                $recommend      = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), $projectId, 'RECOMMEND', str_replace("SIP-", "", $evalKey));
                $funding        = $this->getBlobValue($year, $eval->getId(), $candidate->getId(), $projectId, 'FUNDING', str_replace("SIP-", "", $evalKey));
                
                $html .= "<tr style='border-top: 2px solid #AAAAAA;background:{$background};'>";
                $html .= "<td align='right'>{$candidate->getNameForForms()}</td>";
                $html .= "<td>{$candidate->getUni()}</td>";
                $html .= "<td>{$title}</td>";
                $html .= "<td>{$type}</td>";
                $html .= "<td align='center'>{$theme}</td>";
                $html .= "<td align='center'>{$partners}</td>";
                $html .= "<td align='center'>\${$cash}</td>";
                $html .= "<td align='center'>\${$inki}</td>";
                $html .= "<td align='center'>{$previous}</td>";
                $html .= "<td align='center'>{$button}</td>";
                $html .= "<td>{$eval->getNameForForms()}</td>";
                $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>$qA</td>";
                $html .= "<td valign='top'>$qAComm</td>";
                $html .= "<td align='center'>$qB</td>";
                $html .= "<td valign='top'>$qBComm</td>";
                $html .= "<td align='center'>$qC</td>";
                $html .= "<td valign='top'>$qCComm</td>";
                $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$q[1]}</td>";
                $html .= "<td valign='top'>{$qComm[1]}</td>";
                $html .= "<td align='center'>{$q[2]}</td>";
                $html .= "<td valign='top'>{$qComm[2]}</td>";
                $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$q[3]}</td>";
                $html .= "<td valign='top'>{$qComm[3]}</td>";
                $html .= "<td align='center'>{$q[4]}</td>";
                $html .= "<td valign='top'>{$qComm[4]}</td>";
                $html .= "<td align='center'>{$q[5]}</td>";
                $html .= "<td valign='top'>{$qComm[5]}</td>";
                $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$q[6]}</td>";
                $html .= "<td valign='top'>{$qComm[6]}</td>";
                $html .= "<td align='center'>{$q[7]}</td>";
                $html .= "<td valign='top'>{$qComm[7]}</td>";
                $html .= "<td align='center'>{$q[8]}</td>";
                $html .= "<td valign='top'>{$qComm[8]}</td>";
                $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$q[9]}</td>";
                $html .= "<td valign='top'>{$qComm[9]}</td>";
                $html .= "<td align='center'>{$q[10]}</td>";
                $html .= "<td valign='top'>{$qComm[10]}</td>";
                $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$q[11]}</td>";
                $html .= "<td valign='top'>{$qComm[11]}</td>";
                $html .= "<td align='center'>{$q[12]}</td>";
                $html .= "<td valign='top'>{$qComm[12]}</td>";
                $html .= "<td align='center'>{$q[13]}</td>";
                $html .= "<td valign='top'>{$qComm[13]}</td>";
                $html .= "<td align='center'>{$q[14]}</td>";
                $html .= "<td valign='top'>{$qComm[14]}</td>";
                $html .= "<td align='center'>{$q[15]}</td>";
                $html .= "<td valign='top'>{$qComm[15]}</td>";
                $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$q[16]}</td>";
                $html .= "<td valign='top'>{$qComm[16]}</td>";
                $html .= "<td align='center'>{$q[17]}</td>";
                $html .= "<td valign='top'>{$qComm[17]}</td>";
                $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$q[18]}</td>";
                $html .= "<td valign='top'>{$qComm[18]}</td>";
                $html .= "<td align='center'>{$q[19]}</td>";
                $html .= "<td valign='top'>{$qComm[19]}</td>";
                $html .= "<td align='center'>{$q[20]}</td>";
                $html .= "<td valign='top'>{$qComm[20]}</td>";
                $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$q[21]}</td>";
                $html .= "<td valign='top'>{$qComm[21]}</td>";
                $html .= "<td align='center'>{$q[22]}</td>";
                $html .= "<td valign='top'>{$qComm[22]}</td>";
                $html .= "<td align='center'>{$q[23]}</td>";
                $html .= "<td valign='top'>{$qComm[23]}</td>";
                $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$q[24]}</td>";
                $html .= "<td valign='top'>{$qComm[24]}</td>";
                $html .= "<td align='center'>{$q[25]}</td>";
                $html .= "<td valign='top'>{$qComm[25]}</td>";
                $html .= "<td align='center'>{$q[26]}</td>";
                $html .= "<td valign='top'>{$qComm[26]}</td>";
                $html .= "<td align='center'>{$q[27]}</td>";
                $html .= "<td valign='top'>{$qComm[27]}</td>";
                $html .= "<td style='border-left: 2px solid #AAAAAA;' align='center'>{$total}</td>";
                $html .= "<td align='center'>{$recommend}</td>";
                $html .= "<td align='center'>{$funding}</td>";
                $html .= "</tr>";
            }
        }
        $html .= "</tbody></table>";
        if($container){
            $html .= "</div>";
        }
        return $html;
    }
    
    function getBlobValue($year, $evalId, $candidateId, $projId, $item, $suffix=""){
        if($suffix != ""){
            $suffix = "_".$suffix;
        }
        $addr = ReportBlob::create_address('RP_SIP_REVIEW'.$suffix, 'SIP_REVIEW', $item, $candidateId);
        $blob = new ReportBlob(BLOB_TEXT, $year, $evalId, $projId);
        $blob->load($addr);
        $value = nl2br($blob->getData());
        $value = str_replace("<br", "<br style='mso-data-placement:same-cell'", $value);
        return $value;
    }
    
    static function getApplicationBlobValue($year, $userId, $projId, $type, $section, $item, $suffix=""){
        if($suffix != ""){
            $suffix = "_".$suffix;
        }
        $addr = ReportBlob::create_address('RP_SIP_ACC'.$suffix, $section, $item, 0);
        $blob = new ReportBlob($type, $year, $userId, $projId);
        $blob->load($addr);
        $data = $blob->getData();
        return $data;
    }
    
    static function getApplicationBlobMD5($year, $userId, $projId, $type, $section, $item){
        $addr = ReportBlob::create_address('RP_SIP', $section, $item, 0);
        $blob = new ReportBlob($type, $year, $userId, $projId);
        $blob->load($addr);
        $data = $blob->getMD5();
        return $data;
    }
       
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        
        if((new self)->userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "SIPReviewTable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("SIP Review Table", "$wgServer$wgScriptPath/index.php/Special:SIPReviewTable", $selected);
        }
        return true;
    }

}

?>
