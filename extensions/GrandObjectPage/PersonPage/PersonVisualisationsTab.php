<?php

$wgHooks['UnknownAction'][] = 'PersonVisualisationsTab::getTimelineData';
$wgHooks['UnknownAction'][] = 'PersonVisualisationsTab::getDoughnutData';
$wgHooks['UnknownAction'][] = 'PersonVisualisationsTab::getGraphData';

class PersonVisualisationsTab extends AbstractTab {

    var $person;
    var $visibility;

    function PersonVisualisationsTab($person, $visibility){
        parent::AbstractTab("Visualizations");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        global $wgUser, $wgOut;
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
		            <li><a href='#chart'>Productivity Chart</a></li>
		            <li><a href='#network'>Network</a></li>
	            </ul>
	        <div id='timeline'>";
		        $this->showTimeline($this->person, $this->visibility);
	        $this->html .= "</div>
	        <div id='chart'>";
		        $this->showDoughnut($this->person, $this->visibility);
	        $this->html .= "</div>
	        <div id='network'>";
		        $this->showGraph($this->person, $this->visibility);
	        $this->html.= "</div>
    </div>
    <script type='text/javascript'>
        
        var selectedTab = $('#personVis .ui-tabs-selected');
        if(selectedTab.length > 0){
            // If the tabs were created previously but removed from the dome, 
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
            $timeline = new Simile($dataUrl);
            $wgOut->addScript("<script type='text/javascript'>
                                   var nTimesLoadedTimeline = 0;
                               </script>");
            $this->html .= "<div id='outerTimeline'>";
            $this->html .= $timeline->show();
            $this->html .= "</div>";
            $this->html .="<script type='text/javascript'>
                                    if(nTimesLoadedTimeline == 0){
                                        onLoad{$timeline->index}();
                                        nTimesLoadedTimeline++;
                                    }
                              </script>";
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
            header("Content-Type: application/xml");
            $person = Person::newFromId($_GET['person']);
            $today = date("Y/m/d");
            
            echo "<data>\n";
            foreach($person->getRoles(true) as $role){
                $start = str_replace("-", "/", substr($role->getStartDate(), 0, 10));
                $end = str_replace("-", "/", substr($role->getEndDate(), 0, 10));
                if($end == "0000/00/00"){
                    $end = $today;
                }
                echo "<event start='$start' end='$end' isDuration='true' title='{$role->getRole()}' color='#4E9B05'></event>\n";
            }
       
            foreach($person->getProjects(true) as $project){
                $start = str_replace("-", "/", substr($project->getJoinDate($person), 0, 10));
                $end = str_replace("-", "/", substr($project->getEndDate($person), 0, 10));
                if($end == "0000/00/00"){
                    $end = $today;
                }
                $content = "&lt;a href='{$project->getUrl()}' target='_blank'&gt;Wiki Page&lt;/a&gt;";
                echo "<event start='$start' end='$end' isDuration='true' title='{$project->getName()}' color='#E41B05'>$content</event>\n";
            }
           
            if(count($person->getRelations('all', true)) > 0){
                foreach($person->getRelations('all', true) as $type){
                    foreach($type as $relation){
                        $start = str_replace("-", "/", substr($relation->getStartDate(), 0, 10));
                        $end = str_replace("-", "/", substr($relation->getEndDate(), 0, 10));
                        if($end == "0000/00/00"){
                            $end = $today;
                        }
                        $content = "&lt;a href='{$relation->getUser1()->getUrl()}' target='_blank'&gt;{$relation->getUser1()->getNameForForms()}&lt;/a&gt; {$relation->getType()} &lt;a href='{$relation->getUser2()->getUrl()}' target='_blank'&gt;{$relation->getUser2()->getNameForForms()}&lt;/a&gt;";
                        echo "<event start='$start' end='$end' isDuration='true' title='{$relation->getUser2()->getNameForForms()}' color='#4272B2'>$content</event>\n";
                    }
                }
            }
            
            foreach($person->getPapers('all') as $paper){
                $start = str_replace("-", "/", $paper->getDate());
                $content = "&lt;a href='{$paper->getUrl()}' target='_blank'&gt;Wiki Page&lt;/a&gt;";
                echo "<event start='$start' title='".str_replace("&amp;#39;", "&#39;", str_replace("&", "&amp;", $paper->getTitle()))."' link='' icon='$wgServer$wgScriptPath/extensions/Visualisations/Simile/images/yellow-circle.png' color='#BCB326'>$content</event>\n";
            }
            echo "</data>";
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
                $description .= "<br /><br /><b>Co-Leaders: </b>";
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
