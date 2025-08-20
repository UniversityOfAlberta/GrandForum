<?php

class PositionTableTab extends PeopleTableTab {

    var $table;
    var $visibility;
    var $past;
    
    function __construct($table, $visibility, $past=false){
        parent::__construct($table, $visibility, $past);
    }

    function generateBody(){
        $this->html = "<span class='throbber'></span>";
    }
    
    function getHTML(){
        global $wgServer, $wgScriptPath, $wgUser, $wgOut, $config, $wgRoleValues;
        $html = "";
        $me = Person::newFromId($wgUser->getId());
        
        $start = "0000-00-00";
        $end = date('Y-m-d');
        
        $data = array();
        foreach(Person::getAllPeople() as $person){
            foreach($person->getUniversities() as $uni){
                if($uni['position'] == $this->table){
                    $data[] = $person;
                }
            }
        }

        $emailHeader = "";
        $idHeader = "";
        $epicHeader = "";
        $hqpHeader = "";
        $contactHeader = "";
        $projectsHeader = "";
        $uniHeader = "";
        $committees = $config->getValue('committees');
        if($me->isRoleAtLeast(ADMIN)){
            $idHeader = "<th style='white-space: nowrap;'>User Id</th>";
        }

        $emailHeader = "<th style='white-space: nowrap;'>Email</th><th style='white-space: nowrap;'>Website</th>";

        if($config->getValue('projectsEnabled')){
            $projectsHeader = "<th style='white-space: nowrap;'>".Inflect::pluralize($config->getValue('projectTerm'))."</th>";
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
            if($config->getValue('projectsEnabled')){
                $projects = array_merge($person->leadershipDuring($start, $end), $person->getProjectsDuring($start, $end));
                $projs = array();
                foreach($projects as $project){
                    if(!$project->isSubProject() && !isset($projs[$project->getId()]) &&
                        $project->getStatus() != "Proposed"){
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
