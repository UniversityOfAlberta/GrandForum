<?php

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['ManagePeopleLog'] = 'ManagePeopleLog'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['ManagePeopleLog'] = $dir . 'ManagePeopleLog.i18n.php';
$wgSpecialPageGroups['ManagePeopleLog'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'ManagePeopleLog::createSubTabs';

function runManagePeopleLog($par){
    ManagePeopleLog::execute($par);
}

class ManagePeopleLog extends SpecialPage{

	function __construct() {
		SpecialPage::__construct("ManagePeopleLog", null, false, 'runManagePeopleLog');
	}
	
	function userCanExecute($wgUser){
	    $person = Person::newFromUser($wgUser);
	    return $person->isRoleAtLeast(STAFF);
	}

	function execute($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
		$this->getOutput()->setPageTitle("Manage People Log");
		$notifications = array();
	    $data = DBFunctions::execSQL("SELECT id 
	                                  FROM grand_notifications
	                                  WHERE user_id = 0
	                                  AND (name = 'Role Changed' OR
	                                       name = 'Role Removed' OR
	                                       name = 'Role Added' OR
	                                       name = 'Project Membership Changed' OR
	                                       name = 'Project Membership Removed' OR
	                                       name = 'Project Membership Added' OR
	                                       name = 'Alumni Changed' OR
	                                       name = 'Candidate Changed' OR
	                                       name = 'User Deleted')
	                                  ORDER BY time DESC");
	    foreach($data as $row){
	        $notifications[] = Notification::newFromId($row['id']);
	    }
	    $wgOut->addHTML("<table id='manageMemberHistory' frame='box' rules='all'>
	                        <thead>
	                            <tr>
	                                <th>Type<br /><small>The type of change</small></th>
	                                <th>User Name<br /><small>Who made the change</small></th>
	                                <th>Description<br /><small>The description of the change</small></th>
	                                <th>Timestamp<br /><small>When the change occurred</small></th>
	                            </tr>
	                        </thead>
	                        <tbody>");
	    foreach($notifications as $notification){
	        if($notification->creator != null){
	            $wgOut->addHTML("<tr>
	                                <td>{$notification->name}</td>
	                                <td>{$notification->creator->getNameForForms()}</td>
	                                <td>{$notification->description}</td>
	                                <td>{$notification->time}</td>
	                            </tr>");
	        }
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
	
	static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;

        if((new self)->userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "ManagePeopleLog") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("Manage People Log", "$wgServer$wgScriptPath/index.php/Special:ManagePeopleLog", $selected);
        }
        return true;
    }
}

?>
