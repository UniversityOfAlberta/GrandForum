<?php

class PersonRelationsTab extends AbstractTab {

    var $person;
    var $visibility;

    function PersonRelationsTab($person, $visibility){
        parent::AbstractTab("Relations");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        $this->showRelations($this->person, $this->visibility);
        return $this->html;
    }
    
    /*
     * Displays all of the user's relations
     */
    function showRelations($person, $visibility){
        global $wgUser, $wgOut, $wgScriptPath, $wgServer;
        if($wgUser->isLoggedIn() && ($visibility['edit'] || (!$visibility['edit'] && (count($person->getRelations('public')) > 0 || count($person->getSupervisors(true)) > 0 || ($visibility['isMe'] && count($person->getRelations()) > 0))))){
            if($person->isRoleAtLeast(HQP) || ($person->isRole(INACTIVE) && $person->wasLastRoleAtLeast(HQP))){
                if($person->isHQP() || ($person->isRole(INACTIVE) && $person->wasLastRole(HQP))){
                    if($visibility['edit'] && $visibility['isSupervisor']){
                        $this->html .= "<input type='button' onClick='window.open(\"$wgServer$wgScriptPath/index.php/Special:EditRelations\");' value='Edit Relations' />";
                    }
                    $this->html .= "<h3>Supervisors</h3>";
                    $this->html .= "<table class='wikitable sortable' width='60%' cellspacing='1' cellpadding='5' rules='all' frame='box'>
                                    <tr><th>Start Date</th><th>End Date</th><th>Position</th><th>Projects</th><th>Last Name</th><th>First Name</th></tr>";
                    foreach($person->getSupervisors(true) as $supervisor){
                        // TODO: These loops are a little inneficient, it should probably be extracted to a function, and optimized
                        foreach($supervisor->getRelations("Supervises", true) as $r){
                            $hqp = $r->getUser2();
                            if($hqp->getId() == $person->getId()){
                                $start_date = substr($r->getStartDate(), 0, 10);
                                $end_date = substr($r->getEndDate(), 0, 10);
                                $end_date = ($end_date == '0000-00-00')? "Current" : $end_date;
                                $position = $hqp->getUniversity();
                                $position = $position['position'];
                                $projects = $r->getProjects();
                                $proj_names = array();
                                foreach($projects as $p){
                                    $proj_names[] = $p->getName();
                                }
                                $proj_names = implode(', ', $proj_names);
                                $this->html .= 
                                "<tr><td>$start_date</td><td>$end_date</td><td>$position</td><td>$proj_names</td>
                                <td><a href='{$supervisor->getUrl()}'>{$supervisor->getLastName()}</a></td>
                                <td><a href='{$supervisor->getUrl()}'>{$supervisor->getFirstName()}</a></td></tr>";
                                break;
                            }
                        }
                    }
                    
                    $this->html .= "</table>";
                }
                else{
                    $this->html .= "<table><tr>";
                    if(count($person->getHQP()) > 0){
                        $ethicsHeader = "";
                        if(isExtensionEnabled('EthicsTable')){
                            $ethicsHeader = "<th>Ethical</th>";
                        }
                        $this->html .= "<td style='padding-right:25px;' valign='top'>";
                        $this->html .= "<h3>Supervises</h3>";
                        $this->html .= "<table class='wikitable sortable' width='60%' cellspacing='1' cellpadding='5' rules='all' frame='box'>
                                    <tr><th>Start Date</th><th>End Date</th><th>Position</th><th>Projects</th><th>Last Name</th><th>First Name</th>$ethicsHeader</tr>";
                        $relations = $person->getRelations("Supervises", true);
                        foreach($relations as $r){
                            $hqp =  $r->getUser2();
                            $start_date = substr($r->getStartDate(), 0, 10);
                            $end_date = substr($r->getEndDate(), 0, 10);
                            $end_date = ($end_date == '0000-00-00')? "Current" : $end_date;
                            $position = $hqp->getUniversity();
                            $position = $position['position'];
                            $projects = $r->getProjects();
                            $proj_names = array();
                            foreach($projects as $p){
                                $proj_names[] = $p->getName();
                            }
                            $proj_names = implode(', ', $proj_names);
                            
                            $this->html .= 
                            "<tr><td>$start_date</td><td>$end_date</td><td>$position</td><td>$proj_names</td>
                            <td><a href='{$hqp->getUrl()}'>{$hqp->getLastName()}</a></td>
                            <td><a href='{$hqp->getUrl()}'>{$hqp->getFirstName()}</a></td>";
                            if(isExtensionEnabled('EthicsTable')){
                                $ethics = $hqp->getEthics();
                                if($end_date == 'Current'){
                                    $ethics = ($ethics['completed_tutorial'])? "<img style='vertical-align:bottom;' width='40px' src='$wgServer$wgScriptPath/skins/cavendish/ethical_btns/ethical_button.jpg' />" : "<img style='vertical-align:bottom;' width='40px' src='$wgServer$wgScriptPath/skins/cavendish/ethical_btns/ethical_button_not.jpg' />";
                                }
                                else{
                                    $ethics = "N/A";
                                }
                                $this->html .= "<td align='center'>{$ethics}</td>";
                            }
                            $this->html .= "</tr>";
                        }
                        $this->html .= "</table>";
                        $this->html .= "</td>";
                    }
                    if($visibility['isMe'] && count($person->getRelations('Works With', true)) > 0){
                        $this->html .= "<td style='padding-right:25px;' valign='top'>";
                        $this->html .= "<h3>Works With</h3>";
                        $this->html .= "<table class='wikitable sortable' width='40%' cellspacing='1' cellpadding='5' rules='all' frame='box'>
                                        <tr><th>Start Date</th><th>End Date</th><th>Projects</th><th>Last Name</th><th>First Name</th></tr>";
            
                        foreach($person->getRelations('Works With', true) as $relation){
                            $start_date = substr($relation->getStartDate(), 0, 10);
                            $end_date = substr($relation->getEndDate(), 0, 10);
                            $end_date = ($end_date == '0000-00-00')? "Current" : $end_date;
                            $projects = $relation->getProjects();
                            $proj_names = array();
                            foreach($projects as $p){
                                $proj_names[] = $p->getName();
                            }
                            $proj_names = implode(', ', $proj_names);
                            $this->html .= "<tr><td>$start_date</td><td>$end_date</td><td>$proj_names</td>
                                            <td><a href='{$relation->getUser2()->getUrl()}'>{$relation->getUser2()->getLastName()}</a></td>
                                            <td><a href='{$relation->getUser2()->getUrl()}'>{$relation->getUser2()->getFirstName()}</a></td></tr>";
                        }
                        $this->html .= "</table>";
                        $this->html .= "</td>";
                    }
                    $this->html .= "</tr></table>";
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
