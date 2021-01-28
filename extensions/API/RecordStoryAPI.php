<?php

class RecordStoryAPI extends API{

    function __construct(){
        $this->addPOST("story",true,"The array of stories","[{date: '', img: ''},{date: '', img: ''}]");
    }

    function processParams($params){
        $_POST['story'] = json_encode($_POST['story']);
    }

	function doAction($noEcho=false){
	    global $wgServer, $wgScriptPath;
	    $me = Person::newFromWgUser();
	    $story = json_decode($_POST['story']);
	    $storyToken = $_POST['storyToken'];
	    $delete = isset($_POST['delete']);
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
	            $md5 = md5(json_encode($screenshot));
	            if(!$delete){
	                $stat = ($stat && DBFunctions::insert('grand_recorded_images',
	                                                      array('id' => $md5,
	                                                      'image' => $screenshot->img,
	                                                      'user_id' => $me->getId())));
	            }
	            $screenshot->img = $md5;
	        }
	    }
	    $data = DBFunctions::select(array('grand_recordings'),
	                                array('*'),
	                                array('storyToken' => EQ($storyToken)),
	                                array(),
	                                array(1));
	    if(count($data) > 0){
	        $row = $data[0];
	        $oldStory = json_decode($row['story']);
	        if($delete){
	            foreach($oldStory as $screenshot){
	                if($screenshot->event == 'screen'){
	                    $stat = ($stat && DBFunctions::delete('grand_recorded_images',
	                                                          array('id' => EQ($screenshot->img))));
	                }
	            }
	            $stat = ($stat && DBFunctions::delete('grand_recordings',
	                                                  array('storyToken' => EQ($storyToken))));
	            if(!$stat){
	                $this->addError("There was an error deleting the Recording");
	                DBFunctions::rollback();
	            }
	            else{
	                DBFunctions::commit();
	                $this->addMessage("The story was successfully deleted");
	            }
	            return $stat;
	        }
	        else{
	            foreach($story as $screenshot){
	                $oldStory[] = $screenshot;
	            }
	        }
	        $newStory = json_encode($oldStory);
	        $stat = ($stat && DBFunctions::update('grand_recordings',
	                                              array('story' => $newStory),
	                                              array('storyToken' => EQ($storyToken)),
	                                              array(1)));
	    }
	    else{
	        if($delete){
                $this->addMessage("The story was successfully deleted");
                return true;
            }
	        $story = json_encode($story);
	        $stat = ($stat && DBFunctions::insert('grand_recordings',
	                                              array('storyToken' => $storyToken,
	                                                    'user_id' => $me->getId(),
	                                                    'story' => $story)));
	    }
	    if(!$stat){
	        $this->addError("There was an error adding the Recording");
	        DBFunctions::rollback();
	    }
	    else{
	        DBFunctions::commit();
	        $data = DBFunctions::select(array('grand_recordings'),
	                                    array('id'),
	                                    array('user_id' => EQ($me->getID())),
	                                    array('id' => 'DESC'),
	                                    array(1));
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
