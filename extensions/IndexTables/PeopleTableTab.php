<?php

class PeopleTableTab extends AbstractTab {

    var $table;
    var $visibility;
    var $past;

    function PeopleTableTab($table, $visibility, $past=false){
        if(!$past){
            parent::AbstractTab("Current");
        } 
        else if(is_numeric($past)){
            parent::AbstractTab("$past-".($past+1));
        }
        else {
            parent::AbstractTab("Former");
        }
        $this->table = $table;
        $this->visibility = $visibility;
        $this->past = $past;
    }

    function generateBody(){
        global $wgServer, $wgScriptPath, $wgUser, $wgOut, $config, $wgRoleValues;
        $me = Person::newFromId($wgUser->getId());
        if(!$this->past){
            $data = Person::getAllPeople($this->table);
        }
        else if(is_numeric($this->past)){
            $data = Person::getAllPeopleDuring($this->table, $this->past."-04-01", ($this->past+1)."-03-31");
        }
        else{
            $data = Person::getAllPeopleDuring($this->table, "0000-00-00", date('Y-m-d'));
        }
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
        $statusHeader = "";
        if($me->isRoleAtLeast(STAFF)){
            $statusHeader .= "<th>Gender</th>
                              <th>Nationality</th>
                              <th>Status</th>";
        }
        $this->html .= "Below are all of the ".strtolower($this->id)." {$this->table} members in {$config->getValue('networkName')}.  To search for someone in particular, use the search box below.  You can search by name, project or institution.<br /><br />";
        $this->html .= "<table class='indexTable {$this->id}' style='display:none;' frame='box' rules='all'>
                            <thead>
                                <tr>
                                    <th style='white-space: nowrap;'>Name</th>
                                    <th style='display:none;'>First Name</th>
                                    <th style='display:none;'>Last Name</th>
                                    {$subRoleHeader}
                                    {$projectsHeader}
                                    <th style='white-space: nowrap;'>Institution</th>
                                    <th style='white-space: nowrap;'>{$config->getValue('deptsTerm')}</th>
                                    <th style='white-space: nowrap;'>Title</th>
                                    {$statusHeader}
                                    {$epicHeader}
                                    {$contactHeader}
                                    {$emailHeader}
                                    {$idHeader}
                                </tr>
                            </thead>
                            <tbody>
";
        $count = 0;
        foreach($data as $person){
            if($this->past === true && $person->isRole($this->table) ){
                // Person is still the specified role, don't show on the 'former' table
                continue;
            }
            if($this->table == PL){
                $skip = true;
                foreach($person->leadership() as $project){
                    if($project->getStatus() != "Proposed"){
                        // Don't skip this person, they belong to atleast one project which is not proposed
                        $skip = false;
                        break;
                    }
                }
                if($skip){
                    // Skip the person if they are only a PL of a proposed project
                    continue;                
                }
            }
            $count++;
            $this->html .= "
                <tr>
                    <td align='left' style='white-space: nowrap;'>
                        <a href='{$person->getUrl()}'>{$person->getReversedName()}</a>
                    </td>
                    <td align='left' style='white-space: nowrap;display:none;'>
                        {$person->getFirstName()}
                    </td>
                    <td align='left' style='white-space: nowrap;display:none;'>
                        {$person->getLastName()}
                    </td>";       
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
                $this->html .= "<td align='left' style='white-space: nowrap;'>".implode("<br />", $projs)."</td>";
            }
            $university = $person->getUniversity();
            $this->html .= "<td align='left'>{$university['university']}</td>";
            $this->html .= "<td align='left'>{$university['department']}</td>";
            $this->html .= "<td align='left'>{$university['position']}</td>";
            if($statusHeader != ''){
                if($person->isRole($this->table)){
                    $status = "Active";
                }
                else{
                    $status = "Inactive";                
                }
                $this->html .= "<td align='left'>{$person->getGender()}</td>";
                $this->html .= "<td align='left'>{$person->getNationality()}</td>";
                $this->html .= "<td align='left'>{$status}</td>";
            }
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
        $this->html .= "</tbody></table><script type='text/javascript'>
        $('.indexTable.{$this->id}').dataTable({
            'aLengthMenu': [[100,-1], [100,'All']], 
            'iDisplayLength': 100, 
            'autoWidth':false,
            'columnDefs': [
                {'type': 'date', 'targets': $('.indexTable.{$this->id} th').index($('#epicHeader'))}
            ],
            'dom': 'Blfrtip',
            'buttons': [
                'excel', 'pdf'
            ]
        });</script>";
        if($count == 0){
            $this->html = "";
        }
        return $this->html;
    }
    
}

?>
