<?php

class NITableTab extends PeopleTableTab {

    function __construct($visibility, $past=false){
        parent::__construct(NI, $visibility, $past);
    }

    function generateBody(){
        global $wgServer, $wgScriptPath, $wgUser, $wgOut, $config, $wgRoleValues;
        $me = Person::newFromId($wgUser->getId());
        
        $start = "0000-00-00";
        $end = date('Y-m-d');
        
        $data = array_merge(Person::getAllPeople(NI),
                            Person::getAllPeople(PL));
        $people = array();
        foreach($data as $person){
            $people[$person->getId()] = $person;
        }
        $emailHeader = "";
        $idHeader = "";
        $epicHeader = "";
        $subRoleHeader = "";
        $projectsHeader = "";
        $committees = $config->getValue('committees');
        if($me->isRoleAtLeast(ADMIN)){
            $idHeader = "<th style='white-space: nowrap;'>User Id</th>";
        }
        if($me->isLoggedIn()){
            $emailHeader = "<th style='white-space: nowrap;'>Email</th><th>".AR."</th><th>".CI."</th><th>".PL."</th>";
        }
        if($config->getValue('projectsEnabled') && !isset($committees[$this->table])){
            $projectsHeader = "<th style='white-space: nowrap;'>Projects</th>";
        }
        $statusHeader = "";
        if($me->isRoleAtLeast(STAFF)){
            $statusHeader .= "<th>Gender</th>";
            if($config->getValue('crcEnabled')){
                $statusHeader .= "<th>CRC</th>";
            }
            if($config->getValue('ecrEnabled')){
                $statusHeader .= "<th>ECR</th>";
            }
            if($config->getValue('agenciesEnabled')){
                $statusHeader .= "<th>Agencies</th>";
            }
            if($config->getValue('mitacsEnabled')){
                $statusHeader .= "<th>MITACS</th>";
            }
            if($me->isRoleAtLeast(MANAGER)){
                $statusHeader .= "<th style='display:none;'>Indigenous</th>
                                  <th style='display:none;'>Disability</th>
                                  <th style='display:none;'>Minority</th>";
            }
            $statusHeader .= "<th>Nationality</th>
                              <th>Status</th>";
        }
        $role = "{$this->table} members";
        if($this->table == "Member"){
            $role = "Members";
        }
        $this->html .= "Below are all of the ".strtolower($this->id)." {$role} in {$config->getValue('networkName')}.  To search for someone in particular, use the search box below.  You can search by name, project or institution.<br /><br />";
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
                                    {$emailHeader}
                                    {$idHeader}
                                </tr>
                            </thead>
                            <tbody>
";
        $count = 0;
        foreach($people as $person){
            if($this->past === true && $person->isRole($this->table) ){
                // Person is still the specified role, don't show on the 'former' table
                continue;
            }
            if($this->table == PL){
                $skip = true;
                foreach($person->leadershipDuring($start, $end) as $project){
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
                $history = ($config->getValue('networkName') == "GlycoNet");
                $projects = array_merge($person->leadershipDuring($start, $end), $person->getProjectsDuring($start, $end));
                $projs = array();
                foreach($projects as $project){
                    if(!$project->isSubProject() && !isset($projs[$project->getId()]) &&
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
            $this->html .= @"<td align='left'>{$university['university']}</td>";
            $this->html .= @"<td align='left'>{$university['department']}</td>";
            $this->html .= @"<td align='left'>{$university['position']}</td>";
            if($statusHeader != ''){
                if($person->isRole($this->table)){
                    $status = "Active";
                }
                else{
                    $status = "Inactive";                
                }
                $this->html .= "<td align='left'>{$person->getGender()}</td>";
                if($config->getValue('crcEnabled')){
                    $crcObj = $person->getCanadaResearchChair();
                    if($crcObj != null){
                        $crcObj = array_filter($crcObj);
                    }
                    $this->html .= "<td>".@implode("<br />", $crcObj)."</td>";
                }
                if($config->getValue('ecrEnabled')){
                    $this->html .= "<td align='left'>{$person->getEarlyCareerResearcher()}</td>";
                }
                if($config->getValue('agenciesEnabled')){
                    $this->html .= "<td align='left'>{$person->getAgencies(', ')}</td>";
                }
                if($config->getValue('mitacsEnabled')){
                    $this->html .= "<td align='left'>{$person->getMitacs()}</td>";
                }
                if($me->isRoleAtLeast(MANAGER)){
                    $this->html .= "<td align='left' style='display:none;'>{$person->getIndigenousStatus()}</td>";
                    $this->html .= "<td align='left' style='display:none;'>{$person->getDisabilityStatus()}</td>";
                    $this->html .= "<td align='left' style='display:none;'>{$person->getMinorityStatus()}</td>";
                }
                $this->html .= "<td align='left'>{$person->getNationality()}</td>";
                $this->html .= "<td align='left'>{$status}</td>";
            }
            if($emailHeader != ''){
                if($person->getEmail() == ""){
                    $this->html .= "<td></td>";
                }
                else {
                    $this->html .= "<td><a href='mailto:{$person->getEmail()}'>{$person->getEmail()}</a></td>";
                }
                $isAR = ($person->isRole(AR)) ? "&#10003;" : "";
                $isCI = ($person->isRole(CI)) ? "&#10003;" : "";
                $isPL = ($person->isRole(PL)) ? "&#10003;" : "";
                
                $this->html .= "<td align='center' style='font-size:2em;'>{$isAR}</td>
                                <td align='center' style='font-size:2em;'>{$isCI}</td>
                                <td align='center' style='font-size:2em;'>{$isPL}</td>";
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
        });
        $('.custom-title').hide();
        </script>";
        if($count == 0){
            $this->html = "";
        }
        return $this->html;
    }
    
}

?>
