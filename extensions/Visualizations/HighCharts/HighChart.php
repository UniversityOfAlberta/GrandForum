<?php

class HighChart extends Visualization {
    
    static $a = 0;
    var $url = "";
    var $width = "500px";
    var $height = "500px";
    
    function HighChart($url){
        $this->url = $url;
        self::Visualization();
    }
    
    static function init(){
        global $wgOut, $wgServer, $wgScriptPath;
        $wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualizations/HighCharts/js/highcharts.js" type="text/javascript" charset="utf-8"></script>');
        $wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualizations/HighCharts/js/modules/exporting.js" type="text/javascript" charset="utf-8"></script>');
    }

    function show(){
        global $wgOut, $wgServer, $wgScriptPath;
        $string = "<div style='height:".($this->height).";width:".($this->width).";float:left;' class='highChart' id='vis{$this->index}'>
                   </div>";
        $string .= <<<EOF
<script type='text/javascript'>
    var chart{$this->index};
    var data{$this->index};
    function showVis{$this->index}(){
        $("#vis{$this->index}").html('Loading...');
        $.get('{$this->url}', function(data){
            $("#vis{$this->index}").empty();
            data.chart = {
                "renderTo": "vis{$this->index}",
                "type": "column"
            };
            data{$this->index} = data;
            chart{$this->index} = new Highcharts.Chart(data{$this->index});
        });
    }
    showVis{$this->index}();
</script>
EOF;
        return $string;
    }
}


?>
