<?php

class PersonVisualTab extends AbstractTab {

    var $person;
    var $visibility;

    function __construct($person, $visibility){
        parent::__construct("Visualization");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $config;
         $this->html = "<div id='survey'>";
	 $this->html.= "</div>";
	 
    }

    function showSurvey($person, $visibility){
        global $wgServer, $wgScriptPath, $wgTitle, $wgOut, $wgUser;
        $me = Person::newFromWgUser();
        if($wgUser->isRegistered() && ($person->getId() == $me->getId()) || $me->isRoleAtLeast(MANAGER)){
            $dataUrl1 = "$wgServer$wgScriptPath/index.php/{$wgTitle->getNSText()}:{$wgTitle->getText()}?action=getSurveyData&person={$person->getId()}&degree=1";
            $dataUrl2 = "$wgServer$wgScriptPath/index.php/{$wgTitle->getNSText()}:{$wgTitle->getText()}?action=getSurveyData&person={$person->getId()}&degree=2";
            $fdg1 = new ForceDirectedGraph($dataUrl1);
            $fdg1->width = 800;
            $fdg1->height = 700;
            $wgOut->addScript("<script type='text/javascript'>
                                    $(document).ready(function(){
                                        var nTimesLoadedFDG = 0;
                                        $('#personVis').bind('tabsselect', function(event, ui) {
                                            if(ui.panel.id == 'survey'){
                                                if(nTimesLoadedFDG == 0){
                                                    nTimesLoadedFDG++;
                                                    createFDG({$fdg1->width}, {$fdg1->height}, 'vis{$fdg1->index}', '{$fdg1->url}');
                                                    var showing2nd = false;
                                                    $('#switchSurvey').click(function(){
                                                        $('#vis{$fdg1->index}').empty();
                                                        stopFDG();
                                                        if(!showing2nd){
                                                            createFDG({$fdg1->width}, {$fdg1->height}, 'vis{$fdg1->index}', '{$dataUrl2}');
                                                            $('#switchSurvey').html('View 1st Degree Graph');
                                                        }
                                                        else{
                                                            createFDG({$fdg1->width}, {$fdg1->height}, 'vis{$fdg1->index}', '{$dataUrl1}');
                                                            $('#switchSurvey').html('View 2nd Degree Graph');
                                                        }
                                                        showing2nd = !showing2nd;
                                                    });
                                                }
                                            }
                                        });
                                    });
                              </script>");
            $this->html .= $fdg1->show();
        }
    }
	static function getSurveyData($action, $article){
	    global $wgServer, $wgScriptPath;
	    if($action == "getSurveyData"){
	        $person = Person::newFromId($_GET['person']);
	        $names = array();
	        $nodes = array();
	        $links = array();
	        $groups = array('Author' => 0);
	        $edgeGroups = array('Other','Co-Author');
	        $nodes[] = array("name" => $person->getReversedName(),
	                         "group" => 0);
	         
	        $names[$person->getReversedName()] = $person;
	        $doneLinks = array();
		$key = 1;
	        foreach($person->getCoAuthors('all', true, 'both', false) as $name => $value){
			$pers = Person::newFromName($name);
			if($pers->getId() != 0 && $pers->getId() != $person->getId()){
	                    $nodes[] = array("name" => $pers->getReversedName(),
	                                     "group" => 0,
	                                     "id" => md5($pers->getReversedName()));
	                    $names[$pers->getReversedName()] = $pers;
	                    $links[] = array("source" => 0,
	                                     "target" => $key,
	                                     "group" => 1,
	                                     "value" => $value/5);
			    $doneLinks[0][$key] = true;
		       	    $key++;
			}
	        }/*
	        //if($degree > 1){
	            foreach($nodes as $key1 => $node){
	                if($node['name'] != $person->getName()){
	                    $pers = $names[$node['name']];
	                    foreach($pers->getCoAuthors('all', true, 'both', false) as $name => $value){
	                            $p = Person::newFromName($name);
	                   
                            	if(!isset($names[$p->getReversedName()]) && $p->getId() !=0){
                                     if(!isset($doneLinks[$pers->getId()][$p->getId()]) && !isset($doneLinks[$p->getId()][$pers->getId()])){
                                    	$links[] = array("source" => $pers->getId(),
                                                     "target" => $p->getId(),
                                                     "group" => 'Co-Author',
                                                     "value" => $value);
                                    	$doneLinks[$pers->getId()][$p->getId()] = true;
	                       	     }
	                      	}
	                    }
	        
			}
		     }*/
	        $array = array('groups' => array_flip($groups),
	                       'edgeGroups' => $edgeGroups,
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
