<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Report'] = 'Report'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Report'] = $dir . 'Report.i18n.php';
$wgSpecialPageGroups['Report'] = 'reporting-tools';

require_once("ApplicationsTable.php");

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
        $tabs["ProjectReports"] = TabUtils::createTab("Project Reports");
        $tabs["ThemeReports"] = TabUtils::createTab("Theme Reports");
        $tabs["Applications"] = TabUtils::createTab("Applications");
        return true;
    }
    
    static function quarterToStartDate($quarter){
        $year = substr($quarter,0,4);
        $q = substr($quarter,5,2);
        switch($q){
            default:
            case "Q1":
                $start = ($year-1)."-11-15";
                break;
            case "Q2":
                $start = ($year)."-02-15";
                break;
            case "Q3":
                $start = ($year)."-06-15";
                break;
            case "Q4": // Old
                $start = ($year)."-09-15";
                break;
        }
        return $start;
    }
    
    static function quarterToEndDate($quarter){
        $year = substr($quarter,0,4);
        $q = substr($quarter,5,2);
        switch($q){
            default:
            case "Q1":
                $end = ($year)."-02-15";
                break;
            case "Q2":
                $end = ($year)."-06-15";
                break;
            case "Q3":
                $end = ($year)."-09-15";
                break;
            case "Q4": // Old
                $end = ($year)."-12-15";
                break;
        }
        return $end;
    }
    
    /**
     * CURRENT:
     *  R1: Feb 15, 2024
     *  R2: Jun 15, 2025
     *  R3: Oct 15, 2025
     * OLD:
     *  Q1: Mar 15, 2024
     *  Q2: Jun 15, 2024
     *  Q3: Sep 15, 2024
     *  Q4: Dec 15, 2024
     */
    static function dateToProjectQuarter($date){
        $year = substr($date,0,4);
        $month = substr($date,5,5);
        if($month <= "02-15"){
            return ($year)."_Q1";
        }
        else if($month <= "06-15"){
            return ($year)."_Q2";
        }
        else if($month <= "10-15"){
            return ($year)."_Q3";
        }
        else {
            return ($year+1)."_Q1";
        }
        return "";
    }
    
    /**
     * CURRENT:
     *  R1: Mar 15, 2024
     *  R2: Jul 15, 2025
     *  R3: Nov 15, 2025
     * OLD:
     *  Q1: Apr 15, 2024
     *  Q2: Jul 15, 2024
     *  Q3: Oct 15, 2024
     *  Q4: Jan 15, 2025 
     */
    static function dateToThemeQuarter($date){
        $year = substr($date,0,4);
        $month = substr($date,5,5);
        if($month <= "03-15"){
            return ($year)."_Q1";
        }
        else if($month <= "07-15"){
            return ($year)."_Q2";
        }
        else if($month <= "11-15"){
            return ($year)."_Q3";
        }
        else{
            return ($year+1)."_Q1";
        }
        return "";
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        if(!$person->isLoggedIn()){
            return true;
        }
        $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";
        $projects = $person->leadership();
        $themes = $person->getLeadThemes();
        foreach($projects as $project){
            for($i=0;$i<4;$i++){
                $date = date('Y-m-d', time() - 3600*24*30*4*$i - 3600*24*89);
                if($date >= $project->getStartDate()){
                    $quarter = self::dateToProjectQuarter($date);
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ProjectReport" && @$_GET['project'] == $project->getName() && @$_GET['id'] == $quarter)) ? "selected" : false;
                    $link = "{$url}ProjectReport&project={$project->getName()}&id={$quarter}";
                    if($i == 0){
                        $tabs["ProjectReports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()}", $link, $selected);
                    }
                    $tabs["ProjectReports"]['subtabs'][count($tabs["ProjectReports"]['subtabs'])-1]['dropdown'][] = TabUtils::createSubTab(str_replace("Q", "R", str_replace("_", " ", $quarter)), $link, $selected);
                }
            }
        }
        foreach($themes as $theme){
            for($i=0;$i<4;$i++){
                $date = date('Y-m-d', time() - 3600*24*30*4*$i - 3600*24*89);
                if($date >= $theme->getCreated()){
                    $quarter = self::dateToThemeQuarter($date);
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ThemeReport" && @$_GET['project'] == $theme->getAcronym() && @$_GET['id'] == $quarter)) ? "selected" : false;
                    $link = "{$url}ThemeReport&project={$theme->getAcronym()}&id={$quarter}";
                    if($i == 0){
                        $tabs["ThemeReports"]['subtabs'][] = TabUtils::createSubTab("{$theme->getAcronym()}", $link, $selected);
                    }
                    $tabs["ThemeReports"]['subtabs'][count($tabs["ThemeReports"]['subtabs'])-1]['dropdown'][] = TabUtils::createSubTab(str_replace("Q", "R", str_replace("_", " ", $quarter)), $link, $selected);
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
