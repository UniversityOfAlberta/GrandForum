<?php

use MediaWiki\MediaWikiServices;

$editPermissions = new EditPermissions();

$wgHooks['EditPage::showEditForm:initial'][] = array($editPermissions, 'clearEditForm');

class EditPermissions{

	function clearEditForm($editPage){
		global $wgOut, $wgTitle, $wgUser;
		$groups = MediaWikiServices::getInstance()->getUserGroupManager()->getUserGroups($wgUser);
		if($wgTitle->getNsText() == "Template" && array_search("sysop", $groups) === false){
			$wgOut->clearHTML();
			$wgOut->setPageTitle("Editing Permissions Error");
			$wgOut->addHTML("You must be a sysop to edit this page");
			$wgOut->output();
			$wgOut->disable();
		}
		return true;
	}
}

?>
