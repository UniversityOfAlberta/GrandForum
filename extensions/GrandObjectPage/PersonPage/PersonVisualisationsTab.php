<?php

$wgHooks['UnknownAction'][] = 'PersonVisualisationsTab::getTimelineData';
$wgHooks['UnknownAction'][] = 'PersonVisualisationsTab::getDoughnutData';
$wgHooks['UnknownAction'][] = 'PersonVisualisationsTab::getGraphData';
$wgHooks['UnknownAction'][] = 'PersonVisualisationsTab::getSurveyData';

class PersonVisualisationsTab extends AbstractTab {

    var $person;
    var $visibility;

    function PersonVisualisationsTab($person, $visibility){
        parent::AbstractTab("Visualizations");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        global $wgUser, $wgOut, $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        $this->html = "";
        if($wgUser->isLoggedIn()){
            $wgOut->addScript("<script type='text/javascript'>
                $(document).ready(function(){
                    $('#personVis').tabs({selected: 0});
                    $('#person').bind('tabsselect', function(event, ui) {
                        if(ui.panel.id == 'visualize'){
                            $('#personVis').tabs('option', 'selected', 0);
                        }
                    });
                });
            </script>");
            $this->html .= 
            "<div id='personVis'>
	            <ul>
		            <li><a href='#timeline'>Timeline</a></li>
		            <li><a href='#chart'>Productivity Chart</a></li>";
            if(($wgUser->isLoggedIn() && $this->person->getId() == $me->getId()) || $me->isRoleAtLeast(MANAGER)){
                $this->html .= "<li><a href='#survey'>Survey Graph</a></li>";
            }
		    $this->html .= "<!--<li><a href='#network'>Network</a></li>-->
	            </ul>
	        <div id='timeline'>";
		        $this->showTimeline($this->person, $this->visibility);
	        $this->html .= "</div>
	        <div id='chart'>";
		        $this->showDoughnut($this->person, $this->visibility);
	        $this->html .= "</div>
	        <div id='survey'>";
		        $this->showSurvey($this->person, $this->visibility);
	        /*$this->html .= "</div>
	        <div id='network'>";
		        $this->showGraph($this->person, $this->visibility);*/
	        $this->html.= "</div>
    </div>
    <script type='text/javascript'>
        
        var selectedTab = $('#personVis .ui-tabs-selected');
        if(selectedTab.length > 0){
            // If the tabs were created previously but removed from the dom, 
            // make sure to reselect the same tab as before
            var i = 0;
            $.each($('#personVis li.ui-state-default'), function(index, val){
                if($(val).hasClass('ui-tabs-selected')){
                    i = index;
                }
            });
            $('#personVis').tabs({ selected: i });
        }
    </script>";
        }
        return $this->html;
    }
    
    function showTimeline($person, $visibility){
        global $wgServer, $wgScriptPath, $wgTitle, $wgOut, $wgUser;
        if($wgUser->isLoggedIn()){
            $dataUrl = "$wgServer$wgScriptPath/index.php/{$wgTitle->getNSText()}:{$wgTitle->getText()}?action=getTimelineData&person={$person->getId()}";
            $timeline = new VisTimeline($dataUrl);
            $timeline->height = "600px";
            
            $this->html .="<script type='text/javascript'>
                                $(document).ready(function(){
                                    var nTimesLoadedTimeline = 0;
                                    $('#person').bind('tabsselect', function(event, ui) {
                                        if(ui.panel.id == 'visualizations'){
                                            if(nTimesLoadedTimeline == 0){
                                                onLoad{$timeline->index}();
                                                nTimesLoadedTimeline++;
                                            }
                                        }
                                    });
                                });
                              </script>";
            $this->html .= "<div id='outerTimeline'>";
            $this->html .= $timeline->show();
            $this->html .= "</div>";
        }
    }
    
    function showDoughnut($person, $visibility){
        global $wgServer, $wgScriptPath, $wgTitle, $wgOut, $wgUser;
        if($wgUser->isLoggedIn()){
            $dataUrl = "$wgServer$wgScriptPath/index.php/{$wgTitle->getNSText()}:{$wgTitle->getText()}?action=getDoughnutData&person={$person->getId()}";
            $doughnut = new Doughnut($dataUrl);
            $wgOut->addScript("<script type='text/javascript'>
                                    $(document).ready(function(){
                                        var nTimesLoadedDoughnut = 0;
                                        $('#personVis').bind('tabsselect', function(event, ui) {
                                            if(ui.panel.id == 'chart'){
                                                if(nTimesLoadedDoughnut == 0){
                                                    $('#vis{$doughnut->index}').doughnut('{$doughnut->url}');
                                                    nTimesLoadedDoughnut++;
                                                }
                                            }
                                        });
                                    });
                              </script>");
            $this->html .= $doughnut->show();
        }
    }
    
    function showSurvey($person, $visibility){
        global $wgServer, $wgScriptPath, $wgTitle, $wgOut, $wgUser;
        $me = Person::newFromWgUser();
        if($wgUser->isLoggedIn() && ($person->getId() == $me->getId() || $me->isMemberOf(Project::newFromName("NAVEL")) || $me->isRoleAtLeast(MANAGER))){
            $dataUrl1 = "$wgServer$wgScriptPath/index.php/{$wgTitle->getNSText()}:{$wgTitle->getText()}?action=getSurveyData&person={$person->getId()}&degree=1";
            $dataUrl2 = "$wgServer$wgScriptPath/index.php/{$wgTitle->getNSText()}:{$wgTitle->getText()}?action=getSurveyData&person={$person->getId()}&degree=2";
            $fdg1 = new ForceDirectedGraph($dataUrl1);
            $fdg1->width = 800;
            $fdg1->height = 700;
            $this->html .= "<button id='switchSurvey' onClick='return false;'>View 2nd Degree Graph</button>";
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
    
    function showGraph($person, $visibility){
        global $wgServer, $wgScriptPath, $wgTitle, $wgOut, $wgUser;
        if($wgUser->isLoggedIn()){
            $dataUrl = "$wgServer$wgScriptPath/index.php/{$wgTitle->getNSText()}:{$wgTitle->getText()}?action=getGraphData&person={$person->getId()}";
            $graph = new Graph($dataUrl);
            $wgOut->addScript("<script type='text/javascript'>
                                    $(document).ready(function(){
                                        $('#personVis').bind('tabsselect', function(event, ui) {
                                            if(ui.panel.id == 'network'){
                                                if(graph != null){
                                                    graph.destroy();
                                                    graph = null;
                                                    $('#vis{$graph->index}').html(\"<div style='height:700px;' id='vis{$graph->index}'></div>\");
                                                    $('#vis{$graph->index}').graph('{$graph->url}');
                                                }
                                                else{
                                                    $('#vis{$graph->index}').graph('{$graph->url}');
                                                }
                                            }
                                            else{
                                                if(graph != null){
                                                    graph.destroy();
                                                }
                                            }
                                        });
                                        $('#person').bind('tabsselect', function(event, ui){
                                            if(ui.panel.id != 'visualize' && graph != null){
                                                graph.destroy();
                                            }
                                        });
                                    });
                              </script>");
            $this->html .= $graph->show();
        }
    }
    
    static function getTimelineData($action, $article){
        if($action == "getTimelineData" && isset($_GET['person'])){
            global $wgServer, $wgScriptPath;
            header("Content-Type: application/json");
            $person = Person::newFromId($_GET['person']);
            $today = date("Y-m-d");
            
            $array = array();
            $items = array();
            $groups = array(array('id' => 'roles',
                                  'content' => 'Roles',
                                  'className' => 'visRed'),
                            array('id' => 'locations',
                                  'content' => 'Locations',
                                  'className' => 'visPurple'),
                            array('id' => 'projects',
                                  'content' => 'Projects',
                                  'className' => 'visBlue'),
                            array('id' => 'relations',
                                  'content' => 'Relations',
                                  'className' => 'visGreen'),
                            array('id' => 'products',
                                  'content' => 'Products',
                                  'className' => 'visOrange'));
            foreach($person->getRoles(true) as $role){
                $start = substr($role->getStartDate(), 0, 10);
                $end = substr($role->getEndDate(), 0, 10);
                if($end == "0000-00-00"){
                    $end = $today;
                }
                if(strcmp($start, $end) > 0){
                    $start = $end;
                }
                $items[] = array('content' => $role->getRole(),
                                 'description' => array('title' => $role->getRole(),
                                                        'text' => ""),
                                 'group' => 'roles',
                                 'start' => $start,
                                 'end' => $end);
            }
            
            foreach($person->getUniversities() as $university){
                $start = substr($university['start'], 0, 10);
                $end = substr($university['end'], 0, 10);
                if($end == "0000-00-00"){
                    $end = $today;
                }
                if($start == "0000-00-00"){
                    $startY = substr($person->getRegistration(), 0, 4);
                    $startM = substr($person->getRegistration(), 4, 2);
                    $startD = substr($person->getRegistration(), 6, 2);
                    $start = "$startY-$startM-$startD";
                }
                if(strcmp($start, $end) > 0){
                    $start = $end;
                }
                $items[] = array('content' => $university['university'],
                                 'description' => array('title' => $university['university'],
                                                        'text' => "<table>
                                                                    <tr>
                                                                        <td><b>Title:</b></td><td>{$university['position']}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><b>Department:</b></td><td>{$university['department']}</td>
                                                                    </tr>
                                                                    </table>"),
                                 'group' => 'locations',
                                 'start' => $start,
                                 'end' => $end);
            }
       
            foreach($person->getProjects(true) as $project){
                $start = substr($project->getJoinDate($person), 0, 10);
                $end = substr($project->getEndDate($person), 0, 10);
                if($end == "0000-00-00"){
                    $end = $today;
                }
                if(strcmp($start, $end) > 0){
                    $start = $end;
                }
                $content = "<a href='{$project->getUrl()}' target='_blank'>View Project's Page</a>";
                $items[] = array('content' => $project->getName(),
                                 'description' => array('title' => $project->getName(),
                                                        'text' => $content),
                                 'group' => 'projects',
                                 'start' => $start,
                                 'end' => $end);
            }
           
            if(count($person->getRelations('all', true)) > 0){
                foreach($person->getRelations('all', true) as $type){
                    foreach($type as $relation){
                        $start = substr($relation->getStartDate(), 0, 10);
                        $end = substr($relation->getEndDate(), 0, 10);
                        if($end == "0000-00-00"){
                            $end = $today;
                        }
                        if(strcmp($start, $end) > 0){
                            $start = $end;
                        }
                        $content = "<a href='{$relation->getUser1()->getUrl()}' target='_blank'>{$relation->getUser1()->getNameForForms()}</a> {$relation->getType()} <a href='{$relation->getUser2()->getUrl()}' target='_blank'>{$relation->getUser2()->getNameForForms()}</a>";
                        $items[] = array('content' => $relation->getUser2()->getNameForForms(),
                                         'description' => array('title' => $relation->getUser2()->getNameForForms(),
                                                                'text' => "$content"),
                                         'group' => 'relations',
                                         'start' => $start,
                                         'end' => $end);
                    }
                }
            }
            
            foreach($person->getPapers('all') as $paper){
                $start = $paper->getDate();
                $content = "<a href='{$paper->getUrl()}' target='_blank'>View Product's Page</a>";
                $items[] = array('content' => str_replace("&#39;", "'", $paper->getTitle()),
                                 'description' => array('title' => str_replace("&#39;", "'", $paper->getTitle()),
                                                        'text' => $content),
                                 'group' => 'products',
                                 'start' => $start,
                                 'type' => 'point');
            }
            $array['items'] = $items;
            $array['groups'] = $groups;
            echo json_encode($array);
            exit;
        }
        return true;
    }
    
    static function getDoughnutData($action, $article){
	    global $wgServer, $wgScriptPath;
	    if($action == "getDoughnutData"){
	        $array = array();
            $person = Person::newFromId($_GET['person']);
            
            $legend = array();
            $legend[0]['name'] = "Year";
            $legend[0]['color'] = "#D38946";
            
            $legend[1]['name'] = "Project";
            $legend[1]['color'] = "#82D868";
            
            $legend[2]['name'] = "Co-authorship";
            $legend[2]['color'] = "#6191B3";
            
            $levels = array();
            $levels[0]['labels'] = array();
            $levels[0]['values'] = array();
            
            $labelIndicies = array();
            $index = 0;
            foreach($person->getPapers() as $paper){
                $date = $paper->getDate();
                $year = substr($date, 0, 4);
                if(!isset($labelIndicies[$year])){
                    $labelIndicies[$year] = $index;
                    $levels[0]['labels'][] = $year;
                    $index++;
                }
                @$levels[0]['values'][$labelIndicies[$year]]++;
            }
            
            $labelIndicies = array();
            $index = 0;
            foreach($person->getPapers() as $paper){
                $projects = $paper->getProjects();
                foreach($projects as $project){
                    if(!isset($labelIndicies[$project->getName()])){
                        $labelIndicies[$project->getName()] = $index;
                        $levels[1]['labels'][] = $project->getName();
                        $index++;
                    }
                    @$levels[1]['values'][$labelIndicies[$project->getName()]]++;
                }
            }
            
            $labelIndicies = array();
            $index = 0;
            foreach($person->getPapers() as $paper){
                $authors = $paper->getAuthors();
                foreach($authors as $author){
                    if($author->getId() != $person->getId()){
                        if(!isset($labelIndicies[$author->getNameForForms()])){
                            $labelIndicies[$author->getNameForForms()] = $index;
                            $levels[2]['labels'][] = $author->getNameForForms();
                            $index++;
                        }
                        @$levels[2]['values'][$labelIndicies[$author->getNameForForms()]]++;
                    }
                }
            }
            
            // Config options
            $array['data_type_singular'] = 'product';
            $array['data_type_plural'] = 'products';
            $array['sort'] = 'desc';
            $array['limit'] = '15';
            $array['width'] = '575';
            $array['height'] = '300';
            // Data
            $array['legend'] = $legend;
            $array['levels'] = $levels;
            
            header("Content-Type: application/json");
            echo json_encode(array($array));
            exit;
        }
        return true;
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
	
	static function getSurveyData($action, $article){
	    global $wgServer, $wgScriptPath;
	    if($action == "getSurveyData"){
	        $degree = (isset($_GET['degree'])) ? $_GET['degree'] : 2;
	    
	        $person = Person::newFromId($_GET['person']);
	        $names = array();
	        $nodes = array();
	        $links = array();
	        $groups = array();
	        $disciplines = AboutTab::getDisciplineList();
	        $edgeGroups = array('Works With', 'Gave/Received Advice', 'Friend', 'Acquaintance');
	        $i = 0;
	        foreach($disciplines as $name => $discipline){
	            $groups[$name] = $i;
	            $i++;
	        }
	        $groups["Other"] = $i;
	        $nodes[] = array("name" => $person->getReversedName(),
	                         "group" => $groups[self::getRootDiscipline($person->getSurveyDiscipline())]);
	        
	        $names[$person->getReversedName()] = $person;
	        $doneLinks = array();
	        foreach($person->getSurveyFirstDegreeConnections() as $key => $connection){
	            foreach($connection as $name => $data){
	                $pers = Person::newFromName($name);
	                
	                $value = 0.01;
	                $nFields = 5;
	                $edgeGroup = 1000;
	                foreach($data as $k => $field){
	                    if(is_numeric($field) && $field != 0 && $k != "hotlist"){
                            $value++;
                            if($k == "work_with" && $edgeGroup >= array_search("Works With", $edgeGroups)){
                                $edgeGroup = array_search("Works With", $edgeGroups);
                            }
                            else if(($k == "gave_advice" || $k == "received_advice") && $edgeGroup >= array_search("Gave/Received Advice", $edgeGroups)){
                                $edgeGroup = array_search("Gave/Received Advice", $edgeGroups);
                            }
                            else if($k == "friend" && $edgeGroup >= array_search("Friend", $edgeGroups)){
                                $edgeGroup = array_search("Works With", $edgeGroups);
                            }
                            else if($k == "acquaintance" && $edgeGroup >= array_search("Acquaintance", $edgeGroups)){
                                $edgeGroup = array_search("Works With", $edgeGroups);
                            }
                        }
	                }
	                
	                if($value > 0 && $edgeGroup != 1000 && $value > 0.01){
	                    $nodes[] = array("name" => $pers->getReversedName(),
	                                     "group" => $groups[self::getRootDiscipline($pers->getSurveyDiscipline())],
	                                     "id" => md5($pers->getReversedName()));
	                    $names[$pers->getReversedName()] = $pers;
	                    $links[] = array("source" => 0,
	                                     "target" => $key+1,
	                                     "group" => $edgeGroup,
	                                     "value" => $value/$nFields);
	                    $doneLinks[0][$key] = true;
	                }
	            }
	        }
	        
	        //if($degree > 1){
	            foreach($nodes as $key1 => $node){
	                if($node['name'] != $person->getName()){
	                    $pers = $names[$node['name']];
	                    foreach($pers->getSurveyFirstDegreeConnections() as $connection){
	                        foreach($connection as $name => $data){
	                            $p = Person::newFromName($name);
	                            $value = 0;
                                $nFields = 6;
                                $edgeGroup = 1000;
	                            foreach($data as $k => $field){
	                                if(is_numeric($field) && $field != 0 && $k != "hotlist"){
	                                    $value++;
	                                    if($k == "work_with" && $edgeGroup >= array_search("Works With", $edgeGroups)){
	                                        $edgeGroup = array_search("Works With", $edgeGroups);
	                                    }
	                                    else if(($k == "gave_advice" || $k == "received_advice") && $edgeGroup >= array_search("Gave/Received Advice", $edgeGroups)){
	                                        $edgeGroup = array_search("Gave/Received Advice", $edgeGroups);
	                                    }
	                                    else if($k == "friend" && $edgeGroup >= array_search("Friend", $edgeGroups)){
	                                        $edgeGroup = array_search("Works With", $edgeGroups);
	                                    }
	                                    else if($k == "acquaintance" && $edgeGroup >= array_search("Acquaintance", $edgeGroups)){
	                                        $edgeGroup = array_search("Works With", $edgeGroups);
	                                    }
	                                }
	                            }
                                if(!isset($names[$p->getReversedName().$key1]) && $degree == 2 && $value > 0.01){
                                    $nodes[] = array("name" => $p->getReversedName(),
                                                     "group" => $groups[self::getRootDiscipline($p->getSurveyDiscipline())],
                                                     "id" => md5($p->getReversedName()));
                                    $names[$p->getReversedName().$key1] = $p;
                                    $key = array_search($p->getReversedName().$key1, array_keys($names));
                                }
                                else{
                                    $key = array_search($p->getReversedName(), array_keys($names));
                                }
                                
                                if($key !== false && $key != 0 && $edgeGroup != 1000 && $value > 0.01 && !isset($doneLinks[$key][$key1]) && !isset($doneLinks[$key1][$key])){
                                    $links[] = array("source" => $key1,
                                                     "target" => $key,
                                                     "group" => $edgeGroup,
                                                     "value" => $value/$nFields);
                                    $doneLinks[$key1][$key] = true;
                                }
	                        }
	                    }
	                }
	            }
	        //}
	        //if($degree > 1){
	            foreach($nodes as &$node){
	                $node['name'] = '';
	            }
	        //}
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
	
	static function getGraphData($action, $article){
	    global $wgServer, $wgScriptPath;
	    if($action == "getGraphData"){
            $person = Person::newFromId($_GET['person']);
            
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
            $data['start_node'] = 'p'.$_GET['person'];
            header("Content-Type: application/json");
           
            echo json_encode($data);
            exit;
        }
        return true;
	}
}
?>
