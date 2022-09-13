<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['ThreeMonthSummary'] = 'ThreeMonthSummary'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['ThreeMonthSummary'] = $dir . 'ThreeMonthSummary.i18n.php';
$wgSpecialPageGroups['ThreeMonthSummary'] = 'reporting-tools';

$wgHooks['SubLevelTabs'][] = 'ThreeMonthSummary::createSubTabs';

function runThreeMonthSummary($par) {
    ThreeMonthSummary::execute($par);
}

class ThreeMonthSummary extends IntakeSummary {
    
    static $pageTitle = "3 Month Summary";
    static $reportName = "ThreeMonths";
    static $rpType = "RP_AVOID_THREEMO";
    
    function __construct() {
        SpecialPage::__construct("ThreeMonthSummary", null, true, 'runThreeMonthSummary');
    }
    
    static function createSubTabs(&$tabs){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle;
        $person = Person::newFromWgUser();
        if($person->isRoleAtLeast(STAFF)){
            $selected = @($wgTitle->getText() == "ThreeMonthSummary") ? "selected" : false;
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("3 Month Summary", "{$wgServer}{$wgScriptPath}/index.php/Special:ThreeMonthSummary", $selected);
        }
        return true;
    }
    
}

?>
