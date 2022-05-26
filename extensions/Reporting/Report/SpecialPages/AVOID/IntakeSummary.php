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
    
    static $map = array(
        'avoid_age' => 'Age',
        'avoid_gender' => 'Gender',
        'GENDERSPECIFY' => 'Gender (Specify)',
        'POSTAL' => 'Postal Code',
        'ethnicity_avoid' => 'Ethnicity1',
        'ethnicity_avoid2' => 'Ethnicity2',
        'ETHNICITYINDIGENOUSSPECIFY' => 'Ethnicity (Specify)',
        'transportation_avoid' => 'Transportation',
        'income_avoid' => 'Income',
        'assistive_avoid' => 'Assistive',
        'living_avoid' => 'Living',
        'living_environment' => 'Living Environment',
        'LIVINGSPECIFY' => 'Living (Specify)',
        'education_avoid' => 'Education',
        'employment_avoid' => 'Employment',
        'EMPLOYSPECIFY' => 'Employ (Specify)',
        'device_avoid' => 'Device',
        'DEVICESPECIFY' => 'Device (Specify)',
        'tech_avoid' => 'Tech',
        'internetissues_avoid' => 'Internet Issues',
        'program_avoid' => 'AVOID Program',
        'PROGRAMLOCATIONSPECIFY' => 'AVOID Program (Specify)',
        'platform_avoid' => 'Platform',
        'PROGRAMPLATFORMOTHERSPECIFY' => 'Platform (Specify)',
        'PROGRAMOTHERSPECIFY' => 'Program (Specify)',
        
    );
    
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
        foreach($report->sections as $section){
            foreach($section->items as $item){
                if($item->blobItem != "" && $item->blobItem !== 0){
                    $label = (isset(self::$map[$item->blobItem])) ? self::$map[$item->blobItem] : str_replace("_", " ", $item->blobItem);
                    $wgOut->addHTML("<th>{$label}</th>");
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
                $scores = $api->getFrailtyScore($person->getId());
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
