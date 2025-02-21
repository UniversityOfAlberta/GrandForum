<?php

require_once("Notification.php");

UnknownAction::createAction('viewNotifications');

function viewNotifications($action, $request){
	if($action == "viewNotifications"){
		Notification::createTable();
		return false;
	}
	return true;
}

?>
