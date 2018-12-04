<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['ApplicationsTable'] = 'ApplicationsTable'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['ApplicationsTable'] = $dir . 'ApplicationsTable.i18n.php';
$wgSpecialPageGroups['ApplicationsTable'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'ApplicationsTable::createSubTabs';

function runApplicationsTable($par) {
    ApplicationsTable::execute($par);
}

class ApplicationsTable extends SpecialPage{

    function ApplicationsTable() {
        SpecialPage::__construct("ApplicationsTable", null, false, 'runApplicationsTable');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF) || $person->isRole(SD));
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        ApplicationsTable::generateHTML($wgOut);
    }
    
    function initArrays(){
    
        $this->nis = Person::getAllPeople(NI);
        $this->allNis = array_merge($this->nis, 
                                    Person::getAllCandidates(NI), 
                                    Person::getAllPeople(EXTERNAL), 
                                    Person::getAllCandidates(EXTERNAL));
        
        $this->hqps = array_merge(Person::getAllPeople(HQP), Person::getAllCandidates(HQP));
        $this->projects = Project::getAllProjectsEver();
        
        $this->startUpLegal2018Applicants = array();
        $this->startUpDev2018Applicants = array();
        $this->stratApplicants = array();
        foreach(Person::getAllCandidates() as $person){
            if($person->isSubRole('StratApplicant')){
                $this->stratApplicants[] = $person;
            }
            if($person->isSubRole('StartUpLegal2018')){
                $this->startUpLegal2018Applicants[] = $person;
            }
            if($person->isSubRole('StartUpDev2018')){
                $this->startUpDev2018Applicants[] = $person;
            }
        }
    }
    
    function generateHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config;
        
        $me = Person::newFromWgUser();
        
        $links = array();
        
        $wgOut->addHTML("<style type='text/css'>
            #bodyContent > h1:first-child {
                display: none;
            }
            
            #contentSub {
                display: none;
            }
        </style>");
        
        if($me->isRoleAtLeast(SD)){
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=candidates'>Candidates</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=cat'>Catalyst</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=trans'>Trans</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=collab'>Collab</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=strat'>Strat</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=startup'>StartUp</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=exchange'>Exchange</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=summer'>Summer</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=atop'>ATOP</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=tech'>Tech</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=regional'>Regional</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=seminar'>Seminar</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=project'>Proj Report</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=milestones'>Proj Milestones</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=proposals'>Proj Proposals</a>";
        }
        
        $wgOut->addHTML("<h1>Report Tables:&nbsp;".implode("&nbsp;|&nbsp;", $links)."</h1><br />");
        if(!isset($_GET['program'])){
            return;
        }
        $program = $_GET['program'];
        
        $this->initArrays();
        
        if($program == "candidates" && $me->isRoleAtLeast(SD)){
            $this->generateCandidates();
        }
        if($program == "cat" && $me->isRoleAtLeast(SD)){
            $this->generateCat();
        }
        else if($program == "trans" && $me->isRoleAtLeast(SD)){
            $this->generateTrans();
        }
        if($program == "collab" && $me->isRoleAtLeast(SD)){
            $this->generateCollab();
        }
        else if($program == "strat" && $me->isRoleAtLeast(SD)){
            $this->generateStrat();
        }
        else if($program == "startup" && $me->isRoleAtLeast(SD)){
            $this->generateStartUp();
        }
        else if($program == "exchange" && $me->isRoleAtLeast(SD)){
            $this->generateExchange();
        }
        else if($program == "summer" && $me->isRoleAtLeast(SD)){
            $this->generateSummer();
        }
        else if($program == "atop" && $me->isRoleAtLeast(SD)){
            $this->generateATOP();
        }
        else if($program == "tech" && $me->isRoleAtLeast(SD)){
            $this->generateTech();
        }
        else if($program == "regional" && $me->isRoleAtLeast(SD)){
            $this->generateRegional();
        }
        else if($program == "seminar" && $me->isRoleAtLeast(SD)){
            $this->generateSeminar();
        }
        else if($program == "project" && $me->isRoleAtLeast(SD)){
            $this->generateProject();
        }
        else if($program == "milestones" && $me->isRoleAtLeast(SD)){
            $this->generateProjectMilestones();
        }
        else if($program == "proposals" && $me->isRoleAtLeast(SD)){
            $this->generateProjectProposals();
        }
        return;
    }
    
    function generateCandidates(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new CandidatesTab());
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateCat(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $reviewers = new MultiTextReportItem();
        $reviewers->setBlobType(BLOB_ARRAY);
        $reviewers->setBlobItem("CAT_DESC_REV");
        $reviewers->setBlobSection(CAT_DESC);
        $reviewers->setAttr("labels", "Name|E-Mail|Affiliation");
        $reviewers->setAttr("types", "text|text|text");
        $reviewers->setAttr("multiple", "true");
        $reviewers->setAttr("showHeader", "false");
        $reviewers->setAttr("class", "wikitable");
        $reviewers->setAttr("orientation", "list");
        $reviewers->setId("reviewers");
        $tabbedPage->addTab(new ApplicationTab(array(RP_CATALYST), $this->allNis, 2017, "2017", array($reviewers)));
        $tabbedPage->addTab(new ApplicationTab(array(RP_CATALYST), $this->allNis, 2016, "2016", array($reviewers)));
        $tabbedPage->addTab(new ApplicationTab(array(RP_CATALYST), $this->allNis, 2015, "2015"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateTrans(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $reviewers = new MultiTextReportItem();
        $reviewers->setBlobType(BLOB_ARRAY);
        $reviewers->setBlobItem("CAT_DESC_REV");
        $reviewers->setBlobSection(CAT_DESC);
        $reviewers->setAttr("labels", "Name|E-Mail|Affiliation");
        $reviewers->setAttr("types", "text|text|text");
        $reviewers->setAttr("multiple", "true");
        $reviewers->setAttr("showHeader", "false");
        $reviewers->setAttr("class", "wikitable");
        $reviewers->setAttr("orientation", "list");
        $reviewers->setId("reviewers");
        $tabbedPage->addTab(new ApplicationTab(array(RP_TRANS), $this->allNis, 2016, "2017", array($reviewers)));
        $tabbedPage->addTab(new ApplicationTab(array(RP_TRANS), $this->allNis, 2015, "2015"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateCollab(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $reviewers = new MultiTextReportItem();
        $reviewers->setBlobType(BLOB_ARRAY);
        $reviewers->setBlobItem("CAT_DESC_REV");
        $reviewers->setBlobSection(CAT_DESC);
        $reviewers->setAttr("labels", "Name|E-Mail|Affiliation");
        $reviewers->setAttr("types", "text|text|text");
        $reviewers->setAttr("multiple", "true");
        $reviewers->setAttr("showHeader", "false");
        $reviewers->setAttr("class", "wikitable");
        $reviewers->setAttr("orientation", "list");
        $reviewers->setId("reviewers");
        $tabbedPage->addTab(new ApplicationTab(array('RP_COLLAB'), $this->allNis, 2018, "2018"));
        $tabbedPage->addTab(new ApplicationTab('RP_COLLAB_LOI_2018', $this->allNis, 2018, "LOI 2018", array($reviewers)));
        $tabbedPage->addTab(new ApplicationTab(array('RP_COLLAB_08_2017'), $this->allNis, 2017, "08-2017", array($reviewers)));
        $tabbedPage->addTab(new ApplicationTab(array('RP_COLLAB_04_2017'), $this->allNis, 2017, "04-2017", array($reviewers)));
        $tabbedPage->addTab(new ApplicationTab(array('RP_COLLAB'), $this->allNis, 2016, "2016"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateStrat(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $reviewers = new MultiTextReportItem();
        $reviewers->setBlobType(BLOB_ARRAY);
        $reviewers->setBlobItem("CAT_DESC_REV");
        $reviewers->setBlobSection(CAT_DESC);
        $reviewers->setAttr("labels", "Name|E-Mail|Affiliation");
        $reviewers->setAttr("types", "text|text|text");
        $reviewers->setAttr("multiple", "true");
        $reviewers->setAttr("showHeader", "false");
        $reviewers->setAttr("class", "wikitable");
        $reviewers->setAttr("orientation", "list");
        $reviewers->setId("reviewers");
        $tabbedPage->addTab(new ApplicationTab(array('RP_STRAT'), $this->stratApplicants, 2017, "2017", array($reviewers)));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateStartUp(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab(array('RP_START_UP_LEGAL'), $this->startUpLegal2018Applicants, 2018, "Legal2018"));
        $tabbedPage->addTab(new ApplicationTab(array('RP_START_UP_DEV'), $this->startUpDev2018Applicants, 2018, "Dev2018"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateExchange(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab(array('RP_HQP_EXCHANGE', 'RP_HQP_EXCHANGE_REPORT'), $this->hqps, 2018, "2018", array(), true));
        $tabbedPage->addTab(new ApplicationTab(array('RP_HQP_EXCHANGE', 'RP_HQP_EXCHANGE_REPORT'), $this->hqps, 2017, "2017", array(), true));
        $tabbedPage->addTab(new ApplicationTab(array('RP_HQP_EXCHANGE', 'RP_HQP_EXCHANGE_REPORT'), $this->hqps, 2016, "2016"));
        $tabbedPage->addTab(new ApplicationTab(array('RP_HQP_EXCHANGE', 'RP_HQP_EXCHANGE_REPORT'), $this->hqps, 2015, "2015"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateSummer(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab(array('RP_HQP_SUMMER', 'RP_HQP_SUMMER_REPORT'), $this->hqps, 2018, "2018", array(), true));
        $tabbedPage->addTab(new ApplicationTab(array('RP_HQP_SUMMER', 'RP_HQP_SUMMER_REPORT'), $this->hqps, 2017, "2017", array(), true));
        $tabbedPage->addTab(new ApplicationTab(array('RP_HQP_SUMMER', 'RP_HQP_SUMMER_REPORT'), $this->hqps, 2016, "2016"));
        $tabbedPage->addTab(new ApplicationTab(array('RP_HQP_SUMMER', 'RP_HQP_SUMMER_REPORT'), $this->hqps, 2015, "2015"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateATOP(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab(array('RP_ATOP', 'RP_ATOP_REPORT'), $this->hqps, 2018, "2018", array(), true));
        $tabbedPage->addTab(new ApplicationTab(array('RP_ATOP', 'RP_ATOP_REPORT'), $this->hqps, 2017, "2017", array(), true));
        $tabbedPage->addTab(new ApplicationTab(array('RP_ATOP', 'RP_ATOP_REPORT'), $this->hqps, 2016, "2016"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateTech(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab(array('RP_TECH_WORKSHOP'), $this->nis, 2016, "2016"));
        $tabbedPage->addTab(new ApplicationTab(array('RP_TECH_WORKSHOP'), $this->nis, 2015, "2015"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateRegional(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab(array('RP_REGIONAL_MEETING'), array_merge(Person::getAllPeople(HQP), $this->nis), 2016, "2016"));
        $tabbedPage->addTab(new ApplicationTab(array('RP_REGIONAL_MEETING'), array_merge(Person::getAllPeople(HQP), $this->nis), 2015, "2015"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateSeminar(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab(array('RP_SEMINAR_SERIES'), $this->nis, 2018, "2018", array(), true));
        $tabbedPage->addTab(new ApplicationTab(array('RP_SEMINAR_SERIES'), $this->nis, 2017, "2017", array(), true));
        $tabbedPage->addTab(new ApplicationTab(array('RP_SEMINAR_SERIES'), $this->nis, 2016, "2016"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateProject(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab(array(RP_PROGRESS), $this->projects, 2018, "2018"));
        $tabbedPage->addTab(new ApplicationTab(array(RP_PROGRESS), $this->projects, 2017, "2017"));
        $tabbedPage->addTab(new ApplicationTab(array(RP_PROGRESS), $this->projects, 2016, "2016"));
        $tabbedPage->addTab(new ApplicationTab(array(RP_PROGRESS), $this->projects, 2015, "2015"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateProjectMilestones(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab(array('RP_MILE_REPORT'), $this->projects, 2018, "2018"));
        $tabbedPage->addTab(new ApplicationTab(array('RP_MILE_REPORT'), $this->projects, 2017, "2017"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateProjectProposals(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tab = new ApplicationTab(array('RP_PROJECT_PROPOSAL_ZIP'), $this->projects, 2015, "2015");
        $tab->showAllWithPDFs = true;
        $tabbedPage->addTab($tab);
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        
        if(self::userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "ApplicationsTable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("Reports", "$wgServer$wgScriptPath/index.php/Special:ApplicationsTable", $selected);
        }
        return true;
    }

}

?>
