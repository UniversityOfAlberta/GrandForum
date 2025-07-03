<?php

require_once (dirname ( __FILE__ ) . '/../../maintenance/commandLine.inc');
require_once("WikiDevConfig.php");
require_once("WikiDevFunctions.php");

$sql = "select projectid, mailListName from wikidev_projects";
$dbr = wfGetDB(DB_REPLICA);
$result = $dbr->query($sql);

$mailmanArchivesPaths = array();
while ($row = $dbr->fetchObject($result)) {
	$mailmanArchivesPaths[$row->projectid] = $wdMailmanArchives . "/" . $row->mailListName;
}

$existing = getExistingMIDs();

foreach ($mailmanArchivesPaths as $proj_id => $mailmanArchivesPath) {
	$allMessages = array();
	foreach (glob("$mailmanArchivesPath/*.txt") as $filename) { //TODO is that specific enough?
		$messages = parseMailArchive($filename, $proj_id);
		if (count($messages) > 0) {
			$allMessages[] = $messages;
		}
	}

	if (count($allMessages) == 0) {
		print "No new messages found.\n";
	}

	else {
		$dbw = wfGetDB(DB_MASTER);
		$count = 0;
		foreach ($allMessages as $messages) {
			insertNonPrefix($dbw, 'wikidev_messages', $messages);
			$count += count($messages);
		}
		print "Imported $count new messages.\n";
	}
}

function getExistingMIDs() {
	$dbr = wfGetDB(DB_REPLICA);
	$data = DBFunctions::select(array('wikidev_messages'),
	                            array('mid_header'));
	
	$existing = array();
	foreach($data as $row){
        $existing[$row['mid_header']] = true;
	}
	return $existing;
}

function parseMailArchive($filename, $proj_id) {
	global $existing;
	$text = file_get_contents($filename);
	$pattern = "/From: (.*?) \((.*?)\)\nDate: (.*?)\nSubject: \[.*?\] (.*?)\n.*?(References: (.*?)\n)?Message-ID: <(.*?)>\n\n(.*?)(\n\n(From:.*?)*(From |$))/s";
	preg_match_all($pattern, $text, $matches);
	
	$messages = array();
	$parentMapping = array();
	list($addresses, $names, $dates, $subjects, $refids, $mids, $bodies) = array($matches[1], $matches[2], $matches[3], $matches[4], $matches[6], $matches[7], $matches[8]);
	for ($i = 0; $i < count($mids); $i++) {
	    $subjects[$i] = mb_decode_mimeheader($subjects[$i]);
		if (isset($existing[$mids[$i]])) {
			continue;
		}
		
		$fromAddr = $addresses[$i];
		$fromAddr = str_replace(" at ", "@", $fromAddr);
		$fromAddrA = explode("@", $fromAddr);
		
		$userTable = getTableName("user");
		
		if(isset($fromAddrA[1])){
		    $addr = DBFunctions::escape("{$fromAddrA[0]}%{$fromAddrA[1]}");
		}
		else{
		    $addr = DBFunctions::escape($fromAddrA[0]);
		}
		
		$sql = "SELECT DISTINCT u.user_name as user_name
				FROM $userTable u 
				WHERE LOWER(CONVERT(u.user_email USING latin1)) LIKE '{$addr}'";
				
		$dbr = wfGetDB(DB_REPLICA);
		$result = $dbr->query($sql);
		$data = array();
		$username = "";
		while ($row = $dbr->fetchRow($result)) {
			$data[] = $row;
		}
		
		if(count($data) > 0){
			$username = $data[0]['user_name'];
		}
		else{
		    $name = $names[$i];
		    $explode = explode(",", $names[$i]);
		    if(count($explode) > 1){
		        $name = $explode[1]." ".$explode[0];
		    }
		    $person = Person::newFromName(trim($name));
		    if($person->getName() != ""){
		        $username = $person->getName();
		    }
		}
		
		$refid = $mids[$i];
		
		if (trim($refids[$i]) != "") {
			$curRefids = preg_split("/\s+/", $refids[$i]);
			preg_match("/<(.*)>/", $curRefids[0], $refidMatches);
            if(isset($refidMatches[1])){
                $refid = $refidMatches[1];
            }
			
			//sometimes the first reference is not actually the original message in the thread
			if (isset($parentMapping[$refid])) {
				$refid = $parentMapping[$refid];
			}
			
			$parentMapping[$mids[$i]] = $refid;
		}
		$date = $dates[$i];
		$date = strftime("%Y-%m-%d %H:%M:%S", strtotime($date));
		
		$messages[] = array(
		'project_id' => $proj_id,
		'body' => $bodies[$i],
		'author' => $names[$i], 
		'user_name' => $username, 
		'address' => $fromAddr, 
		'date' => $date, 
		'subject' => $subjects[$i], 
		'mid_header' => $mids[$i], 
		'refid_header' => $refid, 
		);
	}

	return $messages;
}

?>
