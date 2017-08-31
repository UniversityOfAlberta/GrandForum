<?php

$wgHooks['UnknownAction'][] = 'PublicPersonChordTab::getPublicPersonChordData';

class PublicPersonChordTab extends AbstractTab {
	
	function PublicPersonChordTab(){
        parent::AbstractTab("Person Relations");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath;
	    $chord = new Chord("{$wgServer}{$wgScriptPath}/index.php?action=getPublicPersonChordData");
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
	    $this->html .= "<script type='text/javascript'>
        $('#publicVis').bind('tabsselect', function(event, ui) {
            if(ui.panel.id == 'person-relations'){
                onLoad{$chord->index}();
            }
        });
        </script><br />";
	}
	
	static function getPublicPersonChordData($action, $article){
	    global $wgServer, $wgScriptPath, $config;
	    $me = Person::newFromWgUser();
	    $year = (isset($_GET['date'])) ? $_GET['date'] : date('Y');
	    if($action == "getPublicPersonChordData"){
	        session_write_close();
	        $array = array();
            $people = Person::getAllPeopleDuring(null, $year.CYCLE_START_MONTH, $year.CYCLE_END_MONTH_ACTUAL);
            $sortedPeole = array();
            
            foreach($people as $key => $person){
                if(!$person->isRoleDuring(NI, $year.CYCLE_START_MONTH, $year.CYCLE_END_MONTH_ACTUAL) &&
                   !$person->isRoleDuring(PL,$year.CYCLE_START_MONTH, $year.CYCLE_END_MONTH_ACTUAL)){
                    unset($people[$key]);
                    continue;
                }
            }
            
            if(!isset($_GET['sortBy']) || (isset($_GET['sortBy']) && $_GET['sortBy'] == 'university')){
                foreach($people as $person){
                    $sortedPeople[$person->getUni()][] = $person;
                }
            }
            else if(isset($_GET['sortBy']) && $_GET['sortBy'] == 'department'){
                foreach($people as $person){
                    $sortedPeople[$person->getDepartment()][] = $person;
                }
            }

            $colorHashs = array();
            $colors = array();
            $people = array();
            ksort($sortedPeople);
            foreach($sortedPeople as $key => $sort){
                foreach($sort as $person){
                    $key = explode("-", $key);
                    $key = $key[count($key)-1];
                    $uni = University::newFromName($person->getUni());
                    $color = $uni->getColor();
                    $people[] = $person;
                    $colorHashs[] = $key;
                    $colors[] = $color;
                }
            }
            
            $labels = array();
            $matrix = array();
            $projects = Project::getAllProjects();
            // Initialize
            foreach($people as $k1 => $p1){
                foreach($people as $k2 => $p2){
                    $matrix[$p1->getId()][$p2->getId()] = 0;
                }
            }

            foreach($people as $k1 => $p1){
                foreach($people as $k1 => $p2){
                    foreach($projects as $project){
                        if($p1->getId() != $p2->getId() &&
                           $p1->isMemberOfDuring($project, $year.CYCLE_START_MONTH, $year.CYCLE_END_MONTH_ACTUAL) &&
                           $p2->isMemberOfDuring($project, $year.CYCLE_START_MONTH, $year.CYCLE_END_MONTH_ACTUAL)){
                            $matrix[$p1->getId()][$p2->getId()] += 1;
                        }
                    }
                }
            }
            
            $found = false;
            foreach($people as $k1 => $p1){
                if(array_sum($matrix[$p1->getId()]) != 0){
                    $found = true;
                    break;
                }
            }
            if(!$found){
                foreach($people as $k1 => $p1){
                    $matrix[$p1->getId()][$p1->getId()] = 1;
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
            foreach($people as $person){
                $created = intval(substr($person->getRegistration(), 0, 4));
                if($created < $startYear){
                    $startYear = intval($created);
                }
                $labels[] = $person->getNameForForms();
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
                                      
            $array['sortOptions'] = array(array('name' => 'Institution', 'value' => 'university', 'checked' => 'checked'),
                                          array('name' => 'Department', 'value' => 'department'));
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
