<?php

class Bar extends Visualization {
    
    var $data = array();
    
    /**
     * Creates a new Bar visualization
     * @param Array $data The data
     */
    function Bar($data){
        $this->data = $data;
        self::Visualization();
    }
    
    static function init(){
        global $wgOut, $wgServer, $wgScriptPath;
        $wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualizations/Bar/bar/Chart.js"></script>');

    }

    function show(){
        global $wgOut, $wgServer, $wgScriptPath;
	$formattedData = $this->formatData($this->data);
	$labels = $formattedData[0];
	$data = $formattedData[1];
	$string ="
		<div style='width: 50%'>
		<h1>Citations Per Year</h2>
			<canvas id='canvas' height='250' width='320'></canvas>
		</div>
<script src='".$wgServer.$wgScriptPath."/extensions/Visualizations/Bar/bar/Chart.js'></script>

	<script>
	var randomScalingFactor = function(){ return Math.round(Math.random()*100)};
	var barChartData = {
		labels : {$labels},
		datasets : [
			{
				fillColor : 'rgba(220,220,220,0.5)',
				strokeColor : 'rgba(220,220,220,0.8)',
				highlightFill: 'rgba(220,220,220,0.75)',
				highlightStroke: 'rgba(220,220,220,1)',
				data : {$data}
			},
		]
	}
	window.setTimeout(function(){
		var ctx = document.getElementById('canvas').getContext('2d');
		window.myBar = new Chart(ctx).Bar(barChartData,{
							responsive: false,
							scaleShowGridLines : false
							});
	}, 10000)
	</script>
	";
	return $string;
    }

    function formatData($data){
	$labelString = "[";
	$dataSetString = "[";
	$count = 0;
	while(list($key, $val) = each($data)){
	    $count++;
	    $labelString .="$key";
	    $dataSetString .= "$val";
	    if($count != count($data)){
	   	$labelString .= ",";
		$dataSetString .= ",";
	    }
	}
	$labelString .= "]";
	$dataSetString .= "]";
	return array($labelString, $dataSetString);
    }
	
}


?>
