<?php

class DeleteThemeLeaderAPI extends API{

    function __construct(){
        $this->addPOST("name", true, "The User Name of the user to add", "UserName");
        $this->addPOST("theme", true, "The theme number", "2");
        $this->addPOST("comment", true, "A comment for why the user is no longer a leade of this theme", "My Reason");
        $this->addPOST("co_lead", false,"Whether or not this user was a co-leader or not.  If not provided, 'False' is assumed", "False");
        $this->addPOST("coordinator", false,"Whether or not this user was a coordinator or not.  If not provided, 'False' is assumed", "False");
        $this->addPOST("effective_date", false, "The date when the theme change should be made in the format YYYY-MM-DD.  If this value is not included, the current time is assumed.", "2012-10-30");
    }

    function processParams($params){
        $_POST['theme'] = str_replace("'", "", $_POST['theme']);
        $_POST['name'] = str_replace("'", "", $_POST['name']);
        $_POST['comment'] = str_replace("'", "", $_POST['comment']);
    }

	function doAction($noEcho=false){
		global $wgRequest, $wgUser, $wgServer, $wgScriptPath;
		$groups = $wgUser->getGroups();
        $me = Person::newFromId($wgUser->getId());
		if($me->isRoleAtLeast(STAFF)){
            // Actually Delete the Theme Leader
            $person = Person::newFromName($_POST['name']);
            if(!isset($_POST['co_lead']) || ($_POST['co_lead'] != "False" && $_POST['co_lead'] != "True")){
                $_POST['co_lead'] = 'False';
            }
            if(!isset($_POST['coordinator']) || ($_POST['coordinator'] != "False" && $_POST['coordinator'] != "True")){
                $_POST['coordinator'] = 'False';
            }
            $comment = str_replace("'", "&#39;", $_POST['comment']);
            if(!$noEcho){
                if($person->getName() == null){
                    echo "There is no person by the name of '{$_POST['name']}'\n";
                    exit;
                }
                else if($theme >= 1 && $theme <= 5){
                    echo "There is no theme by the number of '{$_POST['theme']}'\n";
                    exit;
                }
            }
            $effectiveDate = "CURRENT_TIMESTAMP";
            if(isset($_POST['effective_date']) && $_POST['effective_date'] != ""){
                $effectiveDate = "'{$_POST['effective_date']} 00:00:00'";
            }
            else{
                $sql = "SELECT CURRENT_TIMESTAMP";
                $data = DBFunctions::execSQL($sql);
                $effectiveDate = "'{$data[0]['CURRENT_TIMESTAMP']}'";
            }
            $sql = "UPDATE grand_theme_leaders
	            SET `comment` = '$comment',
	                `end_date` = $effectiveDate
	            WHERE `theme` = '{$_POST['theme']}'
	            AND `user_id` = '{$person->getId()}'
	            AND `co_lead` = '{$_POST['co_lead']}'
	            AND `coordinator` = '{$_POST['coordinator']}'
	            ORDER BY `start_date` DESC LIMIT 1";
            DBFunctions::execSQL($sql, true);
            
            $sql = "SELECT CURRENT_TIMESTAMP";
            $data = DBFunctions::execSQL($sql);
            $effectiveDate = "'{$data[0]['CURRENT_TIMESTAMP']}'";
            $type = "";
            if($_POST['co_lead'] == "True"){
                $type = 'co-';
            }
            Notification::addNotification($me, $person, "Theme Leader Removed", "Effective $effectiveDate you are no longer a theme {$type}leader of 'Theme {$_POST['theme']} - ".Theme::newFromId($_POST['theme'])->getAcronym()."'", "{$person->getUrl()}");
		}
		else {
		    if(!$noEcho){
			    echo "You must be a bureaucrat to use this API\n";
			}
		}
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
