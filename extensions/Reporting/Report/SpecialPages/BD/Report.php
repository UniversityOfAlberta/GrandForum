<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Report'] = 'Report'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Report'] = $dir . 'Report.i18n.php';
$wgSpecialPageGroups['Report'] = 'reporting-tools';

$wgHooks['TopLevelTabs'][] = 'Report::createTab';
$wgHooks['SubLevelTabs'][] = 'Report::createSubTabs';
$wgHooks['ToolboxLinks'][] = 'Report::createToolboxLinks';

class Report extends AbstractReport{
    
    function __construct(){
        global $config;
        $report = @$_GET['report'];
        $topProjectOnly = false;
        parent::__construct(dirname(__FILE__)."/../../ReportXML/{$config->getValue('networkName')}/$report.xml", -1, false, $topProjectOnly);
    }

    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $tabs["Reports"] = TabUtils::createTab("My Reports");
        $tabs["Applications"] = TabUtils::createTab("Applications");
        return true;
    }
    
    static function dateToProjectQuarter($date){
        $year = substr($date,0,4);
        $month = substr($date,5,5);
        if($month <= "03-15"){
            return "{$year}_Q1";
        }
        else if($month <= "06-15"){
            return "{$year}_Q2";
        }
        else if($month <= "09-15"){
            return "{$year}_Q3";
        }
        else if($month <= "12-15"){
            return "{$year}_Q4";
        }
        else if($month > "12-15"){
            return ($year+1)."_Q1";
        }
        return "";
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";
        $projects = $person->leadership();
        foreach($projects as $project){
            for($i=0;$i<4;$i++){
                $date = date('Y-m-d', time() - 3600*24*30*3*$i);
                if($date >= $project->getStartDate()){
                    $quarter = self::dateToProjectQuarter($date);
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ProjectReport" && @$_GET['project'] == $project->getName() && @$_GET['id'] == $quarter)) ? "selected" : false;
                    $link = "{$url}ProjectReport&project={$project->getName()}&id={$quarter}";
                    if($i == 0){
                        $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()}", $link, $selected);
                    }
                    $tabs["Reports"]['subtabs'][count($tabs["Reports"]['subtabs'])-1]['dropdown'][] = TabUtils::createSubTab(str_replace("_", " ", $quarter), $link, $selected);
                }
            }
        }
        return true;
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $config;

        return true;
    }
}

?>
