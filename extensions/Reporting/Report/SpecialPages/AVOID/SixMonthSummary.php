<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['SixMonthSummary'] = 'SixMonthSummary'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['SixMonthSummary'] = $dir . 'SixMonthSummary.i18n.php';
$wgSpecialPageGroups['SixMonthSummary'] = 'reporting-tools';

$wgHooks['SubLevelTabs'][] = 'SixMonthSummary::createSubTabs';

function runSixMonthSummary($par) {
    SixMonthSummary::execute($par);
}

class SixMonthSummary extends IntakeSummary {
    
    static $pageTitle = "6 Month Summary";
    static $reportName = "SixMonths";
    static $rpType = "RP_AVOID_SIXMO";
    
    function __construct() {
        SpecialPage::__construct("SixMonthSummary", null, true, 'runSixMonthSummary');
    }
    
    static function createSubTabs(&$tabs){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle;
        $person = Person::newFromWgUser();
        if($person->isRoleAtLeast(STAFF)){
            $selected = @($wgTitle->getText() == "SixMonthSummary") ? "selected" : false;
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("6 Month Summary", "{$wgServer}{$wgScriptPath}/index.php/Special:SixMonthSummary", $selected);
        }
        return true;
    }
    
}

?>
