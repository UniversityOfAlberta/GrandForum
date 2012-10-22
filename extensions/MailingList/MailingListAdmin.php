<?php

$MailingListAdmin = new MailingListAdmin();

$notificationFunctions[] = 'MailingListAdmin::createNotification';

class MailingListAdmin {

	static function createNotification(){
		global $notifications, $wgUser, $wgServer, $wgScriptPath;
		$groups = $wgUser->getGroups();
		if(array_search("sysop", $groups) !== false){
			$sql = "SELECT requesting_user, project
				FROM mw_list_request
				WHERE `created` = 'false'
				AND `ignore` = 'false'";
			$rows = DBFunctions::execSQL($sql);
			
			if(count($rows) > 0){
				$notifications[] = new Notification("Mailing List Request", "There is at least one mailing list request pending.", "$wgServer$wgScriptPath/index.php/Special:MailingListRequest?action=view");
			}
		}
	}
}
?>
