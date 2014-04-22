<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['SpecialChord'] = 'SpecialChord';
$wgExtensionMessagesFiles['SpecialChord'] = $dir . 'SpecialChord.i18n.php';

$wgHooks['UnknownAction'][] = 'SpecialChord::getSpecialChordData';

function runSpecialChord($par) {
	SpecialChord::run($par);
}

class SpecialChord extends SpecialPage {

	function __construct() {
		wfLoadExtensionMessages('SpecialChord');
		SpecialPage::SpecialPage("SpecialChord", MANAGER.'+', true, 'runSpecialChord');
	}
	
	function run(){
	    global $wgOut, $wgServer, $wgScriptPath;
	    $chord = new Chord("{$wgServer}{$wgScriptPath}/index.php?action=getSpecialChordData");
	    $chord->height = 700;
	    $chord->width = 700;
	    $string = $chord->show();
	    $wgOut->addHTML($string);
	}
	
	static function getSpecialChordData($action, $article){
	    global $wgServer, $wgScriptPath;
	    $me = Person::newFromWgUser();
	    $year = (isset($_GET['date'])) ? $_GET['date'] : date('Y');
	    if($me->isRoleAtLeast(MANAGER) && $action == "getSpecialChordData"){
	        session_write_close();
	        $array = array();
            $people = Person::getAllPeopleDuring(null, $year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH_ACTUAL);
            $projects = Project::getAllProjectsEver();
            foreach($projects as $key => $project){
                if($project->getChallenge()->getName() == "Not Specified"){
                    unset($projects[$key]);
                }
                if($year <= 2013 && $project->getPhase() != 1){
                    unset($projects[$key]);
                }
                if($year >= 2014 && $project->getPhase() != 2){
                    unset($projects[$key]);
                }
            }
            $sortedProjects = array();
            
            foreach($people as $key => $person){
                if(!$person->isRoleDuring(CNI, $year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH_ACTUAL) && !$person->isRoleDuring(PNI, $year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH_ACTUAL) && !$person->isRoleDuring(AR, $year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH_ACTUAL)){
                    unset($people[$key]);
                    continue;
                }
                if(isset($_GET['noPNI']) && $person->isRoleDuring(PNI, $year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH_ACTUAL)){
                    unset($people[$key]);
                    continue;
                }
                if(isset($_GET['noCNI']) && $person->isRoleDuring(CNI, $year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH_ACTUAL)){
                    unset($people[$key]);
                    continue;
                }
                if(isset($_GET['noAR']) && $person->isRoleDuring(AR, $year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH_ACTUAL)){
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
                    if($person->isMemberOfDuring($project, $year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH_ACTUAL)){
                        foreach($projects as $p){
                            if($person->isMemberOfDuring($p, $year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH_ACTUAL) && isset($matrix[$p->getId()]) && $project->getId() != $p->getId()){
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
            
            foreach($projects as $project){
                $labels[] = $project->getName();
            }
            
            $startYear = date('Y');
            foreach(Project::getAllProjectsEver() as $project){
                $created = intval(substr($project->getCreated(), 0, 4));
                if($created < $startYear){
                    $startYear = intval($created);
                }
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
                                            array('name' => 'Show CNI Chords', 'param' => 'noCNI', 'checked' => 'checked'),
                                            array('name' => 'Show AR Chords', 'param' => 'noAR', 'checked' => 'checked'));

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
