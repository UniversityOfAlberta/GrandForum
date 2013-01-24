<?php
$dir = dirname(__FILE__) . '/';
$wgSpecialPages['SpecialHighChart'] = 'SpecialHighChart';
$wgExtensionMessagesFiles['SpecialHighChart'] = $dir . 'SpecialHighChart.i18n.php';

$wgHooks['UnknownAction'][] = 'SpecialHighChart::getSpecialProjectParetoData';

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
	    $wgOut->addHTML($string);
	}
	
	static function getSpecialProjectParetoData($action, $article){
	    global $wgServer, $wgScriptPath;
	    if($action == "getSpecialProjectParetoData"){
	        $projects = Project::getAllProjects();
	        $pNames = array();
	        $pBudget = array();
	        foreach($projects as $project){
	            $budget = $project->getAllocatedBudget(REPORTING_YEAR-1);
	            if($budget != null){
	                $totalBudget = $budget->copy()->rasterize()->select(CUBE_TOTAL)->where(CUBE_TOTAL);
	                if($totalBudget->nRows() > 0 && $totalBudget->nCols() > 0){
	                    $value = $totalBudget->toString();
	                    $value = (int)str_replace(',', '', str_replace('$', '', $value));
	                    $total = $value;
	                }
	                else{
	                    $total = 0;
	                }
	            }
	            else{
	                $total = 0;
	            }
	            $pBudget[$project->getName()] = $total;
	        }
	        asort($pBudget, SORT_NUMERIC);
	        $pBudget = array_reverse($pBudget, true);
	        $sumSeries = array();
            $pSeries = array();
            $currentSum = 0;
            $i = 1;
	        foreach($pBudget as $pName => $total){
	            $currentSum += $total;
	            $pSeries[] = $total;
	            $sumSeries[] = $currentSum;
	            $pNames[] = "Project $i";
	            $i++;
	        }
	    
	        $array = array();
	        $array['chart'] = null;
	        $array['title'] = array('text' => "Pareto Chart of GRAND Projects");
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
	        $array['series'] = array(array('name' => "Project Funding",
	                                       'data' => $pSeries,
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
	                                       'name' => "Funding Total",
	                                       'yAxis' => 1,
	                                       'data' => $sumSeries
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
