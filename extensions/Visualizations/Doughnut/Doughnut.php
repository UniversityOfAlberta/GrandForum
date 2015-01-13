<?php

//require_once("SpecialDoughnut.php");

class Doughnut extends Visualization {
    
    static $a = 0;
    var $url = "";
    
    function Doughnut($url){
        $this->url = $url;
        self::Visualization();
    }
    
    static function init(){
        global $wgOut, $wgServer, $wgScriptPath;
        $wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualizations/Doughnut/doughnut/raphael.js" type="text/javascript" charset="utf-8"></script>');
        $wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualizations/Doughnut/doughnut/popup.js" type="text/javascript" charset="utf-8"></script>');
        $wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualizations/Doughnut/doughnut/spinner.js" type="text/javascript" charset="utf-8"></script>');
        $wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualizations/Doughnut/doughnut/doughnut.js" type="text/javascript" charset="utf-8"></script>');
    }

    function show(){
        global $wgOut, $wgServer, $wgScriptPath;
        $string = "<script type='text/javascript'>
            $(document).ready(function(){
                if($('#vis{$this->index}:visible').length > 0){
                    $('#vis{$this->index}').doughnut('{$this->url}');
                }
            });
        </script>";
        $string .= "<div style='min-height:175px;max-width:575px' id='vis{$this->index}'></div>";
        return $string;
    }
}


?>
