<?php
require_once("includes/specialpage/SpecialPage.php");

/**
 * This is a special page that allows an administrator to create new namespaces, rename existing ones and
 * in the future it should allow deleting of namespaces that do not contain any pages (but what about
 * namespaces that have pages that were deleted but not completely purged (i.e. can be undeleted))
 *
 */
class NamespaceManager extends SpecialPage {


	function __construct() {
		global $wgMessageCache;
		SpecialPage::__construct("NamespaceManager");

		$wgMessageCache->addMessages( array('namespacemanager' => 'Namespace Manager'), 'en' );
	}

	/**
	 * Only available to sysops
	 *
	 * @return unknown
	 */
	public function isRestricted() {
		return true;
	}

	public function userCanExecute( $user ) {
		return in_array('sysop', $user->getGroups());
	}

	/**
	 * Handle the new namespace creation action
	 *
	 * @param string $newNsName the name of the new namespace
	 */
	function handleNew($newNsName, $userName) {
		global $wgOut, $egAnnokiNamespaces;
		if (!AnnokiNamespaces::isValidNewNamespaceName($newNsName, $error)) {
			$wgOut->addHTML("<font color='red'>ERROR: $error</font><br>");
			return;
		}
		$user = null;
		if ($userName != "" && $userName != "other") {
			$user = User::newFromName($userName);
			if ($user->getID() < 1) {
				$wgOut->addHTML("<font color='red'>ERROR: Invalid user specified</font><br>");
				return;
			}
			if (UserNamespaces::getUserNamespace($user) !== null) {
				$wgOut->addHTML("<font color='red'>ERROR: The specified user already has a namespace associated with it</font><br>");
				return;
			}
		}
		$egAnnokiNamespaces->addNewNamespace($newNsName, $user);
		$wgOut->addHTML("Added Namespace $newNsName");
	}

	/**
	 * Handle the renaming of a namespace
	 *
	 * @param string $oldName the old name of the namespace
	 * @param string $newName the new name of the namespace
	 */
	function handleRename($oldName, $newName) {
		global $wgOut, $egAnnokiNamespaces;

		if ($oldName == "other") {
			$wgOut->addHTML("<font color='red'>ERROR: No namespace selected for renaming</font><br>");
			return;
		}

		if (!AnnokiNamespaces::isValidNewNamespaceName($newName, $error)) {
			$wgOut->addHTML("<font color='red'>ERROR: $error</font><br>");
			return;
		}
		$egAnnokiNamespaces->renameNamespace($oldName, $newName);
		$wgOut->addHTML("Successfully renamed $oldName to $newName.");

	}

	function togglePublic($nsId, $newValue, $redirect) {
	  global $wgOut, $egAnnokiTablePrefix;
		if ($nsId < 100 || $newValue < 0 || $newValue > 1) {
			return;
		}

		$dbw = wfGetDB( DB_MASTER );
		$dbw->update("${egAnnokiTablePrefix}extranamespaces", array("public" => $newValue), array("nsId" => $nsId));
		
		$wgOut->redirect($redirect);
	}
	
	/**
	 * The entry point of the special page.
	 */
	function execute() {
		global $wgOut, $wgRequest, $wgUser;

		$this->setHeaders();

		if (!$this->userCanExecute($wgUser)) {
			$wgOut->showPermissionsErrorPage( array(
			$wgUser->isAnon()
			? 'userrights-nologin'
			: 'userrights-notallowed' ) );
			return;
		}
		if ($wgRequest->getText("nsToEdit") == "other") {
			$wgOut->redirect($this->getTitle()->escapeLocalURL());
			return;
		}
		//show access controls editor if requested
		if ($wgRequest->getText("operation") == "editNS") {
			$nsName = $wgRequest->getText("nsToEdit");
			$actualNsName = substr($nsName, 0, strpos($nsName, ' ('));
			if ($actualNsName) {
	 			$nsName = $actualNsName;
			}
			if ($wgRequest->wasPosted()) {
				$this->applyChanges($nsName);
			}
			$wgOut->addHTML($this->buildAccCtrls($nsName));
			return;
		}
		else if ($wgRequest->getText("operation") == "togglePublic") {
			$this->togglePublic($wgRequest->getInt('nsId', -1), $wgRequest->getInt("newPublicValue", -1), $wgRequest->getText("redirect"));
			return;
		}
		/* process the form if it was submitted */
		if( $wgRequest->wasPosted() ) {
			switch ($wgRequest->getText("operation")) {
				case "new":
					$this->handleNew($wgRequest->getText("newNsName"), $wgRequest->getText("Users"));
					break;
				case "rename":
					$this->handleRename($wgRequest->getText("nsToRename"), $wgRequest->getText("renameNSName"));
					break;
			}
		}

		$wgOut->addWikiText("==Namespace Access Controls==\n");
		$wgOut->addHTML($this->buildNsCtrlsForm());

		/* add the section+form for adding a new namespace */
		$wgOut->addWikiText("==Add New Namespace==\n");
		$wgOut->addHTML(Xml::openElement( 'form', array( 'method' => 'post', 'action' => $this->getTitle()->escapeLocalURL(), 'name' => 'newNS')));
		$wgOut->addHTML(Xml::hidden( 'operation',  'new' ));
		$wgOut->addHTML(
		Xml::inputLabel("Namespace Name: ", "newNsName", "newNsName", false, $wgRequest->getText("newNsName")) .
		"<br>" .
		"User (optional): " .
		$this->buildUsersDropDown() .
		"<br>" .
		Xml::submitButton("Add")
		);
		$wgOut->addHTML(Xml::closeElement( 'form' ));

		/* add the section+form for renaming an existing namespace */
		/*$wgOut->addWikiText("\n\n\n==Rename Existing Namespace - WARNING: CURRENTLY BROKEN.  DO NOT USE.==");
		$wgOut->addHTML(Xml::openElement( 'form', array( 'method' => 'post', 'action' => $this->getTitle()->escapeLocalURL(), 'name' => 'renameNS')));
		$wgOut->addHTML(Xml::hidden( 'operation',  'rename' ));
		$wgOut->addHTML($this->buildNsDropDown("nsToRename"));
		$wgOut->addHTML("&nbsp;&nbsp;&nbsp;&nbsp;");
		$wgOut->addHTML(Xml::inputLabel("New Name: ", "renameNSName", "renameNSName", false, $wgRequest->getText("renameNSName")));
		$wgOut->addHTML("&nbsp;");
		$wgOut->addHTML(Xml::submitButton("Rename"));
		$wgOut->addHTML(Xml::closeElement( 'form' ));*/

	}

	function buildNsDropDown($name) {
		global $wgRequest;
		return Xml::listDropDown($name,
		"* Project namespaces
		" . $this->buildNsList(PROJECT_NS) . "
		* User namespaces 
		" . $this->buildNsList(USER_NS), "Please select a namespace", $wgRequest->getText($name)
		);
	}

	static function buildUsersDropDown($lowercase = false, $topElement = 'None') {
	  global $egAnnokiCommonPath, $wgRequest;
		
		require_once("$egAnnokiCommonPath/AnnokiUtils.php");
		$allUsers =  AnnokiUtils::getAllUsers();
		if ($lowercase) {
			$allUsers = array_map('strtolower', $allUsers);
		}
		$usersList = "* Users\n";
		foreach ($allUsers as $user) {
			$usersList .= "** $user\n";
		}
		return Xml::listDropDown("Users", $usersList, $topElement, $wgRequest->getText("Users"));

	}
	/**
	 * builds a wiki list (needed for the dropdown menu) for all namespaces of the given type
	 *
	 * @param int $type the type of namespaces to include in the list
	 * @param User $user limits the list to just those namespaces accessible by $user.  Null for all.
	 * @return string wikitext for the list
	 */
	function buildNsList($type) {
		global $egAnnokiNamespaces, $wgUserNamespaces;
		$egAnnokiNamespaces->registerExtraNamespaces(); //this is so that if there is a newly added namespace it will show up in the list
		$list = "";
		$nsList = AnnokiNamespaces::getExtraNamespaces($type);
		sort($nsList);
		foreach ($nsList as $nsItem) {
			if ($type == USER_NS) {
				$nsId = $egAnnokiNamespaces->getNsId($nsItem);
				$userName = $wgUserNamespaces[$nsId]['name'];
				$nsItem .= " ($userName)";
			}
			$list .= "** $nsItem\n";
		}

		return $list;
	}
	
	function buildPublicForm($nsId, $newPublicValue, $buttonText, $redirectURL) {
		 $publicForm = Xml::openElement( 'form', array( 'method' => 'post', 'name' => 'togglePublic')) .
			Xml::hidden( 'operation',  'togglePublic' ) .
			Xml::hidden('nsId', $nsId) .
			Xml::hidden( 'newPublicValue',  $newPublicValue ) .
			Xml::hidden('redirect', $redirectURL) .
			Xml::submitButton($buttonText) .
			Xml::closeElement('form');
		
		return $publicForm;
	}
	function buildAccCtrls($nsName) {
	  global $egAnnokiNamespaces, $wgRequest, $egAnnokiCommonPath;

		$ret = "";
		if ($wgRequest->getBool('editPages')) {
			$currentPages = $egAnnokiNamespaces->getAllPagesInNS($nsName, false);
			$ns = $wgRequest->getText("ns", "");
			if ($ns == "") {
				$allPages = $egAnnokiNamespaces->getAllPages();
			}
			else {
				$allPages = $egAnnokiNamespaces->getAllPagesInNS($ns, false);
			}
				
			$ret .= $this->buildListBoxes($currentPages, $allPages, "Editing pages for namespace $nsName", 'onchange', true, true, true);
		}
		else if ($wgRequest->getBool('editUsers')) {
			$nsId = $egAnnokiNamespaces->getNsId($nsName);
			$isPublic = isPublicNS($nsId);
			if ($isPublic) {
				return "This namespace is public and accessible by all registered users. To make it a regular namespace click the button below.".
				"<br><br><center>" . $this->buildPublicForm($nsId, 0, "Make this namespace non-public", $wgRequest->getRequestURL()) . "</center><br><br>" . $this->buildNsCtrlsForm(); 
			}
			
			$publicButton = $this->buildPublicForm($nsId, 1, "Make this namespace public", $wgRequest->getRequestURL());
		
			$currentUsers = $egAnnokiNamespaces->getAllUsersInNS($nsName);
			require_once("$egAnnokiCommonPath/AnnokiUtils.php");
			$allUsers =  AnnokiUtils::getAllUsers();
			$nsUser = UserNamespaces::getUserFromNamespace($nsName);

			if ($nsUser != null) {
				$nsUserName = $nsUser->getName();
				$allUsers = array_diff($allUsers, array($nsUserName));
				$currentUsers[] = array($nsUserName, true);
			}
			
			$ret .= $this->buildListBoxes($currentUsers, $allUsers, "Editing users for namespace $nsName", 'onkeyup',true,true,false,$publicButton);
		}
		else {
			//???
		}
		$ret .= "<br><hr><br>" . $this->buildNsCtrlsForm();
		return $ret;
	}

	function buildListBoxes($leftList, $rightList, $header, $filterEvent, $leftToRight = true, $rightToLeft = true, $disabled = false, $subheader = "") {
		$gm = new GroupsManager();
		$collisions = array();

		if ($leftList == null) {
			$leftList = array();
		}
		if (is_array($rightList[0])) {
			$oldRight = $rightList;
			$rightList = array();
			foreach ($oldRight as $rightEntry) {
				list($pageName, $isRedirect) = $rightEntry;
				//print("page: $pageName, isR: $isRedirect\n");
				if ($isRedirect == 0) {
					$rightList[] = $pageName;
					$titleWithoutNS = Title::newFromText($pageName)->getText();
					//echo "twNS = $titleWithoutNS\n";
					//if (!is_array($leftList))
					foreach ($leftList as $leftEntry) {
						$leftTitleWithoutNS = Title::newFromText($leftEntry)->getText();
						//echo "ltwNS: $leftTitleWithoutNS";
						if ($titleWithoutNS == $leftTitleWithoutNS && !$this->isRedirect($leftEntry, $oldRight)) {
							$collisions[] = $pageName;
						}
					}
				}
			}
		}
		$rightList = array_diff($rightList, $leftList);
		
		return "<h3><center>$header</center></h3>" .
		"<h4><center>$subheader</center></h4>" . 
		Xml::openElement( 'form', array( 'method' => 'post', 'onsubmit' => 'return dualList.prepareForSubmit()')) .
		$gm->createPermBoxes($leftList, $rightList, false, $filterEvent, 30, $leftToRight, $rightToLeft, $disabled, $collisions) .
		 "<center>" . Xml::submitButton("Apply changes") . "</center>" .
		Xml::closeElement('form');
	}

	function isRedirect($pageName, $allPages) {
		foreach ($allPages as $page) {
			if ($pageName == $page[0] && $page[1] == 1) {
				return true;
			}
		}
		return false;
	}

	function doMove($oldTitle, $newTitle) {
		global $wgOut;
		$wgOut->addHTML("<pre>");
		$wgOut->addHTML("moving page $oldTitle to $newTitle\n");
		$err = $oldTitle->moveTo($newTitle, true);
		DoubleRedirectJob::fixRedirects( 'move', $oldTitle, $newTitle );
		
		if ($err != 1) {
			$wgOut->addHTML("err: $err\n");
			$wgOut->addHTML(print_r($err, TRUE));
		}
		$wgOut->addHTML("</pre>");
	}
	function applyChanges($nsName) {
	  global $wgOut, $wgRequest, $egAnnokiNamespaces;
		
		if ($wgRequest->getBool('editPages')) {
			$targetPages = $wgRequest->getArray( 'removable' );
						
			/*
			 * each page that is in $targetPages but is not yet of the namespace that we are editing
			 * must be moved from wherever it is to the new namespace
			 */
			foreach ($targetPages as $targetPage) {
				$title = Title::newFromText($targetPage);
				$newTitle = Title::newFromText("$nsName:" . $title->getText());
				if ($title->isRedirect()) {
					/*
					 * even though redirects are not shown in the list, this could still occur if a user moves pages and then click refresh
					 * (and reposts the form)
					 */
					continue;
				}
				if ($title->getArticleID() == $newTitle->getArticleID()) {
					continue;
				}
				if ($title->getArticleID() == 0) {
					$wgOut->addHTML("Error: old article ($title) does not seem to exist");
					continue;
				}
				else if ($newTitle->exists()) {
					//if we get to this point then the user has been warned about the name collision and has chosen to overwrite
					$article = new Article($newTitle);
					$article->doDeleteArticle('overwriting article from the Namespace Manager');

				}
				$this->doMove($title, $newTitle);
				$talkPage = $title->getTalkPage();
				if ($talkPage->exists()) {
					$newTalk = Title::newFromText("${nsName}_Talk:" . $title->getText());
					if ($newTalk->exists()) {
						$article = new Article($newTitle);
						$article->doDeleteArticle('overwriting talk page from the Namespace Manager');
					}
					$this->doMove($talkPage, $newTalk);
			}
				
		}

		$wgOut->addHTML("saving pages");
			
	}
	else if ($wgRequest->getBool('editUsers')) {
		$currentUsers = $egAnnokiNamespaces->getAllUsersInNS($nsName);
			
		$targetUsers = $wgRequest->getArray( 'removable', array() );
		
		$toAdd = array_diff($targetUsers, $currentUsers);
		$toDel = array_diff($currentUsers, $targetUsers);
		//TODO it will be more efficient to manually update the table
		foreach ($toAdd as $toAddUserName) {
			$user = User::newFromName($toAddUserName);
			$user->addGroup($nsName); //BT
		}

		foreach ($toDel as $toDelUserName) {
			$user = User::newFromName($toDelUserName);
			$user->removeGroup($nsName); //BT
		}

	}
	$wgOut->addHTML("</pre>");
}

function buildNsCtrlsForm() {
	$out = Xml::openElement( 'form', array( 'method' => 'get', 'action' => $this->getTitle()->escapeLocalURL(), 'name' => 'editNS')) .
	Xml::hidden( 'operation',  'editNS' ) .
	$this->buildNsDropDown("nsToEdit") . "&nbsp;&nbsp;&nbsp;&nbsp;" . Xml::submitButton("Edit Pages", array('name' => 'editPages')) . "&nbsp;&nbsp;&nbsp;&nbsp;" . Xml::submitButton("Edit Users", array('name' => 'editUsers')) .
	Xml::closeElement( 'form' );

	return $out;
}


}
?>
