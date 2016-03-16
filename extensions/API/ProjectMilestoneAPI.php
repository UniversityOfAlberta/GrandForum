<?php

class ProjectMilestoneAPI extends API{

    var $update;

    function ProjectMilestoneAPI($update=false){
        $this->update = $update;
        $this->addPOST("project",true,"The name of the project","MEOW");
        $this->addPOST("leader",false,"The name of the leader for this milestone","First.Last");
        $this->addPOST("activity",true,"The name of the activity", "Analysis");
        $this->addPOST("activity_id",false,"The id of the activity", "2");
        $this->addPOST("milestone",true,"The title of the milestone","MEOW is great");
        $this->addPOST("problem",true,"The problem of this milestone","Show that MEOW is great");
	    $this->addPOST("description",true,"The description for this milestone","Show that MEOW is great");
	    $this->addPOST("assessment",true,"The assessment for this milestone","Use surveys to determine MEOW\'s greatness");
	    $this->addPOST("status",true,"The status of this milestone. Can be one of either ('New','Revised','Continuing','Closed','Abandoned')","New");
	    $this->addPOST("people",false,"The people involved with this milestone, people separated by commas.", "First1.Last1, First2.Last2");
	    $this->addPOST("end_date",true,"The projected end date of this milestone, in the form YYYY-MM","2012-10");
	    $this->addPOST("quarters",false,"The yearly quarters that this milestone is active, in the form YYYY:Q,YYYY:Q", "2012:1,2012:2");
	    $this->addPOST("comment",false,"The comment for this milestone. Usually this will only be used if the status is Closed or Abandoned","My comment");
	    $this->addPOST("new_title", false, "The new title for this milestone.  If left blank, the previous title is used", "My Milestone");
	    $this->addPOST("identifier", false, "Used when creating a new milestone.  If you do not know exactly what you are doing, do not use this parameter as in most cases it is not required", "123456");
	    $this->addPOST("id", false, "Used to more accurately reference milestones.", "98");
    }

    function processParams($params){
        if(isset($_POST['problem']) && $_POST['problem'] != ""){
            $_POST['problem'] = @addslashes(str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['problem'])));
        }
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
        if(isset($_POST['leader']) && $_POST['leader'] != ""){
            $_POST['leader'] = @addslashes(str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['leader'])));
        }
        if(isset($_POST['people']) && $_POST['people'] != null){
            $_POST['people'] = @$_POST['people'];
        }
        else{
            $_POST['people'] = array();
        }
        if(isset($_POST['title']) && $_POST['title'] != ""){
            $_POST['title'] = @addslashes(str_replace("'", "&#39;", str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['title']))));
        }
        if(isset($_POST['activity']) && $_POST['activity'] != ""){
            $_POST['activity'] = @addslashes(str_replace("'", "&#39;", str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['activity']))));
        }
        if(isset($_POST['activity_id']) && $_POST['activity_id'] != ""){
            $_POST['activity_id'] = @addslashes(str_replace("'", "&#39;", str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['activity_id']))));
        }
        if(isset($_POST['new_title']) && $_POST['new_title'] != ""){
            $_POST['new_title'] = @addslashes(str_replace("'", "&#39;", str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['new_title']))));
        }
        else{
            $_POST['new_title'] = $_POST['title'];
        }
        if(isset($_POST['quarters']) && $_POST['quarters'] != ""){
            $_POST['quarters'] = @addslashes(str_replace("'", "&#39;", str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['quarters']))));
        }
        if(isset($_POST['identifier']) && $_POST['identifier'] != ""){
            $_POST['identifier'] = @addslashes(str_replace("'", "&#39;", str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['identifier']))));
        }
        if(isset($_POST['id']) && $_POST['id'] != ""){
            $_POST['id'] = @addslashes(str_replace("'", "&#39;", str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['id']))));
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
		
        $people = "";
        if(isset($_POST['people'])){
            $people = $_POST['people'];
        }
		
		$leader = 0;
		if(isset($_POST['leader'])){
		    $l = Person::newFromNameLike(trim($_POST['leader']));
		    if($l != null && $l->getName() != ""){
		        $leader = $l->getId();
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
		
		DBFunctions::begin();
		if(isset($_POST['id']) && $_POST['id'] != ''){
            $join = "milestone_id = '{$_POST['id']}'";
        }
        else{
            $join = "title = '".DBFunctions::escape($_POST['title'])."'";
        }
        if(!isset($_POST['problem'])){
            $_POST['problem'] = '';
        }
        if($this->update){
            $sql = "UPDATE grand_milestones
                    SET `end_date` = CURRENT_TIMESTAMP
                    WHERE (project_id = '{$project->getId()}'
                        ".implode("", $projectIds)."
                    )
                    AND $join
                    ORDER BY id DESC
                    LIMIT 1";
            DBFunctions::execSQL($sql, true);
        }
        
        $activityId = 0;
        if(isset($_POST['activity_id'])){
            $activity = Activity::newFromId($_POST['activity_id']);
            $activityId = $_POST['activity_id'];
            if($activity->getName() != $_POST['activity']){
                DBFunctions::update('grand_activities',
                                    array('name' => $_POST['activity']),
                                    array('id' => $activity->getId()));
            }
        }
        else if(isset($_POST['activity'])){
            $activity = Activity::newFromName($_POST['activity'], $project->getId());
            if($activity->getName() != ""){
                $activityId = $activity->getId();
            }
            else if($_POST['activity'] != ""){
                // Activity not found, so add it
                DBFunctions::insert('grand_activities',
                                    array('name' => $_POST['activity'],
                                          'project_id' => $project->getId()));
                $activity = Activity::newFromName($_POST['activity'], $project->getId());
                $activityId = $activity->getId();
            }
        }
        if(isset($_POST['id'])){
		    $milestone = Milestone::newFromId($_POST['id']);
		    if($milestone->getTitle() == $_POST['new_title'] &&
		       $milestone->getPeopleText() == $_POST['people'] &&
		       $milestone->quarters == $_POST['quarters'] &&
		       $milestone->getStatus() == $_POST['status'] &&
		       $milestone->getLeader()->getNameForForms() == $_POST['leader'] &&
		       $milestone->getComment() == $_POST['comment']){
		        return;   
		   }
		}
        $sql = "SELECT *
                FROM grand_milestones
                WHERE $join
                AND (project_id = '{$project->getID()}' 
                     ".implode("", $projectIds)."
                     )";
        
        $rows = DBFunctions::execSQL($sql);
        if(count($rows) > 0 && $this->update){
            $milestoneId = $rows[0]['milestone_id'];
            DBFunctions::insert('grand_milestones',
		                        array('activity_id'         => $activityId,
		                              'milestone_id'        => $milestoneId,
		                              'project_id'          => $project->getId(),
		                              'leader'              => $leader,
		                              'title'               => $_POST['new_title'],
		                              'status'              => $_POST['status'],
		                              'problem'             => $_POST['problem'],
		                              'description'         => $_POST['description'],
		                              'assessment'          => $_POST['assessment'],
		                              'comment'             => $_POST['comment'],
		                              'people'              => $_POST['people'],
		                              'edited_by'           => $me->getId(),
		                              'quarters'            => $_POST['quarters'],
		                              'start_date'          => EQ(COL('CURRENT_TIMESTAMP')),
		                              'projected_end_date'  => "{$_POST['end_date']}-00"));
            $_POST['title'] = $_POST['new_title'];
            Milestone::$cache = array();
            //$this->updatePeople($people);
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
                                `problem` = '%s',
                                `description` = '%s',
                                `assessment` = '%s',
                                `start_date` = CURRENT_TIMESTAMP,
                                `projected_end_date` = '{$_POST['end_date']}-00'
                            WHERE identifier = '{$_POST['identifier']}'
                            AND (project_id = '{$project->getId()}'
                                 ".implode("", $projectIds)."
                                 )",
                            DBFunctions::escape($_POST['title']),
                            DBFunctions::escape($_POST['problem']), 
                            DBFunctions::escape($_POST['description']), 
                            DBFunctions::escape($_POST['assessment'])
                            );
                    
                    DBFunctions::execSQL($sql, true);
                    Milestone::$cache = array();
                    //$this->updatePeople($people);
                    Milestone::$cache = array();
                    DBFunctions::commit();
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
		    DBFunctions::insert('grand_milestones',
		                        array('identifier'          => $_POST['identifier'],
		                              'activity_id'         => $activityId,
		                              'milestone_id'        => $milestoneId,
		                              'project_id'          => $project->getId(),
		                              'leader'              => $leader,
		                              'title'               => $_POST['title'],
		                              'status'              => $_POST['status'],
		                              'problem'             => $_POST['problem'],
		                              'description'         => $_POST['description'],
		                              'assessment'          => $_POST['assessment'],
		                              'comment'             => @$_POST['comment'],
		                              'people'              => $_POST['people'],
		                              'edited_by'           => $me->getId(),
		                              'quarters'            => $_POST['quarters'],
		                              'start_date'          => EQ(COL('CURRENT_TIMESTAMP')),
		                              'projected_end_date'  => "{$_POST['end_date']}-00"));
            Milestone::$cache = array();
            $staffs = array_merge(Person::getAllPeople(STAFF), Person::getAllPeople(MANAGER));
            foreach($staffs as $staff){
                Notification::addNotification($me, $staff, "Milestone Added", "A new milestone <i>{$_POST['title']}</i> has been added to the project <b>{$project->getName()}</b>", "");
            }
            //$this->updatePeople($people);
        }
        
        if(!$noEcho){
            echo "Project milestones updated\n";
        }
        Milestone::$cache = array();
        DBFunctions::commit();
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
                    DBFunctions::insert('grand_milestones_people',
                                        array('milestone_id' => $milestone->getId(),
                                              'user_id'      => $person->getId()));
                    Notification::addNotification($me, $person, "Milestone Involvement Added", "You have been added as being involved with the Milestone entitled <i>{$milestone->getTitle()}</i>", "{$milestone->getProject()->getUrl()}");
                }
                else{
                    // Person Remains part of this Milestone
                    DBFunctions::insert('grand_milestones_people',
                                        array('milestone_id' => $milestone->getId(),
                                              'user_id'      => $person->getId()));
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
        /*
        foreach($milestone->getProject()->getLeaders() as $leader){
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
        }*/
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
