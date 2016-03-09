<?php

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['ManagePeopleHistory'] = 'ManagePeopleHistory'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['ManagePeopleHistory'] = $dir . 'ManagePeopleHistory.i18n.php';
$wgSpecialPageGroups['ManagePeopleHistory'] = 'network-tools';

$wgHooks['UnknownAction'][] = 'contributionSearch';

function runManagePeopleHistory($par){
    ManagePeopleHistory::execute($par);
}

class ManagePeopleHistory extends SpecialPage{

	function ManagePeopleHistory() {
		SpecialPage::__construct("ManagePeopleHistory", STAFF.'+', true, 'runManagePeopleHistory');
	}

	function execute($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
		$notifications = array();
	    $data = DBFunctions::execSQL("SELECT id 
	                                  FROM grand_notifications
	                                  WHERE user_id = 0
	                                  AND (name = 'Role Changed' OR
	                                       name = 'Role Removed' OR
	                                       name = 'Role Added' OR
	                                       name = 'Project Membership Changed' OR
	                                       name = 'Project Membership Removed' OR
	                                       name = 'Project Membership Added')
	                                  ORDER BY time DESC");
	    foreach($data as $row){
	        $notifications[] = Notification::newFromId($row['id']);
	    }
	    $wgOut->addHTML("<table id='manageMemberHistory' frame='box' rules='all'>
	                        <thead>
	                            <tr>
	                                <th>Type</th>
	                                <th>User Name</th>
	                                <th>Description</th>
	                                <th>Timestamp</th>
	                            </tr>
	                        </thead>
	                        <tbody>");
	    foreach($notifications as $notification){
	        $wgOut->addHTML("<tr>
	                            <td>{$notification->name}</td>
	                            <td>{$notification->creator->getNameForForms()}</td>
	                            <td>{$notification->description}</td>
	                            <td>{$notification->time}</td>
	                        </tr>");
	    }
	    $wgOut->addHTML("   </tbody>
	                     </table>");
	    $wgOut->addHTML("<script type='text/javascript'>
	        $('#manageMemberHistory').dataTable({'iDisplayLength': 100,
                'aaSorting': [[3,'desc']],
                'autoWidth': false,
                'aLengthMenu': [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']]});
	    </script>");
	}
}

?>
