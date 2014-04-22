<?php

$wgHooks['UnknownAction'][] = 'PublicWordleTab::getPublicWordleData';

class PublicWordleTab extends AbstractTab {
	
	function PublicWordleTab(){
        parent::AbstractTab("Project Tag Cloud");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath;
	    $wordle = new Wordle("{$wgServer}{$wgScriptPath}/index.php?action=getPublicWordleData");
	    $wordle->width = 640;
        $wordle->height = 480;
	    $this->html = $wordle->show();
	    $this->html .= "<script type='text/javascript'>
            $('#publicVis').bind('tabsselect', function(event, ui) {
                if(ui.panel.id == 'project-tag-cloud'){
                    _.defer(function(){
	                    onLoad{$wordle->index}();
	                });
	            }
	        });
	    </script>";
	}
	
	static function getPublicWordleData($action, $article){
	    global $wgServer, $wgScriptPath;
	    if($action == "getPublicWordleData"){
	        $projects = Project::getAllProjects();
	        $description = "";
	        foreach($projects as $project){
	            $description .= $project->getDescription();
            }
            $data = Wordle::createDataFromText($description);
            header("Content-Type: application/json");
            echo json_encode($data);
            exit;
        }
        return true;
	}
}
?>
