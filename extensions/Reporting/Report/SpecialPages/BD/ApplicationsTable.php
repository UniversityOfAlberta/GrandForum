<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['ApplicationsTable'] = 'ApplicationsTable'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['ApplicationsTable'] = $dir . 'ApplicationsTable.i18n.php';
$wgSpecialPageGroups['ApplicationsTable'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'ApplicationsTable::createSubTabs';

require_once("SurveyTab.php");

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

    function __construct() {
        SpecialPage::__construct("ApplicationsTable", null, false, 'runApplicationsTable');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF));
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        $this->getOutput()->setPageTitle("Report Table");
        ApplicationsTable::generateHTML($wgOut);
    }
    
    function initArrays(){
        $this->themes = Theme::getAllThemes();
        $this->projects = Project::getAllProjectsEver(false, true);
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
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=project'>Project</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=theme'>Theme</a>";
            $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=survey'>Survey</a>";
        }
        
        $wgOut->addHTML("<h1>Report Tables:&nbsp;".implode("&nbsp;|&nbsp;", $links)."</h1><br />");
        if(!isset($_GET['program'])){
            return;
        }
        $program = $_GET['program'];
        
        $this->initArrays();
        
        if($program == "project" && $me->isRoleAtLeast(SD)){
            $this->generateProject();
        }
        else if($program == "theme" && $me->isRoleAtLeast(SD)){
            $this->generateTheme();
        }
        else if($program == "survey" && $me->isRoleAtLeast(SD)){
            $this->generateSurvey();
        }
        return;
    }
    
    function generateProject(){
        global $wgOut, $config;
        $tabbedPage = new InnerTabbedPage("reports");
        $max = Report::dateToProjectQuarter(date('Y-m-d'));
        for($y=date('Y');$y>=substr($config->getValue('projectPhaseDates')[1],0,4);$y--){
            for($q=4;$q>=1;$q--){
                $quarter = "{$y}_Q{$q}";
                if($quarter <= $max){
                    $tabbedPage->addTab(new ApplicationTab("ProjectReport", $this->projects, 0, "{$y}: Q{$q}", array(), false, null, array('id' => $quarter)));
                }
            }
        }
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateTheme(){
        global $wgOut, $config;
        $tabbedPage = new InnerTabbedPage("reports");
        $max = Report::dateToThemeQuarter(date('Y-m-d'));
        for($y=date('Y');$y>=substr($config->getValue('projectPhaseDates')[1],0,4);$y--){
            for($q=4;$q>=1;$q--){
                $quarter = "{$y}_Q{$q}";
                if($quarter <= $max){
                    $tabbedPage->addTab(new ApplicationTab("ThemeReport", $this->themes, 0, "{$y}: Q{$q}", array(), false, null, array('id' => $quarter)));
                }
            }
        }
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateSurvey(){
        global $wgOut, $config;
        $tabbedPage = new InnerTabbedPage("reports");
        for($y=date('Y');$y>=substr($config->getValue('projectPhaseDates')[1],0,4);$y--){
            $tabbedPage->addTab(new SurveyTab($y));
        }
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