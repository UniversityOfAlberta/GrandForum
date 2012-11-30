<?php

class DeletePaperAPI extends API{

    function DeletePaperAPI(){
        $this->addPOST("id",true,"The id of the paper to delete","18");
        $this->addPOST("notify",false,"Whether or not to send out notifications",'false');
    }

    function processParams($params){
        $_POST['id'] = @str_replace("'", "&#39;", $_POST['id']);
        $_POST['notify'] = (@str_replace("'", "&#39", $_POST['notify']) == "true");
    }

	function doAction($noEcho=false){
		global $wgRequest, $wgUser, $wgServer, $wgScriptPath;
		$me = Person::newFromId($wgUser->getId());
        $paper = Paper::newFromId($_POST['id']);
		if(!$noEcho){
            if($paper == null || $paper->getTitle() == null){
                $this->addError("There is no paper by the id of '{$_POST['id']}'\n");
                return;
            }
        }
		if($me->isRoleAtLeast(HQP)){
            // Actually Delete the Paper
            DBFunctions::execSQL("UPDATE `grand_products`
                                  SET `deleted` = '1'
                                  WHERE `id` = '{$paper->getId()}'", true);
            if(!$noEcho){
                $this->addMessage("Paper <i>{$paper->getTitle()}</i> Deleted.\n");
            }
		}
		else {
		    if(!$noEcho){
			    $this->addError("You do not have the correct permissions to delete this paper\n");
			    return;
			}
		}
	}
	
	function isLoginRequired(){
		return true;
	}
}

?>
