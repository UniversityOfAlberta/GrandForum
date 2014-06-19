<?php

class TreeMap extends Visualisation {
    
    static $a = 0;
    var $url = "";
    var $width = "500";
    var $height = "500";
    
    function TreeMap($url){
        $this->url = $url;
        self::Visualisation();
    }
    
    static function init(){
        global $wgOut, $wgServer, $wgScriptPath, $visualisations;
        $wgOut->addScript("<script src='$wgServer$wgScriptPath/extensions/Visualisations/TreeMap/js/treemap.js' type='text/javascript'></script>");
        $wgOut->addScript('<style rel="stylesheet" type="text/css">

}</style>');
    }

    function show(){
        global $wgOut, $wgServer, $wgScriptPath;
        $string = "<div style='height:".($this->height)."px;width:".($this->width)."px;'><div id='vis{$this->index}options' style='float:right;'>
          <label><input type='radio' checked='checked' value='size' name='{$this->index}mode'> Size</label>
          <label><input type='radio' value='count' name='{$this->index}mode'> Count</label>
        </div>";
        $string .= "<div style='height:".($this->height)."px;width:".($this->width)."px;' class='treeMap' id='vis{$this->index}'></div></div>";
        $string .= <<<EOF
<script type='text/javascript'>
    
    function onLoad{$this->index}(){
        $('#vis{$this->index}').treemap({url: '{$this->url}'});
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
