<?php

$editMemberAdmin = new EditMemberAdmin();

$notificationFunctions[] = 'EditMemberAdmin::createNotification';

class EditMemberAdmin {

	static function createNotification(){
		global $notifications, $wgUser, $wgServer, $wgScriptPath;
		$me = Person::newFromId($wgUser->getId());
		if($me->isRoleAtLeast(STAFF)){
			$sql = "SELECT requesting_user, role
				FROM grand_role_request
				WHERE `created` = 'false'
				AND `ignore` = 'false'";
            $rows = DBFunctions::execSQL($sql);
			if(count($rows) > 0){
				$notifications[] = new Notification("User Role Request", "There is at least one user role request pending.", "$wgServer$wgScriptPath/index.php/Special:EditMember?action=view");
			}
		}
	}
}
?>
