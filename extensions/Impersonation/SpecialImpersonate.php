<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Impersonate'] = 'Impersonate'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Impersonate'] = $dir . 'SpecialImpersonate.i18n.php';
$wgSpecialPageGroups['Impersonate'] = 'network-tools';

$wgHooks['ToolboxLinks'][] = 'Impersonate::createDelegateLink';

function runImpersonate($par) {
  Impersonate::execute($par);
}

class Impersonate extends SpecialPage {

	function Impersonate() {
	    global $wgOut, $wgServer, $wgScriptPath;
	    SpecialPage::__construct("Impersonate", null, true, 'runImpersonate');
	}
	
	function userCanExecute($user){
	    global $wgImpersonate, $wgDelegate;
	    if($wgImpersonate || $wgDelegate){
	        return false;
	    }
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF) || $person->isRole(SD) || count($person->getDelegates()) > 0);
    }
	
	function execute($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
	    $user = Person::newFromWgUser();
	    $allPeople = array();
	    if($user->isRoleAtLeast(STAFF) || $user->isRole(SD)){
	        $allPeople = Person::getAllCandidates('all');
	        $i = 0;
            foreach(Person::getAllStaff() as $person){
                $allPeople[] = $person;
            }
        }
        else if(count($user->getDelegates()) > 0){
            $allPeople = $user->getDelegates();
        }
	    
	    $wgOut->addHTML("<span id='pageDescription'>Impersonating allows you to temporarily view the Forum as another user.  Select a user from the list below, and then click the 'Impersonate' button to begin a session.</span><table>
	                        <tr><td>
	                            <select id='names' data-placeholder='Chose a Person...' name='name' size='10' style='width:100%'>");
	    foreach($allPeople as $person){
	        $wgOut->addHTML("<option value=\"{$person->getName()}\">".str_replace(".", " ", $person->getNameForForms())."</option>\n");
	    }
	    $wgOut->addHTML("</select>
	            </td></tr>
	            <tr><td>
	        <input type='button' id='button' name='next' value='Impersonate' disabled='disabled' /></td></tr></table>
	    <script type='text/javascript'>
	        $('#names').chosen();
	        $(document).ready(function(){
	            $('#names').change(function(){
	                var page = $('#names').val();
	                if(page != ''){
	                    $('#button').prop('disabled', false);
	                }
	            });
	            $('#button').click(function(){
                    var page = $('#names').val();
                    if(typeof page != 'undefined'){
                        document.location = '".$wgServer.$wgScriptPath."/index.php?impersonate=' + page;
                    }
                });
	        });
	    </script>");
	}
	
	static function createDelegateLink(&$toolbox){
        global $wgImpersonating, $wgDelegating, $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if(!$wgImpersonating && !$wgDelegating && count($me->getDelegates()) > 0){
            $link = TabUtils::createToolboxLink("Delegate", "$wgServer$wgScriptPath/index.php/Special:Impersonate");
            $toolbox['Other']['links'][] = $link;
        }
        else if(!$wgImpersonating && !$wgDelegating && ($me->isRoleAtLeast(STAFF) || $me->isRole(SD))){
            $link = TabUtils::createToolboxLink("Impersonate", "$wgServer$wgScriptPath/index.php/Special:Impersonate");
            $toolbox['Other']['links'][] = $link;
        }
        return true;
    }
	
}

?>
