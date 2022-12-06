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

    var $nis;
    var $fullHQPs;
    var $hqps;
    var $externals;
    var $wps;
    var $ccs;
    var $ihs;
    var $catalyst;
    var $platform;
    var $projects;

    function ApplicationsTable() {
        SpecialPage::__construct("ApplicationsTable", null, false, 'runApplicationsTable');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(SD) || $person->isRole('BOARD-ADMIN') || count($person->getEvaluates('RP_SUMMER', 2015, "Person")) > 0 || $person->getName() == "Euson.Yeung" || $person->getName() == "Susan.Jaglal");
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        ApplicationsTable::generateHTML($wgOut);
    }
    
    function initArrays(){
        $this->wps = Theme::getAllThemes();
        
        $this->ccs = array();
        $this->ihs = array();
        $this->catalyst = array();
        $this->platform = array();
        $this->projects = Project::getAllProjectsEver(false, true);
        foreach($this->projects as $project){
            if($project->getType() == 'Administrative'){
                $this->ccs[] = $project;            
            }
            if($project->getType() == "Innovation Hub"){
                $this->ihs[] = $project;
            }
            if(preg_match("/.*CAT-2019.*/", $project->getName()) != 0){
                $this->catalyst[] = $project;
            }
            if(preg_match("/.*AW-PP2019.*/", $project->getName()) != 0){
                $this->platform[] = $project;
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
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=sip'>SIP</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=cip'>CIP</a>";
        }
        if($me->isRoleAtLeast(SD) || $me->isRole('BOARD-ADMIN')){
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=crp'>CRP</a>";
        }
        if($me->isRoleAtLeast(SD)){
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=access'>ACCESS</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=eea'>EEA</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=edge'>Edge</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=air'>AIR</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=ecr'>ECR</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=catalyst'>Catalyst</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=award'>Award</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=wp'>WP</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=cc'>CC</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=ih'>IH</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=project'>Project Evaluation</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=fellow'>Policy Challenge</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=epicconference'>EPIC Conference</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=epicat'>EPIC AT</a>";
        }
        if($me->isRoleAtLeast(SD) || count($me->getEvaluates('RP_SUMMER', 2015, "Person")) > 0 || $me->getName() == "Euson.Yeung" || $me->getName() == "Susan.Jaglal"){
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=summer'>Summer Institute</a>";
        }
        
        $wgOut->addHTML("<h1>Report Tables:&nbsp;".implode("&nbsp;|&nbsp;", $links)."</h1><br />");
        if(!isset($_GET['program'])){
            return;
        }
        $program = $_GET['program'];
        
        $this->initArrays();
        
        if($program == "sip" && $me->isRoleAtLeast(SD)){
            $this->generateSIP();
        }
        else if($program == "cip" && $me->isRoleAtLeast(SD)){
            $this->generateCIP();
        }
        else if($program == "crp" && ($me->isRoleAtLeast(SD) || $me->isRole('BOARD-ADMIN'))){
            $this->generateCRP();
        }
        if($program == "access" && $me->isRoleAtLeast(SD)){
            $this->generateAccess();
        }
        else if($program == "eea" && $me->isRoleAtLeast(SD)){
            $this->generateEEA();
        }
        else if($program == "edge" && $me->isRoleAtLeast(SD)){
            $this->generateEdge();
        }
        else if($program == "air" && $me->isRoleAtLeast(SD)){
            $this->generateAir();
        }
        else if($program == "ecr" && $me->isRoleAtLeast(SD)){
            $this->generateECR();
        }
        else if($program == "catalyst" && $me->isRoleAtLeast(SD)){
            $this->generateCatalyst();
        }
        else if($program == "award" && $me->isRoleAtLeast(SD)){
            $this->generateAward();
        }
        else if($program == "summer" && ($me->isRoleAtLeast(SD) || count($me->getEvaluates('RP_SUMMER', 2015, "Person")) > 0 || $me->getName() == "Euson.Yeung" || $me->getName() == "Susan.Jaglal")){
            $this->generateSummer();
        }
        else if($program == "epicconference" && $me->isRoleAtLeast(SD)){
            $this->generateEpicConference();
        }
        else if($program == "epicat" && $me->isRoleAtLeast(SD)){
            $this->generateEpicAt();
        }
        else if($program == "fellow" && $me->isRoleAtLeast(SD)){
            $this->generateFellow();
        }
        else if($program == "wp" && $me->isRoleAtLeast(SD)){
            $this->generateWP();
        }
        else if($program == "cc" && $me->isRoleAtLeast(SD)){
            $this->generateCC();
        }
        else if($program == "ih" && $me->isRoleAtLeast(SD)){
            $this->generateIH();
        }
        else if($program == "project" && $me->isRoleAtLeast(SD)){
            $this->generateProject();
        }
        return;
    }
    
    function generateSIP(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab('RP_SIP_CRP', null, 2020, "CRP", array(), false, array(0,1,2)));
        $tabbedPage->addTab(new ApplicationTab('RP_SIP_ACC_2019', null, 2019, "Accelerator 5", array(), false, array(0,1,2)));
        $tabbedPage->addTab(new ApplicationTab('RP_SIP_ACC_2018_2', null, 2018, "Accelerator 4"));
        $tabbedPage->addTab(new ApplicationTab('RP_SIP_ACC_2018', null, 2018, "Accelerator 3"));
        $tabbedPage->addTab(new ApplicationTab('RP_SIP_ACC_09_2017', null, 2017, "Accelerator 2"));
        $tabbedPage->addTab(new ApplicationTab('RP_SIP_ACC', null, 2017, "Accelerator"));
        $tabbedPage->addTab(new ApplicationTab('RP_SIP_01_2017', null, 2015, "01-2017"));
        $tabbedPage->addTab(new ApplicationTab('RP_SIP_10_2016', null, 2015, "10-2016"));
        $tabbedPage->addTab(new ApplicationTab('RP_SIP_07_2016', null, 2015, "07-2016"));
        $tabbedPage->addTab(new ApplicationTab('RP_SIP_04_2016', null, 2015, "04-2016"));
        $tabbedPage->addTab(new ApplicationTab('RP_SIP', null, 2015, "01-2016"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateCIP(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab('RP_CIP', null, 2015, "2016"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateCRP(){
        global $wgOut;
        
        $merged = new UploadReportItem();
        $merged->setBlobType(BLOB_RAW);
        $merged->setBlobItem('MERGED');
        $merged->setBlobSection("PART3");
        $merged->setId("merged");
        
        $team = new MultiTextReportItem();
        $team->setBlobType(BLOB_ARRAY);
        $team->setBlobItem('TEAM');
        $team->setBlobSection("PART1");
        $team->setAttr("labels", "Team Member Name|Role");
        $team->setAttr("orientation", "list");
        $team->setAttr("showHeader", "false");
        $team->setAttr("multiple", "true");
        $team->setId("team");
        
        $title = new TextReportItem();
        $title->setBlobType(BLOB_TEXT);
        $title->setBlobItem('TITLE');
        $title->setBlobSection("COVER");
        $title->setId("title");
        
        $primary = new SelectReportItem();
        $primary->setBlobType(BLOB_TEXT);
        $primary->setBlobItem('PRIMARY');
        $primary->setBlobSection("COVER");
        $primary->setId("primary");
        
        $secondary = new SelectReportItem();
        $secondary->setBlobType(BLOB_TEXT);
        $secondary->setBlobItem('SECONDARY');
        $secondary->setBlobSection("COVER");
        $secondary->setId("secondary");
        
        $total = new TextReportItem();
        $total->setBlobType(BLOB_TEXT);
        $total->setBlobItem(TOTAL);
        $total->setBlobSection("COVER");
        $total->setId("total");
        
        $medteq = new CheckboxReportItem();
        $medteq->setBlobType(BLOB_ARRAY);
        $medteq->setBlobItem('SECTION5_CHECK');
        $medteq->setBlobSection("PART1");
        $medteq->setId("section5_check");
        
        $mitacs = new CheckboxReportItem();
        $mitacs->setBlobType(BLOB_ARRAY);
        $mitacs->setBlobItem('SECTION7_CHECK');
        $mitacs->setBlobSection("PART1");
        $mitacs->setId("section7_check");
        
        $section2 = new TextareaReportItem();
        $section2->setBlobType(BLOB_TEXT);
        $section2->setBlobItem('SECTION2');
        $section2->setBlobSection("PART1");
        $section2->setAttr("rich", "true");
        $section2->setId("section2");
        
        $orgs = new MultiTextReportItem();
        $orgs->setBlobType(BLOB_ARRAY);
        $orgs->setBlobItem('TEAM');
        $orgs->setBlobSection("PART1");
        $orgs->setAttr("labels", "Institution/Organization");
        $orgs->setAttr("orientation", "list");
        $orgs->setAttr("showHeader", "false");
        $orgs->setAttr("multiple", "true");
        $orgs->setId("team");
        
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab('RP_CRP', null, 2018, "2018", array('Supporting Documents' => $merged, 'Team' => $team, 'Title' => $title, 'Primary' => $primary, 'Secondary' => $secondary, 'AGE-WELL Request ($)' => $total, 'MEDTEQ' => $medteq, 'MITACS' => $mitacs, 'Product' => $section2, 'Organizations' => $orgs)));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateAir(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab('RP_AIR', null, 2022, "2022"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateECR(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab('RP_ECR_AUG', null, 2022, "2022-08"));
        $tabbedPage->addTab(new ApplicationTab('RP_ECR', null, 2022, "2022-04"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateCatalyst(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab('RP_CAT', null, 2017, "2018"));
        $tabbedPage->addTab(new ApplicationTab('RP_CAT', null, 2016, "2017"));
        $tabbedPage->addTab(new ApplicationTab('RP_CAT', null, 2015, "2016"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateAccess(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab('RP_ACCESS_10_2022', null, 2022, "2022-10"));
        $tabbedPage->addTab(new ApplicationTab('RP_ACCESS_07_2022', null, 2022, "2022-07"));
        $tabbedPage->addTab(new ApplicationTab('RP_ACCESS_04_2022', null, 2022, "2022-04"));
        $tabbedPage->addTab(new ApplicationTab('RP_ACCESS_01_2022', null, 2022, "2022-01"));
        $tabbedPage->addTab(new ApplicationTab('RP_ACCESS_10_2021', null, 2021, "2021-10"));
        $tabbedPage->addTab(new ApplicationTab('RP_ACCESS_07_2021', null, 2021, "2021-07"));
        $tabbedPage->addTab(new ApplicationTab('RP_ACCESS_04_2021', null, 2021, "2021-04"));
        $tabbedPage->addTab(new ApplicationTab('RP_ACCESS_01_2021', null, 2021, "2021-01"));
        $tabbedPage->addTab(new ApplicationTab('RP_ACCESS_07_2020', null, 2020, "2020-07"));
        $tabbedPage->addTab(new ApplicationTab('RP_ACCESS_04_2020', null, 2020, "2020-04"));
        $tabbedPage->addTab(new ApplicationTab('RP_ACCESS_01_2020', null, 2020, "2020-01"));
        $tabbedPage->addTab(new ApplicationTab('RP_ACCESS_10_2019', null, 2019, "2019-10"));
        $tabbedPage->addTab(new ApplicationTab('RP_ACCESS_07_2019', null, 2019, "2019-07"));
        $tabbedPage->addTab(new ApplicationTab('RP_ACCESS_04_2019', null, 2019, "2019-04"));
        $tabbedPage->addTab(new ApplicationTab('RP_ACCESS_01_2019', null, 2019, "2019-01"));
        $tabbedPage->addTab(new ApplicationTab('RP_ACCESS_07_2018', null, 2018, "2018-07"));
        $tabbedPage->addTab(new ApplicationTab('RP_ACCESS_04_2018', null, 2018, "2018-04"));
        $tabbedPage->addTab(new ApplicationTab('RP_ACCESS_01_2018', null, 2018, "2018-01"));
        $tabbedPage->addTab(new ApplicationTab('RP_ACCESS_10_2017', null, 2017, "2017-10"));
        $tabbedPage->addTab(new ApplicationTab('RP_ACCESS_07_2017', null, 2017, "2017-07"));
        $tabbedPage->addTab(new ApplicationTab('RP_ACCESS_04_2017', null, 2017, "2017-04"));
        $tabbedPage->addTab(new ApplicationTab('RP_ACCESS_01_2017', null, 2017, "2017-01"));
        $tabbedPage->addTab(new ApplicationTab('RP_ACCESS_10_2016', null, 2016, "2016-10"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateAward(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        
        $sup = new TextReportItem();
        $sup->setBlobType(BLOB_TEXT);
        $sup->setBlobItem(HQP_APPLICATION_SUP);
        $sup->setBlobSection(HQP_APPLICATION_FORM);
        $sup->setId("supervisor");
        
        $uni = new TextReportItem();
        $uni->setBlobType(BLOB_TEXT);
        $uni->setBlobItem(HQP_APPLICATION_UNI);
        $uni->setBlobSection(HQP_APPLICATION_FORM);
        $uni->setId("uni");
        
        $dept = new TextReportItem();
        $dept->setBlobType(BLOB_TEXT);
        $dept->setBlobItem("HQP_APPLICATION_STAT");
        $dept->setBlobSection(HQP_APPLICATION_FORM);
        $dept->setId("status");
        
        $title = new TextReportItem();
        $title->setBlobType(BLOB_TEXT);
        $title->setBlobItem(HQP_APPLICATION_PROJ);
        $title->setBlobSection(HQP_APPLICATION_FORM);
        $title->setId("project");
        
        $keywords = new MultiTextReportItem();
        $keywords->setBlobType(BLOB_ARRAY);
        $keywords->setBlobItem(HQP_APPLICATION_KEYWORDS);
        $keywords->setBlobSection(HQP_APPLICATION_FORM);
        $keywords->setAttr('orientation', "list");
        $keywords->setId("keywords");
        
        $level = new CheckboxReportItem();
        $level->setBlobType(BLOB_ARRAY);
        $level->setBlobItem(HQP_APPLICATION_LVL);
        $level->setBlobSection(HQP_APPLICATION_FORM);
        $level->setId("level");
        
        $michael = new CheckboxReportItem();
        $michael->setBlobType(BLOB_ARRAY);
        $michael->setBlobItem("HQP_APPLICATION_MICHAEL");
        $michael->setBlobSection(HQP_APPLICATION_FORM);
        $michael->setId("MICHAEL");
        
        $ind = new CheckboxReportItem();
        $ind->setBlobType(BLOB_ARRAY);
        $ind->setBlobItem("HQP_APPLICATION_INDIGENOUS");
        $ind->setBlobSection(HQP_APPLICATION_FORM);
        $ind->setId("INDIGENOUS");
        
        $kob = new CheckboxReportItem();
        $kob->setBlobType(BLOB_ARRAY);
        $kob->setBlobItem("HQP_APPLICATION_KOBAYASHI");
        $kob->setBlobSection(HQP_APPLICATION_FORM);
        $kob->setId("KOBAYASHI");
             
        $bme = new CheckboxReportItem();
        $bme->setBlobType(BLOB_ARRAY);
        $bme->setBlobItem("HQP_APPLICATION_BME");
        $bme->setBlobSection(HQP_APPLICATION_FORM);
        $bme->setId("BME");
        
        $wbhi = new CheckboxReportItem();
        $wbhi->setBlobType(BLOB_ARRAY);
        $wbhi->setBlobItem("HQP_APPLICATION_WBHI");
        $wbhi->setBlobSection(HQP_APPLICATION_FORM);
        $wbhi->setId("WBHI");
        
        $mira = new CheckboxReportItem();
        $mira->setBlobType(BLOB_ARRAY);
        $mira->setBlobItem("HQP_APPLICATION_MIRA");
        $mira->setBlobSection(HQP_APPLICATION_FORM);
        $mira->setId("MIRA");
        
        $nbhrf = new CheckboxReportItem();
        $nbhrf->setBlobType(BLOB_ARRAY);
        $nbhrf->setBlobItem("HQP_APPLICATION_NBHRF");
        $nbhrf->setBlobSection(HQP_APPLICATION_FORM);
        $nbhrf->setId("NBHRF");
        
        $trp = new CheckboxReportItem();
        $trp->setBlobType(BLOB_ARRAY);
        $trp->setBlobItem("HQP_APPLICATION_TRP");
        $trp->setBlobSection(HQP_APPLICATION_FORM);
        $trp->setId("TRP");
        
        $uoft = new CheckboxReportItem();
        $uoft->setBlobType(BLOB_ARRAY);
        $uoft->setBlobItem("HQP_APPLICATION_UOFT");
        $uoft->setBlobSection(HQP_APPLICATION_FORM);
        $uoft->setId("UOFT");
        
        $sfu = new CheckboxReportItem();
        $sfu->setBlobType(BLOB_ARRAY);
        $sfu->setBlobItem("HQP_APPLICATION_SFU");
        $sfu->setBlobSection(HQP_APPLICATION_FORM);
        $sfu->setId("SFU");
        
        $ubc = new CheckboxReportItem();
        $ubc->setBlobType(BLOB_ARRAY);
        $ubc->setBlobItem("HQP_APPLICATION_UBC");
        $ubc->setBlobSection(HQP_APPLICATION_FORM);
        $ubc->setId("UBC");
        
        $shrf = new CheckboxReportItem();
        $shrf->setBlobType(BLOB_ARRAY);
        $shrf->setBlobItem("HQP_APPLICATION_SHRF");
        $shrf->setBlobSection(HQP_APPLICATION_FORM);
        $shrf->setId("SHRF");
        
        $uw = new CheckboxReportItem();
        $uw->setBlobType(BLOB_ARRAY);
        $uw->setBlobItem("HQP_APPLICATION_UW");
        $uw->setBlobSection(HQP_APPLICATION_FORM);
        $uw->setId("UW");
        
        $uofc = new CheckboxReportItem();
        $uofc->setBlobType(BLOB_ARRAY);
        $uofc->setBlobItem("HQP_APPLICATION_UOFC");
        $uofc->setBlobSection(HQP_APPLICATION_FORM);
        $uofc->setId("UOFC");
        
        $tabbedPage->addTab(new ApplicationTab(RP_HQP_APPLICATION, null, 2022, "2022", array("Level" => $level,
                                                                                             "Michael F. Harcourt" => $michael,
                                                                                             "Indigenous" => $ind,
                                                                                             "Kobayashi" => $kob,
                                                                                             "MIRA" => $mira,
                                                                                             "UofT" => $uoft,
                                                                                             "UBC" => $ubc,
                                                                                             "NBHRF" => $nbhrf,
                                                                                             "SHRF" => $shrf,
                                                                                             "UW" => $uw,
                                                                                             "UOFC" => $uofc,
                                                                                             "Supervisor" => $sup,
                                                                                             "Institution" => $uni,
                                                                                             "Status/Department" => $dept,
                                                                                             "Project Title" => $title,
                                                                                             "Keywords" => $keywords)));
        
        $tabbedPage->addTab(new ApplicationTab(RP_HQP_APPLICATION, null, 2021, "2021", array("Level" => $level,
                                                                                             "Michael F. Harcourt" => $michael,
                                                                                             "Indigenous" => $ind,
                                                                                             "MIRA" => $mira,
                                                                                             "UofT" => $uoft,
                                                                                             "UBC" => $ubc,
                                                                                             "NBHRF" => $nbhrf,
                                                                                             "SHRF" => $shrf,
                                                                                             "Supervisor" => $sup,
                                                                                             "Institution" => $uni,
                                                                                             "Status/Department" => $dept,
                                                                                             "Project Title" => $title,
                                                                                             "Keywords" => $keywords)));
        
        $tabbedPage->addTab(new ApplicationTab(RP_HQP_APPLICATION, null, 2020, "2020", array("Level" => $level,
                                                                                             "Michael F. Harcourt" => $michael,
                                                                                             "Indigenous" => $ind,
                                                                                             "MIRA" => $mira,
                                                                                             "UofT" => $uoft,
                                                                                             "SFU" => $sfu,
                                                                                             "NBHRF" => $nbhrf,
                                                                                             "SHRF" => $shrf,
                                                                                             "Supervisor" => $sup,
                                                                                             "Institution" => $uni,
                                                                                             "Status/Department" => $dept,
                                                                                             "Project Title" => $title,
                                                                                             "Keywords" => $keywords)));
        
        $tabbedPage->addTab(new ApplicationTab(RP_HQP_APPLICATION, null, 2019, "2019", array("Level" => $level,
                                                                                             "Michael F. Harcourt" => $michael,
                                                                                             "MIRA" => $mira,
                                                                                             "NBHRF" => $nbhrf,
                                                                                             "TRP" => $trp)));
        
        $tabbedPage->addTab(new ApplicationTab(RP_HQP_APPLICATION, null, 2018, "2018", array("Level" => $level,
                                                                                             "Michael F. Harcourt" => $michael,
                                                                                             "BME" => $bme,
                                                                                             "WBHI" => $wbhi,
                                                                                             "MIRA" => $mira,
                                                                                             "NBHRF" => $nbhrf)));
        $tabbedPage->addTab(new ApplicationTab(RP_HQP_APPLICATION, null, 2017, "2017"));
        $tabbedPage->addTab(new ApplicationTab(RP_HQP_APPLICATION, null, 2016, "2016"));
        $tabbedPage->addTab(new ApplicationTab(RP_HQP_APPLICATION, null, 2015, "2015"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateSummer(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab('RP_SUMMER', null, 2022, "2022"));
        $tabbedPage->addTab(new ApplicationTab('RP_SUMMER', null, 2020, "2020"));
        $tabbedPage->addTab(new ApplicationTab('RP_SUMMER', null, 2019, "2019"));
        $tabbedPage->addTab(new ApplicationTab('RP_SUMMER', null, 2018, "2018"));
        $tabbedPage->addTab(new ApplicationTab('RP_SUMMER', null, 2016, "2017"));
        $tabbedPage->addTab(new ApplicationTab('RP_SUMMER', null, 2015, "2016"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateEpicConference(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab('RP_EPIC_CONFERENCE', null, 2022, "2022"));
        $tabbedPage->addTab(new ApplicationTab('RP_EPIC_CONFERENCE', null, 2021, "2021"));
        $tabbedPage->addTab(new ApplicationTab('RP_EPIC_CONFERENCE', null, 2020, "2020"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateFellow(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab('RP_FELLOW', null, 2018, "2018"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateEpicAt(){
        global $wgOut;
        
        $stat = new TextReportItem();
        $stat->setBlobType(BLOB_TEXT);
        $stat->setBlobItem('HQP_APPLICATION_STAT');
        $stat->setBlobSection(HQP_APPLICATION_FORM);
        
        $sup = new TextReportItem();
        $sup->setBlobType(BLOB_TEXT);
        $sup->setBlobItem(HQP_APPLICATION_SUP);
        $sup->setBlobSection(HQP_APPLICATION_FORM);
        
        $uni = new TextReportItem();
        $uni->setBlobType(BLOB_TEXT);
        $uni->setBlobItem(HQP_APPLICATION_UNI);
        $uni->setBlobSection(HQP_APPLICATION_FORM);
        
        $lvl = new CheckboxReportItem();
        $lvl->setBlobType(BLOB_ARRAY);
        $lvl->setBlobItem(HQP_APPLICATION_LVL);
        $lvl->setBlobSection(HQP_APPLICATION_FORM);
        $lvl->setId("level");
        
        $mem = new CheckboxReportItem();
        $mem->setBlobType(BLOB_ARRAY);
        $mem->setBlobItem('HQP_APPLICATION_MEMBERSHIPS');
        $mem->setBlobSection(HQP_APPLICATION_FORM);
        $mem->setId("memberships");
        
        $title = new TextReportItem();
        $title->setBlobType(BLOB_TEXT);
        $title->setBlobItem('PROJECT_TITLE');
        $title->setBlobSection(HQP_APPLICATION_FORM);
        
        $keywords = new MultiTextReportItem();
        $keywords->setBlobType(BLOB_ARRAY);
        $keywords->setBlobItem(HQP_APPLICATION_KEYWORDS);
        $keywords->setBlobSection(HQP_APPLICATION_FORM);
        $keywords->setAttr("orientation", "list");
        $keywords->setId("keywords");
        
        $age = new CheckboxReportItem();
        $age->setBlobType(BLOB_ARRAY);
        $age->setBlobItem('AGE');
        $age->setBlobSection(HQP_APPLICATION_FORM);
        $age->setId("age");
        
        $gender = new CheckboxReportItem();
        $gender->setBlobType(BLOB_ARRAY);
        $gender->setBlobItem('GENDER');
        $gender->setBlobSection(HQP_APPLICATION_FORM);
        $gender->setId("gender");
        
        $gender_other = new TextReportItem();
        $gender_other->setBlobType(BLOB_TEXT);
        $gender_other->setBlobItem('GENDER_OTHER');
        $gender_other->setBlobSection(HQP_APPLICATION_FORM);
        
        $indigenous = new CheckboxReportItem();
        $indigenous->setBlobType(BLOB_ARRAY);
        $indigenous->setBlobItem('INDIGENOUS');
        $indigenous->setBlobSection(HQP_APPLICATION_FORM);
        $indigenous->setId("indigenous");
        
        $ethnicities = new CheckboxReportItem();
        $ethnicities->setBlobType(BLOB_ARRAY);
        $ethnicities->setBlobItem('ETHNICITIES');
        $ethnicities->setBlobSection(HQP_APPLICATION_FORM);
        $ethnicities->setId("ethnicities");
        
        $ethnicities_other = new TextReportItem();
        $ethnicities_other->setBlobType(BLOB_TEXT);
        $ethnicities_other->setBlobItem('ETHNICITIES_OTHER');
        $ethnicities_other->setBlobSection(HQP_APPLICATION_FORM);
        
        $disability = new CheckboxReportItem();
        $disability->setBlobType(BLOB_ARRAY);
        $disability->setBlobItem('DISABILITY');
        $disability->setBlobSection(HQP_APPLICATION_FORM);
        $disability->setId("disability");
        
        $postsecondary = new CheckboxReportItem();
        $postsecondary->setBlobType(BLOB_ARRAY);
        $postsecondary->setBlobItem('POSTSECONDARY');
        $postsecondary->setBlobSection(HQP_APPLICATION_FORM);
        $postsecondary->setId("postsecondary");
        
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab('RP_EPIC_AT', null, 2023, "2023", array("Academic Status" => $stat,
                                                                                       "Institution" => $uni,
                                                                                       "Level" => $lvl,
                                                                                       "Title" => $title,
                                                                                       "Age" => $age,
                                                                                       "Gender" => $gender,
                                                                                       "Gender (Other)" => $gender_other,
                                                                                       "Indigenous" => $indigenous,
                                                                                       "Ethnicities" => $ethnicities,
                                                                                       "Ethnicities (Other)" => $ethnicities_other,
                                                                                       "Disability" => $disability,
                                                                                       "Post-Secondary" => $postsecondary
                                                                                       )));
        $tabbedPage->addTab(new ApplicationTab('RP_EPIC_AT', null, 2022, "2022", array("Academic Status" => $stat,
                                                                                       "Supervisor" => $sup,
                                                                                       "Institution" => $uni,
                                                                                       "Level" => $lvl,
                                                                                       "Memberships" => $mem,
                                                                                       "Title" => $title,
                                                                                       "Keywords" => $keywords,
                                                                                       "Age" => $age,
                                                                                       "Gender" => $gender,
                                                                                       "Gender (Other)" => $gender_other,
                                                                                       "Indigenous" => $indigenous,
                                                                                       "Ethnicities" => $ethnicities,
                                                                                       "Ethnicities (Other)" => $ethnicities_other,
                                                                                       "Disability" => $disability,
                                                                                       "Post-Secondary" => $postsecondary
                                                                                       )));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateEEA(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab('RP_EEA', null, 2022, "2022"));
        $tabbedPage->addTab(new ApplicationTab('RP_EEA', null, 2021, "2021"));
        $tabbedPage->addTab(new ApplicationTab('RP_EEA', null, 2020, "2020"));
        $tabbedPage->addTab(new ApplicationTab('RP_EEA', null, 2019, "2019"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateEdge(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab('RP_EDGE', null, 2019, "2019"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateWP(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab('RP_WP_REPORT', $this->wps, 2019, "2019-20"));
        $tabbedPage->addTab(new ApplicationTab('RP_WP_REPORT', $this->wps, 2018, "2018-19"));
        $tabbedPage->addTab(new ApplicationTab('RP_WP_REPORT', $this->wps, 2017, "2017-18"));
        $tabbedPage->addTab(new ApplicationTab('RP_WP_REPORT', $this->wps, 2016, "2016-17"));
        $tabbedPage->addTab(new ApplicationTab('RP_WP_REPORT', $this->wps, 2015, "2015-16"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateCC(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab('RP_WP_REPORT', $this->ccs, 2019, "2019-20"));
        $tabbedPage->addTab(new ApplicationTab('RP_WP_REPORT', $this->ccs, 2018, "2018-19"));
        $tabbedPage->addTab(new ApplicationTab('RP_WP_REPORT', $this->ccs, 2017, "2017-18"));
        $tabbedPage->addTab(new ApplicationTab('RP_WP_REPORT', $this->ccs, 2016, "2016-17"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateIH(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        for($year=YEAR; $year >= 2017; $year--){
            $tabbedPage->addTab(new ApplicationTab('RP_WP_REPORT', $this->ihs, $year, "{$year}-".($year+1)));
        }
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateProject(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab('RP_CRP_PPP_REPORT', $this->projects, 2022, "CRP-PPP Mid-Year"));
        $tabbedPage->addTab(new ApplicationTab('RP_PROJ_PLAN_UPDATE', $this->projects, 2021, "Progress Evaluation"));
        $tabbedPage->addTab(new ApplicationTab('RP_PROJ_PLAN_UPDATE', $this->projects, 2020, "Project Plan Update"));
        $tabbedPage->addTab(new ApplicationTab('RP_CRP_REPORT', $this->projects, 2019, "CRP End of Term"));
        $tabbedPage->addTab(new ApplicationTab('RP_PROJ_EVALUATION', $this->projects, 2019, "2020"));
        $tabbedPage->addTab(new ApplicationTab('RP_PLAT_REPORT', $this->platform, 2019, "PLAT-2019 Report"));
        $tabbedPage->addTab(new ApplicationTab('RP_CAT_REPORT', $this->catalyst, 2019, "CAT-2019 Report"));
        $tabbedPage->addTab(new ApplicationTab('RP_PLAT_SCORECARD', $this->platform, 2018, "PLAT-2019 Scorecard"));
        $tabbedPage->addTab(new ApplicationTab('RP_CAT_SCORECARD', $this->catalyst, 2018, "CAT-2019 Scorecard"));
        $tabbedPage->addTab(new ApplicationTab('RP_PROJ_EVALUATION', $this->projects, 2018, "2019"));
        $tabbedPage->addTab(new ApplicationTab('RP_PROJ_EVALUATION', $this->projects, 2017, "2018"));
        $tabbedPage->addTab(new ApplicationTab('RP_PROJ_EVALUATION', $this->projects, 2016, "2017"));
        $tabbedPage->addTab(new ApplicationTab('RP_PROJ_EVALUATION', $this->projects, 2015, "2016"));
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
