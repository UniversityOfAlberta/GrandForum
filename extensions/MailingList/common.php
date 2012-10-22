<?php
require_once (dirname ( __FILE__ ) . '/../../maintenance/commandLine.inc');
$wgTitle = Title::newFromID(1);
$wgUser = User::newFromId(1);

require_once("WikiDevConfig.php");
require_once("WikiDevFunctions.php");

class DrProjPage {
	const WIKI = 'wiki';
	const TICKET = 'ticket';
	const FILE = 'file';
	const CHANGESET = 'changeset';
	const MAIL = 'mail';
	
	var $page_id; //dr_id
	var $proj; //project_name
	var $title; //name
	var $revisions = array(); //array of DrProjRevision
	var $type;
	
	/**
	 * Constructs a new page that can be imported
	 *
	 * @param int $page_id original dr proj id (can be null)
	 * @param string $proj the namespace (usually project + optionally subnamespace such as ticket/file/etc.)
	 * @param string $title title of the page; the combination $proj/$title must be unique 
	 * @return DrProjPage
	 */
	function DrProjPage($page_id, $proj, $title, $type = null) {
		$this->page_id = $page_id;
		$this->proj = $proj;
		$this->title = $title;
		$this->type = $type;
	}

	function getKey() {
		return $this->proj . ":" . $this->title;
	}
	
	function isOnlyNobody() {
		foreach ($this->revisions as $revision) {
			if ($revision->author != 'nobody') {
				return false;
			}
		}
		
		return true;
	}
}

class DrProjRevision {
	var $revId; //id
	var $pageId;
	var $time;
	var $author;
	var $comment;
	var $text;
	var $page; /* DrProjPage */

	/**
	 * Constructs a new revision for a page that will be imported
	 *
	 * @param int $revId the dr project id for the revision (can be null)
	 * @param int $pageId the dr project page id corresponding to the revision (can be null)
	 * @param string $time - the time of the revision
	 * @param string $author - the author (will be created if does not exist)
	 * @param string $comment - comment for the revision (optional) 
	 * @param string $text - contents of the revision
	 * @param DrProjPage $page - the page that owns the revision (can be null if not converting the text)
	 * @param boolean $convert whether to run the text through the wiki markup converter
	 * @return DrProjRevision
	 */
	function DrProjRevision($revId, $pageId, $time, $author, $comment, $text, $page, $convert = true) {
		$this->revId = $revId;
		$this->pageId = $pageId;
		$this->time = $time;
		$this->author = $author;
		$this->comment = $comment;
		$this->page = $page;
		$this->text = $text;
		
		if ($convert) {
			$this->text = $this->convWikiText($this->text, $this->page->proj, $page->type, $this->pageId);
		}
	}

	function convWikiText($text, $nsForLinks, $type, $dr_id) {
		$cached = checkCache($text);
		if ($cached !== null) {
			return $cached;
		}
		
		$descriptorspec = array(
		0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
		1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
		2 =>  array("pipe", "w") // stderr is a pipe that the child will write to
		);

		$pipes = array();
		global $wgServer, $wgScriptDir;
		
		$cmd = "./Markdown_mw.pl  $nsForLinks $type $dr_id";
		
		$process = proc_open($cmd, $descriptorspec, $pipes);

		fwrite($pipes[0], $text);
		fclose($pipes[0]);

		$result =  stream_get_contents($pipes[1]);
		fclose($pipes[1]);
		proc_close($process);
		insertCacheEntry($text, $result);
		return $result;
	}
}

function tz_conv($ts, $fromTZ, $toTZ, $format = "%Y-%m-%d %H:%M:%S") {
	$old_tz = getenv('TZ');
	putenv("TZ=$fromTZ");
	$newTS = strtotime($ts);
	putenv("TZ=$toTZ");
	$newTS = strftime($format, $newTS);

	putenv("TZ=$old_tz");
	return $newTS;
}

function importMWPages($pages, $dbw) {
	global $wgUser;
	
	$counter = 0;
	foreach ($pages as $page) {
		$counter++;
		if ($page->isOnlyNobody()) {
			continue;	
		}
		
		$key = $page->getKey();
		$key = str_replace("[", "(", $key);
		$key = str_replace("]", ")", $key);
		$key = str_replace("<", "(", $key);
		$key = str_replace(">", ")", $key);
		
		$title = Title::newFromText($key);
		if ($title === null) {
			print "bad title ($key), skipping\n";
			continue;
		}
		$article = new Article($title);
		
		foreach ($page->revisions as $rev) {		
			$author = User::getCanonicalName($rev->author);
			$user = User::newFromName($author);
			if ($user == null || $user->isAnon()) {
				$user = User::newFromId(4);
			}
			$wgUser = $user;
			
			$article->doEdit($rev->text, $rev->comment);

			$timeStr = $rev->time;
			
			if (!isset($rev->keepOrigTime) || $rev->keepOrigTime === false) {
				$ts = tz_conv($timeStr, "MDT", "UTC", "%Y%m%d%H%M%S");
			}
			else {
				$ts = date("YmdHis", strtotime($timeStr));
			}
						
			$article = new Article($title);
			$mwRevId = $article->getLatest();
			if (method_exists($rev, 'setMWRev')) {
				$rev->setMWRev($mwRevId);
			}

		/*	if ($page->type != null && $rev->revId != null) {
				$revMapping = array('type' => $page->type, 'drproj_id' => $rev->revId, 'mw_id' => $mwRevId);
				$dbw->insert('revmapping', $revMapping);
			}*/
			;
			$dbw->update( 'revision',
			array(  /*SET*/
						'rev_timestamp' => $ts,
			), array(  /*WHERE*/
						'rev_id' => $mwRevId
			)
			);
		}
		
		
		if ($counter %100 == 0)
			printf("%d%%\n", (($counter / count($pages)) * 100));
		//printf("\r%d%%", (($counter / count($pages)) * 100)); 
		
	}
	print "\n";
}

function checkCache($originalText) {
	$md5 = md5($originalText);
	$filename = "cache/$md5";
	if (file_exists($filename)) {
		return file_get_contents($filename);
	}
	else {
		return null;
	}
}

function insertCacheEntry($originalText, $convertedText) {
	$md5 = md5($originalText);
	$filename = "cache/$md5";
	file_put_contents($filename, $convertedText);
}

function getLastTs($type) {
	$validTypes = array('file', 'mail', 'irc');
	if (!in_array($type, $validTypes)) {
		print "invalid type ($type)!\n";
		die;
	}
	
	$sql = "SELECT MAX(time) as lastTime FROM wikidev_importlog WHERE type = '$type'";
	$dbr = wfGetDB(DB_READ);
	$result = $dbr->query($sql);
	
	$row = $dbr->fetchObject($result);
	
	if (!$row || $row->lastTime == null) {
		return 0; //no updates yet
	}
	
	return $row->lastTime;	
}
?>
