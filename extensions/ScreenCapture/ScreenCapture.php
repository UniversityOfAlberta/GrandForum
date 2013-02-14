<?php

$wgHooks['BeforePageDisplay'][] = 'ScreenCapture::addRecordScript';

class ScreenCapture {
    
    function ScreenCapture(){
    
    }
    
    function addRecordScript($out){
        global $wgServer, $wgScriptPath, $wgUser;
        if($wgUser->isLoggedIn()){
            $out->addScript("<script type='text/javascript'>
                $(document).ready(function(){    
                    $('#bodyContent').record({
                        convertSVG: true,
                        delay: 0,
                        el: '#nav',
                        maxSize: 5*1000*1000,
                        onFinishedRecord: function(story){
                            $.post('{$wgServer}{$wgScriptPath}/index.php?action=api.addRecordStory', {\"story\": story}, function(response){
                                
                            });
                        },
                        convertURL: '{$wgServer}{$wgScriptPath}/convertSvg.php'
                    });
                });
            </script>");
        }
        return true;
    }   
    
}

?>
