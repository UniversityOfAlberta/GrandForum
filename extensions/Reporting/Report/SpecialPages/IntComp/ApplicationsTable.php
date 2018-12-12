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
        return ($person->isRoleAtLeast(STAFF));
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
        
        $wgOut->addHTML("<h1>Report Tables:&nbsp;".implode("&nbsp;|&nbsp;", $links)."</h1><br />");
        if(!isset($_GET['program'])){
            return;
        }
        $program = $_GET['program'];
        
        $this->initArrays();
        
        /*if($program == "candidates" && $me->isRoleAtLeast(SD)){
            $this->generateCandidates();
        }*/
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
