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
    
    function execute($par){
        global $wgOut, $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        $report = new DummyReport(IntakeSummary::$reportName, $me, null, YEAR);
        $wgOut->setPageTitle("Assessor");
        $wgOut->addHTML("<table id='summary' class='wikitable' frame='box' rules='all'>
            <thead>
                <tr>
                    <th>Person</th>
                    <th>In Person Assessment</th>
                    ".IntakeSummary::getHeader($report, false, true)."
                </tr>
            </thead>
            <tbody>");
        $rels = $me->getRelations("Assesses");
        foreach($rels as $rel){
            $person = $rel->getUser2();
            $report->person = $person;
            $wgOut->addHTML("<tr>
                <td>{$person->getNameForForms()}</td>
                <td><a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=InPersonAssessment&person={$person->getId()}'>Form</a></td>
                ".IntakeSummary::getRow($person, $report, false, true)."
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
            $GLOBALS['tabs']['InPersonAssessment'] = TabUtils::createTab("Assessor", "{$wgServer}{$wgScriptPath}/index.php/Special:InPersonAssessment", $selected);
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
