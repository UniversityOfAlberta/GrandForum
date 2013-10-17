<?php

class ProjectChampionsAPI extends API{

    function ProjectChampionsAPI(){
        $this->addPOST("project",true,"The name of the project","MEOW");
	    $this->addPOST("champion_name",true,"Name of the champion","John Doe");
        $this->addPOST("champion_email",true,"Email of the champion","john@doe.com");
        $this->addPOST("champion_org",true,"Organization of the champion","JDoe Inc.");
        $this->addPOST("champion_title",true,"Title of the champion","Chief Technology Officer");
    }

    function processParams($params){
        if(isset($_POST['champion_name']) && $_POST['champion_name'] != ""){
            $_POST['champion_name'] = mysql_real_escape_string( $_POST['champion_name'] );
        }
        if(isset($_POST['champion_email']) && $_POST['champion_email'] != ""){
            $_POST['champion_email'] = mysql_real_escape_string( $_POST['champion_email'] );
        }
        if(isset($_POST['champion_org']) && $_POST['champion_org'] != ""){
            $_POST['champion_org'] = mysql_real_escape_string( $_POST['champion_org'] );
        }
        if(isset($_POST['champion_title']) && $_POST['champion_title'] != ""){
            $_POST['champion_title'] = mysql_real_escape_string( $_POST['champion_title'] );
        }
        
    }

	function doAction($noEcho=false){
		$project = Project::newFromName($_POST['project']);
		if(!$noEcho){
		    if($project == null || $project->getName() == null){
		        echo "A valid project must be provided\n";
		        exit;
		    }
		    $person = Person::newFromName($_POST['user_name']);
            $isLead = false;
            foreach($me->getLeadProjects() as $p){
                if($p->getId() == $project->getId()){
                    $isLead = true;
                    break;
                }
            }
            if(!$isLead){
                echo "You must be logged in as a project leader\n";
                exit;
            }
		}
		
	    if(isset($_POST['champion_name']) && !empty($_POST['champion_name'])){
            DBFunctions::begin();
            $sql = "SELECT id
                    FROM grand_project_champions
                    WHERE project_id = '{$project->getId()}'
                    ORDER BY id DESC LIMIT 1";
            $data = DBFunctions::execSQL($sql);
            $last_champ_id = (isset($data[0]['id']))? $data[0]['id'] : null;

            if(isset($data[0]['id'])){            
                $sql = "UPDATE grand_project_champions
                        SET `end_date` = CURRENT_TIMESTAMP
                        WHERE project_id = '{$project->getId()}' 
                        AND id = '{$last_champ_id}'";
                DBFunctions::execSQL($sql, true);
            }
            $sql = "INSERT INTO grand_project_champions (`project_id`,`champion_name`,`champion_email`,`champion_org`,`champion_title`,`start_date`)
                    VALUES ('{$project->getId()}','{$_POST['champion_name']}','{$_POST['champion_email']}','{$_POST['champion_org']}','{$_POST['champion_title']}',CURRENT_TIMESTAMP)";
            DBFunctions::execSQL($sql, true);

            if(!$noEcho){
                echo "Project champion updated\n";
            }
        }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
