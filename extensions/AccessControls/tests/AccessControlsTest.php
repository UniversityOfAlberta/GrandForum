<?php
class AccessControlsTest extends PHPUnit_Framework_TestCase {
	var $nsObj;

	function setUp() {
		//registerExtraNamespaces();
		//$this->buildTestDatabase(array("user"));
		global $egAnnokiNamespaces;
		$this->nsObj = $egAnnokiNamespaces;
		$dbw = wfGetDB(DB_PRIMARY);

		$user = User::newFromName("AccessTestUser1");

		if ($user->getID() != 0)
		return;

		//register a few test users
		$user = User::createNew("AccessTestUser1");
		$this->nsObj->addNewNamespace("TU1", $user);
		$user = User::createNew("AccessTestUser2");
		$this->nsObj->addNewNamespace("TU2", $user);
		$user = User::createNew("AccessTestUser3");
		$this->nsObj->addNewNamespace("TU3", $user);

		//and a few test namespaces
		$this->nsObj->addNewNamespace("AccessTestCOP");
		$this->nsObj->addNewNamespace("AccessTestSRN");
		$this->nsObj->addNewNamespace("AccessTestAnmo");

		$this->nsObj->registerExtraNamespaces(); //TODO is there a better way?

		global $wgCanonicalNamespaceNames, $wgExtraNamespaces, $wgContLang;
		$wgCanonicalNamespaceNames = $wgCanonicalNamespaceNames + $wgExtraNamespaces;
		$wgContLang->fixUpSettings(); //TODO (IMPORTANT) should this be in the main code??

		$title = Title::newFromText("accessTestAnmo:page1");
		$article = new Article($title);

		global $_ENV;
		if (strstr($_ENV['PHPRC'], "zend") === FALSE) //this is just to get it to work with the zend debugger...
		$article->doEdit( "text", "summary", EDIT_NEW );

		$user1 = User::newFromName("AccessTestUser1");
		foreach ($user1->getGroups() as $group) {
			$user1->removeGroup($group);
		}
		$user2 = User::newFromName("AccessTestUser2");
		foreach ($user2->getGroups() as $group) {
			$user2->removeGroup($group);
		}
		$user3 = User::newFromName("AccessTestUser3");
		foreach ($user3->getGroups() as $group) {
			$user3->removeGroup($group);
		}

		$user1->addGroup($this->nsObj->getNsId("AccessTestCOP")); //cop

		$user2->addGroup($this->nsObj->getNsId("AccessTestSRN")); //srn
		$user2->addGroup($this->nsObj->getNsId("AccessTestAnmo")); //anmo

		$user3->addGroup($this->nsObj->getNsId("AccessTestSRN")); //srn
		$user3->addGroup($this->nsObj->getNsId("AccessTestCOP")); //cop

	}

	function tearDown() {

	}


	/**
	 * runs a single test
	 *
	 * @param string $pageID the page to test
	 * @param array $allowedUsers names of users that are expected to be allowed access to the page
	 * @param unknown_type $disallowedUsers names of users that are expected to NOT be allowed access to the page
	 * @return true if the test passes false otherwise
	 */
	function doAccessTest($pageTitle, $allowedUsers, $disallowedUsers) {
		$title = Title::newFromText($pageTitle);

		foreach ($allowedUsers as $allowedUser) {
			$user = User::newFromName($allowedUser);
			$errorMsg = sprintf("User %s (%d) should have access to %s (id: %d, ns: %d) but does not", $user->getName(), $user->getID(), $title->getFullText(), $title->getArticleID(), $title->getNamespace());
			$this->assertTrue(onUserCan($title, $user, "view", $result), $errorMsg);
		}

		foreach ($disallowedUsers as $disallowedUser) {
			$user = User::newFromName($disallowedUser);
			$errorMsg = sprintf("User %s (%d) should NOT have access to %s (id: %d, ns: %d) but does", $user->getName(), $user->getID(), $title->getFullText(), $title->getArticleID(), $title->getNamespace());
			$this->assertFalse(onUserCan($title, $user, "view", $result), $errorMsg);
		}
	}


	function testAccessControls() {
		$testCases = array();
		$testCases[] = array('pageTitle' => "accessTestCOP:page1", 'allowed' => array("accessTestUser1", "accessTestUser3"), 'disallowed' => array("accessTestUser2"));
		$testCases[] = array('pageTitle' => "accessTestSRN:page1", 'allowed' => array("accessTestUser2", "accessTestUser3"), 'disallowed' => array("accessTestUser1"));
		$testCases[] = array('pageTitle' => "accessTestAnmo:page2", 'allowed' => array("accessTestUser2"), 'disallowed' => array("accessTestUser1", "accessTestUser3"));
		$testCases[] = array('pageTitle' => "accessTestAnmo_Talk:page2", 'allowed' => array("accessTestUser2"), 'disallowed' => array("accessTestUser1", "accessTestUser3"));
		$testCases[] = array('pageTitle' => "TU3:page1", 'allowed' => array("accessTestUser3"), 'disallowed' => array("accessTestUser1", "accessTestUser2"));
		$testCases[] = array('pageTitle' => "TU3_Talk:page1", 'allowed' => array("accessTestUser3"), 'disallowed' => array("accessTestUser1", "accessTestUser2"));

		foreach ($testCases as $testCase) {
			$pageTitle = $testCase['pageTitle'];
			$allowed = $testCase['allowed'];
			$disallowed = $testCase['disallowed'];
			$this->doAccessTest($pageTitle, $allowed, $disallowed);
		}
	}

	function testExtraAccess() {
		$title = Title::newFromText("accessTestAnmo:page1");
		updateExtraPermissions($title, array($this->nsObj->getNsId("TU1")));

		$testCases = array();
		$testCases[] = array('pageTitle' => "accessTestAnmo:page1", 'allowed' => array("accessTestUser1", "accessTestUser2"), 'disallowed' => array("accessTestUser3"));
		$testCases[] = array('pageTitle' => "accessTestAnmo_Talk:page1", 'allowed' => array("accessTestUser1", "accessTestUser2"), 'disallowed' => array("accessTestUser3"));

		foreach ($testCases as $testCase) {
			$pageTitle = $testCase['pageTitle'];
			$allowed = $testCase['allowed'];
			$disallowed = $testCase['disallowed'];
			$this->doAccessTest($pageTitle, $allowed, $disallowed);
		}
	}

	function testExtraNamespacesRegistered() {
		global $wgExtraNamespaces,  $wgContentNamespaces;

		foreach ($wgExtraNamespaces as $nsId => $nsName) {
			$this->assertGreaterThanOrEqual(100, $nsId, "Extra namespace id < 100!");
		}
		$this->assertEquals("TU1", $wgExtraNamespaces[$this->nsObj->getNsId("TU1")], "TU1 not registered!");
		$this->assertEquals("TU1_Talk", $wgExtraNamespaces[$this->nsObj->getNsId("TU1_Talk")], "TU1_Talk not registered!");
		$this->assertEquals("AccessTestCOP", $wgExtraNamespaces[$this->nsObj->getNsId("AccessTestCOP")], "AccessTestCOP not registered!");
		$this->assertEquals("AccessTestCOP_Talk", $wgExtraNamespaces[$this->nsObj->getNsId("AccessTestCOP_Talk")], "AccessTestCOP_Talk not registered!");
		$this->assertTrue(in_array($this->nsObj->getNsId("TU1"), $wgContentNamespaces));
		$title = Title::newFromText("AccessTestCOP:Page1");
		$this->assertEquals($this->nsObj->getNsId("AccessTestCOP"), $title->getNamespace());
		//$this->assertEquals(3, $title->getArticleID());

	}

	function testGetUserNamespaces() {
		global $wgUserNamespaces, $wgExtraNamespaces;
		$user1 = User::newFromName("AccessTestUser1");
		$user2 = User::newFromName("AccessTestUser2");
		$user3 = User::newFromName("AccessTestUser3");

		$expectedUserNs = array(
		$this->nsObj->getNsId("TU1") => array("id" => $user1->getID(), "name" => $user1->getName()),
		$this->nsObj->getNsId("TU2") => array("id" => $user2->getID(), "name" => $user2->getName()),
		$this->nsObj->getNsId("TU3") => array("id" => $user3->getID(), "name" => $user3->getName())
		);
		$this->assertEquals($expectedUserNs, $wgUserNamespaces);

	}

	function testUpdateUserRights() {
		$user1 = User::newFromName("AccessTestUser1");
		$this->assertEquals(array(106), $user1->getGroups());
		foreach ($user1->getGroups() as $group)
		$user1->removeGroup($group);

		$this->assertEquals(array(), $user1->getGroups());
	}

	function testInsertBadNs() {
		global $wgRequest;
		$userNs = new UserNamespaces();
		$msg = "";

		$this->assertFalse($userNs->onAbortNewAccount(null, $msg));

		$wgRequest->data['wpUserNS'] = "NonExistantNS";
		$this->assertTrue($userNs->onAbortNewAccount(null, $msg));

		$wgRequest->data['wpUserNS'] = "TU1";
		$this->assertFalse($userNs->onAbortNewAccount(null, $msg));

		$wgRequest->data['wpUserNS'] = "sysop";
		$this->assertFalse($userNs->onAbortNewAccount(null, $msg));
	}

	function testIsUserNS() {
		$this->assertTrue(UserNamespaces::isUserNs($this->nsObj->getNsId("TU1")));
		$this->assertFalse(UserNamespaces::isUserNs($this->nsObj->getNsId("AccessTestAnmo")));
	}

	function testGetNamespaces() {
		$projNS = AnnokiNamespaces::getExtraNamespaces(PROJECT_NS);
		$this->assertEquals(array("AnalysisTest", "AccessTestCOP", "AccessTestSRN", "AccessTestAnmo"), $projNS);
		$usersNS = AnnokiNamespaces::getExtraNamespaces(USER_NS);
		$this->assertEquals(array("TU1", "TU2", "TU3"), $usersNS);
	}

	function testRenameNamespaces() {
		$this->assertTrue($this->nsObj->isExtraNs("AccessTestCOP"));
		$this->assertFalse($this->nsObj->isExtraNs("COPNewName"));
		$this->nsObj->renameNamespace("AccessTestCOP", "COPNewName");
		$this->assertTrue($this->nsObj->isExtraNs("COPNewName"));
		$this->assertFalse($this->nsObj->isExtraNs("AccessTestCOP"));
		$this->nsObj->renameNamespace("COPNewName", "AccessTestCOP");
		$this->assertTrue($this->nsObj->isExtraNs("AccessTestCOP"));
		$this->assertFalse($this->nsObj->isExtraNs("COPNewName"));
	}

	function testMovePermissions() {
		/*
		 * User1 wants to move page1 to page2. It should be allowed if the user has access to both
		 * the source and destination namespaces
		 */
		$user = User::newFromName("AccessTestUser1");
		//TODO why is this needed? it should already be in that group
		$user->addGroup($this->nsObj->getNsId("AccessTestCOP"));

		//AccessTestUser1 has access to TU1 but not TU2 so this should fail

		$srcTitle = Title::newFromText("TU1:page1");
		$dstTitle = Title::newFromText("TU2:page1");
		$this->assertFalse(onAbortMove($srcTitle, $dstTitle, $user, $error));

		//AccessTestUser1 has access to TU1 and accessTestCOP so this should succeed
		$srcTitle = Title::newFromText("TU1:page1");
		$dstTitle = Title::newFromText("AccessTestCOP:page1");
		$this->assertTrue(onAbortMove($srcTitle, $dstTitle, $user, $error));
	}
	
	function testGetAllPagesInNs() {
		$allpages = $this->nsObj->getAllPagesInNS("AccessTestAnmo");
		print_r($allpages);	
	}
	
	function testGetAllUsersInNS() {
		$allusers = $this->nsObj->getAllUsersInNS("AccessTestAnmo");
		print_r($allusers);
		
		require_once("$egAnnokiCommonPath/AnnokiUtils.php");
		$allUsers =  AnnokiUtils::getAllUsers();
		print_r($allusers);
	}
}
?>
