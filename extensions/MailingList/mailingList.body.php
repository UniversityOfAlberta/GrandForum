<?php

require_once("MyMailingLists.php");
require_once("MailingListAdmin.php");
require_once("MailingListRequest.php");

global $wgArticle;
$mailList = new MailList();
$wgHooks['ArticleViewHeader'][] = array($mailList, 'createMailList');
$wgHooks['ArticleViewHeader'][] = array($mailList, 'createMailListTable');

class MailList{

	function createMailList($action, $article){
		global $wgTitle, $IP;
		if($wgTitle->getText() == "Mail Index"){
			exec("php $IP/extensions/MailingList/importMailinglist.php &> /dev/null &");
			exec("php $IP/extensions/MailingList/importDrProjMails.php &> /dev/null &");
		}
		return true;
	}
	
	function createMailListTable($action, $article){
		global $wgOut, $wgTitle, $wgScriptPath, $wgServer, $wgUser;
		if($wgUser->isLoggedIn()){
		    if($wgTitle->getText() == "Mail Index"){
		        $me = Person::newFromId($wgUser->getId());
			    $project_name = $wgTitle->getNsText();
			    $project = Project::newFromName($project_name);
			    $sql = "SELECT * 
				    FROM wikidev_projects p
				    WHERE p.projectname = '$project_name'";
			    $data = DBFunctions::execSQL($sql);
			    if(DBFunctions::getNRows() > 0 && (($project != null && $project->getName() != "" && $me->isMemberOf($project)) || ($me->isRole($project_name) || $me->isRoleAtLeast(STAFF)))){
				    $wgOut->addHTML("<b>Mail List Address:</b> <a href='mailto:{$data[0]['mailListName']}@forum.grand-nce.ca'>{$data[0]['mailListName']}@forum.grand-nce.ca</a>");
			    }
			    else{
			        $wgOut->setPageTitle("Permission error");
			        $wgOut->addHTML("<p>You are not allowed to execute the action you have requested.</p>
                                     <p>Return to <a href='$wgServer$wgScriptPath/index.php/Main Page'>Main Page</a>.</p>");
			        $wgOut->output();
			        $wgOut->disable();
			    }
			    $wgOut->addHTML("<h2>$project_name Mail List Archive</h2>");
			    $sql = "SELECT m.subject as subject, MIN(date) as first_date, MAX(date) as last_date
				    FROM wikidev_projects p, wikidev_messages m
				    WHERE m.project_id = p.projectid
				    AND p.projectname = '$project_name'
				    GROUP BY m.subject
				    ORDER BY first_date DESC";
				
			    $data = DBFunctions::execSQL($sql);	
			    if(DBFunctions::getNRows() > 0){
				    $wgOut->addHTML("<br /><table class='wikitable sortable' bgcolor='#aaaaaa' cellspacing='1' cellpadding='2'>
						    <tr>
							    <th style='white-space:nowrap;' bgcolor='#F2F2F2'>First Message</th><th style='white-space:nowrap;' bgcolor='#F2F2F2'>Last Message</th><th style='white-space:nowrap;' bgcolor='#F2F2F2'>Messages</th><th style='white-space:nowrap;' bgcolor='#F2F2F2'>Subject</th><th style='white-space:nowrap;' bgcolor='#F2F2F2'>People</th>
						    </tr>");
				    $userTable = getTableName("user");
				    $pageTable = getTableName("page");
				    $revTable = getTableName("revision");
			
				    foreach($data as $row){
			
					    $sql = "SELECT m.user_name
							    FROM wikidev_messages m 
							    WHERE m.subject = '".addslashes($row['subject'])."'";
					    $data2 = DBFunctions::execSQL($sql);
					    $users = "";
					    
					    foreach($data2 as $row2){
					        $person = Person::newFromName($row2['user_name']);
						    $users .= ", {$person->getNameForForms()}";
					    }
					    if(count($users) > 0){
						    $users[0] = "";
					    }
					    $sql = "SELECT MAX(r.rev_id) as maxRev, MIN(r.rev_id) as minRev, COUNT(*) as messages, p.page_title
						    FROM $pageTable p, $revTable r
						    WHERE p.page_id = r.rev_page
						    AND LOWER(CONVERT(p.page_title USING latin1)) LIKE REPLACE('MAIL_".addslashes($row['subject'])."%', ' ', '_')
						    GROUP BY p.page_title";
					    $data2 = DBFunctions::execSQL($sql);
					    foreach($data2 as $row2){
						    $page_title = $row2['page_title'];
						    $first_oldid = $row2['minRev'];
						    $last_oldid = $row2['maxRev'];
						    $wgOut->addHTML("<tr>
								    <td bgcolor='#FFFFFF' style='white-space:nowrap;'>{$row['first_date']}</td><td style='white-space:nowrap;' bgcolor='#FFFFFF'>{$row['last_date']}</td><td bgcolor='#FFFFFF' align='right'> {$row2['messages']} </td><td bgcolor='#FFFFFF'> <a href='$wgScriptPath/index.php/{$project_name}:".str_replace("'", "%27", $page_title)."'>{$row['subject']}</a></td><td bgcolor='#FFFFFF'> $users </td>
							    </tr>");
					    }
				    }
				    $wgOut->addHTML("</table>");
			    }
			    else {
				    $wgOut->addHTML("There have been no messages sent");
			    }
			    $wgOut->setPageTitle($wgTitle->getNSText()." Mailing List Archives");
			    $wgOut->output();
				$wgOut->disable();
			    return false;
		    }
		    else if(strpos($wgTitle->getText(), "MAIL") === 0){
		        $me = Person::newFromId($wgUser->getId());
			    $project_name = $wgTitle->getNsText();
			    $project = Project::newFromName($project_name);
			    $sql = "SELECT * 
				    FROM wikidev_projects p
				    WHERE p.projectname = '$project_name'";
			    $data = DBFunctions::execSQL($sql);
			    if(!(DBFunctions::getNRows() > 0 && (($project != null && $project->getName() != "" && $me->isMemberOf($project)) || ($me->isRole($project_name) || $me->isRoleAtLeast(STAFF))))){
			        $wgOut->setPageTitle("Permission error");
			        $wgOut->addHTML("<p>You are not allowed to execute the action you have requested.</p>
                                     <p>Return to <a href='$wgServer$wgScriptPath/index.php/Main Page'>Main Page</a>.</p>");
			        $wgOut->output();
			        $wgOut->disable();
			    }
		    }
		    return true;
		}
		else if(strpos($wgTitle->getText(), "MAIL") === 0){
		    $wgOut->setPageTitle("Permission error");
	        $wgOut->addHTML("<p>You are not allowed to execute the action you have requested.</p>
                             <p>Return to <a href='$wgServer$wgScriptPath/index.php/Main Page'>Main Page</a>.</p>");
	        $wgOut->output();
	        $wgOut->disable();
		    return true;
		}
		return false;
	}
}

?>
