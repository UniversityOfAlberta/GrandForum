<?php

class AddRelationAPI extends API{

    function __construct(){
        $this->addPOST("name1",true,"The name of the first user, as in 'name1 works with name2'","First1.Last1");
        $this->addPOST("name2",true,"The name of the second user, as in 'name1 works with name2'","First2.Last2");
        $this->addPOST("type",true,"The type of relation (ie. Supervises)","Supervises");
        $this->addPOST("project_relations",false,"The projects which this relation has, separated by commas","MEOW,NAVEL");
    }

    function processParams($params){
        
    }

	function doAction($noEcho=false){
		global $wgRequest, $wgUser, $wgServer, $wgScriptPath, $wgMessage;
		$me = Person::newFromId($wgUser->getId());

		$person1 = Person::newFromNameLike($_POST['name1']);
		$person2 = Person::newFromNameLike($_POST['name2']);
		if($person1->getName() == ""){
		    $person1 = Person::newFromName($_POST['name1']);
		}
		if($person2->getName() == ""){
		    $person2 = Person::newFromName($_POST['name2']);
		}
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
        if($person1->getId() == $person2->getId()){
            // Don't allow the user to have a relationship with themselves
            $wgMessage->addError("You can not have a relationship with yourself");
            return;
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
            DBFunctions::insert('grand_relations',
                                array('user1' => $person1->getId(),
                                      'user2' => $person2->getId(),
                                      'type' => $_POST['type'],
                                      'projects' => serialize($projectIds),
                                      'start_date' => EQ(COL('CURRENT_TIMESTAMP'))));
            if(!$noEcho){
                echo "{$person1->getNameForForms()} relation with {$person2->getNameForForms()} added.\n";
            }
            Notification::addNotification($me, $person1, "Relation Added", "You and {$person2->getNameForForms()} are related through the '{$_POST['type']}' relation", "{$person2->getUrl()}");
            Notification::addNotification($me, $person2, "Relation Added", "You and {$person1->getNameForForms()} are related through the '{$_POST['type']}' relation", "{$person1->getUrl()}");
            if($_POST['type'] == SUPERVISES){
                $supervisors = $person2->getSupervisors();
                if(count($supervisors) > 0){
                    foreach($supervisors as $supervisor){
                        if($person1->getName() != $supervisor->getName()){
                            Notification::addNotification($me, $supervisor, "Relation Added", "{$person2->getNameForForms()} has been added to the Relation '{$person1->getNameForForms()} {$_POST['type']} {$person2->getNameForForms()}'", "{$person2->getUrl()}");
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
