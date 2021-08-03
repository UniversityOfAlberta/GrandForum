<?php

/**
 * @package GrandObjects
 */

class Milestone {

    static $cache = array();
    
    static $statuses = array("New" => "#BBBBBB",
                             "Completed" => "#3399FF",
                             "On Going" => "#55BB55",
                             "Late" => "#FFDD00",
                             "Problem" => "#FF8800",
                             "Abandoned" => "#FF6666");
                             
    static $fesStatuses = array("New" => "#BBBBBB",
                                "Completed" => "#3399FF",
                                "Pending" => "#55BB55",
                                "Abandoned" => "#FF6666");
                                
    static $modifications = array("" => "transparent", 
                                  "Revised" => "#0000FF", 
                                  "Postponed" => "#FF0000");
                                  
    static $endUsers = array("",
                             "Researchers",
                             "Policymakers",
                             "Government Officials",
                             "Funders",
                             "Media",
                             "General Public",
                             "Other");

    var $id;
    var $activity;
    var $milestone_id;
    var $order;
    var $parent;
    var $project;
    var $leader;
    var $people;
    var $peopleText;
    var $peopleWaiting;
    var $title;
    var $status;
    var $modification;
    var $description;
    var $endUser;
    var $parentWaiting;
    var $editedBy;
    var $start_date;
    var $end_date;
    var $quarters;
    var $comment;
    
    /**
     * Creates a Milestone from the given milestone_id and id
     * @param integer $milestone_id The id of the milestone
     * @param integer $id The id of the revision (optional)
     * @return Milestone The milestone
     */
    function newFromId($milestone_id, $id=2147483647){
        if(isset(self::$cache[$milestone_id."id".$id])){
            return self::$cache[$milestone_id."id".$id];
        }
        $data = DBFunctions::select(array('grand_milestones'),
                                    array('*'),
                                    array('milestone_id' => EQ($milestone_id),
                                          'id' => LTEQ($id)),
                                    array('id' => 'DESC'),
                                    array('2'));
        $milestone = new Milestone($data);
        self::$cache[$milestone_id."id".$id] = &$milestone;
        self::$cache[$milestone_id."id".$milestone->getId()] = &$milestone;
        return $milestone;
    }
    
    /**
     * Creates a Milestone from the given revision id
     * @param integer $id The id of the revision
     * @return Milestone The milestone
     */
    function newFromIndex($id=2147483647){
        $data = DBFunctions::select(array('grand_milestones'),
                                    array('*'),
                                    array('id' => EQ($id)));
        $milestone = new Milestone($data);
        return $milestone;
    }

    /**
     * Creates a new Milestone from the given title and revision id
     * @param string $milestone_title The title of the Milestone
     * @param integer $id The revision id of the Milestone
     * @return Milestone The Milestone
     */
    function newFromTitle($milestone_title, $id=2147483647){
        $milestone_title = str_replace("'", "#39;", $milestone_title);
        if(isset(self::$cache[$milestone_title."id".$id])){
            return self::$cache[$milestone_title."id".$id];
        }
        $data = DBFunctions::select(array('grand_milestones'),
                                    array('*'),
                                    array('title' => EQ($milestone_title),
                                          'id' => EQ($id)),
                                    array('id' => 'DESC'));
        $milestone = new Milestone($data);
        self::$cache[$milestone_title."id".$id] = &$milestone;
        self::$cache[$milestone_title."id".$milestone->getId()] = &$milestone;
        return $milestone;
    }

    function Milestone($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->activity_id = $data['0']['activity_id'];
            $this->milestone_id = $data[0]['milestone_id'];
            $this->order = $data[0]['order'];
            $this->title = $data[0]['title'];
            if(isset($data[1])){
                $this->parent = $data[1]['id'];
                $this->parentWaiting = true; // Lazyness
            }
            else{
                $this->parent = null;
                $this->parentWaiting = false;
            }
            $this->status = $data[0]['status'];
            $this->modification = $data[0]['modification'];
            $this->project = Project::newFromId($data[0]['project_id']);
            $this->leader = json_decode($data[0]['leader']);
            if(!is_array($this->leader)){
                $this->leader = array($this->leader);
            }
            $this->people = array();
            $this->peopleText = $data[0]['people'];
            $this->peopleWaiting = true;
            $this->description = $data[0]['description'];
            $this->endUser = $data[0]['end_user'];
            $this->quarters = $data[0]['quarters'];
            $this->start_date = $data[0]['start_date'];
            $this->end_date = $data[0]['end_date'];
            $this->projected_end_date = $data[0]['projected_end_date'];
            $this->comment = $data[0]['comment'];
            $this->editedBy = Person::newFromId($data[0]['edited_by']);
        }
    }
    
    /**
     * Returns the revision id of this Milestone
     * @return integer The revision id of this Milestone
     */
    function getId(){
        return $this->id;
    }
    
    /**
     * Returns the Activity associated with this Milestone (null if there is no activity)
     * @return Activity The Activity associated with this Milestone
     */
    function getActivity(){
        if($this->activity == null){
            $data = DBFunctions::select(array('grand_activities'),
                                        array('name'),
                                        array('id' => EQ($this->activity_id)));
            if(isset($data[0])){
                $this->activity = Activity::newFromId($this->activity_id);
                return $this->activity;
            }
        }
        return $this->activity;
    }
    
    /**
     * Returns the id of this Milestone
     * @return integer The id of this Milestone
     */
    function getMilestoneId(){
        return $this->milestone_id;
    }
    
    /**
     * Returns the order of this Milestone
     * @return integer The order of this Milestone
     */
    function getOrder(){
        return $this->order;
    }

    /**
     * Returns the parent of this Milestone
     * (Lazy to help avoid potential infinite loops, and to improve performance on Object construction)
     * @return Milestone the parent of this Milestone
     */
    function getParent(){
        if($this->parentWaiting){
            $this->parent = Milestone::newFromId($this->milestone_id, $this->parent);
            $this->parentWaiting = false;
        }
        if($this->parent == null ||
           $this->parent->getId() == null){
            $this->parent = null; 
        }
        return $this->parent;
    }
    
    /**
     * Returns the Milestone revision closest to the given date
     * @param string $date The date of the revision
     * @return Milestone The revision closest to the given date
     */
    function getRevisionByDate($date){
        $parent = $this;
        $dateTime = strtotime($date);
        $minDistance = 1000000000;
        $smallestSoFar = null;
        while($parent != null){
            $startDate = strtotime($parent->getStartDate());
            $endDate = $parent->getEndDate();
            if($endDate == "0000-00-00 00:00:00"){
                $endDate = time();
            }
            else{
                $endDate = strtotime($endDate);
            }
            
            $avgTime = ($startDate+$endDate)/2;
            $diffTime = abs($avgTime - $dateTime);
            if($diffTime <= $minDistance && $startDate <= $dateTime && $endDate >= $dateTime){
                $minDistance = $diffTime;
                $smallestSoFar = $parent;
            }
            $parent = $parent->getParent();
        }
        return $smallestSoFar;
    }
    
    /**
     * Return the title of this Milestone
     * @return string The title of this Milestone
     */
    function getTitle(){
        return $this->title;
    }

    /**
     * Returns the status of this Milestone
     * @return string The status of this Milestone
     */
    function getStatus(){
        return $this->status;
    }
    
    /**
     * Returns the modification of this Milestone
     * @return string The modification of this Milestone
     */
    function getModification(){
        return $this->modification;
    }
    
    /**
     * Returns the Project that this Milestone belongs to
     * @return Project The Project that this Milestone belongs to
     */
    function getProject(){
        return $this->project;    
    }
    
    /**
     * Returns the People who leads this Milestone
     * @return array The People who leads this Milestone
     */
    function getLeaders(){
        $leaders = array();
        foreach($this->leader as $leader){
            $leaders[] = Person::newFromId($leader);
        }
        if(count($leaders) == 0){
            $leaders[] = new Person(array());
        }
        return $leaders;
    }

    /**
     * Returns the People involved in this Milestone revision
     * @return array The People involved in this Milestone revision
     */
    function getPeople(){
        if($this->peopleWaiting){
            $data = DBFunctions::select(array('grand_milestones_people' => 'p',
                                              'mw_user' => 'u'),
                                        array('u.user_id'),
                                        array('milestone_id' => EQ($this->id),
                                              'u.user_id' => EQ(COL('p.user_id')),
                                              'u.deleted' => NEQ(1)));
            foreach($data as $row){
                $this->people[] = Person::newFromId($row['user_id']);
            }
            $this->peopleWaiting = false;
        }
        return $this->people;
    }
    
    /**
     * Returns the People involved in this Milestone revision
     * @return array The People involved in this Milestone revision
     */
    function getPeopleText(){
        return $this->peopleText;
    }
    
    /**
     * Returns the description of this Milestone
     * @return string The description of this Milestone
     */
    function getDescription(){
        return str_replace("\\'", "&#39;", 
               str_replace("\\&quot;", "&quot;", 
               str_replace("\n\n", "\n", 
               str_replace("&lt;br /&gt;", "\n", 
               str_replace("&lt;br/&gt;", "\n", $this->description)))));
    }
    
    /**
     * Returns the 'end user' of this Milestone
     * @return string The 'end user' of this Milestone
     */
    function getEndUser(){
        return $this->endUser;
    }
    
    /**
     * Returns the Person who modified this Milestone revision
     * @return Person The Person who modified this Milestone revision
     */
    function getEditedBy(){
        return $this->editedBy;
    }
    
    /**
     * Returns the start date of this Milestone
     * @return string The start date of this Milestone
     */
    function getStartDate(){
        return $this->start_date;
    }
    
    /**
     * Returns the end date of this Milestone
     * @return string The end date of this Milestone
     */
    function getEndDate(){
        return $this->end_date;
    }
    
    /**
     * Returns which quarters of for each year this milestone is to take place
     * @return array The quarters for each year
     */
    function getQuarters(){
        $years = array();
        $quarters = explode(",", $this->quarters);
        foreach($quarters as $quarter){
            $exp = explode(":", $quarter);
            if(count($exp) == 2){
                $years[$exp[0]][$exp[1]] = $this->getStatus();
            } 
            else if(count($exp) == 3){
                $years[$exp[0]][$exp[1]] = $exp[2];
            }
        }
        return $years;
    }
    
    /**
     * Returns the start date of the very first revision of this Milestone
     * NOTE: This method may be slow if there are many revisions
     * @return string The start date of the very first revision of this Milestone
     */
    function getVeryStartDate(){
        $parent = $this->getParent();
        if($parent != null){
            return $parent->getVeryStartDate();
        }
        else{
            return $this->getStartDate();
        }
    }
    
    
    function isNew(){
        return ("2016-03-11 00:00:00" <= $this->getVeryStartDate());
    }
    
    /**
     * Returns the projected end date of this Milestone
     * @return string The projected end date of this Milestone
     */
    function getProjectedEndDate(){
        return $this->projected_end_date;
    }
    
    /**
     * Returns the comment of this Milestone
     * @return string The comment of this Milestone
     */
    function getComment(){
        return str_replace("\\'", "&#39;", 
               str_replace("\\&quot;", "&quot;", 
               str_replace("\n\n", "\n", 
               str_replace("&lt;br /&gt;", "\n", 
               str_replace("&lt;br/&gt;", "\n", $this->comment)))));
    }
}
?>
