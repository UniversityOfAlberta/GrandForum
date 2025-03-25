<?php

use MediaWiki\MediaWikiServices;

define('PROJECT_NS', 1);
define('USER_NS', 2);

/**
 * This class is responsible for handling the custom namespaces in annoki
 *
 */
class AnnokiNamespaces {
	var $nsManager;

	/*
	 * Initializes the Annoki Custom Namespaces object
	 */
	function __construct() {
	  $this->registerHooks();
	}

	/**
	 * registers all hooks that are handled by this class
	 *
	 */
	function registerHooks() {
		global $wgHooks;
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

		if ($extraNamespace["nsUser"] != null) {
			$wgUserNamespaces[$nsId] = array("id" => $extraNamespace["nsUser"], "name" => $extraNamespace["user_name"]);
		}
		if (!MediaWikiServices::getInstance()->getNamespaceInfo()->isTalk($nsId)) {
			$talk = MediaWikiServices::getInstance()->getNamespaceInfo()->getTalk($nsId);
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
 * retrieves all of the extra namespaces from the database
 *
 * @return an array containing all extra namespaces
 */
function retrieveAllExtraNamespaces() {
  global $egAnnokiTablePrefix;
	$dbr = wfGetDB( DB_REPLICA );
	$extraNSTable = $dbr->tableName("${egAnnokiTablePrefix}extranamespaces");
	$userTable = $dbr->tableName("user");
	$sql = "SELECT nsId, nsName, nsUser, user_name from $extraNSTable LEFT JOIN $userTable ON nsUser = user_id ORDER BY nsName";
	$result = $dbr->query($sql);
	$extraNS = array();
	foreach ( $result as $row ) {
		$extraNS[] = get_object_vars($row);
	}
	
	return $extraNS;
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

}
?>
