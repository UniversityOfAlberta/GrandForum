<?php

$wgHooks['UnknownAction'][] = 'AdminUniversityFundingTab::getAdminUniversityData';
$wgHooks['UnknownAction'][] = 'AdminUniversityFundingTab::getAdminUniversityYearlyData';

class AdminUniversityFundingTab extends AbstractTab {
	
	function AdminUniversityFundingTab(){
        parent::AbstractTab("University Funding");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath;
	    
	    $chart1 = new HighChart("{$wgServer}{$wgScriptPath}/index.php?action=getAdminUniversityData");
	    $chart1->height = "800px";
	    $chart1->width = "100%";
	    $this->html .= $chart1->show();
	    
	    $chart2 = new HighChart("{$wgServer}{$wgScriptPath}/index.php?action=getAdminUniversityAvgData");
	    $chart2->height = "800px";
	    $chart2->width = "100%";
	    $this->html .= $chart2->show();
	    
	    $chart3 = new HighChart("{$wgServer}{$wgScriptPath}/index.php?action=getAdminUniversityYearlyData");
	    $chart3->height = "800px";
	    $chart3->width = "100%";
	    $this->html .= $chart3->show();
	    $this->html .= "<script type='text/javascript'>
            $('#adminVis').bind('tabsselect', function(event, ui) {
                if(ui.panel.id == 'university-funding'){
                    $('div#vis{$chart1->index}').html('Loading...');
                    $('div#vis{$chart2->index}').html('Loading...');
                    $('div#vis{$chart3->index}').html('Loading...');
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
	
	static function getAdminUniversityData($action, $article){
	    global $wgServer, $wgScriptPath;
	    $me = Person::newFromWgUser();
	    if(($action == "getAdminUniversityData" || $action == "getAdminUniversityAvgData") && $me->isRoleAtLeast(MANAGER)){
	        session_write_close();
	        $people = Person::getAllPeople();
	        $pNames = array();
	        $pBudget = array();
	        $rBudget = array();
	        $nProducts = array();
	        
	        $pUniCounts = array();
	        $rUniCounts = array();
	        $papersDone = array();
	        foreach($people as $person){
	            $isPNI = $person->isRoleDuring(PNI, (REPORTING_YEAR-1).REPORTING_CYCLE_START_MONTH, (REPORTING_YEAR-1).REPORTING_CYCLE_END_MONTH);
	            $isCNI = $person->isRoleDuring(CNI, (REPORTING_YEAR-1).REPORTING_CYCLE_START_MONTH, (REPORTING_YEAR-1).REPORTING_CYCLE_END_MONTH);
	            $isHQP = $person->isRoleDuring(HQP, (REPORTING_YEAR-1).REPORTING_CYCLE_START_MONTH, (REPORTING_YEAR-1).REPORTING_CYCLE_END_MONTH);
	            if(!$isPNI && 
	               !$isCNI &&
	               !$isHQP){
	                continue;
	            }
	            $university = $person->getUniversity();
                $uni = $university['university'];
                if($uni == ''){
                    $uni = 'Unknown';
                }
	            if($isPNI || $isCNI){
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
                    @$pBudget[$uni] += $aTotal;
                    @$rBudget[$uni] += $rTotal;
                    if($abudget != null){
                        @$pUniCounts[$uni]++;
                    }
                    if($rbudget != null){
                        @$rUniCounts[$uni]++;
                    }
                }
                if($action == "getAdminUniversityData"){
                    $papers = $person->getPapersAuthored('all', (REPORTING_YEAR).REPORTING_CYCLE_START_MONTH, (REPORTING_YEAR).REPORTING_CYCLE_END_MONTH);
                    foreach($papers as $p){
                        if(!isset($papersDone[$p->getId()])){
                            @$nProducts[$uni]++;
                            $papersDone[$p->getId()] = true;
                        }
                    }
                }
	        }
	        
	        if($action == "getAdminUniversityAvgData"){
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
            $pSeries3 = array();
            $currentSum1 = 0;
            $currentSum2 = 0;
	        foreach($pBudget as $pName => $total){
	            $currentSum1 += $total;
	            $pSeries1[] = $total;
	            $sumSeries1[] = $currentSum1;
	            
	            $currentSum2 += $rBudget[$pName];
	            $pSeries2[] = $rBudget[$pName];
	            $pSeries3[] = @$nProducts[$pName];
	            $sumSeries2[] = $currentSum2;
	            $pNames[] = str_replace("&amp;", "&", "$pName");
	        }
	    
	        $array = array();
	        $array['chart'] = null;
	        if($action == "getAdminUniversityData"){
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
	        if($action == "getAdminUniversityData"){
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
	            $array['yAxis'] = array(array('title' => array('text' => "Allocated Funds ($)"))
	                                   );
	        }
	        $array['legend'] = array('enabled' => true);
	        if($action == "getAdminUniversityData"){
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
	                                           'data' => $pSeries2
	                                          ),
	                                     array('name' => "# Products",
	                                           'yAxis' => 2,
	                                           'data' => $pSeries3
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
	
	static function getAdminUniversityYearlyData($action, $article){
	    global $wgServer, $wgScriptPath;
	    $me = Person::newFromWgUser();
	    if($action == "getAdminUniversityYearlyData" && $me->isRoleAtLeast(MANAGER)){
	        session_write_close();
	        $people = Person::getAllPeople();
	        $array = array();
	        $array['chart'] = null;
	        $array['title'] = array('text' => "Chart of Yearly Allocated Funds for GRAND Universities");
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
	        $universities = array();
	        $series = array();
	        for($i=$startingYear;$i <= REPORTING_YEAR; $i++){
	            $pBudget = array();
	            foreach($people as $person){
	                $isPNI = $person->isRoleDuring(PNI, ($i-1).REPORTING_CYCLE_START_MONTH, ($i-1).REPORTING_CYCLE_END_MONTH);
	                $isCNI = $person->isRoleDuring(CNI, ($i-1).REPORTING_CYCLE_START_MONTH, ($i-1).REPORTING_CYCLE_END_MONTH);
	                $university = $person->getUniversity();
                    $uni = $university['university'];
                    if($uni == ''){
                        $uni = 'Unknown';
                    }
                    $universities[$uni] = true;
	                if($isPNI || 
	                   $isCNI){
                        $abudget = $person->getAllocatedBudget($i-1);
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
                        @$pBudget[$uni] += $aTotal;
                    }
                    else{
                        @$pBudget[$uni] += 0;
                    }
	            }
	            foreach($pBudget as $pName => $total){
	                $series[$pName][] = $total;
	            }
	        }
	        
	        foreach($universities as $uni => $val){
	            $array['series'][] = array('type' => "line",
                                           'name' => str_replace("&amp;", "&", "{$uni}"),
                                           'data' => $series[$uni]
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
