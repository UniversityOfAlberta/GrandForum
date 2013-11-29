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
            MailingList::unsubscribe($project, $person);
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
        if($person->isProjectLeader() ||
           $person->isProjectCoLeader()){
            $changeList = false;
            foreach($person->leadership() as $project){
                if($project->isSubProject()){
                    continue;
                }
                $changeList = true;
            }
            if($changeList){
                MailingList::unsubscribe("grand-forum-project-leaders", $person);
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
            MailingList::subscribe($project, $person);
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
        if($person->isProjectLeader() ||
           $person->isProjectCoLeader()){
           $changeList = false;
            foreach($person->leadership() as $project){
                if($project->isSubProject()){
                    continue;
                }
                $changeList = true;
            }
            if($changeList){
                MailingList::subscribe("grand-forum-project-leaders", $person);
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
