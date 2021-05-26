<?php

$wgHooks['UnknownAction'][] = 'PublicChordTab::getPublicChordData';

class PublicChordTab extends AbstractTab {
	
	function PublicChordTab(){
        parent::AbstractTab("Project Relations");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath;
	    $sortBy = (isset($_GET['sortBy'])) ? "&sortBy={$_GET['sortBy']}" : "";
	    $chord = new Chord("{$wgServer}{$wgScriptPath}/index.php?action=getPublicChordData{$sortBy}");
	    $chord->height = 600;
	    $chord->width = 600;
	    $this->html .= $chord->show();
	    $this->html .= "<script type='text/javascript'>
        $('#publicVis').bind('tabsselect', function(event, ui) {
            if(ui.panel.id == 'project-relations'){
                onLoad{$chord->index}();
            }
        });
        </script>
        <p>This visualization shows the relations between projects.  Each chord represents a person who is in both projects.</p>
        <ul>
            <li>Using the date slider allows the chart to only show projects from the specified year.  This is useful to see the evolution of the network.</li>
            <li>To change how the projects are sorted/coloured, select one of the options in the 'Sorting Options'.</li>
        </ul>
        <p>You can also highlight an individual project or theme either by hovering over the outer wedge in the chart, or by hovering over the sorting category in the legend.</p>";
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
                   $project->getChallenge()->getName() == "" ||
                   $project->getType() == "Administrative"){
                    unset($projects[$key]);
                }
            }
            $sortedProjects = array();
            
            foreach($people as $key => $person){
                if(!$person->isRoleDuring(NI, $year.CYCLE_START_MONTH, $year.CYCLE_END_MONTH_ACTUAL) &&
                   !$person->isRoleDuring(PL,$year.CYCLE_START_MONTH, $year.CYCLE_END_MONTH_ACTUAL)){
                    unset($people[$key]);
                    continue;
                }
            }
            
            if(($config->getValue('networkName') == "AI4Society") && (!isset($_GET['sortBy']) || (isset($_GET['sortBy']) && $_GET['sortBy'] == 'activity'))){
                foreach($projects as $project){
                    foreach($project->getChallenges() as $theme){
                        if(strstr($theme->getName(), "Activity - ") !== false){
                            $sortedProjects[$theme->getId()."-".$theme->getName()][] = $project;
                            break;
                        }
                    }
                }
            }
            else if(!isset($_GET['sortBy']) || (isset($_GET['sortBy']) && $_GET['sortBy'] == 'theme')){
                foreach($projects as $project){
                    if($config->getValue('networkName') == "AI4Society"){
                        foreach($project->getChallenges() as $theme){
                            if(strstr($theme->getName(), "Theme - ") !== false){
                                $sortedProjects[$theme->getId()."-".$theme->getName()][] = $project;
                                break;
                            }
                        }
                    }
                    else{
                        $sortedProjects[$project->getChallenge()->getId()."-".$project->getChallenge()->getName()][] = $project;
                    }
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
                $key = explode("-", $key);
                $id = $key[0];
                $key = $key[count($key)-1];
                foreach($sort as $project){
                    $theme = Theme::newFromId($id);
                    $color = $theme->getColor();
                    $projects[] = $project;
                    $colorHashs[] = $key;
                    $colors[] = $color;
                }
            }
            
            $labels = array();
            $matrix = array();
            $chordLabels = array();
            
            // Initialize
            foreach($projects as $k1 => $project){
                foreach($projects as $k2 => $p){
                    $matrix[$project->getId()][$p->getId()] = 0;
                }
            }

            foreach($people as $k1 => $person){
                foreach($projects as $project){
                    if($person->isMemberOfDuring($project, $year.CYCLE_START_MONTH, $year.CYCLE_END_MONTH_ACTUAL)){
                        foreach($projects as $p){
                            if($person->isMemberOfDuring($p, $year.CYCLE_START_MONTH, $year.CYCLE_END_MONTH_ACTUAL) && isset($matrix[$p->getId()]) && $project->getId() != $p->getId()){
                                $matrix[$project->getId()][$p->getId()] += 1;
                                $chordLabels[$project->getId()][$p->getId()][$person->getId()] = $person->getNameForForms();
                            }
                        }
                    }
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
            $newChordLabels = array();
            foreach($matrix as $i => $row){
                $newRow = array();
                $newChordRow = array();
                foreach($row as $j => $col){
                    $newRow[] = $col;
                    $newChordRow[] = @implode("<br />", $chordLabels[$i][$j]);
                }
                $newMatrix[] = $newRow;
                $newChordLabels[] = $newChordRow;
            }
            $matrix = $newMatrix;
            $chordLabels = $newChordLabels;
            
            $startYear = date('Y');
            foreach($projects as $project){
                $created = intval(substr($project->getCreated(), 0, 4));
                if($created < $startYear){
                    $startYear = intval($created);
                }
                $labels[] = "{$project->getName()} - {$project->getFullName()}";
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
            
            if($config->getValue('networkName') == "AI4Society"){
                $array['sortOptions'] = array(array('name' => "Activity", 'value' => 'activity', 'checked' => (!isset($_GET['sortBy']) || $_GET['sortBy'] == 'activity') ? 'checked' : ""),
                                              array('name' => "Theme", 'value' => 'theme', 'checked' => (@$_GET['sortBy'] == 'theme') ? 'checked' : ""));
            }
            else{
                $array['sortOptions'] = array(array('name' => $config->getValue('projectThemes'), 'value' => 'theme', 'checked' => 'checked'));
            }
            $array['matrix'] = $matrix;
            $array['labels'] = $labels;
            $array['colorHashs'] = $colorHashs;
            $array['colors'] = $colors;
            $array['chordLabels'] = $chordLabels;

            header("Content-Type: application/json");
            echo json_encode($array);
            exit;
        }
        return true;
	}
}
?>
