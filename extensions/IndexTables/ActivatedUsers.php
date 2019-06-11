<?php

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['ActivatedUsers'] = 'ActivatedUsers'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['ActivatedUsers'] = $dir . 'ActivatedUsers.i18n.php';
$wgSpecialPageGroups['ActivatedUsers'] = 'other-tools';

$wgHooks['SubLevelTabs'][] = 'ActivatedUsers::createSubTabs';

function runActivatedUsers($par){
    ActivatedUsers::execute($par);
}

class ActivatedUsers extends SpecialPage {

    function ActivatedUsers() {
		SpecialPage::__construct("ActivatedUsers", MANAGER.'+', true, 'runActivatedUsers');
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
                         $wgTitle->getText() == "ActivatedUsers") ? "selected" : ""; 
	        $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("Activated Users", 
                                                                   "$wgServer$wgScriptPath/index.php/Special:ActivatedUsers", 
                                                                   "$selected");
        }
	}
}

?>
