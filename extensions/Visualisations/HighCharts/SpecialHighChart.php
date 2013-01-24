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
		SpecialPage::SpecialPage("SpecialHighChart", HQP.'+', true, 'runSpecialHighChart');
	}
	
	function run(){
	    global $wgOut, $wgServer, $wgScriptPath;
	    $chart = new HighChart("{$wgServer}{$wgScriptPath}/index.php?action=getSpecialProjectParetoData");
	    $chart->height = "800px";
	    $chart->width = "100%";
	    $string = $chart->show();
	    
	    $chart = new HighChart("{$wgServer}{$wgScriptPath}/index.php?action=getSpecialUniversityParetoData");
	    $chart->height = "800px";
	    $chart->width = "100%";
	    $string .= $chart->show();
	    
	    $wgOut->addHTML($string);
	}
	
	static function getSpecialProjectParetoData($action, $article){
	    global $wgServer, $wgScriptPath;
	    if($action == "getSpecialProjectParetoData"){
	        $projects = Project::getAllProjectsDuring((REPORTING_YEAR-1).REPORTING_CYCLE_START_MONTH, (REPORTING_YEAR-1).REPORTING_CYCLE_END_MONTH);
	        $pNames = array();
	        $pBudget = array();
	        $rBudget = array();
	        foreach($projects as $project){
	            $abudget = $project->getAllocatedBudget(REPORTING_YEAR-1);
	            $rbudget = $project->getRequestedBudget(REPORTING_YEAR-1);
	            if($abudget != null){
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
	            $pNames[] = "Project $i";
	            $i++;
	        }
	    
	        $array = array();
	        $array['chart'] = null;
	        $array['title'] = array('text' => "Chart of ".(REPORTING_YEAR-1)." Funds for GRAND Projects");
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
	        $array['yAxis'] = array(array(
	                                      'title' => array('text' => "Allocated Funds ($)")
	                                     ),
	                                array('min' => 0,
	                                      'opposite' => true,
	                                      'title' => array('text' => "Cumulative Allocated Funds ($)")
	                                     )
	                               );
	        $array['legend'] = array('enabled' => true);
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
	                                 array('type' => "line",
	                                       'name' => "Total Allocated Funds",
	                                       'yAxis' => 1,
	                                       'data' => $sumSeries1
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
	                                       'name' => "Total Requested Funds",
	                                       'yAxis' => 1,
	                                       'data' => $sumSeries2
	                                      )
	                                );

            header("Content-Type: application/json");
            echo json_encode($array);
            exit;
        }
        return true;
	}
	
	static function getSpecialUniversityParetoData($action, $article){
	    global $wgServer, $wgScriptPath;
	    if($action == "getSpecialUniversityParetoData"){
	        $people = Person::getAllPeople();
	        $pNames = array();
	        $pBudget = array();
	        $rBudget = array();
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
                    $totalBudget = $abudget->copy()->rasterize()->where(COL_TOTAL)->select(ROW_TOTAL);
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
	        }
	        asort($pBudget, SORT_NUMERIC);
	        $pBudget = array_reverse($pBudget, true);
	        $sumSeries1 = array();
	        $sumSeries2 = array();
            $pSeries1 = array();
            $pSeries2 = array();
            $currentSum1 = 0;
            $currentSum2 = 0;
            $i = 1;
	        foreach($pBudget as $pName => $total){
	            $currentSum1 += $total;
	            $pSeries1[] = $total;
	            $sumSeries1[] = $currentSum1;
	            
	            $currentSum2 += $rBudget[$pName];
	            $pSeries2[] = $rBudget[$pName];
	            $sumSeries2[] = $currentSum2;
	            if($pName != 'Unknown'){
	                $pNames[] = "University $i";
	                $i++;
	            }
	            else{
	                $pNames[] = 'Unknown';
	            }
	        }
	    
	        $array = array();
	        $array['chart'] = null;
	        $array['title'] = array('text' => "Chart of ".(REPORTING_YEAR-1)." Funds for GRAND Universities");
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
	        $array['yAxis'] = array(array(
	                                      'title' => array('text' => "Allocated Funds ($)")
	                                     ),
	                                array('min' => 0,
	                                      'opposite' => true,
	                                      'title' => array('text' => "Cumulative Allocated Funds ($)")
	                                     )
	                               );
	        $array['legend'] = array('enabled' => true);
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
	                                 array('type' => "line",
	                                       'name' => "Total Allocated Funds",
	                                       'yAxis' => 1,
	                                       'data' => $sumSeries1
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
	                                       'name' => "Total Requested Funds",
	                                       'yAxis' => 1,
	                                       'data' => $sumSeries2
	                                      )
	                                );

            header("Content-Type: application/json");
            echo json_encode($array);
            exit;
        }
        return true;
	}
}
?>
