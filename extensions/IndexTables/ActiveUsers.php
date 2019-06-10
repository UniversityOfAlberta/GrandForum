<?php

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['ActiveUsers'] = 'ActiveUsers'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['ActiveUsers'] = $dir . 'ActiveUsers.i18n.php';
$wgSpecialPageGroups['ActiveUsers'] = 'other-tools';

$wgHooks['SubLevelTabs'][] = 'ActiveUsers::createSubTabs';

function runActiveUsers($par){
    ActiveUsers::execute($par);
}

class ActiveUsers extends SpecialPage {

    function ActiveUsers() {
		SpecialPage::__construct("ActiveUsers", MANAGER.'+', true, 'runActiveUsers');
	}

    function execute($par){
		global $wgServer, $wgScriptPath, $wgUser, $wgOut, $config;
		$text = "";
		$data = Person::getAllCandidates('all');

		$text .= "<table id='activeUsers' class='wikitable' frame='box' rules=all'>
                    <thead><tr bgcolor='#F2F2F2'><th>Last Name</th><th>First Name</th><th>Email</th><th>Status</th></tr></thead>
                    <tbody>";
		
		foreach($data as $person){
		    $user = $person->getUser();
		    if($user->getEmailAuthenticationTimestamp() != ""){
		        $status = "Activated";   
		    }
		    else{
		        $status = "Not Activated";
		    }
		    $text .= "<tr bgcolor='#FFFFFF'>
                        <td align='left'>
                            <a href='{$person->getUrl()}'>{$person->getLastName()}</a>
                        </td>
                        <td align='left'>
                            <a href='{$person->getUrl()}'>{$person->getFirstName()}</a>
                        </td>
                        <td align='left'>
                            <a href='{$person->getUrl()}'>{$person->getEmail()}</a>
                        </td>
                        <td>
                            {$status}
                        </td>
                    </tr>";
		}
		$text .= "</tbody></table>
		<script type='text/javascript'>
		    $('#activeUsers').dataTable({
		        'aLengthMenu': [[100,-1], [100,'All']], 
                'iDisplayLength': 100
            });
		</script>";
        $wgOut->addHTML($text);
		return true;
	}
	
	static function createSubTabs(&$tabs){
	    global $wgServer, $wgScriptPath, $wgTitle, $config;
	    $me = Person::newFromWgUser();
	    if($me->isRoleAtLeast(MANAGER)){
            $selected = ($wgTitle->getNSText() == "Special" && 
                         $wgTitle->getText() == "ActiveUsers"); 
	        $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("Active Users", 
                                                                   "$wgServer$wgScriptPath/index.php/Special:ActiveUsers", 
                                                                   "$selected");
        }
	}
}

?>
