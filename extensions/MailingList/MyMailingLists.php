<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['MyMailingLists'] = 'MyMailingLists'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['MyMailingLists'] = $dir . 'MyMailingLists.i18n.php';
$wgSpecialPageGroups['MyMailingLists'] = 'other-tools';

function runMyMailingLists($par) {
  MyMailingLists::run($par);
}

class MyMailingLists extends SpecialPage{

	function MyMailingLists() {
		wfLoadExtensionMessages('MyMailingLists');
		SpecialPage::SpecialPage("MyMailingLists", HQP.'+', true, 'runMyMailingLists');
	}

	function run($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
	    $person = Person::newFromId($wgUser->getId());
	    if($person->isProjectLeader() || $person->isProjectCoLeader()){
	        $wgOut->addHTML("<a href='$wgServer$wgScriptPath/index.php/Special:MailingListRequest'>Subscribe/Unsubscribe Users</a><br />");
	    }
	    if($person->isRoleAtLeast(MANAGER)){
	        $projects = Project::getAllProjects();
	    }
	    else{
	        $projects = $person->getProjects();
	    }
	    $count = 0;
        $wgOut->addHTML("<ul>\n");
        if($person->isRole(HQP) || $person->isRoleAtLeast(MANAGER)){
	        $wgOut->addHTML("<li><a href='$wgServer$wgScriptPath/index.php/HQP:Mail_Index'>HQP Archives</a></li>");
	    }
	    if($person->isRole(CNI)){
	        $wgOut->addHTML("<li><a href='$wgServer$wgScriptPath/index.php/CNI:Mail_Index'>Researcher Archives</a></li>");
	    }
	    if($person->isRole(PNI) || $person->isRoleAtLeast(MANAGER)){
	        $wgOut->addHTML("<li><a href='$wgServer$wgScriptPath/index.php/PNI:Mail_Index'>Researcher Archives</a></li>");
	    }
	    foreach($projects as $project){
	        $wgOut->addHTML("<li><a href='$wgServer$wgScriptPath/index.php/{$project->getName()}:Mail_Index'>{$project->getName()} Archives</a></li>");
	    }
	    $wgOut->addHTML("</ul>");
	}
	
	static function createTab() {
		global $wgServer, $wgScriptPath;
		echo <<<EOM
<li class='top-nav-element'><span class='top-nav-left'>&nbsp;</span>
<a class='top-nav-mid' href='$wgServer$wgScriptPath/index.php/Special:MyMailingLists' class='new'>My Mailing Lists</a>
<span class='top-nav-right'>&nbsp;</span></li>
EOM;
	}
}
?>
