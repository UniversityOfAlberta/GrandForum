<?php

UnknownAction::createAction('PublicWordleTab::getPublicWordleData');

class PublicWordleTab extends AbstractTab {
	
	function __construct(){
        parent::AbstractTab("Project Tag Cloud");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath;
	    $wordle = new Wordle("{$wgServer}{$wgScriptPath}/index.php?action=getPublicWordleData");
	    $wordle->width = "100%";
        $wordle->height = 480;
	    $this->html .= $wordle->show();
	    $projects = Project::getAllProjects();
	    $this->html .= "<style>
	        #vis{$wordle->index} text:hover {
	            cursor: pointer;
	            stroke: black;
	            stroke-width: 0.5px;
	            fill-opacity: 0.5;
	        }
	    </style>";
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
	        
	        $(document).on('click', '#vis{$wordle->index} text', function(){
	            var word = $(this).text().toLowerCase();
	            $('#resultsContainer{$wordle->index}').show();
	            $('#resultsContainer{$wordle->index} #results li').each(function(){
	                var text = $(this).text().toLowerCase();
	                if(text.indexOf(word) != -1){
	                    $(this).slideDown();
	                }
	                else{
	                    $(this).slideUp();
	                }
	            });
	            document.location = '#resultsContainer{$wordle->index}';
	        });
	    </script>
	    <div id='resultsContainer{$wordle->index}' name='resultsContainer{$wordle->index}' style='display:none;'>
	        <h3>Projects</h3>
	        <div id='results'>
	            <ul>";
	    foreach($projects as $project){
	        $this->html .= "<li style='display:none;'><a href='{$project->getUrl()}'>{$project->getName()} - {$project->getFullName()}</a> <span style='display:none;'>{$project->getDescription()}</span></li>\n";
	    }
	    $this->html .= "</ul>
	        </div>
	    </div>
	    <p>This visualization shows the most used keywords in the project descriptions.  The more times the word is used, the larger it appears in the tag cloud.  Click a word to show the projects that contain that word in its title or description.</p>";
	}
	
	static function getPublicWordleData($action, $article){
	    global $wgServer, $wgScriptPath;
	    if($action == "getPublicWordleData"){
	        $projects = Project::getAllProjects();
	        $description = array();
	        foreach($projects as $project){
	            $description[] = strip_tags($project->getDescription());
	            $description[] = strip_tags($project->getFullName());
            }
            $data = array();
            foreach(Wordle::createDataFromText(implode(" ", $description)) as $word){
                if($word['freq'] >= 3){
                    $data[] = $word;
                }
            }
            $data = array_slice($data, 0, 300);
            header("Content-Type: application/json");
            echo json_encode($data);
            exit;
        }
        return true;
	}
}
?>
