<?php

class PersonVisualizationsTab extends AbstractTab {

    var $person;
    var $visibility;

    function __construct($person, $visibility){
        parent::__construct("Visualizations");
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
		            <li><a href='#timeline'>Timeline</a></li>";
		            //<li><a href='#chart'>Productivity Chart</a></li>";
		    $this->html .= "<!--<li><a href='#network'>Network</a></li>-->
	            </ul>
	        <div id='timeline'>";
		        $this->showTimeline($this->person, $this->visibility);
	        /*$this->html .= "</div>
	        <div id='chart'>";
		        $this->showDoughnut($this->person, $this->visibility);*/
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
    
    static function getTimelineData($action, $article){
        global $config;
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
                                  'content' => Inflect::pluralize($config->getValue('productsTerm')),
                                  'className' => 'visOrange'));
            foreach($person->getRoles(true) as $role){
                if($role->isAlias()){
                    continue;
                }
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
                                                                        <td><b>{$config->getValue('deptsTerm')}:</b></td><td>{$university['department']}</td>
                                                                    </tr>
                                                                    </table>"),
                                 'group' => 'locations',
                                 'start' => $start,
                                 'end' => $end);
            }
       
            foreach($person->getProjects(true) as $project){
                $start = substr($project->getJoinDate($person), 0, 10);
                $end = substr($project->getLeaveDate($person), 0, 10);
                if($end == "0000-00-00"){
                    $end = $today;
                }
                if(strcmp($start, $end) > 0){
                    $start = $end;
                }
                $content = "<a href='{$project->getUrl()}' style='color: white;' target='_blank'>View Project's Page</a>";
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
                $content = "<a href='{$paper->getUrl()}' target='_blank' style='color: white;'>View Product's Page</a>";
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
	    global $wgServer, $wgScriptPath, $config;
	    if($action == "getDoughnutData"){
	        $me = Person::newFromWgUser();
	        $array = array();
            $person = Person::newFromId($_GET['person']);
            
            $legend = array();
            $i = 0;
            $legend[$i]['name'] = "Year";
            $legend[$i++]['color'] = "#D38946";
            if($config->getValue('projectsEnabled')){
                $legend[$i]['name'] = "Project";
                $legend[$i++]['color'] = "#82D868";
            }
            
            $legend[$i]['name'] = "Institution";
            $legend[$i++]['color'] = "#B26060";
            if($me->isLoggedIn()){
                $legend[$i]['name'] = "Co-authorship";
                $legend[$i++]['color'] = "#6191B3";
            }
            
            $levels = array();
            $i = 0;
            $levels[$i]['labels'] = array();
            $levels[$i]['values'] = array();
            
            $products = $person->getPapers("all", false, 'both', true, "Public");
            
            $labelIndicies = array();
            $index = 0;
            foreach($products as $paper){
                $date = $paper->getDate();
                $year = substr($date, 0, 4);
                if(!isset($labelIndicies[$year])){
                    $labelIndicies[$year] = $index;
                    $levels[$i]['labels'][] = $year;
                    $index++;
                }
                @$levels[$i]['values'][$labelIndicies[$year]]++;
            }
            $i++;
            $labelIndicies = array();
            $index = 0;
            if($config->getValue('projectsEnabled')){
                foreach($products as $paper){
                    $projects = $paper->getProjects();
                    if(count($projects) == 0){
                        if(!isset($labelIndicies["None"])){
                            $labelIndicies["None"] = $index;
                            $levels[$i]['labels'][] = "None";
                            $index++;
                        }
                        @$levels[$i]['values'][$labelIndicies["None"]]++;
                    }
                    foreach($projects as $project){
                        if(!isset($labelIndicies[$project->getName()])){
                            $labelIndicies[$project->getName()] = $index;
                            $levels[$i]['labels'][] = $project->getName();
                            $index++;
                        }
                        @$levels[$i]['values'][$labelIndicies[$project->getName()]]++;
                    }
                }
                $i++;
            }
            
            $labelIndicies = array();
            $index = 0;
            foreach($products as $paper){
                $unis = $paper->getUniversities();
                foreach($unis as $uni){
                    if(!isset($labelIndicies[$uni])){
                        $labelIndicies[$uni] = $index;
                        $levels[$i]['labels'][] = $uni;
                        $index++;
                    }
                    @$levels[$i]['values'][$labelIndicies[$uni]]++;
                }
            }
            
            if($me->isLoggedIn()){
                $i++;
                $labelIndicies = array();
                $index = 0;
                foreach($products as $paper){
                    $authors = $paper->getAuthors();
                    foreach($authors as $author){
                        if($author->getId() != $person->getId()){
                            if(!isset($labelIndicies[$author->getNameForForms()])){
                                $labelIndicies[$author->getNameForForms()] = $index;
                                $levels[$i]['labels'][] = $author->getNameForForms();
                                $index++;
                            }
                            @$levels[$i]['values'][$labelIndicies[$author->getNameForForms()]]++;
                        }
                    }
                }
            }
            
            // Config options
            $array['data_type_singular'] = 'product';
            $array['data_type_plural'] = 'products';
            $array['sort'] = 'desc';
            $array['limit'] = '15';
            $array['width'] = '100%';
            $array['height'] = '275';
            // Data
            $array['legend'] = $legend;
            $array['levels'] = $levels;
            
            header("Content-Type: application/json");
            echo json_encode(array($array));
            exit;
        }
        return true;
	}
	
	static function getChordData($action, $article){
	    global $wgServer, $wgScriptPath, $config;
	    if($action == "getChordData"){
	        $person = Person::newFromId($_GET['person']);
	        $authors = array_merge(array($person->getName() => 10000), $person->getCoAuthors("all", false, 'both', true, "Public"));
	        
	        asort($authors);
	        $authors = array_reverse($authors);
	        
	        $labels = array();
	        $matrix = array();
	        $colorHashs = array();
	        $colors = array();
	        
	        $possibleColors = array("#33A02C",
	                                "#A6CEE3",
	                                "#E31A1C",
	                                "#1F78B4",
	                                "#FB9A99",
	                                "#6a3d9a",
	                                "#FF7F00",
	                                "#FDBF6F",
	                                "#CAB2D6",
	                                "#B15928",
	                                "#B2DF8A",
	                                "#009090");
	        
	        $newAuthors = array();
	        foreach($authors as $author => $amount){
	            $a = Person::newFromName($author);
	            if($a->getId() != 0){
	                $newAuthors[] = $a;
	            }
	        }
	        $authors = $newAuthors;
	        
	        // Initialize
	        if(count($authors) > 1){
	            $i = 0;
                foreach($authors as $k1 => $author){
                    foreach($authors as $k2 => $a){
                        $matrix[$author->getId()][$a->getId()] = 0;
                    }
                    $labels[] = $author->getNameForForms();
                    $colors[] = $possibleColors[$i];
                    if($i < count($possibleColors)-1){
                        $i++;
                    }
                    else{
                        break;
                    }
                }
	            
	            foreach($authors as $author){
	                $products = $author->getPapers("all", false, 'both', true, "Public");
	                foreach($products as $product){
	                    $auths = $product->getAuthors();
	                    foreach($auths as $a){
	                        if(isset($matrix[$author->getId()][$a->getId()]) && $author->getId() != $a->getId()){
	                            $matrix[$author->getId()][$a->getId()] += 1;
	                        }
	                    }
	                }
	            }
	            
	            $found = false;
                foreach($authors as $k1 => $author){
                    if(array_sum($matrix[$author->getId()]) != 0){
                        $found = true;
                        break;
                    }
                }
                if(!$found){
                    foreach($authors as $k1 => $author){
                        $matrix[$author->getId()][$author->getId()] = 1;
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
            }
	        
	        $array = array();
	        
	        $array['filterOptions'] = array();
            $array['dateOptions'] = array();                     
            $array['sortOptions'] = array();
            $array['matrix'] = $matrix;
            $array['labels'] = $labels;
            $array['colorHashs'] = $colorHashs;
            $array['colors'] = $colors;

            header("Content-Type: application/json");
            echo json_encode($array);
            exit;
	    }
	}

}
?>
