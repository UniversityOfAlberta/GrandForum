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
        $this->getOutput()->setPageTitle("Admin");
        $this->showActionPlanStats();
        $this->showRegistrantsStats();
        $this->showProgramStats();
        $this->showCommunityProgramStats();
        $this->showEducationStats();
        $this->showIntakeStats();
    }
    
    function exclude($userId){
        $person = Person::newFromId($userId);
        if($person->getId() == 0){ return true; }
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
            if($person->isRole(CI)){
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
            if($person->isRole(CI)){
                if(AVOIDDashboard::hasSubmittedSurvey($person->getId())){
                    $submitted[] = $person;
                }
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
        $users = array(0);
        foreach($dcs as $dc){
            if($this->exclude($dc->getUserId())){ continue; }
            @$count += $dc->getField('count');
            $users[$dc->getUserId()] = $dc->getUserId();
        }
        
        // Also check report_blobs
        $data = DBFunctions::execSQL("(SELECT * FROM `grand_report_blobs` 
                                       WHERE rp_type = 'RP_PEER_COACHING'
                                       AND user_id NOT IN (".implode(",", $users).")
                                       GROUP BY user_id)
                                      UNION
                                      (SELECT * FROM `grand_report_blobs` 
                                       WHERE rp_type = 'RP_VOLUNTEER_OPPORTUNITIES'
                                       AND user_id NOT IN (".implode(",", $users).")
                                       GROUP BY user_id)");
        foreach($data as $row){
            if($this->exclude($row['user_id'])){ continue; }
            $count++;
            $users[$row['user_id']] = $row['user_id'];
        }
        
        $wgOut->addHTML("<table class='wikitable' frame='box' rules='all'>
            <tr>
                <td class='label'>Number of unique users</td>
                <td align='right'>".(count($users)-1)."</td>
            </tr>
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
        $users = array(0);
        foreach($dcs as $dc){
            if($this->exclude($dc->getUserId())){ continue; }
            @$count += $dc->getField('count');
            @$webinars += $dc->getField('webinarsClicks');
            @$oneOnOne += $dc->getField('1on1Clicks');
            @$completeCollection += $dc->getField('completeCollectionClicks');
            $users[$dc->getUserId()] = $dc->getUserId();
        }
        
        $wgOut->addHTML("<table class='wikitable' frame='box' rules='all'>
            <tr>
                <td class='label'>Number of unique users</td>
                <td align='right'>".(count($users)-1)."</td>
            </tr>
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
        $users = array(0);
        foreach($dcs as $dc){
            if($this->exclude($dc->getUserId())){ continue; }
            @$count += $dc->getField('count');
            $users[$dc->getUserId()] = $dc->getUserId();
        }
        
        // Also check report_blobs
        $data = DBFunctions::execSQL("SELECT * FROM `grand_report_blobs` 
                                      WHERE rp_type = 'RP_COMMUNITY_CONNECTORS'
                                      AND user_id NOT IN (".implode(",", $users).")
                                      GROUP BY user_id");
        foreach($data as $row){
            if($this->exclude($row['user_id'])){ continue; }
            $count++;
            $users[$row['user_id']] = $row['user_id'];
        }
        
        $wgOut->addHTML("<table class='wikitable' frame='box' rules='all'>
            <tr>
                <td class='label'>Number of unique users</td>
                <td align='right'>".(count($users)-1)."</td>
            </tr>
            <tr>
                <td class='label'>Number of hits on page</td>
                <td align='right'>$count</td>
            </tr>
        </table>");
    }
    
    function showCommunityProgramStats(){
        global $wgOut;
        $wgOut->addHTML("<h1>Community Programs</h1>");
        $leaves = PharmacyMap::getCategoryLeaves();
        $clipboards = array();
        foreach(Person::getAllPeople() as $person){
            if($this->exclude($person->getId())){ continue; }
            $clipboard = $person->getClipboard();
            if(!empty($clipboard)){
                $clipboards[] = $clipboard;
            }
        }
        
        $dcs = DataCollection::newFromPage('ProgramLibrary');
        $count = 0;
        $users = array(0);
        foreach($dcs as $dc){
            if($this->exclude($dc->getUserId())){ continue; }
            @$count += $dc->getField('count');
            $users[$dc->getUserId()] = $dc->getUserId();
        }
        
        // Also check INDEX (but only count once)
        $dcs = DataCollection::newFromPage('ProgramLibrary-INDEX');
        foreach($dcs as $dc){
            if($this->exclude($dc->getUserId()) || isset($users[$dc->getUserId()])){ continue; }
            $users[$dc->getUserId()] = $dc->getUserId();
            @$count++;
        }

        $topPages = array();
        foreach($leaves as $leaf){
            $dcs = DataCollection::newFromPage("ProgramLibrary-{$leaf->code}");
            foreach($dcs as $dc){
                if($this->exclude($dc->getUserId())){ continue; }
                @$topPages[$leaf->code] += $dc->getField('pageCount');
            }
        }
        
        asort($topPages);
        $topPages = array_reverse($topPages);
        $topPagesKeys = array_keys($topPages);
        
        @$wgOut->addHTML("<table class='wikitable' frame='box' rules='all'>
            <tr>
                <td class='label'>Number of clipboards created</td>
                <td align='right'>".count($clipboards)."</td>
            </tr>
            <tr>
                <td class='label'>Number of unique users</td>
                <td align='right'>".(count($users)-1)."</td>
            </tr>
            <tr>
                <td class='label'>Number of hits on page</td>
                <td align='right'>$count</td>
            </tr>
            <tr>
                <td class='label'>Top 3 main categories hit</td>
                <td align='right'>
                    <table>
                        <tr><td style='font-weight: bold;'>{$topPagesKeys[0]}&nbsp;</td><td align='right'>{$topPages[$topPagesKeys[0]]}</td></tr>
                        <tr><td style='font-weight: bold;'>{$topPagesKeys[1]}&nbsp;</td><td align='right'>{$topPages[$topPagesKeys[1]]}</td></tr>
                        <tr><td style='font-weight: bold;'>{$topPagesKeys[2]}&nbsp;</td><td align='right'>{$topPages[$topPagesKeys[2]]}</td></tr>
                    </table>
                </td>
            </tr>
        </table>");
    }
    
    function showEducationStats(){
        global $wgOut;
        $wgOut->addHTML("<h1>AVOID Education</h1>");
        
        $dcs = DataCollection::newFromPage('Topic-*');
        $topTopics = array();
        foreach($dcs as $dc){
            if($this->exclude($dc->getUserId())){ continue; }
            @$topTopics[str_replace("Topic-", "", $dc->page)] += $dc->getField('count');
        }

        asort($topTopics);
        $topTopics = array_reverse($topTopics);
        $topTopicsKeys = array_keys($topTopics);
        
        $dcs = array_merge(DataCollection::newFromPage('*.pdf'), 
                           DataCollection::newFromPage('*.mp4'));
        $libraryHits = 0;
        foreach($dcs as $dc){
            if($this->exclude($dc->getUserId())){ continue; }
            @$libraryHits += $dc->getField('count');
        }
        
        $dcs = array_merge(DataCollection::newFromPage('IngredientsForChange'), 
                           DataCollection::newFromPage('Activity'),
                           DataCollection::newFromPage('Vaccination'),
                           DataCollection::newFromPage('OptimizeMedication'),
                           DataCollection::newFromPage('Interact'),
                           DataCollection::newFromPage('DietAndNutrition'),
                           DataCollection::newFromPage('Sleep'),
                           DataCollection::newFromPage('FallsPrevention'));
        $moduleHits = 0;
        $users = array(0);
        foreach($dcs as $dc){
            if($this->exclude($dc->getUserId())){ continue; }
            @$moduleHits += $dc->getField('video1PageCount');
            $users[$dc->getUserId()] = $dc->getUserId();
        }
        
        $modules = EducationResources::JSON();
        $completed = 0;
        foreach(Person::getAllPeople() as $person){
            if($this->exclude($person->getId())){ continue; }
            foreach($modules as $module){
                $completion = EducationResources::completion($module->id, $person);
                if($completion == 100){
                    $completed++;
                }
            }
        }
        
        @$wgOut->addHTML("<table class='wikitable' frame='box' rules='all'>
            <tr>
                <td class='label'>Top 3 topics</td>
                <td align='right'>
                    <table>
                        <tr><td style='font-weight: bold;'>{$topTopicsKeys[0]}&nbsp;</td><td align='right'>{$topTopics[$topTopicsKeys[0]]}</td></tr>
                        <tr><td style='font-weight: bold;'>{$topTopicsKeys[1]}&nbsp;</td><td align='right'>{$topTopics[$topTopicsKeys[1]]}</td></tr>
                        <tr><td style='font-weight: bold;'>{$topTopicsKeys[2]}&nbsp;</td><td align='right'>{$topTopics[$topTopicsKeys[2]]}</td></tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class='label'>Number of unique users</td>
                <td align='right'>".(count($users)-1)."</td>
            </tr>
            <tr>
                <td class='label'>Hits on resource library</td>
                <td align='right'>$libraryHits</td>
            </tr>
            <tr>
                <td class='label'>Hits on modules</td>
                <td align='right'>$moduleHits</td>
            </tr>
            <tr>
                <td class='label'>Completed modules</td>
                <td align='right'>$completed</td>
            </tr>
        </table>");
    }
    
    function showIntakeStats(){
        global $wgOut;
        $wgOut->addHTML("<h1>How did you hear about AVOID?</h1>");
        
        $top3 = array();
        foreach(Person::getAllPeople() as $person){
            if($this->exclude($person->getId())){ continue; }
            $hear = AdminDataCollection::getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "AVOID_Questions_tab0", "program_avoid", $person->getId());
            $hear = ($hear == "") ? $person->getExtra('hearField', '') : $hear;
            if($hear != ""){
                @$top3[$hear]++;
            }
        }

        asort($top3);
        $top3 = array_reverse($top3);
        $top3Keys = array_keys($top3);
        
        @$wgOut->addHTML("<table class='wikitable' frame='box' rules='all'>
            <tr>
                <td class='label'>Top 3</td>
                <td align='right'>
                    <table>
                        <tr><td style='font-weight: bold;'>{$top3Keys[0]}&nbsp;</td><td align='right'>{$top3[$top3Keys[0]]}</td></tr>
                        <tr><td style='font-weight: bold;'>{$top3Keys[1]}&nbsp;</td><td align='right'>{$top3[$top3Keys[1]]}</td></tr>
                        <tr><td style='font-weight: bold;'>{$top3Keys[2]}&nbsp;</td><td align='right'>{$top3[$top3Keys[2]]}</td></tr>
                    </table>
                </td>
            </tr>
        </table>");
    }

    static function createSubTabs(&$tabs){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle;
        $person = Person::newFromWgUser();
        if($person->isRoleAtLeast(STAFF)){
            $selected = @($wgTitle->getText() == "AdminUsageStats") ? "selected" : false;
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("Admin", "{$wgServer}{$wgScriptPath}/index.php/Special:AdminUsageStats", $selected);
        }
        return true;
    }

}

?>
