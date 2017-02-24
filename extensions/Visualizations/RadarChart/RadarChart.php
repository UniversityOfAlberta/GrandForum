<?php

class RadarChart extends Visualization {

    var $data = array();

    /**
     * Creates a new Radar graph visualization
     * @param Array $data The data
     */
    function RadarChart($data){
        $this->data = $data;
        self::Visualization();
    }

    static function init(){
        global $wgOut, $wgServer, $wgScriptPath;
        $wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualizations/RadarChart/radarchart/radarchart.js"></script>');

    }

    function show(){
        global $wgOut, $wgServer, $wgScriptPath;
        $formattedData = $this->formatData($this->data);
        $labels = $formattedData[0];
        $data = $formattedData[1];
        $string ="
                <div id='radarchart{$this->index}' style='width: 50%'>
                        <canvas id='canvas' height='250' width='320'></canvas>
                </div>
<script src='".$wgServer.$wgScriptPath."/extensions/Visualizations/RadarChart/radarchart/radarchart.js'></script>

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
                if($('#radarchart{$this->index}').is(':visible')){
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
