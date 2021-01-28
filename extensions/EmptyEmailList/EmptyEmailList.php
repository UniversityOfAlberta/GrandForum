<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['EmptyEmailList'] = 'EmptyEmailList'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['EmptyEmailList'] = $dir . 'EmptyEmailList.i18n.php';
$wgSpecialPageGroups['EmptyEmailList'] = 'other-tools';

$wgHooks['SubLevelTabs'][] = 'EmptyEmailList::createSubTabs';

function runEmptyEmailList($par) {
  EmptyEmailList::execute($par);
}

class EmptyEmailList extends SpecialPage{

	function __construct() {
		SpecialPage::__construct("EmptyEmailList", NI.'+', true, 'runEmptyEmailList');
	}

	function execute($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $config;
	    $wgOut->addHTML("<table class='wikitable sortable' bgcolor='#aaaaaa' cellspacing='1' cellpadding='2' style='text-align:center;'>
<tr bgcolor='#F2F2F2'><th>Last Name</th><th>First Name</th><th>Type</th><th>Email</th></tr>");
        foreach(Person::getAllPeople('all') as $person){
            if(($person->getEmail() == "" || $person->getEmail() == "{$config->getValue('supportEmail')}") && 
               ($person->isRole(HQP) || $person->isRole(NI))){
                $names = explode(".", $person->getName());
                $wgOut->addHTML("<tr bgcolor='#FFFFFF'>
                                    <td align='left'>
                                        <a href='{$person->getUrl()}'>{$names[1]}</a>
                                    </td>
                                    <td align='left'>
                                        <a href='{$person->getUrl()}'>{$names[0]}</a>
                                    </td>
                                    <td>{$person->getType()}</td>
                                    <td>{$person->getEmail()}</td>
                                 </tr>");
            }
        }
	    $wgOut->addHTML("</table>");                 
	}
	
	static function createSubTabs(&$tabs){
	    global $wgServer, $wgScriptPath, $wgTitle, $wgUser;
	    $person = Person::newFromWgUser($wgUser);
	    if($person->isRoleAtLeast(MANAGER)){
	        $selected = @($wgTitle->getText() == "EmptyEmailList") ? "selected" : false;
	        $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("Empty Emails", "$wgServer$wgScriptPath/index.php/Special:EmptyEmailList", $selected);
	    }
	    return true;
    }
}
?>
