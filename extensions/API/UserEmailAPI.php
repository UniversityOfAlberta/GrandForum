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
        foreach($person->getProjects() as $project){
            MailingList::unsubscribe($project, $person);
        }
        if($person->isRole(PNI) || 
           $person->isRole(CNI)){
            $command =  "/usr/lib/mailman/bin/remove_members -n -N grand-forum-researchers {$person->getEmail()}";
		    exec($command, $output);
        }
        if($person->isRole(HQP)){
            $command =  "/usr/lib/mailman/bin/remove_members -n -N grand-forum-hqps {$person->getEmail()}";
		    exec($command, $output);
        }
        if($person->isProjectLeader() ||
           $person->isProjectCoLeader()){
            $command =  "/usr/lib/mailman/bin/remove_members -n -N grand-forum-project-leaders {$person->getEmail()}";
		    exec($command, $output);
        }
        $sql = "UPDATE mw_user
                SET `user_email` = '{$_POST['email']}'
                WHERE user_id = '{$person->getId()}'";
        DBFunctions::execSQL($sql, true);
        
        $person->email = $_POST['email'];
        // Re-Add the person to the mailing lists using their new email
        foreach($person->getProjects() as $project){
            MailingList::subscribe($project, $person);
        }
        if($person->isRole(PNI) || 
           $person->isRole(CNI)){
            $command =  "echo {$person->getEmail()} | /usr/lib/mailman/bin/add_members --welcome-msg=n --admin-notify=n -r - grand-forum-researchers";
		    exec($command, $output);
        }
        if($person->isRole(HQP)){
            $command =  "echo {$person->getEmail()} | /usr/lib/mailman/bin/add_members --welcome-msg=n --admin-notify=n -r - grand-forum-hqps";
		    exec($command, $output);
        }
        if($person->isProjectLeader() ||
           $person->isProjectCoLeader()){
            $command =  "echo {$person->getEmail()} | /usr/lib/mailman/bin/add_members --welcome-msg=n --admin-notify=n -r - grand-forum-project-leaders";
		    exec($command, $output);
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
