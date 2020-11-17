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
        
        $ceri1 = new AverageArrayReportItem();
        $ceri1->setBlobType(BLOB_ARRAY);
        $ceri1->setBlobItem('CERI');
        $ceri1->setBlobSection("SECTION3");
        $ceri1->setAttr("indices", "ceri_1_1|ceri_2_1|ceri_3_1|ceri_4_1|ceri_5_1|ceri_6_1|ceri_7_1|ceri_8_1|ceri_9_1|ceri_10_1|ceri_11_1|ceri_12_1|ceri_13_1");
        
        $ceri2 = new AverageArrayReportItem();
        $ceri2->setBlobType(BLOB_ARRAY);
        $ceri2->setBlobItem('CERI');
        $ceri2->setBlobSection("SECTION3");
        $ceri2->setAttr("indices", "ceri_1_2|ceri_2_2|ceri_3_2|ceri_4_2|ceri_5_2|ceri_6_2|ceri_7_2|ceri_8_2|ceri_9_2|ceri_10_2|ceri_11_2|ceri_12_2|ceri_13_2");
        
        $ceri3 = new AverageArrayReportItem();
        $ceri3->setBlobType(BLOB_ARRAY);
        $ceri3->setBlobItem('CERI');
        $ceri3->setBlobSection("SECTION3");
        $ceri3->setAttr("indices", "ceri_1_3|ceri_2_3|ceri_3_3|ceri_4_3|ceri_5_3|ceri_6_3|ceri_7_3|ceri_8_3|ceri_9_3|ceri_10_3|ceri_11_3|ceri_12_3|ceri_13_3");
        
        $ceri4 = new AverageArrayReportItem();
        $ceri4->setBlobType(BLOB_ARRAY);
        $ceri4->setBlobItem('CERI');
        $ceri4->setBlobSection("SECTION3");
        $ceri4->setAttr("indices", "ceri_1_4|ceri_2_4|ceri_3_4|ceri_4_4|ceri_5_4|ceri_6_4|ceri_7_4|ceri_8_4|ceri_9_4|ceri_10_4|ceri_11_4|ceri_12_4|ceri_13_4");
        
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab('RP_IMPACT', $this->projects, 2020, "2020", array('Service-delivery agencies' => $ceri1,
                                                                                                 'Indigenous community-based agencies and/or governing bodies' => $ceri2,
                                                                                                 'Persons with lived experiences of homelessness' => $ceri3,
                                                                                                 'Orders of Government' => $ceri4)));
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
