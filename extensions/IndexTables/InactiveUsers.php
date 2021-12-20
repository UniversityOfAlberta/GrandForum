<?php

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['InactiveUsers'] = 'InactiveUsers'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['InactiveUsers'] = $dir . 'InactiveUsers.i18n.php';
$wgSpecialPageGroups['InactiveUsers'] = 'other-tools';

function runInactiveUsers($par){
    InactiveUsers::execute($par);
}

class InactiveUsers extends SpecialPage {

    function __construct() {
		SpecialPage::__construct("InactiveUsers", HQP.'+', true, 'runInactiveUsers');
	}

    function execute($par){
		global $wgServer, $wgScriptPath, $wgUser, $wgOut, $config;
		$this->getOutput()->setPageTitle("Inactive Users");
		$text = "";
		$data = Person::getAllPeople(INACTIVE);

        $text .= "Below are all the current ".INACTIVE." users in the {$config->getValue('siteName')}.  To search for someone in particular, use the search box below.  You can search by name, project or university.  Regular Expressions are supported, so a search such as 'PROJ1|PROJ2' will list every member in either PROJ1 or PROJ2.<br />";
		$text .= "<b>Search:</b> <input id='search' type='text' size='50' onKeyUp='filterResults(this.value);' />
<table class='wikitable sortable' bgcolor='#aaaaaa' cellspacing='1' cellpadding='2' style='text-align:center;'>
<tr bgcolor='#F2F2F2'><th>Last Name</th><th>First Name</th><th>Last Role</th></tr>
";
		foreach($data as $person){
		    $roles = $person->getRoles(true);
		    $role = "";
		    if(count($roles) > 0){
		        $role = $roles[count($roles) - 1]->getRole();
		    }
		    $projects = $person->getProjects();
            $projs = array();
            foreach($projects as $project){
                $projs[] = $project->getName();
            }
            $university = $person->getUniversity();
            if(isset($university['university'])){
                $projs[] = $university['university'];
            }
            $names = explode(".", $person->getName());
			$text .= "
<tr name='search' id='".str_replace(".", "_", $person->getName())."' class='".implode(", ", $projs)."' bgcolor='#FFFFFF'>
<td align='left'>
<a href='{$person->getUrl()}'>{$person->getLastName()}</a>
</td>
<td align='left'>
<a href='{$person->getUrl()}'>{$person->getFirstName()}</a>
</td>
<td align='left'>
    {$role}
</td>
</tr>";
		}
		$text .= "</table>";
        $wgOut->addHTML($text);
		return true;
	}
}

?>
