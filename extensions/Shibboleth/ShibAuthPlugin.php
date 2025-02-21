<?php
 
/**
 * Version 1.2.5 (Works out of box with MW 1.7 or above)
 *
 * Authentication Plugin for Shibboleth (http://shibboleth.internet2.edu)
 * Derived from AuthPlugin.php
 * Much of the commenting comes straight from AuthPlugin.php
 * 
 * Portions Copyright 2006, 2007 Regents of the University of California.
 * Portions Copyright 2007, 2008 Steven Langenaken
 * Released under the GNU General Public License
 *
 * Documentation at http://www.mediawiki.org/wiki/Extension:Shibboleth_Authentication
 * Project IRC Channel: #sdcolleges on irc.freenode.net
 * 
 * Extension Maintainer:
 *	* Steven Langenaken - Added assertion support, more robust https checking, bugfixes for lazy auth, ShibUpdateUser hook
 * Extension Developers:
 *	* D.J. Capelis - Developed initial version of the extension
 */
 
function ShibGetAuthHook() {
	global $wgVersion;
	if ($wgVersion >= "1.13") {
		return 'UserLoadFromSession';
	} else {
		return 'AutoAuthenticate';
	}
}
/*
 * End of AuthPlugin Code, beginning of hook code and auth functions
 */

$wgExtensionCredits['other'][] = array(
			'name' => 'Shibboleth Authentication',
			'version' => '1.2.4',
			'author' => "Regents of the University of California, Steven Langenaken",
			'url' => "http://www.mediawiki.org/wiki/Extension:Shibboleth_Authentication",
			'description' => "Allows logging in through Shibboleth",
			);
 
function SetupShibAuth()
{
	global $shib_UN;
	global $wgHooks;
	global $wgCookieExpiration;
	
	if($shib_UN != null){
		$wgCookieExpiration = -3600;
		$wgHooks[ShibGetAuthHook()][] = "Shib".ShibGetAuthHook();
		$wgHooks['PersonalUrls'][] = 'ShibActive'; /* Disallow logout link */
	} else {
		$wgHooks['PersonalUrls'][] = 'ShibLinkAdd';
	}
	$wgHooks['UserLoadAfterLoadFromSession'][] = 'ShibAutoAuthenticate';
}
 
/* Add login link */
function ShibLinkAdd(&$personal_urls, $title)
{
	global $shib_WAYF, $shib_LoginHint, $shib_Https, $shib_AssertionConsumerServiceURL;
	global $shib_WAYFStyle;
	if (! isset($shib_AssertionConsumerServiceURL) || $shib_AssertionConsumerServiceURL == '')
		$shib_AssertionConsumerServiceURL = "/Shibboleth.sso";
	if (! isset($shib_Https))
		$shib_Https = false;
	if (! isset($shib_WAYFStyle))
		$shib_WAYFStyle = 'WAYF';
	if ($shib_WAYFStyle == 'WAYF')
		$shib_ConsumerPrefix = 'WAYF/';
	else
		$shib_ConsumerPrefix = '';
	$pageurl = $title->getLocalUrl();
	if (! isset($shib_LoginHint))
		$shib_LoginHint = "Login via Single Sign-on";
 
	if ($shib_WAYFStyle == "Login") {
		$personal_urls['SSOlogin'] = array(
			'text' => $shib_LoginHint,
			'href' => ($shib_Https ? 'https' :  'http') .'://' . $_SERVER['HTTP_HOST'] .
			$shib_AssertionConsumerServiceURL . "/" . $shib_ConsumerPrefix . $shib_WAYFStyle .
			'?target=' . (isset($_SERVER['HTTPS']) ? 'https' : 'http') .
			'://' . $_SERVER['HTTP_HOST'] . $pageurl, );
	}
	elseif ($shib_WAYFStyle == "CustomLogin") {
		$personal_urls['SSOlogin'] = array(
			'text' => $shib_LoginHint,
			'href' => ($shib_Https ? 'https' :  'http') .'://' . $_SERVER['HTTP_HOST'] .
			$shib_AssertionConsumerServiceURL .
			'?target=' . (isset($_SERVER['HTTPS']) ? 'https' : 'http') .
			'://' . $_SERVER['HTTP_HOST'] . $pageurl, );
	}
	else {
		$personal_urls['SSOlogin'] = array(
			'text' => $shib_LoginHint,
			'href' => ($shib_Https ? 'https' :  'http') .'://' . $_SERVER['HTTP_HOST'] .
			$shib_AssertionConsumerServiceURL . "/" . $shib_ConsumerPrefix . $shib_WAYF .
			'?target=' . (isset($_SERVER['HTTPS']) ? 'https' : 'http') .
			'://' . $_SERVER['HTTP_HOST'] . $pageurl, );
	}
	return true;
}	
 
/* Kill logout link */
function ShibActive(&$personal_urls, $title)
{
	global $shib_logout;
	global $shib_RN;
	global $shib_map_info;
 
	if($shib_logout == null)
		$personal_urls['logout'] = null;
	else
		$personal_urls['logout']['href'] = $shib_logout;
 
	if ($shib_RN && $shib_map_info)
		$personal_urls['userpage']['text'] = $shib_RN;
 
	return true;
}
 
function ShibAutoAuthenticate($user) {
	ShibUserLoadFromSession($user, true);
}
/* Tries to be magical about when to log in users and when not to. */
function ShibUserLoadFromSession($user, $result)
{
    global $wgUser;
    global $wgMessage;
	global $wgContLang;
	global $shib_UN;
	global $wgHooks;
	global $shib_map_info;
	global $shib_map_info_existing;
	global $shib_pretend;
	global $shib_groups;
	global $shib_email;
	global $config;
	global $wgRequest;

	ShibKillAA();
 
	//For versions of mediawiki which enjoy calling AutoAuth with null users
	if ($user === null) {
		//$user = User::loadFromSession();
		$user = new User();
		$user->mFrom = 'session';
		$user->load();
	}
 
	//They already with us?  If so, nix this function, we're good.
	if($user->isRegistered())
	{
		ShibBringBackAA();
		return true;
	}
    
    $wgUserBefore = $wgUser;
    $wgUser = User::newFromId(1); // Temporarily switch to Admin
	//Is the user already in the database?
	$person = new Person(array());
	//$person = Person::newFromEmployeeId($shib_employeeId);
	if($person == null || $person->getId() == 0){
	    $person = Person::newFromEmail($shib_email);
	}
	if($person == null || $person->getId() == 0){
	    $person = Person::newFromName($shib_UN);
	}
	if($person != null && $person->getId() != 0){
	    if($person->isRole(HQP) || $person->isRole(INACTIVE)){
		    $wgMessage->addError("You do not have permission to view the {$config->getValue('networkName')} Forum");
		    $wgUser = new User();
	        return true;
		}
		$user = $person->getUser();
		$user->load();
		//$wgAuth->existingUser = true;
		//$wgAuth->updateUser($user); //Make sure password is nologin
		//wfSetupSession();
		$user->setCookies();
		ShibAddGroups($user);
		$wgUser = $user;
		startImpersonate();
		return true;
	}
	$wgUser = $wgUserBefore; // Switch back to user
	if(!$config->getValue('shibCreateUser')){
	    $wgMessage->addError("You do not have an account on the {$config->getValue('networkName')} Forum");
	    return true;
	}

	$user = $person->getUser();
 
	//Place the hook back (Not strictly necessarily MW Ver >= 1.9)
	ShibBringBackAA();
 
	//Okay, kick this up a notch then...
	$user->setName($wgContLang->ucfirst($shib_UN));
 
	/* 
	 * Since we only get called when someone should be logged in, if they
	 * aren't let's make that happen.  Oddly enough the way MW does all
	 * this is simply to use a loginForm class that pretty much does
	 * most of what you need.  Creating a loginform is a very very small
	 * part of this object.
	 */
	require_once('includes/specials/SpecialUserlogin.php');
 
	//This section contains a silly hack for MW
	global $wgLang;
	global $wgContLang;
	global $wgRequest;
	$wgLangUnset = false;
 
	if(!isset($wgLang))
	{
		$wgLang = $wgContLang;
		$wgLangUnset = true;
	}
 
	ShibKillAA();
 
	//This creates our form that'll do black magic
	$lf = new LoginForm($wgRequest);
 
	//Place the hook back (Not strictly necessarily MW Ver >= 1.9)
	ShibBringBackAA();
 
	//And now we clean up our hack
	if($wgLangUnset == true)
	{
		unset($wgLang);
		unset($wgLangUnset);
	}
 
	//The mediawiki developers entirely broke use of this the
	//straightforward way in 1.9, so now we just lie...
	$shib_pretend = true;
 
	//Now we _do_ the black magic
	//$lf->mRemember = false;
	$user->loadDefaults($shib_UN);
	$lf->initUser($user, true);
 
	//Stop pretending now
	$shib_pretend = false;
 
	//Finish it off
	$user->saveSettings();
	//$user->setupSession();
    $wgRequest->getSession()->persist();
        
	$user->setCookies();
	ShibAddGroups($user);
	DBFunctions::update('mw_user',
                        array('user_email' => $shib_email,
                              //'employee_id' => $shib_employeeId
                              ),
                        array('user_id' => EQ($user->getId())));
    Cache::delete("mw_user_{$user->getId()}");
	if($config->getValue('shibDefaultRole') != ""){
	    $role = $config->getValue('shibDefaultRole');
	    if(strstr($role, "-Candidate") !== false){
	        $role = str_replace("-Candidate", "", $role);
	        DBFunctions::update('mw_user',
                        array('candidate' => 1),
                        array('user_id' => EQ($user->getId())));
	    }
	    DBFunctions::insert('grand_roles',
	                        array('user_id'    => $user->getId(),
	                              'role'       => $role,
	                              'start_date' => EQ(COL('CURRENT_TIMESTAMP'))));
	}
	$person = Person::newFromId($user->getId());
	return true;
}

function ShibAddGroups($user) {
	global $shib_groups;
	global $shib_group_prefix;
 
        $oldGroups = $user->getGroups();
        foreach ($oldGroups as $group) {
                $user->removeGroup($group);
        }
 
	if (isset($shib_groups)) {
		foreach (explode(';', $shib_groups) as $group) {
			if (isset($shib_group_prefix) && !empty($shib_group_prefix)) {
				$vals = explode(":", $group);
				if ($vals[0] == "wiki") {
					$user->addGroup($vals[1]);
				}
			}
			else {
				$user->addGroup($group);
			}
		}
	}
}
function ShibKillAA()
{
	global $wgHooks;

	//Temporarily kill The AutoAuth Hook to prevent recursion
	foreach ($wgHooks[ShibGetAuthHook()] as $key => $value)
	{
		if($value == "Shib".ShibGetAuthHook())
			$wgHooks[ShibGetAuthHook()][$key] = 'ShibBringBackAA';
	}
}
/* Puts the auto-auth hook back into the hooks array */
function ShibBringBackAA()
{
	global $wgHooks;

	foreach ($wgHooks[ShibGetAuthHook()] as $key => $value)
	{
		if($value == 'ShibBringBackAA')
			$wgHooks[ShibGetAuthHook()][$key] = "Shib".ShibGetAuthHook();
	}
	return true;
}
?>
