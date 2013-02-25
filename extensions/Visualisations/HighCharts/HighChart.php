<?php

require_once("SpecialHighChart.php");

class HighChart extends Visualisation {
    
    static $a = 0;
    var $url = "";
    var $width = "500px";
    var $height = "500px";
    
    function HighChart($url){
        $this->url = $url;
        self::Visualisation();
    }
    
    static function init(){
        global $wgOut, $wgServer, $wgScriptPath;
        $wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualisations/HighCharts/js/highcharts.js" type="text/javascript" charset="utf-8"></script>');
        $wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualisations/HighCharts/js/modules/exporting.js" type="text/javascript" charset="utf-8"></script>');
    }

    function show(){
        global $wgOut, $wgServer, $wgScriptPath;
        $string = "<div style='height:".($this->height).";width:".($this->width).";float:left;' class='highChart' id='vis{$this->index}'>
                   </div>";
        $string .= <<<EOF
<script type='text/javascript'>
    
    function showVis{$this->index}(){
        $.get('{$this->url}', function(data){
            $("#vis{$this->index}").empty();
            data.chart = {
                "renderTo": "vis{$this->index}",
                "type": "column",
                "margin": [
                    50,
                    60,
                    200,
                    60
                ]
            };
            var chart = new Highcharts.Chart(data);
        });
    }
    showVis{$this->index}();
</script>
EOF;
        return $string;
    }
}


?>
