<?php

class Graph extends Visualization {
    
    static $a = 0;
    var $url = "";
    
    function __construct($url){
        $this->url = $url;
        parent::__construct();
    }
    
    static function init(){
        global $wgOut, $wgServer, $wgScriptPath;
        //$wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualizations/Graph/js/excanvas.min.js" type="text/javascript" charset="utf-8"></script>');
        //$wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualizations/Graph/js/jsPlumb.min.js" type="text/javascript" charset="utf-8"></script>');
        $wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualizations/Graph/js/absPos.js" type="text/javascript" charset="utf-8"></script>');
        $wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualizations/Graph/js/graph.js" type="text/javascript" charset="utf-8"></script>');
        $wgOut->addStyle($wgServer.$wgScriptPath.'/extensions/Visualizations/Graph/css/main.css');
    }

    function show(){
        global $wgOut, $wgServer, $wgScriptPath;
        $string = "<script type='text/javascript'>
            $(document).ready(function(){
                if($('#vis{$this->index}').parent().css('display') != 'none'){
                    $('#vis{$this->index}').graph('{$this->url}');
                }
            });
        </script>";
        $string .= "<div id='vis{$this->index}' style='height:700px;position:relative;'></div>";
        return $string;
    }
}


?>
