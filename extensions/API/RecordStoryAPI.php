<?php

class RecordStoryAPI extends API{

    function RecordStoryAPI(){
        $this->addPOST("story",true,"The array of stories","[{date: '', img: ''},{date: '', img: ''}]");
    }

    function processParams($params){
        $_POST['story'] = @mysql_real_escape_string(json_encode($_POST['story']));
    }

	function doAction($noEcho=false){
	    $me = Person::newFromWgUser();
	    $sql = "INSERT INTO `grand_recordings`
	            (`person`, `story`) VALUES
	            ('{$me->getId()}','{$_POST['story']}')";
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
