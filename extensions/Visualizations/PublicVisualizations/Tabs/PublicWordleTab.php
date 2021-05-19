<?php

$wgHooks['UnknownAction'][] = 'PublicWordleTab::getPublicWordleData';

class PublicWordleTab extends AbstractTab {
	
	function PublicWordleTab(){
        parent::AbstractTab("Project Tag Cloud");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath;
	    $wordle = new Wordle("{$wgServer}{$wgScriptPath}/index.php?action=getPublicWordleData");
	    $wordle->width = "100%";
        $wordle->height = 480;
        $this->html = "<div><a class='button' onClick='$(\"#help{$wordle->index}\").show();$(this).hide();'>Show Help</a>
	        <div id='help{$wordle->index}' style='display:none;'>
	            <p>This visualization shows the most used keywords in the project and theme descriptions.  The more times the word is used, the larger it appears in the tag cloud.</p>
	        </div>
	    </div>";
	    $this->html .= $wordle->show();
	    $this->html .= "<script type='text/javascript'>
	        var nTimesLoadedProjectWordle = 0;
            $('#publicVis').bind('tabsselect', function(event, ui) {
                if(ui.panel.id == 'project-tag-cloud'){
                    _.defer(function(){
                        if(nTimesLoadedProjectWordle == 0){
	                        onLoad{$wordle->index}();
	                        nTimesLoadedProjectWordle++;
	                    }
	                });
	            }
	        });
	    </script>";
	}
	
	static function getPublicWordleData($action, $article){
	    global $wgServer, $wgScriptPath;
	    if($action == "getPublicWordleData"){
	        $projects = Project::getAllProjects();
	        $themes = Theme::getAllThemes();
	        $description = array();
	        foreach($projects as $project){
	            $description[] = strip_tags($project->getDescription());
	            $description[] = strip_tags($project->getFullName());
            }
            foreach($themes as $theme){
                $description[] = strip_tags($theme->getName());
                $description[] = strip_tags($theme->getDescription());
            }
            $data = Wordle::createDataFromText(implode(" ", $description));
            header("Content-Type: application/json");
            echo json_encode($data);
            exit;
        }
        return true;
	}
}
?>
