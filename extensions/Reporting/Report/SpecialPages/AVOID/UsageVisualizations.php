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
        global $wgUser, $wgOut, $wgServer, $wgScriptPath, $wgTitle, $config;
        $this->getOutput()->setPageTitle("Usage Visualizations");
        
        $wgOut->addScript("<script src='$wgServer$wgScriptPath/extensions/Visualizations/Vis/js/vis-timeline-graph2d.min.js.js' type='text/javascript'></script>");
        $wgOut->addScript("<link href='$wgServer$wgScriptPath/extensions/Visualizations/Vis/js/vis-timeline-graph2d.min.css' rel='stylesheet' type='text/css' />");
        
        $people = array();
        foreach(Person::getAllPeople() as $person){
            if($this->exclude($person->getId())){ continue; }
            $people[] = $person;
        }
        
        if(!isset($_GET['groupByMonth']) && !isset($_GET['groupByDay'])){
            $_GET['groupByMonth'] = true;
        }
        
        $startDate = substr($config->getValue('projectPhaseDates')[1], 0, 10);
        $endDate = isset($_GET['groupByMonth']) ? substr(date('Y-m-d'),0,7)."-".cal_days_in_month(CAL_GREGORIAN,date('m'),date('Y')) : 
                                                  date('Y-m-d', time() + 24*3600);
        $rangeStart = date('Y-m-d', strtotime($endDate) - 365*24*3600);
        
        // Initialize arrays
        $registrations = array();
        $logins = array();
        $pageviews1 = array();
        $pageviews2 = array();
        $pageviews3 = array();
        $pageviews4 = array();
        for($date=$startDate; $date <= $endDate; $date=date('Y-m-d', strtotime($date) + 24*3600)){
            $date1 = isset($_GET['groupByMonth']) ? substr($date,0,7)."-01" : $date;
            $date2 = isset($_GET['groupByMonth']) ? substr($date,0,7)."-".cal_days_in_month(CAL_GREGORIAN,substr($date,5,2),substr($date,0,4))." 23:59:59" : $date." 23:59:59";
            $logins[$date1] = ['x' => $date1,
                               'end' => $date2,
                               'y' => 0,
                               'group' => '0'];
            $registrations[$date1] = ['x' => $date1,
                                      'end' => $date2,
                                      'y' => '0',
                                      'group' => '0'];
            $pageviews1[$date1] = ['x' => $date1, 
                                   'y' => 0, 
                                   'group' => '0'];
            $pageviews2[$date1] = ['x' => $date1, 
                                   'y' => 0, 
                                   'group' => '1'];
            $pageviews3[$date1] = ['x' => $date1, 
                                   'y' => 0, 
                                  'group' => '2'];
            $pageviews4[$date1] = ['x' => $date1, 
                                   'y' => 0, 
                                   'group' => '3'];
        }
        
        // Logins
        $loggedinDC = DataCollection::newFromPage('loggedin');
        foreach($loggedinDC as $dc){
            foreach($dc->getData()['log'] as $date){
                $date1 = isset($_GET['groupByMonth']) ? substr($date,0,7)."-01" : $date;
                if(isset($logins[$date1])){
                    $logins[$date1]['y']++;
                }
            }
        }
        
        // Registrations
        foreach($people as $person){
            $date = $person->getRegistration(true);
            $date1 = isset($_GET['groupByMonth']) ? substr($date,0,7)."-01" : $date;
            if(isset($registrations[$date1])){
                $registrations[$date1]['y']++;
            }
        }
        
        // PageViews
        $pageViewsDC = DataCollection::newFromPage('EducationResources-Hit');
        foreach($pageViewsDC as $dc){
            foreach(array_unique($dc->getData()['log']) as $date){
                $date1 = isset($_GET['groupByMonth']) ? substr($date,0,7)."-01" : $date;
                if(isset($pageviews1[$date1])){
                    $pageviews1[$date1]['y']++;
                }
            }
        }
        $pageViewsDC = DataCollection::newFromPage('Programs-Hit');
        foreach($pageViewsDC as $dc){
            foreach(array_unique($dc->getData()['log']) as $date){
                $date1 = isset($_GET['groupByMonth']) ? substr($date,0,7)."-01" : $date;
                if(isset($pageviews2[$date1])){
                    $pageviews2[$date1]['y']++;
                }
            }
        }
        $pageViewsDC = DataCollection::newFromPage('CommunityPrograms-Hit');
        foreach($pageViewsDC as $dc){
            foreach(array_unique($dc->getData()['log']) as $date){
                $date1 = isset($_GET['groupByMonth']) ? substr($date,0,7)."-01" : $date;
                if(isset($pageviews3[$date1])){
                    $pageviews3[$date1]['y']++;
                }
            }
        }
        $pageViewsDC = DataCollection::newFromPage('AskAnExpert-Hit');
        foreach($pageViewsDC as $dc){
            foreach(array_unique($dc->getData()['log']) as $date){
                $date1 = isset($_GET['groupByMonth']) ? substr($date,0,7)."-01" : $date;
                if(isset($pageviews4[$date1])){
                    $pageviews4[$date1]['y']++;
                }
            }
        }
        
        $logins = array_values($logins);
        $registrations = array_values($registrations);
        $pageviews = array_merge(array_values($pageviews1), 
                                 array_values($pageviews2), 
                                 array_values($pageviews3), 
                                 array_values($pageviews4));
        $unit = isset($_GET['groupByMonth']) ? "Month" : "Day";
        $daySelected = (isset($_GET['groupByDay'])) ? "selected" : "";
        $monthSelected = (isset($_GET['groupByMonth'])) ? "selected" : "";
        $wgOut->addHTML("<b style='line-height:2em;'>Group By:</b>&nbsp;
                         <select id='groupBy'>
                            <option $daySelected>Day</option>
                            <option $monthSelected>Month</option>
                         </select>
                         <h1 style='text-align: center;'>Unique Logins per {$unit}</h1>
                         <div id='logins'></div>
                         
                         <h1 style='text-align: center;'>Registrations per {$unit}</h1>
                         <div id='registrations'></div>
                         
                         <h1 style='text-align: center;'>Page Views per {$unit}</h1>
                         <div id='pageviews'></div>
        <script type='text/javascript'>
            $('#groupBy').change(function(){
                if($('#groupBy option:selected').val() == 'Day'){
                    document.location = wgServer + wgScriptPath + '/index.php/Special:UsageVisualizations?groupByDay';
                }
                else{
                    document.location = wgServer + wgScriptPath + '/index.php/Special:UsageVisualizations?groupByMonth';
                }
            });
        
            // create a dataSet with groups
            var logins = ".json_encode($logins).";
            var registrations = ".json_encode($registrations).";
            var pageviews = ".json_encode($pageviews).";

            function barChart(id, title, items){
                var dataset = new vis.DataSet(items);
                var options = {
                    style:'bar',
                    stack: true,
                    drawPoints: false,
                    dataAxis: {
                        showMinorLabels: true,
                        left: {
                            title: {
                                text: title
                            }
                        }
                    },
                    start: '{$rangeStart}',
                    end: '{$endDate}',
                    min: '{$startDate}',
                    max: '{$endDate}'
                };
                return graph2d = new vis.Graph2d(document.getElementById(id), items, options);
            }
            
            function lineChart(id, title, items){
                var dataset = new vis.DataSet(items);
                var options = {
                    drawPoints: {
                        style: 'circle'
                    },
                    dataAxis: {
                        showMinorLabels: true,
                        left: {
                            title: {
                                text: title
                            }
                        }
                    },
                    legend: {left:{position:'bottom-left'}},
                    start: '{$rangeStart}',
                    end: '{$endDate}',
                    min: '{$startDate}',
                    max: '{$endDate}'
                };
                return graph2d = new vis.Graph2d(document.getElementById(id), items, options);
            }
            
            var loginsChart        = barChart('logins', 'Logins', logins);
            var registrationsChart = barChart('registrations', 'Registrations', registrations);
            var pageViewsChart     = lineChart('pageviews', 'Page Views', pageviews);
            
            var groups = new vis.DataSet();
            groups.add({id: '0', content: 'Education Resources'});
            groups.add({id: '1', content: 'Programs'});
            groups.add({id: '2', content: 'Community Programs'});
            groups.add({id: '3', content: 'Ask an Expert'});
            
            pageViewsChart.setGroups(groups);

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
