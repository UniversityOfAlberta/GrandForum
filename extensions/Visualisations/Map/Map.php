<?php

class Map extends Visualisation {
    
    static $a = 0;
    var $url = "";
    var $width = "500px";
    var $height = "500px";
    
    function Map($url){
        $this->url = $url;
        self::Visualisation();
    }
    
    static function init(){
        global $wgOut, $wgServer, $wgScriptPath;
        $wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualisations/Map/js/jquery-jvectormap.min.js" type="text/javascript" charset="utf-8"></script>');
        $wgOut->addScript('<link href="'.$wgServer.$wgScriptPath.'/extensions/Visualisations/Map/style/jquery-jvectormap.css" type="text/css" rel="stylesheet" charset="utf-8"></script>');
        $wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualisations/Map/js/canada.js" type="text/javascript" charset="utf-8"></script>');
    }

    function show(){
        global $wgOut, $wgServer, $wgScriptPath;
        $string = "<div style='height:".($this->height).";width:".($this->width).";' class='map' id='vis{$this->index}'>
                   </div>";
        $string .= <<<EOF
<script type='text/javascript'>
    
    function showVis{$this->index}(){
        $("#vis{$this->index}").html('Loading...');
        $.get('{$this->url}', function(data){
            $("#vis{$this->index}").empty();
            $("#vis{$this->index}").vectorMap({
                                 map:'ca_lcc_en',
                                 series: {regions:[{ values: data.values ,scale: ["#153a50","#69b3ff"]}]},
                                 regionStyle: {
                                      initial: {
                                        fill: '#888888',
                                        "fill-opacity": 1,
                                        stroke: 'none',
                                        "stroke-width": 0,
                                        "stroke-opacity": 1
                                      },
                                      hover: {
                                        "fill-opacity": 0.8
                                      },
                                      selected: {
                                        fill: 'yellow'
                                      },
                                      selectedHover: {
                                      }
                                    },
                                 onRegionLabelShow: function(event, label, code){
                                    if(data.text[code] != undefined){
                                        label.html(
                                          '<b>'+label.html()+'</b>'+
                                          '<br />' + data.text[code]
                                        );
                                    }
                                    else{
                                        label.html(
                                          '<b>'+label.html()+'</b>'
                                        );
                                    }
                                  }
                               }
                               );
        });
    }
    showVis{$this->index}();
</script>
EOF;
        return $string;
    }
}


?>
