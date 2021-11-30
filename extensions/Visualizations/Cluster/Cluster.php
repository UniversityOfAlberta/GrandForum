<?php

class Cluster extends Visualization {
    
    static $a = 0;
    var $url = "";
    var $width = "800";
    var $height = "800";
    var $sizeLabel = "";
    var $countLabel = "";
    
    function __construct($url, $sizeLabel="Size", $countLabel="Count"){
        $this->url = $url;
        $this->sizeLabel = $sizeLabel;
        $this->countLabel = $countLabel;
        self::Visualization();
    }
    
    static function init(){
        global $wgOut, $wgServer, $wgScriptPath, $visualizations;
        $wgOut->addScript("<script src='$wgServer$wgScriptPath/extensions/Visualizations/Cluster/js/cluster.js' type='text/javascript'></script>");
    }

    function show(){
        global $wgOut, $wgServer, $wgScriptPath;
        $string = "<div style='height:".($this->height)."px;width:".($this->width)."px;' class='Cluster' id='vis{$this->index}'></div>";
        $string .= <<<EOF
<script type='text/javascript'>
    
    function onLoad{$this->index}(){
        $('#vis{$this->index}').empty();
        $('#vis{$this->index}').cluster({url: '{$this->url}'});
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
