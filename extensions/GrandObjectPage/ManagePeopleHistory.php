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
	    $data = DBFunctions::execSQL("SELECT * 
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
	        $notifications[] = Notification($row['name'], $row['description'], $row['url'], $row['time']);
	    }
	    
	}
}

?>
