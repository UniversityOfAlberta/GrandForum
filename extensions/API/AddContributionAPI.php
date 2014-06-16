<?php

class AddContributionAPI extends API{

    function AddContributionAPI(){
        $this->addPOST("id", false, "The id of the contribution(only required if updating)","5");
        $this->addPOST("users", true, "The user names of the users involved with this contribution, separated by commas","First1.Last1, First2.Last2, First3.Last3");
        $this->addPOST("projects", true, "The projects involved with this contribution, separated by commas","MEOW, NAVEL");
        $this->addPOST("title", true, "The title of the contribution","My contribution");
        $this->addPOST("description", true, "The description of the contribution", "This is the description of my contribution");
        $this->addPOST("partners", true, "The list of the parters involved with this contribution, separated by commas", "IBM, Intel");
        $this->addPOST("type", true, "The type of contribution this is", "cash");
        $this->addPOST("subtype", false, "The sub-type of contribution this is (This is generally only needed if the type is in-kind)", "cash");
        $this->addPOST("inKind", true, "The amount of money was contributed In-Kind", "3000");
        $this->addPOST("cash", true, "The amount of money was contributed via Cash", "1000");
        $this->addPOST("year", true, "year that this contribution was made", "2011");
    }

    function processParams($params){
        $users = explode(", ", $_POST['users']);
        $_POST['users'] = array();
        foreach($users as $user){
            $person = Person::newFromNameLike($user)->getId();
            if($person != null && $person->getName() != null){
                $_POST['users'][] = $person->getId();
            }
            else{
                $_POST['users'][] = $user;
            }           
        }
        $projects = explode(", ", $_POST['projects']);
        $_POST['projects'] = array();
        foreach($projects as $project){
            $_POST['projects'][] = Project::newFromName($project)->getId();
        }
        $partners = explode(", ", $_POST['partners']);
        $_POST['partners'] = array();
        foreach($partners as $partner){
            $_POST['partners'][] = array("name" => Partner::newFromName($partner)->getName(), "id" => Partner::newFromName($partner)->getId());
        }
    }

	function doAction($noEcho=false){
		global $wgRequest, $wgUser, $wgServer, $wgScriptPath, $wgMessage;
		$groups = $wgUser->getGroups();
		$me = Person::newFromId($wgUser->getId());
        if(!isset($_POST['partners']) || count($_POST['partners']) == 0){
            $wgMessage->addError("A partner must be provided");
            return;
        }
        if(!isset($_POST['projects']) || count($_POST['projects']) == 0){
            $_POST['projects'] = array();
        }
		if(isset($_POST['id'])){
		    //Updating
		    if($_POST['title'] == ""){
	            $string = "The Contribution must not have an empty title";
	            $wgMessage->addError($string);
	            return $string;
	        }
		    $sql = "INSERT INTO `grand_contributions`
                        (`id`,`name`,`users`,`description`,`year`)
                        VALUES ('{$_POST['id']}','{$_POST['title']}','".serialize($_POST['users'])."','".str_replace("'", "&#39;", $_POST['description'])."','{$_POST['year']}')";
            DBFunctions::execSQL($sql, true);
            Contribution::$cache = array();
            $contribution = Contribution::newFromId($_POST['id']);
            foreach($_POST['projects'] as $project){
                $sql = "INSERT INTO `grand_contributions_projects`
                        (`contribution_id`,`project_id`)
                        VALUES ('{$contribution->rev_id}','{$project}')";
                DBFunctions::execSQL($sql, true);
            }
            
            foreach($_POST['partners'] as $key => $partner){
                $value = $partner['id'];
                if($value == ""){
                    $value = $partner['name'];
                }
                DBFunctions::insert('grand_contributions_partners',
                                    array('contribution_id' => $contribution->rev_id,
                                          'partner' => $value,
                                          'type' => @$_POST['type'][$key],
                                          'subtype' => @$_POST['subtype'][$key],
                                          'cash' => @$_POST['cash'][$key],
                                          'kind' => @$_POST['kind'][$key]));
                                          
                /*$sql = "INSERT INTO `grand_contributions_partners`
                        (`contribution_id`,`partner`,`type`,`subtype`,`cash`,`kind`)
                        VALUES ('{$contribution->rev_id}','{$value}','{$_POST['type'][$key]}','{$_POST['subtype'][$key]}','{$_POST['cash'][$key]}','{$_POST['kind'][$key]}')";
                DBFunctions::execSQL($sql, true);*/
            }
            
            Contribution::$cache = array();
	        $contributionAfter = Contribution::newFromName($_POST['title']);
	        // Notification for new authors
	        foreach($contributionAfter->getPeople() as $author){
	            if($author instanceof Person){
                    $found = false;
                    foreach($contribution->getPeople() as $author1){
                        if($author1 instanceof Person){
                            if($author->getId() == $author1->getId()){
                                $found = true;
                                break;
                            }
                        }
                    }
                    if($found == false){
                        Notification::addNotification($me, $author, "Contribution Researcher Added", "You have been added as a researcher to the contribution entitled '{$contributionAfter->getName()}'", "{$contributionAfter->getUrl()}");
                    }
                    else{
                        // Generic change to contribution
                        Notification::addNotification($me, $author, "Contribution Modified", "Your contribution entitled '{$contributionAfter->getName()}' has been modified", "{$contributionAfter->getUrl()}");
                    }
                }
	        }
            // Notification for removed authors
	        foreach($contribution->getPeople() as $author){
                $found = false;
                if($author instanceof Person){
                    foreach($contributionAfter->getPeople() as $author1){
                        if($author1 instanceof Person){
                            if($author->getId() == $author1->getId()){
                                $found = true;
                                break;
                            }
                        }
                    }
                    if($found == false){
                        Notification::addNotification($me, $author, "Contribution Researcher Removed", "You have been removed as a researcher from the contribution entitled '{$contributionAfter->getName()}'", "{$contributionAfter->getUrl()}");
                    }
                }
	        }
		}
		else{
		    //Inserting
		    if($_POST['title'] == ""){
	            $string = "The Contribution must not have an empty title";
	            $wgMessage->addError($string);
	            return $string;
	        }
		    $sql = "SELECT `id`
		            FROM `grand_contributions`
		            ORDER BY id DESC LIMIT 1";
		    $data = DBFunctions::execSQL($sql, false);
		    if(count($data) > 0){
		        $id = $data[0]['id'];
		    }
		    $sql = "INSERT INTO grand_contributions
                        (`id`,`name`,`users`,`description`,`year`)
                        VALUES ('".($id + 1)."','{$_POST['title']}','".serialize($_POST['users'])."','".str_replace("'", "&#39;", $_POST['description'])."','{$_POST['year']}')";
            DBFunctions::execSQL($sql, true);
            Contribution::$cache = array();
            $contribution = Contribution::newFromName($_POST['title']);
            foreach($_POST['projects'] as $project){
                $sql = "INSERT INTO `grand_contributions_projects`
                        (`contribution_id`,`project_id`)
                        VALUES ('{$contribution->rev_id}','{$project}')";
                DBFunctions::execSQL($sql, true);
            }
            
            foreach($_POST['partners'] as $key => $partner){
                $value = $partner['id'];
                if($value == ""){
                    $value = $partner['name'];
                }
                $sql = "INSERT INTO `grand_contributions_partners`
                        (`contribution_id`,`partner`,`type`,`subtype`,`cash`,`kind`)
                        VALUES ('{$contribution->rev_id}','{$value}','{$_POST['type'][$key]}','{$_POST['subtype'][$key]}','{$_POST['cash'][$key]}','{$_POST['kind'][$key]}')";
                DBFunctions::execSQL($sql, true);
            }
            
            
	        foreach($_POST['users'] as $author){
	            if(is_numeric($author)){ 
	                $person = Person::newFromId($author);
                    if($person != null && $person->getName() != null){
                        Notification::addNotification($me, $person, "Contribution Created", "A new Contribution entitled <i>{$contribution->getName()}</i>, has been created with yourself listed as one of the researchers", "{$contribution->getUrl()}");
                    }
                }
	        }
		}
	}
	
	function isLoginRequired(){
		return true;
	}
}

?>
