<?php
 
$publicPresent = false;
$egAlwaysAllow = array();

  /** 
   * Run any initialization code needed by the extension.
   */
function initializeAccessControls(){
  global $egAnnokiNamespaces, $wgExtraNamespaces;
  
  createRoleNamespaces();
  
  $egAnnokiNamespaces->registerExtraNamespaces($wgExtraNamespaces);
}

function createRoleNamespaces(){
    global $wgAllRoles;
    $nsId = 0;
    $namespaces = array();
    $data = DBFunctions::select(array('mw_an_extranamespaces'),
                                array('nsId', 'nsName'),
                                array('nsName' => NOTLIKE('%_Talk')));
    foreach($data as $row){
        $namespaces[strtoupper($row['nsName'])] = $row['nsId'];
        $nsId = max($nsId, $row['nsId']);
    }
    foreach($wgAllRoles as $role){
        if(!isset($namespaces[strtoupper($role)])){
            $nsId += 2;
            DBFunctions::insert('mw_an_extranamespaces',
                                array('nsId' => $nsId,
                                      'nsName' => $role,
                                      'public' => 1));
            DBFunctions::insert('mw_an_extranamespaces',
                                array('nsId' => $nsId+1,
                                      'nsName' => $role.'_Talk',
                                      'public' => 1));
            
        }
        if(!isset($namespaces[strtoupper($role."_Wiki")])){
            $nsId += 2;
            DBFunctions::insert('mw_an_extranamespaces',
                                array('nsId' => $nsId,
                                      'nsName' => $role."_Wiki",
                                      'public' => 0));
            DBFunctions::insert('mw_an_extranamespaces',
                                array('nsId' => $nsId+1,
                                      'nsName' => $role.'_Wiki_Talk',
                                      'public' => 0));
        }
    }
}

function checkPublicSections(&$parser, &$text){
	$text = parsePublicSections($parser->getTitle(), $text);
	return true;
}

function parsePublicSections($title, $text){
	global $wgUser, $wgScriptPath, $wgOut, $publicPresent;
	if(!is_null($title) && !$wgOut->isDisabled() && !$wgUser->isLoggedIn()){
		$buffer = "";
		$offset = 0;
		
		$pos1 = stripos($text, "[public]");
		$pos2 = stripos($text, "[/public]");
		
		if(($pos1 == false || $pos2 == false) && !$publicPresent){
			// Do Nothing
		}
		else {
			$publicPresent = true;
			while($pos1 != false && $pos2 != false){
				$buffer .= substr($text, $pos1, $pos2 - $pos1);
				$offset = $pos2 + strlen("[/public]");
				$pos1 = stripos($text, "[public]", $offset);
				$pos2 = stripos($text, "[/public]", $offset);
			}
		
			$text = $buffer;
		
			$text = preg_replace("/\[private\].*\[\/private\]/", "", $text);
		}
	}
	$text = str_ireplace("[public]", "<public>", $text);
	$text = str_ireplace("[/public]", "</public>", $text);
	$text = str_ireplace("[private]", "<private>", $text);
	$text = str_ireplace("[/private]", "</private>", $text);
	return $text;
}

function checkLoggedIn($title, $article, $output, $user, $request, $mediaWiki){
    global $config, $wgUser;
    if(!$user->isLoggedIn()){
        if(in_array($_SERVER['REMOTE_ADDR'], $config->getValue('ipWhitelist')) &&
           isset($_GET['apiKey']) && 
           in_array($_GET['apiKey'], $config->getValue('apiKeys')) &&
           strpos(@$_GET['action'], 'api.') === 0 && $_SERVER['REQUEST_METHOD'] == "GET"){
            // Allow Access
            $wgUser = User::newFromId(1);
            return true;
        }
    }
}

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
function onUserCan(&$title, &$user, $action, &$result) {
    //GrandAccess::setupGrandAccess($user, $user->getRights());
    $ret = onUserCan2($title, $user, $action, $result);
    return $ret;
}
 
function onUserCan2(&$title, &$user, $action, &$result) {
  global $wgExtraNamespaces, $egAnProtectUploads, $egNamespaceAllowPagesInMainNS, $egAlwaysAllow, $wgWhitelistRead, $wgRoles, $wgGroupPermissions;
  $person = Person::newFromId($user->getId());
  
  // Is API set?
  if(isset($_GET['action'])){
    $actions = explode(".", $_GET['action'], 2);
    if($actions[0] == "api"){
        $result = true;
        return true;
    }
  }
  
  // Check public sections of wiki page
  if(!$user->isLoggedIn() && $title->getNamespace() >= 0 && $action == 'read'){
      $article = WikiPage::factory($title);
      if($article != null && $article->getContent() != null){
          $text = $article->getContent()->getText();
          if(strstr($text, "[public]") !== false && strstr($text, "[/public]") !== false){
            $result = true;
            return true;
          }
      }
  }

  if($user->isLoggedIn() && $title->getNamespace() == NS_MAIN && $action == 'read'){
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
	
	// TODO: A Hack to allow special access rules for AGE-WELL
	if($title->getNSText() == "HQP_Wiki" && $title->getText() == "HQP Resources"){
	    if($action == 'create' || $action == 'edit'){
	        if($person->isRole(HQP) || $person->isRoleAtLeast(STAFF)){
	            $result = true;
	            return true;
	        }
	    }
	    else if($action == 'read'){
	        // Allow everyone to read
	        if($person->isLoggedIn()){
	            $result = true;
	            return true;
	        }
	    }
	}
	
	//sysops are allowed to do anything (if we reach here then the action is not creating/moving a new page
	if (in_array('sysop', $user->getGroups()) || in_array('Management', $user->getGroups())) {
		$result = true;
		return true;
	}
	
	//Check to see if the title is for an uploaded file, and if the user has permission to view that file.
	if ($egAnProtectUploads && $title->getNamespace() == NS_FILE){
	  require_once('UploadProtection.php');
	  
	  $uploadNS = UploadProtection::getNsForImageTitle($title);
      if(array_search($uploadNS, AnnokiNamespaces::getPublicNamespaces()) !== false){
        $project = Project::newFromName(str_replace("_", " ", $uploadNS));
        $isRole = isset($wgRoles[$uploadNS]);
        $me = Person::newFromId($user->getId());
        if($project == null || $project->getName() == null){
            $result = true;
            return true;
        }
        else if(($project != null && $project->getName() != null) && $me->isMemberOf($project)){
            $result = true;
            return true;
        }
        else if($isRole && $me->isRole($uploadNS)){
            $result = true;
            return true;
        }
        else{
            $result = false;
            return false;
        }
        
      }
	  if ($uploadNS && ($uploadNS !== null || $uploadNS == false)){
	    if (!AnnokiNamespaces::canUserAccessNamespace($user, $uploadNS)){
	      $result = false;
	      return false;
	    }
	  }
	  else if($uploadNS === null || $uploadNS == false){
	    $result = true;
	    return true;
	  }
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

function onFetchChangesList($user, $sk, &$list) {
  $list = $user->getOption( 'usenewrc' ) ? new ProtectedEnhancedChangesList( $sk ) : new ProtectedOldChangesList( $sk );
  return false;
}

function isPublicNS($nsId) {
  global $egAnnokiTablePrefix;
  if ($nsId == -1) //-1 is a placeholder for a public page that is not in a public namespace
    return true;
  
	$dbr = wfGetDB( DB_REPLICA );
	$result = $dbr->select("${egAnnokiTablePrefix}extranamespaces", "public", array("nsId" => $nsId) );

	if (!($row = $dbr->fetchRow($result)) || ($row[0] == 0)) {
		return false;
	}

	return true;
}

/**
 * Moves are only allowed if the user has access to both namespaces, so we need to check that here.
 *
 * @param Title $oldtitle
 * @param Title $newtitle
 * @param User $user
 * @param string $error
 * @return boolean
 */
function onAbortMove($oldtitle, $newtitle, $user, &$error) {
	//nobody but Admin(userid 1) can move anything to the main namespace
	if (($newtitle->getNamespace() == NS_MAIN || $newtitle->getNamespace() == NS_TALK) && $user->getId() != 1) {
		return "<font color='red'>Error: You cannot move a page to the main namespace.</font>";
	}
	$isAllowed = (onUserCan($oldtitle, $user, 'move', $result) && (onUserCan($newtitle, $user, 'move', $result)));
	if ($isAllowed) {
		return true;
	}
	else {
		$error = "<font color='red'>Error: You do not have access to the namespace " . $newtitle->getNsText() . " </font>";
		return false;
	}
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
 * update permissions when a page has been moved
 * important: when a page is moved mediawiki will make simply change the title of the page (leaving
 * the pageid the way it is) and then will create a new page with the old title and a new page id
 * which is a redirect to the old page (with the new title). The newid corresponds to the page with the
 * old title but new pageid and newtitle corresponds to the page with the new title but old pageid.
 * Therefore we want to take the permissions from the new title and add them to the page with id "newid"
 *
 * @param Title $title
 * @param Title $newtitle
 * @param User $user
 * @param int $oldid
 * @param int $newid
 * @return unknown
 */
function onTitleMoveComplete(&$title, &$newtitle, &$user, $oldid, $newid) {
	$extraPermissions = getExtraPermissions($newtitle);
	updatePermissionsByPageID($newid, $extraPermissions);
	return true;
}
/**
 * Updates the per-page permissions for the given page (title)
 *
 * @param Title $title
 * @param array $permissions the new set of permissions (ids of groups/namespaces)
 */

function updateExtraPermissions($title, $permissions) {
	updatePermissionsByPageID($title->getArticleID(), $permissions);
}

function updatePermissionsByPageID($pageID, $permissions) {
  global $egAnnokiTablePrefix;
  
  if ($pageID == 0) { //TODO error?
    return;
  }
  $dbw = wfGetDB( DB_MASTER );
  $dbw->delete("${egAnnokiTablePrefix}pagepermissions", array("page_id" => $pageID));
  
  $newPermissions = array();
  
  foreach ($permissions as $groupID) {
    $newPermissions[] = array("page_id" => $pageID, "group_id" => $groupID);
  }
  $dbw->insert("${egAnnokiTablePrefix}pagepermissions", $newPermissions);
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
	while ($row = $dbr->fetchRow($result)) {
	  $extraPerm[] = $row[0];
	}
	return $extraPerm;
}

function preventUnauthorizedTransclusionOnPreview(&$parser, &$text, &$strip_state){
	return true;
  //return performActionOnTransclusion($text, $error, false); //$error is not used here.
}
  
function preventUnauthorizedTransclusionsOnSave( $editPage, $text, $section, &$error ) {
  return performActionOnTransclusion($text, $error, true);
}

//$isSave=true on save, false on preview
//Always returns true (to continue hook processing)
//Adapted from PageSecurity extension (regex for transclusions)
function performActionOnTransclusion(&$text, &$error, $isSave){
  $pattern = '@{{(.+?)(\|.*?)?}}@is';
  $offset = 0;
  while (preg_match($pattern, $text, $matches, PREG_OFFSET_CAPTURE, $offset)) {
    $offset = $matches[0][1] + 1;  // restart search at the second character to prevent attacks
    $transclusion_text = trim($matches[1][0]);
    $transclusion_title = Title::newFromDBkey($transclusion_text);

    if ($transclusion_title !== null && !$transclusion_title->userCanRead()) {
      if ($isSave){
	$error = '<p class="error">You do not have permission to access transcluded article '.$transclusion_text.'.</p>';
	return true;
      }
      else {
	$start = $offset - 1;
	$stop = $start + 4 + strlen($matches[1][0]);
	
	$text = substr($text, 0, $start)."[[$transclusion_text]]".substr($text, $stop);
      }
    }
  }

  return true;
}

function logout($action, $article){
    global $wgUser, $wgServer, $wgScriptPath;
    if($action == "logout" && $wgUser->isLoggedIn()){
        $wgUser->logout();
        if(isset($_GET['returnto'])){
            redirect($_GET['returnto']);
        }
        else{
            redirect("$wgServer$wgScriptPath");
        }
        return false;
    }
    return true;
}

?>
