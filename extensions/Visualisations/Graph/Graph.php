<?php

//require_once("SpecialGraph.php");

class Graph extends Visualisation {
    
    static $a = 0;
    var $url = "";
    
    function Graph($url){
        $this->url = $url;
        self::Visualisation();
    }
    
    static function init(){
        global $wgOut, $wgServer, $wgScriptPath;
        $wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualisations/Graph/js/excanvas.min.js" type="text/javascript" charset="utf-8"></script>');
        $wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualisations/Graph/js/jsPlumb.min.js" type="text/javascript" charset="utf-8"></script>');
        $wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualisations/Graph/js/absPos.js" type="text/javascript" charset="utf-8"></script>');
        $wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualisations/Graph/js/graph.js" type="text/javascript" charset="utf-8"></script>');
        $wgOut->addStyle($wgServer.$wgScriptPath.'/extensions/Visualisations/Graph/css/main.css');
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
