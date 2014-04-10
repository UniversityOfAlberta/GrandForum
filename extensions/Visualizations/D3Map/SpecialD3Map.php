<?php
$dir = dirname(__FILE__) . '/';
$wgSpecialPages['SpecialD3Map'] = 'SpecialD3Map';
$wgExtensionMessagesFiles['SpecialD3Map'] = $dir . 'SpecialD3Map.i18n.php';

$wgHooks['UnknownAction'][] = 'SpecialD3Map::getSpecialD3MapData';

function runSpecialD3Map($par) {
	SpecialD3Map::run($par);
}

class SpecialD3Map extends SpecialPage {

	function __construct() {
		wfLoadExtensionMessages('SpecialD3Map');
		SpecialPage::SpecialPage("SpecialD3Map", MANAGER.'+', true, 'runSpecialD3Map');
	}
	
	function run(){
	    global $wgOut, $wgServer, $wgScriptPath;
	    $map = new D3Map("{$wgServer}{$wgScriptPath}/index.php?action=getSpecialD3MapData");
	    $map->width = "100%";
	    $map->height = "600px";
	    $string = $map->show();
	    $wgOut->addHTML($string);
	}
	
	static function getSpecialD3MapData($action, $article){
	    global $wgServer, $wgScriptPath;
	    if($action == "getSpecialD3MapData"){
	            
	        $array = array();
	        $universities = array();
	        $edges = array();
	        
	        $year = (isset($_GET['date'])) ? $_GET['date'] : date('Y');
            
            $start = "$year".CYCLE_START_MONTH;
            $end = "$year".CYCLE_END_MONTH_ACTUAL;
            
            $pni = (!isset($_GET['noPNI']) || true) ? Person::getAllPeopleDuring(PNI, $start, $end) : array();
            $cni = (isset($_GET['showCNI']) || true) ? Person::getAllPeopleDuring(CNI, $start, $end) : array();
            
            $people = array_merge($pni, $cni);
            $projectUnis = array();
            foreach($people as $person){
                $uni = $person->getUniversityDuring($start, $end);
                $university = University::newFromName($uni['university']);
                if($university->getLatitude() != "" && $university->getLongitude() != ""){
                    foreach($person->getProjectsDuring($start, $end) as $project){
                        $universities[$university->getName()] = array('name' => $university->getName(),
                                                                    'latitude' => $university->getLatitude(),
                                                                    'longitude' => $university->getLongitude(),
                                                                    'color' => $university->getColor());
                        $projectUnis[$project->getId()][$university->getId()] = $university;
                    }
                }
            }
            foreach($projectUnis as $projectUnis2){
                $i = 0;
                foreach($projectUnis2 as $uni1){
                    $j = 0;
                    foreach($projectUnis2 as $uni2){
                        if($uni1->getId() != $uni2->getId()){
                            if($uni1->getId() <= $uni2->getId()){
                                $edges[] = array('source' => $uni1->getName(),
                                                 'target' => $uni2->getName());
                            }
                            else{
                                $edges[] = array('source' => $uni2->getName(),
                                                 'target' => $uni1->getName());
                            }
                        }
                        $j++;
                    }
                    $i++;
                }
            }
            
            $projects = Project::getAllProjectsEver();
            $startYear = intval(date('Y'));
            foreach($projects as $project){
                $created = intval(substr($project->getCreated(), 0, 4));
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
            
            ksort($universities);
            $array['locations'] = array_values($universities);
            $array['edges'] = $edges;
            
            /*$array['filterOptions'] = array(array('name' => 'Show PNIs', 'param' => 'noPNI', 'checked' => 'checked'),
                                            array('name' => 'Show CNIs', 'param' => 'showCNI', 'checked' => '', 'inverted' => true));
            */
            $array['filterOptions'] = array();
            $array['dateOptions'] = $dates;

            header("Content-Type: application/json");
            echo json_encode($array);
            exit;
        }
        return true;
	}
}
?>
