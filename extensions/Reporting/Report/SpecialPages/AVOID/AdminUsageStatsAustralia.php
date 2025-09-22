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
        $group = (isset($_GET['group'])) ? $_GET['group'] : "GroupA";
        $wgOut->addHTML("<div style='font-size: 1.25em;'>
                            <a class='GroupA' href='{$wgServer}{$wgScriptPath}/index.php/Special:AdminUsageStatsAustralia?group=GroupA'>Group A</a> | 
                            <a class='GroupB' href='{$wgServer}{$wgScriptPath}/index.php/Special:AdminUsageStatsAustralia?group=GroupB'>Group B</a>
                         </div>
                         <script type='text/javascript'>
                            $('.$group').css('font-weight', 'bold');
                         </script>");
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
        $group = (isset($_GET['group'])) ? $_GET['group'] : "GroupA";
        if($person->isRoleAtLeast(STAFF) || $postal_code == "CFN" || !$person->isRole($group)){
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
