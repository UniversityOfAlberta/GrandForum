<?php

class ProjectMilestoneAPI extends API{

    var $update;

    function ProjectMilestoneAPI($update=false){
        $this->update = $update;
        $this->addPOST("project",true,"The name of the project","MEOW");
        $this->addPOST("milestone",true,"The title of the milestone","MEOW is great");
	    $this->addPOST("description",true,"The description for this milestone","Show that MEOW is great");
	    $this->addPOST("assessment",true,"The assessment for this milestone","Use surveys to determine MEOW\'s greatness");
	    $this->addPOST("status",true,"The status of this milestone. Can be one of either ('New','Revised','Continuing','Closed','Abandoned')","New");
	    $this->addPOST("people",false,"The people involved with this milestone, people separated by commas.", "First1.Last1, First2.Last2");
	    $this->addPOST("end_date",true,"The projected end date of this milestone, in the form YYYY-MM","2012-10");
	    $this->addPOST("comment",false,"The comment for this milestone. Usually this will only be used if the status is Closed or Abandoned","My comment");
	    $this->addPOST("new_title", false, "The new title for this milestone.  If left blank, the previous title is used", "My Milestone");
	    $this->addPOST("identifier", false, "Used when creating a new milestone.  If you do not know exactly what you are doing, do not use this parameter as in most cases it is not required", "123456");
    }

    function processParams($params){
        if(isset($_POST['description']) && $_POST['description'] != ""){
            $_POST['description'] = @addslashes(str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['description'])));
        }
        if(isset($_POST['assessment']) && $_POST['assessment'] != ""){
            $_POST['assessment'] = @addslashes(str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['assessment'])));
        }
        if(isset($_POST['status']) && $_POST['status'] != ""){
            $_POST['status'] = @addslashes(str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['status'])));
        }
        if(isset($_POST['end_date']) && $_POST['end_date'] != ""){
            $_POST['end_date'] = @addslashes(str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['end_date'])));
        }
        if(isset($_POST['comment']) && $_POST['comment'] != ""){
            $_POST['comment'] = @addslashes(str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['comment'])));
        }
        if(isset($_POST['project']) && $_POST['project'] != ""){
            $_POST['project'] = @addslashes(str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['project'])));
        }
        if(isset($_POST['people']) && $_POST['people'] != null){
            $_POST['people'] = @explode(", ", $_POST['people']);
        }
        else{
            $_POST['people'] = array();
        }
        if(isset($_POST['title']) && $_POST['title'] != ""){
            $_POST['title'] = @addslashes(str_replace("'", "&#39;", str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['title']))));
        }
        if(isset($_POST['new_title']) && $_POST['new_title'] != ""){
            $_POST['new_title'] = @addslashes(str_replace("'", "&#39;", str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['new_title']))));
        }
        else{
            $_POST['new_title'] = $_POST['title'];
        }
        if(isset($_POST['identifier']) && $_POST['identifier'] != ""){
            $_POST['identifier'] = @addslashes(str_replace("'", "&#39;", str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['identifier']))));
        }
    }

	function doAction($noEcho=false){
		$project = Project::newFromName($_POST['project']);
		$projectIds = array();
        $preds = $project->getAllPreds();
        foreach($preds as $pred){
            $projectIds[] = " OR project_id = '".$pred->getId()."'\n";
        }
		if(!isset($_POST['new_title']) || $_POST['new_title'] == ""){
		    return "A valid title must be provided";
		}
		
		$people = array();
		if(isset($_POST['people']) && count($_POST['people']) > 0){
            foreach($_POST['people'] as $person){
                $p = Person::newFromNameLike($person);
                if($p != null && $p->getName() != ""){
                    $people[] = $p;
                }
            }
        }
		
		if(!$noEcho){
		    if($project == null || $project->getName() == null){
		        echo "A valid project must be provided\n";
		        exit;
		    }
		    $person = Person::newFromName($_POST['user_name']);
            if($person == null || $person->getName() == ""){
                echo "A valid user name must be provided\n";
                exit;
            }
		}
		$me = Person::newFromName($_POST['user_name']);
		
        if($this->update){
            $sql = sprintf("UPDATE grand_milestones
                    SET `end_date` = CURRENT_TIMESTAMP
                    WHERE (project_id = '{$project->getId()}'
                        ".implode("", $projectIds)."
                    )
                    AND title = '%s'
                    ORDER BY id DESC
                    LIMIT 1", mysql_real_escape_string($_POST['title']));
            DBFunctions::execSQL($sql, true);
        }
        $sql = sprintf("SELECT *

                FROM grand_milestones
                WHERE title = '%s'
                AND (project_id = '{$project->getID()}' 
                     ".implode("", $projectIds)."
                     )", mysql_real_escape_string($_POST['title']));
        
        $rows = DBFunctions::execSQL($sql);
		
        if(count($rows) > 0 && $this->update){
            $sql = sprintf("INSERT INTO grand_milestones
                          (`milestone_id`, 
                           `project_id`, 
                           `title`, 
                           `status`, 
                           `description`, 
                           `assessment`, 
                           `comment`, 
                           `edited_by`,
                           `start_date`, 
                           `projected_end_date`)
                    VALUES ('{$rows[0]['milestone_id']}', 
                            '{$project->getId()}', 
                            '%s', 
                            '{$_POST['status']}', 
                            '%s',
                            '%s',
                            '%s',
                            '{$me->getId()}',
                             CURRENT_TIMESTAMP,
                            '{$_POST['end_date']}-00')",
                            mysql_real_escape_string($_POST['new_title']),
                            mysql_real_escape_string($_POST['description']),
                            mysql_real_escape_string($_POST['assessment']),
                            mysql_real_escape_string($_POST['comment'])
                            );
            $_POST['title'] = $_POST['new_title'];
            DBFunctions::execSQL($sql, true);
            Milestone::$cache = array();
            $this->updatePeople($people);
            Milestone::$cache = array();
        }
        else if(!$this->update){
            if(!isset($_POST['identifier']) || $_POST['identifier'] == ""){
                $_POST['identifier'] = 0;
            }
            else if($_POST['identifier'] != 0){
                if($this->checkIdentifier($project)){
                    $sql = sprintf("UPDATE grand_milestones
                            SET `title` = '%s',
                                `description` = '%s',
                                `assessment` = '%s',
                                `start_date` = CURRENT_TIMESTAMP,
                                `projected_end_date` = '{$_POST['end_date']}-00'
                            WHERE identifier = '{$_POST['identifier']}'
                            AND (project_id = '{$project->getId()}'
                                 ".implode("", $projectIds)."
                                 )",
                            mysql_real_escape_string($_POST['title']),
                            mysql_real_escape_string($_POST['description']), 
                            mysql_real_escape_string($_POST['assessment'])
                            );
                                
                    DBFunctions::execSQL($sql, true);
                    Milestone::$cache = array();
                    $this->updatePeople($people);
                    Milestone::$cache = array();
                    return;
                }
            }
            $sql = "SELECT MAX(milestone_id) as max
                    FROM grand_milestones";
            $rows = DBFunctions::execSQL($sql);
		    if($rows[0]['max'] == null){
		        $milestoneId = 0;
		    }
		    else{
		        $milestoneId = $rows[0]['max']+1;
		    }
            $sql = sprintf("INSERT INTO grand_milestones
                    (`identifier`,
                     `milestone_id`,
                     `project_id`,
                     `title`,
                     `status`,
                     `description`,
                     `assessment`,
                     `edited_by`,
                     `start_date`,
                     `projected_end_date`)
                    VALUES 
                    ('{$_POST['identifier']}',
                     '$milestoneId',    
                     '{$project->getId()}',
                     '%s',
                     '{$_POST['status']}',
                     '%s',
                     '%s',
                     '{$me->getId()}',
                     CURRENT_TIMESTAMP,
                     '{$_POST['end_date']}-00')",
                      mysql_real_escape_string($_POST['title']),
                      mysql_real_escape_string($_POST['description']),
                      mysql_real_escape_string($_POST['assessment'])
                     );
            DBFunctions::execSQL($sql, true);
            Milestone::$cache = array();
            $this->updatePeople($people);
        }
        
        if(!$noEcho){
            echo "Project milestones updated\n";
        }
        Milestone::$cache = array();
	}
	
	function updatePeople($people){
	    global $wgUser;
	    $me = Person::newFromId($wgUser->getId());
	    $milestone = Milestone::newFromTitle($_POST['title']);
        if($milestone != null && $milestone->getTitle() != null){
            foreach($people as $person){
                $skip = false;
                if($milestone->getParent() != null){
                    foreach($milestone->getParent()->getPeople() as $p){
                        if($p->getId() == $person->getId()){
                            $skip = true;
                            break;
                        }
                    }
                }
                if(!$skip){
                    // Person is being added to this Milestone
                    $sql = "INSERT INTO `grand_milestones_people`
                            (`milestone_id`,`person_id`) VALUES
                            ('{$milestone->getId()}','{$person->getId()}')";
                    DBFunctions::execSQL($sql, true);
                    Notification::addNotification($me, $person, "Milestone Involvement Added", "You have been added as being involved with the Milestone entitled <i>{$milestone->getTitle()}</i>", "{$milestone->getProject()->getUrl()}");
                }
                else{
                    // Person Remains part of this Milestone
                    $sql = "INSERT INTO `grand_milestones_people`
                            (`milestone_id`,`person_id`) VALUES
                            ('{$milestone->getId()}','{$person->getId()}')";
                    DBFunctions::execSQL($sql, true);
                    Notification::addNotification($me, $person, "Milestone Changed", "Your Milestone entitled <i>{$milestone->getTitle()}</i> has been modified", "{$milestone->getProject()->getUrl()}");
                }
            }
            if($milestone->getParent() != null){
                foreach($milestone->getParent()->getPeople() as $person){
                    $skip = false;
                    foreach($people as $p){
                        if($p->getId() == $person->getId()){
                            $skip = true;
                            break;
                        }
                    }
                    if(!$skip){
                        // Person is Removed from this Milestone
                        Notification::addNotification($me, $person, "Milestone Involvement Removed", "You have been removed as being involved with the Milestone entitled <i>{$milestone->getTitle()}</i>", "{$milestone->getProject()->getUrl()}");
                    }
                }
            }
        }
        foreach(array_merge($milestone->getProject()->getLeaders(), $milestone->getProject()->getCoLeaders()) as $leader){
            $skip = false;
            foreach($people as $person){
                if($leader->getId() == $person->getId()){
                    $skip = true;
                    break;
                }
            }            
            if($milestone->getParent() != null){
                $removed = true;
                foreach($milestone->getParent()->getPeople() as $person){
                    if($leader->getId() == $person->getId()){
                        foreach($people as $p){
                            if($p->getId() == $person->getId()){
                                $removed = false;
                                break;
                            }
                        }
                        $skip = $removed || $skip;
                        break;
                    }
                }
            }
            if(!$skip){
                Notification::addNotification($me, $leader, "Milestone Changed", "{$milestone->getProject()->getName()}'s Milestone entitled <i>{$milestone->getTitle()}</i> has been modified", "{$milestone->getProject()->getUrl()}");
            }
        }
	}
	
	function checkIdentifier($project){
	    $projectIds = array();
        $preds = $project->getAllPreds();
        foreach($preds as $pred){
            $projectIds[] = " OR project_id = '".$pred->getId()."'\n";
        }
	    $sql = "SELECT *
	            FROM grand_milestones
	            WHERE (project_id = '{$project->getId()}'
	                   ".implode("", $projectIds)."
	                  )
	            AND identifier = '{$_POST['identifier']}'";
	    $data = DBFunctions::execSQL($sql);
	    if(count($data) > 0){
	        return true;
	    }
	    else{
	        return false;
	    }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
