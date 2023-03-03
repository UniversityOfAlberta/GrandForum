<?php
require_once('UsageVisualizations.php');

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['UsageVisualizations'] = 'UsageVisualizations'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['UsageVisualizations'] = $dir . 'UsageVisualizations.i18n.php';
$wgSpecialPageGroups['UsageVisualizations'] = 'other-tools';

$wgHooks['SubLevelTabs'][] = 'UsageVisualizations::createSubTabs';

class UsageVisualizations extends SpecialPage {

    function __construct() {
        SpecialPage::__construct("UsageVisualizations", STAFF.'+', true);
    }
    
    function exclude($userId){
        $person = Person::newFromId($userId);
        if($person->getId() == 0){ return true; }
        $postal_code = AdminDataCollection::getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "AVOID_Questions_tab0", "POSTAL", $person->getId());
        if($person->isRoleAtLeast(STAFF) || $postal_code == "CFN"){
            return true;
        }
        return false;
    }

    function execute($par){
        global $wgUser, $wgOut, $wgServer, $wgScriptPath, $wgTitle;
        $this->getOutput()->setPageTitle("Usage Visualizations");
        
        $wgOut->addScript("<script src='$wgServer$wgScriptPath/extensions/Visualizations/Vis/js/vis-timeline-graph2d.min.js.js' type='text/javascript'></script>");
        $wgOut->addScript("<link href='$wgServer$wgScriptPath/extensions/Visualizations/Vis/js/vis-timeline-graph2d.min.css' rel='stylesheet' type='text/css' />");
        
        function exclude($userId){
            $person = Person::newFromId($userId);
            if($person->getId() == 0){ return true; }
            $postal_code = AdminDataCollection::getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "AVOID_Questions_tab0", "POSTAL", $person->getId());
            if($person->isRoleAtLeast(STAFF) || $postal_code == "CFN"){
                return true;
            }
            return false;
        }
        
        $people = array();
        foreach(Person::getAllPeople() as $person){
            if($this->exclude($person->getId())){ continue; }
            $people[] = $person;
        }
        
        $registrations = array();
        $logins = array();
        
        $startDate = '2022-01-01';
        $endDate = date('Y-m-d');
        $rangeStart = date('Y-m-d', strtotime($endDate) - 365*24*3600);
        
        $loggedinDC = DataCollection::newFromPage('loggedin');
        for($date=$startDate; $date <= $endDate; $date=date('Y-m-d', strtotime($date) + 24*3600)){
            $logins[$date] = ['x' => $date, 
                              'y' => 0,
                              'group' => 0];
            $registrations[] = ['x' => $date, 
                                'y' => rand(0,10), 
                                'group' => 1];
        }
        foreach($loggedinDC as $dc){
            foreach($dc->getData()['log'] as $date){
                $logins[$date]['y']++;
            }
        }
        $logins = array_values($logins);
        
        $wgOut->addHTML("<h2 style='text-align: center;'>Unique Logins per Day</h2>
                         <div id='logins'></div>
        <script type='text/javascript'>
            // create a dataSet with groups
            var items = ".json_encode($logins).";

            var dataset = new vis.DataSet(items);
            var options = {
                style:'bar',
                drawPoints: false,
                dataAxis: {
                    showMinorLabels: false,
                    left: {
                        title: {
                            text: 'Logins'
                        }
                    }
                },
                start: '{$rangeStart}',
                end: '{$endDate}',
                min: '{$startDate}',
                max: '{$endDate}'
            };
            var graph2d = new vis.Graph2d(document.getElementById('logins'), items, options);

        </script>");
    }

    static function createSubTabs(&$tabs){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle;
        $person = Person::newFromWgUser();
        if($person->isRoleAtLeast(STAFF)){
            $selected = @($wgTitle->getText() == "UsageVisualizations") ? "selected" : false;
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("Usage Visualizations", "{$wgServer}{$wgScriptPath}/index.php/Special:UsageVisualizations", $selected);
        }
        return true;
    }

}

?>
