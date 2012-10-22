<?php

class DeletePaperAPI extends API{

    function DeletePaperAPI(){
        $this->addPOST("id",true,"The id of the paper to delete","18");
    }

    function processParams($params){
        $_POST['id'] = str_replace("'", "&#39;", $_POST['id']);
    }

	function doAction($noEcho=false){
		global $wgRequest, $wgUser, $wgServer, $wgScriptPath;
		$me = Person::newFromId($wgUser->getId());
        $paper = Paper::newFromId($_POST['id']);
		if(!$noEcho){
            if($paper == null || $paper->getTitle() == null){
                echo "There is no paper by the id of '{$_POST['id']}'\n";
                exit;
            }
        }
		if($me->isRoleAtLeast(HQP)){
            // Actually Delete the Paper
            DBFunctions::execSQL("UPDATE `grand_products`
                                  SET `deleted` = '1'
                                  WHERE `id` = '{$paper->getId()}'", true);
            if(!$noEcho){
                echo "Paper {$paper->getTitle()} Deleted.\n";
            }
		}
		else {
		    if(!$noEcho){
			    echo "You do not have the correct permissions to delete this paper\n";
			}
		}
	}
	
	function isLoginRequired(){
		return true;
	}
}

?>
