<?php
define('PROJECT_NS', 1);
define('USER_NS', 2);

/**
 * This class is responsible for handling the custom namespaces in annoki
 *
 */
class AnnokiNamespaces {
	var $userNS;
	var $nsManager;

	/*
	 * Initializes the Annoki Custom Namespaces object
	 */
	function __construct() {
	  global $wgNamespaces;
	  $this->userNS = new UserNamespaces();
	  $this->registerHooks();
	}

	/**
	 * registers all hooks that are handled by this class
	 *
	 */
	function registerHooks() {
		global $wgHooks;
		$wgHooks['UserCreateForm'][] = $this->userNS;
		$wgHooks['AbortNewAccount'][] = $this->userNS;
		$wgHooks['AddNewAccount'][] = $this->userNS;
		$wgHooks['SpecialRecentChangesPanel'][] = $this->userNS;
		$wgHooks['CanonicalNamespaces'][] = array($this, 'registerExtraNamespaces');
	}

/**
 * Registers the custom namespaces with mediawiki
 */
function registerExtraNamespaces(&$namespaces) {
	global $wgContentNamespaces, $wgUserNamespaces;
	$wgUserNamespaces = array();
	$extraNamespaces = $this->retrieveAllExtraNamespaces();
	foreach ($extraNamespaces as $extraNamespace) {
		$nsId = $extraNamespace["nsId"];
		$nsName = $extraNamespace["nsName"];

		$namespaces[$nsId] = $nsName;
		$wgContentNamespaces[] = $nsId;

		/*if ($extraNamespace["nsUser"] != null) {
			$wgUserNamespaces[$nsId] = array("id" => $extraNamespace["nsUser"], "name" => $extraNamespace["user_name"]);
		}*/
		if (!MWNamespace::isTalk($nsId)) {
			$talk = MWNamespace::getTalk($nsId);
			$namespaces[$talk] = "{$nsName}_Talk";
		}
	}
	//natcasesort($wgExtraNamespaces);
	return true;
}

/**
 * Rerieves the id of an extra namespace given its name
 *
 * @param string $nsName the name of the extra namespace
 * @return the id of the extra namespace (-1 if there is no such extra namespace)
 */
function getNsId($nsName) {
	global $wgExtraNamespaces;
	if (!$wgExtraNamespaces) {
		return -1;
	}

	// Hack off username
	$nsName = $this->getCleanNsName($nsName);
	
	return array_search($nsName, $wgExtraNamespaces);
}

/** 
 * Removes the user name from a namespace name, if it exists.
 *
 * @param string $nsName the name of the namespace
 */
 function getCleanNsName($nsName){
   $actualNsName = substr($nsName, 0, strpos($nsName, ' ('));
   if ($actualNsName)
     $nsName = $actualNsName;

   return $nsName;
 }

/**
 * Checks if the given namespace name is one of the extra namespaces
 *
 * @param string $nsName name of the namespace to check
 * @return true if it is one of the extra namespaces
 */
function isExtraNs($nsName) {
	$nsId = $this->getNsId($nsName);
	return ($nsId != -1 && $nsId != null);
}
/**
 * adds a new custom namespace to the database
 *
 * @param string $nsName the name of the new namespace
 * @param User $user if it is a user namespace, the user object for the user
 */
function addNewNamespace($nsName, $user = null) {
  global $egAnnokiTablePrefix, $egNamespaceAllowUsersWithoutNamespaces;

  if ($this->isExtraNs($nsName))
    return false;

  if ($egNamespaceAllowUsersWithoutNamespaces && trim($nsName) == "")
    return false;
  
  $dbw = wfGetDB( DB_PRIMARY );
  $result = $dbw->selectRow("${egAnnokiTablePrefix}extranamespaces", "MAX(nsId) AS maxId", "");
  $nsId = (int) $result->maxId;
  
  if (!is_int($nsId) || ($nsId < 100)) {
    $nsId = 100;
  }
  else {
    $nsId += 2;
  }
  
  $userID = null;
  if ($user != null) {
    $userID = $user->getID();
  }
  $dbw->insert("${egAnnokiTablePrefix}extranamespaces", array("nsId" => $nsId, "nsName" => $nsName, "nsUser" => $userID));
  
  return true;
}

/**
 * renames an existing namespace
 *
 * @param string $nsName the name of the old namespace
 * @param string $newNsName the name of the new namespace
 */
function renameNamespace($nsName, $newNsName)  {
  global $egAnnokiTablePrefix;
	$nsId = $this->getNsId($nsName);
	if ($nsId == -1) {
		return;
	}
	$dbw = wfGetDB( DB_PRIMARY );
	$dbw->update("${egAnnokiTablePrefix}extranamespaces", array("nsName" => $newNsName), array("nsId" => $nsId));
	$this->registerExtraNamespaces();
}

/**
 * retrieves all of the extra namespaces from the database
 *
 * @return an array containing all extra namespaces
 */
function retrieveAllExtraNamespaces() {
  global $egAnnokiTablePrefix;
	$dbr = wfGetDB(DB_REPLICA);
	$sql = "SELECT nsId, nsName, nsUser from `mw_an_extranamespaces`";

	$result = $dbr->query($sql);
	$extraNS = array();
	while ($row = $dbr->fetchRow($result)) {
		$extraNS[] = $row;
	}
	return $extraNS;
}

function getAllPagesInNS($nsName, $includeRedir = true) {
	$pages = array();
	$nsId = $this->getNsId($nsName);
	$dbr = wfGetDB( DB_REPLICA );
	$result = $dbr->select("page", array("page_title", "page_is_redirect"), array("page_namespace" => $nsId) );
	if ($nsName == "Main") {
		$nsName = "";
	}
	else {
		$nsName .= ":";
	}
	while ($row = $dbr->fetchRow($result)) {
		if (!$includeRedir && $row[1] == 1) {
			continue;
		}
		$pages[] = "$nsName" . str_replace('_', ' ', $row[0]);
	}
	return $pages;
}

function getAllPages($includeTalk = false) {
	global $wgExtraNamespaces;
	$dbr = wfGetDB( DB_REPLICA );
	$result = $dbr->select("page", array("page_title", "page_namespace", "page_is_redirect") );

	while ($row = $dbr->fetchRow($result)) {
		if ($row[1] < 100 && $row[1] != NS_MAIN && $row[1] != NS_TALK) {
			continue;
		} 
		if (!$includeTalk && MWNamespace::isTalk($row[1])) {
			continue;
		}
		$nsName = "";
		if (array_key_exists($row[1], $wgExtraNamespaces)) {
			$nsName = $wgExtraNamespaces[$row[1]] . ":";
		}
		$pages[] = array($nsName . str_replace('_', ' ', $row[0]), $row[2]);
	}
	return $pages;
}

function getAllUsersInNS($nsName) {
  //$nsId = $this->getNsId($nsName); //BT
  $nsName = $this->getCleanNsName($nsName);
  $dbr = wfGetDB( DB_REPLICA );
	$userGroupsTable = $dbr->tableName("user_groups");
	$userTable = $dbr->tableName("user");
	$users = array();

	$result = $dbr->query(
	"SELECT u.user_name
	FROM $userGroupsTable ug, $userTable u
	WHERE u.user_id = ug.ug_user
	AND ug.ug_group = '$nsName'"); //BT

	while ($row = $dbr->fetchRow($result)) {
		$users[] = $row[0];
	}
	return $users;
}

/**
 * Retrieves the extra namespaces of the given type (PROJECT_NS, USER_NS)
 *
 * @param int $type the type of namespaces to retrieve
 * @param boolean $includeTalk whether or not to include talk namespaces (default is false)
 */
static function getExtraNamespaces($type, $includeTalk = false) {
	global $wgExtraNamespaces, $wgUserNamespaces;


	$list = array();
	foreach ($wgExtraNamespaces as $extraNSId => $extraNS) {
		if (MWNamespace::isTalk($extraNSId) && !$includeTalk) {
			continue;
		}
		if ($type == USER_NS && UserNamespaces::isUserNs($extraNSId)) {
			$list[] = $extraNS;
		}
		else if ($type == PROJECT_NS && !UserNamespaces::isUserNs($extraNSId)) {
			$list[] = $extraNS;
		}
	}

	return $list;
}

 static function isValidNewNamespaceName($nsName, &$error = "") {
   global $wgExtraNamespaces, $egNamespaceAllowUsersWithoutNamespaces;
   
   if ($wgExtraNamespaces == null) {
     $wgExtraNamespaces = array();
   }
   
   if (!$egNamespaceAllowUsersWithoutNamespaces && trim($nsName) == "") {
     $error = "Invalid namespace name";
     return false;
   }
   
   if (in_array($nsName, $wgExtraNamespaces)) {
     $error = "A namespace with this name already exists.";
     return false;
   }
   
   if (in_array($nsName, User::getAllGroups())) {
     $error = "A group with this name already exists.";
     return false;
   }
   
   return true;
 }
 
 //Get a list of every namespace the given user can access, including their own user namespace, and all public namespaces.
 static function getNamespacesForUser($user){
   global $wgExtraNamespaces;
   
   $groups = $user->getGroups();
   
   $person = Person::newFromUser($user);
   foreach($person->getProjects() as $project){
        $groups[] = $project->getName();
   }
   
   foreach($person->getRoles() as $role){
        $groups[] = $role->getRole();
   }
   
   foreach($person->getThemeProjects() as $project){
        $groups[] = $project->getName();
   }
   
   $namespaces = array();
  
   if (in_array('sysop', $groups))
     $groups = $wgExtraNamespaces;

   $ignore = array('sysop', 'bureaucrat', 'bot');
   
   foreach ($groups as $index => $ns) {
     if (!in_array($ns, $ignore) && in_array(str_replace(" ", "_", $ns), $wgExtraNamespaces) && !MWNamespace::isTalk(self::getNamespaceID($ns))){
       $namespaces[] = $ns;
       $namespaces[] = str_replace(" ", "_", $ns);
     }
   }

   sort($namespaces);

   $userNS = self::getUserNSforUser($user);
   if ($userNS)
     $namespaces = array_merge(array($userNS), $namespaces);

   $namespaces = array_merge($namespaces);

   return array_unique($namespaces);
 }

 static function getPublicNamespaces(){
   global $egAnnokiTablePrefix;
   
   $publicNS = array();

   $dbr = wfGetDB( DB_REPLICA );
   $result = $dbr->select("${egAnnokiTablePrefix}extranamespaces", 'nsName', array('public' => 1) );

   while ($row = $dbr->fetchRow($result)){
     $publicNS[] = $row[0];
   }
   
   $dbr->freeResult($result);

   sort($publicNS);
   
   return $publicNS;
 }

 //Returns false if not a valid namespace name
 static function getNamespaceID($nsName){
   global $wgExtraNamespaces;

   $nsIdLookupArray = array_flip($wgExtraNamespaces);
   if (array_key_exists($nsName, $nsIdLookupArray))
     return $nsIdLookupArray[$nsName];
   else
     return false;
 }

 static function canUserAccessNamespace($user, $nsName){
   $userNamespaces = self::getNamespacesForUser($user);
   return in_array($nsName, $userNamespaces);
 }

 //Gets the user namespace associated with a user.
 //Returns the NS name on success, false on failure.
 static function getUserNSforUser($user){
   global $egAnnokiTablePrefix;

   $dbr = wfGetDB( DB_REPLICA );
   $result = $dbr->selectField("${egAnnokiTablePrefix}extranamespaces", 'nsName', array('nsUser' => $user->getId()));

   if (!$result)
     return false;
   
   return $result;
 }
}
?>
