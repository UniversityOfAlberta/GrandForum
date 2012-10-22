<?php

require_once("Notification.php");

$wgHooks['UnknownAction'][] = 'viewNotifications';

function viewNotifications($action, $request){
	if($action == "viewNotifications"){
		Notification::createTable();
		return false;
	}
	return true;
}

?>
