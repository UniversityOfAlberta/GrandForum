<?php

$wgHooks['UnknownAction'][] = 'PublicChordTab::getPublicChordData';

class PublicChordTab extends AbstractTab {
	
	function PublicChordTab(){
        parent::AbstractTab("Project Relations");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath;
	    $chord = new Chord("{$wgServer}{$wgScriptPath}/index.php?action=getPublicChordData");
	    $chord->height = 600;
	    $chord->width = 600;
	    $this->html = "<div><a class='button' onClick='$(\"#help{$chord->index}\").show();$(this).hide();'>Show Help</a>
	        <div id='help{$chord->index}' style='display:none;'>
	            <p>This visualization shows the relations between projects.  Each chord represents a person who is in both projects.</p>
	            <ul>
	                <li>To filter the results, you can check/uncheck the available roles, which will only show chords for people in the selected roles.</li>
	                <li>Using the date slider allows the chart to only show projects/people from the specified year.  This is useful to see the evolution of the network.</li>
	                <li>To change how the projects are sorted/coloured, select one of the options in the 'Sorting Options'.</li>
	            </ul>
	            <p>You can also highlight an individual project or theme either by hovering over the outer wedge in the chart, or by hovering over the theme in the legend.</p>
	        </div>
	    </div>";
	    $this->html .= $chord->show();
	}
	
	static function getPublicChordData($action, $article){
	    global $wgServer, $wgScriptPath, $config;
	    $me = Person::newFromWgUser();
	    $year = (isset($_GET['date'])) ? $_GET['date'] : date('Y');
	    if($action == "getPublicChordData"){
	        session_write_close();
	        $array = array();
            $people = Person::getAllPeopleDuring(null, $year.CYCLE_START_MONTH, $year.CYCLE_END_MONTH_ACTUAL);
            $projects = Project::getAllProjectsEver();
            foreach($projects as $key => $project){
                if($project->getChallenge()->getName() == "Not Specified" || 
                   $project->getType() == "Administrative"){
                    unset($projects[$key]);
                }
            }
            $sortedProjects = array();
            
            foreach($people as $key => $person){
                if(!$person->isRoleDuring(NI, $year.CYCLE_START_MONTH, $year.CYCLE_END_MONTH_ACTUAL)){
                    unset($people[$key]);
                    continue;
                }
            }
            
            if(!isset($_GET['sortBy']) || (isset($_GET['sortBy']) && $_GET['sortBy'] == 'theme')){
                foreach($projects as $project){
                    $sortedProjects[$project->getChallenge()->getId()."-".$project->getChallenge()->getName()][] = $project;
                }
            }
            else if(isset($_GET['sortBy']) && $_GET['sortBy'] == 'name'){
                foreach($projects as $project){
                    $sortedProjects[$project->getName()][] = $project;
                }
            }

            $colorHashs = array();
            $colors = array();
            $projects = array();
            ksort($sortedProjects);
            foreach($sortedProjects as $key => $sort){
                foreach($sort as $project){
                    $key = explode("-", $key);
                    $key = $key[count($key)-1];
                    $theme = $project->getChallenge();
                    $color = $theme->getColor();
                    $projects[] = $project;
                    $colorHashs[] = $key;
                    $colors[] = $color;
                }
            }
            
            $labels = array();
            $matrix = array();
            
            // Initialize
            foreach($projects as $k1 => $project){
                foreach($projects as $k2 => $p){
                    $matrix[$project->getId()][$p->getId()] = 0;
                }
            }
            
            $found = false;
            foreach($projects as $k1 => $project){
                if(array_sum($matrix[$project->getId()]) != 0){
                    $found = true;
                    break;
                }
            }
            if(!$found){
                foreach($projects as $k1 => $project){
                    $matrix[$project->getId()][$project->getId()] = 1;
                }
            }
            
            $newMatrix = array();
            foreach($matrix as $row){
                $newRow = array();
                foreach($row as $col){
                    $newRow[] = $col;
                }
                $newMatrix[] = $newRow;
            }
            $matrix = $newMatrix;
            
            $startYear = date('Y');
            foreach($projects as $project){
                $created = intval(substr($project->getCreated(), 0, 4));
                if($created < $startYear){
                    $startYear = intval($created);
                }
                $labels[] = $project->getName();
            }
            
            $dates = array();
            for($i=$startYear; $i <= date('Y'); $i++){
                if($i == date('Y')){
                    $dates[] = array('date' => $i, 'checked' => 'checked');
                }
                else{
                    $dates[] = array('date' => $i, 'checked' => '');
                }
            }
            
            $array['filterOptions'] = array();

            $array['dateOptions'] = $dates;
                                      
            $array['sortOptions'] = array(array('name' => $config->getValue('projectThemes'), 'value' => 'theme', 'checked' => 'checked'));
            $array['matrix'] = $matrix;
            $array['labels'] = $labels;
            $array['colorHashs'] = $colorHashs;
            $array['colors'] = $colors;

            header("Content-Type: application/json");
            echo json_encode($array);
            exit;
        }
        return true;
	}
}
?>
