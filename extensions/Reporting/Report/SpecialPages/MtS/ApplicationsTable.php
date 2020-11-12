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
        return ($person->isRoleAtLeast(STAFF));
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        ApplicationsTable::generateHTML($wgOut);
    }
    
    function initArrays(){
        $this->nis = array_merge(Person::getAllPeople(NI), 
                                 Person::getAllCandidates(NI),
                                 Person::getAllPeople(EXTERNAL),
                                 Person::getAllCandidates(EXTERNAL));
        
        $this->fullHQPs = Person::getAllPeople(HQP);
        
        $this->hqps = array_merge($this->fullHQPs,
                                  Person::getAllCandidates(HQP));
        
        $this->everyone = array_merge(Person::getAllPeople(),
                                      Person::getAllCandidates());
                                  
        $this->themes = Theme::getAllThemes();

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

        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=impact'>Impact</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=datatech'>DataTech</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=openround2'>OpenRound2</a>";

        
        $wgOut->addHTML("<h1>Report Tables:&nbsp;".implode("&nbsp;|&nbsp;", $links)."</h1><br />");
        if(!isset($_GET['program'])){
            return;
        }
        $program = $_GET['program'];
        
        $this->initArrays();
        
        if($program == "impact"){
            $this->generateImpact();
        }
        else if($program == "datatech"){
            $this->generateDataTech();
        }
        else if($program == "openround2"){
            $this->generateOpenRound2();
        }
        return;
    }
    
    function generateImpact(){
        global $wgOut;

        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab('RP_IMPACT', $this->projects, 2020, "2020"));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateDataTech(){
        global $wgOut;

        $survey = array('title' => 'Title',
                        'stream' => 'Stream',
                        'applicant' => 'Applicant',
                        'position' => 'Position',
                        'dept' => 'Department',
                        'institution' => 'Institution',
                        'dept' => 'Department',
                        'tri_council' => "Tri-Council",
                        'sign_off' => "Sign-Off",
                        'other_funding' => "Other Funding",
                        'sources' => "Sources",
                        'ci1' => 'CI1',
                        'ci1_institution' => 'CI1 Institution',
                        'ci2' => 'CI2',
                        'ci2_institution' => 'CI2 Institution',
                        'involve' => "Involve",
                        'amount' => "Amount");
        
        $fields = array();
        foreach($survey as $key => $label){
            if($key == "involve"){
                $field = new CheckboxReportItem();
                $field->setBlobType(BLOB_ARRAY);
            }
            else{
                $field = new TextReportItem();
                $field->setBlobType(BLOB_TEXT);
            }
            $field->setBlobSection("SURVEY");
            $field->setBlobItem(strtoupper($key));
            $field->setId($key);
            $fields[$label] = $field;
        }
        
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab('RP_DATA_TECH', $this->everyone, 2020, "2020", $fields));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateOpenRound2(){
        global $wgOut;

        $survey = array('title' => 'Title',
                        'applicant' => 'Applicant',
                        'position' => 'Position',
                        'dept' => 'Department',
                        'institution' => 'Institution',
                        'dept' => 'Department',
                        'tri_council' => "Tri-Council",
                        'sign_off' => "Sign-Off",
                        'other_funding' => "Other Funding",
                        'sources' => "Sources",
                        'ci1' => 'CI1',
                        'ci1_institution' => 'CI1 Institution',
                        'ci2' => 'CI2',
                        'ci2_institution' => 'CI2 Institution',
                        'involve' => "Involve",
                        'amount' => "Amount");
        
        $fields = array();
        foreach($survey as $key => $label){
            if($key == "involve"){
                $field = new CheckboxReportItem();
                $field->setBlobType(BLOB_ARRAY);
            }
            else{
                $field = new TextReportItem();
                $field->setBlobType(BLOB_TEXT);
            }
            $field->setBlobSection("SURVEY");
            $field->setBlobItem(strtoupper($key));
            $field->setId($key);
            $fields[$label] = $field;
        }
        
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab('RP_OPEN2', $this->everyone, 2020, "2020", $fields));
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
