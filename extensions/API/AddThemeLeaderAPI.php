<?php

class AddThemeLeaderAPI extends API{

    function AddThemeLeaderAPI(){
        $this->addPOST("name",true,"The User Name of the user to add","UserName");
        $this->addPOST("theme",true,"The number of the theme","1");
        $this->addPOST("co_lead", false,"Whether or not this user should be a co leader or not.  If not provided, 'False' is assumed", "False");
    }
    
    function processParams($params){
        $_POST['theme'] = str_replace("'", "", $_POST['theme']);
        $_POST['name'] = str_replace("'", "", $_POST['name']);
        $_POST['co_lead'] = str_replace("'", "", $_POST['co_lead']);
    }

	function doAction($noEcho=false){
		global $wgRequest, $wgUser, $wgServer, $wgScriptPath;
		$groups = $wgUser->getGroups();
        $me = Person::newFromId($wgUser->getId());
		if($me->isRoleAtLeast(STAFF)){
            // Actually Add the Theme Leader
            $person = Person::newFromName($_POST['name']);
            if(!isset($_POST['co_lead']) || ($_POST['co_lead'] != "False" && $_POST['co_lead'] != "True")){
                $_POST['co_lead'] = 'False';
            }
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
            // Add entry into grand_theme_leaders
            $sql = "INSERT INTO grand_theme_leaders (`user_id`,`theme`,`co_lead`,`start_date`)
					VALUES ('{$person->getId()}','{$_POST['theme']}','{$_POST['co_lead']}', CURRENT_TIMESTAMP)";
            DBFunctions::execSQL($sql, true);

            if(!$noEcho){
                echo "{$person->getName()} is now a theme leader of {$project->getName()}\n";
            }
            
            $sql = "SELECT CURRENT_TIMESTAMP";
            $data = DBFunctions::execSQL($sql);
            $effectiveDate = "'{$data[0]['CURRENT_TIMESTAMP']}'";
            $type = "";
            if($_POST['co_lead'] == "True"){
                $type = 'co-';
            }
            Notification::addNotification($me, $person, "Theme Leader Added", "Effective $effectiveDate you are a theme {$type}leader of 'Theme {$_POST['theme']} - ".Project::getThemeName($_POST['theme'])."'", "{$person->getUrl()}");
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
