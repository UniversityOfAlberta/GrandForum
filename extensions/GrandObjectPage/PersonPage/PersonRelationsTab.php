<?php

class PersonRelationsTab extends AbstractTab {

    var $person;
    var $visibility;

    function __construct($person, $visibility){
        parent::__construct("Supervisors");
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
        $me = Person::newFromWgUser();
        if($wgUser->isRegistered() && ($visibility['edit'] || (!$visibility['edit'] && (count($person->getRelations('public')) > 0 || count($person->getSupervisors(true)) > 0 || ($visibility['isMe'] && count($person->getRelations()) > 0))))){
            if($person->isRoleAtLeast(HQP) || ($person->isRole(INACTIVE) && $person->wasLastRoleAtLeast(HQP))){
                if(count($person->getSupervisors(true)) > 0){
                    $universities = $person->getUniversities();
                    if($me->isAllowedToEdit($this->person)){
                        $this->html .= "<a class='button' href='$wgServer$wgScriptPath/index.php/Special:ManagePeople'>Manage HQP</a>";
                    }
                    $this->html .= "<table id='relations_table' class='wikitable' width='100%' cellspacing='1' cellpadding='2' rules='all' frame='box'>
                                    <thead><tr><th>Start Date</th><th>End Date</th><th>Position</th><th>Relation</th><th>Last Name</th><th>First Name</th></tr></thead><tbody>";
                    foreach($person->getSupervisors(true) as $supervisor){
                        // TODO: These loops are a little inneficient, it should probably be extracted to a function, and optimized
                        $relations = array_merge($supervisor->getRelations(SUPERVISES, true), 
                                                 $supervisor->getRelations(CO_SUPERVISES, true));
                        foreach($relations as $r){
                            $hqp = $r->getUser2();
                            if($hqp->getId() == $person->getId()){
                                $start_date = substr($r->getStartDate(), 0, 10);
                                $end_date = substr($r->getEndDate(), 0, 10);
                                $end_date = ($end_date == ZOT) ? "Current" : $end_date;
                                
                                $position = "";
                                foreach($universities as $university){
                                    if($university['id'] == $r->getUniversity()){
                                        $position = $university['position'];
                                        break;
                                    }
                                }

                                $this->html .= 
                                "<tr>
                                    <td>$start_date</td>
                                    <td>$end_date</td>
                                    <td>$position</td>
                                    <td>{$r->getType()}</td>
                                    <td><a href='{$supervisor->getUrl()}'>{$supervisor->getLastName()}</a></td>
                                    <td><a href='{$supervisor->getUrl()}'>{$supervisor->getFirstName()}</a></td>
                                 </tr>";
                            }
                        }
                    }
                    $this->html .= "</tbody></table><script type='text/javascript'>$('#relations_table').dataTable({autoWidth: false});</script>";
                }
            }
        }
        if($wgUser->isRegistered()){
            if($this->html == ""){
                if($visibility['isMe'] && $this->person->isRole(HQP)){
                    $this->html .= "Contact your supervisor in order be added as their student";
                }
                else if($me->isAllowedToEdit($this->person)){
                    $this->html .= "<a class='button' href='$wgServer$wgScriptPath/index.php/Special:ManagePeople'>Manage HQP</a>";
                }
                else{
                    $this->html .= "This user has no relations";
                }
            }
        }
    }
}
?>
