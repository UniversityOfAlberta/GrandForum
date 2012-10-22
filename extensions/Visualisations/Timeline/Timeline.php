<?php

class Timeline extends Visualisation {
    
    static $a = 0;
    var $url = "";
    
    function Timeline($url){
        $this->url = $url;
        self::Visualisation();
    }
    
    static function init(){
        global $wgOut, $wgServer, $wgScriptPath;
        $wgOut->addStyle($wgServer.$wgScriptPath.'/extensions/Visualisations/Timeline/TimeGlider/js/timeglider/Timeglider.css');
        $wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualisations/Timeline/TimeGlider/js/timeglider-0.1.3.min.js" type="text/javascript" charset="utf-8"></script>');
    }

    function show(){
        global $wgOut, $wgServer, $wgScriptPath;
        $string = "<script type='text/javascript'>
            $(document).ready(function(){
                if($('#vis{$this->index}:visible').length > 0){
                    var tg1 = $('#vis{$this->index}').timeline({
                        'data_source':'{$this->url}',
                        'min_zoom':30,
                        'max_zoom':45, 
                        'icon_folder':'$wgServer$wgScriptPath/extensions/Visualisations/Timeline/TimeGlider/js/timeglider/icons/'
                    });
                }
            });
        </script>";
        $string .= "<div style='height:700px;' id='vis{$this->index}'></div>";
        return $string;
    }
}


?>
