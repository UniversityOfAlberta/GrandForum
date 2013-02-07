<?php
$dir = dirname(__FILE__) . '/';
$wgSpecialPages['SpecialFDG'] = 'SpecialFDG';
$wgExtensionMessagesFiles['SpecialFDG'] = $dir . 'SpecialFDG.i18n.php';

$wgHooks['UnknownAction'][] = 'SpecialFDG::getSpecialFDGData';

function runSpecialFDG($par) {
	SpecialFDG::run($par);
}

class SpecialFDG extends SpecialPage {

	function __construct() {
		wfLoadExtensionMessages('SpecialFDG');
		SpecialPage::SpecialPage("SpecialFDG", CNI.'+', true, 'runSpecialFDG');
	}
	
	function run(){
	    global $wgOut, $wgServer, $wgScriptPath;
	    $person = Person::newFromId($_GET['person']);
	    $wgOut->addHTML("<h2>{$person->getReversedName()}</h2>");
	    $person = @$_GET['person'];
	    $degree = isset($_GET['degree']) ? $_GET['degree'] : 1;
	    $graph = new ForceDirectedGraph("{$wgServer}{$wgScriptPath}/index.php?action=getSpecialFDGData&person={$person}&degree={$degree}");
	    $string = $graph->show();
	    $wgOut->addHTML($string);
	}
	
	static function getRootDiscipline($disc){
	    $discs = explode("|", $disc);
	    $dics = $discs[0];
	    $disciplines = AboutTab::getDisciplineList();
	    foreach($disciplines as $name => $discipline){
	        foreach($discipline as $d){
	            if($d == $disc){
	                return $name;
	            }
	        }
	    }
	    return "Other";
	}
	
	static function getSpecialFDGData($action, $article){
	    global $wgServer, $wgScriptPath;
	    if($action == "getSpecialFDGData"){
	        $degree = (isset($_GET['degree'])) ? $_GET['degree'] : 2;
	    
	        $person = Person::newFromId($_GET['person']);
	        $names = array();
	        $nodes = array();
	        $links = array();
	        $groups = array();
	        $disciplines = AboutTab::getDisciplineList();
	        $i = 0;
	        foreach($disciplines as $name => $discipline){
	            $groups[$name] = $i;
	            $i++;
	        }
	        $groups["Other"] = $i;
	        $nodes[] = array("name" => $person->getReversedName(),
	                         "group" => $groups[self::getRootDiscipline($person->getSurveyDiscipline())]);
	        
	        $names[$person->getReversedName()] = $person;
	        foreach($person->getSurveyFirstDegreeConnections() as $key => $connection){
	            foreach($connection as $name => $data){
	                $pers = Person::newFromName($name);
	                
	                $value = 0;
	                $nFields = 5;
	                foreach($data as $k => $field){
	                    if(is_numeric($field) && $field != 0 && $k != "hotlist"){
	                        $value++;
	                    }
	                }
	                
	                if($value > 0){
	                    $nodes[] = array("name" => $pers->getReversedName(),
	                                     "group" => $groups[self::getRootDiscipline($pers->getSurveyDiscipline())]);
	                    $names[$pers->getReversedName()] = $pers;
	                    $links[] = array("source" => 0,
	                                     "target" => $key+1,
	                                     "value" => $value/$nFields);
	                }
	            }
	        }
	        
	        foreach($nodes as $key1 => $node){
	            if($node['name'] != $person->getName()){
	                $pers = $names[$node['name']];
	                foreach($pers->getSurveyFirstDegreeConnections() as $connection){
	                    foreach($connection as $name => $data){
	                        $p = Person::newFromName($name);
	                        $value = 0;
                            $nFields = 6;
                            foreach($data as $field){
                                if(is_numeric($field) && $field != 0){
                                    $value++;
                                }
                            }
                            if(!isset($names[$p->getReversedName().$key1]) && $degree == 2){
                                $nodes[] = array("name" => $p->getReversedName(),
                                                 "group" => $groups[self::getRootDiscipline($p->getSurveyDiscipline())]);
                                $names[$p->getReversedName().$key1] = $p;
                                $key = array_search($p->getReversedName().$key1, array_keys($names));
                            }
                            else{
                                $key = array_search($p->getReversedName(), array_keys($names));
                            }
                            
                            if($key !== false && $key != 0){
                                $links[] = array("source" => $key1,
                                                 "target" => $key,
                                                 "value" => $value/$nFields);
                            }
	                    }
	                }
	            }
	        }
	        if($degree > 1){
	            foreach($nodes as &$node){
	                $node['name'] = '';
	            }
	        }
	        $array = array('groups' => array_flip($groups),
	                       'nodes' => $nodes,
	                       'links' => $links);
            header("Content-Type: application/json");
            echo json_encode($array); 
            exit;
        }
        return true;
	}
}
?>
