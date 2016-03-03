<?php

$approvePageAdmin = new ApprovePageAdmin();

$notificationFunctions[] = 'ApprovePageAdmin::createNotification';

class ApprovePageAdmin {

        static function createNotification(){
                global $notifications, $wgUser, $wgServer, $wgScriptPath;
                $me = Person::newFromId($wgUser->getId());
                if($me->isRoleAtLeast(STAFF)){
			$rows = Wiki::getAllUnapprovedPages();
                        if(count($rows) > 0){
                                $notifications[] = new Notification("Page Approval Pending", "There is at least one page requiring approval.", "$wgServer$wgScriptPath/index.php/Special:ApprovePage?action=view");
                        }
                }
        }
}
?>
