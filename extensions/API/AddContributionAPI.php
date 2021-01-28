<?php

class AddContributionAPI extends API{

    function __construct(){
        $this->addPOST("id", false, "The id of the contribution(only required if updating)","5");
        $this->addPOST("users", true, "The user names of the users involved with this contribution, separated by commas","First1.Last1, First2.Last2, First3.Last3");
        $this->addPOST("projects", true, "The projects involved with this contribution, separated by commas","MEOW, NAVEL");
        $this->addPOST("title", true, "The title of the contribution","My contribution");
        $this->addPOST("description", true, "The description of the contribution", "This is the description of my contribution");
        $this->addPOST("access_id", false, "The id of the user that this contribution belongs to", "4");
        $this->addPOST("partners", true, "The list of the parters involved with this contribution, separated by commas", "IBM, Intel");
        $this->addPOST("type", true, "The type of contribution this is", "cash");
        $this->addPOST("subtype", false, "The sub-type of contribution this is (This is generally only needed if the type is in-kind)", "cash");
        $this->addPOST("kind", true, "The amount of money was contributed In-Kind", "3000");
        $this->addPOST("cash", true, "The amount of money was contributed via Cash", "1000");
        $this->addPOST("start_date", true, "start_date that this contribution was made", "2011-04-01");
        $this->addPOST("end_date", true, "end_date that this contribution goes until", "2013-04-01");
    }

    function processParams($params){
        $users = explode(", ", $_POST['users']);
        $_POST['users'] = array();
        foreach($users as $user){
            $person = Person::newFromNameLike($user);
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
            $proj = Project::newFromName($project);
            if($proj != null && $proj->getName() != null){
                $_POST['projects'][] = $proj->getId();
            }
        }
        $partners = explode(", ", $_POST['partners']);
        $_POST['partners'] = array();
        foreach($partners as $partner){
            $part = Partner::newFromName($partner);
            $name = ($part->getOrganization() != "") ? $part->getOrganization() : $partner;
            $id = ($part->getOrganization() != "") ? $part->getId() : "";
            $_POST['partners'][] = array("name" => $name, "id" => $id);
        }
    }

    function doAction($noEcho=false){
        global $wgRequest, $wgUser, $wgServer, $wgScriptPath, $wgMessage;
        $me = Person::newFromId($wgUser->getId());
        if(!isset($_POST['partners']) || count($_POST['partners']) == 0){
            $wgMessage->addError("A partner must be provided");
            return;
        }
        if(!isset($_POST['projects']) || count($_POST['projects']) == 0){
            $_POST['projects'] = array();
        }
        if($_POST['start_date'] > $_POST['end_date']){
            $wgMessage->addError("The start date must be before the end date.");
        }
        if(isset($_POST['id'])){
            //Updating
            if($_POST['title'] == ""){
                $string = "The Contribution must not have an empty title";
                $wgMessage->addError($string);
                return $string;
            }
            $contribution = Contribution::newFromId($_POST['id']);
            $_POST['access_id'] = (isset($_POST['access_id'])) ? $_POST['access_id'] : $contribution->getAccessId();
            DBFunctions::insert('grand_contributions',
                                array('id' => $_POST['id'],
                                      'name' => $_POST['title'],
                                      'users' => serialize($_POST['users']),
                                      'description' => $_POST['description'],
                                      'access_id' => $_POST['access_id'],
                                      'start_date' => $_POST['start_date'],
                                      'end_date' => $_POST['end_date']));
            Contribution::$cache = array();
            $contribution = Contribution::newFromId($_POST['id']);
            foreach($_POST['projects'] as $project){
                DBFunctions::insert('grand_contributions_projects',
                                    array('contribution_id' => $contribution->rev_id,
                                          'project_id' => $project));
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
            }
            
            Contribution::$cache = array();
            if(count(DBFunctions::select(array('grand_contribution_edits'),
                                         array('*'),
                                         array('id' => $contribution->getId(),
                                               'user_id' => $me->getId()))) == 0){
                DBFunctions::insert('grand_contribution_edits',
                                    array('id' => $contribution->getId(),
                                          'user_id' => $me->getId()));
            }
            $contributionAfter = Contribution::newFromName($_POST['title']);
            // Notification for new authors
            foreach($contributionAfter->getPeople() as $author){
                if($author instanceof Person){
                    if($contributionAfter->getAccessId() != $author->getId() && 
                       $contributionAfter->getAccessId() != 0){
                        continue;
                    }
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
                    if($contributionAfter->getAccessId() != $author->getId() && 
                       $contributionAfter->getAccessId() != 0){
                        continue;
                    }
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
            $data = DBFunctions::select(array('grand_contributions'),
                                        array('id'),
                                        array(),
                                        array('id' => 'DESC'),
                                        array(1));
            if(count($data) > 0){
                $id = $data[0]['id'];
            }
            $_POST['access_id'] = (isset($_POST['access_id'])) ? $_POST['access_id'] : 0;
            
            DBFunctions::insert('grand_contributions',
                                array('id' => $id + 1,
                                      'name' => $_POST['title'],
                                      'users' => serialize($_POST['users']),
                                      'description' => $_POST['description'],
                                      'access_id' => $_POST['access_id'],
                                      'start_date' => $_POST['start_date'],
                                      'end_date' => $_POST['end_date']));
            DBFunctions::insert('grand_contribution_edits',
                                array('id' => $id + 1,
                                      'user_id' => $me->getId()));
            Contribution::$cache = array();
            $contribution = Contribution::newFromName($_POST['title']);
            foreach($_POST['projects'] as $project){
                DBFunctions::insert('grand_contributions_projects',
                                    array('contribution_id' => $contribution->rev_id,
                                          'project_id' => $project));
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
            }
            
            foreach($_POST['users'] as $author){
                if(is_numeric($author)){
                    $person = Person::newFromId($author);
                    if($person != null && $person->getName() != null){
                        if($contribution->getAccessId() != $person->getId() && 
                           $contribution->getAccessId() != 0){
                            continue;
                        }
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
