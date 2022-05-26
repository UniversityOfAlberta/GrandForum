<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['IntakeSummary'] = 'IntakeSummary'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['IntakeSummary'] = $dir . 'IntakeSummary.i18n.php';
$wgSpecialPageGroups['IntakeSummary'] = 'reporting-tools';

$wgHooks['SubLevelTabs'][] = 'IntakeSummary::createSubTabs';

function runIntakeSummary($par) {
    IntakeSummary::execute($par);
}

class IntakeSummary extends SpecialPage {
    
    function __construct() {
        SpecialPage::__construct("IntakeSummary", null, true, 'runIntakeSummary');
    }
    
    function userCanExecute($wgUser){
        $person = Person::newFromUser($wgUser);
        return $person->isRoleAtLeast(STAFF);
    }
    
    function execute($par){
        global $wgServer, $wgScriptPath, $wgOut;
        $me = Person::newFromWgUser();
        $wgOut->setPageTitle("Intake Summary");
        $people = Person::getAllPeople(CI);
        
        $report = new DummyReport("IntakeSurvey", $me, null, YEAR);
        
        $wgOut->addHTML("<table id='summary' class='wikitable'>
                            <thead>
                            <tr>
                                <th>Frailty Score</th>");
        $wgOut->addHTML("");
        foreach($report->sections as $section){
            foreach($section->items as $item){
                if($item->blobItem != "" && $item->blobItem !== 0){
                    $wgOut->addHTML("<th>".str_replace("_", " ", $item->blobItem)."</th>");
                }
            }
        }                       
        $wgOut->addHTML("       </tr>
                            </thead>
                            <tbody>");
        
        foreach($people as $person){
            if(AVOIDDashboard::hasSubmittedSurvey($person->getId()) && $this->getBlobData("AVOID_Questions_tab0", "POSTAL", $person, YEAR) != "CFN"){
                $report->person = $person;
                $api = new UserFrailtyIndexAPI();
                $scores = $api->getFrailtyScore($me->getId());
                $wgOut->addHTML("<tr>
                                    <td>{$scores["Total"]}</td>");
                foreach($report->sections as $section){
                    foreach($section->items as $item){
                        if($item->blobItem != "" && $item->blobItem !== 0){
                            $value = $item->getBlobValue();
                            if(is_array($value)){
                                $wgOut->addHTML("<td>".implode(", ", $value)."</td>");
                            }
                            else{
                                $wgOut->addHTML("<td>{$value}</td>");
                            }
                        }
                    }
                }
                $wgOut->addHTML("</tr>");
            }
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
                ]
            });
        </script>");
    }
    
    static function createSubTabs(&$tabs){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle;
        $person = Person::newFromWgUser();
        if($person->isRoleAtLeast(STAFF)){
            $selected = @($wgTitle->getText() == "IntakeSummary") ? "selected" : false;
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("Intake Summary", "{$wgServer}{$wgScriptPath}/index.php/Special:IntakeSummary", $selected);
        }
        return true;
    }
    
    function getBlobData($blobSection, $blobItem, $person, $year){
        $blb = new ReportBlob(BLOB_TEXT, $year, $person->getId(), 0);
        $addr = ReportBlob::create_address("RP_AVOID", $blobSection, $blobItem, 0);
        $result = $blb->load($addr);
        return $blb->getData();
    }
    
}

?>
