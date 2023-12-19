<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['InPersonAssessment'] = 'InPersonAssessment'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['InPersonAssessment'] = $dir . 'InPersonAssessment.i18n.php';
$wgSpecialPageGroups['InPersonAssessment'] = 'reporting-tools';

$wgHooks['TopLevelTabs'][] = 'InPersonAssessment::createTab';
$wgHooks['SubLevelTabs'][] = 'InPersonAssessment::createSubTabs';

function runInPersonAssessment($par) {
    InPersonAssessment::execute($par);
}

class InPersonAssessment extends SpecialPage {
    
    static $map = array(
        
    );
    
    function __construct() {
        SpecialPage::__construct("InPersonAssessment", null, true, 'runInPersonAssessment');
    }
    
    function userCanExecute($wgUser){
        $person = Person::newFromUser($wgUser);
        return $person->isRole('Assessor');
    }
    
    function generateReport(){
        $api = new UserFrailtyIndexAPI();
        $scores = $api->getFrailtyScore($me->getId());
        exit;
    }
    
    function userTable(){
        global $wgOut;
        $me = Person::newFromWgUser();
        $report = new DummyReport(IntakeSummary::$reportName, $me, null, YEAR);
        
        $wgOut->addHTML("<table id='summary' class='wikitable' frame='box' rules='all'>
            <thead>
                <tr>
                    <th>Person</th>
                    ".IntakeSummary::getHeader($report, true, true)."
                </tr>
            </thead>
            <tbody>");
        
        $people = array();
        foreach(explode(",", $_GET['users']) as $id){
            $people[] = Person::newFromId($id);
        }
        
        foreach($people as $person){
            $report->person = $person;
            $report->reportType = "RP_AVOID";
            $wgOut->addHTML("<tr>
                <td>{$person->getNameForForms()}</td>
                ".IntakeSummary::getRow($person, $report, "Intake", true)."
            </tr>");
            
            $report->reportType = "RP_AVOID_THREEMO";
            $wgOut->addHTML("<tr>
                <td>{$person->getNameForForms()}</td>
                ".IntakeSummary::getRow($person, $report, "3 Month", true)."
            </tr>");
            
            $report->reportType = "RP_AVOID_SIXMO";
            $wgOut->addHTML("<tr>
                <td>{$person->getNameForForms()}</td>
                ".IntakeSummary::getRow($person, $report, "6 Month", true)."
            </tr>");
        }
        $wgOut->addHTML("</tbody>
                        </table>
        <script type='text/javascript'>
            $('#summary').DataTable({
                'aLengthMenu': [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']],
                'iDisplayLength': -1,
                'dom': 'Blfrtip',
                'buttons': [
                    'excel'
                ],
                scrollX: true,
                scrollY: $('#bodyContent').height() - 400
            });
        </script>");
    }
    
    static function getHeader($report){
        $html = "";
        foreach($report->sections as $section){
            foreach($section->items as $item){
                if($item->blobItem != "" && $item->blobItem !== 0){
                    $label = (isset(self::$map[$item->blobItem])) ? self::$map[$item->blobItem] : str_replace("_", " ", $item->blobItem);
                    $html .= "<th>{$label}</th>";
                }
            }
        }
        return $html;
    }
    
    static function getRow($person, $report){
        $html = "";
        foreach($report->sections as $section){
            foreach($section->items as $item){
                if($item->blobItem != "" && $item->blobItem !== 0){
                    $value = $item->getBlobValue();
                    $labels = explode("|", $item->getAttr('labels', ''));
                    $options = explode("|", $item->getAttr('options', ''));
                    if(is_array($value)){
                        $html .= "<td>".implode(", ", $value)."</td>";
                    }
                    else{
                        $html .= "<td>{$value}</td>";
                    }
                }
            }
        }
        return $html;
    }
    
    function execute($par){
        global $wgOut, $wgServer, $wgScriptPath;
        if(isset($_GET['users'])){
            $this->userTable();
            return;
        }
        $me = Person::newFromWgUser();
        $report = new DummyReport(IntakeSummary::$reportName, $me, null, YEAR);
        $assessment = new DummyReport("RP_AVOID_INPERSON", $me, null, YEAR);
        $wgOut->setPageTitle("Assessor");
        $wgOut->addHTML("<table id='summary' class='wikitable' frame='box' rules='all'>
            <thead>
                <tr>
                    <th>Person</th>
                    <th>Frailty Report</th>
                    <th>In Person Assessment</th>
                    ".IntakeSummary::getHeader($report, false, true)."
                    ".InPersonAssessment::getHeader($assessment)."
                </tr>
            </thead>
            <tbody>");
        $rels = $me->getRelations("Assesses");
        foreach($rels as $rel){
            $person = $rel->getUser2();
            $report->person = $person;
            $assessment->person = $person;
            $wgOut->addHTML("<tr>
                <td><a href='{$wgServer}{$wgScriptPath}/index.php/Special:InPersonAssessment?users={$person->getId()}'>{$person->getNameForForms()}</a></td>
                <td><a href='{$wgServer}{$wgScriptPath}/index.php/Special:FrailtyReport?user={$person->getId()}' target='_blank'>Download</a>
                <td><a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=InPersonAssessment&person={$person->getId()}'>Form</a></td>
                ".IntakeSummary::getRow($person, $report, false, true)."
                ".InPersonAssessment::getRow($person, $assessment)."
            </tr>");
        }
        $wgOut->addHTML("</tbody></table>
        <script type='text/javascript'>
            $('#summary').DataTable({
                'aLengthMenu': [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']],
                'iDisplayLength': -1,
                scrollX: true,
                'dom': 'Blfrtip',
                'buttons': [
                    'excel'
                ],
            });
        </script>");
    }

    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        $me = Person::newFromWgUser();
        if($me->isRole("Assessor")){
            $selected = @($wgTitle->getText() == "InPersonAssessment" || ($wgTitle->getText() == "Report" && @$_GET['report'] == "InPersonAssessment")) ? "selected" : false;
            $GLOBALS['tabs']['InPersonAssessment'] = TabUtils::createTab("<en>Assessor</en><fr>Conseiller</fr>", "{$wgServer}{$wgScriptPath}/index.php/Special:InPersonAssessment", $selected);
        }
        return true;
    }


    static function createSubTabs(&$tabs){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle;
            $me = Person::newFromWgUser();
            if($me->isRole("Assessor")){
                $selected = @($wgTitle->getText() == "InPersonAssessment") ? "selected" : false;
            $tabs['InPersonAssessment']['subtabs'][] = TabUtils::createSubTab("Assessment", "{$wgServer}{$wgScriptPath}/index.php/Special:InPersonAssessment", $selected);
        }
        return true;
    }
    
}

?>
