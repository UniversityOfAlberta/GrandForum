<?php

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['MilestonesLog'] = 'MilestonesLog'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['MilestonesLog'] = $dir . 'MilestonesLog.i18n.php';
$wgSpecialPageGroups['MilestonesLog'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'MilestonesLog::createSubTabs';

function runMilestonesLog($par){
    MilestonesLog::execute($par);
}

class MilestonesLog extends SpecialPage{

	function MilestonesLog() {
		SpecialPage::__construct("MilestonesLog", null, false, 'runMilestonesLog');
	}
	
	function userCanExecute($wgUser){
	    $person = Person::newFromUser($wgUser);
	    return $person->isRoleAtLeast(STAFF);
	}

	function execute($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
		$projects = Project::getAllProjectsEver();
		$milestones = array();
	    foreach($projects as $project){
	        $milestones = array_merge($milestones, array_merge($project->getMilestones(true, false),
	                                                           $project->getMilestones(true, true)));
	    }
	    $wgOut->addHTML("<table id='milestonesHistory' frame='box' rules='all'>
	                        <thead>
	                            <tr>
	                                <th>Type</th>
	                                <th>User Name</th>
	                                <th>Milestone</th>
	                                <th>Description</th>
	                                <th>Project</th>
	                                <th>Timestamp</th>
	                            </tr>
	                        </thead>
	                        <tbody>");
	    foreach($milestones as $milestone){
	        do {
	            $action = ($milestone->getParent() == null) ? "Created" : "Updated";
	            $wgOut->addHTML("<tr>
	                                <td>{$action}</td>
	                                <td>{$milestone->editedBy->getNameForForms()}</td>
	                                <td>{$milestone->getTitle()}</td>
	                                <td>{$milestone->getDescription()}</td>
	                                <td>{$milestone->getProject()->getName()}</td>
	                                <td style='white-space:nowrap;'>{$milestone->getCreated()}</td>
	                            </tr>");
	        }
	        while($milestone=$milestone->getParent());
	    }
	    $wgOut->addHTML("   </tbody>
	                     </table>");
	    $wgOut->addHTML("<script type='text/javascript'>
	        $('#milestonesHistory').dataTable({
	            'iDisplayLength': 100,
                'aaSorting': [[4,'desc']],
                'autoWidth': false,
                'aLengthMenu': [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']],
                'dom': 'Blfrtip',
                'buttons': [
                    'excel', 'pdf'
                ]
            });
	    </script>");
	}
	
	static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;

        if(self::userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "MilestonesLog") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("Milestones Log", "$wgServer$wgScriptPath/index.php/Special:MilestonesLog", $selected);
        }
        return true;
    }
}

?>
