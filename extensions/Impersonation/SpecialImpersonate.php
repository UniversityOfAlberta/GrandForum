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
	    $wgOut->addScript("<script type='text/javascript'>
	        $(document).ready(function(){
	            $('#button').val('Impersonate');
	            $('#pageDescription').html('Select a user from the list below, and then click the \'Impersonate\' button.  You can filter out the selection box by searching a name, user role, or project below.');
	            $('#mainForm').attr('method', 'get');
	            $('#mainForm').attr('action', '$wgServer$wgScriptPath/index.php');
	            $('#button').click(function(){
                    var page = $('select option:selected').attr('name');
                    if(typeof page != 'undefined'){
                        document.location = '".$wgServer.$wgScriptPath."/index.php?impersonate=' + page;
                    }
                });
	        });
	    </script>");
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
	        $names = array();
	        foreach($allPeople as $person){
	            $names[] = $person->getName();
	        }
            foreach(Person::getAllStaff() as $person){
                $names[] = $person->getName();
                $allPeople[] = $person;
            }
        }
        else if(count($user->getDelegates()) > 0){
            $allPeople = $user->getDelegates();
            foreach($allPeople as $person){
                $names[] = $person->getName();
            }
        }
        $names = array_unique($names);
	    
	    $wgOut->addHTML("<span id='pageDescription'>Select a user from the list below, and then click the 'Go To User&#39;s Page' button.  You can filter out the selection box by searching a name, user role, or project below.</span><br/>
            <div style='margin-top: 8px;'>
                <input type='hidden' name='inputImpersonation' />
                <select class='filter_option' style='width: 225px;' id='chooseImpersonation' data-placeholder='Select a User'>
                <option></option>
            "
        );
	    foreach($allPeople as $person){
	        $projects = $person->getProjects();
	        $roles = $person->getRoles();
	        $projs = array();
	        foreach($projects as $project){
	            $projs[] = $project->getName();
	        }
	        $wgOut->addHTML("<option value='".str_replace("'", "&#39", $person->getName())."' class='".implode(" ", $projs)."' name='{$person->getName()}' id='".unaccentChars(str_replace(".", "", $person->getName()))."'>".str_replace(".", " ", $person->getNameForForms())."</option>\n");
	    }
	    $wgOut->addHTML("
            </select>
            </div>
            <br/>
	        <input type='button' id='button' name='next' value='Impersonate' style='margin-top: 8px;'/>
            <script type='text/javascript'>
                this.$('#chooseImpersonation').chosen();
            </script>");

	}
	
	static function createDelegateLink(&$toolbox){
        global $wgImpersonating, $wgDelegating, $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
/*        if(!$wgImpersonating && !$wgDelegating && count($me->getDelegates()) > 0){
            $link = TabUtils::createToolboxLink("Delegate", "$wgServer$wgScriptPath/index.php/Special:Impersonate");
            $toolbox['Other']['links'][] = $link;
        }
        else if(!$wgImpersonating && !$wgDelegating && ($me->isRoleAtLeast(STAFF) || $me->isRole(SD))){
            $link = TabUtils::createToolboxLink("Impersonate", "$wgServer$wgScriptPath/index.php/Special:Impersonate");
            $toolbox['Other']['links'][] = $link;
        }*/
        return true;
    }
	
}

?>
