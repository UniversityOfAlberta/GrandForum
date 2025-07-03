<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['AdminUsageStatsAustralia'] = 'AdminUsageStatsAustralia'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AdminUsageStatsAustralia'] = $dir . 'AdminUsageStatsAustralia.i18n.php';
$wgSpecialPageGroups['AdminUsageStatsAustralia'] = 'other-tools';

$wgHooks['SubLevelTabs'][] = 'AdminUsageStatsAustralia::createSubTabs';

class AdminUsageStatsAustralia extends AdminUsageStats {

    function __construct() {
        SpecialPage::__construct("AdminUsageStatsAustralia", STAFF.'+', true);
    }

    function execute($par){
        global $wgUser, $wgOut, $wgServer, $wgScriptPath, $wgTitle;
        $this->getOutput()->setPageTitle("Admin");
        $this->showActionPlanStats();
        $this->showRegistrantsStats();
        $this->showCommunityProgramStats();
        $this->showEducationStats();
        $this->showResourcesStats();
        $this->showCompletionStats();
    }
    
    function exclude($userId){
        $person = Person::newFromId($userId);
        if($person->getId() == 0){ return true; }
        $postal_code = AdminDataCollection::getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "AVOID_Questions_tab0", "POSTAL", $person->getId());
        if($person->isRoleAtLeast(STAFF) || $postal_code == "CFN" || !$person->isRole("GroupA")){
            return true;
        }
        return false;
    }

    static function createSubTabs(&$tabs){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle;
        $person = Person::newFromWgUser();
        if($person->isRoleAtLeast(STAFF)){
            $selected = @($wgTitle->getText() == "AdminUsageStatsAustralia") ? "selected" : false;
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("Admin (AUS)", "{$wgServer}{$wgScriptPath}/index.php/Special:AdminUsageStatsAustralia", $selected);
        }
        return true;
    }

}

?>
