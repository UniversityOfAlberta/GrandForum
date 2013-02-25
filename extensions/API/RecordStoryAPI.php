<?php

class RecordStoryAPI extends API{

    function RecordStoryAPI(){
        $this->addPOST("story",true,"The array of stories","[{date: '', img: ''},{date: '', img: ''}]");
    }

    function processParams($params){
        $_POST['story'] = json_encode($_POST['story']);
    }

	function doAction($noEcho=false){
	    global $wgServer, $wgScriptPath;
	    $me = Person::newFromWgUser();
	    $story = json_decode($_POST['story']);
	    DBFunctions::begin();
	    $stat = true;
	    foreach($story as $screenshot){
	        if($screenshot->event == 'screen'){
	            if(!isset($screenshot->transition)){
	                $screenshot->transition = '';
	            }
	            if(!isset($screenshot->descriptions)){
	                $screenshot->descriptions = array();
	            }
	            $img = mysql_real_escape_string($screenshot->img);
	            $md5 = md5(json_encode($screenshot));
	            $screenshot->img = $md5;
	            $sql = "INSERT INTO `grand_recorded_images`
	                    (`id`,`image`,`person`) VALUES
	                    ('$md5','$img','{$me->getId()}')";
	            $stat = ($stat && DBFunctions::execSQL($sql, true));
	        }
	    }
	    $story = mysql_real_escape_string(json_encode($story));
	    $sql = "INSERT INTO `grand_recordings`
	            (`person`, `story`) VALUES
	            ('{$me->getId()}','{$story}')";
	    $stat = ($stat && DBFunctions::execSQL($sql, true));
	    if(!$stat){
	        $this->addError("There was an error adding the Recording");
	        DBFunctions::rollback();
	    }
	    else{
	        DBFunctions::commit();
	        $sql = "SELECT `id`
	                FROM `grand_recordings`
	                WHERE `person` = '{$me->getId()}'
	                ORDER BY `id` DESC LIMIT 1";
	        $data = DBFunctions::execSQL($sql);
	        if(count($data) > 0){
	            $id = $data[0]['id'];
	            $this->addMessage("The story was successfully uploaded.  You can view it here: <a href='$wgServer$wgScriptPath/index.php/Special:MyScreenCaptures?id=".$id."' target='_blank'>My Screen Captures</a>");
	        }
	    }
	    return $stat;
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
