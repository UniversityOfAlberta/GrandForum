<?php

require_once("MyScreenCaptures.php");

$wgHooks['BeforePageDisplay'][] = 'ScreenCapture::addRecordScript';
$wgHooks['UnknownAction'][] = 'ScreenCapture::getRecordedStory';
$wgHooks['UnknownAction'][] = 'ScreenCapture::setRecordedStory';
$wgHooks['UnknownAction'][] = 'ScreenCapture::getRecordedImage';


class ScreenCapture {
    
    function addRecordScript($out){
        global $wgServer, $wgScriptPath, $wgUser, $wgImpersonating;
        $me = Person::newFromWgUser();
        if($wgUser->isLoggedIn() && $me->isRoleAtLeast(HQP) && !$wgImpersonating){
            $out->addScript("<script type='text/javascript'>
                $(document).ready(function(){
                    $('#nav .pBody').last().append('<li><a href=\"{$wgServer}{$wgScriptPath}/index.php/Special:MyScreenCaptures\">My Screen Captures</a></li>');
                    $('#bodyContent').record({
                        convertSVG: true,
                        delay: 0,
                        el: '#nav',
                        maxSize: 5*1000*1000,
                        onFinishedRecord: function(story){
                            $.post('{$wgServer}{$wgScriptPath}/index.php?action=api.addRecordStory', {\"story\": story}, function(response){
                                clearSuccess();
                                clearError();
                                if(response.errors.length == 0){
                                    // No Errors
                                    response.messages.forEach(function(val, index){
                                        addSuccess(val);
                                    });
                                }
                                else{
                                    // User
                                    response.errors.forEach(function(val, index){
                                        addError(val);
                                    });
                                }
                            });
                        },
                        convertURL: '{$wgServer}{$wgScriptPath}/convertSvg.php'
                    });
                });
            </script>");
        }
        return true;
    }
    
    function getRecordedStory($action){
        if($action == 'getRecordedStory'){
            $me = Person::newFromWgUser();
            if(isset($_GET['id'])){
                $id = mysql_real_escape_string($_GET['id']);
                $sql = "SELECT *
                        FROM `grand_recordings`
                        WHERE `id` = '{$id}'";
                $data = DBFunctions::execSQL($sql);
                if(count($data) > 0){
                    $row = $data[0];
                    $personId = $row['person'];
                    if($me->getId() == $personId || $me->isRoleAtLeast(MANAGER)){
                        header('Content-Type: application/json');
                        header("Cache-Control: no-cache");
                        header("Pragma: no-cache");
                        $events = json_decode($row['story']);
                        $story = (object)'a';
                        $story->id = $row['id'];
                        $story->person = $row['person'];
                        $story->events = $events;
                        echo json_encode($story);
                        exit;
                    }
                }
            }
            return false;
        }
        return true;
    }
    
    function setRecordedStory($action){
        if($action == 'setRecordedStory'){
            $me = Person::newFromWgUser();
            print_r($_POST);
            if(isset($_POST['story'])){
                $story = json_decode($_POST['story']);
                $id = $story->id;
                $sql = "SELECT *
                        FROM `grand_recordings`
                        WHERE `id` = '{$id}'";
                $data = DBFunctions::execSQL($sql);
                if(count($data) > 0){
                    $row = $data[0];
                    $personId = $row['person'];
                    if(($me->getId() == $personId || $me->isRoleAtLeast(MANAGER)) && $personId == $story->person){
                        // Ok, it is safe to update
                        foreach($story->events as &$screen){
                            if($screen->event == 'screen'){
                                $cleanedDesc = array();
                                foreach($screen->descriptions as $desc){
                                    if($desc != null){
                                        $cleanedDesc[] = $desc;
                                    }
                                }
                                $screen->descriptions = $cleanedDesc;
                            }
                        }
                        $storyData = mysql_real_escape_string(json_encode($story->events));
                        $sql = "UPDATE `grand_recordings`
                                SET `story` = '$storyData'
                                WHERE `id` = '{$story->id}'";
                        DBFunctions::execSQL($sql, true);
                        exit;
                    }
                }
            }
            return false;
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
                        if(substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')){
                            ob_start("ob_gzhandler");
                        }
                        else{
                            ob_start();
                        }
                        header('Pragma: public');
                        header('Cache-Control: max-age=86400');
                        header('Expires: '. gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));
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
