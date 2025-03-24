<?php
 
$egAlwaysAllow = array();

use MediaWiki\MediaWikiServices;

function onUserCanExecute($special, $subpage){
    if(!$special->userCanExecute($special->getUser())){
        permissionError();
    }
    return true;
}

/**
 * handler for the userCan hook; tells mediawiki whether the given user is allowed to perform the given action to the given title
 *
 * @param Title $title
 * @param User $user
 * @param string $action
 * @param boolean $result
 * @return unknown
 * 
 * TODO: This should be cached because it gets called a lot in each request and the permissions for a specific
 * action do not change during a single request.
 */
function onUserCan(&$title, &$user, $action, &$result){
    global $wgMessage, $config;
    $me = Person::newFromUser($user);
    if($me instanceof FullPerson){
        $me->getFecPersonalInfo();
        if(!$me->inFaculty() && !$me->isRoleAtLeast(MANAGER) && !$me->isRole("FEC ".getFaculty())){
            $wgMessage->clearError();
            $wgMessage->addError("You are on the wrong AIMS instance.  Make sure you clicked on the correct faculty.  Contact <a href='mailto:{$config->getValue('supportEmail')}'>{$config->getValue('supportEmail')}</a> if you still don't have access.");
            $ret = false;
            return false;
        }
    }
    //GrandAccess::setupGrandAccess($user, $user->getRights());
    $ret = onUserCan2($title, $user, $action, $result);
    return $ret;
}

function onUserCan2(&$title, &$user, $action, &$result) {
  global $wgExtraNamespaces, $egNamespaceAllowPagesInMainNS, $egAlwaysAllow, $wgWhitelistRead, $wgRoles, $wgGroupPermissions;
  $person = Person::newFromId($user->getId());
  // Is API set?
  if(isset($_GET['action'])){
    $actions = explode(".", $_GET['action'], 2);
    if($actions[0] == "api"){
        $result = true;
        return true;
    }
  }

  if($action == 'read'){
    // A logged in user should be able to read any page in the main namespace
    
    $result = true;
    return true;
  }
  
  if ($user->getName() == "Redirect fixer") {
		/*
		 * Note: even if the redirect fixer account has not been created yet, mediawiki 1.13 will not allow anybody to register with that name
		 * Not sure what happens when importing from older versions that might have a user called "Redirect fixer"
		 */
		$result = true;
		return $result;
	}
    
	//only staff+ can create pages in the main namespace
	if (($action == 'create' || $action == 'edit') && !$egNamespaceAllowPagesInMainNS && 
	    ($title->getNamespace() == NS_MAIN || $title->getNamespace() == NS_TALK || 
	     $title->getNamespace() == NS_HELP || $title->getNamespace() == NS_HELP) && $person->isRoleAtLeast(STAFF)) {
		return true;
	}
	
	if($person->isRoleAtLeast(STAFF)){
	    $result = true;
	    return true;
	}
	
	//sysops are allowed to do anything (if we reach here then the action is not creating/moving a new page
	if (in_array('sysop', $user->getGroups())) {
		$result = true;
		return true;
	}
	
	if (($action == 'edit' || $action == 'move') && $title->isProtected($action)) {
		//this is needed because the global protect permissions that we had to set above somehow conflict with page edit restrictions
		//if we reach here then the user is not a sysop
		
		$result = false;
		return false;
	}
	
	if (($action == 'delete' || $action == 'browsearchive' 
	|| $action == 'undelete' || $action == 'deletedhistory') && $title->isProtected('edit')) {
		//if the page is protected for editing and this user is not a sysop then we don't let them delete/undelete it
		$result = false;
		return false;
	}
	
	if( isset($wgWhitelistRead) && is_array($wgWhitelistRead)) {
		$name = $title->getPrefixedText();
		$dbName = $title->getPrefixedDBKey();
		if( in_array($name,$wgWhitelistRead,true) || in_array($dbName,$wgWhitelistRead,true) ) {
			$result = true;
			return true;
		}
	}
	
	if ( $title->getNamespace() < 100 || isPublicNS($title->getNamespace()))  {
		if ($action == 'protect' || $action == 'unprotect') {
			//only sysops can protect main/public namespaces
			$result = false;
			return $result;
		}
		$result = null;
		if ($title->getFullText() == "Special:UserLogin") {
			$result = true;
			return true;
		}
		
	}

	if ($action == 'protect' || $action == 'unprotect') {
		$owner = UserNamespaces::getUserFromNamespace($title->getNsText());
		if ($owner === null) {
			$result = false;
			return false;
		}
		$result = ($owner->getId() == $user->getId());
		return $result;
	}
	
	if ($title->isTalkPage()) {
		$title = $title->getSubjectPage();
	}
	$allowedGroups = getExtraPermissions($title);
	$allowedGroups[] = $title->getNamespace();

    $nsText = "";
    if(strstr($title->getText(), ":") !== false){
        $exploded = explode(":", $title->getText());
        $nsText = @$exploded[0];
    }
    
    $userGroups = $user->getGroups();

    foreach($userGroups as $group){
        if($nsText == $group){
            $result = true;
            return true;
        }
    }

	foreach ($allowedGroups as $index => $group){
	  if (isPublicNS($group)) {
	    $result = true;
	    return true;
	  }
	  if($wgExtraNamespaces != null){
	      if (array_key_exists($group, $wgExtraNamespaces)){
	        $allowedGroups[$index] = $wgExtraNamespaces[$group];
	      }
	  }
	}
	
	$userNS = UserNamespaces::getUserNamespace($user);
	if($wgExtraNamespaces != null){
	    if (array_key_exists($userNS, $wgExtraNamespaces)){
	      $userNS = $wgExtraNamespaces[$userNS];
	    }
	}

	$userGroups[] = $userNS;
	$userName = $user->getName();
	
	$nsText = $title->getNsText(); 
	
	if (isset($egAlwaysAllow[$userName][$nsText])) {
		$allowedAction = $egAlwaysAllow[$userName][$nsText];
		
		if ($allowedAction == 'all' || $allowedAction == $action) {
			$userGroups[] = $nsText;	
		}
	}
	
	$result = (count(array_intersect($allowedGroups, $userGroups)) > 0);
	if ($result) {
		return true;
	}
	else {
		if ($user->getId() != 0 && $action == 'read' && ($title->getNamespace() < 100 || isPublicNS($title->getNamespace()))) {
			return true;
		}
		else {
			return false;
		}
	}
}

function isPublicNS($nsId) {
  global $egAnnokiTablePrefix;
  if ($nsId == -1) //-1 is a placeholder for a public page that is not in a public namespace
    return true;
  
	$dbr = wfGetDB( DB_REPLICA );
	$result = $dbr->select("${egAnnokiTablePrefix}extranamespaces", "public", array("nsId" => $nsId) );

	if (!($row = $result->fetchRow()) || ($row[0] == 0)) {
		return false;
	}

	return true;
}

/**
 * Abort login if user is deleted
 */
function onAbortLogin($user, $password, &$retval, &$msg){
    $data = DBFunctions::select(array('mw_user'),
                                array('user_id'),
                                array('user_id' => $user->getId(),
                                      'deleted' => 0));
    if(count($data) > 0){
        // User exists
        return true;
    }
    // User does not exist/is deleted
    return false;
}

/**
 * Retrieves the per-page permissions for the given page (title). Those are in addition to the ones inherited from the namespace
 *
 * @param Title $title
 * @return array of group/namespace ids that are allowed to see the specific page
 */
function getExtraPermissions($title) {
  global $egAnnokiTablePrefix;

	$dbr = wfGetDB( DB_REPLICA );
	$result = $dbr->select("${egAnnokiTablePrefix}pagepermissions", "group_id", array("page_id" => $title->getArticleID()) );
	$extraPerm = array();
	while ($row = $result->fetchRow()) {
	  $extraPerm[] = $row[0];
	}
	return $extraPerm;
}

?>
