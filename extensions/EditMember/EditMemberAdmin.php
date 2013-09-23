<?php

$editMemberAdmin = new EditMemberAdmin();

$notificationFunctions[] = 'EditMemberAdmin::createNotification';

class EditMemberAdmin {

	static function createNotification(){
		global $notifications, $wgUser, $wgServer, $wgScriptPath;
		$me = Person::newFromId($wgUser->getId());
		if($me->isRoleAtLeast(STAFF)){
		    $rows = DBFunctions::select(array('grand_role_request'),
		                                array('requesting_user', 'role'),
		                                array('created' => EQ(0),
		                                      '`ignore`' => EQ(0)));
			if(count($rows) > 0){
				$notifications[] = new Notification("User Role Request", "There is at least one user role request pending.", "$wgServer$wgScriptPath/index.php/Special:EditMember?action=view");
			}
		}
	}
}
?>
