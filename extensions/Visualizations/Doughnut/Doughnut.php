<?php

class Doughnut extends Visualization {
    
    static $a = 0;
    var $url = "";
    var $clickable = "false";
    var $fn = "";
    
    /**
     * Creates a new Doughnut visualization
     * @param string $url The data url
     * @param boolean $clickable Whether or not the sections should respond to click events.
     * @param string $fn The javascript code to run when a section is clicked.  A 'text' variable can be accessed for this code
     */
    function __construct($url, $clickable=false, $fn=""){
        $this->url = $url;
        $this->clickable = ($clickable) ? "true" : "false";
        $this->fn = $fn;
        parent::__construct();
    }
    
    static function init(){
        global $wgOut, $wgServer, $wgScriptPath;
        $wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualizations/Doughnut/doughnut/raphael.js" type="text/javascript" charset="utf-8"></script>');
        $wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualizations/Doughnut/doughnut/popup.js" type="text/javascript" charset="utf-8"></script>');
        $wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualizations/Doughnut/doughnut/doughnut.js" type="text/javascript" charset="utf-8"></script>');
    }

    function show(){
        global $wgOut, $wgServer, $wgScriptPath;
        $string = "<script type='text/javascript'>
            $(document).ready(function(){
                if($('#vis{$this->index}:visible').length > 0){
                    $('#vis{$this->index}').doughnut('{$this->url}', {$this->clickable}, function(text){ {$this->fn} });
                }
            });
        </script>";
        $string .= "<div style='min-height:175px;max-width:575px' id='vis{$this->index}'></div>";
        return $string;
    }
}


?>
