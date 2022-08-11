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
        $this->getOutput()->setPageTitle("Admin Usage Stats (Work in Progress)");
        $this->showActionPlanStats();
        $this->showRegistrantsStats();
        $this->showProgramStats();
    }
    
    function exclude($userId){
        $person = Person::newFromId($userId);
        $postal_code = AdminDataCollection::getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "AVOID_Questions_tab0", "POSTAL", $person->getId());
        if($person->isRoleAtLeast(STAFF) || $postal_code == "CFN"){
            return true;
        }
        return false;
    }
    
    function showActionPlanStats(){
        global $wgOut;
        $wgOut->addHTML("<h1>My weekly action plan</h1>");
        $plans = array();
        foreach(ActionPlan::getAll() as $plan){
            if($this->exclude($plan->getUserId())){ continue; }
            $plans[] = $plan;
        }
        
        $users = array();
        $submitted = array();
        $components = array('A' => 0, 
                            'V' => 0, 
                            'O' => 0, 
                            'I' => 0, 
                            'D' => 0, 
                            'S' => 0, 
                            'F' => 0);
        foreach($plans as $plan){
            $users[$plan->getUserId()] = $plan->getPerson();
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
                <td class='label'>How many users with action plans</td>
                <td align='right'>".count($users)."</td>
            </tr>
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
    
    function showRegistrantsStats(){
        global $wgOut;
        $wgOut->addHTML("<h1>Registrants</h1>");
        
        $members = array();
        $partners = array();
        $clinicians = array();
        $submitted = array();
        foreach(Person::getAllPeople() as $person){
            if($this->exclude($person->getId())){ continue; }
            if($person->isRole("Member")){ // TODO: Should be changed to role CONSTANT
                $members[] = $person;
            }
            if($person->isRole("Provider")){
                if($person->getExtra("ageOfLovedOne") != "" && 
                   $person->getExtra("ageField") != ""){
                    $partners[] = $person;
                }
                else if($person->getExtra("practiceField") != "" && 
                        $person->getExtra("roleField") != ""){
                    $clinicians[] = $person;
                }
            }
            if(AVOIDDashboard::hasSubmittedSurvey($person->getId())){
                $submitted[] = $person;
            }
        }
        
        $wgOut->addHTML("<table class='wikitable' frame='box' rules='all'>
            <tr>
                <td class='label'>Total number of members</td>
                <td align='right'>".count($members)."</td>
            </tr>
            <tr>
                <td class='label'>Total number of clinicians</td>
                <td align='right'>".count($clinicians)."</td>
            </tr>
            <tr>
                <td class='label'>Total number care partners/guests</td>
                <td align='right'>".count($partners)."</td>
            </tr>
            <tr>
                <td class='label'>Total number completed Assessments</td>
                <td align='right'>".count($submitted)."</td>
            </tr>
        </table>");
    }
    
    function showProgramStats(){
        global $wgOut;
        $wgOut->addHTML("<h1>Peer Coaching (be a coach, and get a coach)</h1>");
        
        $dcs = array_merge(DataCollection::newFromPage('Program-PeerCoaching'), 
                           DataCollection::newFromPage('Program-PeerCoachingVolunteer'));
        $count = 0;
        foreach($dcs as $dc){
            if($this->exclude($dc->getUserId())){ continue; }
            $count += @$dc->getField('count');
        }
        
        $wgOut->addHTML("<table class='wikitable' frame='box' rules='all'>
            <tr>
                <td class='label'>Number of hits on page</td>
                <td align='right'>$count</td>
            </tr>
        </table>");
        
        $wgOut->addHTML("<h1>Cyber Seniors</h1>");
        
        $dcs = DataCollection::newFromPage('Program-CyberSeniors');
        $count = 0;
        $webinars = 0;
        $oneOnOne = 0;
        $completeCollection = 0;
        foreach($dcs as $dc){
            if($this->exclude($dc->getUserId())){ continue; }
            $count += @$dc->getField('count');
            $webinars += @$dc->getField('webinarsClicks');
            $oneOnOne += @$dc->getField('1on1Clicks');
            $completeCollection += @$dc->getField('completeCollectionClicks');
        }
        
        $wgOut->addHTML("<table class='wikitable' frame='box' rules='all'>
            <tr>
                <td class='label'>Number of hits on page</td>
                <td align='right'>$count</td>
            </tr>
            <tr>
                <td class='label'>Number of hits on \"Register for daily webinars\"</td>
                <td align='right'>$webinars</td>
            </tr>
            <tr>
                <td class='label'>Number of hits on \"Sign up here to work one-on-one with trained tech mentors\"</td>
                <td align='right'>$oneOnOne</td>
            </tr>
            <tr>
                <td class='label'>Number of hits on \"You can find the complete collection of webinars here\"</td>
                <td align='right'>$completeCollection</td>
            </tr>
        </table>");
        
        $wgOut->addHTML("<h1>Community Connector</h1>");
        
        $dcs = DataCollection::newFromPage('Program-CommunityConnectors');
        $count = 0;
        foreach($dcs as $dc){
            if($this->exclude($dc->getUserId())){ continue; }
            $count += @$dc->getField('count');
        }
        
        $wgOut->addHTML("<table class='wikitable' frame='box' rules='all'>
            <tr>
                <td class='label'>Number of hits on page</td>
                <td align='right'>$count</td>
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
