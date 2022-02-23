<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['AVOIDDashboard'] = 'AVOIDDashboard'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AVOIDDashboard'] = $dir . 'AVOIDDashboard.i18n.php';
$wgSpecialPageGroups['AVOIDDashboard'] = 'reporting-tools';

$wgHooks['TopLevelTabs'][] = 'AVOIDDashboard::createTab';
$wgHooks['SubLevelTabs'][] = 'AVOIDDashboard::createSubTabs';
array_unshift($wgHooks['ArticleViewHeader'], 'AVOIDDashboard::processPage');

function runDashboard($par) {
    AVOIDDashboard::execute($par);
}

class AVOIDDashboard extends SpecialPage {
    
    function __construct() {
		SpecialPage::__construct("AVOIDDashboard", null, true, 'runDashboard');
	}
	
	function userCanExecute($wgUser){
	    $person = Person::newFromUser($wgUser);
	    return $person->isLoggedIn();
	}
	
	function execute($par){
	    global $wgOut, $wgServer, $wgScriptPath;
	    $dir = dirname(__FILE__) . '/';
	    $modules = json_decode(file_get_contents("{$dir}EducationModules/modules.json"));
	    $programs = json_decode(file_get_contents("{$dir}Programs/programs.json"));
	    $wgOut->setPageTitle("AVOID Dashboard");
	    $wgOut->addHTML("<div class='modules'>");
	    // Education
	    $wgOut->addHTML("<div class='modules module-2cols'>");
	    $wgOut->addHTML("<div class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'>Education</div>");
	    $cols = 3;
        $n = 0;
        foreach($modules as $key => $module){
            if($key >= $cols){ break; }
            $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=EducationModules/{$module->id}";
            $percent = rand(0,100);
            $wgOut->addHTML("<div id='module{$module->id}' class='module module-{$cols}cols' href='{$url}'>
                <img src='{$wgServer}{$wgScriptPath}/EducationModules/{$module->id}/thumbnail.png' />
                <div class='module-progress'>
                    <div class='module-progress-bar'></div>
                    <div class='module-progress-text'></div>
                </div>
            </div>");
            $n++;
        }
        for($i = 0; $i < $cols - ($n % $cols); $i++){
            $wgOut->addHTML("<div class='module-empty module-{$cols}cols'></div>");
        }
	    $wgOut->addHTML("</div>");
	    
	    // Programs
	    $wgOut->addHTML("<div class='modules module-2cols'>");
        $wgOut->addHTML("<div class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'>Programs</div>");
        $programs = json_decode(file_get_contents("{$dir}Programs/programs.json"));
        
        $cols = 3;
        $n = 0;
        foreach($programs as $key => $program){
            if($key >= $cols){ break; }
            $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=Programs/{$program->id}";
            $percent = rand(0,100);
            $wgOut->addHTML("<div id='module{$program->id}' class='module module-{$cols}cols' href='{$url}'>
                <img src='{$wgServer}{$wgScriptPath}/EducationModules/{$program->id}.png' />
                <div class='module-progress-text' style='border-top: 2px solid #548ec9;'>{$program->title}</div>
            </div>");
            $n++;
        }
        for($i = 0; $i < $cols - ($n % $cols); $i++){
            $wgOut->addHTML("<div class='module-empty module-{$cols}cols'></div>");
        }
        $wgOut->addHTML("</div>");
	    
	    // Resources
	    $wgOut->addHTML("<div class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'>Resources</div>");
	    $wgOut->addHTML("</div><script type='text/javascript'>
	        $('#bodyContent h1').hide();
	        $('.module').click(function(){
                document.location = $(this).attr('href');
            });
            ".EducationModules::completionScript()."
	    </script>");
	}

    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        $selected = @($wgTitle->getText() == "AVOIDDashboard") ? "selected" : false;
        $GLOBALS['tabs']['Profile'] = TabUtils::createTab("Dashboard", "{$wgServer}{$wgScriptPath}/index.php/Special:AVOIDDashboard", $selected);
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle;
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            $selected = @($wgTitle->getText() == "AVOIDDashboard") ? "selected" : false;
            unset($tabs['Profile']['subtabs'][0]);
        }
        return true;
    }
    
    static function processPage($article, $outputDone, $pcache){
        global $wgOut, $wgUser, $wgRoles, $wgServer, $wgScriptPath, $wgTitle, $wgRoleValues, $config;
        $me = Person::newFromId($wgUser->getId());
        $nsText = ($article != null) ? str_replace("_", " ", $article->getTitle()->getNsText()) : "";
        if(isset($wgRoleValues[$nsText]) ||
           ($me->isLoggedIn() && $nsText == "" && $wgTitle->getText() == "Main Page")){
            redirect("{$wgServer}{$wgScriptPath}/index.php/Special:AVOIDDashboard");
        }
        return true;
    }
    
}

?>
