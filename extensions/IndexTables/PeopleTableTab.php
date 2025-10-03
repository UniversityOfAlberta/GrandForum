<?php

class PeopleTableTab extends AbstractTab {

    var $table;
    var $visibility;
    var $past;

    function __construct($table, $visibility, $past=false){
        global $config, $wgOut;
        if($table != "Candidate"){
            if(isset($config->getValue('roleDefs')[$table])){
                $tabTitle = Inflect::pluralize($config->getValue('roleDefs', $table));
            }
            else{
                $tabTitle = Inflect::pluralize($table);
            }
        }
        else{
            $tabTitle = Inflect::pluralize($table);
        }
        $tabTitle = ucwords($tabTitle);
        $wgOut->setPageTitle($tabTitle);
        if(!$past){
            parent::__construct($tabTitle);
        }
        else if($past === "6 months"){
            parent::__construct("6 months");
        } 
        else if(is_numeric($past)){
            parent::__construct("$past-".($past+1));
        }
        else {
            parent::__construct("Former");
        }
        $this->table = $table;
        $this->visibility = $visibility;
        $this->past = $past;
        if(isset($_GET['getHTML']) && @$_GET['tab'] == $this->id){
            echo $this->getHTML();
            close();
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
        else if($this->past === "6 months"){
            $html .= "<p>Shows all people who have been active on a project for more than 6 months</p>";
            $start = "0000-00-00";
            $end = date('Y-m-d');
            $data = array();
            $datatmp = Person::getAllPeopleDuring($this->table, "0000-00-00", date('Y-m-d'));
            $now = new DateTime(date("Y-m-d", time()));
            foreach($datatmp as $person){
                foreach($person->getPersonProjects() as $project){
                    $p = Project::newFromId($project['projectId']);
                    $startDate = new DateTime($project['startDate']);
                    if(substr($p->getEndDate(),0,10) != "0000-00-00" && ($p->getEndDate() <= $project['endDate'] || substr($project['endDate'],0,10) == "0000-00-00")){
                        $endDate = new DateTime($p->getEndDate());
                    } 
                    else{
                        $endDate = new DateTime($project['endDate']);
                    }
                    
                    $interval1 = $startDate->diff($now);
                    $interval2 = $startDate->diff($endDate);
                    $diff1 = abs((($interval1->y) * 12) + ($interval1->m));
                    $diff2 = abs((($interval2->y) * 12) + ($interval2->m));
                    if(($diff1 >= 6 && $diff2 >= 6) || ($diff1 >= 6 && substr($project['endDate'],0,10) == "0000-00-00")){
                        $data[] = $person;
                        break;
                    }
                }
            }
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
            $emailHeader = "<th style='white-space: nowrap;'>Email</th><th style='white-space: nowrap;'>Website</th>";
        }
        if($this->table == HQP){
            if($me->isRoleAtLeast(STAFF)){
                $subRoleHeader = "<th style='white-space: nowrap;'>".Inflect::pluralize($config->getValue('subRoleTerm'))."</th>";
            }
            if($config->getValue('networkName') == 'AGE-WELL' && ($me->isRoleAtLeast(STAFF) || $me->isThemeLeader() || $me->isThemeCoordinator())){
                $epicHeader = "<th id='epicHeader' style='white-space: nowrap;'>EPIC Due Date</th>
                               <th style='white-space: nowrap;'>Appendix A</th>
                               <th style='white-space: nowrap;'>COI</th>
                               <th style='white-space: nowrap;'>NDA</th>";
            }
            $hqpHeader = "<th>Supervisors</th>";
        }
        if($config->getValue('projectsEnabled') && !isset($committees[$this->table])){
            $projectsHeader = "<th style='white-space: nowrap;'>".Inflect::pluralize($config->getValue('projectTerm'))."</th>";
        }
        $statusHeader = "";
        if($me->isRoleAtLeast(STAFF)){
            $statusHeader .= "<th>Google Scholar</th>
                              <th>ORCID</th>
                              <th>Scopus</th>
                              <th>Researcher Id</th>";
            if($config->getValue("genderEnabled") && (!$config->getValue('networkName') == "FES" || $me->getName() == "Samuel.Ferraz")){ // TODO: This is ugly
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
            if($me->isRoleAtLeast(MANAGER) && (!$config->getValue('networkName') == "FES" || $me->getName() == "Samuel.Ferraz")){ // TODO: This is ugly
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
        $facultyHead = ($config->getValue("splitDept")) ? "<th style='white-space: nowrap; width:20%;'>Faculty</th>" : "";
        $firstFacultyHead = ($config->getValue("splitDept")) ? "<th style='display:none;'>First Faculty</th>" : "";
        
        $html .= "<table class='indexTable {$this->id}' frame='box' rules='all'>
                            <thead>
                                <tr>
                                    <th style='white-space: nowrap; width:20%;'>Name</th>
                                    <th style='display:none;'>First Name</th>
                                    <th style='display:none;'>Last Name</th>
                                    {$subRoleHeader}
                                    {$projectsHeader}
                                    {$uniHeader}
                                    {$facultyHead}
                                    <th style='white-space: nowrap; width:20%;'>{$config->getValue('deptsTerm')}</th>
                                    <th style='white-space: nowrap; width:20%;'>Title / Rank</th>
                                    <th style='display:none;'>Start Date</th>
                                    <th style='display:none;'>End Date</th>
                                    <th style='display:none;'>Prev University</th>
                                    <th style='display:none;'>Prev {$config->getValue('deptsTerm')}</th>
                                    <th style='display:none;'>Prev Title / Rank</th>
                                    <th style='display:none;'>Prev Start Date</th>
                                    <th style='display:none;'>Prev End Date</th>
                                    <th style='display:none;'>First University</th>
                                    {$firstFacultyHead}
                                    <th style='display:none;'>First {$config->getValue('deptsTerm')}</th>
                                    <th style='display:none;'>First Title / Rank</th>
                                    <th style='display:none;'>First Start Date</th>
                                    <th style='display:none;'>First End Date</th>
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
                    $subRole = $config->getValue('subRoles', $sub, true);
                    if($subRole){
                        $subRoles[] = $subRole;
                    }
                }
                $html .= "<td style='white-space:nowrap;' align='left'>".implode(",<br />", $subRoles)."</td>";
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
                $html .= "<td align='left'>".implode(", ", $projs)."</td>";
            }
            // Current University
            $university = $person->getUniversity();
            if($uniHeader != ''){
                $html .= "<td align='left'>{$university['university']}</td>";
            }
            if($facultyHead != ""){
                $html .= "<td align='left'>{$person->getFaculty()}</td>";
            }
            $subPosition = (isset($person->getExtra()['sub_position']) && $person->getExtra()['sub_position'] != "") ? " / {$person->getExtra()['sub_position']}" : "";
            $html .= "<td align='left'>{$person->getDepartment()}</td>";
            $html .= "<td align='left'>{$university['position']}{$subPosition}</td>";
            $html .= "<td align='left' style='display:none;'>{$university['start']}</td>";
            $html .= "<td align='left' style='display:none;'>{$university['end']}</td>";
            
            // Previous University
            $prevuniversity = $person->getPreviousUniversity();
            $html .= "<td style='display:none;' align='left'>{$prevuniversity['university']}</td>";
            $html .= "<td style='display:none;' align='left'>{$prevuniversity['department']}</td>";
            $html .= "<td style='display:none;' align='left'>{$prevuniversity['position']}</td>";
            $html .= "<td align='left' style='display:none;'>{$prevuniversity['start']}</td>";
            $html .= "<td align='left' style='display:none;'>{$prevuniversity['end']}</td>";
            
            // First University
            $firstuniversity = $person->getFirstUniversity();
            $html .= "<td style='display:none;' align='left'>{$firstuniversity['university']}</td>";
            if($firstFacultyHead != ""){
                $html .= "<td style='display:none;' align='left'>{$firstuniversity['faculty']}</td>";
            }
            $html .= "<td style='display:none;' align='left'>{$firstuniversity['department']}</td>";
            $html .= "<td style='display:none;' align='left'>{$firstuniversity['position']}</td>";
            $html .= "<td align='left' style='display:none;'>{$firstuniversity['start']}</td>";
            $html .= "<td align='left' style='display:none;'>{$firstuniversity['end']}</td>";
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
                $html .= "<td>{$person->getGoogleScholar()}</td>
                          <td>{$person->getOrcid()}</td>
                          <td>{$person->getScopus()}</td>
                          <td>{$person->getResearcherId()}</td>";
                if($config->getValue("genderEnabled") && (!$config->getValue('networkName') == "FES" || $me->getName() == "Samuel.Ferraz")){ // TODO: This is ugly){
                    $html .= "<td align='left'>{$person->getGender()}</td>";
                }
                if($config->getValue('crcEnabled')){
                    $crcObj = $person->getCanadaResearchChair();
                    if($crcObj != null){
                        $crcObj = array_filter($crcObj);
                    }
                    $html .= "<td>".@implode("<br />", $crcObj)."</td>";
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
                if($me->isRoleAtLeast(MANAGER) && (!$config->getValue('networkName') == "FES" || $me->getName() == "Samuel.Ferraz")){ // TODO: This is ugly){
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
                $html .= "<td align='left'><a href='mailto:{$person->getEmail()}'>{$person->getEmail()}</a></td><td align='left'>{$person->getPhoneNumber()}</td>";
            }
            if($emailHeader != ''){
                $html .= ($person->getEmail() != "") ? "<td align='left'><a href='mailto:{$person->getEmail()}'>{$person->getEmail()}</a></td>" : "<td></td>";
                $html .= ($person->getWebsite() != "http://" && $person->getWebsite() != "https://") ? "<td align='left'><a href='{$person->getWebsite()}'>{$person->getWebsite()}</a></td>" : "<td></td>";
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
