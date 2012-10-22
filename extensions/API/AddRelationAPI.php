<?php

class AddRelationAPI extends API{

    function AddRelationAPI(){
        $this->addPOST("name1",true,"The name of the first user, as in 'name1 works with name2'","First1.Last1");
        $this->addPOST("name2",true,"The name of the second user, as in 'name1 works with name2'","First2.Last2");
        $this->addPOST("type",true,"The type of relation (ie. Supervises)","Supervises");
        $this->addPOST("project_relations",false,"The projects which this relation has, separated by commas","MEOW,NAVEL");
    }

    function processParams($params){
        $_POST['name1'] = str_replace("'", "&#39;", $_POST['name1']);
        $_POST['name2'] = str_replace("'", "&#39;", $_POST['name2']);
        $_POST['type'] = str_replace("'", "&#39;", $_POST['type']);
        $_POST['project_relations'] = str_replace("'", "&#39;", $_POST['project_relations']);
    }

	function doAction($noEcho=false){
		global $wgRequest, $wgUser, $wgServer, $wgScriptPath;
		$me = Person::newFromId($wgUser->getId());

		$person1 = Person::newFromName($_POST['name1']);
		$person2 = Person::newFromName($_POST['name2']);
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
            // Actually Add the Relation
            if($person1->getId() == 0 || $person2->getId() == 0){
                return;
            }
            $relations = $person1->getRelations($_POST['type']);
            foreach($relations as $relation){
                if($relation->getUser2()->getId() == $person2->getId()){
                    return;
                }
            }
            
            $projectIds = array();
            if(isset($_POST['project_relations']) && $_POST['project_relations'] != ""){
                $projects = explode(",", str_replace(" ", "", $_POST['project_relations']));
                foreach($projects as $proj){
                    $project = Project::newFromName($proj);
                    $projectIds[] = $project->getId();
                }
            }
            DBFunctions::execSQL("INSERT INTO grand_relations
                                  (`user1`,`user2`,`type`,`projects`,`start_date`)
                                  VALUES ('{$person1->getId()}','{$person2->getId()}','{$_POST['type']}','".serialize($projectIds)."',CURRENT_TIMESTAMP)", true);
            if(!$noEcho){
                echo "{$person1->getName()} relation with {$person2->getName()} added.\n";
            }
            Notification::addNotification($me, $person1, "Relation Added", "You and {$person2->getName()} are related through the '{$_POST['type']}' relation", "{$person2->getUrl()}");
            Notification::addNotification($me, $person2, "Relation Added", "You and {$person1->getName()} are related through the '{$_POST['type']}' relation", "{$person1->getUrl()}");
            if($_POST['type'] == SUPERVISES){
                $supervisors = $person2->getSupervisors();
                if(count($supervisors) > 0){
                    foreach($supervisors as $supervisor){
                        if($person1->getName() != $supervisor->getName()){
                            Notification::addNotification($me, $supervisor, "Relation Added", "{$person2->getName()} has been added to the Relation '{$person1->getName()} {$_POST['type']} {$person2->getName()}'", "{$person2->getUrl()}");
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
