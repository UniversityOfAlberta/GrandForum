<?php

class DeleteRelationAPI extends API{

    function __construct(){
        $this->addPOST("name1",true,"The name of the first user, as in 'name1 works with name2'","First1.Last1");
        $this->addPOST("name2",true,"The name of the second user, as in 'name1 works with name2'","First2.Last2");
        $this->addPOST("type",true,"The type of relation (ie. Supervises)","Supervises");
    }

    function processParams($params){
        $_POST['name1'] = str_replace("'", "&#39;", $_POST['name1']);
        $_POST['name2'] = str_replace("'", "&#39;", $_POST['name2']);
        $_POST['type'] = str_replace("'", "&#39;", $_POST['type']);
    }

	function doAction($noEcho=false){
		global $wgRequest, $wgUser, $wgServer, $wgScriptPath;
		$me = Person::newFromId($wgUser->getId());

		$person1 = Person::newFromNameLike($_POST['name1']);
		$person2 = Person::newFromNameLike($_POST['name2']);

		if(!$noEcho){
            if($person1->getName() == null){
                echo "There is no person by the name of '{$_POST['name1']}'\n";
                exit;
            }
            if($person2->getName() == null){
                echo "There is no person by the name of '{$_POST['name2']}'\n";
                exit;
            }
        }
		if($me->isRoleAtLeast(HQP)){
            // Actually Delete the Relation
            $sql = "UPDATE grand_relations
                    SET `end_date` = CURRENT_TIMESTAMP
                    WHERE `user1` = '{$person1->getId()}'
                    AND `user2` = '{$person2->getId()}'
                    AND `type` = '{$_POST['type']}'
                    ORDER BY `start_date` DESC LIMIT 1";
            DBFunctions::execSQL($sql, true);
            if(!$noEcho){
                echo "{$person1->getName()} relation with {$person2->getName()} Deleted.\n";
            }
            Notification::addNotification($me, $person1, "Relation Removed", "You and {$person2->getName()} are no longer related through the '{$_POST['type']}' relation", "{$person2->getUrl()}");
            Notification::addNotification($me, $person2, "Relation Removed", "You and {$person1->getName()} are no longer related through the '{$_POST['type']}' relation", "{$person1->getUrl()}");
            if($_POST['type'] == "Supervises"){
                $supervisors = $person2->getSupervisors();
                if(count($supervisors) > 0){
                    foreach($supervisors as $supervisor){
                        if($person1->getName() != $supervisor->getName()){
                            Notification::addNotification($me, $supervisor, "Relation Removed", "{$person1->getName()} and {$person2->getName()} are no longer related through the '{$_POST['type']}' relation", "{$person2->getUrl()}");
                        }
                    }
                }
            }
		}
		else {
		    if(!$noEcho){
			    echo "You do not have the correct permissions to edit this user\n";
			}
		}
	}
	
	function isLoginRequired(){
		return true;
	}
}

?>
