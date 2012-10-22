<?php

function p_var($v, $pref = "", $die = true) {
	print "<pre>";
	$call = array_shift(debug_backtrace());
	$file = $call['file'];
	$line = $call['line'];
	
	print "$file:$line\n";
	
	if (is_array($v) || is_object($v)) {
		if ($pref !== "") {
			print "$pref: ";
		}
		print_r($v);
		print "\n";
	}
	else {
		if ($pref !== "") {
			print "$pref: ";
		}
		print "$v\n";
	}
	
	if ($die)
		die;
}

function getEarliestActivityTime(){
    //Ignores wikidev_importlog
    $query = 'SELECT min(time) as time FROM (
SELECT min(timestamp) as time from wikidev_changesets UNION
SELECT min(date) as time from wikidev_messages UNION
SELECT min(change_time) as time from wikidev_ticketchanges UNION
SELECT min(created) as time from wikidev_tickets) times';

    $res = AnnokiDatabaseFunctions::getQueryResultsAsArray($query, 'time');
    
    if (array_key_exists(0, $res))
	return $res[0];
    return null;
}

function getLatestActivityTime(){
    $query = 'SELECT max(time) as time FROM (
SELECT max(timestamp) as time from wikidev_changesets UNION
SELECT max(date) as time from wikidev_messages UNION
SELECT max(change_time) as time from wikidev_ticketchanges UNION
SELECT max(last_modified) as time from wikidev_tickets) times';

    $res = AnnokiDatabaseFunctions::getQueryResultsAsArray($query, 'time');
    
    if (array_key_exists(0, $res))
	return $res[0];
    return null;
}

function getUsersByProject(){
    $query = 'SELECT p.projectname as project, pr.userid as user
FROM wikidev_projects p LEFT JOIN wikidev_projectroles pr
on pr.projectid=p.projectid';

    $results = AnnokiDatabaseFunctions::getQueryResultsAsArray($query);

    $projectUsers = array();
    foreach ($results as $res){
	if ($res['user'] == null) //Will only happen if there are no members of team
	    $projectUsers[$res['project']] = array();
	else
	    $projectUsers[$res['project']][] = $res['user'];
    }

    return $projectUsers;
}

function getUsersInProjectName($projectName){
    $query = 'SELECT pr.userid as user FROM wikidev_projectroles pr, wikidev_projects p
WHERE pr.projectid=p.projectid AND p.projectname='."'$projectName'";

    $results = AnnokiDatabaseFunctions::getQueryResultsAsArray($query, 'user');
    
    return $results;
}

function getUsersInProjectId($projectId){
    $query = 'SELECT pr.userid as user FROM wikidev_projectroles pr
WHERE pr.projectid='."'$projectId'";

    $results = AnnokiDatabaseFunctions::getQueryResultsAsArray($query, 'user');

    return $results;
}

function getProjectList(){
  static $projectList = null;
  
  if ($projectList === null) {
  	$dbr =  wfGetDB(DB_READ);
  	$result = $dbr->query("SELECT projectid, projectname from wikidev_projects order by projectname");

  	$projectList = array();
  	while($row = $dbr->fetchObject($result)){
	  $projectList[$row->projectid] = $row->projectname;
	}
	
  	$dbr->freeResult($result);
  }

  return $projectList;
}

function getAddressMapping() {
	$mappingsFromUser = getMappingsFromTable('user');
	$mappingsFromCustom = getMappingsFromTable(' wikidev_addressmapping');

	$allMappings = array_merge($mappingsFromUser, $mappingsFromCustom);
	return $allMappings;
}

function getAllUserAddr() {
	$mappings = getAddressMapping();
	$userEmails = array();
		
	foreach ($mappings as $email => $user) {
		if (!isset($userEmails[$user])) {
			$userEmails[$user] = array();
		}

		$userEmails[$user][] = $email;
	}

	return $userEmails;
}

function getBugzillaDB() {
	static $db = null;

	if ($db === null) {
		global $wgBugzillaReports;

		$db = new Database($wgBugzillaReports['host'], $wgBugzillaReports['user'],
		$wgBugzillaReports['password'], $wgBugzillaReports['database'], false, 0, '');
	}

	return $db;
}

function getBugzillaGID($project) {
	$bzdb = getBugzillaDB();
	$gid = $bzdb->selectField('groups', 'id', "name = '$project'");
	if (!$gid) {
		return -1;
	}
	
	return $gid;
}

function getUserEmail($username) {
	$username = strtolower($username);
	$allUserAddr = getAllUserAddr();

	if (!isset($allUserAddr[$username]) || count($allUserAddr[$username]) == 0) {
		return null;
	}

	return $allUserAddr[$username];
}

function getBugzillaUID($username, $userAddr = null) {
	if ($userAddr === null) {
		$userAddr = getUserEmail($username);

		if ($userAddr === null) {
			return -1;
		}
	}

	$bzdb = getBugzillaDB();

	$cond = array();
	foreach ($userAddr as $addr) {
		$cond[] = "login_name = '$addr'";
	}

	$where = $bzdb->makeList($cond, LIST_OR);
	
	$uid = $bzdb->selectField("profiles", "userid", $where);
	if (!$uid) {
		return -1;
	}

	return $uid;
}

function getTeamIdFromName($name) {
	static $projListByName = null;
	
	if ($projListByName === null) {
		$projListByName = array_flip(getProjectList());
	}
	
	if (isset($projListByName[$name])) {
		return $projListByName[$name];
	}
	
	return -1;
}

function getTeamNameFromId($id) {
	$projList = getProjectList();
	
	if (isset($projList[$id])) {
		return $projList[$id];
	}
	
	return null;
}


function getMappingsFromTable($table) {
	$dbr = wfGetDB(DB_READ);
	$result = $dbr->select($table, array('user_name', 'user_email'));
	
	$mapping = array();
	while ($row = $dbr->fetchObject($result)) {
		$mapping[$row->user_email] = strtolower($row->user_name);
	}
	
	return $mapping;	
}

function insertNonPrefix($dbw, $table, $newData) {
	if (count($newData) == 0) {
		return;
	}
	
	$sql = "INSERT INTO $table (" . implode( ',', array_keys($newData[0])) . ") VALUES ";
	
	$first = true;
	
	foreach ( $newData as $row ) {
		if ( $first ) {
			$first = false;
		} else {
			$sql .= ',';
		}
		$sql .= '(' . $dbw->makeList( $row ) . ')';
	}
	
	return $dbw->query($sql);
}

function updateNonPrefix($dbw, $table, $values, $conds) {
	$sql = "UPDATE $table SET " . $dbw->makeList( $values, LIST_SET );
	if ( $conds != '*' ) {
		$sql .= " WHERE " . $dbw->makeList( $conds, LIST_AND );
	}
	
	return $dbw->query( $sql );
}	

function createNamespaces($extraNS) {
	global $egAnnokiNamespaces;
	foreach (array_keys($extraNS) as $ns) {
		$ns = strtoupper($ns[0]) . substr($ns, 1);
		
		if ($egAnnokiNamespaces->isExtraNs($ns)) {
			wikidevPrintCL("namespace $ns already exists; skipping...\n");
		} 
		else {
			wikidevPrintCL("adding namespace $ns\n");
			$egAnnokiNamespaces->addNewNamespace($ns);
		}
		
	}

	$egAnnokiNamespaces->registerExtraNamespaces();
}

function getProjectNamespaces($projectName) {
	$suffixes = array("Changeset", "File", "IRC", "Index", "Mail");
	
	$allNamespaces = array($projectName);
	foreach ($suffixes as $suffix) {
		$allNamespaces[] = "${projectName}_$suffix";
	}

	return $allNamespaces;
}

function getProjFromNS() {
	global $wgTitle;
	
	static $proj = null;
	
	if ($proj === null) {
		$allProj = getProjectList();
		$ns = $wgTitle->getNsText();
		if ($ns == 'WikiDev') {
			$pageName = $wgTitle->getText();
			$titleParts = split(" ", $pageName);
			return $titleParts[0];
		}

		foreach ($allProj as $curProj) {
			$projNamespaces = getProjectNamespaces($curProj);
			foreach ($projNamespaces as $projNS) {
				if ($ns == $projNS) {
					$proj = $curProj;
					return $proj;
				}
			}
		}
	}
	
	return $proj;
}

function getPrimaryGroups($passwdFile = null) {
	if ($passwdFile ===  null) {
		global $wdPasswdFile;
		$passwdFile = $wdPasswdFile;
	}
	
	if (!file_exists($passwdFile)) {
		wikidevErrorLog("ERROR: Could not open $passwdFile");
		return null;
	}
	
	$passwd = file_get_contents($passwdFile);
	$passwdLines = split("\n", $passwd);
	
	$groups = array();
	
	foreach ($passwdLines as $passwdLine) {
		if (trim($passwdLine) === "") {
			continue;
		}
		$fields = split(":", $passwdLine);
		$username = $fields[0];
		$primaryGroup = $fields[3];
		
		if (!isset($groups[$primaryGroup])) {
			$groups[$primaryGroup] = array();
		}
		
		$groups[$primaryGroup][] = $username;
	}
	
	return $groups;
}

function parseGroupFile($groupFile = null) {
	if ($groupFile ===  null) {
		global $wdGroupFile;
		$groupFile = $wdGroupFile;
	}
	
	if (!file_exists($groupFile)) {
		wikidevErrorLog("ERROR: Could not open $groupFile");
		return null;
	}
	
	$sql = "
	SELECT p.projectid, projectname, userid
	FROM wikidev_projects p LEFT JOIN wikidev_projectroles r ON r.projectid = p.projectid
	";
	
	$dbr = wfGetDB(DB_READ);
	
	$projects = array();
	$projNameToId = array();
	
	$result = $dbr->query($sql);
	while ($row = $dbr->fetchObject($result)) {		
		$row->projectname = strtolower($row->projectname);
		$projNameToId[$row->projectname] = $row->projectid;
		
		if (!isset($projects[$row->projectid])) {
			$projects[$row->projectid] = array();
		}
		
		if ($row->userid !== null)
			$projects[$row->projectid][] = $row->userid;
	}
		
	$groupsFile = file_get_contents($groupFile);
	$groupLines = split("\n", $groupsFile);
	
	$groups = array();
	$primaryGroups = getPrimaryGroups();
	foreach ($groupLines as $groupLine) {
		if (trim($groupLine) === "") {
			continue;
		}
		
		$fields = split(":", $groupLine);
		$groupName = strtolower($fields[0]);
		if (isset($projNameToId[$groupName])) {
			$projId = $projNameToId[$groupName];
			$groups[$projId] = array();
			
			if ($fields[3] && $fields[3] !== "") {
				$users = split(",", $fields[3]);
							
				foreach ($users as $user) {
					$groups[$projId][] = $user;
				}
			}
			
			$gid = $fields[2];
			if (isset($primaryGroups[$gid])) {
				foreach ($primaryGroups[$gid] as $user) {
					if (!in_array($user, $groups[$projId]))
						$groups[$projId][] = $user;
				}
			}
		}
	}
	
	return array($groups, $projects);
}

function getGroups($groupFile = null) {
	if ($groupFile ===  null) {
		global $wdGroupFile;
		$groupFile = $wdGroupFile;
	}
	$groupsFile = file_get_contents($groupFile);
	
	if ($groupsFile === false) {
		return false;
	}
	
	$groupLines = split("\n", $groupsFile);
	
	$groups = array();

	foreach ($groupLines as $groupLine) {
		$fields = split(":", $groupLine);
		$groupName = strtolower($fields[0]);
		if (trim($groupName) !== "") {
			$groups[] = $groupName;
		}
	}
	
	return $groups;
}

/**
 * if the default e-mail assignemnt uses a single domain - returns that domain, otherwise returns false
 * if an error occurs - returns null
 */
function isSameDomain() {
	$dbr = wfGetDB(DB_READ);
	
	$defEmailSetting = $dbr->selectField(' wikidev_general_config', 'value', "name = 'defEmail'");
	if (!$defEmailSetting) {
		return null;
	}
	
	if ($defEmailSetting == 'custom') {
		return false;
	}
	
	else if ($defEmailSetting == 'same') {
		return true;
	}
	
	else {
		return null;
	}
}

function getSameDomainValue() {
	$dbr = wfGetDB(DB_READ);
	$domain = $dbr->selectField(' wikidev_general_config', 'value', "name = 'emailDomain'");
	if (!$domain) {
		return "";
	}
	
	return $domain;
}

function syncTeams($groupFile = null) {
	if ($groupFile ===  null) {
		global $wdGroupFile;
		$groupFile = $wdGroupFile;
	}
	
	$parsedGroups = parseGroupFile($groupFile);
	if ($parsedGroups === null) {
		print "Could not parse $groupFile!\n";
		die;
	}
	
	list($groups, $projects) = $parsedGroups;
	$groupUsers = array();
	
	foreach ($groups as $users) {
		foreach ($users as $user) {
			$groupUsers[$user] = $user;
			
		}
	}
	
	$allUsers = AnnokiUtils::getAllUsers();
	
	$groupUsers = array_map('strtolower', $groupUsers);
	$allUsers = array_map('strtolower', $allUsers);
	
	$newUsers = array_diff($groupUsers, $allUsers);
	//TODO if deleted - take off team but don't try to delete user?
	
	$newUserObjs = array();
	if (count($newUsers) > 0) {
		foreach ($newUsers as $newUser) {
			$newUser= ucwords($newUser);
			$user = User::createNew($newUser);
			
			$username = strtolower( $newUser );
			$account = posix_getpwnam( $username );
			$gecos = split( ',', $account['gecos'] );
 
			$user->setRealName( $gecos[0] );
			$user->saveSettings();
			$newUserObjs[] = $user;
			wikidevPrintCL("Crated new user $newUser with id = " . $user->getId() . " and real name = " . $user->getRealName() . "\n");
		}
	}
	
	else {
		wikidevPrintCL("No new users\n");
	}
	
	$anyChanges = false;
	foreach ($projects as $projId => $curUsers) {
		if (!isset($groups[$projId]))
			continue;
			
		$newUsers = $groups[$projId];
		
		$toAdd = array_diff($newUsers, $curUsers);
		$toDel = array_diff($curUsers, $newUsers);
		
		$projName = getTeamNameFromId($projId);
		
		$countAdd = count($toAdd);
		$countDel = count($toDel);
		
		if (count($toDel) > 0) {
			wikidevPrintCL("Deleting members from $projName: " . join(", ", $toDel) . "\n");
			TeamManager::removeMembers($projId, $toDel);
			$anyChanges = true;
		}
		
		if (count($toAdd) > 0) {
			wikidevPrintCL("Adding members to $projName: " . join(", ", $toAdd) . "\n");
			TeamManager::addMembers($projId, $toAdd);
			$anyChanges = true;
		}
		
	}
	
	if (!$anyChanges) {
		wikidevPrintCL("No group changes found between group file and database\n");
	}

	if (isSameDomain()) {
		foreach ($newUserObjs as $newUser) {
			$newUser->setOption("oldEmail", "");
			$username = strtolower($newUser->getName());
			$newUser->setEmail($username . "@" . getSameDomainValue());
			$newUser->confirmEmail();
		}
	}
}

function attemptCreateBugzillaAccount(User $user) {
	global $IP, $wdBugzillaPath;
	
	$username = strtolower($user->getName());
	
	$email = $user->getEmail();
	
	if ($email == '') {
		//no email yet
		return;
	}
	
	$bzUID = getBugzillaUID($username);
	
	if ($bzUID == -1) {
		$account = posix_getpwnam( $username );
		$gecos = split( ',', $account['gecos'] );
		$fullname = $gecos[0];

		$oldcwd = getcwd();
		chdir("$wdBugzillaPath");
		$handle = popen("perl createUser.pl", 'w');
		chdir($oldcwd);
		
		if ($handle === FALSE) {
			wikidevErrorLog("Error opening pipe to createUser.pl");
			return;
		}

		$password = '';
		for($i=0; $i<15;++$i) $password .= chr(mt_rand(0,255));
		
		if (fwrite($handle, "$email\n$fullname\n$password\n") === FALSE) {
			wikidevErrorLog("Error writing to createUser.pl pipe");
			return;
		}

		$result = pclose($handle);
		if ($result != 0) {
			wikidevErrorLog("Error adding bugzilla account $email (createUser.pl returned $result)");
			return;
		}
		
		$bzUID = getBugzillaUID($username);
		if ($bzUID == -1) {
			wikidevErrorLog("Error when adding user $username to bugzilla!");
			return;
		}
		
		$bzDB = getBugzillaDB();
		$bzDB->delete('wikidev_mapping', array("username = '$username'"));
		$bzDB->insert('wikidev_mapping', array(array('username' => $username, 'bz_username' => $email)));
		
		$projects = getUserProjects($username);
		if (count($projects) == 0) {
			return; //not part of a project, so not trying to assign permissions
		}
		
		foreach ($projects as $proj) {
			$bzGID = getBugzillaGID($proj);
			if ($bzUID == -1 || $bzGID == -1) {
				wikidevErrorLog("Error when updating bugzilla group: bugzilla user id = $bzUID, bugzilla group id = $bzGID");
			}
			else {
				wikidevPrintCL("adding bugzilla user $bzUID to bugzilla group $bzGID\n");
				$bugzillaRows = array(array('user_id'=>$bzUID, 'group_id'=>$bzGID, 'isbless'=>0, 'grant_type'=>0));
				$bzDB->insert('user_group_map', $bugzillaRows);
			}
		}
	}
}

function getUserProjects($username) {
	$dbr = wfGetDB(DB_READ);
	$sql = "
	SELECT projectname
	FROM wikidev_projectroles r, wikidev_projects p
	WHERE r.userid = '" . strtolower($username) . "'
	AND r.projectid = p.projectid
	";

	$result = $dbr->query($sql);
	$projects = array();
	
	while ($row = $dbr->fetchObject($result)) {
		$projects[] = $row->projectname;
	}

	return $projects;
}

function wikidevErrorLog($msg) {
	//TODO should probably use a real logging library...
	global $wgSitename;
	$call = array_shift(debug_backtrace());
	$file = $call['file'];
	$line = $call['line'];
	error_log("[$wgSitename] $msg ($file:$line)");
}

function wikidevPrintCL($msg) {
	//will only print if running from the commandline
	global $wgCommandLineMode;
	 
	if ($wgCommandLineMode) {
		print $msg;
	}
}
?>
