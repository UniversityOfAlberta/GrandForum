<?php

class ProjectVisualizationsTab extends AbstractTab {

    var $project;
    var $visibility;

    function ProjectVisualizationsTab($project, $visibility){
        parent::AbstractTab("Visualizations");
        $this->project = $project;
        $this->visibility = $visibility;
    }

    function generateBody(){
        global $wgOut, $wgServer, $wgScriptPath;
        $this->html = "";
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(HQP) && $me->isMemberOf($this->project) && !$me->isSubRole("UofC")){
            $wgOut->addScript("<script type='text/javascript'>
                $(document).ready(function(){
                    $('#projectVis').tabs({selected: 0});
                    $('#project').bind('tabsselect', function(event, ui) {
                        if(ui.panel.id == 'visualizations'){
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
		            <li><a href='#wordle'>Tag Cloud</a></li>
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
	                        <div id='wordle'>";
		        $this->showWordle($this->project, $this->visibility);
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
            $timeline = new VisTimeline($dataUrl);
            
            $this->html .="<script type='text/javascript'>
                                $(document).ready(function(){
                                    var nTimesLoadedTimeline = 0;
                                    $('#project').bind('tabsselect', function(event, ui) {
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
    
    function showWordle($project, $visibility){
        global $wgServer, $wgScriptPath, $wgTitle, $wgOut, $wgUser;
        if($wgUser->isLoggedIn()){
            $dataUrl = "$wgServer$wgScriptPath/index.php/{$wgTitle->getNSText()}:{$wgTitle->getText()}?action=getProjectWordleData&project={$project->getId()}";
            $wordle = new Wordle($dataUrl);
            $wordle->width = "100%";
            $wordle->height = 480;
            $wgOut->addScript("<script type='text/javascript'>
                                    $(document).ready(function(){
                                        var nTimesLoadedWordle = 0;
                                        $('#projectVis').bind('tabsselect', function(event, ui) {
                                            if(ui.panel.id == 'wordle'){
                                                if(nTimesLoadedWordle == 0){
                                                    onLoad{$wordle->index}();
                                                    nTimesLoadedWordle++;
                                                }
                                            }
                                        });
                                    });
                              </script>");
            $this->html .= $wordle->show();
        }
    }
    
    static function getProjectTimelineData($action, $article){
        global $config;
        if($action == "getProjectTimelineData" && isset($_GET['project'])){
            global $wgServer, $wgScriptPath;
            header("Content-Type: application/json");
            $project = Project::newFromId($_GET['project']);
            $today = date("Y-m-d");
            
            $array = array();
            $items = array();
            $groups = array(array('id' => 'members',
                                  'content' => 'Members (NI)',
                                  'className' => 'visRed'),
                            array('id' => 'members_ot',
                                  'content' => 'Members (Other)',
                                  'className' => 'visBlue'),
                            array('id' => 'products',
                                  'content' => Inflect::pluralize($config->getValue('productsTerm')),
                                  'className' => 'visOrange'));
            foreach($project->getAllPeopleDuring(null, '0000-00-00 00:00:00', '2100-00-00 00:00:00') as $person){
                $start = substr($project->getJoinDate($person), 0, 10);
                $end = substr($project->getLeaveDate($person), 0, 10);
                if($end == "0000-00-00"){
                    $end = $today;
                }
                if(strcmp($start, $end) > 0){
                    $start = $end;
                }
                if($person->isRoleDuring(NI, $start, $end, $project) || $person->leadershipOf($project)){
                    $content = "<a href='{$person->getUrl()}' style='color: white;' target='_blank'>View Member's Page</a>";
                    $items[] = array('content' => $person->getNameForForms(),
                                     'description' => array('title' => $person->getNameForForms(),
                                                            'text' => $content),
                                     'group' => 'members',
                                     'start' => $start,
                                     'end' => $end);
                }
                else {
                    $content = "<a href='{$person->getUrl()}' style='color: white;' target='_blank'>View Member's Page</a>";
                    $items[] = array('content' => $person->getNameForForms(),
                                     'description' => array('title' => $person->getNameForForms(),
                                                            'text' => $content),
                                     'group' => 'members_ot',
                                     'start' => $start,
                                     'end' => $end);
                }
            }

            $papersDone = array();
            foreach($project->getPapers('all', '0000-00-00 00:00:00', '2100-00-00 00:00:00') as $paper){
                if(isset($papersDone[$paper->getId()])){
                    continue;
                }
                $start = $paper->getDate();
                $content = "<a href='{$paper->getUrl()}' target='_blank' style='color: white;'>View ".$config->getValue('productsTerm')."'s Page</a>";
                $items[] = array('content' => $paper->getTitle(),
                                 'description' => array('title' => $paper->getTitle(),
                                                        'text' => $content),
                                 'group' => 'products',
                                 'start' => $start,
                                 'type' => 'point');
                $papersDone[$paper->getId()] = $paper;
            }
            $array['items'] = $items;
            $array['groups'] = $groups;
            echo json_encode($array);
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
            /*foreach($project->getPapers('all', '0000-00-00 00:00:00', '2100-00-00 00:00:00') as $paper){
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
            }*/
            
            if(!isset($levels[1])){
                @$levels[1]['labels'][] = "No other Projects";
                @$levels[1]['values'][0] = 1;
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
	
	static function getProjectChordData($action, $article){
	    global $wgServer, $wgScriptPath, $config;
	    if($action == "getProjectChordData"){
	        $year = (isset($_GET['date'])) ? $_GET['date'] : YEAR;
	        $array = array();
            $project = Project::newFromId($_GET['project']);
            $people = $project->getAllPeople(null, $year.CYCLE_START_MONTH, $year.CYCLE_END_MONTH);
            $sortedPeople = array();
            foreach($people as $key => $person){
                if(!$person->isRoleDuring(NI, $year.CYCLE_START_MONTH, $year.CYCLE_END_MONTH) &&
                   !$person->isRoleDuring(PL, $year.CYCLE_START_MONTH, $year.CYCLE_END_MONTH, $project)){
                    unset($people[$key]);
                    continue;
                }
                else if(!isset($_GET['sortBy']) || (isset($_GET['sortBy']) && $_GET['sortBy'] == 'name')){
                    $sortedPeople[$person->getReversedName()][] = $person;
                }
                else if($_GET['sortBy'] == 'uni'){
                    $university = $person->getUniversityDuring($year.CYCLE_START_MONTH, $year.CYCLE_END_MONTH);
                    if($university['university'] != ''){
                        $sortedPeople[$university['university']][] = $person;
                    }
                    else{
                        $sortedPeople['Unknown'][] = $person;
                    }
                }
                else if($_GET['sortBy'] == 'dept'){
                    $university = $person->getUniversityDuring($year.CYCLE_START_MONTH, $year.CYCLE_END_MONTH);
                    if($university['department'] != ''){
                        $sortedPeople[$university['department']][] = $person;
                    }
                    else{
                        $sortedPeople['Unknown'][] = $person;
                    }
                }
                else if($_GET['sortBy'] == 'position'){
                    $university = $person->getUniversityDuring($year.CYCLE_START_MONTH, $year.CYCLE_END_MONTH);
                    if($university['position'] != ''){
                        $sortedPeople[$university['position']][] = $person;
                    }
                    else{
                        $sortedPeople['Unknown'][] = $person;
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
                foreach($people as $k1 => $person){
                    $papers = $person->getPapersAuthored('all', $year.CYCLE_START_MONTH, $year.CYCLE_END_MONTH);
                    foreach($papers as $paper){
                        if($paper->belongsToProject($project)){
                            foreach($paper->getAuthors() as $p){
                                if(isset($matrix[$p->getId()]) && $person->getId() != $p->getId()){
                                    $matrix[$person->getId()][$p->getId()] += 1;
                                }
                            }
                        }
                    }
                }
            }
            if(!isset($_GET['noRelations'])){
                foreach($people as $k1 => $person){
                    foreach($people as $k2 => $p){
                        $relations = $person->getRelationsDuring(WORKS_WITH, $year.CYCLE_START_MONTH, $year.CYCLE_END_MONTH);
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
            $startDate = substr($project->getCreated(), 0, 4);
            for($i=$startDate; $i <= YEAR; $i++){
                if($i == YEAR){
                    $dates[] = array('date' => $i, 'checked' => 'checked');
                }
                else{
                    $dates[] = array('date' => $i, 'checked' => '');
                }
            }
            
            $array['filterOptions'] = array(array('name' => 'Show Co-Authorship', 'param' => 'noCoAuthorship', 'checked' => 'checked'),
                                            array('name' => 'Show Relationships', 'param' => 'noRelations', 'checked' => 'checked'));
                                      
            $array['dateOptions'] = $dates;
                                      
            $array['sortOptions'] = array(array('name' => 'Last Name', 'value' => 'name', 'checked' => 'checked'),
                                          array('name' => 'University', 'value' => 'uni', 'checked' => ''),
                                          array('name' => 'Title', 'value' => 'position', 'checked' => ''),
                                          array('name' => $config->getValue('deptsTerm'), 'value' => 'dept', 'checked' => '')
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
	
	static function getProjectWordleData($action, $article){
	    global $wgServer, $wgScriptPath;
	    if($action == "getProjectWordleData"){
	        
	        $project = Project::newFromId($_GET['project']);
	        $description = strip_tags($project->getDescription());
	        
	        $data = Wordle::createDataFromText($description);

            header("Content-Type: application/json");
            echo json_encode($data);
            exit;
        }
        return true;
	}
	
}
?>
