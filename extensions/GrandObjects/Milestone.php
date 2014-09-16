<?php

/**
 * @package GrandObjects
 */

class Milestone {

    static $cache = array();

    var $id;
    var $identifier;
    var $milestone_id;
    var $parent;
    var $project;
    var $people;
    var $peopleWaiting;
    var $title;
    var $status;
    var $description;
    var $assessment;
    var $parentWaiting;
    var $editedBy;
    var $start_date;
    var $end_date;
    var $comment;
    
    // Creates a Milestone from the given milestone_id and id
    function newFromId($milestone_id, $id=2147483647){
        if(isset(self::$cache[$milestone_id."id".$id])){
            return self::$cache[$milestone_id."id".$id];
        }
        $sql = "SELECT *
                FROM grand_milestones
                WHERE milestone_id = '$milestone_id'
                AND id <= $id
                ORDER BY id DESC";
        $data = DBFunctions::execSQL($sql);
        $milestone = new Milestone($data);
        self::$cache[$milestone_id."id".$id] = &$milestone;
        self::$cache[$milestone_id."id".$milestone->getId()] = &$milestone;
        return $milestone;
    }
    
    // Creates a Milestone from the given milestone_id and id
    function newFromIndex($id=2147483647){
        //if(isset(self::$cache[$milestone_id."id".$id])){
        //    return self::$cache[$milestone_id."id".$id];
        //}
        $sql = "SELECT *
                FROM grand_milestones
                WHERE id = '$id'";
        $data = DBFunctions::execSQL($sql);
        $milestone = new Milestone($data);
        //self::$cache[$milestone_id."id".$id] = &$milestone;
        //self::$cache[$milestone_id."id".$milestone->getId()] = &$milestone;
        return $milestone;
    }

    function newFromTitle($milestone_title, $id=2147483647){
        $milestone_title = str_replace("'", "#39;", $milestone_title);
        if(isset(self::$cache[$milestone_title."id".$id])){
            return self::$cache[$milestone_title."id".$id];
        }
        $sql = "SELECT *
                FROM grand_milestones
                WHERE title = '$milestone_title'
                AND id <= $id
                ORDER BY id DESC";
        $data = DBFunctions::execSQL($sql);
        $milestone = new Milestone($data);
        self::$cache[$milestone_title."id".$id] = &$milestone;
        self::$cache[$milestone_title."id".$milestone->getId()] = &$milestone;
        return $milestone;
    }

    function Milestone($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->identifier = $data['0']['identifier'];
            $this->milestone_id = $data[0]['milestone_id'];
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
            $this->project = Project::newFromId($data[0]['project_id']);
            $this->people = array();
            $this->peopleWaiting = true;
            $this->description = $data[0]['description'];
            $this->assessment = $data[0]['assessment'];
            $this->start_date = $data[0]['start_date'];
            $this->end_date = $data[0]['end_date'];
            $this->projected_end_date = $data[0]['projected_end_date'];
            $this->comment = $data[0]['comment'];
            $this->editedBy = Person::newFromId($data[0]['edited_by']);
        }
    }
    
    // Returns the revision number of this Milestone
    function getId(){
        return $this->id;
    }
    
    // Returns the identifier of this Milestone(used for when it is first created)
    function getIdentifier(){
        return $this->identifier;
    }
    
    // Returns the id of this Milestone
    function getMilestoneId(){
        return $this->milestone_id;
    }
    
    // Returns the parent of this Milestone
    // (Lazy to help avoid potential infinite loops, and to improve performance on Object construction)
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
    
    // Returns the the milestone revision closest to the given date
    function getRevisionByDate($date){
        $parent = $this->getParent();
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
    
    // Returns the title of this Milestone
    function getTitle(){
        return $this->title;
    }
    
    // Returns the status of this Milestone
    function getStatus(){
        return $this->status;
    }
    
    // Returns the Project of this Milestone
    function getProject(){
        return $this->project;    
    }
    
    // Returns the People involved in this Milestone revision
    function getPeople(){
        if($this->peopleWaiting){
            $sql = "SELECT * 
                    FROM `grand_milestones_people` p, `mw_user` u
                    WHERE milestone_id = '{$this->id}'
                    AND u.user_id = p.user_id
                    AND u.deleted != '1'";
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                $this->people[] = Person::newFromId($row['user_id']);
            }
            $this->peopleWaiting = false;
        }
        return $this->people;
    }
    
    // Returns the description of this Milestone
    function getDescription(){
        return str_replace("\\'", "&#39;", 
               str_replace("\\&quot;", "&quot;", 
               str_replace("\n\n", "\n", 
               str_replace("&lt;br /&gt;", "\n", 
               str_replace("&lt;br/&gt;", "\n", $this->description)))));
    }
    
    // Returns the assessment of this Milestone
    function getAssessment(){
        return str_replace("\\'", "&#39;", 
               str_replace("\\&quot;", "&quot;", 
               str_replace("\n\n", "\n", 
               str_replace("&lt;br /&gt;", "\n", 
               str_replace("&lt;br/&gt;", "\n", $this->assessment)))));
    }
    
    // Returns the Person who modified this Milestone revision
    function getEditedBy(){
        return $this->editedBy;
    }
    
    // Returns the start_date of this Milestone
    function getStartDate(){
        return $this->start_date;
    }
    
    // Returns the end_date of this Milestone
    function getEndDate(){
        return $this->end_date;
    }
    
    // Returns the start_date of the very first revision of this Milestone
    // NOTE: This method may be slow if there are many revisions
    function getVeryStartDate(){
        $parent = $this->getParent();
        if($parent != null){
            return $parent->getVeryStartDate();
        }
        else{
            return $this->getStartDate();
        }
    }
    
    // Returns the projected end date of this Milestone
    function getProjectedEndDate(){
        return $this->projected_end_date;
    }
    
    // Returns the comment of this Milestone
    function getComment(){
        return str_replace("\\'", "&#39;", 
               str_replace("\\&quot;", "&quot;", 
               str_replace("\n\n", "\n", 
               str_replace("&lt;br /&gt;", "\n", 
               str_replace("&lt;br/&gt;", "\n", $this->comment)))));
    }
    
    function getHistoryPopup($backUntilDate='0000-00-00'){
        global $wgServer, $wgScriptPath;
        $url = "{$wgServer}{$wgScriptPath}/index.php?action=api.getProjectMilestoneHistory/{$this->getMilestoneId()}/{$this->getId()}/{$backUntilDate}";
        $html = "
            <script type='text/javascript'>
                function openDialog{$this->getId()}(){
                    $('#milestone_history_{$this->getId()}').dialog('open');
                    $.get('$url', function(response){
                        $('#milestone_history_{$this->getId()}').html(response);
                    });
                }
            </script>
            <a style='font-style:italic; font-size:11px; font-weight:bold;' onclick='openDialog{$this->getId()}(); return false;' href='#'>See Milestone History</a>
            <div title='{$this->getTitle()}' style='white-space: pre-line;' id='milestone_history_{$this->getId()}'>Loading...</div>
            <script type='text/javascript'>
                $(document).ready(function(){
                    $('#milestone_history_{$this->getId()}').dialog({ autoOpen: false, height: '600', width: '800' });
                });
           </script>";
        return $html;
    }
}
?>
