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

    function __construct() {
        SpecialPage::__construct("ApplicationsTable", null, false, 'runApplicationsTable');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF) || $person->isRole(SD));
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        $this->getOutput()->setPageTitle("Report Table");
        ApplicationsTable::generateHTML($wgOut);
    }
    
    function initArrays(){
        $this->projects = Project::getAllProjectsEver();
    }
    
    function generateHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config;
  
        $me = Person::newFromWgUser();
        
        $wgOut->addHTML("<style type='text/css'>
            #bodyContent > h1:first-child {
                display: none;
            }
            
            #contentSub {
                display: none;
            }
            
            h1 {
                margin-top: 0;
            }
        </style>");
        
        $links = array();
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=strat'>Strat</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=cat'>Catalyst</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=collab'>Collab</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=glycotwinning'>GlycoTwinning Survey</a>";
        $wgOut->addHTML("<h1>Recent</h1><span style='font-size:1.25em;'>".implode("&nbsp;|&nbsp;", $links)."</span>");
        
        $links = array();
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=trans'>Trans</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=international'>Int'l Partnerships</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=clinical'>Clinical</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=cycleiiloi'>CycleIILOI</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=ssfloi'>SSFLOI</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=legacy'>Legacy</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=alberta'>Alberta</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=startup'>StartUp</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=exchange'>Exchange</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=hqpresearch'>Research & Travel</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=summer'>Summer</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=atop'>ATOP</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=bio'>BioTalent</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=tsf'>TSF Survey</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=cspc'>CSPC</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=tech'>Tech</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=regional'>Regional</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=seminar'>Seminar</a>";
        $wgOut->addHTML("<h1>Old</h1><span style='font-size:1.25em;'>".implode("&nbsp;|&nbsp;", $links)."</span>");
        
        $links = array();
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=candidates'>Candidates</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=project'>Proj Report</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=milestones'>Proj Milestones</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=proposals'>Proj Proposals</a>";
        $wgOut->addHTML("<h1>Other</h1><span style='font-size:1.25em;'>".implode("&nbsp;|&nbsp;", $links)."</span>");
        
        
        $wgOut->addHTML("<br /><br />");
        
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
        else if($program == "collab" && $me->isRoleAtLeast(SD)){
            $this->generateCollab();
        }
        else if($program == "international" && $me->isRoleAtLeast(SD)){
            $this->generateInternational();
        }
        else if($program == "clinical" && $me->isRoleAtLeast(SD)){
            $this->generateClinical();
        }
        else if($program == "cycleiiloi" && $me->isRoleAtLeast(SD)){
            $this->generateCycleIILOI();
        }
        else if($program == "ssfloi" && $me->isRoleAtLeast(SD)){
            $this->generateSSFLOI();
        }
        else if($program == "legacy" && $me->isRoleAtLeast(SD)){
            $this->generateLegacy();
        }
        else if($program == "alberta" && $me->isRoleAtLeast(SD)){
            $this->generateAlberta();
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
        else if($program == "hqpresearch" && $me->isRoleAtLeast(SD)){
            $this->generateHQPResearch();
        }
        else if($program == "summer" && $me->isRoleAtLeast(SD)){
            $this->generateSummer();
        }
        else if($program == "atop" && $me->isRoleAtLeast(SD)){
            $this->generateATOP();
        }
        else if($program == "bio" && $me->isRoleAtLeast(SD)){
            $this->generateBioTalent();
        }
        else if($program == "tsf" && $me->isRoleAtLeast(SD)){
            $this->generateTSF();
        }
        else if($program == "glycotwinning" && $me->isRoleAtLeast(SD)){
            $this->generateGlycoTwinning();
        }
        else if($program == "cspc" && $me->isRoleAtLeast(SD)){
            $this->generateCSPC();
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
        $wgOut->addHTML("<script type='text/javascript'>
            $('a[href=\"$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program={$program}\"]').wrap('<b>');
        </script>");
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
        $tabbedPage->addTab(new ApplicationTab(array(RP_CATALYST), null, 2024, "2024", array(), true));
        $tabbedPage->addTab(new ApplicationTab(array(RP_CATALYST), null, 2017, "2017", array($reviewers)));
        $tabbedPage->addTab(new ApplicationTab(array(RP_CATALYST), null, 2016, "2016", array($reviewers)));
        $tabbedPage->addTab(new ApplicationTab(array(RP_CATALYST), null, 2015, "2015"));
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
        $tabbedPage->addTab(new ApplicationTab(RP_TRANS, null, 2024, "2024", array(), true));
        $tabbedPage->addTab(new ApplicationTab(RP_TRANS, null, 2022, "2022", array($reviewers)));
        $tabbedPage->addTab(new ApplicationTab(RP_TRANS, null, 2021, "2021", array($reviewers)));
        $tabbedPage->addTab(new ApplicationTab(RP_TRANS, null, 2020, "2020", array($reviewers)));
        $tabbedPage->addTab(new ApplicationTab(RP_TRANS, null, 2016, "2017", array($reviewers)));
        $tabbedPage->addTab(new ApplicationTab(RP_TRANS, null, 2015, "2015"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateAlberta(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab('RP_ALBERTA', null, 2019, "2019"));
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
        $tabbedPage->addTab(new ApplicationTab('RP_FALL_COLLAB', null, 2024, "Fall 2024", array(), true));
        $tabbedPage->addTab(new ApplicationTab('RP_COLLAB', null, 2024, "2024", array(), true));
        $tabbedPage->addTab(new ApplicationTab('RP_COLLAB', null, 2022, "2022", array($reviewers)));
        $tabbedPage->addTab(new ApplicationTab('RP_COLLAB', null, 2020, "2020", array($reviewers)));
        $tabbedPage->addTab(new ApplicationTab('RP_COLLAB', null, 2018, "2018"));
        $tabbedPage->addTab(new ApplicationTab('RP_COLLAB_LOI_2018', null, 2018, "LOI 2018", array($reviewers)));
        $tabbedPage->addTab(new ApplicationTab('RP_COLLAB_08_2017', null, 2017, "08-2017", array($reviewers)));
        $tabbedPage->addTab(new ApplicationTab('RP_COLLAB_04_2017', null, 2017, "04-2017", array($reviewers)));
        $tabbedPage->addTab(new ApplicationTab('RP_COLLAB', null, 2016, "2016"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateInternational(){
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
        $tabbedPage->addTab(new ApplicationTab('RP_INTERNATIONAL', null, 2022, "2022", array($reviewers)));
        $tabbedPage->addTab(new ApplicationTab('RP_INTERNATIONAL', null, 2021, "2021", array($reviewers)));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateClinical(){
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
        $tabbedPage->addTab(new ApplicationTab(array('RP_CLINICAL'), null, 2022, "2022", array($reviewers)));
        $tabbedPage->addTab(new ApplicationTab(array('RP_CLINICAL'), null, 2021, "2021", array($reviewers)));
        $tabbedPage->addTab(new ApplicationTab(array('RP_CLINICAL'), null, 2020, "2020", array($reviewers)));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateCycleIILOI(){
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
        $tabbedPage->addTab(new ApplicationTab('RP_CYCLEII', null, 2020, "2020", array($reviewers)));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateSSFLOI(){
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
        $tabbedPage->addTab(new ApplicationTab('RP_SSF_LOI', null, 2022, "2022", array($reviewers)));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateLegacy(){
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
        $tabbedPage->addTab(new ApplicationTab('RP_LEGACY_APPLICATION', null, 2021, "2021", array($reviewers)));
        $tabbedPage->addTab(new ApplicationTab('RP_LEGACY', null, 2021, "2021 LOI", array($reviewers)));
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
        $tabbedPage->addTab(new ApplicationTab('RP_STRAT', null, 2024, "2024", array(), true));
        $tabbedPage->addTab(new ApplicationTab('RP_STRAT', null, 2022, "2022", array($reviewers)));
        $tabbedPage->addTab(new ApplicationTab('RP_STRAT', null, 2021, "2021", array($reviewers)));
        $tabbedPage->addTab(new ApplicationTab('RP_STRAT', null, 2020, "2020", array($reviewers)));
        $tabbedPage->addTab(new ApplicationTab('RP_STRAT', null, 2019, "2019", array($reviewers)));
        $tabbedPage->addTab(new ApplicationTab('RP_STRAT', null, 2017, "2017-18", array($reviewers)));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateStartUp(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab('RP_START_UP_LEGAL', null, 2020, "Legal2020", array(), false, array(0,1,2)));
        $tabbedPage->addTab(new ApplicationTab('RP_START_UP_LEGAL', null, 2019, "Legal2019", array(), false, array(0,1,2)));
        $tabbedPage->addTab(new ApplicationTab('RP_START_UP_LEGAL', null, 2018, "Legal2018"));
        $tabbedPage->addTab(new ApplicationTab(array('RP_START_UP_DEV'), null, 2018, "Dev2018"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateHQPResearch(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab(array('RP_HQP_RESEARCH', 'RP_HQP_TRAVEL_REPORT'), null, 2022, "2022"));
        $tabbedPage->addTab(new ApplicationTab(array('RP_HQP_RESEARCH', 'RP_HQP_TRAVEL_REPORT'), null, 2021, "2021"));
        $tabbedPage->addTab(new ApplicationTab(array('RP_HQP_RESEARCH', 'RP_HQP_TRAVEL_REPORT'), null, 2020, "2020"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateExchange(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab(array('RP_HQP_EXCHANGE', 'RP_HQP_EXCHANGE_REPORT'), null, 2018, "2018", array(), true));
        $tabbedPage->addTab(new ApplicationTab(array('RP_HQP_EXCHANGE', 'RP_HQP_EXCHANGE_REPORT'), null, 2017, "2017", array(), true));
        $tabbedPage->addTab(new ApplicationTab(array('RP_HQP_EXCHANGE', 'RP_HQP_EXCHANGE_REPORT'), null, 2016, "2016"));
        $tabbedPage->addTab(new ApplicationTab(array('RP_HQP_EXCHANGE', 'RP_HQP_EXCHANGE_REPORT'), null, 2015, "2015"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateSummer(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab(array('RP_HQP_SUMMER', 'RP_HQP_SUMMER_REPORT'), null, 2024, "2024", array(), true));
        $tabbedPage->addTab(new ApplicationTab(array('RP_HQP_SUMMER', 'RP_HQP_SUMMER_REPORT'), null, 2023, "2023", array(), true));
        $tabbedPage->addTab(new ApplicationTab(array('RP_HQP_SUMMER', 'RP_HQP_SUMMER_REPORT'), null, 2022, "2022", array(), true));
        $tabbedPage->addTab(new ApplicationTab(array('RP_HQP_SUMMER', 'RP_HQP_SUMMER_REPORT'), null, 2021, "2021", array(), true));
        $tabbedPage->addTab(new ApplicationTab(array('RP_HQP_SUMMER', 'RP_HQP_SUMMER_REPORT'), null, 2020, "2020", array(), true));
        $tabbedPage->addTab(new ApplicationTab(array('RP_HQP_SUMMER', 'RP_HQP_SUMMER_REPORT'), null, 2018, "2018", array(), true));
        $tabbedPage->addTab(new ApplicationTab(array('RP_HQP_SUMMER', 'RP_HQP_SUMMER_REPORT'), null, 2017, "2017", array(), true));
        $tabbedPage->addTab(new ApplicationTab(array('RP_HQP_SUMMER', 'RP_HQP_SUMMER_REPORT'), null, 2016, "2016"));
        $tabbedPage->addTab(new ApplicationTab(array('RP_HQP_SUMMER', 'RP_HQP_SUMMER_REPORT'), null, 2015, "2015"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateATOP(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab(array('RP_ATOP', 'RP_ATOP_REPORT'), null, 2024, "2024", array(), true));
        $tabbedPage->addTab(new ApplicationTab(array('RP_ATOP', 'RP_ATOP_REPORT'), null, 2022, "2022", array(), true));
        $tabbedPage->addTab(new ApplicationTab(array('RP_ATOP', 'RP_ATOP_REPORT'), null, 2021, "2021", array(), true));
        $tabbedPage->addTab(new ApplicationTab(array('RP_ATOP', 'RP_ATOP_REPORT'), null, 2020, "2020", array(), true));
        $tabbedPage->addTab(new ApplicationTab(array('RP_ATOP', 'RP_ATOP_REPORT'), null, 2018, "2018", array(), true));
        $tabbedPage->addTab(new ApplicationTab(array('RP_ATOP', 'RP_ATOP_REPORT'), null, 2017, "2017", array(), true));
        $tabbedPage->addTab(new ApplicationTab(array('RP_ATOP', 'RP_ATOP_REPORT'), null, 2016, "2016"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateBioTalent(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab(array('RP_BIO_TALENT'), null, 2023, "2023", array(), true));
        $tabbedPage->addTab(new ApplicationTab(array('RP_BIO_TALENT'), null, 2021, "2021", array(), true));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateTSF(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab(array('RP_TECH_SKILLS_SURVEY'), null, 0, "Survey", array(), true));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateGlycoTwinning(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab(array('RP_GLYCOTWINNING_SURVEY'), null, 0, "Survey", array(), true));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateCSPC(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab(array('RP_CSPC'), null, 2020, "2020", array(), true));
        $tabbedPage->addTab(new ApplicationTab(array('RP_CSPC'), null, 2019, "2019", array(), true));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateTech(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab(array('RP_TECH_WORKSHOP'), null, 2016, "2016"));
        $tabbedPage->addTab(new ApplicationTab(array('RP_TECH_WORKSHOP'), null, 2015, "2015"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateRegional(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab(array('RP_REGIONAL_MEETING'), null, 2016, "2016"));
        $tabbedPage->addTab(new ApplicationTab(array('RP_REGIONAL_MEETING'), null, 2015, "2015"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateSeminar(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab(array('RP_SEMINAR_SERIES'), null, 2018, "2018", array(), true));
        $tabbedPage->addTab(new ApplicationTab(array('RP_SEMINAR_SERIES'), null, 2017, "2017", array(), true));
        $tabbedPage->addTab(new ApplicationTab(array('RP_SEMINAR_SERIES'), null, 2016, "2016"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateProject(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab(array(RP_PROGRESS), null, 2023, "2023"));
        $tabbedPage->addTab(new ApplicationTab(array(RP_PROGRESS), null, 2022, "2022"));
        $tabbedPage->addTab(new ApplicationTab(array(RP_PROGRESS), null, 2021, "2021"));
        $tabbedPage->addTab(new ApplicationTab(array(RP_PROGRESS), null, 2020, "2020"));
        $tabbedPage->addTab(new ApplicationTab(array(RP_PROGRESS), null, 2019, "2019"));
        $tabbedPage->addTab(new ApplicationTab(array(RP_PROGRESS), null, 2018, "2018"));
        $tabbedPage->addTab(new ApplicationTab(array(RP_PROGRESS), null, 2017, "2017"));
        $tabbedPage->addTab(new ApplicationTab(array(RP_PROGRESS), null, 2016, "2016"));
        $tabbedPage->addTab(new ApplicationTab(array(RP_PROGRESS), null, 2015, "2015"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateProjectMilestones(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab(array('RP_MILE_REPORT'), null, 2023, "2023"));
        $tabbedPage->addTab(new ApplicationTab(array('RP_MILE_REPORT'), null, 2022, "2022"));
        $tabbedPage->addTab(new ApplicationTab(array('RP_MILE_REPORT'), null, 2020, "2020"));
        $tabbedPage->addTab(new ApplicationTab(array('RP_MILE_REPORT'), null, 2019, "2019"));
        $tabbedPage->addTab(new ApplicationTab(array('RP_MILE_REPORT'), null, 2018, "2018"));
        $tabbedPage->addTab(new ApplicationTab(array('RP_MILE_REPORT'), null, 2017, "2017"));
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
        
        if((new self)->userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "ApplicationsTable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("Applications/Reports", "$wgServer$wgScriptPath/index.php/Special:ApplicationsTable", $selected);
        }
        return true;
    }

}

?>
