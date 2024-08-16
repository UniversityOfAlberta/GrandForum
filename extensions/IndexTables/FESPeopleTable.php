<?php

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['FESPeopleTable'] = 'FESPeopleTable'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['FESPeopleTable'] = $dir . 'FESPeopleTable.i18n.php';
$wgSpecialPageGroups['FESPeopleTable'] = 'other-tools';

$wgHooks['SubLevelTabs'][] = 'FESPeopleTable::createSubTabs';

function runFESPeopleTable($par){
    FESPeopleTable::execute($par);
}

class FESPeopleTable extends SpecialPage {

    function FESPeopleTable() {
		SpecialPage::__construct("FESPeopleTable", STAFF.'+', true, 'runFESPeopleTable');
	}

    function execute($par){
		global $wgServer, $wgScriptPath, $wgUser, $wgOut, $config;
		$me = Person::newFromWgUser();
		$people = Person::getAllPeople();
    
        $wgOut->addHTML("<table id='peopleTable' class='wikitable'>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Employee ID</th>
                    <th>Roles</th>
                    <th style='display:none;'>Role Comments</th>
                    <th>".Inflect::pluralize($config->getValue('subRoleTerm'))."</th>
                    <th>Projects</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Recruited</th>
                    <th>Recruited Country</th>
                    <th>Alumni</th>
                    <th>Alumni Country</th>
                    <th>Alumni Sector</th>
                    <th>Institution</th>
                    <th>Faculty</th>
                    <th>Department</th>");
        if($config->getValue("genderEnabled") && (!$config->getValue('networkName') == "FES" || $me->getName() == "Samuel.Ferraz")){ // TODO: This is ugly
            $wgOut->addHTML("<th>Gender</th>");
        }
        if($config->getValue('crcEnabled')){
            $wgOut->addHTML("<th>CRC</th>");
        }
        if($config->getValue('ecrEnabled')){
            $wgOut->addHTML("<th>ECR</th>");
        }
        if($config->getValue('mitacsEnabled')){
            $wgOut->addHTML("<th>MITACS</th>");
        }
        if($me->isRoleAtLeast(MANAGER) && (!$config->getValue('networkName') == "FES" || $me->getName() == "Samuel.Ferraz")){ // TODO: This is ugly
            $wgOut->addHTML("
                    <th style='display:none;'>Pronouns</th>
                    <th style='display:none;'>Indigenous</th>
                    <th style='display:none;'>Disability</th>
                    <th style='display:none;'>Minority</th>
                    <th style='display:none;'>Group</th>");
        }
        $wgOut->addHTML("
                    <th>Nationality</th>
                    <th>Status</th>
                    <th>Relationships?</th>
                    <th>Supervises</th>
                    <th>Mentors</th>
                    <th>Works With</th>
                    <th>Bio</th>
                    <th>Keywords</th>
                </tr>
            </thead>
            <tbody>");
        
        foreach($people as $person){
            $roles = $person->getRoles(true);
            $roleComments = array();
            foreach($roles as $role){
                $roleComments[] = $role->getComment();
            }
            $earliestDate = EOT;
            $latestDate = "0000-00-00";
            foreach($roles as $role){
                $earliestDate = min($earliestDate, $role->getStartDate());
                $latestDate = max($latestDate, str_replace("0000-00-00", EOT, $role->getEndDate()));
            }
            $earliestDate = substr($earliestDate, 0, 10);
            $latestDate = str_replace(EOT, "Current", substr($latestDate, 0, 10));
            $alumni = Alumni::newFromUserId($person->getId());
            $projects = array_merge($person->leadership(true), $person->getProjects(true));
            $projs = array();
            foreach($projects as $project){
                if(!isset($projs[$project->getId()]) &&
                    $project->getStatus() != "Proposed"){
                    $projs[$project->getId()] = "<a href='{$project->getUrl()}'>{$project->getName()}</a>";
                }
            }
            $universities = $person->getUniversities();
            $positions = array();
            foreach($universities as $uni){
                $positions[$uni['position']] = $uni['position'];
            }
            $projectsRow = implode(", ", $projs);
            if($person->isActive()){
                $status = "Active";
            }
            else{
                $status = "Inactive";                
            }
            $profile = $person->getProfile(true);
            if($profile == ""){
                $profile = $person->getProfile(false);
            }
            $wgOut->addHTML("<tr>
                             <td>{$person->getReversedName()}</td>
                             <td>{$person->getEmail()}</td>
                             <td>{$person->getEmployeeId()}</td>
                             <td>{$person->getRoleString()}</td>
                             <td style='display:none;'>".implode("<br />", $roleComments)."</td>
                             <td>".implode(", ", $positions)."</td>
                             <td align='left'>{$projectsRow}</td>
                             <td>{$earliestDate}</td>
                             <td>{$latestDate}</td>
                             <td>{$alumni->recruited}</td>
                             <td>{$alumni->recruited_country}</td>
                             <td>{$alumni->alumni}</td>
                             <td>{$alumni->alumni_country}</td>
                             <td>{$alumni->alumni_sector}</td>
                             <td>{$person->getUni()}</td>
                             <td>{$person->getFaculty()}</td>
                             <td>{$person->getDepartment()}</td>");
            if($config->getValue("genderEnabled") && (!$config->getValue('networkName') == "FES" || $me->getName() == "Samuel.Ferraz")){ // TODO: This is ugly
                $wgOut->addHTML("<td>{$person->getGender()}</td>");
            }
            if($config->getValue('crcEnabled')){
                $crcObj = $person->getCanadaResearchChair();
                if($crcObj != null){
                    $crcObj = array_filter($crcObj);
                }
                $wgOut->addHTML("<td>".@implode("<br />\n", $crcObj)."</td>");
            }
            if($config->getValue('ecrEnabled')){
                $wgOut->addHTML("<td>{$person->getEarlyCareerResearcher()}</td>");
            }
            if($config->getValue('mitacsEnabled')){
                $wgOut->addHTML("<td>{$person->getMitacs()}</td>");
            }
            $supervises = array();
            $mentors = array();
            $worksWith = array();
            foreach($person->getHQP(true) as $hqp){
                $supervises[$hqp->getId()] = "<span style='white-space:nowrap;'>{$hqp->getNameForForms()}</span>";
            }
            foreach($person->getRelations(MENTORS, true) as $r){
                $mentors[$r->getUser2()->getId()] = "<span style='white-space:nowrap;'>{$r->getUser2()->getNameForForms()}</span>";
            }
            foreach($person->getRelations(WORKS_WITH, true) as $r){
                $worksWith[$r->getUser2()->getId()] = "<span style='white-space:nowrap;'>{$r->getUser2()->getNameForForms()}</span>";
            }
            foreach($person->getRelations(WORKS_WITH, true, true) as $r){
                $worksWith[$r->getUser1()->getId()] = "<span style='white-space:nowrap;'>{$r->getUser1()->getNameForForms()}</span>";
            }
            $inverse = $person->getRelations('all', true, true);
            $relationships = (count($supervises) + count($mentors) + count($worksWith) + count($inverse) > 0) ? "Yes" : "No";
            if($me->isRoleAtLeast(MANAGER) && (!$config->getValue('networkName') == "FES" || $me->getName() == "Samuel.Ferraz")){ // TODO: This is ugly
                $wgOut->addHTML("<td style='display:none;'>{$person->getPronouns()}</td>
                                 <td style='display:none;'>{$person->getIndigenousStatus()}</td>
                                 <td style='display:none;'>{$person->getDisabilityStatus()}</td>
                                 <td style='display:none;'>{$person->getMinorityStatus()}</td>
                                 <td style='display:none;'>{$person->getEthnicity()}</td>");
            }
            $wgOut->addHTML("<td>{$person->getNationality()}</td>
                             <td>{$status}</td>
                             <td>{$relationships}</td>
                             <td>".implode(", ", $supervises)."</td>
                             <td>".implode(", ", $mentors)."</td>
                             <td>".implode(", ", $worksWith)."</td>
                             <td>{$profile}</td>
                             <td>{$person->getKeywords(', ')}</td>
                            </tr>");
        }
        
        $wgOut->addHTML("</tbody></table>");
        $wgOut->addHTML("<script type='text/javascript'>
        $('#peopleTable').dataTable({
            'aLengthMenu': [[100,-1], [100,'All']], 
            'iDisplayLength': 100, 
            'autoWidth':false,
            'dom': 'Blfrtip',
            'buttons': [
                {
                    extend: 'excel',
                    exportOptions: {
                        format: {
                            body: function ( data, column, row ) {
                                return $('<div>' + data.replace( /<br\s*\/?>/ig, \"\\n\" ) + '</div>').text().trim();
                            }
                        }
                    }
                },
                'pdf'
            ],
        });</script>");
	}
	
	static function createSubTabs(&$tabs){
	    global $wgServer, $wgScriptPath, $wgTitle;
	    $person = Person::newFromWgUser();
	    if($person->isRoleAtLeast(STAFF)){
	        $selected = @($wgTitle->getText() == "FESPeopleTable") ? "selected" : false;
	        $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("Members", "$wgServer$wgScriptPath/index.php/Special:FESPeopleTable", $selected);
	    }
	    return true;
    }
}

?>
