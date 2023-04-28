<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['TwelveMonthSummary'] = 'TwelveMonthSummary'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['TwelveMonthSummary'] = $dir . 'TwelveMonthSummary.i18n.php';
$wgSpecialPageGroups['TwelveMonthSummary'] = 'reporting-tools';

$wgHooks['SubLevelTabs'][] = 'TwelveMonthSummary::createSubTabs';

function runTwelveMonthSummary($par) {
    TwelveMonthSummary::execute($par);
}

class TwelveMonthSummary extends IntakeSummary {
    
    static $pageTitle = "6 Month Summary";
    static $reportName = "TwelveMonths";
    static $rpType = "RP_AVOID_TWELVEMO";
    
    function __construct() {
        SpecialPage::__construct("TwelveMonthSummary", null, true, 'runTwelveMonthSummary');
    }
    
    static function createSubTabs(&$tabs){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle;
        $person = Person::newFromWgUser();
        if($person->isRoleAtLeast(STAFF)){
            $selected = @($wgTitle->getText() == "TwelveMonthSummary") ? "selected" : false;
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("12 Month Summary", "{$wgServer}{$wgScriptPath}/index.php/Special:TwelveMonthSummary", $selected);
        }
        return true;
    }
    
}

?>
