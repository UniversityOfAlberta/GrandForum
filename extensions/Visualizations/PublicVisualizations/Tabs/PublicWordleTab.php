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
	    </script>
	    <h3>Help</h3>
	    <p>This visualization shows the most used keywords in the project and theme descriptions.  The more times the word is used, the larger it appears in the tag cloud.</p>";
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
            $data = array_splice($data, 0, 600);
            header("Content-Type: application/json");
            echo json_encode($data);
            exit;
        }
        return true;
	}
}
?>
