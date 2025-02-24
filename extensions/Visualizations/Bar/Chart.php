<?php

class Bar extends Visualization {
    
    var $data = array();
    
    /**
     * Creates a new Bar visualization
     * @param Array $data The data
     */
    function __construct($data){
        $this->data = $data;
        parent::__construct();
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
		<div id='barchart{$this->index}' style='width: 50%'>
		<h1>Citations Per Year</h2>
			<canvas id='canvas' height='250' width='320'></canvas>
		</div>
<script src='".$wgServer.$wgScriptPath."/extensions/Visualizations/Bar/bar/Chart.js'></script>

	<script>
	window.onload = slide();

	function slide(){
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
	     var intervalId = setInterval(function(){
		if($('#barchart{$this->index}').is(':visible')){
		 var blank = document.createElement('canvas');
                 var canvas = document.getElementById('canvas');
		 blank.width = canvas.width;
		 blank.height = canvas.height;
		 var ctx = document.getElementById('canvas').getContext('2d');
		     window.myBar = new Chart(ctx).Bar(barChartData,{
							responsive: false,
							scaleShowGridLines : false
							});
		clearInterval(intervalId);
		intervalId = null;
		}
		
	     }, 100)

	     	
	}
	</script>
	";
	return $string;
    }

    function formatData($data){
	$labelString = "[";
	$dataSetString = "[";
	$count = 0;
	foreach($data as $key => $val){
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
