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
        
        //$links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=candidates'>Candidates</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=loi'>JIC LOIs</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=huawei'>JIC</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=progress'>Progress</a>";
        
        $wgOut->addHTML("<h1>Report Tables:&nbsp;".implode("&nbsp;|&nbsp;", $links)."</h1><br />");
        if(!isset($_GET['program'])){
            return;
        }
        $program = $_GET['program'];
        
        $this->initArrays();
        
        if($program == "loi"){
            $this->generateLOI();
        }
        else if($program == "huawei"){
            $this->generateHuawei();
        }
        else if($program == "progress"){
            $this->generateProgress();
        }
        return;
    }
    
    function generateCandidates(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new CandidatesTab());
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateLOI(){
        global $wgOut;
        
        $title = new TextReportItem();
        $title->setBlobType(BLOB_TEXT);
        $title->setBlobItem("TITLE");
        $title->setBlobSection(PROP_DESC);
        $title->setId("title");
        
        $duration = new TextReportItem();
        $duration->setBlobType(BLOB_TEXT);
        $duration->setBlobItem("DURATION");
        $duration->setBlobSection(PROP_DESC);
        $duration->setId("duration");
        
        $budget = new TextReportItem();
        $budget->setBlobType(BLOB_TEXT);
        $budget->setBlobItem("BUDGET");
        $budget->setBlobSection(PROP_DESC);
        $budget->setId("budget");
        
        $pi = new MultiTextReportItem();
        $pi->setBlobType(BLOB_ARRAY);
        $pi->setBlobItem("PI");
        $pi->setBlobSection(PROP_DESC);
        $pi->attributes['labels'] = 'Name|E-mail';
        $pi->setAttr("showHeader", "false");
        $pi->setAttr("class", "wikitable");
        $pi->setAttr("orientation", "list");
        $pi->setId("pi");
        
        $contact = new MultiTextReportItem();
        $contact->setBlobType(BLOB_ARRAY);
        $contact->setBlobItem("CONTACT");
        $contact->setBlobSection(PROP_DESC);
        $contact->attributes['labels'] = 'Name|E-mail';
        $contact->setAttr("showHeader", "false");
        $contact->setAttr("class", "wikitable");
        $contact->setAttr("orientation", "list");
        $contact->setId("contact");
        
        $primary = new TextReportItem();
        $primary->setBlobType(BLOB_TEXT);
        $primary->setBlobItem("PRIMARY");
        $primary->setBlobSection(PROP_DESC);
        $primary->setId("primary");
        
        $primary_other = new TextReportItem();
        $primary_other->setBlobType(BLOB_TEXT);
        $primary_other->setBlobItem("PRIMARY_OTHER");
        $primary_other->setBlobSection(PROP_DESC);
        $primary_other->setId("primary_other");
        
        $secondary = new TextReportItem();
        $secondary->setBlobType(BLOB_TEXT);
        $secondary->setBlobItem("SECONDARY");
        $secondary->setBlobSection(PROP_DESC);
        $secondary->setId("secondary");
        
        $secondary_other = new TextReportItem();
        $secondary_other->setBlobType(BLOB_TEXT);
        $secondary_other->setBlobItem("SECONDARY_OTHER");
        $secondary_other->setBlobSection(PROP_DESC);
        $secondary_other->setId("secondary_other");
        
        $tabbedPage = new InnerTabbedPage("reports");
        $tab1 = new ApplicationTab('RP_LOI', null, 2018, "Winter 2019", array('Title' => $title, 
                                                                              'Duration' => $duration,
                                                                              'Budget ($K)' => $budget,
                                                                              'PI' => $pi,
                                                                              'Contact' => $contact,
                                                                              'Primary' => $primary,
                                                                              'Primary (Other)' => $primary_other,
                                                                              'Secondary' => $secondary,
                                                                              'Secondary (Other)' => $secondary_other));
        $tab1->idProjectRange = array(0,1);
        
        $tab2 = new ApplicationTab('RP_LOI_FALL_2019', null, 2018, "Fall 2019", array('Title' => $title, 
                                                                                      'Duration' => $duration,
                                                                                      'Budget ($K)' => $budget,
                                                                                      'PI' => $pi,
                                                                                      'Contact' => $contact,
                                                                                      'Primary' => $primary,
                                                                                      'Secondary' => $secondary));
        $tab2->idProjectRange = array(0,1,2,3,4,5,6,7,8,9);
        
        $tab3 = new ApplicationTab('RP_LOI_2021', null, 2021, "Spring 2021", array('Title' => $title, 
                                                                                   'Duration' => $duration,
                                                                                   'PI' => $pi,
                                                                                   'Contact' => $contact,
                                                                                   'Primary' => $primary,
                                                                                   'Secondary' => $secondary));
        $tab3->idProjectRange = array(0,1,2,3,4,5,6,7,8,9);
        
        $tabbedPage->addTab($tab3);
        $tabbedPage->addTab($tab2);
        $tabbedPage->addTab($tab1);
        
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateHuawei(){
        global $wgOut;
        
        $title = new TextReportItem();
        $title->setBlobType(BLOB_TEXT);
        $title->setBlobItem("TITLE");
        $title->setBlobSection(PROP_DESC);
        $title->setId("title");
        
        $tabbedPage = new InnerTabbedPage("reports");
        $tab1 = new ApplicationTab('RP_HUAWEI', null, 2018, "Winter 2019", array('Title' => $title));
        $tab1->idProjectRange = array(0,1);
        
        $tab2 = new ApplicationTab('RP_HUAWEI_FALL_2019', null, 2018, "Fall 2019", array('Title' => $title));
        $tab2->idProjectRange = array(0,1,2,3,4,5,6,7,8,9);
        $tabbedPage->addTab($tab2);
        $tabbedPage->addTab($tab1);
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateProgress(){
        global $wgOut;
        
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab("RP_PROGRESS_REPORT", null, 2021, "May 2021"));
        $tabbedPage->addTab(new ApplicationTab("RP_PROGRESS_REPORT", null, 2018, "2020"));
        
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        
        if((new self)->userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "ApplicationsTable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("Reports", "$wgServer$wgScriptPath/index.php/Special:ApplicationsTable", $selected);
        }
        return true;
    }

}

?>
