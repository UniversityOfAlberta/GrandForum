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
    var $inactives;
    var $fullHQPs;
    var $hqps;
    var $projects;

    function ApplicationsTable() {
        SpecialPage::__construct("ApplicationsTable", null, false, 'runApplicationsTable');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(SD));
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        ApplicationsTable::generateHTML($wgOut);
    }
    
    function initArrays(){

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
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=ifp'>IFP</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=kt'>KT</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=rcha'>RCHA</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=rcha'>Ancillary</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=ecr'>Early Career</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=cat'>Catalyst</a>";
        }
        
        $wgOut->addHTML("<h1>Report Tables:&nbsp;".implode("&nbsp;|&nbsp;", $links)."</h1><br />");
        if(!isset($_GET['program'])){
            return;
        }
        $program = $_GET['program'];
        
        $this->initArrays();
        
        if($program == "ifp" && $me->isRoleAtLeast(SD)){
            $this->generateIFP();
        }
        else if($program == "kt" && $me->isRoleAtLeast(SD)){
            $this->generateKT();
        }
        else if($program == "rcha" && $me->isRoleAtLeast(SD)){
            $this->generateRCHA();
        }
        else if($program == "ancillary" && $me->isRoleAtLeast(SD)){
            $this->generateAncillary();
        }
        else if($program == "ecr" && $me->isRoleAtLeast(SD)){
            $this->generateECR();
        }
        else if($program == "cat" && $me->isRoleAtLeast(SD)){
            $this->generateCat();
        }
    }
    
    function generateIFP(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab("RP_IFP_APPLICATION", null, 2020, "2020"));
        $tabbedPage->addTab(new ApplicationTab("RP_IFP_APPLICATION", null, 2019, "2019"));
        $tabbedPage->addTab(new ApplicationTab("RP_IFP_APPLICATION", null, 2018, "2018"));
        $tabbedPage->addTab(new ApplicationTab("RP_IFP_APPLICATION", null, 2017, "2017"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateKT(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $pis = new MultiTextReportItem();
        $pis->setBlobType(BLOB_ARRAY);
        $pis->setBlobItem("PI");
        $pis->setBlobSection("INTENT");
        $pis->setAttr("labels", "First Name|Last Name|Email Address|Institution that will receive/administer funds|Title at Institution/Organization");
        $pis->setAttr("types", "text|text|text|text|text");
        $pis->setAttr("multiple", "true");
        $pis->setAttr("showHeader", "false");
        $pis->setAttr("class", "wikitable");
        $pis->setAttr("orientation", "list");
        $pis->setId("pi");
        
        $cis = new MultiTextReportItem();
        $cis->setBlobType(BLOB_ARRAY);
        $cis->setBlobItem("CI");
        $cis->setBlobSection("INTENT");
        $cis->setAttr("labels", "First Name|Last Name|Email Address|Institution/Organization|Title at Institution/Organization");
        $cis->setAttr("types", "text|text|text|text|text");
        $cis->setAttr("multiple", "true");
        $cis->setAttr("showHeader", "false");
        $cis->setAttr("class", "wikitable");
        $cis->setAttr("orientation", "list");
        $cis->setId("ci");
        $tabbedPage->addTab(new ApplicationTab("KT2019Application", null, 2019, "2019"));
        $tabbedPage->addTab(new ApplicationTab("KT2019Intent", null, 2019, "2019 Intent", array("PIs" => $pis, "CIs" => $cis)));
        $tabbedPage->addTab(new ApplicationTab("RP_KT_APPLICATION", null, 2017, "2017"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateRCHA(){
        global $wgOut;
        
        $allPeople = array_merge(Person::getAllCandidates(), Person::getAllPeople());
        $fullApplicants = array();
        foreach($allPeople as $person){
            if($person->isSubRole('RCHA2021Applicant')){
                $fullApplicants[$person->getId()] = $person;
            }
        }
        
        $tabbedPage = new InnerTabbedPage("reports");
        $pis = new MultiTextReportItem();
        $pis->setBlobType(BLOB_ARRAY);
        $pis->setBlobItem("PI");
        $pis->setBlobSection("INTENT");
        $pis->setAttr("labels", "First Name|Last Name|Email Address|Institution that will receive/administer funds|Title at Institution/Organization");
        $pis->setAttr("types", "text|text|text|text|text");
        $pis->setAttr("multiple", "true");
        $pis->setAttr("showHeader", "false");
        $pis->setAttr("class", "wikitable");
        $pis->setAttr("orientation", "list");
        $pis->setId("pi");
        
        $cis = new MultiTextReportItem();
        $cis->setBlobType(BLOB_ARRAY);
        $cis->setBlobItem(CI);
        $cis->setBlobSection("INTENT");
        $cis->setAttr("labels", "First Name|Last Name|Email Address|Institution/Organization|Title at Institution/Organization");
        $cis->setAttr("types", "text|text|text|text|text");
        $cis->setAttr("multiple", "true");
        $cis->setAttr("showHeader", "false");
        $cis->setAttr("class", "wikitable");
        $cis->setAttr("orientation", "list");
        $cis->setId("ci");
        $tabbedPage->addTab(new ApplicationTab("RCHA2021Application", $fullApplicants, 2021, "2021"));
        $tabbedPage->addTab(new ApplicationTab("RCHA2021Intent", null, 2021, "2021 Intent", array("PIs" => $pis, "CIs" => $cis)));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateAncillary(){
        global $wgOut;
        
        $allPeople = array_merge(Person::getAllCandidates(), Person::getAllPeople());
        $fullApplicants = array();
        foreach($allPeople as $person){
            if($person->isSubRole('AncillaryApplicant')){
                $fullApplicants[$person->getId()] = $person;
            }
        }
        
        $tabbedPage = new InnerTabbedPage("reports");
        $pis = new MultiTextReportItem();
        $pis->setBlobType(BLOB_ARRAY);
        $pis->setBlobItem("PI");
        $pis->setBlobSection("INTENT");
        $pis->setAttr("labels", "First Name|Last Name|Email Address|Institution that will receive/administer funds|Title at Institution/Organization");
        $pis->setAttr("types", "text|text|text|text|text");
        $pis->setAttr("multiple", "true");
        $pis->setAttr("showHeader", "false");
        $pis->setAttr("class", "wikitable");
        $pis->setAttr("orientation", "list");
        $pis->setId("pi");
        
        $cis = new MultiTextReportItem();
        $cis->setBlobType(BLOB_ARRAY);
        $cis->setBlobItem(CI);
        $cis->setBlobSection("INTENT");
        $cis->setAttr("labels", "First Name|Last Name|Email Address|Institution/Organization|Title at Institution/Organization");
        $cis->setAttr("types", "text|text|text|text|text");
        $cis->setAttr("multiple", "true");
        $cis->setAttr("showHeader", "false");
        $cis->setAttr("class", "wikitable");
        $cis->setAttr("orientation", "list");
        $cis->setId("ci");
        //$tabbedPage->addTab(new ApplicationTab("RCHA2021Application", $fullApplicants, 2021, "2021"));
        $tabbedPage->addTab(new ApplicationTab("AncillaryStudiesIntent", null, 2022, "2022 Intent", array("PIs" => $pis, "CIs" => $cis)));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateECR(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab("EarlyCareerApplication", null, 2022, "2022"));
        $tabbedPage->addTab(new ApplicationTab("EarlyCareerIntent", null, 2022, "2022 Intent"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateCat(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab("Catalyst2018Application", null, 2018, "2018"));
        $tabbedPage->addTab(new ApplicationTab("Catalyst2017Application", null, 2017, "2017"));
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
