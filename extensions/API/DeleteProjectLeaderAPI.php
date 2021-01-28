<?php

class DeleteProjectLeaderAPI extends API{

    function __construct(){
        $this->addPOST("name", true, "The User Name of the user to add", "UserName");
        $this->addPOST("project", true, "The name of the project", "MEOW");
        $this->addPOST("comment", true, "A comment for why the user is no longer a leade of this project", "My Reason");
        $this->addPOST("co_lead", false,"Whether or not this user was a co-leader or not.  If not provided, 'False' is assumed", "False");
        $this->addPOST("manager", false,"Whether or not this user should be a manager or not.  If not provided, 'False' is assumed", "False");
        $this->addPOST("effective_date", false, "The date when the project change should be made in the format YYYY-MM-DD.  If this value is not included, the current time is assumed.", "2012-10-30");
    }

    function processParams($params){
        $_POST['role'] = $_POST['project'];
        $_POST['user'] = $_POST['name'];
        $_POST['comment'] = str_replace("'", "", $_POST['comment']);
    }

	function doAction($noEcho=false){
		global $wgRequest, $wgUser, $wgServer, $wgScriptPath;
		$groups = $wgUser->getGroups();
        $me = Person::newFromId($wgUser->getId());
        $project = Project::newFromName($_POST['role']);
		if($me->isRoleAtLeast(STAFF) || $me->leadershipOf($project->getParent())){
            // Actually Add the Project Member
            $person = Person::newFromName($_POST['user']);
            
            if(!isset($_POST['co_lead']) || ($_POST['co_lead'] != "False" && $_POST['co_lead'] != "True")){
                $_POST['co_lead'] = 'False';
            }
            if(!isset($_POST['manager']) || ($_POST['manager'] != "True")){
                $_POST['manager'] = 0;
            }
            else{
                $_POST['manager'] = 1;
            }
            
            $comment = str_replace("'", "&#39;", $_POST['comment']);
            if(!$noEcho){
                if($person->getName() == null){
                    echo "There is no person by the name of '{$_POST['user']}'\n";
                    exit;
                }
                else if($project->getName() == null){
                    echo "There is no project by the name of '{$_POST['role']}'\n";
                    exit;
                }
            }
            $effectiveDate = "CURRENT_TIMESTAMP";
            if(isset($_POST['effective_date']) && $_POST['effective_date'] != ""){
                $effectiveDate = "'{$_POST['effective_date']} 00:00:00'";
            }
            else{
                $sql = "SELECT CURRENT_TIMESTAMP";
                $data = DBFunctions::execSQL($sql);
                $effectiveDate = "'{$data[0]['CURRENT_TIMESTAMP']}'";
            }
            
            $lead_type = "leader";
            if($_POST['co_lead'] == "True"){
                $lead_type = "co-leader";
            }else if($_POST['manager'] == 1){
                $lead_type = "manager";
            }
            
            MailingList::unsubscribeAll($person);

            $sql = "UPDATE grand_project_leaders
	                SET `comment` = '$comment',
	                    `end_date` = $effectiveDate
	                WHERE `project_id` = '{$project->getId()}'
	                AND `user_id` = '{$person->getId()}'
	                AND `type` = '{$lead_type}'
	                ORDER BY `start_date` DESC LIMIT 1";
            DBFunctions::execSQL($sql, true);
            
            foreach($project->getAllPreds() as $pred){
                $sql = "UPDATE grand_project_leaders
	                    SET `comment` = '$comment',
	                        `end_date` = $effectiveDate
	                    WHERE `project_id` = '{$pred->getId()}'
	                    AND `user_id` = '{$person->getId()}'
	                    AND `type` = '{$lead_type}'
	                    ORDER BY `start_date` DESC LIMIT 1";
                DBFunctions::execSQL($sql, true);
                Cache::delete("project{$pred->getId()}_people", true);
            }
            Cache::delete("project{$project->getId()}_people", true);
            Person::$cache = array();
            Person::$idsCache = array();
            Person::$namesCache = array();
            Person::$leaderCache = array();
            $person = Person::newFromId($person->getId());
            MailingList::subscribeAll($person);
            
            $sql = "SELECT CURRENT_TIMESTAMP";
            $data = DBFunctions::execSQL($sql);
            $effectiveDate = "'{$data[0]['CURRENT_TIMESTAMP']}'";
           
            Notification::addNotification($me, $person, "Project Leader Removed", "Effective $effectiveDate you are no longer a project {$lead_type} of '{$project->getName()}'", "{$person->getUrl()}");
            $leaders = $project->getLeaders();
            if(count($leaders) > 0){
                foreach($leaders as $leader){
                    if($leader->getName() != $person->getName()){
                        Notification::addNotification($me, $leader, "Project Leader Removed", "Effective $effectiveDate {$person->getReversedName()} is no longer a {$lead_type} of '{$project->getName()}'", "{$person->getUrl()}");
                    }
                }
            }
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
