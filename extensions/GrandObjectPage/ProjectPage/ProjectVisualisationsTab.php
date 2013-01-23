<?php

$wgHooks['UnknownAction'][] = 'ProjectVisualisationsTab::getProjectTimelineData';
$wgHooks['UnknownAction'][] = 'ProjectVisualisationsTab::getProjectMilestoneTimelineData';
$wgHooks['UnknownAction'][] = 'ProjectVisualisationsTab::getProjectDoughnutData';
$wgHooks['UnknownAction'][] = 'ProjectVisualisationsTab::getProjectGraphData';
$wgHooks['UnknownAction'][] = 'ProjectVisualisationsTab::getProjectChordData';

class ProjectVisualisationsTab extends AbstractTab {

    var $project;
    var $visibility;

    function ProjectVisualisationsTab($project, $visibility){
        parent::AbstractTab("Visualizations");
        $this->project = $project;
        $this->visibility = $visibility;
    }

    function generateBody(){
        global $wgUser, $wgOut;
        $this->html = "";
        if($wgUser->isLoggedIn()){
            $wgOut->addScript("<script type='text/javascript'>
                $(document).ready(function(){
                    $('#projectVis').tabs({selected: 0});
                    $('#project').bind('tabsselect', function(event, ui) {
                        if(ui.panel.id == 'visualize'){
                            $('#projectVis').tabs('option', 'selected', 0);
                        }
                    });
                });
            </script>");
            $this->html .= 
            "<div id='projectVis'>
	            <ul>
	                <li><a href='#timeline'>Timeline</a></li>
		            <li><a href='#chart'>Productivity Chart</a></li>
		            <li><a href='#chord'>Relations</a></li>
		            <li><a href='#network'>Network</a></li>
	            </ul>
	        <div id='timeline'>";
		        $this->showTimeline($this->project, $this->visibility);
	        $this->html .= "</div>";
	        $this->html .= "<div id='chart'>";
		        $this->showDoughnut($this->project, $this->visibility);
		    $this->html .= "</div>
		                    <div id='chord'>";
		        $this->showChord($this->project, $this->visibility);
	        $this->html .= "</div>
	        <div id='network'>";
		        $this->showGraph($this->project, $this->visibility);
	        $this->html.= "</div>
    </div>
    <script type='text/javascript'>
        
        var selectedTab = $('#projectVis .ui-tabs-selected');
        if(selectedTab.length > 0){
            // If the tabs were created previously but removed from the dome, 
            // make sure to reselect the same tab as before
            var i = 0;
            $.each($('#projectVis li.ui-state-default'), function(index, val){
                if($(val).hasClass('ui-tabs-selected')){
                    i = index;
                }
            });
            $('#projectVis').tabs({ selected: i });
        }
    </script>";
        }
        return $this->html;
    }
    
    function showTimeline($project, $visibility){
        global $wgServer, $wgScriptPath, $wgTitle, $wgOut, $wgUser;
        if($wgUser->isLoggedIn()){
            $dataUrl = "$wgServer$wgScriptPath/index.php/{$wgTitle->getNSText()}:{$wgTitle->getText()}?action=getProjectTimelineData&project={$project->getId()}";
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
    
    function showDoughnut($project, $visibility){
        global $wgServer, $wgScriptPath, $wgTitle, $wgOut, $wgUser;
        if($wgUser->isLoggedIn()){
            $dataUrl = "$wgServer$wgScriptPath/index.php/{$wgTitle->getNSText()}:{$wgTitle->getText()}?action=getProjectDoughnutData&project={$project->getId()}";
            $doughnut = new Doughnut($dataUrl);
            $wgOut->addScript("<script type='text/javascript'>
                                    $(document).ready(function(){
                                        var nTimesLoadedDoughnut = 0;
                                        $('#projectVis').bind('tabsselect', function(event, ui) {
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
    
    function showChord($project, $visibility){
        global $wgServer, $wgScriptPath, $wgTitle, $wgOut, $wgUser;
        if($wgUser->isLoggedIn()){
            $dataUrl = "$wgServer$wgScriptPath/index.php/{$wgTitle->getNSText()}:{$wgTitle->getText()}?action=getProjectChordData&project={$project->getId()}";
            $chord = new Chord($dataUrl);
            $wgOut->addScript("<script type='text/javascript'>
                                    $(document).ready(function(){
                                        var nTimesLoadedChord = 0;
                                        $('#projectVis').bind('tabsselect', function(event, ui) {
                                            if(ui.panel.id == 'chord'){
                                                if(nTimesLoadedChord == 0){
                                                    onLoad{$chord->index}();
                                                    nTimesLoadedChord++;
                                                }
                                            }
                                        });
                                    });
                              </script>");
            $this->html .= $chord->show();
        }
    }
    
    function showGraph($project, $visibility){
        global $wgServer, $wgScriptPath, $wgTitle, $wgOut, $wgUser;
        if($wgUser->isLoggedIn()){
            $dataUrl = "$wgServer$wgScriptPath/index.php/{$wgTitle->getNSText()}:{$wgTitle->getText()}?action=getProjectGraphData&project={$project->getId()}";
            $graph = new Graph($dataUrl);
            $wgOut->addScript("<script type='text/javascript'>
                                    $(document).ready(function(){
                                        $('#projectVis').bind('tabsselect', function(event, ui) {
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
                                        $('#project').bind('tabsselect', function(event, ui){
                                            if(ui.panel.id != 'visualize' && graph != null){
                                                graph.destroy();
                                            }
                                        });
                                    });
                              </script>");
            $this->html .= $graph->show();
        }
    }
    
    static function getProjectMilestoneTimelineData($action, $article){
        $images = array();
        $images[] = 'dark-blue-circle.png';
        $images[] = 'dark-green-circle.png';
        $images[] = 'dark-yellow-circle.png';
        $images[] = 'dark-red-circle.png';
        $images[] = 'dull-blue-circle.png';
        $images[] = 'dull-green-circle.png';
        $images[] = 'dull-red-circle.png';
        $images[] = 'dark-purple-circle.png';
        $images[] = 'dark-magenta-circle.png';
        $images[] = 'gray-circle.png';
        $images[] = 'green-circle.png';
        $images[] = 'red-circle.png';
        $images[] = 'yellow-circle.png';
        $images[] = 'purple-circle.png';
        $images[] = 'magenta-circle.png';
        if($action == "getProjectMilestoneTimelineData" && isset($_GET['project'])){
            global $wgServer, $wgScriptPath;
            header("Content-Type: application/xml");
            $project = Project::newFromId($_GET['project']);
            $today = date("Y/m/d");
            
            echo "<data>\n";
            $i = 0;
            if(isset($_GET['year'])){
                $milestones = $project->getMilestonesDuring($_GET['year']);
            }
            else{
                $milestones = $project->getMilestones();
            }
            foreach($milestones as $milestone){
                $key = $milestone->getMilestoneId();
                $title = $milestone->getTitle();
                $description = nl2br($milestone->getDescription());
                $assessment = nl2br($milestone->getAssessment());
                $start_date = str_replace("-", "/", substr($milestone->getVeryStartDate(), 0, 10));
                $end_date = str_replace("-", "/", substr($milestone->getEndDate(), 0, 10));
                if($end_date == "0000/00/00"){
                    $end_date = $today;
                }
                $status = $milestone->getStatus();
                
                echo "<event trackNum='$i' start='$start_date' end='$end_date' isDuration='true' icon='$wgServer$wgScriptPath/extensions/Visualisations/Simile/images/{$images[$i]}' color='#4272B2'></event>\n";
                while($milestone != null){
                    $start = str_replace("-", "/", substr($milestone->getStartDate(), 0, 10));
                    $end = str_replace("-", "/", substr($milestone->getEndDate(), 0, 10));
                    if($end == "0000/00/00"){
                        $end = $today;
                    }
                    
                    $p_status = $milestone->getStatus();
                    $changed_on = $milestone->getStartDate();
                    $p_title = $milestone->getTitle();
                    $p_end_date = $milestone->getProjectedEndDate();
                    $p_description = nl2br($milestone->getDescription());
                    $p_assessment = nl2br($milestone->getAssessment());
                    $p_comment = nl2br($milestone->getComment());
                    if($p_comment){
                        $p_comment = "<br /><strong>Comment:</strong> $p_comment";
                    }
                    if($p_status == "New"){
                        $label = "Created";
                    }
                    else{
                        $label = $status;
                    }
                    
                    $peopleInvolved = array();
                    foreach($milestone->getPeople() as $person){
                        $peopleInvolved[] = "<a href='{$person->getUrl()}'>{$person->getNameForForms()}</a>";
                    }
                    $people = "";
                    if(count($peopleInvolved) > 0){
                        $people = implode(", ", $peopleInvolved);
                        $people = "<strong>People Involved:</strong> $people<br />";
                    }
                    
                    $lastEdit = "";
                    if($milestone->getEditedBy() != null && $milestone->getEditedBy()->getName() != ""){
                        $lastEdit = "<strong>Last Edited By:</strong> <a href='{$milestone->getEditedBy()->getUrl()}'>{$milestone->getEditedBy()->getNameForForms()}</a><br />";
                    }
                    $content = str_replace("'", "&#39;", "<strong>$label</strong> on $changed_on<br />
                     <strong>Projected End Date:</strong> $p_end_date<br />
                     $people
                     <strong>Description:</strong> $p_description<br />
                     <strong>Assessment:</strong> $p_assessment
                     $p_comment<br />
                     $lastEdit");
                     $content = str_replace("&", "&amp;", $content);
                     $content = str_replace("&amp;#39;", "&#39;", $content);
                     $content = str_replace("<", "&lt;", $content);
                     $content = str_replace(">", "&gt;", $content);
                    //echo "<event trackNum='$i' start='$start' icon='$wgServer$wgScriptPath/extensions/Visualisations/Simile/images/{$images[$i]}' color='#4272B2'>$content</event>\n";
                    $milestone = $milestone->getParent();
                }
                $i++;
                if(count($images) == $i){
                    $i = 0;
                }
            }
            echo "</data>";
            exit;
        }
        return true;
    }
    
    static function getProjectTimelineData($action, $article){
        if($action == "getProjectTimelineData" && isset($_GET['project'])){
            global $wgServer, $wgScriptPath;
            header("Content-Type: application/xml");
            $project = Project::newFromId($_GET['project']);
            $today = date("Y/m/d");
            
            echo "<data>\n";
            foreach($project->getAllPeopleDuring(null, '0000-00-00 00:00:00', '2100-00-00 00:00:00') as $person){
                $start = str_replace("-", "/", substr($project->getJoinDate($person), 0, 10));
                $end = str_replace("-", "/", substr($project->getEndDate($person), 0, 10));
                if($end == "0000/00/00"){
                    $end = $today;
                }
                $content = "&lt;a href='{$person->getUrl()}' target='_blank'&gt;{$person->getNameForForms()}&lt;/a&gt;";
                echo "<event start='$start' end='$end' isDuration='true' title='{$person->getNameForForms()}' color='#4272B2'>$content</event>\n";
            }

            foreach($project->getPapers('all', '0000-00-00 00:00:00', '2100-00-00 00:00:00') as $paper){
                $start = str_replace("-", "/", $paper->getDate());
                $content = "&lt;a href='{$paper->getUrl()}' target='_blank'&gt;Wiki Page&lt;/a&gt;";
                echo "<event start='$start' end='$start' title='".str_replace("'", "&#39;", str_replace("&amp;#39;", "&#39;", str_replace("&", "&amp;", $paper->getTitle())))."' link='' icon='$wgServer$wgScriptPath/extensions/Visualisations/Simile/images/yellow-circle.png' color='#BCB326'>$content</event>\n";
            }
            echo "</data>";
            exit;
        }
        return true;
    }
    
    static function getProjectDoughnutData($action, $article){
	    global $wgServer, $wgScriptPath;
	    if($action == "getProjectDoughnutData"){
	        $array = array();
            $project = Project::newFromId($_GET['project']);
            
            $legend = array();
            $legend[0]['name'] = "Year";
            $legend[0]['color'] = "#D38946";
            
            $legend[1]['name'] = "Other Projects";
            $legend[1]['color'] = "#82D868";
            
            $legend[2]['name'] = "Co-authorship";
            $legend[2]['color'] = "#6191B3";
            
            $levels = array();
            $levels[0]['labels'] = array();
            $levels[0]['values'] = array();
            
            $labelIndicies = array();
            $index = 0;
            foreach($project->getPapers('all', '0000-00-00 00:00:00', '2100-00-00 00:00:00') as $paper){
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
            foreach($project->getPapers('all', '0000-00-00 00:00:00', '2100-00-00 00:00:00') as $paper){
                $projects = $paper->getProjects();
                foreach($projects as $proj){
                    if($project->getId() != $proj->getId()){
                        if(!isset($labelIndicies[$proj->getName()])){
                            $labelIndicies[$proj->getName()] = $index;
                            $levels[1]['labels'][] = $proj->getName();
                            $index++;
                        }
                        @$levels[1]['values'][$labelIndicies[$proj->getName()]]++;
                    }
                }
            }
            
            $labelIndicies = array();
            $index = 0;
            foreach($project->getPapers('all', '0000-00-00 00:00:00', '2100-00-00 00:00:00') as $paper){
                $authors = $paper->getAuthors();
                foreach($authors as $author){
                    if(!isset($labelIndicies[$author->getNameForForms()])){
                        $labelIndicies[$author->getNameForForms()] = $index;
                        $levels[2]['labels'][] = $author->getNameForForms();
                        $index++;
                    }
                    @$levels[2]['values'][$labelIndicies[$author->getNameForForms()]]++;
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
	
	static function getProjectGraphData($action, $article){
	    global $wgServer, $wgScriptPath;
	    if($action == "getProjectGraphData"){
            $project = Project::newFromId($_GET['project']);
            
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
                $description = array();
                $description[] = "<img src='{$person->getPhoto()}' /><br />";
                
                $description[] = "<b>Roles:</b> ";
                $roles = array();
                foreach($person->getRoles() as $role){
                    $roles[] = $role->getRole();
                }
                $description[] = implode(", ", $roles);
                
                $projs = array();
                $description[] = "<br /><br /><b>Projects:</b> ";
                foreach($person->getProjects() as $proj){
                    $projs[] = "<a href='{$proj->getUrl()}' target='_blank'>{$proj->getName()}</a>";
                }
                $description[] = implode(", ", $projs);
                
                $description[] = "<br /><br /><a href='{$person->getUrl()}' target='_blank'>User Page</a>";
                
                $data['nodes']['p'.$person->getId()]['description'] = implode('', $description);

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
                
                $description = array();
                
                $description[] = "<b>Leaders: </b>";
                $leads = array();
                foreach($project->getLeaders() as $member){
                    $leads[] = "<a href='{$member->getUrl()}' target='_blank'>{$member->getNameForForms()}</a>";
                }
                $description[] = implode(", ", $leads);
                $description[] = "<br /><br /><b>Co-Leaders: </b>";
                $leads = array();
                foreach($project->getCoLeaders() as $member){
                    $leads[] = "<a href='{$member->getUrl()}' target='_blank'>{$member->getNameForForms()}</a>";
                }
                $description[] = implode(", ", $leads);
                $description[] = "<br /><br /><b>Members: </b>";
                $membs = array();
                foreach($members as $member){
                    $membs[] = "<a href='{$member->getUrl()}' target='_blank'>{$member->getNameForForms()}</a>";
                }
                $description[] = implode(", ", $membs);
                $description[] = "<br /><br /><a href='{$project->getUrl()}' target='_blank'>Project Page</a>";
                $data['nodes']['pr'.$project->getId()]['description'] = implode('', $description);
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
            $data['start_node'] = 'pr'.$_GET['project'];
            header("Content-Type: application/json");
           
            echo json_encode($data);
            exit;
        }
        return true;
	}
	
	static function getProjectChordData($action, $article){
	    global $wgServer, $wgScriptPath;
	    if($action == "getProjectChordData"){
	        $array = array();
            $project = Project::newFromId($_GET['project']);
            $people = $project->getAllPeople();
            foreach($people as $key => $person){
                if(!$person->isRole(CNI) && !$person->isRole(PNI) && !$person->isRole(AR)){
                    unset($people[$key]);
                    continue;
                }
                if(isset($_GET['noPNI']) && $person->isRole(PNI)){
                    unset($people[$key]);
                    continue;
                }
                if(isset($_GET['noCNI']) && $person->isRole(CNI)){
                    unset($people[$key]);
                    continue;
                }
                if(isset($_GET['noAR']) && $person->isRole(AR)){
                    unset($people[$key]);
                    continue;
                }
                else if(!isset($_GET['sortBy']) || (isset($_GET['sortBy']) && $_GET['sortBy'] == 'name')){
                    $sortedPeople[$person->getReversedName()][] = $person;
                }
                else if($_GET['sortBy'] == 'uni'){
                    $university = $person->getUniversity();
                    if($university['university'] != ''){
                        $sortedPeople[$university['university']][] = $person;
                    }
                    else{
                        $sortedPeople['Unknown'][] = $person;
                    }
                }
                else if($_GET['sortBy'] == 'role'){
                    if($person->isRole(PNI)){
                        $sortedPeople[PNI][] = $person;
                    }
                    else if($person->isRole(CNI)){
                        $sortedPeople[CNI][] = $person;
                    }
                    else if($person->isRole(AR)){
                        $sortedPeople[AR][] = $person;
                    }
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
                $papers = $project->getPapers();
                foreach($papers as $paper){
                    foreach($people as $k1 => $person){
                        foreach($people as $k2 => $p){
                            if($p->isAuthorOf($paper) && $person->isAuthorOf($paper) && $p->getId() != $person->getId()){
                                @$matrix[$person->getId()][$p->getId()] += 1;
                            }
                        }
                    }
                }
            }
            
            if(!isset($_GET['noRelations'])){
                foreach($people as $k1 => $person){
                    foreach($people as $k2 => $p){
                        $relations = $person->getRelations(WORKS_WITH);
                        if(count($relations) > 0){
                            foreach($relations as $relation){
                                if($relation instanceof Relationship && $relation->getUser2()->getId() == $p->getId()){
                                    @$matrix[$person->getId()][$p->getId()] += 5;
                                }
                            }
                        }
                    }
                }
            }
            
            foreach($people as $k1 => $person){
                if(array_sum($matrix[$person->getId()]) == 0){
                    $matrix[$person->getId()][$person->getId()] = 1;
                    foreach($people as $k2 => $p){
                        if($p->getId() != $person->getId() && $matrix[$p->getId()][$person->getId()] > 0){
                            $matrix[$person->getId()][$person->getId()] = 0;
                            break;
                        }
                    }
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
            
            $array['filterOptions'] = array(array('name' => 'Show Co-Authorship', 'param' => 'noCoAuthorship', 'checked' => 'checked'),
                                      array('name' => 'Show Relationships', 'param' => 'noRelations', 'checked' => 'checked'),
                                      array('name' => 'Show PNIs', 'param' => 'noPNI', 'checked' => 'checked'),
                                      array('name' => 'Show CNIs', 'param' => 'noCNI', 'checked' => 'checked'),
                                      array('name' => 'Show ARs', 'param' => 'noAR', 'checked' => 'checked'));
                                      
            $array['sortOptions'] = array(array('name' => 'Last Name', 'value' => 'name', 'checked' => 'checked'),
                                          array('name' => 'University', 'value' => 'uni', 'checked' => ''),
                                          array('name' => 'Primary Role', 'value' => 'role', 'checked' => '')
                                          );
                                      
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
