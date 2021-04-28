<?php

class PeopleTableTab extends AbstractTab {

    var $table;
    var $visibility;
    var $past;

    function PeopleTableTab($table, $visibility, $past=false){
        global $config, $wgOut;
        if($table != "Candidate"){
            $tabTitle = Inflect::pluralize($config->getValue('roleDefs', $table));
        }
        else{
            $tabTitle = Inflect::pluralize($table);
        }
        $tabTitle = ucwords($tabTitle);
        $wgOut->setPageTitle($tabTitle);
        if(!$past){
            parent::AbstractTab($tabTitle);
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
        if(isset($_GET['getHTML']) && @$_GET['tab'] == $this->id){
            echo $this->getHTML();
            exit;
        }
    }
    
    function tabSelect(){
        global $wgServer, $wgScriptPath, $config;
        $tabTitle = ($this->table == "Candidate") ? "Candidates" : $this->table;
        return "_.defer(function(){
            if($('.indexTable.{$this->id}').length == 0){
                $.get('{$wgServer}{$wgScriptPath}/index.php/{$config->getValue('networkName')}:ALL_{$tabTitle}?getHTML&tab={$this->id}', function(response){
                    $('#{$this->id}').html(response);
                });
            }
        });";
    }

    function generateBody(){
        $this->html = "<span class='throbber'></span>";
    }
    
    function getHTML(){
        global $wgServer, $wgScriptPath, $wgUser, $wgOut, $config, $wgRoleValues;
        $html = "";
        $me = Person::newFromId($wgUser->getId());
        $start = "";
        $end = "";
        if($this->table == "Candidate"){
            $data = Person::getAllCandidates();
            foreach($data as $key => $row){
                if(!$row->isCandidate()){
                    unset($data[$key]);
                }
            }
            $start = "0000-00-00";
            $end = date('Y-m-d');
        }
        else if(!$this->past){
            $data = Person::getAllPeople($this->table);
            $start = "0000-00-00";
            $end = date('Y-m-d');
        }
        else if(is_numeric($this->past)){
            $data = Person::getAllPeopleDuring($this->table, $this->past."-04-01", ($this->past+1)."-03-31");
            $start = $this->past."-04-01";
            $end = ($this->past+1)."-03-31";
        }
        else{
            $data = Person::getAllPeopleDuring($this->table, "0000-00-00", date('Y-m-d'));
            $start = "0000-00-00";
            $end = date('Y-m-d');
        }
        $emailHeader = "";
        $idHeader = "";
        $epicHeader = "";
        $hqpHeader = "";
        $contactHeader = "";
        $subRoleHeader = "";
        $projectsHeader = "";
        $uniHeader = "";
        $committees = $config->getValue('committees');
        if($me->isRoleAtLeast(ADMIN)){
            $idHeader = "<th style='white-space: nowrap;'>User Id</th>";
        }
        if($me->isLoggedIn() && $this->table != "Candidate" &&
           ($this->table == TL || $this->table == TC || $wgRoleValues[$this->table] >= $wgRoleValues[SD])){
            $contactHeader = "<th style='white-space: nowrap;'>Email</th><th style='white-space: nowrap;'>Phone</th>";
        }
        else if($me->isLoggedIn()){
            $emailHeader = "<th style='white-space: nowrap;'>Email</th>";
        }
        if($this->table == HQP){
            $subRoleHeader = "<th style='white-space: nowrap;'>".Inflect::pluralize($config->getValue('subRoleTerm'))."</th>";
            if($config->getValue('networkName') == 'AGE-WELL' && ($me->isRoleAtLeast(STAFF) || $me->isThemeLeader() || $me->isThemeCoordinator())){
                $epicHeader = "<th id='epicHeader' style='white-space: nowrap;'>EPIC Due Date</th>
                               <th style='white-space: nowrap;'>Appendix A</th>
                               <th style='white-space: nowrap;'>COI</th>
                               <th style='white-space: nowrap;'>NDA</th>";
            }
            $hqpHeader = "<th>Supervisors</th>";
        }
        if($config->getValue('projectsEnabled') && !isset($committees[$this->table])){
            $projectsHeader = "<th style='white-space: nowrap;'>Projects</th>";
        }
        $statusHeader = "";
        if($me->isRoleAtLeast(STAFF)){
            if($config->getValue("genderEnabled")){
                $statusHeader .= "<th>Gender</th>";
            }
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
            if($config->getValue("nationalityEnabled")){
                $statusHeader .= "<th>Nationality</th>";
            }
            $statusHeader .= "<th>Status</th>";
        }
        $role = "{$this->table} members";
        if($this->table == "Member"){
            $role = "Members";
        }
        if(!isExtensionEnabled("Shibboleth")){
            $uniHeader = "<th style='white-space: nowrap; width:20%;'>Institution</th>";
        }
        $facultyHead = (count(Person::$facultyMap) > 0) ? " / Faculty" : "";
        $html .= "<table class='indexTable {$this->id}' frame='box' rules='all'>
                            <thead>
                                <tr>
                                    <th style='white-space: nowrap; width:20%;'>Name</th>
                                    <th style='display:none;'>First Name</th>
                                    <th style='display:none;'>Last Name</th>
                                    {$subRoleHeader}
                                    {$projectsHeader}
                                    {$uniHeader}
                                    <th style='white-space: nowrap; width:20%;'>{$config->getValue('deptsTerm')}{$facultyHead}</th>
                                    <th style='white-space: nowrap; width:20%;'>Title / Rank</th>
                                    <th style='display:none;'>First University</th>
                                    <th style='display:none;'>First {$config->getValue('deptsTerm')}{$facultyHead}</th>
                                    <th style='display:none;'>First Title / Rank</th>
                                    {$hqpHeader}
                                    <th style='white-space: nowrap; width:40%;'>Keywords / Bio</th>
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
            $html .= "
                <tr>
                    <td align='center' style='white-space: nowrap;'>
                        <a href='{$person->getUrl()}'><img src='{$person->getPhoto(true)}' style='max-width:100px;max-height:132px; border-radius: 5px;' /></a><br />
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
                $html .= "<td style='white-space:nowrap;' align='left'>".implode("<br />", $subRoles)."</td>";
            }
            if($config->getValue('projectsEnabled') && !isset($committees[$this->table])){
                $projects = array_merge($person->leadershipDuring($start, $end), $person->getProjectsDuring($start, $end));
                $projs = array();
                foreach($projects as $project){
                    if(!$project->isSubProject() && !isset($projs[$project->getId()]) &&
                        $project->getStatus() != "Proposed" &&
                        ($person->isRole($this->table, $project) || ($this->past !== false && $person->isRoleDuring($this->table, $start, $end, $project)))){
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
                $html .= "<td align='left' style='white-space: nowrap;'>".implode("<br />", $projs)."</td>";
            }
            // Current University
            $university = $person->getUniversity();
            if($uniHeader != ''){
                $html .= "<td align='left'>{$university['university']}</td>";
            }
            if($person->getFaculty() != ""){
                $html .= "<td align='left'>{$person->getDepartment()} / {$person->getFaculty()}</td>";
            }
            else{
                $html .= "<td align='left'>{$person->getDepartment()}</td>";
            }
            $html .= "<td align='left'>{$university['position']}</td>";
            // First University
            $firstuniversity = $person->getFirstUniversity();
            $html .= "<td style='display:none;' align='left'>{$firstuniversity['university']}</td>";
            $html .= "<td style='display:none;' align='left'>{$firstuniversity['department']}</td>";
            $html .= "<td style='display:none;' align='left'>{$firstuniversity['position']}</td>";
            if($hqpHeader != ''){
                $supervisors = array();
                foreach($person->getSupervisorsDuring($start, $end) as $supervisor){
                    $supervisors[$supervisor->id] = "<a href='{$supervisor->getUrl()}'>{$supervisor->getNameForForms()}</a>";
                }
                $html .= "<td>".implode("; ", $supervisors)."</td>";
            }
            $keywords = $person->getKeywords(', ');
            $bio = strip_tags(trim($person->getProfile()));
            if($bio != ""){
                $bio = "<div style='display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden;'>{$bio}</div>";
            }
            if($keywords != "" && $bio != ""){
                $keywords .= "<br /><br />";
            }
            $html .= "<td align='left'>{$keywords}{$bio}</td>";
            if($statusHeader != ''){
                if($person->isRole($this->table)){
                    $status = "Active";
                }
                else{
                    $lastRole = $person->getRole(HQP, true);
                    $status = "Inactive (".substr($lastRole->getEndDate(), 0, 10).")";
                }
                if($config->getValue("genderEnabled")){
                    $html .= "<td align='left'>{$person->getGender()}</td>";
                }
                if($config->getValue('crcEnabled')){
                    $crcObj = $person->getCanadaResearchChair();
                    $html .= "<td align='left'>".@implode("<br />\n", $crcObj)."</td>";
                }
                if($config->getValue('ecrEnabled')){
                    $html .= "<td align='left'>{$person->getEarlyCareerResearcher()}</td>";
                }
                if($config->getValue('agenciesEnabled')){
                    $html .= "<td align='left'>{$person->getAgencies(', ')}</td>";
                }
                if($config->getValue('mitacsEnabled')){
                    $html .= "<td align='left'>{$person->getMitacs()}</td>";
                }
                if($me->isRoleAtLeast(MANAGER)){
                    $html .= "<td align='left' style='display:none;'>{$person->getIndigenousStatus()}</td>";
                    $html .= "<td align='left' style='display:none;'>{$person->getDisabilityStatus()}</td>";
                    $html .= "<td align='left' style='display:none;'>{$person->getMinorityStatus()}</td>";
                }
                if($config->getValue("nationalityEnabled")){
                    $html .= "<td align='left'>{$person->getNationality()}</td>";
                }
                $html .= "<td align='left'>{$status}</td>";
            }
            if($epicHeader != ''){
                $hqpTab = new HQPEpicTab($person, array());
                $date = $hqpTab->getBlobValue('HQP_EPIC_REP_DATE');
                $html .= "<td align='left'>{$date}</td>";
                $doc1 = $hqpTab->getBlobValue('HQP_EPIC_DOCS_A');
                $html .= "<td align='center'><span style='font-size:2em;'>{$doc1}</span></td>";
                $doc2 = $hqpTab->getBlobValue('HQP_EPIC_DOCS_COI');
                $html .= "<td align='center'><span style='font-size:2em;'>{$doc2}</span></td>";
                $doc3 = $hqpTab->getBlobValue('HQP_EPIC_DOCS_NDA');
                $html .= "<td align='center'><span style='font-size:2em;'>{$doc3}</span></td>";
            }
            if($contactHeader != ''){
                if($person->getEmail() == ""){
                    $html .= "<td></td>";
                }
                else {
                    $html .= "<td align='left'><a href='mailto:{$person->getEmail()}'>{$person->getEmail()}</a></td>";
                }
                $html .= "<td align='left'>{$person->getPhoneNumber()}</td>";
            }
            if($emailHeader != ''){
                if($person->getEmail() == ""){
                    $html .= "<td></td>";
                }
                else {
                    $html .= "<td><a href='mailto:{$person->getEmail()}'>{$person->getEmail()}</a></td>";
                }
            }
            if($idHeader != ''){
                $html .= "<td>{$person->getId()}</td>";
            }
            $html .= "</tr>";
        }
        $html .= "</tbody></table><script type='text/javascript'>
            $('.indexTable.{$this->id}').dataTable({
                'aLengthMenu': [[100,-1], [100,'All']], 
                'iDisplayLength': 100, 
                'autoWidth':false,
                'columnDefs': [
                    ($('.indexTable.{$this->id} th').index($('#epicHeader')) != -1) ? {'type': 'date', 'targets': $('.indexTable.{$this->id} th').index($('#epicHeader'))} : {}
                ],
                'dom': 'Blfrtip',
                'buttons': [
                    'excel', 'pdf'
                ]
            });
        </script>";
        if($count == 0){
            $html = "No people found for this time period";
        }
        return $html;
    }
    
}

?>
