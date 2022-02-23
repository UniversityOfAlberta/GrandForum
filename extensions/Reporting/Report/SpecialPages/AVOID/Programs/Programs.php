<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Programs'] = 'Programs'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Programs'] = $dir . 'Programs.i18n.php';
$wgSpecialPageGroups['Programs'] = 'reporting-tools';

$wgHooks['TopLevelTabs'][] = 'Programs::createTab';
$wgHooks['SubLevelTabs'][] = 'Programs::createSubTabs';

class Programs extends SpecialPage {
    
    function __construct() {
		SpecialPage::__construct("Programs", null, false);
	}
	
	function userCanExecute($user){
	    return ($user->isLoggedIn());
	}
	
	function execute($par){
        global $wgOut, $wgServer, $wgScriptPath;
        $dir = dirname(__FILE__) . '/';
        $wgOut->setPageTitle("AVOID Programs");
        $json = file_get_contents("{$dir}programs.json");
        $programs = json_decode($json);
        
        $cols = 4;
        $wgOut->addHTML("<p>The AVOID Frailty programs are designed to keep you connected with your peers and community as well as support the development of healthy behaviour. You can choose to participate as a volunteer or find the help you need to be empowered to take control of your health. Click on the program that you are interested in and sign up using the orange link at the bottom of the page.</p><div class='modules'>");
        $n = 0;
        foreach($programs as $program){
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
        $wgOut->addHTML("</div>
        <script type='text/javascript'>
            var programs = {$json};
            $('.module').click(function(){
                document.location = $(this).attr('href');
            });
        </script>");
    }

    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        $tabs["Programs"] = TabUtils::createTab("AVOID Programs");
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";
        if($person->isLoggedIn()){
            $dir = dirname(__FILE__) . '/';
            $json = file_get_contents("{$dir}programs.json");
            $programs = json_decode($json);
            
            $selected = @($wgTitle->getText() == "Programs") ? "selected" : false;
            $tabs["Programs"]['subtabs'][] = TabUtils::createSubTab("All Programs", "$wgServer$wgScriptPath/index.php/Special:Programs", $selected);
            
            foreach($programs as $program){
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "Programs/{$program->id}")) ? "selected" : false;
                $tabs["Programs"]['subtabs'][] = TabUtils::createSubTab("{$program->title}", "{$url}Programs/{$program->id}", $selected);
            }
        }
        return true;
    }
    
}

?>
