<?php

require_once("MyScreenCaptures.php");

$wgHooks['BeforePageDisplay'][] = 'ScreenCapture::addRecordScript';
$wgHooks['UnknownAction'][] = 'ScreenCapture::getRecordedImage';


class ScreenCapture {
    
    function ScreenCapture(){
    
    }
    
    function addRecordScript($out){
        global $wgServer, $wgScriptPath, $wgUser, $wgImpersonating;
        $me = Person::newFromWgUser();
        if($wgUser->isLoggedIn() && $me->isRoleAtLeast(HQP) && !$wgImpersonating){
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
    
    function getRecordedImage($action){
        if($action == 'getRecordedImage'){
            $me = Person::newFromWgUser();
            if(isset($_GET['id'])){
                $id = mysql_real_escape_string($_GET['id']);
                $sql = "SELECT *
                        FROM `grand_recorded_images`
                        WHERE `id` = '{$id}'";
                $data = DBFunctions::execSQL($sql);
                if(count($data) > 0){
                    $row = $data[0];
                    $personId = $row['person'];
                    if($me->getId() == $personId || $me->isRoleAtLeast(MANAGER)){
                        $imgData = $row['image'];
                        header('Content-Type: image/png');
                        echo base64_decode($imgData);
                        exit;
                    }
                }
            }
            return false;
        }
        return true;
    }   
    
}

?>
