<?php
$notificationFunctions = array();
$notifications = array();

$notificationFunctions[] = 'Notification::generateNotifications';

class Notification{
	
	var $id;
	var $name;
	var $description;
	var $url;
	var $time;
	var $history;
	var $creator;
	
	function __construct($name, $description, $url, $time=null, $history=false){
		$this->name = $name;
		$this->description = $description;
		$this->url = $url;
		$this->time = $time;
		$this->history = $history;
	}
	
	// Creates a new Notification from the given id
	static function newFromId($id){
	    $sql = "SELECT *
                FROM `grand_notifications`
                WHERE `id` = '$id'";
        $data = DBFunctions::execSQL($sql);
        if(count($data) > 0){
            $row = $data[0];
            $history = ($row['active'] == 0);
            $notification = new Notification($row['name'], $row['message'], $row['url'], $row['time'], $history);
            if($row['creator'] != ""){
                $creator = Person::newFromId($row['creator']);
                if($creator != null && $creator->name != ""){
                    $notification->creator = $creator;
                }
            }
            return $notification;
        }
        return null;
	}
	
	static function addNotification($creator, $user, $name, $message, $url, $mail=false){
	    global $wgServer, $wgScriptPath, $wgImpersonating, $config, $wgPasswordSender;
	    if($wgImpersonating){
	        return;
	    }
	    if($wgScriptPath != ""){
	        $mail = false;
	    }
	    $message = str_replace("'", "&#39;", $message);
	    $url = str_replace("'", "&#39;", $url);
	    $name = str_replace("'", "&#39;", $name);
	    $id = "";
	    if($creator != null){
	        $id = $creator->getId();
	    }
	    $sql = "INSERT INTO `grand_notifications` (`creator`,`user_id`,`name`,`message`,`url`,`time`,`active`)
                VALUES('{$id}','{$user->getId()}','$name','{$message}','{$url}',CURRENT_TIMESTAMP,'1')";
        if($mail){
            if($id == 0){
                $from = "From: {$config->getValue('siteName')} <{$wgPasswordSender}>" . "\r\n";
            }
            else{
                $from = "From: {$creator->getNameForForms()} <{$creator->getEmail()}>" . "\r\n";
            }
            $headers = "Content-type: text/html\r\n"; 
            $headers .= $from;
            $wUser = User::newFromId($user->getId());
            mail($wUser->getEmail(), $name, nl2br($message)."<br /><br /><a href='$url'>Notification URL</a><br /><br /><a href='{$wgServer}{$wgScriptPath}'>{$config->getValue('siteName')}</a>", $headers);
        }
        DBFunctions::execSQL($sql, true);
	}
	
	static function deactivateNotification($id){
	    global $wgUser;
	    $sql = "UPDATE `grand_notifications` 
	            SET `active` = '0'
	            WHERE `id` = '$id'";
        DBFunctions::execSQL($sql, true);
	}
	
	static function generateNotifications($history=false){
	    global $notifications, $wgUser, $wgServer, $wgScriptPath, $wgArticle, $wgTitle, $wgImpersonating;
	    if($wgUser->getId() == 0){
	        return false;
	    }
        $me = Person::newFromId($wgUser->getId());
        $sql = "SELECT *
                FROM `grand_notifications`
                WHERE `user_id` = '{$me->getId()}'\n";
        if(!$history){
            $sql .= "AND `active` = '1'\n";
        }
        $sql .= "ORDER BY time DESC";
        $data = DBFunctions::execSQL($sql);
        if(DBFunctions::getNRows() > 0){
            foreach($data as $row){
                $url = $row['url'];
                if(!$wgImpersonating && (strstr($url, str_replace(" ", "_", $_SERVER['REQUEST_URI'])) !== false || strstr($url, $_SERVER['REQUEST_URI']) !== false) || 
                   isset($_POST['markAllNotificationsAsRead'])){
                    Notification::deactivateNotification($row['id']);
                }
                else{
                    $history = false;
                    if($row['active'] != 1){
                        $history = true;
                    }
                    $notification = self::newFromId($row['id']);
                    if($notification != null){
                        $notifications[] = $notification;
                    }
                    //$notifications[] = new Notification($row['name'], "{$row['message']}", $row['url'], $row['time'], $history);
		        }
            }
        }
    }
	
	static function createTable(){
		global $wgUser, $wgOut, $notifications, $notificationFunctions, $wgServer, $wgScriptPath, $config;
		$me = Person::newFromId($wgUser->getId());
		if($me == null || $me->getName() == ""){
		    permissionError();
		}
		$wgOut->setPageTitle("My Notifications");
		$history = false;
		if(isset($_GET['history']) && $_GET['history'] == true){
		    $history = true;
		}
		if(!$history && count($notifications) == 0){
			foreach($notificationFunctions as $function){
				call_user_func($function);
			}
		}
		else if($history){
		    self::generateNotifications(true);
		}
		if(count($notifications) == 0){
			$wgOut->addHTML("You have no new notifications.<br /><br />");
		}
		if($history){
	        $wgOut->addHTML("<a href='$wgServer$wgScriptPath/index.php?action=viewNotifications'>View New Notifications</a><br /><br />");
	    }
	    else{
	        $wgOut->addHTML("<a href='$wgServer$wgScriptPath/index.php?action=viewNotifications&history=true'>View History</a><br />");
	        if($me->isRoleAtLeast(STAFF)){
	            $wgOut->addHTML("<a href='$wgServer$wgScriptPath/index.php/Special:AddMember?action=view&history=true'>View Add Member History</a><br />");
	        }
	        $wgOut->addHTML("<br />");
	    }
        $wgOut->addHTML("<table class='dataTable' frame='box' rules='all'>
			        <thead><tr>
			            <th>Name</th> <th>Created By</th> <th>Description</th> <th>Timestamp</th>
		            </tr></thead><tbody>");
        foreach($notifications as $notification){
            $wgOut->addHTML("<tr>");
            if($notification->url != ""){
	            $wgOut->addHTML("<td><a href='{$notification->url}'>{$notification->name}</a></td> <td>");
	        }
	        else{
	            $wgOut->addHTML("<td>{$notification->name}</td> <td>");
	        }
            if($notification->creator != null && $notification->creator != ""){
                $wgOut->addHTML("<a href='{$notification->creator->getUrl()}'>{$notification->creator->getReversedName()}</a>");
            }
            else{
                $wgOut->addHTML($config->getValue('siteName'));
            }
            $wgOut->addHTML("</td> <td>{$notification->description}</td> <td>{$notification->time}</td>
		            </tr>");
        }
        $wgOut->addHTML("</tbody></table><script type='text/javascript'>$('.dataTable').dataTable({'iDisplayLength': 100, 
                                                                                                   'aaSorting': [ [3,'desc']],
                                                                                                   'autoWidth': false
                                                                                                  });</script>");
        if(!$history){
            $wgOut->addHTML("<br /><form action='' method='post'><input type='submit' name='markAllNotificationsAsRead' value='Mark All As Read' /></form>");
        }
	}
	
	static function createTab(){
		global $notifications, $wgServer, $wgScriptPath;
		$count = 0;
		foreach($notifications as $notification){
		    if(!$notification->history){
		        $count++;
		    }
		}
		$selected = "";
		if(isset($_GET['action']) && $_GET['action'] == "viewNotifications"){
		    $selected = "selected";
		}
		if($count > 0){
		    echo "<li class='top-nav-element $selected'>\n";
		    echo "	<span class='top-nav-left'>&nbsp;</span>\n";
		    echo "	<a id='lnk-notifications' class='top-nav-mid' href='$wgServer$wgScriptPath/index.php?action=viewNotifications' class='new'>My&nbsp;Notifications&nbsp;($count)</a>\n";
		    echo "	<span class='top-nav-right'>&nbsp;</span>\n";
		}
		else{
		    echo "<li class='top-nav-element $selected'>\n";
		    echo "	<span class='top-nav-left'>&nbsp;</span>\n";
		    echo "	<a id='lnk-notifications' class='top-nav-mid' href='$wgServer$wgScriptPath/index.php?action=viewNotifications' class='new'>My&nbsp;Notifications</a>\n";
		    echo "	<span class='top-nav-right'>&nbsp;</span>\n";
		}
	}
}


?>
