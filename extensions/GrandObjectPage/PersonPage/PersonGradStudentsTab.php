<?php

class PersonGradStudentsTab extends AbstractTab {

    var $person;
    var $visibility;

    function PersonGradStudentsTab($person, $visibility){
        parent::AbstractTab("Students");
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
                    $this->html .= "<table width='100%'><tr>";
                    if(count($person->getRelationsAll()>0)){
                        $this->html .= "<td style='width:100%;' valign='top'>";
                        $this->html .= "<table id='relations_table' class='wikitable sortable' width='100%' cellspacing='1' cellpadding='2' rules='all' frame='box'>
                                    <thead><tr>
					        <th>Name</th>
					        <th>Position</th>
					        <th style='white-space: nowrap;'>Start Date</th>
						<th style='white-space: nowrap;'>End Date</th>
						<th style='white-space: nowrap;'>Research Area</th>
						<th>Completion Milestones</th>
						<th>Supervisors & Committees</th>
						<th>Role</th>
				    </tr></thead><tbody>";
                        $relations = $person->getRelationsAll();
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
			    $research_area = $position['research_area'];
                            $position = $position['position'];
			    $role = $person->getRoleFor($hqp->id);
			    if($role == "Supervises"){
				$role= "Supervisor";
			    }
			    $names = array();
                            $rel = array_merge($hqp->getSupervisors(), $hqp->getCommittee());
			    foreach($rel as $rels){
				$names[] = "<a href='{$rels->getUrl()}'>{$rels->getNameForForms()}</a>";
			    } 
                            $this->html .= 
                            "<tr>
				<td style='white-space: nowrap;'><a href='{$hqp->getUrl()}'>{$hqp->getNameForForms()}</a></td>
                             	<td>$position</td>
			        <td style='white-space: nowrap;'>$start_date</td>
				<td style='white-space: nowrap;'>$end_date</td>
				<td>$research_area</td>
				<td></td><td>".implode(", ",$names)."</td>
				<td style='white-space: nowrap;'>$role</td>";
                            $this->html .= "</tr>";
                        }
                        $this->html .= "</tbody></table><script type='text/javascript'>$('#relations_table').dataTable()</script>";
                    	$this->html .= "</td></tr></table>";
		    }
            }
        }
        if($wgUser->isLoggedIn()){
            if($this->html == ""){
                if($visibility['isMe'] || $visibility['isSupervisor']){
                    $this->html .= "<input type='button' onClick='window.open(\"$wgServer$wgScriptPath/index.php/Special:EditRelations\");' value='Edit Relations' />";
                }
                else{
                    $this->html .= "This user has no relations";
                }
            }
        }
    }
}
?>
