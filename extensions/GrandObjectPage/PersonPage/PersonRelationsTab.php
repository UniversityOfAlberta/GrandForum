<?php

class PersonRelationsTab extends AbstractTab {

    var $person;
    var $visibility;

    function __construct($person, $visibility){
        parent::__construct("Relations");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        $this->showRelations($this->person, $this->visibility);
        return $this->html;
    }
    
    /**
     * Displays all of the user's relations
     */
    function showRelations($person, $visibility){
        global $wgUser, $wgOut, $wgScriptPath, $wgServer;
        if($wgUser->isLoggedIn() && ($visibility['edit'] || (!$visibility['edit'] && (count($person->getRelations('public')) > 0 || count($person->getSupervisors(true)) > 0 || ($visibility['isMe'] && count($person->getRelations()) > 0))))){
            if($person->isRoleAtLeast(HQP) || ($person->isRole(INACTIVE) && $person->wasLastRoleAtLeast(HQP))){
                if(count($person->getSupervisors(true)) > 0){
                    $this->html .= "<h3>Supervisors</h3>";
                    $this->html .= "<table class='wikitable sortable' width='100%' cellspacing='1' cellpadding='5' rules='all' frame='box'>
                                    <tr><th>Start Date</th><th>End Date</th><th>Position</th><th>Last Name</th><th>First Name</th></tr>";
                    foreach($person->getSupervisors(true) as $supervisor){
                        // TODO: These loops are a little inneficient, it should probably be extracted to a function, and optimized
                        foreach($supervisor->getRelations(SUPERVISES, true) as $r){
                            $hqp = $r->getUser2();
                            if($hqp->getId() == $person->getId()){
                                $start_date = substr($r->getStartDate(), 0, 10);
                                $end_date = substr($r->getEndDate(), 0, 10);
                                $end_date = ($end_date == '0000-00-00')? "Current" : $end_date;
                                
                                if($r->getEndDate() != "0000-00-00 00:00:00"){
                                    $position = $hqp->getUniversityDuring($r->getEndDate(), $r->getEndDate());
                                }
                                else{
                                    $position = $hqp->getUniversity();
                                }
                                $position = $position['position'];

                                $this->html .= 
                                "<tr><td>$start_date</td><td>$end_date</td><td>$position</td>
                                <td><a href='{$supervisor->getUrl()}'>{$supervisor->getLastName()}</a></td>
                                <td><a href='{$supervisor->getUrl()}'>{$supervisor->getFirstName()}</a></td></tr>";
                            }
                        }
                    }
                    $this->html .= "</table>";
                }
                if($wgUser->isLoggedIn()){
                    if($this->person->isMe() && ($this->person->isRole(HQP) || $this->person->isRole(HQP.'-Candidate'))){
                        $this->html .= "Contact your supervisor in order be added as their student";
                    }
                    else if($this->person->isMe()){
                        $this->html .= "<a class='button' href='$wgServer$wgScriptPath/index.php/Special:ManagePeople'>Manage People</a>";
                    }
                }
                $this->html .= "<table width='100%'><tr>";
                if(count($person->getRelations(SUPERVISES, true)) > 0){
                    $this->html .= "<td style='padding-right:25px;width:50%;' valign='top'>";
                    $this->html .= "<h3>Supervises</h3>";
                    $this->html .= "<table class='wikitable sortable' width='100%' cellspacing='1' cellpadding='5' rules='all' frame='box'>
                                <tr><th>Start Date</th><th>End Date</th><th>Position</th><th>Last Name</th><th>First Name</th></tr>";
                    $relations = $person->getRelations(SUPERVISES, true);
                    foreach($relations as $r){
                        $hqp =  $r->getUser2();
                        $start_date = substr($r->getStartDate(), 0, 10);
                        $end_date = substr($r->getEndDate(), 0, 10);
                        $end_date = ($end_date == '0000-00-00')? "Current" : $end_date;
                        if($r->getEndDate() != "0000-00-00 00:00:00"){
                            $position = $hqp->getUniversityDuring($r->getEndDate(), $r->getEndDate());
                        }
                        else{
                            $position = $hqp->getUniversity();
                        }
                        $position = @$position['position'];
                        
                        $this->html .= 
                        "<tr><td>$start_date</td><td>$end_date</td><td>$position</td>
                        <td><a href='{$hqp->getUrl()}'>{$hqp->getLastName()}</a></td>
                        <td><a href='{$hqp->getUrl()}'>{$hqp->getFirstName()}</a></td>";
                        $this->html .= "</tr>";
                    }
                    $this->html .= "</table>";
                    $this->html .= "</td>";
                }
                if($visibility['isMe'] && count($person->getRelations(WORKS_WITH, true)) > 0){
                    $this->html .= "<td style='padding-right:25px;width:50%;' valign='top'>";
                    $this->html .= "<h3>Works With</h3>";
                    $this->html .= "<table class='wikitable sortable' width='100%' cellspacing='1' cellpadding='5' rules='all' frame='box'>
                                    <tr><th>Start Date</th><th>End Date</th><th>Last Name</th><th>First Name</th></tr>";
        
                    foreach($person->getRelations(WORKS_WITH, true) as $relation){
                        $start_date = substr($relation->getStartDate(), 0, 10);
                        $end_date = substr($relation->getEndDate(), 0, 10);
                        $end_date = ($end_date == '0000-00-00')? "Current" : $end_date;

                        $this->html .= "<tr><td>$start_date</td><td>$end_date</td>
                                        <td><a href='{$relation->getUser2()->getUrl()}'>{$relation->getUser2()->getLastName()}</a></td>
                                        <td><a href='{$relation->getUser2()->getUrl()}'>{$relation->getUser2()->getFirstName()}</a></td></tr>";
                    }
                    $this->html .= "</table>";
                    $this->html .= "</td>";
                }
                $this->html .= "</tr><tr>";
                if(count($person->getRelations(MENTORS, true)) > 0){
                    $this->html .= "<td style='padding-right:25px;width:50%;' valign='top'>";
                    $this->html .= "<h3>Mentors</h3>";
                    $this->html .= "<table class='wikitable sortable' width='100%' cellspacing='1' cellpadding='5' rules='all' frame='box'>
                                <tr><th>Start Date</th><th>End Date</th><th>Position</th><th>Last Name</th><th>First Name</th></tr>";
                    $relations = $person->getRelations(MENTORS, true);
                    foreach($relations as $r){
                        $hqp =  $r->getUser2();
                        $start_date = substr($r->getStartDate(), 0, 10);
                        $end_date = substr($r->getEndDate(), 0, 10);
                        $end_date = ($end_date == '0000-00-00')? "Current" : $end_date;
                        if($r->getEndDate() != "0000-00-00 00:00:00"){
                            $position = $hqp->getUniversityDuring($r->getEndDate(), $r->getEndDate());
                        }
                        else{
                            $position = $hqp->getUniversity();
                        }
                        $position = $position['position'];
                        
                        $this->html .= 
                        "<tr><td>$start_date</td><td>$end_date</td><td>$position</td>
                        <td><a href='{$hqp->getUrl()}'>{$hqp->getLastName()}</a></td>
                        <td><a href='{$hqp->getUrl()}'>{$hqp->getFirstName()}</a></td>";
                        $this->html .= "</tr>";
                    }
                    $this->html .= "</table>";
                    $this->html .= "</td>";
                }
                $this->html .= "<td style='width:50%;'></td>";
                $this->html .= "</tr></table>";
            }
        }
    }
}
?>
