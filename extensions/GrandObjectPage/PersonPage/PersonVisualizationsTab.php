<?php

$wgHooks['UnknownAction'][] = 'PersonVisualizationsTab::getTimelineData';
$wgHooks['UnknownAction'][] = 'PersonVisualizationsTab::getDoughnutData';
$wgHooks['UnknownAction'][] = 'PersonVisualizationsTab::getChordData';

class PersonVisualizationsTab extends AbstractTab {

    var $person;
    var $visibility;

    function PersonVisualizationsTab($person, $visibility){
        parent::AbstractTab("Timeline");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        global $wgUser, $wgOut, $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        $this->html = "";
        if($wgUser->isLoggedIn()){
            $this->showTimeline($this->person, $this->visibility);
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
                                        if(ui.panel.id == 'timeline'){
                                            if(nTimesLoadedTimeline == 0){
                                                onLoad{$timeline->index}();
                                                nTimesLoadedTimeline++;
                                            }
                                        }
                                    });
                                });
                                
                                $.get('$dataUrl', function(response){
                                    var groups = response.groups;
                                    var data = _.map(response.items, function(item){
                                        var date = '';
                                        if(item.end != undefined){
                                            date = item.start + ' - ' + item.end;
                                        }
                                        else{
                                            date = item.start;
                                        }
                                        var group = _.findWhere(groups, {id: item.group});
                                        return ['<span class=' + group.className + '>' + item.content + '</span>', 
                                                '<span class=' + group.className + '>' + group.content  + '</span>', 
                                                '<span class=' + group.className + '>' + date  + '</span>'];
                                    });
                                    $('#timelineTable').DataTable({
                                        data: data,
                                        autoWidth: false,
                                        pageLength: 100
                                    });
                                });
                           </script>";
            $this->html .= "<div id='outerTimeline'>";
            $this->html .= $timeline->show();
            $this->html .= "</div><br />";
            $this->html .= "<table id='timelineTable' class='wikitable' frame='box' rules='all' width='100%'>
                                <thead>
                                    <tr>
                                        <th>Event</th>
                                        <th>Event Type</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>";

            $this->html .= "</tbody></table>";
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
                                  'content' => 'Positions',
                                  'className' => 'visPurple'),
                            array('id' => 'relations',
                                  'content' => 'Relations',
                                  'className' => 'visGreen'),
                            array('id' => 'products',
                                  'content' => Inflect::pluralize($config->getValue('productsTerm')),
                                  'className' => 'visOrange'));
            foreach($person->getRoles(true) as $role){
                $start = substr($role->getStartDate(), 0, 10);
                $end = substr($role->getEndDate(), 0, 10);
                if($end == ZOT){
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
                if($end == ZOT){
                    $end = $today;
                }
                if($start == ZOT){
                    $startY = substr($person->getRegistration(), 0, 4);
                    $startM = substr($person->getRegistration(), 4, 2);
                    $startD = substr($person->getRegistration(), 6, 2);
                    $start = "$startY-$startM-$startD";
                }
                if(strcmp($start, $end) > 0){
                    $start = $end;
                }
                $items[] = array('content' => $university['position'],
                                 'description' => array('title' => $university['position'],
                                                        'text' => "<table>
                                                                    <tr>
                                                                        <td><b>University:</b></td><td>{$university['university']}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><b>Position:</b></td><td>{$university['position']}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><b>Department:</b></td><td>{$university['department']}</td>
                                                                    </tr>
                                                                    </table>"),
                                 'group' => 'locations',
                                 'start' => $start,
                                 'end' => $end);
            }
           
            if(count($person->getRelations('all', true)) > 0){
                // My Relations
                foreach($person->getRelations('all', true) as $type){
                    foreach($type as $relation){
                        $start = substr($relation->getStartDate(), 0, 10);
                        $end = substr($relation->getEndDate(), 0, 10);
                        if($end == ZOT){
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
            if(count($person->getSupervisors(true)) > 0){
                // Supervisors
                foreach($person->getSupervisors(true) as $supervisor){
                    $relations = array_merge($supervisor->getRelations(SUPERVISES, true), $supervisor->getRelations(CO_SUPERVISES, true));
                    foreach($relations as $r){
                        $hqp = $r->getUser2();
                        if($hqp->getId() == $person->getId()){
                            $start = substr($r->getStartDate(), 0, 10);
                            $end = substr($r->getEndDate(), 0, 10);
                            if($end == ZOT){
                                $end = $today;
                            }
                            if(strcmp($start, $end) > 0){
                                $start = $end;
                            }
                            $content = "<a href='{$r->getUser1()->getUrl()}' target='_blank'>{$r->getUser1()->getNameForForms()}</a> {$r->getType()} <a href='{$r->getUser2()->getUrl()}' target='_blank'>{$r->getUser2()->getNameForForms()}</a>";
                            $items[] = array('content' => $r->getUser1()->getNameForForms(),
                                             'description' => array('title' => $r->getUser1()->getNameForForms(),
                                                                    'text' => "$content"),
                                             'group' => 'relations',
                                             'start' => $start,
                                             'end' => $end);
                        }
                    }
                }
            }
            
            
            foreach($person->getPapersAuthored('all', "1900-00-00", "2100-01-01", false, false) as $paper){
                $start = $paper->getDate();
                if($start == ZOT){
                    $start = $paper->getAcceptanceDate();
                }
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
	    global $wgServer, $wgScriptPath, $config;
	    if($action == "getDoughnutData"){
	        $me = Person::newFromWgUser();
	        $array = array();
            $person = Person::newFromId($_GET['person']);
            
            $legend = array();
            $i = 0;
            $legend[$i]['name'] = "Year";
            $legend[$i++]['color'] = "#a6cee3";
            $legend[$i]['name'] = "University";
            $legend[$i++]['color'] = "#b2df8a";
            $legend[$i]['name'] = "Co-authorship";
            $legend[$i++]['color'] = "#fb9a99";
            
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
