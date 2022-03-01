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
	
	function compareTags($tags1, $tags2){
	    $found = 0;
	    foreach($tags1 as $tag1){
	        if(in_array($tag1, $tags2)){
	            $found++;
	        }
	    }
	    return $found;
	}
	
	function sort($objs, $tags){
	    usort($objs, function($a, $b) use($tags) {
            return ($this->compareTags($a->tags, $tags) < $this->compareTags($b->tags, $tags));
        });
        return $objs;
	}
	
	function execute($par){
	    global $wgOut, $wgServer, $wgScriptPath;
	    $dir = dirname(__FILE__) . '/';
	    $me = Person::newFromWgUser();
	    $_GET['id'] = $me->getId();
	    $tags = (new UserTagsAPI())->getTags($me->getId());
	    
	    $programs = json_decode(file_get_contents("{$dir}Programs/programs.json"));
	    $programs = $this->sort($programs, $tags);
	    
	    $modules = EducationModules::modulesJSON();
	    $modules = $this->sort($modules, $tags);
	    usort($modules, function($a, $b){
	        return (floor(EducationModules::completion($a->id)/100) > floor(EducationModules::completion($b->id)/100));
	    });
	    
	    
	    $wgOut->setPageTitle("AVOID Dashboard");
	    $wgOut->addHTML("<div class='modules'>");
	    
	    // Frailty Status
	    $api = new UserFrailtyIndexAPI();
	    $score = $api->getFrailtyScore($me->getId());
	    $frailty = "";
	    if($score >= 0 && $score <= 3){
	        $frailty = "Non-Frail";
	    }
	    else if($score > 3 && $score <= 8){
	        $frailty = "Vulnerable";
	    }
	    else if($score > 8 && $score <= 16){
	        $frailty = "Severely Frail";
	    }
	    $wgOut->addHTML("<div class='modules module-2cols'>
	                        <div class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'>Frailty Status</div>
	                        <div style='font-size: 4em; line-height: 1em; margin-top: 0.5em; margin-bottom: 0.75em; text-align: center; width: 100%;'>{$frailty}</div>
	                     </div>");
	    
	    // Upcoming Events
	    $events = Wiki::newFromTitle("UpcomingEvents");
	    $wgOut->addHTML("<div class='modules module-2cols'>
	                        <div class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'>Upcoming Events</div>
	                        {$events->getText()}
	                     </div>");
	    
	    // Education
	    $wgOut->addHTML("<div class='modules module-2cols'>");
	    $wgOut->addHTML("<div class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'>AVOID Education Modules <a style='float: right; font-size: 0.75em; color:white;' href='{$wgServer}{$wgScriptPath}/index.php/Special:EducationModules'>View All</a></div>");
	    $cols = 3;
        $n = 0;
        foreach($modules as $key => $module){
            if($key >= $cols){ break; }
            $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=EducationModules/{$module->id}";
            $percent = EducationModules::completion($module->id);
            $wgOut->addHTML("<div id='module{$module->id}' class='module module-{$cols}cols' href='{$url}'>
                <img src='{$wgServer}{$wgScriptPath}/EducationModules/{$module->id}/thumbnail.png' />
                <div class='module-progress'>
                    <div class='module-progress-bar' style='width:{$percent}%;'></div>
                    <div class='module-progress-text'>".number_format($percent)."% Complete</div>
                </div>
            </div>");
            $n++;
        }
        if($n % $cols > 0){
            for($i = 0; $i < $cols - ($n % $cols); $i++){
                $wgOut->addHTML("<div class='module-empty module-{$cols}cols'></div>");
            }
        }
	    $wgOut->addHTML("</div>");
	    
	    // Programs
	    $wgOut->addHTML("<div class='modules module-2cols'>");
        $wgOut->addHTML("<div class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'>AVOID Programs <a style='float: right; font-size: 0.75em; color:white;' href='{$wgServer}{$wgScriptPath}/index.php/Special:Programs'>View All</a></div>");
        $programs = json_decode(file_get_contents("{$dir}Programs/programs.json"));
        
        $cols = 3;
        $n = 0;
        foreach($programs as $key => $program){
            if($key >= $cols){ break; }
            $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=Programs/{$program->id}";
            $wgOut->addHTML("<div id='module{$program->id}' class='module module-{$cols}cols' href='{$url}'>
                <img src='{$wgServer}{$wgScriptPath}/EducationModules/{$program->id}.png' />
                <div class='module-progress-text' style='border-top: 2px solid #548ec9;'>{$program->title}</div>
            </div>");
            $n++;
        }
        if($n % $cols > 0){
            for($i = 0; $i < $cols - ($n % $cols); $i++){
                $wgOut->addHTML("<div class='module-empty module-{$cols}cols'></div>");
            }
        }
        $wgOut->addHTML("</div>");
	    
	    // Resources
	    $wgOut->addHTML("<div class='modules module-2cols'>
	                        <div class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'>AVOID Education Resources</div>
	                        Coming Soon...
	                     </div>");
	                     
	    // Community Program Library
	    $wgOut->addHTML("<div class='modules module-2cols'>
	                        <div class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'>Community Program Library <a style='float: right; font-size: 0.75em; color:white;' href='{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap'>View All</a></div>
	                        Coming Soon...
	                     </div>");
	    
	    $wgOut->addHTML("</div>
	    <script type='text/javascript'>
	        $('#bodyContent h1').hide();
	        $('.module').click(function(){
                document.location = $(this).attr('href');
            });
	    </script>");
	}

    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            $selected = @($wgTitle->getText() == "AVOIDDashboard") ? "selected" : false;
            $GLOBALS['tabs']['Profile'] = TabUtils::createTab("Dashboard", "{$wgServer}{$wgScriptPath}/index.php/Special:AVOIDDashboard", $selected);
        }
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
        if($me->isRole(ADMIN)){
            //return true;
        }
        if(isset($wgRoleValues[$nsText]) ||
           ($me->isLoggedIn() && $nsText == "" && $wgTitle->getText() == "Main Page")){
            $blob = new ReportBlob(BLOB_TEXT, YEAR, $me->getId(), 0);
	        $blob_address = ReportBlob::create_address("RP_AVOID", "SUBMIT", "SUBMITTED", 0);
	        $blob->load($blob_address);
	        $blob_data = $blob->getData();
	        $submitted = ($blob_data == "Submitted");
            if($submitted){
                redirect("{$wgServer}{$wgScriptPath}/index.php/Special:AVOIDDashboard");
            }
            else{
                redirect("{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=IntakeSurvey");
            }
        }
        return true;
    }
    
}

?>
