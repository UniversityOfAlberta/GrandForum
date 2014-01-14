<?php

class UserEmailAPI extends API{

    function UserEmailAPI(){
        $this->addPOST("email", true, "The email address for this account.", "email@mail.com");
    }

    function processParams($params){
        if(isset($_POST['email']) && $_POST['email'] != ""){
            $_POST['email'] = str_replace("'", "&#39;", $_POST['email']);
        }
    }

	function doAction($noEcho=false){
        $person = Person::newFromName($_POST['user_name']);
        // Remove the person from previous mailing lists
        $uni = $person->getUni();
        foreach(MailingList::getListByUniversity($uni) as $list){
            MailingList::unsubscribe($list, $person);
        }
        foreach($person->getProjects() as $project){
            if(!$project->isSubProject()){
                MailingList::unsubscribe($project, $person);
            }
        }
        if($person->isRole(PNI) || 
           $person->isRole(CNI) ||
           $person->isRole(AR)){
            MailingList::unsubscribe("grand-forum-researchers", $person);
        }
        if($person->isRole(HQP)){
            MailingList::unsubscribe("grand-forum-hqps", $person);
        }
        if($person->isRole(RMC)){
            MailingList::unsubscribe("rmc-list", $person);
        }
        if($person->isRole(ISAC)){
            MailingList::unsubscribe("isac-list", $person);
        }
        if($person->isRole(CHAMP)){
	        foreach($person->getProjects() as $proj){
	            if($proj->getPhase() == PROJECT_PHASE){
	                MailingList::unsubscribe("grand-forum-p2-champions", $person);
	                break;
	            }
	        }
	    }
        if($person->isProjectLeader() ||
           $person->isProjectCoLeader()){
            $changeList = false;
            $changeList1 = false;
            $changeList2 = false;
            foreach($person->leadership() as $project){
                if($project->isSubProject()){
                    continue;
                }
                if($project->getPhase() == 1){
                    $changeList1 = true;
                }
                if($project->getPhase() == 2){
                    $changeList2 = true;
                }
                $changeList = true;
            }
            if($changeList){
                MailingList::unsubscribe("grand-forum-project-leaders", $person);
		    }
		    if($changeList1){
                MailingList::unsubscribe("grand-forum-p1-leaders", $person);
		    }
		    if($changeList2){
                MailingList::unsubscribe("grand-forum-p2-leaders", $person);
		    }
        }
        $sql = "UPDATE mw_user
                SET `user_email` = '{$_POST['email']}'
                WHERE user_id = '{$person->getId()}'";
        DBFunctions::execSQL($sql, true);
        
        $person->email = $_POST['email'];
        // Re-Add the person to the mailing lists using their new email
        foreach(MailingList::getListByUniversity($uni) as $list){
            MailingList::subscribe($list, $person);
        }
        foreach($person->getProjects() as $project){
            if(!$project->isSubProject()){
                MailingList::subscribe($project, $person);
            }
        }
        if($person->isRole(PNI) || 
           $person->isRole(CNI) ||
           $person->isRole(AR)){
            MailingList::subscribe("grand-forum-researchers", $person);
        }
        if($person->isRole(RMC)){
            MailingList::subscribe("rmc-list", $person);
        }
        if($person->isRole(HQP)){
            MailingList::subscribe("grand-forum-hqps", $person);
        }
        if($person->isRole(ISAC)){
            MailingList::subscribe("isac-list", $person);
        }
        if($person->isRole(CHAMP)){
	        foreach($person->getProjects() as $proj){
	            if($proj->getPhase() == PROJECT_PHASE){
	                MailingList::subscribe("grand-forum-p2-champions", $person);
	                break;
	            }
	        }
	    }
        if($person->isProjectLeader() ||
           $person->isProjectCoLeader()){
           $changeList = false;
           $changeList1 = false;
           $changeList2 = false;
            foreach($person->leadership() as $project){
                if($project->isSubProject()){
                    continue;
                }
                if($project->getPhase() == 1){
                    $changeList1 = true;
                }
                if($project->getPhase() == 2){
                    $changeList2 = true;
                }
                $changeList = true;
            }
            if($changeList){
                MailingList::subscribe("grand-forum-project-leaders", $person);
		    }
		    if($changeList1){
                MailingList::subscribe("grand-forum-p1-leaders", $person);
		    }
		    if($changeList2){
                MailingList::subscribe("grand-forum-p2-leaders", $person);
		    }
        }
        if(!$noEcho){
            echo "Account email updated\n";
        }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
