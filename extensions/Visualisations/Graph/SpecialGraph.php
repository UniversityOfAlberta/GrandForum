<?php
$dir = dirname(__FILE__) . '/';
$wgSpecialPages['SpecialGraph'] = 'SpecialGraph';
$wgExtensionMessagesFiles['SpecialGraph'] = $dir . 'SpecialGraph.i18n.php';

$wgHooks['UnknownAction'][] = 'SpecialGraph::getSpecialGraphData';

function runSpecialGraph($par) {
	SpecialGraph::run($par);
}

class SpecialGraph extends SpecialPage {

	function __construct() {
		wfLoadExtensionMessages('SpecialGraph');
		SpecialPage::SpecialPage("SpecialGraph", HQP.'+', true, 'runSpecialGraph');
	}
	
	function run(){
	    global $wgOut, $wgServer, $wgScriptPath;
	    $graph = new Graph("{$wgServer}{$wgScriptPath}/index.php?action=getSpecialGraphData");
	    $string = $graph->show();
	    $wgOut->addHTML($string);
	}
	
	static function getSpecialGraphData($action, $article){
	    global $wgServer, $wgScriptPath;
	    if($action == "getSpecialGraphData"){
            $person = Person::newFromId(3);
            
            $data = array();
            $data['legend'] = array();
            $data['legend'][PNI] = array('color' => "#4E9B05",
                                         'name' => PNI);
            $data['legend'][CNI] = array('color' => "#46731D",
                                         'name' => CNI);
            $data['legend'][HQP] = array('color' => "#394D26",
                                         'name' => HQP);     
            $data['legend']["Project"] = array('color' => "#E41B05",
                                               'name' => "Project");                        
            $data['nodes'] = array();
            $people = Person::getAllPeople();
            $projects = Project::getAllProjects();
            foreach($people as $person){
                if($person->isRole(INACTIVE)){
                    continue;
                }
                $relations = $person->getRelations();
                $data['nodes']['p'.$person->getId()]['id'] = 'p'.$person->getId();
                if(count($person->leadership()) > 0){
                    $data['nodes']['p'.$person->getId()]['name'] = "<img style='width:8px;height:8px;vertical-align:top;' src='$wgServer$wgScriptPath/extensions/Visualisations/Graph/lead.png' />&nbsp;";
                }
                @$data['nodes']['p'.$person->getId()]['name'] .= str_replace(" ", "&nbsp;", $person->getNameForForms());
                
                if($person->isHQP()){
                    $data['nodes']['p'.$person->getId()]['type'] = HQP;
                }
                else if($person->isCNI()){
                    $data['nodes']['p'.$person->getId()]['type'] = CNI;
                }
                else if($person->isPNI()){
                    $data['nodes']['p'.$person->getId()]['type'] = PNI;
                }
                $description = "<img src='{$person->getPhoto()}' /><br />";
                
                $description .= "<b>Roles:</b> ";
                $roles = array();
                foreach($person->getRoles() as $role){
                    $roles[] = $role->getRole();
                }
                $description .= implode(", ", $roles);
                
                $projs = array();
                $description .= "<br /><br /><b>Projects:</b> ";
                foreach($person->getProjects() as $proj){
                    $projs[] = "<a href='{$proj->getUrl()}' target='_blank'>{$proj->getName()}</a>";
                }
                $description .= implode(", ", $projs);
                
                $description .= "<br /><br /><a href='{$person->getUrl()}' target='_blank'>User Page</a>";
                
                $data['nodes']['p'.$person->getId()]['description'] = $description;

                if(count($relations) > 0){
                    foreach($relations as $relationTypes){
                        foreach($relationTypes as $relation){
                            $weight = 3;
                            $type = $relation->getType();
                            if($type == "Supervises"){
                                $weight = 6;
                            }
                            $data['nodes']['p'.$relation->getUser1()->getId()]['connections'][] = array('a' => 'p'.$relation->getUser1()->getId(),
                                                                                        'b' => 'p'.$relation->getUser2()->getId(),
                                                                                        'weight' => $weight);
                            $data['nodes']['p'.$relation->getUser2()->getId()]['connections'][] = array('a' => 'p'.$relation->getUser1()->getId(),
                                                                                          'b' => 'p'.$relation->getUser2()->getId(),
                                                                                          'weight' => $weight);
                        }
                    }
                }
            }
            foreach($projects as $project){
                $members = $project->getAllPeople();
                $data['nodes']['pr'.$project->getId()]['id'] = 'pr'.$project->getId();
                $data['nodes']['pr'.$project->getId()]['name'] = str_replace(" ", "&nbsp", $project->getName());
                $data['nodes']['pr'.$project->getId()]['type'] = "Project";
                
                $description = "";
                
                $description .= "<b>Leaders: </b>";
                $leads = array();
                foreach($project->getLeaders() as $member){
                    $leads[] = "<a href='{$member->getUrl()}' target='_blank'>{$member->getNameForForms()}</a>";
                }
                $description .= implode(", ", $leads);
                $description .= "<br /><br /><b>co-Leaders: </b>";
                $leads = array();
                foreach($project->getCoLeaders() as $member){
                    $leads[] = "<a href='{$member->getUrl()}' target='_blank'>{$member->getNameForForms()}</a>";
                }
                $description .= implode(", ", $leads);
                $description .= "<br /><br /><b>Members: </b>";
                $membs = array();
                foreach($members as $member){
                    $membs[] = "<a href='{$member->getUrl()}' target='_blank'>{$member->getNameForForms()}</a>";
                }
                $description .= implode(", ", $membs);
                $description .= "<br /><br /><a href='{$project->getUrl()}' target='_blank'>Project Page</a>";
                $data['nodes']['pr'.$project->getId()]['description'] = $description;
                foreach($members as $member){
                    if($member->isRole(INACTIVE)){
                        continue;
                    }
                    $data['nodes']['p'.$member->getId()]['connections'][] = array('a' => 'p'.$member->getId(),
                                                                                  'b' => 'pr'.$project->getId(),
                                                                                  'weight' => 3);
                    $data['nodes']['pr'.$project->getId()]['connections'][] = array('a' => 'p'.$member->getId(),
                                                                                  'b' => 'pr'.$project->getId(),
                                                                                  'weight' => 3);
                }
            }
            $data['start_node'] = 'p'.'3';
            header("Content-Type: application/json");
           
            echo json_encode($data);
            exit;
        }
        return true;
	}
}
?>
