<?php

require_once("MyScreenCaptures.php");

$wgHooks['BeforePageDisplay'][] = 'ScreenCapture::addRecordScript';
UnknownAction::createAction('ScreenCapture::getRecordedStory');
UnknownAction::createAction('ScreenCapture::setRecordedStory');
UnknownAction::createAction('ScreenCapture::getRecordedImage');


class ScreenCapture {
    
    function addRecordScript($out){
        global $wgServer, $wgScriptPath, $wgUser, $wgImpersonating;
        $me = Person::newFromWgUser();
        if($wgUser->isLoggedIn() && 
           //$me->isRoleAtLeast(HQP) && TODO:When this feature is good to go, uncomment this
           ($me->isRoleAtLeast(MANAGER) || $me->getName() == "Eleni.Stroulia") &&
           !$wgImpersonating){
            $out->addScript("<script type='text/javascript'>
                $(document).ready(function(){
                    var browserVersion = parseFloat($.browser.fullVersion);
                    if(($.browser.msie && browserVersion >= 9) ||
                       ($.browser.mozilla && browserVersion >= 3.5) ||
                       ($.browser.opera && browserVersion >= 12) ||
                       ($.browser.webkit)){
                        $('#nav .pBody').last().append('<li><a class=\"highlights-background-hover\" href=\"{$wgServer}{$wgScriptPath}/index.php/Special:MyScreenCaptures\">My Screen Captures</a></li>');
                    }
                    $('#bodyContent').record({
                        convertSVG: true,
                        delay: 0,
                        el: '#nav',
                        chunkSize: 0.5*1000*1000,
                        onStartRecord: function(){
                            if($.cookie('storyToken') == undefined){
                                var storyToken = $.md5(new Date() + Math.random());
                                $.cookie('storyToken', storyToken, {expires: 1});
                            }
                        },
                        onChunkComplete: function(story){
                            $.post('{$wgServer}{$wgScriptPath}/index.php?action=api.addRecordStory', {'story': story, 'storyToken': $.cookie('storyToken')}, function(response){
                                clearSuccess();
                                clearError();
                                if(response.errors.length > 0){
                                    response.errors.forEach(function(val, index){
                                        addError(val);
                                    });
                                }
                            });
                        },
                        onFinishedRecord: function(story){
                            $.post('{$wgServer}{$wgScriptPath}/index.php?action=api.addRecordStory', {'story': story, 'storyToken': $.cookie('storyToken')}, function(response){
                                clearSuccess();
                                clearError();
                                if(response.errors.length == 0){
                                    response.messages.forEach(function(val, index){
                                        addSuccess(val);
                                    });
                                }
                                else{
                                    response.errors.forEach(function(val, index){
                                        addError(val);
                                    });
                                }
                            });
                            $.removeCookie('storyToken');
                        },
                        onCancelRecord: function(story){
                            $.post('{$wgServer}{$wgScriptPath}/index.php?action=api.addRecordStory', {'story': story, 'storyToken': $.cookie('storyToken'), 'delete':true}, function(response){
                                clearSuccess();
                                clearError();
                            });
                            $.removeCookie('storyToken');
                        },
                        convertURL: '{$wgServer}{$wgScriptPath}/convertSvg.php'
                    });
                    if($.cookie('storyToken') != undefined){
                        $('#bodyContent').record('start');
                    }
                });
            </script>");
        }
        return true;
    }
    
    function getRecordedStory($action){
        if($action == 'getRecordedStory'){
            $me = Person::newFromWgUser();
            if(isset($_GET['id'])){
                $data = DBFunctions::select(array('grand_recordings'),
                                            array('*'),
                                            array('id' => EQ($_GET['id'])));
                if(count($data) > 0){
                    $row = $data[0];
                    $personId = $row['user_id'];
                    if($me->getId() == $personId || $me->isRoleAtLeast(MANAGER)){
                        header('Content-Type: application/json');
                        header("Cache-Control: no-cache");
                        header("Pragma: no-cache");
                        $events = json_decode($row['story']);
                        $story = (object)'a';
                        $story->id = $row['id'];
                        $story->person = $row['user_id'];
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
            if(isset($_POST['story'])){
                $story = json_decode($_POST['story']);
                $id = $story->id;
                $data = DBFunctions::select(array('grand_recordings'),
                                            array('*'),
                                            array('id' => EQ($id)));
                if(count($data) > 0){
                    $row = $data[0];
                    $personId = $row['user_id'];
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
                        $storyData = json_encode($story->events);
                        DBFunctions::update('grand_recordings',
                                            array('story' => $storyData),
                                            array('id' => EQ($story->id)));
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
                $data = DBFunctions::select(array('grand_recorded_images'),
                                            array('*'),
                                            array('id' => EQ($_GET['id'])));
                if(count($data) > 0){
                    $row = $data[0];
                    $personId = $row['user_id'];
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
                        if(isset($_GET['thumbnail'])){
                            $pngSrc = imagecreatefromstring(base64_decode($imgData));
                            $src_width = imagesx($pngSrc);
                            $src_height = imagesy($pngSrc);
                            $dst_width = 100;
                            $dst_height = $dst_width*$src_height/$src_width;
                            if($dst_height > 50){
                                $dst_height = 50;
                                $dst_width = $dst_height*$src_width/$src_height;
                            }

                            $pngDst = imagecreatetruecolor($dst_width, $dst_height);
                            imagecopyresampled($pngDst, $pngSrc, 0, 0, 0, 0, $dst_width, $dst_height, $src_width, $src_height);
                            imagepng($pngDst);
                        }
                        else{
                            echo base64_decode($imgData);
                        }
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
