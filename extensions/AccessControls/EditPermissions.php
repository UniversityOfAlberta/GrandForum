<?php

$wgHooks['EditPage::showEditForm:initial'][] = 'EditPermissions::clearEditForm';

class EditPermissions{

	static function clearEditForm($editPage){
		global $wgOut, $wgTitle, $wgUser;
		$groups = $wgUser->getGroups();
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
