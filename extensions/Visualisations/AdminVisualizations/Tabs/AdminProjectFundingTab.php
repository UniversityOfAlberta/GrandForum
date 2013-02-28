<?php

$wgHooks['UnknownAction'][] = 'AdminProjectFundingTab::getAdminProjectData';
$wgHooks['UnknownAction'][] = 'AdminProjectFundingTab::getAdminProjectYearlyData';

class AdminProjectFundingTab extends AbstractTab {
	
	function AdminProjectFundingTab(){
        parent::AbstractTab("Project Funding");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath;
	    $chart1 = new HighChart("{$wgServer}{$wgScriptPath}/index.php?action=getAdminProjectData");
	    $chart1->height = "800px";
	    $chart1->width = "100%";
	    $this->html .= $chart1->show();

	    $chart2 = new HighChart("{$wgServer}{$wgScriptPath}/index.php?action=getAdminProjectAvgData");
	    $chart2->height = "800px";
	    $chart2->width = "100%";
	    $this->html .= $chart2->show();
	    
	    $chart3 = new HighChart("{$wgServer}{$wgScriptPath}/index.php?action=getAdminProjectYearlyData");
	    $chart3->height = "800px";
	    $chart3->width = "100%";
	    $this->html .= $chart3->show();
	    
	    $this->html .= "<script type='text/javascript'>
            $('#adminVis').bind('tabsselect', function(event, ui) {
                if(ui.panel.id == 'project-funding'){
                    $('div#vis{$chart1->index}').empty();
                    $('div#vis{$chart2->index}').empty();
                    $('div#vis{$chart3->index}').empty();
                    setTimeout(function(){
                        if(data{$chart1->index} != undefined){
                            data{$chart1->index}.chart.width = $('#vis{$chart1->index}').width()-1;
                        }
                        if(data{$chart2->index} != undefined){
                            data{$chart2->index}.chart.width = $('#vis{$chart2->index}').width()-1;
                        }
                        if(data{$chart3->index} != undefined){
                            data{$chart3->index}.chart.width = $('#vis{$chart3->index}').width()-1;
                        }
                        
                        if(chart{$chart1->index} != undefined){
                            chart{$chart1->index} = new Highcharts.Chart(data{$chart1->index});
                        }
                        if(chart{$chart2->index} != undefined){
                            chart{$chart2->index} = new Highcharts.Chart(data{$chart2->index});
                        }
                        if(chart{$chart3->index} != undefined){
                            chart{$chart3->index} = new Highcharts.Chart(data{$chart3->index});
                        }
                    }, 10);
                }
            });
	    </script>";
	}
	
	static function getAdminProjectData($action, $article){
	    global $wgServer, $wgScriptPath;
	    $me = Person::newFromWgUser();
	    if(($action == "getAdminProjectData" || $action == "getAdminProjectAvgData") && $me->isRoleAtLeast(MANAGER)){
	        session_write_close();
	        $projects = Project::getAllProjectsDuring((REPORTING_YEAR-1).REPORTING_CYCLE_START_MONTH, (REPORTING_YEAR-1).REPORTING_CYCLE_END_MONTH);
	        $pNames = array();
	        $pBudget = array();
	        $rBudget = array();
	        $nProducts = array();
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
	            if($action == "getAdminProjectData"){
	                $nProducts[$project->getName()] = count($project->getPapers('all', (REPORTING_YEAR).REPORTING_CYCLE_START_MONTH, (REPORTING_YEAR).REPORTING_CYCLE_END_MONTH));
	            }
	            if($action == "getAdminProjectAvgData"){
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
	            $pSeries3[] = @$nProducts[$pName];
	            $sumSeries1[] = $currentSum1;
	            $sumSeries2[] = $currentSum2;
	            $pNames[] = str_replace("&amp;", "&", "$pName");
	            $i++;
	        }
	    
	        $array = array();
	        $array['chart'] = null;
	        if($action == "getAdminProjectData"){
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
	        if($action == "getAdminProjectData"){
	            $array['yAxis'] = array(array('title' => array('text' => "Allocated Funds ($)")),
	                                    array('min' => 0,
	                                          'opposite' => true,
	                                          'title' => array('text' => "Cumulative Allocated Funds ($)")
	                                         ),
	                                    array('min' => 0,
	                                          'opposite' => true,
	                                          'title' => array('text' => "# Products")
	                                         )
	                                   );
	        }
	        else{
	            $array['yAxis'] = array(array('title' => array('text' => "Allocated Funds ($)")),
	                                   );
	        }
	        $array['legend'] = array('enabled' => true);
	        if($action == "getAdminProjectData"){
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
	                                          ),
	                                     array('name' => "# Products",
	                                           'yAxis' => 2,
	                                           'data' => $pSeries3,
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
	        else if($action == "getAdminProjectAvgData"){
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
	
	static function getAdminProjectYearlyData($action, $article){
	    global $wgServer, $wgScriptPath;
	    $me = Person::newFromWgUser();
	    if($action == "getAdminProjectYearlyData" && $me->isRoleAtLeast(MANAGER)){
	        session_write_close();
	        $projects = Project::getAllProjects();
	        $array = array();
	        $array['chart'] = null;
	        $array['title'] = array('text' => "Chart of Yearly Allocated Funds for GRAND Projects");
	        $startingYear = 2010;
	        $years = array();
	        for($i=$startingYear;$i <= REPORTING_YEAR; $i++){
	            $years[] = "$i";
	        }
	        $array['xAxis'] = array('categories' => $years,
	                                'labels' => array('rotation' => -45,
	                                                  'align' => "right",
	                                                  'x' => 5,
	                                                  'minPadding' => 1,
	                                                  'maxPadding' => 1,
	                                                  'style' => array('fontSize' =>"10px",
	                                                                   'fontFamily' => "Verdana, sans-serif")
	                                                  )
	                               );
	        $array['yAxis'] = array(array('min' => 0,
                                          'title' => array('text' => "Allocated Funds ($)")
                                         )
	                                   );
	        foreach($projects as $project){
	            $series = array();
	            for($i=$startingYear;$i <= REPORTING_YEAR;$i++){
	                $aTotal = 0;
	                $abudget = $project->getAllocatedBudget($i-1);
	                $totalBudget = $abudget->copy()->rasterize()->select(CUBE_TOTAL)->where(CUBE_TOTAL);
	                if($totalBudget->nRows() > 0 && $totalBudget->nCols() > 0){
	                    $value = $totalBudget->toString();
	                    $value = (int)str_replace(',', '', str_replace('$', '', $value));
	                    $aTotal = $value;
	                }
	                $series[] = $aTotal;
	            }
                $array['series'][] = array('type' => "line",
                                           'name' => "{$project->getName()}",
                                           'data' => $series
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
