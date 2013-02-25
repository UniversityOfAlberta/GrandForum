<?php
$dir = dirname(__FILE__) . '/';
$wgSpecialPages['SpecialHighChart'] = 'SpecialHighChart';
$wgExtensionMessagesFiles['SpecialHighChart'] = $dir . 'SpecialHighChart.i18n.php';

$wgHooks['UnknownAction'][] = 'SpecialHighChart::getSpecialProjectParetoData';
$wgHooks['UnknownAction'][] = 'SpecialHighChart::getSpecialUniversityParetoData';

function runSpecialHighChart($par) {
	SpecialHighChart::run($par);
}

class SpecialHighChart extends SpecialPage {

	function __construct() {
		wfLoadExtensionMessages('SpecialHighChart');
		SpecialPage::SpecialPage("SpecialHighChart", MANAGER.'+', true, 'runSpecialHighChart');
	}
	
	function run(){
	    global $wgOut, $wgServer, $wgScriptPath;
	    $chart = new HighChart("{$wgServer}{$wgScriptPath}/index.php?action=getSpecialProjectParetoData");
	    $chart->height = "800px";
	    $chart->width = "100%";
	    $string = $chart->show();
	    
	    $chart = new HighChart("{$wgServer}{$wgScriptPath}/index.php?action=getSpecialProjectAvgParetoData");
	    $chart->height = "800px";
	    $chart->width = "100%";
	    $string .= $chart->show();
	    
	    $chart = new HighChart("{$wgServer}{$wgScriptPath}/index.php?action=getSpecialUniversityParetoData");
	    $chart->height = "800px";
	    $chart->width = "100%";
	    $string .= $chart->show();
	    
	    $chart = new HighChart("{$wgServer}{$wgScriptPath}/index.php?action=getSpecialUniversityAvgParetoData");
	    $chart->height = "800px";
	    $chart->width = "100%";
	    $string .= $chart->show();
	    
	    $wgOut->addHTML($string);
	}
	
	static function getSpecialProjectParetoData($action, $article){
	    global $wgServer, $wgScriptPath;
	    $me = Person::newFromWgUser();
	    if(($action == "getSpecialProjectParetoData" || $action == "getSpecialProjectAvgParetoData") && $me->isRoleAtLeast(MANAGER)){
	        $projects = Project::getAllProjectsDuring((REPORTING_YEAR-1).REPORTING_CYCLE_START_MONTH, (REPORTING_YEAR-1).REPORTING_CYCLE_END_MONTH);
	        $pNames = array();
	        $pBudget = array();
	        $rBudget = array();
	        
	        foreach($projects as $project){
	            $nAUploaded = 0;
	            $nRUploaded = 0;
	            $abudget = $project->getAllocatedBudget(REPORTING_YEAR-1);
	            $rbudget = $project->getRequestedBudget(REPORTING_YEAR-1);
	            if($abudget != null){
	                $nAUploaded = $abudget->copy()->select(V_PERS_NOT_NULL)->nCols();
	                $totalBudget = $abudget->copy()->rasterize()->select(CUBE_TOTAL)->where(CUBE_TOTAL);
	                if($totalBudget->nRows() > 0 && $totalBudget->nCols() > 0){
	                    $value = $totalBudget->toString();
	                    $value = (int)str_replace(',', '', str_replace('$', '', $value));
	                    $aTotal = $value;
	                }
	                else{
	                    $aTotal = 0;
	                }
	            }
	            else{
	                $aTotal = 0;
	            }
	            if($rbudget != null){
	                $nRUploaded = $rbudget->copy()->select(V_PERS_NOT_NULL)->nCols();
	                $totalBudget = $rbudget->copy()->rasterize()->select(CUBE_TOTAL)->where(CUBE_TOTAL);
	                if($totalBudget->nRows() > 0 && $totalBudget->nCols() > 0){
	                    $value = $totalBudget->toString();
	                    $value = (int)str_replace(',', '', str_replace('$', '', $value));
	                    $rTotal = $value;
	                }
	                else{
	                    $rTotal = 0;
	                }
	            }
	            else{
	                $rTotal = 0;
	            }
	            $pBudget[$project->getName()] = $aTotal;
	            $rBudget[$project->getName()] = $rTotal;
	            if($action == "getSpecialProjectAvgParetoData"){
	                $pBudget[$project->getName()] = round($aTotal/max(1, $nAUploaded));
	                $rBudget[$project->getName()] = round($rTotal/max(1, $nRUploaded));
	            }
	        }
	        
	        $names = array();
	        $i=1;
	        foreach($pBudget as $pName => $p){
	            $names[$pName] = "Project $i";
	            $i++;
	        }
	        
	        asort($pBudget, SORT_NUMERIC);
	        $pBudget = array_reverse($pBudget, true);
	        $sumSeries1 = array();
            $pSeries1 = array();
            $currentSum1 = 0;
            $currentSum2 = 0;
            $i = 1;
	        foreach($pBudget as $pName => $total){
	            $currentSum1 += $total;
	            $currentSum2 += $rBudget[$pName];
	            $pSeries1[] = $total;
	            $pSeries2[] = $rBudget[$pName];
	            $sumSeries1[] = $currentSum1;
	            $sumSeries2[] = $currentSum2;
	            $pNames[] = str_replace("&amp;", "&", "$pName");
	            $i++;
	        }
	    
	        $array = array();
	        $array['chart'] = null;
	        if($action == "getSpecialProjectParetoData"){
	            $array['title'] = array('text' => "Chart of ".(REPORTING_YEAR)." Funds for GRAND Projects");
	        }
	        else{
	            $array['title'] = array('text' => "Chart of ".(REPORTING_YEAR)." Funds per Person for GRAND Projects");
	        }
	        $array['xAxis'] = array('categories' => $pNames,
	                                'labels' => array('rotation' => -45,
	                                                  'align' => "right",
	                                                  'x' => 5,
	                                                  'minPadding' => 1,
	                                                  'maxPadding' => 1,
	                                                  'style' => array('fontSize' =>"10px",
	                                                                   'fontFamily' => "Verdana, sans-serif")
	                                                  )
	                               );
	        if($action == "getSpecialProjectParetoData"){
	            $array['yAxis'] = array(array('title' => array('text' => "Allocated Funds ($)")),
	                                    array('min' => 0,
	                                          'opposite' => true,
	                                          'title' => array('text' => "Cumulative Allocated Funds ($)")
	                                         )
	                                   );
	        }
	        else{
	            $array['yAxis'] = array(array('title' => array('text' => "Allocated Funds ($)")),
	                                   );
	        }
	        $array['legend'] = array('enabled' => true);
	        if($action == "getSpecialProjectParetoData"){
	            $array['series'] = array(array('name' => "Allocated Funds",
	                                           'data' => $pSeries1,
	                                           'dataLabels' => array('enabled' => true,
	                                                                 'rotation' => -45,
	                                                                 'color' => "#000000",
	                                                                 'align' => "center",
	                                                                 'x' => 0,
	                                                                 'y' => -10,
	                                                                 'style' => array('fontSize' => "10px",
	                                                                                  'fontFamily' => "Verdana, sans-serif")
	                                                                )
	                                          ),
	                                     array('name' => "Requested Funds",
	                                           'data' => $pSeries2,
	                                           'dataLabels' => array('enabled' => true,
	                                                                 'rotation' => -45,
	                                                                 'color' => "#000000",
	                                                                 'align' => "center",
	                                                                 'x' => 0,
	                                                                 'y' => -10,
	                                                                 'style' => array('fontSize' => "10px",
	                                                                                  'fontFamily' => "Verdana, sans-serif")
	                                                                )
	                                          ),
	                                     array('type' => "line",
	                                           'name' => "Total Allocated Funds",
	                                           'yAxis' => 1,
	                                           'data' => $sumSeries1
	                                          ),
	                                     array('type' => "line",
	                                           'name' => "Total Requested Funds",
	                                           'yAxis' => 1,
	                                           'data' => $sumSeries2
	                                          )
	                                    );
	        }
	        else{
	            $array['series'] = array(array('name' => "Allocated Funds per Person",
	                                           'data' => $pSeries1,
	                                           'dataLabels' => array('enabled' => true,
	                                                                 'rotation' => -45,
	                                                                 'color' => "#000000",
	                                                                 'align' => "center",
	                                                                 'x' => 0,
	                                                                 'y' => -10,
	                                                                 'style' => array('fontSize' => "10px",
	                                                                                  'fontFamily' => "Verdana, sans-serif")
	                                                                )
	                                          ),
	                                     array('name' => "Requested Funds per Person",
	                                           'data' => $pSeries2,
	                                           'dataLabels' => array('enabled' => true,
	                                                                 'rotation' => -45,
	                                                                 'color' => "#000000",
	                                                                 'align' => "center",
	                                                                 'x' => 0,
	                                                                 'y' => -10,
	                                                                 'style' => array('fontSize' => "10px",
	                                                                                  'fontFamily' => "Verdana, sans-serif")
	                                                                )
	                                          )
	                                    );
	        }

            header("Content-Type: application/json");
            echo json_encode($array);
            exit;
        }
        return true;
	}
	
	static function getSpecialUniversityParetoData($action, $article){
	    global $wgServer, $wgScriptPath;
	    $me = Person::newFromWgUser();
	    if(($action == "getSpecialUniversityParetoData" || $action == "getSpecialUniversityAvgParetoData") && $me->isRoleAtLeast(MANAGER)){
	        $people = Person::getAllPeople();
	        $pNames = array();
	        $pBudget = array();
	        $rBudget = array();
	        
	        $pUniCounts = array();
	        $rUniCounts = array();
	        foreach($people as $person){
	            if(!$person->isRoleDuring(PNI, (REPORTING_YEAR-1).REPORTING_CYCLE_START_MONTH, (REPORTING_YEAR-1).REPORTING_CYCLE_END_MONTH) && 
	               !$person->isRoleDuring(CNI, (REPORTING_YEAR-1).REPORTING_CYCLE_START_MONTH, (REPORTING_YEAR-1).REPORTING_CYCLE_END_MONTH)){
	                continue;
	            }
                $abudget = $person->getAllocatedBudget(REPORTING_YEAR-1);
	            $rbudget = $person->getRequestedBudget(REPORTING_YEAR-1);
                if($abudget != null){
                    $totalBudget = $abudget->copy()->rasterize()->where(COL_TOTAL)->select(ROW_TOTAL);
                    if($totalBudget->nRows() > 0 && $totalBudget->nCols() > 0){
                        $value = $totalBudget->toString();
                        $value = (int)str_replace(',', '', str_replace('$', '', $value));
                        $aTotal = $value;
                    }
                    else{
                        $aTotal = 0;
                    }
                }
                else{
                    $aTotal = 0;
                }
                if($rbudget != null){
                    $totalBudget = $rbudget->copy()->rasterize()->where(COL_TOTAL)->select(ROW_TOTAL);
                    if($totalBudget->nRows() > 0 && $totalBudget->nCols() > 0){
                        $value = $totalBudget->toString();
                        $value = (int)str_replace(',', '', str_replace('$', '', $value));
                        $rTotal = $value;
                    }
                    else{
                        $rTotal = 0;
                    }
                }
                else{
                    $rTotal = 0;
                }
                $university = $person->getUniversity();
                $uni = $university['university'];
                if($uni == ''){
                    $uni = 'Unknown';
                }
                @$pBudget[$uni] += $aTotal;
                @$rBudget[$uni] += $rTotal;
                if($abudget != null){
                    @$pUniCounts[$uni]++;
                }
                if($rbudget != null){
                    @$rUniCounts[$uni]++;
                }
	        }
	        if($action == "getSpecialUniversityAvgParetoData"){
	            foreach($pBudget as $uni => $amount){
	                @$pBudget[$uni] = round($amount/max(1, $pUniCounts[$uni]));
	                @$rBudget[$uni] = round($rBudget[$uni]/max(1, $rUniCounts[$uni]));
	            }
	        }
	        
	        $names = array();
	        $i=1;
	        foreach($pBudget as $uni => $p){
	            $names[$uni] = "University $i";
	            $i++;
	        }
	        
	        asort($pBudget, SORT_NUMERIC);
	        $pBudget = array_reverse($pBudget, true);
	        $sumSeries1 = array();
	        $sumSeries2 = array();
            $pSeries1 = array();
            $pSeries2 = array();
            $currentSum1 = 0;
            $currentSum2 = 0;
	        foreach($pBudget as $pName => $total){
	            $currentSum1 += $total;
	            $pSeries1[] = $total;
	            $sumSeries1[] = $currentSum1;
	            
	            $currentSum2 += $rBudget[$pName];
	            $pSeries2[] = $rBudget[$pName];
	            $sumSeries2[] = $currentSum2;
	            $pNames[] = str_replace("universite", "uni", str_replace("University", "Uni", str_replace("&amp;", "&", "$pName")));
	        }
	    
	        $array = array();
	        $array['chart'] = null;
	        if($action == "getSpecialUniversityParetoData"){
	            $array['title'] = array('text' => "Chart of ".(REPORTING_YEAR)." Funds for GRAND Universities");
	        }
	        else{
	            $array['title'] = array('text' => "Chart of ".(REPORTING_YEAR)." Funds per Person for GRAND Universities");
	        }
	        $array['xAxis'] = array('categories' => $pNames,
	                                'labels' => array('rotation' => -45,
	                                                  'align' => "right",
	                                                  'x' => 5,
	                                                  'minPadding' => 1,
	                                                  'maxPadding' => 1,
	                                                  'style' => array('fontSize' =>"10px",
	                                                                   'fontFamily' => "Verdana, sans-serif")
	                                                  )
	                               );
	        if($action == "getSpecialUniversityParetoData"){
	            $array['yAxis'] = array(array('title' => array('text' => "Allocated Funds ($)")),
	                                    array('min' => 0,
	                                          'opposite' => true,
	                                          'title' => array('text' => "Cumulative Allocated Funds ($)")
	                                         )
	                                   );
	        }
	        else{
	            $array['yAxis'] = array(array('title' => array('text' => "Allocated Funds ($)"))
	                                   );
	        }
	        $array['legend'] = array('enabled' => true);
	        if($action == "getSpecialUniversityParetoData"){
	            $array['series'] = array(array('name' => "Allocated Funds",
	                                           'data' => $pSeries1,
	                                           'dataLabels' => array('enabled' => true,
	                                                                 'rotation' => -45,
	                                                                 'color' => "#000000",
	                                                                 'align' => "center",
	                                                                 'x' => 0,
	                                                                 'y' => -10,
	                                                                 'style' => array('fontSize' => "10px",
	                                                                                  'fontFamily' => "Verdana, sans-serif")
	                                                                )
	                                          ),
	                                     array('name' => "Requested Funds",
	                                           'data' => $pSeries2,
	                                           'dataLabels' => array('enabled' => true,
	                                                                 'rotation' => -45,
	                                                                 'color' => "#000000",
	                                                                 'align' => "center",
	                                                                 'x' => 0,
	                                                                 'y' => -10,
	                                                                 'style' => array('fontSize' => "10px",
	                                                                                  'fontFamily' => "Verdana, sans-serif")
	                                                                )
	                                          ),
	                                     array('type' => "line",
	                                           'name' => "Total Allocated Funds",
	                                           'yAxis' => 1,
	                                           'data' => $sumSeries1
	                                          ),
	                                     array('type' => "line",
	                                           'name' => "Total Requested Funds",
	                                           'yAxis' => 1,
	                                           'data' => $sumSeries2
	                                          )
	                                    );
	        }
	        else{
	            $array['series'] = array(array('name' => "Allocated Funds per Person",
	                                           'data' => $pSeries1,
	                                           'dataLabels' => array('enabled' => true,
	                                                                 'rotation' => -45,
	                                                                 'color' => "#000000",
	                                                                 'align' => "center",
	                                                                 'x' => 0,
	                                                                 'y' => -10,
	                                                                 'style' => array('fontSize' => "10px",
	                                                                                  'fontFamily' => "Verdana, sans-serif")
	                                                                )
	                                          ),
	                                     array('name' => "Requested Funds per Person",
	                                           'data' => $pSeries2,
	                                           'dataLabels' => array('enabled' => true,
	                                                                 'rotation' => -45,
	                                                                 'color' => "#000000",
	                                                                 'align' => "center",
	                                                                 'x' => 0,
	                                                                 'y' => -10,
	                                                                 'style' => array('fontSize' => "10px",
	                                                                                  'fontFamily' => "Verdana, sans-serif")
	                                                                )
	                                          )
	                                    );
	        }

            header("Content-Type: application/json");
            echo json_encode($array);
            exit;
        }
        return true;
	}
}
?>
