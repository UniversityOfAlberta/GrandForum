<?php

class PeopleTableTab extends AbstractTab {

    var $table;
    var $visibility;

    function PeopleTableTab($table, $visibility){
         parent::AbstractTab($table);
         $this->table = $table;
         $this->visibility = $visibility;
    }

    function generateBody(){
        global $wgServer, $wgScriptPath, $wgUser, $wgOut, $config, $wgRoleValues;
        $me = Person::newFromId($wgUser->getId());
        $data = Person::getAllPeople($this->table);
        $emailHeader = "";
        $idHeader = "";
        $epicHeader = "";
        $contactHeader = "";
        $subRoleHeader = "";
        $projectsHeader = "";
        $committees = $config->getValue('committees');
        if($me->isLoggedIn()){
            $emailHeader = "<th style='white-space: nowrap;'>Email</th>";
        }
        if($me->isRoleAtLeast(ADMIN)){
            $idHeader = "<th style='white-space: nowrap;'>User Id</th>";
        }
        if($me->isLoggedIn() &&
           ($this->table == TL || $this->table == TC || $wgRoleValues[$this->table] >= $wgRoleValues[SD])){
            $contactHeader = "<th style='white-space: nowrap;'>Email</th><th style='white-space: nowrap;'>Phone</th>";
        }
        if($this->table == HQP){
            $subRoleHeader = "<th style='white-space: nowrap;'>Sub Roles</th>";
            if($config->getValue('networkName') == 'AGE-WELL' && ($me->isRoleAtLeast(STAFF) || $me->isThemeLeader() || $me->isThemeCoordinator())){
                $epicHeader = "<th id='epicHeader' style='white-space: nowrap;'>EPIC Due Date</th>";
            }
        }
        if($config->getValue('projectsEnabled') && !isset($committees[$this->table])){
            $projectsHeader = "<th style='white-space: nowrap;'>Projects</th>";
        }
        $this->html .= "Below are all the current $this->table in {$config->getValue('networkName')}.  To search for someone in particular, use the search box below.  You can search by name, project or institution.<br /><br />";
        $this->html .= "<table class='indexTable' style='display:none;' frame='box' rules='all'>
                            <thead>
                                <tr>
                                    <th style='white-space: nowrap;'>Name</th>
                                    {$subRoleHeader}
                                    {$projectsHeader}
                                    <th style='white-space: nowrap;'>Institution</th>
                                    <th style='white-space: nowrap;'>{$config->getValue('deptsTerm')}</th>
                                    <th style='white-space: nowrap;'>Title</th>
                                    {$epicHeader}
                                    {$contactHeader}
                                    {$emailHeader}
                                    {$idHeader}
                                </tr>
                            </thead>
                            <tbody>
";
        foreach($data as $person){
                $this->html .= "
<tr>
<td align='left' style='white-space: nowrap;'>
<a href='{$person->getUrl()}'>{$person->getReversedName()}</a>
</td>
";
                if($subRoleHeader != ""){
                    $subRoles = array();
                    foreach(@$person->getSubRoles() as $sub){
                        $subRoles[] = $config->getValue('subRoles', $sub);
                    }
                    $this->html .= "<td style='white-space:nowrap;' align='left'>".implode("<br />", $subRoles)."</td>";
                }

                if($config->getValue('projectsEnabled') && !isset($committees[$this->table])){
                    $projects = array_merge($person->leadership(), $person->getProjects());
                    $projs = array();
                    foreach($projects as $project){
                        if(!$project->isSubProject() && !isset($projs[$project->getId()]) &&
                            $project->getPhase() == PROJECT_PHASE &&
                            $project->getStatus() != "Proposed" &&
                            $person->isRole($this->table, $project)){
                            $subprojs = array();
                            foreach($project->getSubProjects() as $subproject){
                                if($person->isMemberOf($subproject)){
                                    $subprojs[] = "<a href='{$subproject->getUrl()}'>{$subproject->getName()}</a>";
                                }
                            }
                            $subprojects = "";
                            if(count($subprojs) > 0){
                                $subprojects = "(".implode(", ", $subprojs).")";
                            }
                            $projs[$project->getId()] = "<a href='{$project->getUrl()}'>{$project->getName()}</a> $subprojects";
                        }
                    }
                    $this->html .= "<td align='left'style='white-space: nowrap;'>".implode("<br />", $projs)."</td>";
                }
                $university = $person->getUniversity();
                $this->html .= "<td align='left'>{$university['university']}</td>";
                $this->html .= "<td align='left'>{$university['department']}</td>";
                $this->html .= "<td align='left'>{$university['position']}</td>";
                if($epicHeader != ''){
                    $hqpTab = new HQPEpicTab($person, array());
                    $date = $hqpTab->getBlobValue('HQP_EPIC_REP_DATE');
                    $this->html .= "<td align='left'>{$date}</td>";
                }
                if($contactHeader != ''){
                    $this->html .= "<td align='left'><a href='mailto:{$person->getEmail()}'>{$person->getEmail()}</a></td>";
                    $this->html .= "<td align='left'>{$person->getPhoneNumber()}</td>";
                }
                if($emailHeader != ''){
                    $this->html .= "<td>{$person->getEmail()}</td>";
                }
                if($idHeader != ''){
                    $this->html .= "<td>{$person->getId()}</td>";
                }
                $this->html .= "</tr>";
        }
        $this->html .= "</tbody></table><script type='text/javascript'>$('.indexTable').dataTable({
            'aLengthMenu': [[100,-1], [100,'All']], 
            'iDisplayLength': 100, 
            'autoWidth':false,
            'columnDefs': [
                {'type': 'date', 'targets': $('.indexTable th').index($('#epicHeader'))}
            ]
        });</script>";
        return $this->html;
    }
    
}

?>
