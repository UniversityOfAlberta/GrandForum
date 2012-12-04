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

	function doAction(){
		global $wgRequest, $wgUser, $wgServer, $wgScriptPath;
		$me = Person::newFromId($wgUser->getId());
        $paper = Paper::newFromId($_POST['id']);
        if($paper == null || $paper->getTitle() == null){
            $this->addError("There is no product with the id '{$_POST['id']}'\n");
            return;
        }
        if($paper->deleted){
            $this->addError("This product is already deleted\n");
            return;
        }
		if($me->isRoleAtLeast(HQP)){
            // Actually Delete the Paper
            $status = DBFunctions::execSQL("UPDATE `grand_products`
                                           SET `deleted` = '1'
                                           WHERE `id` = '{$paper->getId()}'", true);
            if($status){
                $this->addMessage("The {$paper->getCategory()} <i>{$paper->getTitle()}</i> was Deleted\n");
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
		    $this->addError("You do not have the correct permissions to delete this product\n");
		    return;
		}
	}
	
	function isLoginRequired(){
		return true;
	}
}

?>
