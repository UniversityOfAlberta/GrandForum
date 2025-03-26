<?php

require_once("$IP/includes/specials/SpecialSearch.php");
require "AnnokiNamespaces.php";
require "AccessControls.body.php";
require "GrandAccess.php";

/** Extension configuration **/
$egAnnokiNamespaces = new AnnokiNamespaces();
$egNamespaceAllowPagesInMainNS = false;

//Without this uploads will not be accessible
$wgUploadPath = "$wgScriptPath/AnnokiUploadAuth.php";

$wgHooks['userCan'][] = 'onUserCan';
$wgHooks['SpecialPageBeforeExecute'][] = 'onUserCanExecute';
$wgHooks['AbortLogin'][] = 'onAbortLogin';
$wgHooks['UserGetRights'][] = 'GrandAccess::setupGrandAccess';
$wgHooks['isValidEmailAddr'][] = 'isValidEmailAddr';
$wgHooks['UserSetCookies'][] = 'userSetCookies';

//Disable parser and page caches for maximum security
$wgEnableParserCache = false;
$wgCachePages = false;

$dir = dirname(__FILE__) . '/';
/* Uncomment the following code to completely disable feeds */
$wgFeed = false;
$wgFeedClasses = array();

$wgExtensionCredits['specialpage'][] = array(
				       'name' => 'AccessControls',
				       'author' =>'UofA: SERL', 
				       //'url' => 'http://www.mediawiki.org/wiki/User:JDoe', 
				       'description' => 'Limits access to pages based on membership in namespaces.'
				       );
				       
function permissionError($text="You are not allowed to execute the action you have requested."){
    global $wgOut, $wgServer, $wgScriptPath, $wgTitle;
    if($wgTitle == null){
        // Depending on when this function is called, the title may not be created yet, so make an empty one
        $wgTitle = new Title();
    }
    $wgOut->setPageTitle("Permission error");
    $wgOut->addHTML("<p>$text</p>
                     <p>Return to <a href='$wgServer$wgScriptPath/index.php/Main_Page'>Main Page</a>.</p>");
    $wgOut->output();
    $wgOut->disable();
    close();
}

function isValidEmailAddr($addr, &$result){
    $result = filter_var(unaccentChars($addr), FILTER_VALIDATE_EMAIL);
    return false;
}

function userSetCookies($user, $session, $cookies){
    global $wgCookiePrefix;
    foreach($cookies as $name => $value){
        $_COOKIE[$wgCookiePrefix . $name] = $value;
    }
    return true;
}
?>
