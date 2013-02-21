<?php

require_once("MyScreenCaptures.php");

$wgHooks['BeforePageDisplay'][] = 'ScreenCapture::addRecordScript';
$wgHooks['UnknownAction'][] = 'ScreenCapture::getRecordedStory';
$wgHooks['UnknownAction'][] = 'ScreenCapture::setRecordedStory';
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
                        header("Cache-Control: private, max-age=10800, pre-check=10800");
                        header("Pragma: private");
                        header("Expires: " . date(DATE_RFC822,strtotime(" 2 day")));
                        header('Content-Length: '.strlen(base64_decode($imgData)));
                        header('Content-Type: image/png');
                        header('Content-transfer-encoding: binary'); 
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
