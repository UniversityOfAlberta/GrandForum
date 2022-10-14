<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Report'] = 'Report'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Report'] = $dir . 'Report.i18n.php';
$wgSpecialPageGroups['Report'] = 'reporting-tools';

$wgHooks['TopLevelTabs'][] = 'Report::createTab';
$wgHooks['SubLevelTabs'][] = 'Report::createSubTabs';
$wgHooks['ToolboxLinks'][] = 'Report::createToolboxLinks';

class Report extends AbstractReport{
    
    function Report(){
        global $config;
        $report = @$_GET['report'];
        $topProjectOnly = false;
        $this->AbstractReport(dirname(__FILE__)."/../../ReportXML/{$config->getValue('networkName')}/$report.xml", -1, false, $topProjectOnly);
    }

    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $tabs["Intern"] = TabUtils::createTab("<span class='en'>ELITE Intern Application</span><span class='fr'>Formulaire de demande pour les stagiaires ELITE</span>");
        $tabs["PhD"] = TabUtils::createTab("<span class='en'>Engineering PhD Fellowship Application</span><span class='fr'>Formulaire de demande pour les candidat-e-s de bourse doctorale</span>");
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        $person = Person::newFromWgUser();
        $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";
        if($person->isRole(HQP)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "Application")) ? "selected" : false;
            $tabs["Intern"]['subtabs'][] = TabUtils::createSubTab("<span class='en'>ELITE Intern Application</span><span class='fr'>Formulaire de demande pour les stagiaires ELITE</span>", "{$url}Application", $selected);
            
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "PhDApplication")) ? "selected" : false;
            $tabs["PhD"]['subtabs'][] = TabUtils::createSubTab("<span class='en'>Engineering PhD Fellowship Application</span><span class='fr'>Formulaire de demande pour les candidat-e-s de bourse doctorale</span>", "{$url}PhDApplication", $selected);
        }
        return true;
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $config;
        return true;
    }
}

?>
