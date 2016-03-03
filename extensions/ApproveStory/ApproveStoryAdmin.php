<?php

$approvePageAdmin = new ApproveStoryAdmin();

$notificationFunctions[] = 'ApproveStoryAdmin::createNotification';

class ApproveStoryAdmin {

        static function createNotification(){
                global $notifications, $wgUser, $wgServer, $wgScriptPath;
                $me = Person::newFromId($wgUser->getId());
                if($me->isRoleAtLeast(STAFF)){
			$rows = Story::getAllUnapprovedStories();
                        if(count($rows) > 0){
                                $notifications[] = new Notification("Story Approval Pending", "There is at least one story requiring approval.", "$wgServer$wgScriptPath/index.php/Special:ApproveStory?action=view");
                        }
                }
        }
}
?>
