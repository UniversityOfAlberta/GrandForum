<?php
require_once('AdminUsageStats.php');

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['AdminUsageStats'] = 'AdminUsageStats'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AdminUsageStats'] = $dir . 'AdminUsageStats.i18n.php';
$wgSpecialPageGroups['AdminUsageStats'] = 'other-tools';

$wgHooks['SubLevelTabs'][] = 'AdminUsageStats::createSubTabs';

class AdminUsageStats extends SpecialPage {

    function __construct() {
        SpecialPage::__construct("AdminUsageStats", STAFF.'+', true);
    }

    function execute($par){
        global $wgUser, $wgOut, $wgServer, $wgScriptPath, $wgTitle;
        $this->getOutput()->setPageTitle("Admin Usage Stats");
        $this->showActionPlanStats();
    }
    
    function showActionPlanStats(){
        global $wgOut;
        $wgOut->addHTML("<h1>My weekly action plan</h1>");
        $plans = ActionPlan::getAll();
        
        $submitted = array();
        $components = array('A' => 0, 
                            'V' => 0, 
                            'O' => 0, 
                            'I' => 0, 
                            'D' => 0, 
                            'S' => 0, 
                            'F' => 0);
        foreach($plans as $plan){
            foreach($plan->getComponents() as $comp => $val){
                if($val == 1){
                    @$components[$comp]++;
                }
            }
            if($plan->getSubmitted()){
                $submitted[] = $plan;
            }
        }
        
        $wgOut->addHTML("<table class='wikitable' frame='box' rules='all'>
            <tr>
                <td class='label'>How many action plans created</td>
                <td align='right'>".count($plans)."</td>
            </tr>
            <tr>
                <td class='label'>What AVOID categories action plans fall under</td>
                <td class='value'>
                    <table>");
        foreach($components as $comp => $count){
            $wgOut->addHTML("<tr><td align='center' style='font-weight: bold;'>{$comp}&nbsp;&nbsp;&nbsp;</td><td align='right'>{$count}</td></tr>");
        }
        $wgOut->addHTML("
                    </table>
                </td>
            </tr>
            <tr>
                <td class='label'>How many action plans submitted</td>
                <td align='right'>".count($submitted)."</td>
            </tr>
        </table>");
    }

    static function createSubTabs(&$tabs){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle;
        $person = Person::newFromWgUser();
        if($person->isRoleAtLeast(STAFF)){
            $selected = @($wgTitle->getText() == "AdminUsageStats") ? "selected" : false;
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("Usage Stats", "{$wgServer}{$wgScriptPath}/index.php/Special:AdminUsageStats", $selected);
        }
        return true;
    }

}

?>
