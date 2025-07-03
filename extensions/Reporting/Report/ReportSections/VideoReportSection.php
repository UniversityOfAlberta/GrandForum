<?php

class VideoReportSection extends NextPrevReportSection {
    
    function render(){
        global $wgOut;
        parent::render();
        $wgOut->addHTML("<script type='text/javascript'>
            $('#reportHeader').hide();
            $('#reportHeader').next().hide();
        </script>");
    }
    
}

?>
