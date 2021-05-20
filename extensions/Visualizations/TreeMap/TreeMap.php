<?php

class TreeMap extends Visualization {
    
    static $a = 0;
    var $url = "";
    var $sizeLabel;
    var $countLabel;
    var $sizeUnit;
    var $countUnit;
    var $width = "500";
    var $height = "500";
    
    function TreeMap($url, $sizeLabel="Size", $countLabel="Count", $sizeUnit="", $countUnit=""){
        $this->url = $url;
        $this->sizeLabel = $sizeLabel;
        $this->countLabel = $countLabel;
        $this->sizeUnit = $sizeUnit;
        $this->countUnit = $countUnit;
        self::Visualization();
    }
    
    static function init(){
        global $wgOut, $wgServer, $wgScriptPath, $visualizations;
        $wgOut->addScript("<script src='$wgServer$wgScriptPath/extensions/Visualizations/TreeMap/js/treemap.js' type='text/javascript'></script>");
    }

    function show(){
        global $wgOut, $wgServer, $wgScriptPath;
        $string = "<div style='height:".($this->height)."px;width:".($this->width)."px;'>";
        if($this->countLabel != ""){
            $string .= "<div id='vis{$this->index}options' style='float:right;'>
              <label>&nbsp;<input type='radio' checked='checked' value='size' name='{$this->index}mode'>{$this->sizeLabel}</label>
              <label>&nbsp;<input type='radio' value='count' name='{$this->index}mode'>{$this->countLabel}</label>
            </div>";
        }
        $string .= "<div style='height:".($this->height)."px;width:".($this->width)."px;' class='treeMap' id='vis{$this->index}'></div></div>";
        $string .= <<<EOF
<script type='text/javascript'>
    
    function onLoad{$this->index}(){
        $('#vis{$this->index}').width('{$this->width}');
        $('#vis{$this->index}').height('{$this->height}');
        $('#vis{$this->index}').empty();
        $('#vis{$this->index}').treemap({url: '{$this->url}',
                                         sizeLabel: '{$this->sizeLabel}',
                                         sizeUnit: '{$this->sizeUnit}',
                                         countLabel: '{$this->countLabel}',
                                         countUnit: '{$this->countUnit}'});
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
