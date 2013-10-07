<?php

$MailingListAdmin = new MailingListAdmin();

$notificationFunctions[] = 'MailingListAdmin::createNotification';

class MailingListAdmin {

    static function createNotification(){
        global $notifications, $wgUser, $wgServer, $wgScriptPath;
        $groups = $wgUser->getGroups();
        if(array_search("sysop", $groups) !== false){
            $rows = DBFunctions::select(array('grand_list_request'),
                                        array('requesting_user', 'project'),
                                        array('created' => EQ(0),
                                              '`ignore`' => EQ(0)));
            if(count($rows) > 0){
                $notifications[] = new Notification("Mailing List Request", "There is at least one mailing list request pending.", "$wgServer$wgScriptPath/index.php/Special:MailingListRequest?action=view");
            }
        }
    }
}
?>
