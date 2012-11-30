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
            $status = DBFunctions::execSQL("UPDATE `grand_products`
                                           SET `deleted` = '1'
                                           WHERE `id` = '{$paper->getId()}'", true);
            if(!$noEcho && $status){
                $this->addMessage("Paper <i>{$paper->getTitle()}</i> Deleted.\n");
            }
            else{
                $this->addError("There was an error deleting the product");
            }
            if($status && isset($_POST['notify']) && $_POST['notify'] === true){
                foreach($paper->getAuthors() as $author){
                    if($author instanceof Person){
                        Notification::addNotification($me, $author, "{$paper->getCategory()} Deleted", "Your ".strtolower($paper->getCategory())." entitled <i>{$paper->getTitle()}</i> has been deleted", "{$paper->getUrl()}");
                    }
                }
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
