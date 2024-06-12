<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['RawData'] = 'RawData'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['RawData'] = $dir . 'RawData.i18n.php';
$wgSpecialPageGroups['RawData'] = 'reporting-tools';

$wgHooks['SubLevelTabs'][] = 'RawData::createSubTabs';

function runRawData($par) {
    RawData::execute($par);
}

require_once("EQ5D5L.php");

class RawData extends SpecialPage {
    
    static $reportName = "IntakeSurvey";
    static $rpType = "RP_AVOID";
    
    function __construct() {
        global $config;
        SpecialPage::__construct("RawData", null, true, 'runRawData');
    }
    
    function userCanExecute($wgUser){
        $person = Person::newFromUser($wgUser);
        return $person->isRoleAtLeast(STAFF);
    }
    
    static function getRow($person, $report, $type=false, $simple=false){
        global $wgServer, $wgScriptPath, $config, $EQ5D5L;
        $me = Person::newFromWgUser();

        $api = new UserFrailtyIndexAPI();
        $baseScores = $api->getFrailtyScore($person->getId(), "RP_AVOID");
        $sixScores = $api->getFrailtyScore($person->getId(), "RP_AVOID_SIXMO");
        $twelveScores = $api->getFrailtyScore($person->getId(), "RP_AVOID_TWELVEMO");
        $html = "";
        $html .= "<tr data-id='{$person->getId()}'>
                    <td>{$person->getId()}</td>
                    <td>".number_format($baseScores["Total"]/36, 3)."</td>
                    <td>".$baseScores["CFS"]."</td>
                    <td>".number_format($sixScores["Total"]/36, 3)."</td>
                    <td>".$sixScores["CFS"]."</td>
                    <td>".number_format($twelveScores["Total"]/36, 3)."</td>
                    <td>".$twelveScores["CFS"]."</td>
                 </tr>";
        
        return $html;
    }

    function execute($par){
        global $wgServer, $wgScriptPath, $wgOut;
        $me = Person::newFromWgUser();
        $people = Person::getAllPeople(CI);
        
        $report = new DummyReport(static::$reportName, $me, null, YEAR);
        $wgOut->addHTML("<table id='summary' class='wikitable'>
                            <thead>
                                <tr>
                                    <th rowspan='2'>User Id</th>
                                    <th colspan='2'>Baseline</th>
                                    <th colspan='2'>6 Month</th>
                                    <th colspan='2'>12 Month</th>
                                </tr>
                                <tr>
                                    <th>Frailty Score</th>
                                    <th>CFS Score</th>
                                    <th>Frailty Score</th>
                                    <th>CFS Score</th>
                                    <th>Frailty Score</th>
                                    <th>CFS Score</th>
                                </tr>
                            </thead>");
        $wgOut->addHTML("<tbody>");
        
        foreach($people as $person){
            if(!$person->isRoleAtMost(CI)){
                continue;
            }
            if(AVOIDDashboard::hasSubmittedSurvey($person->getId(), static::$rpType) && IntakeSummary::getBlobData("AVOID_Questions_tab0", "POSTAL", $person, YEAR, "RP_AVOID") != "CFN"){
                $report->person = $person;
                $wgOut->addHTML(self::getRow($person, $report));
            }
        }
        $wgOut->addHTML("</tbody>
                        </table>");
        $wgOut->addHTML("
        <style>
            .downloadExcel {
                float: left;
            }
        </style>                
        <script type='text/javascript'>
            $('#summary').DataTable({
                'aLengthMenu': [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']],
                'scrollX': true,
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
    
    static function createSubTabs(&$tabs){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle, $config;
        $person = Person::newFromWgUser();
        if($person->isRoleAtLeast(STAFF) && $config->getValue('networkFullName') != "AVOID Australia"){
            $selected = @($wgTitle->getText() == "RawData") ? "selected" : false;
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("Raw Data", "{$wgServer}{$wgScriptPath}/index.php/Special:RawData", $selected);
        }
        return true;
    }
    
}

?>
