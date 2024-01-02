<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['NineMonthSummary'] = 'NineMonthSummary'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['NineMonthSummary'] = $dir . 'NineMonthSummary.i18n.php';
$wgSpecialPageGroups['NineMonthSummary'] = 'reporting-tools';

$wgHooks['SubLevelTabs'][] = 'NineMonthSummary::createSubTabs';

function runNineMonthSummary($par) {
    NineMonthSummary::execute($par);
}

class NineMonthSummary extends IntakeSummary {
    
    static $pageTitle = "9 Month Summary";
    static $reportName = "NineMonths";
    static $rpType = "RP_AVOID_NINEMO";
    
    function __construct() {
        SpecialPage::__construct("NineMonthSummary", null, true, 'runNineMonthSummary');
    }
    
    static function createSubTabs(&$tabs){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle, $config;
        $person = Person::newFromWgUser();
        if($person->isRoleAtLeast(STAFF) && $config->getValue('networkFullName') != "AVOID Australia"){
            $selected = @($wgTitle->getText() == "NineMonthSummary") ? "selected" : false;
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("9 Month Summary", "{$wgServer}{$wgScriptPath}/index.php/Special:NineMonthSummary", $selected);
        }
        return true;
    }
    
}

?>
