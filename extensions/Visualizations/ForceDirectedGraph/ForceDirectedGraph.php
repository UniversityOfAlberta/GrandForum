<?php

class ForceDirectedGraph extends Visualization {
    
    static $a = 0;
    var $url = "";
    var $height = 800;
    var $width = 1000;
    
    function __construct($url){
        $this->url = $url;
        parent::__construct();
    }
    
    static function init(){
        global $wgOut, $wgServer, $wgScriptPath;
        $wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualizations/ForceDirectedGraph/fdg.js" type="text/javascript" charset="utf-8"></script>');
    }

    function show(){
        global $wgOut, $wgServer, $wgScriptPath;
        $string = "<div id='vis{$this->index}'></div>
        <script type='text/javascript'>
            $(document).ready(function(){
                if($('#vis{$this->index}').parent().css('display') != 'none'){
                    createFDG({$this->width}, {$this->height}, 'vis{$this->index}', '{$this->url}');
                }
            });
        </script>";
        return $string;
    }
}


?>
