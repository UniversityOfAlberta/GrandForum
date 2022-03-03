<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['EducationModules'] = 'EducationModules'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['EducationModules'] = $dir . 'EducationModules.i18n.php';
$wgSpecialPageGroups['EducationModules'] = 'reporting-tools';

$wgHooks['TopLevelTabs'][] = 'EducationModules::createTab';
$wgHooks['SubLevelTabs'][] = 'EducationModules::createSubTabs';

class EducationModules extends SpecialPage {
    
    static $json = "";
    
    function __construct() {
		SpecialPage::__construct("EducationModules", null, false);
	}
	
	function userCanExecute($user){
	    return ($user->isLoggedIn());
	}
	
	function execute($par){
        global $wgOut, $wgServer, $wgScriptPath;
        $dir = dirname(__FILE__) . '/';
        $wgOut->setPageTitle("AVOID Education Modules");
        $modules = self::modulesJSON();
        $cols = 3;

        $wgOut->addHTML("<p>Click on the topic that you want to explore today. To begin any section of the module (labelled on the left), press the play button. You can control the playback speed with the toggle underneath. Supplement your learning by checking out the resource library, and apply your new knowledge by participating in the AVOID Frailty programs or finding opportunities in the Community Program Library.</p><div class='modules'>");
        $n = 0;
        foreach($modules as $module){
            $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=EducationModules/{$module->id}";
            $percent = self::completion($module->id);
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
        $wgOut->addHTML("</div>
        <script type='text/javascript'>
            $('.module').click(function(){
                document.location = $(this).attr('href');
            });
        </script>");
    }
    
    static function modulesJSON(){
        if(self::$json == null){
            $dir = dirname(__FILE__) . '/';
            self::$json = json_decode(file_get_contents("{$dir}modules.json"));
        }
        return self::$json;
    }
    
    static function completion($page){
        $json = self::modulesJSON();
        
        $me = Person::newFromWgUser();
        $data = DataCollection::newFromUserId($me->getId(), $page);
        
        $completed = 0;
        foreach($json as $module){
            if($module->id == $page){
                for($i = 1; $i <= $module->videos; $i++){
                    if(round($data->sum("video{$i}Watched")/count($data->getField("video{$i}Watched", [0]))*100) > 90){
                        $completed++;
                    }
                }
                for($i = 1; $i <= $module->questions; $i++){
                    if(!empty($data->getField("q{$i}"))){
                        $completed += 1/$module->questions;
                    }
                }
                $percent = ($completed / ($module->videos + 1))*100;
                return round($percent);
            }
        }
        return 0;
    }

    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        $tabs["Modules"] = TabUtils::createTab("AVOID Education");
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";
        if($person->isLoggedIn()){
            if(AVOIDDashboard::hasSubmittedSurvey()){
                $dir = dirname(__FILE__) . '/';
                $json = file_get_contents("{$dir}modules.json");
                $modules = json_decode($json);
                
                $selected = @($wgTitle->getText() == "EducationModules") ? "selected" : false;
                $tabs["Modules"]['subtabs'][] = TabUtils::createSubTab("All Modules", "$wgServer$wgScriptPath/index.php/Special:EducationModules", $selected);
                
                foreach($modules as $module){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "EducationModules/{$module->id}")) ? "selected" : false;
                    $tabs["Modules"]['subtabs'][] = TabUtils::createSubTab("{$module->title}", "{$url}EducationModules/{$module->id}", $selected);
                }
            }
        }
        return true;
    }
    
}

?>
