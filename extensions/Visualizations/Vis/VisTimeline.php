<?php

class VisTimeline extends Visualization {
    
    static $a = 0;
    var $url = "";
    var $width = "100%";
    var $height = "500px";
    
    function __construct($url){
        $this->url = $url;
        parent::__construct();
    }
    
    static function init(){
        global $wgOut, $wgServer, $wgScriptPath, $visualizations;
        $wgOut->addScript("<script src='$wgServer$wgScriptPath/extensions/Visualizations/Vis/js/vis.min.js' type='text/javascript'></script>");
        $wgOut->addScript("<link href='$wgServer$wgScriptPath/extensions/Visualizations/Vis/js/vis.min.css' rel='stylesheet' type='text/css' />");
        $wgOut->addScript("<script src='$wgServer$wgScriptPath/extensions/Visualizations/Vis/js/timeline/timeline.js' type='text/javascript'></script>");
        $wgOut->addScript("<link href='$wgServer$wgScriptPath/extensions/Visualizations/Vis/js/timeline/timeline.css' rel='stylesheet' type='text/css' />");
    }

    function show(){
        global $wgOut, $wgServer, $wgScriptPath;
        $string = "<div style='display:inline-block;margin-right:25px;' id='vis{$this->index}Filter'></div><div>Use the mouse wheel to zoom in and out.  You can drag left or right to move the time scale.</div><div class='VisTimeline' id='vis{$this->index}'></div>";
        $string .= <<<EOF
<script type='text/javascript'>
    
    function onLoad{$this->index}(){
        $("#vis{$this->index}").timeline({url: "{$this->url}", 
                                          width: "{$this->width}", 
                                          height: "{$this->height}"
                                         });
    }
            
    $(document).ready(function(){
        if($('#vis{$this->index}:visible').length > 0){
            onLoad{$this->index}();
        }
    });

</script>
EOF;
        return $string;
    }
}


?>
