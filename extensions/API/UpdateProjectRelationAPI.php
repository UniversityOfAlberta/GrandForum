<?php

class UpdateProjectRelationAPI extends API{

    function __construct(){
        $this->addPOST("id",true,"The id of the relation in which to edit","42");
        $this->addPOST("project_relations",true,"The projects which this relation has, separated by commas","MEOW,NAVEL");
    }

    function processParams($params){
        $_POST['id'] = str_replace("'", "&#39;", $_POST['id']);
        $_POST['project_relations'] = str_replace("'", "&#39;", $_POST['projects']);
    }

	function doAction($noEcho=false){
		global $wgRequest, $wgUser, $wgServer, $wgScriptPath;
		$me = Person::newFromId($wgUser->getId());
        $relation = Relationship::newFromId($_POST['id']);
        
		$person1 = $relation->getUser1();
		$person2 = $relation->getUser2();
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
        $projectIds = array();
        if($_POST['project_relations'] != ""){
            $projects = explode(",", str_replace(" ", "", $_POST['project_relations']));
            foreach($projects as $proj){
                $project = Project::newFromName($proj);
                $projectIds[] = $project->getId();
            }
        }
		if($me->isRoleAtLeast(HQP)){
            // Actually Add the Relation
            if($person1->getId() == 0 || $person2->getId() == 0){
                return;
            }
            DBFunctions::execSQL("UPDATE grand_relations
                                  SET `projects` = '".serialize($projectIds)."'
                                  WHERE `id` = '{$_POST['id']}'", true);
            if(!$noEcho){
                echo "{$person1->getName()} relation with {$person2->getName()} was updated.\n";
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
