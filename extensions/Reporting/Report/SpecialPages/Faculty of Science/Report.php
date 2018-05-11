<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Report'] = 'Report'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Report'] = $dir . 'Report.i18n.php';
$wgSpecialPageGroups['Report'] = 'reporting-tools';

$wgHooks['TopLevelTabs'][] = 'Report::createTab';
$wgHooks['SubLevelTabs'][] = 'Report::createSubTabs';
$wgHooks['ToolboxLinks'][] = 'Report::createToolboxLinks';
//$notificationFunctions[] = 'Report::createNotification';

class Report extends AbstractReport{
    
    function Report(){
        global $config;
        $report = @$_GET['report'];
        $topProjectOnly = false;
        if(isset($_GET['project']) && ($report == "NIReport" || $report == "HQPReport" || $report == "SABReport")){
            $topProjectOnly = true;
        }
        $this->AbstractReport(dirname(__FILE__)."/../../ReportXML/{$config->getValue('networkName')}/$report.xml", -1, false, $topProjectOnly);
    }
    
    /*static function createNotification(){
        global $notifications, $wgUser, $wgServer, $wgScriptPath;
        $groups = $wgUser->getGroups();
        if($wgUser->isLoggedIn()){
            $me = Person::newFromId($wgUser->getId());
            $notifications[] = new Notification("Annual Report Out of Date", "The data in your report has changed since the last time that you generated your Annual Report. <button id='ar_button'>Regenerate PDF</button><span id='ar_throbber' style='display:none;' class='throbber'></span>
            <script type='text/javascript'>
                $('#ar_button').click(function(){
                    $('#ar_throbber').show();
                    $.get('$wgServer$wgScriptPath/index.php/Special:Report?report=FEC&generatePDF', function(){
                        $('#ar_throbber').hide();
                    });
                });
            </script>", "$wgServer$wgScriptPath/index.php/Special:Report?report=FEC");
        }
    }*/

    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        if($wgUser->isLoggedIn()){
            $tabs["Reports"] = TabUtils::createTab("My Annual Report");
            $tabs["CV"] = TabUtils::createTab("My QA CV");
            $tabs["Chair"] = TabUtils::createTab("Chair");
        }
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        $person = Person::newFromWgUser();
        $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";
        if($person->isRole(NI)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "FEC")) ? "selected" : false;
            $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("Annual Report", "{$url}FEC", $selected);
            
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "QACV")) ? "selected" : false;
            $tabs["CV"]['subtabs'][] = TabUtils::createSubTab("QA CV", "{$url}QACV", $selected);
        }
        if($person->isRole(ISAC)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ChairTable")) ? "selected" : false;
            $tabs["Chair"]['subtabs'][] = TabUtils::createSubTab("Chair", "{$url}ChairTable", $selected);
        }
        return true;
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $config;
        return true;
    }
}

?>
