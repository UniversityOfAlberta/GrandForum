<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['EducationModules'] = 'EducationModules'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['EducationModules'] = $dir . 'EducationModules.i18n.php';
$wgSpecialPageGroups['EducationModules'] = 'reporting-tools';

$wgHooks['TopLevelTabs'][] = 'EducationModules::createTab';
$wgHooks['SubLevelTabs'][] = 'EducationModules::createSubTabs';

class EducationModules extends SpecialPage {
    
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
        $json = file_get_contents("{$dir}modules.json");
        $modules = json_decode($json);
        $cols = 3;

        $wgOut->addHTML("<p>Click on the topic that you want to explore today. To begin any section of the module (labelled on the left), press the play button. You can control the playback speed with the toggle underneath. Supplement your learning by checking out the resource library, and apply your new knowledge by participating in the AVOID Frailty programs or finding opportunities in the Community Program Library.</p><div id='modules'>");
        $n = 0;
        foreach($modules as $module){
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
        $wgOut->addHTML("</div>
        <script type='text/javascript'>
            var modules = {$json};
            $('.module').click(function(){
                document.location = $(this).attr('href');
            });
            _.each(modules, function(module){
                var dataCollection = new DataCollection();
                dataCollection.init(me.get('id'), module.id);
                dataCollection.ready().always(function(){
                    var completed = 0;
                    for(i = 1; i <= module.videos; i++){
                        if(Math.round(dataCollection.sum('video' + i + 'Watched')/_.size(dataCollection.getField('video' + i + 'Watched', [0]))*100) > 90){
                            completed++;
                        }
                    }
                    for(i = 1; i <= module.questions; i++){
                        if(!_.isEmpty(dataCollection.getField('q' + i))){
                            completed += 1/module.questions;
                        }
                    }
                    var percent = (completed / (module.videos + 1))*100;
                    $('#module' + module.id + ' .module-progress-bar').css('width', percent + '%');
                    $('#module' + module.id + ' .module-progress-text').text(percent.toFixed(0) + '% Complete');
                });
            });
        </script>");
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
        return true;
    }
    
}

?>
