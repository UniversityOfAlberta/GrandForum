<?php

class RecordStoryAPI extends API{

    function RecordStoryAPI(){
        $this->addPOST("story",true,"The array of stories","[{date: '', img: ''},{date: '', img: ''}]");
    }

    function processParams($params){
        $_POST['story'] = json_encode($_POST['story']);
    }

	function doAction($noEcho=false){
	    $me = Person::newFromWgUser();
	    $story = json_decode($_POST['story']);
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
	            DBFunctions::execSQL($sql, true);
	        }
	    }
	    $story = mysql_real_escape_string(json_encode($story));
	    $sql = "INSERT INTO `grand_recordings`
	            (`person`, `story`) VALUES
	            ('{$me->getId()}','{$story}')";
	    $stat = DBFunctions::execSQL($sql, true);
	    if(!$stat){
	        $this->errors[] = "There was an error adding the Recording";
	    }
	    return $stat;
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
