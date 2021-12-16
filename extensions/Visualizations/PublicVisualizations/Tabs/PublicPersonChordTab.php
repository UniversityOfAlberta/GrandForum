<?php

UnknownAction::createAction('PublicPersonChordTab::getPublicPersonChordData');

class PublicPersonChordTab extends AbstractTab {
	
	function __construct(){
        parent::AbstractTab("Person Relations");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath;
	    $chord = new Chord("{$wgServer}{$wgScriptPath}/index.php?action=getPublicPersonChordData");
	    $chord->height = 600;
	    $chord->width = 600;
	    $this->html = "<div><a class='button' onClick='$(\"#help{$chord->index}\").show();$(this).hide();'>Show Help</a>
	        <div id='help{$chord->index}' style='display:none;'>
	            <p>This visualization shows the relations between people.  Each chord represents a common project between the two people.</p>
	            <ul>
	                <li>Using the date slider allows the chart to only show people from the specified year.  This is useful to see the evolution of the network.</li>
	                <li>To change how the people are sorted/coloured, select one of the options in the 'Sorting Options'.</li>
	            </ul>
	            <p>You can also highlight an individual person either by hovering over the outer wedge in the chart, or by hovering over the sorting category in the legend.</p>
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
            $presetColors = array("#1f78b4",
                                  "#33a02c",
                                  "#e31a1c",
                                  "#ff7f00",
                                  "#804C96",
                                  "#00AAFF",
                                  "#c51b7d",
                                  "#8c510a",
                                  "#f781bf",
                                  "#ffcc33",
                                  "#01665e",
                                  "#cab2d6",
                                  "#8dd3c7",
                                  "#ffffb3",
                                  "#bebada",
                                  "#fb8072",
                                  "#80b1d3",
                                  "#fdb462",
                                  "#b3de69",
                                  "#fccde5",
                                  "#d9d9d9",
                                  "#fbb4ae",
                                  "#b3cde3",
                                  "#ccebc5",
                                  "#decbe4",
                                  "#fed9a6",
                                  "#ffffcc",
                                  "#e5d8bd",
                                  "#fddaec",
                                  "#f2f2f2",
                                  "#a6cee3",
                                  "#b2df8a",
                                  "#fb9a99",
                                  "#fdbf6f");
            $i = 0;
            foreach($sortedPeople as $key => $sort){
                if(!isset($presetColors[$i])){
                    $i = 0;
                }
                $color = $presetColors[$i];
                foreach($sort as $person){
                    $key = explode("-", $key);
                    $key = $key[count($key)-1];
                    //$uni = University::newFromName($person->getUni());
                    //$color = $uni->getColor();
                    $people[] = $person;
                    $colorHashs[] = $key;
                    $colors[] = $color;
                }
                $i++;
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
                foreach($p1->getProjectsDuring($year.CYCLE_START_MONTH, $year.CYCLE_END_MONTH_ACTUAL) as $project){
                    foreach($people as $k2 => $p2){
                        if($p1->getId() != $p2->getId() &&
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
                                          array('name' => $config->getValue('deptsTerm'), 'value' => 'department'));
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
