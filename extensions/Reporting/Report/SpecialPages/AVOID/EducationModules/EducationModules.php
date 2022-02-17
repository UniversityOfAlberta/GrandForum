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
        $wgOut->addHTML("<style>
            #modules {
                display: flex;
                justify-content: space-between;
                flex-wrap: wrap;
            }
            
            .module {
                width: calc(33.3% - 10px);
                border-radius: 10px;
                border: 2px solid #548ec9;
                overflow: hidden;
                cursor: pointer;
                margin-bottom: 10px;
                z-index: 1;
                transition: 0.25s;
            }
            
            .module:hover {
                -webkit-transform: scale(1.025);
                -moz-transform: scale(1.025);
                -ms-transform: scale(1.025);
                -o-transform: scale(1.025);
                z-index: 2;
                transition: 0.25s;
            }
            
            .module img {
                width: 100%;
            }
            
            .module-progress {
                height: 21px;
                text-align: center;
                font-weight: bold;
                border-top: 2px solid #548ec9;
                position: relative;
            }
            
            .module-progress-bar {
                position: absolute;
                top: 0;
                bottom: 0;
                left: 0;
                background: #7ac043;
                z-index: 0;
            }
            
            .module-progress-text {
                z-index: 1;
                position: absolute;
                width: 100%;
            }

        </style>");
        
        $wgOut->addHTML("<p>Click on the topic that you want to explore today. To begin any section of the module (labelled on the left), press the play button. You can control the playback speed with the toggle underneath. Supplement your learning by checking out the resource library, and apply your new knowledge by participating in the AVOID Frailty programs or finding opportunities in the Community Program Library.</p><div id='modules'>");
        foreach($modules as $module){
            $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=EducationModules/{$module->id}";
            $percent = rand(0,100);
            $wgOut->addHTML("<div id='module{$module->id}' class='module' href='{$url}'>
                <img src='{$wgServer}{$wgScriptPath}/EducationModules/{$module->id}/thumbnail.png'>
                <div class='module-progress'>
                    <div class='module-progress-bar'></div>
                    <div class='module-progress-text'></div>
                </div>
            </div>");
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
            $selected = @($wgTitle->getText() == "EducationModules") ? "selected" : false;
            $tabs["Modules"]['subtabs'][] = TabUtils::createSubTab("All Modules", "$wgServer$wgScriptPath/index.php/Special:EducationModules", $selected);
            
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "EducationModules/Activity")) ? "selected" : false;
            $tabs["Modules"]['subtabs'][] = TabUtils::createSubTab("Activity", "{$url}EducationModules/Activity", $selected);
            
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "EducationModules/DietAndNutrition")) ? "selected" : false;
            $tabs["Modules"]['subtabs'][] = TabUtils::createSubTab("Diet & Nutrition", "{$url}EducationModules/DietAndNutrition", $selected);
            
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "EducationModules/IngredientsForChange")) ? "selected" : false;
            $tabs["Modules"]['subtabs'][] = TabUtils::createSubTab("Ingredients for Change", "{$url}EducationModules/IngredientsForChange", $selected);
            
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "EducationModules/Interact")) ? "selected" : false;
            $tabs["Modules"]['subtabs'][] = TabUtils::createSubTab("Interact", "{$url}EducationModules/Interact", $selected);
            
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "EducationModules/OptimizeMedication")) ? "selected" : false;
            $tabs["Modules"]['subtabs'][] = TabUtils::createSubTab("Optimize Medication", "{$url}EducationModules/OptimizeMedication", $selected);
            
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "EducationModules/Vaccination")) ? "selected" : false;
            $tabs["Modules"]['subtabs'][] = TabUtils::createSubTab("Vaccination", "{$url}EducationModules/Vaccination", $selected);
        }
        return true;
    }
    
}

?>
