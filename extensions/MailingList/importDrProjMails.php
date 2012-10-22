<?php
require_once("common.php");

updateUnassigned();
incrementalUpdate();

function updateUnassigned() {
	$mapping = getAddressMapping();
	
	$sql = "SELECT address FROM wikidev_messages WHERE user_name IS NULL";
	$dbr = wfGetDB(DB_READ);
	$result = $dbr->query($sql);
	$updatedEmails = array();
	while ($row = $dbr->fetchObject($result)) {
		if (isset($mapping[$row->address])) {
			$updatedEmails[$row->address] = $row->address;
		}
	}
	
	if (count($updatedEmails) > 0) {
		$dbw = wfGetDB(DB_MASTER);
		foreach ($updatedEmails as $newEmail) {
			$username = $mapping[$newEmail];
			$sql = "UPDATE wikidev_messages SET user_name = '$username' WHERE address = '$newEmail'";
			$dbw->query($sql);
			recreateThreads($newEmail);
		}
	}
}
function buildPages() {
	$dbr = wfGetDB(DB_READ);
	$sql = "SELECT DISTINCT subject, project_id
		FROM wikidev_messages";
		
	$pages = array();
	$extraNS = array();
	$admin = User::newFromId(1);
		
	$threads = $dbr->query($sql);
	$curLast = "";
	while($thread = $dbr->fetchRow($threads)){
		$subject = $thread['subject'];
		$sql = "SELECT m.*, p.projectname
			FROM wikidev_messages m, wikidev_projects p
			WHERE m.project_id = p.projectid 
			AND m.subject = '".addslashes($subject)."'
			ORDER BY date ASC";
		$result = $dbr->query($sql);
		$emails = array();
		while($row = $dbr->fetchRow($result)){
			$emails[] = $row;
		}
		$curLast = "";
		if(count($emails) > 0){
		    $projectNames = array();
		    foreach($emails as $email){
		        $projectNames[$email['projectname']] = $email['projectname'];
		    }
			$id = $emails[0]['id'];
			$projectName = $emails[0]['projectname'];
			$pages2 = array();
			foreach($projectNames as $name){
			    $pages2[$name] = new DrProjPage($id, ''.$name, 'MAIL_'.$subject);
			}
			
			$text = "";
			foreach($emails as $email){
				$author = $email['author'];
				$address = $email['address'];
				$date = $email['date'];
				$body = fixQuotes($email['body']);
				$text .= "\n== From: $author <$address> ($date) ==\n<pre>$body</pre>\n";
				
				$userName = $email['user_name'];
				if ($userName === null) {
					$userName = $admin->getName();
				}
				foreach($pages2 as $page){
				    $rev = new DrProjRevision($id, null, $date, $userName, "", $text, null, false);
				    $page->revisions[] = $rev;
				    $rev->page = $page;
				}
				$curLast = $date;
			}
			foreach($pages2 as $ns => $page){
			    $key = $page->getKey();
			    $pages[$key] = $page;
			    $extraNS[$ns] = true;
			}
		}
	}
	return array($pages, $extraNS, $curLast);
}

function incrementalUpdate() {
	list($pages, $extraNS, $curLast) = buildPages();
	$dbw = wfGetDB(DB_MASTER);
	
	$lastTime = getLastTs('mail');
	
	foreach ($pages as $pageKey => $page) {
		foreach ($page->revisions as $key => $rev) {
			if ($rev->time <= $lastTime) {
				unset($page->revisions[$key]);
			}
		}

		if (count($page->revisions) == 0) {
			unset($pages[$pageKey]);
		}
	}

	if (count($pages) == 0) {
		//print "No new e-mails have been found.\n";
		return;
	}
	
	createNamespaces($extraNS);
	importMWPages($pages, $dbw);
	$sql = "INSERT INTO wikidev_importlog VALUES('mail', '$curLast')";
	$dbw->query($sql);
}

function recreateThreads($emailAddr) {
	$dbw = wfGetDB(DB_MASTER);
	
	list($pages, $extraNS, $curLast) = buildPages();
	$sql = "SELECT m.subject, p.projectname
		FROM wikidev_messages m, wikidev_projects p
		WHERE m.project_id = p.projectid 
		AND m.address = '$emailAddr'";
	$dbr = wfGetDB(DB_READ);
	$result = $dbr->query($sql);
	$toRecreate = array();
	while ($row = $dbr->fetchObject($result)) {
		$pageName = "".$row->projectname.":MAIL_$row->subject";
		//print "Recreating $pageName because of address: $emailAddr\n";
		$title = Title::newFromText($pageName);
		$article = new Article($title);
		if (!$article->exists()) {
			wikidevErrorLog("Error while trying to re-create $pageName: article does not exist. (E-mail address: $emailAddr)");
			continue;
		}
		
		$article->doDelete("Re-creating due to change of address");
		$toRecreate[$pageName] = true;
	}
	
	foreach ($pages as $pageKey => $page) {
		if (!isset($toRecreate[$pageKey])) {
			unset($pages[$pageKey]);
		}	
	}
	
	importMWPages($pages, $dbw);	
}

function fixQuotes($body) {
	/*
	 * 1. if the last line starts with '>' - remove it and keep removing until a line that does not start with >
	 * 2. remove the first nonblank line above the last line starting with >
	 * 3. if the last line does not start with > (could be a signature) then start at line last-2 and remove all lines starting with >
	 * 4. again remove the first nonbalnk after the >'s
	 * 5. this time though keep the last two lines that were skipped
	 */
	
	$bodyLines = explode("\n", $body);
	$bodyLines = array_reverse($bodyLines);
	
	foreach ($bodyLines as $key => $bodyLine) {
		$bodyLine = trim($bodyLine);
		if ($bodyLine === "")
			unset($bodyLines[$key]);
		else 
			break; //$bodyLines[$key] = $bodyLine; //BT: Changed to only remove blank lines at end of message and not trim lines.
	}
	
	//p_var($bodyLines);
	
	$lastLine = array_shift($bodyLines);
	if ($lastLine[0] == '>')
		$bodyLines = removeQuotedText($bodyLines);
	else {
		$lastTwoLines = array();
		$lastTwoLines[] = $lastLine; //[0] = last line
		$lastTwoLines[] = array_shift($bodyLines); //[1] = second last line
		$bodyLines = removeQuotedText($bodyLines);
		$bodyLines = array_merge($lastTwoLines, $bodyLines);
	}
	
	$bodyLines = array_reverse($bodyLines);
	$body = join("\n", $bodyLines);
	
	return $body;
}

function removeQuotedText($body) {
	$hasQuotesAtEnd = false;
	foreach ($body as $key => $line) {
		$line = trim($line);
		
		if ($line === "")
			continue;
	
		if ($line[0] == '>') {
			unset($body[$key]);
			$hasQuotesAtEnd = true;
		}
		else 
			break;
	}
	
	//remove first line before start of quoted text (which is something like "On X, Y wrote:" 
	if ($hasQuotesAtEnd) { 
		foreach ( $body as $key => $line ) {
			$line = trim($line);
			if ($line === "")
				continue;
			unset($body[$key]);
			break;
		}
	}
	
	return $body;
}

?>
