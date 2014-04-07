<?php

$wgHooks['UnknownAction'][] = 'PublicChordTab::getPublicChordData';

class PublicChordTab extends AbstractTab {
	
	function PublicChordTab(){
        parent::AbstractTab("Project Relations");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath;
	    $chord = new Chord("{$wgServer}{$wgScriptPath}/index.php?action=getPublicChordData");
	    $chord->height = 700;
	    $chord->width = 700;
	    $this->html = $chord->show();
	}
	
	static function getPublicChordData($action, $article){
	    global $wgServer, $wgScriptPath;
	    $me = Person::newFromWgUser();
	    $year = (isset($_GET['date'])) ? $_GET['date'] : date('Y');
	    if($action == "getPublicChordData"){
	        session_write_close();
	        $array = array();
            $people = Person::getAllPeopleDuring(null, $year.CYCLE_START_MONTH, $year.CYCLE_END_MONTH_ACTUAL);
            $projects = Project::getAllProjectsEver();
            $sortedProjects = array();
            
            foreach($people as $key => $person){
                if(!$person->isRoleDuring(CNI, $year.CYCLE_START_MONTH, $year.CYCLE_END_MONTH_ACTUAL) && !$person->isRoleDuring(PNI, $year.CYCLE_START_MONTH, $year.CYCLE_END_MONTH_ACTUAL) && !$person->isRoleDuring(AR, $year.CYCLE_START_MONTH, $year.CYCLE_END_MONTH_ACTUAL)){
                    unset($people[$key]);
                    continue;
                }
                if(isset($_GET['noPNI']) && $person->isRoleDuring(PNI, $year.CYCLE_START_MONTH, $year.CYCLE_END_MONTH_ACTUAL)){
                    unset($people[$key]);
                    continue;
                }
                if(!isset($_GET['showCNI']) && $person->isRoleDuring(CNI, $year.CYCLE_START_MONTH, $year.CYCLE_END_MONTH_ACTUAL)){
                    unset($people[$key]);
                    continue;
                }
                if(!isset($_GET['showAR']) && $person->isRoleDuring(AR, $year.CYCLE_START_MONTH, $year.CYCLE_END_MONTH_ACTUAL)){
                    unset($people[$key]);
                    continue;
                }
            }
            
            if(!isset($_GET['sortBy']) || (isset($_GET['sortBy']) && $_GET['sortBy'] == 'theme')){
                foreach($projects as $project){
                    $sortedProjects[$project->getChallenge()->getName()][] = $project;
                }
            }
            else if(isset($_GET['sortBy']) && $_GET['sortBy'] == 'name'){
                foreach($projects as $project){
                    $sortedProjects[$project->getName()][] = $project;
                }
            }

            $colorHashs = array();
            $projects = array();
            ksort($sortedProjects);
            foreach($sortedProjects as $key => $sort){
                foreach($sort as $project){
                    $projects[] = $project;
                    $colorHashs[] = $key;
                }
            }
            
            $labels = array();
            $matrix = array();
            $colors = array();
            
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
                $created = substr($project->getCreated(), 0, 4);
                if($created < $startYear){
                    $startYear = $created;
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
            
            $array['filterOptions'] = array(array('name' => 'Show PNI Chords', 'param' => 'noPNI', 'checked' => 'checked'),
                                            array('name' => 'Show CNI Chords', 'param' => 'showCNI', 'checked' => 'checked', 'inverted' => true),
                                            array('name' => 'Show AR Chords', 'param' => 'showAR', 'checked' => 'checked', 'inverted' => true));

            $array['dateOptions'] = $dates;
                                      
            $array['sortOptions'] = array(array('name' => 'Theme', 'value' => 'theme', 'checked' => 'checked'),
                                          array('name' => 'Project Name', 'value' => 'name', 'checked' => ''));
            $array['matrix'] = $matrix;
            $array['labels'] = $labels;
            $array['colorHashs'] = $colorHashs;

            header("Content-Type: application/json");
            echo json_encode($array);
            exit;
        }
        return true;
	}
}
?>
