<?php

$wgHooks['UnknownAction'][] = 'PublicUniversityMapTab::getPublicUniversityMapData';

class PublicUniversityMapTab extends AbstractTab {
	
	function PublicUniversityMapTab(){
        parent::AbstractTab("University Map");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath, $config;
	    $map = new D3Map("{$wgServer}{$wgScriptPath}/index.php?action=getPublicUniversityMapData");
	    $map->width = "100%";
	    $map->height = "600px";
	    if($config->getValue('projectsEnabled')){
	        $this->html = "<div><a class='button' onClick='$(\"#help{$map->index}\").show();$(this).hide();'>Show Help</a>
	            <div id='help{$map->index}' style='display:none;'>
	                <p>This visualization shows the relations between universities.  Each arc represents a project that has people in more than one university.</p>
	                <ul>
	                    <li>Using the date slider allows the map to only show universities and their relations from the specified year.</li>
	                    <li>To highlight a University and its relations, check/uncheck the Universities you wish to highlight.</li>
	                </ul>
	            </div>
	        </div>";
	    }
	    else{
	        $this->html = "<div><a class='button' onClick='$(\"#help{$map->index}\").show();$(this).hide();'>Show Help</a>
	            <div id='help{$map->index}' style='display:none;'>
	                <p>This visualization shows the relations between universities.  Each arc represents a product that has authors in more than one university.</p>
	                <ul>
	                    <li>Using the date slider allows the map to only show universities and their relations from the specified year.</li>
	                    <li>To highlight a University and its relations, check/uncheck the Universities you wish to highlight.</li>
	                </ul>
	            </div>
	        </div>";
	    }
	    $this->html .= $map->show();
	    $this->html .= "<script type='text/javascript'>
            $('#publicVis').bind('tabsselect', function(event, ui) {
                if(ui.panel.id == 'university-map'){
                    _.defer(function(){
	                    onLoad{$map->index}();
	                });
	            }
	        });
	    </script>";
	}
	
	static function getPublicUniversityMapData($action, $article){
	    global $wgServer, $wgScriptPath, $config;
	    $me = Person::newFromWgUser();
	    $year = (isset($_GET['date'])) ? $_GET['date'] : date('Y');
	    if($action == "getPublicUniversityMapData"){
	        $array = array();
	        $universities = array();
	        $edges = array();
	        
	        $year = (isset($_GET['date'])) ? $_GET['date'] : date('Y');
            
            $start = "$year".CYCLE_START_MONTH;
            $end = "$year".CYCLE_END_MONTH_ACTUAL;
            
            $phaseDates = $config->getValue('projectPhaseDates');
            $currentYear = date('Y');
            $startYear = max($currentYear - 8, substr($phaseDates[1], 0, 4));
            
            $people = array_merge(Person::getAllPeopleDuring(NI, $start, $end),
                                  Person::getAllPeopleDuring(PARTNER, $start, $end));
            
            if($config->getValue('projectsEnabled')){
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
                foreach($projects as $project){
                    $created = intval(substr($project->getCreated(), 0, 4));
                    if($created < $startYear){
                        $startYear = intval($created);
                    }
                    $labels[] = $project->getName();
                }
            }
            else{
                $products = Product::getAllPapers();
                foreach($products as $product){
                    if($product->getYear() == $year){
                        $unis = $product->getUniversities();
                        foreach($unis as $uni){
                            $university = University::newFromName($uni);
                            if($university->getColor() != '#888888'){
                                $universities[$university->getName()] = array('name' => $university->getName(),
                                                                              'latitude' => $university->getLatitude(),
                                                                              'longitude' => $university->getLongitude(),
                                                                              'color' => $university->getColor());
                                $productUnis[$product->getId()][$university->getId()] = $university;
                            }
                        }
                    }
                }
                foreach($productUnis as $productUnis2){
                    $i = 0;
                    foreach($productUnis2 as $uni1){
                        $j = 0;
                        foreach($productUnis2 as $uni2){
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
            }
            $dates = array();
            for($i=$startYear; $i <= date('Y'); $i++){
                if($i == date('Y')){
                    $dates[] = array('date' => (int) $i, 'checked' => 'checked');
                }
                else{
                    $dates[] = array('date' => (int) $i, 'checked' => '');
                }
            }
            
            ksort($universities);
            $array['locations'] = array_values($universities);
            $array['edges'] = $edges;

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
