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
		$people = Person::getAllPeople();
    
        $wgOut->addHTML("<table id='peopleTable' class='wikitable'>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Roles</th>
                    <th>Projects</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Recruited</th>
                    <th>Recruited Country</th>
                    <th>Alumni</th>
                    <th>Alumni Country</th>
                    <th>Alumni Sector</th>
                </tr>
            </thead>
            <tbody>");
        
        foreach($people as $person){
            $roles = $person->getRoles(true);
            $earliestDate = "9999-99-99";
            $latestDate = "0000-00-00";
            foreach($roles as $role){
                $earliestDate = min($earliestDate, $role->getStartDate());
                $latestDate = max($latestDate, str_replace("0000-00-00", "9999-99-99", $role->getEndDate()));
            }
            $earliestDate = substr($earliestDate, 0, 10);
            $latestDate = str_replace("9999-99-99", "Current", substr($latestDate, 0, 10));
            $alumni = Alumni::newFromUserId($person->getId());
            $projects = array_merge($person->leadership(), $person->getProjects());
            $projs = array();
            foreach($projects as $project){
                if(!$project->isSubProject() && !isset($projs[$project->getId()]) &&
                    $project->getStatus() != "Proposed"){
                    $projs[$project->getId()] = "<a href='{$project->getUrl()}'>{$project->getName()}</a>";
                }
            }
            $projectsRow = implode("<br />", $projs);
            $wgOut->addHTML("<tr>
                             <td>{$person->getReversedName()}</td>
                             <td>{$person->getEmail()}</td>
                             <td>{$person->getRoleString()}</td>
                             <td align='left' style='white-space: nowrap;'>{$projectsRow}</td>
                             <td>{$earliestDate}</td>
                             <td>{$latestDate}</td>
                             <td>{$alumni->recruited}</td>
                             <td>{$alumni->recruited_country}</td>
                             <td>{$alumni->alumni}</td>
                             <td>{$alumni->alumni_country}</td>
                             <td>{$alumni->alumni_sector}</td>
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
                'excel', 'pdf'
            ]
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
