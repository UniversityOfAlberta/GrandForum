<?php

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['MilestonesLog'] = 'MilestonesLog'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['MilestonesLog'] = $dir . 'MilestonesLog.i18n.php';
$wgSpecialPageGroups['MilestonesLog'] = 'network-tools';

$wgHooks['UnknownAction'][] = 'MilestonesLog::milestoneData';
$wgHooks['SubLevelTabs'][] = 'MilestonesLog::createSubTabs';

function runMilestonesLog($par){
    MilestonesLog::execute($par);
}

class MilestonesLog extends SpecialPage{

	function __construct() {
		SpecialPage::__construct("MilestonesLog", null, false, 'runMilestonesLog');
	}
	
	function userCanExecute($wgUser){
	    $person = Person::newFromUser($wgUser);
	    return $person->isRoleAtLeast(STAFF);
	}
	
	static function milestoneData($action){
	    if($action == "milestoneData"){
	        $data = array();
	        $projects = Project::getAllProjectsEver();
		    $milestones = array();
		    foreach($projects as $project){
		        foreach($project->getMilestones(true, false) as $milestone){
		            // Regular Milestones
		            $milestones[] = $milestone;
		        }
		        foreach($project->getMilestones(true, true) as $milestone){
		            // FES Milestones
		            $milestones[] = $milestone;
		        }
		    }
	        foreach($milestones as $key => $milestone){
	            $tmp = $milestone;
	            if(!Cache::exists("milestone_{$milestone->milestone_id}_{$milestone->id}")){
	                $milestone->prepareParents();
	            }
	            do {
	                $action = ($tmp->getParent() == null) ? "Created" : "Updated";
	                $data[] = array($action, 
	                                $tmp->editedBy->getNameForForms(), 
	                                $tmp->getTitle(),
	                                $tmp->getDescription(),
	                                "<span style='white-space:nowrap;'>{$tmp->getProject()->getName()}</span>",
	                                "<span style='white-space:nowrap;'>{$tmp->getCreated()}</span>");
	            }
	            while($tmp=$tmp->getParent());
	            $milestones[$key] = null;
	            unset($milestone->parent);
	            unset($milestone);
	            Milestone::$cache = array();
	        }
	        header('Content-Type: application/json');
	        echo json_encode(array("data" => $data));
	        exit;
	    }
	    return true;
	}

	function execute($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
	    $wgOut->addHTML("<table id='milestonesHistory' frame='box' rules='all'>
	                        <thead>
	                            <tr>
	                                <th>Action</th>
	                                <th>User Name</th>
	                                <th>Milestone</th>
	                                <th>Description</th>
	                                <th>Project</th>
	                                <th>Timestamp</th>
	                            </tr>
	                        </thead>
	                        <tbody>
	                        </tbody>
	                     </table>");
	    $wgOut->addHTML("<script type='text/javascript'>
	        $('#milestonesHistory').dataTable({
	            'iDisplayLength': 100,
                'aaSorting': [[5,'desc']],
                'ajax': '{$wgServer}{$wgScriptPath}/index.php?action=milestoneData',
                'deferRender': true,
                'autoWidth': false,
                'aLengthMenu': [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']],
                'dom': 'Blfrtip',
                'buttons': [
                    'excel'
                ]
            });
	    </script>");
	}
	
	static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        if((new self)->userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "MilestonesLog") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("Milestones Log", "$wgServer$wgScriptPath/index.php/Special:MilestonesLog", $selected);
        }
        return true;
    }
}

?>
