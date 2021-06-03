<?php

class ForceDirectedGraph extends Visualization {
    
    static $a = 0;
    var $url = "";
    var $height = "800px";
    var $width = "1000px";
    
    function ForceDirectedGraph($url){
        $this->url = $url;
        self::Visualization();
    }
    
    static function init(){
        global $wgOut, $wgServer, $wgScriptPath;
        if(strstr($wgOut->getScript(), 'vis.min.js') === false){
            $wgOut->addScript("<script src='$wgServer$wgScriptPath/extensions/Visualizations/Vis/js/vis.min.js' type='text/javascript'></script>");
            $wgOut->addScript("<link href='$wgServer$wgScriptPath/extensions/Visualizations/Vis/js/vis.min.css' rel='stylesheet' type='text/css' />");
        }
        $wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualizations/ForceDirectedGraph/fdg.js" type="text/javascript" charset="utf-8"></script>');
    }

    function show(){
        global $wgOut, $wgServer, $wgScriptPath;
        $string = "<div id='vis{$this->index}' style='width:{$this->width}px; height:{$this->height}px;'></div>
        <script type='text/javascript'>
            function onLoad{$this->index}(){
                createFDG('vis{$this->index}', '{$this->url}');
            }
            $(document).ready(function(){
                onLoad{$this->index}();
            });
        </script>";
        return $string;
    }
}


?>
