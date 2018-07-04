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

    var $allPeople;

    function ApplicationsTable() {
        SpecialPage::__construct("ApplicationsTable", null, false, 'runApplicationsTable');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF));
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        ApplicationsTable::generateHTML($wgOut);
    }
    
    function initArrays(){
        $this->allPeople = array_merge(Person::getAllPeople(), Person::getAllCandidates());
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

        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=eoi'>EOI</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=projects'>Projects</a>";
        $wgOut->addHTML("<h1>Report Tables:&nbsp;".implode("&nbsp;|&nbsp;", $links)."</h1><br />");
        
        if(!isset($_GET['program'])){
            return;
        }
        $program = $_GET['program'];
        
        $this->initArrays();
        
        if($program == "eoi" && $me->isRoleAtLeast(SD)){
            $this->generateEOI();
        } else if($program == "projects" && $me->isRoleAtLeast(SD)){
            $this->generateProjects();        
        }
        return;
    }
    
    function generateEOI(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        
        $themes1 = new RadioReportItem();
        $themes1->setBlobType(BLOB_TEXT);
        $themes1->setBlobItem("PRIMARY_THEMES");
        $themes1->setBlobSection("EOI");
        $themes1->setId("primary_themes");
        
        $themes2 = new CheckboxReportItem();
        $themes2->setBlobType(BLOB_ARRAY);
        $themes2->setBlobItem("SECONDARY_THEMES");
        $themes2->setBlobSection("EOI");
        $themes2->setId("secondary_themes");
        
        $tab = new ApplicationTab('RP_EOI', $this->allPeople, 2018, "EOI", array('Primary Theme' => $themes1, 'Secondary Themes' => $themes2));
        $tab->idProjectRange = array(1,2,3);
        $tabbedPage->addTab($tab);
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateProjects(){
        global $wgOut;
        $tabbedPage = new InnerTabbedPage("reports");
        
        $title = new TextReportItem();
        $title->setBlobType(BLOB_TEXT);
        $title->setBlobItem("TITLE");
        $title->setBlobSection("EOI");
        $title->setId("title"); 
        
        
        $copis = new MultiTextReportItem();
        $copis->setBlobType(BLOB_ARRAY);
        $copis->setBlobItem("COPIS");
        $copis->setBlobSection("EOI");
        $copis->setId("copis");
        
        $partner = new MultiTextReportItem();
        $partner->setBlobType(BLOB_ARRAY);
        $partner->setBlobItem("PARTNERS");
        $partner->setBlobSection("EOI");
        $partner->setId("partners");
        
        $summary = new TextareaReportItem();
        $summary->setBlobType(BLOB_TEXT);
        $summary->setBlobItem("SUMMARY");
        $summary->setBlobSection("EOI");
        $summary->setId("summary");
        
        $themes1 = new RadioReportItem();
        $themes1->setBlobType(BLOB_TEXT);
        $themes1->setBlobItem("PRIMARY_THEMES");
        $themes1->setBlobSection("EOI");
        $themes1->setId("primary_themes");
        
        $themes2 = new CheckboxReportItem();
        $themes2->setBlobType(BLOB_ARRAY);
        $themes2->setBlobItem("SECONDARY_THEMES");
        $themes2->setBlobSection("EOI");
        $themes2->setId("secondary_themes");
        
        
        $tab = new ProjectTab('RP_EOI', $this->allPeople, 2018, "EOI", array('Title' => $title, 'Co-PIs' => $copis, 'Partners' => $partner, 'Summary' => $summary, 'Primary Theme' => $themes1, 'Secondary Themes' => $themes2));
        $tab->idProjectRange = array(1, 2, 3);
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
