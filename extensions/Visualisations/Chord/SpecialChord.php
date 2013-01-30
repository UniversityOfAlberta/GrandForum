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
	    $year = (isset($_GET['date'])) ? $_GET['date'] : REPORTING_YEAR;
	    if($action == "getSpecialChordData" && $me->isRoleAtLeast(MANAGER)){
	        $array = array();
            $people = Person::getAllPeopleDuring(null, $year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH);
            $sortedPeople = array();
            
            foreach($people as $key => $person){
                if(!$person->isRoleDuring(CNI, $year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH) && !$person->isRoleDuring(PNI, $year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH) && !$person->isRoleDuring(AR, $year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH)){
                    unset($people[$key]);
                    continue;
                }
                if(isset($_GET['noPNI']) && $person->isRoleDuring(PNI, $year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH)){
                    unset($people[$key]);
                    continue;
                }
                if(!isset($_GET['showCNI']) && $person->isRoleDuring(CNI, $year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH)){
                    unset($people[$key]);
                    continue;
                }
                if(!isset($_GET['showAR']) && $person->isRoleDuring(AR, $year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH)){
                    unset($people[$key]);
                    continue;
                }
                
                if(!isset($_GET['sortBy']) || (isset($_GET['sortBy']) && $_GET['sortBy'] == 'uni')){
                    $university = $person->getUniversityDuring($year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH);
                    if($university['university'] != ''){
                        $sortedPeople[$university['university']][] = $person;
                    }
                    else{
                        $sortedPeople['Unknown'][] = $person;
                    }
                }
                else if($_GET['sortBy'] == 'dept'){
                    $university = $person->getUniversityDuring($year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH);
                    if($university['department'] != ''){
                        $sortedPeople[$university['department']][] = $person;
                    }
                    else{
                        $sortedPeople['Unknown'][] = $person;
                    }
                }
                else if($_GET['sortBy'] == 'fund'){
                    $agency = $person->getPrimaryFundingAgency();
                    $sortedPeople[$agency][] = $person;
                }
                else if($_GET['sortBy'] == 'proj_req'){
                    $budget = $person->getRequestedBudget($year);
                    if($budget != null){
                        $projBudget = $budget->copy()->rasterize()->select(V_PROJ)->where(V_PROJ);
                        $rowBudget = $budget->copy()->rasterize()->select(V_PROJ)->where(COL_TOTAL);
                        
                        $largest = 0;
                        $largestProj = '';
                        foreach($rowBudget->xls as $nRow => $row){
                            foreach($row as $nCol => $value){
                                if($value->getValue() > $largest){
                                    $largest = $value->getValue();
                                    $largestProj = $projBudget->copy()->limitCols($nCol-1, 1)->toString();
                                }
                            }
                        }
                        $sortedPeople[$largestProj][] = $person;
                    }
                    else{
                        $sortedPeople['No Project'][] = $person;
                    }
                }
                else if($_GET['sortBy'] == 'proj_alloc'){
                    $budget = $person->getAllocatedBudget($year-1);
                    if($budget != null){
                        $projBudget = $budget->copy()->rasterize()->select(V_PROJ)->where(V_PROJ);
                        $rowBudget = $budget->copy()->rasterize()->select(V_PROJ)->where(COL_TOTAL);
                        
                        $largest = 0;
                        $largestProj = '';
                        foreach($rowBudget->xls as $nRow => $row){
                            foreach($row as $nCol => $value){
                                if($value->getValue() > $largest){
                                    $largest = $value->getValue();
                                    $largestProj = $projBudget->copy()->limitCols($nCol-1, 1)->toString();
                                }
                            }
                        }
                        $sortedPeople[$largestProj][] = $person;
                    }
                    else{
                        $sortedPeople['No Project'][] = $person;
                    }
                }
                else if($_GET['sortBy'] == 'proj_both'){
                    $budget = $person->getRequestedBudget($year);
                    if($budget == null){
                        $budget = $person->getAllocatedBudget($year-1);
                    }
                    if($budget != null){
                        $projBudget = $budget->copy()->rasterize()->select(V_PROJ)->where(V_PROJ);
                        $rowBudget = $budget->copy()->rasterize()->select(V_PROJ)->where(COL_TOTAL);
                        
                        $largest = 0;
                        $largestProj = '';
                        foreach($rowBudget->xls as $nRow => $row){
                            foreach($row as $nCol => $value){
                                if($value->getValue() > $largest){
                                    $largest = $value->getValue();
                                    $largestProj = $projBudget->copy()->limitCols($nCol-1, 1)->toString();
                                }
                            }
                        }
                        $sortedPeople[$largestProj][] = $person;
                    }
                    else{
                        $sortedPeople['No Project'][] = $person;
                    }
                }
                else if($_GET['sortBy'] == 'name'){
                    $sortedPeople[$person->getReversedName()][] = $person;
                }
            }

            $colorHashs = array();
            $people = array();
            ksort($sortedPeople);
            foreach($sortedPeople as $key => $sort){
                foreach($sort as $person){
                    $people[] = $person;
                    $colorHashs[] = $key;
                }
            }
            
            $labels = array();
            $matrix = array();
            $colors = array();
            
            // Initialize
            foreach($people as $k1 => $person){
                foreach($people as $k2 => $p){
                    $matrix[$person->getId()][$p->getId()] = 0;
                }
            }
            
            if(!isset($_GET['noCoAuthorship'])){
                foreach($people as $k1 => $person){
                    $papers = $person->getPapersAuthored("all", $year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH, false);
                    foreach($papers as $paper){
                        foreach($paper->getAuthors() as $p){
                            if(isset($matrix[$p->getId()]) && $person->getId() != $p->getId()){
                                $matrix[$person->getId()][$p->getId()] += 1;
                            }
                        }
                    }
                }
            }
            
            if(!isset($_GET['noRelations'])){
                foreach($people as $k1 => $person){
                    foreach($people as $k2 => $p){
                        $relations = $person->getRelationsDuring(WORKS_WITH, $year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH);
                        if(count($relations) > 0){
                            foreach($relations as $relation){
                                if($relation instanceof Relationship && $relation->getUser2()->getId() == $p->getId()){
                                    $matrix[$person->getId()][$p->getId()] += 5;
                                }
                            }
                        }
                    }
                }
            }
            
            $found = false;
            foreach($people as $k1 => $person){
                if(array_sum($matrix[$person->getId()]) != 0){
                    $found = true;
                    break;
                }
            }
            if(!$found){
                foreach($people as $k1 => $person){
                    $matrix[$person->getId()][$person->getId()] = 1;
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
            
            foreach($people as $person){
                $labels[] = $person->getReversedName();
            }
            
            $dates = array();
            for($i=2010; $i <= REPORTING_YEAR; $i++){
                if($i == REPORTING_YEAR){
                    $dates[] = array('date' => $i, 'checked' => 'checked');
                }
                else{
                    $dates[] = array('date' => $i, 'checked' => '');
                }
            }
            
            $array['filterOptions'] = array(array('name' => 'Show Co-Authorship', 'param' => 'noCoAuthorship', 'checked' => 'checked'),
                                            array('name' => 'Show Relationships', 'param' => 'noRelations', 'checked' => 'checked'),
                                            array('name' => 'Show PNIs', 'param' => 'noPNI', 'checked' => 'checked'),
                                            array('name' => 'Show CNIs', 'param' => 'showCNI', 'checked' => '', 'inverted' => true),
                                            array('name' => 'Show ARs', 'param' => 'showAR', 'checked' => '', 'inverted' => true));

            $array['dateOptions'] = $dates;
                                      
            $array['sortOptions'] = array(array('name' => 'University', 'value' => 'uni', 'checked' => 'checked'),
                                          array('name' => 'Department', 'value' => 'dept', 'checked' => ''),
                                          array('name' => 'Primary Funding Agency', 'value' => 'fund', 'checked' => ''),
                                          array('name' => 'Primary Project (Requested Budget)', 'value' => 'proj_req', 'checked' => ''),
                                          array('name' => 'Primary Project (Allocated Budget)', 'value' => 'proj_alloc', 'checked' => ''),
                                          array('name' => 'Primary Project (RequestedBudget OR Allocated Budget)', 'value' => 'proj_both', 'checked' => ''),
                                          array('name' => 'Last Name', 'value' => 'name', 'checked' => ''));
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
