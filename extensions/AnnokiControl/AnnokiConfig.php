<?php
/** Set up Annoki logo **/
$wgLogo = "$wgScriptPath/skins/logo.png";

/** Set up Annoki-based global variables **/
$egAnnokiMoodleTablePrefix = "mdl_";
$egAnnokiTablePrefix = "an_";
$egAnnokiCommonPath = "$IP/extensions/AnnokiControl/common";

/** Set up Wiki state **/
$wgWhitelistRead = array ("Special:Userlogin", "Special:InvalidateEmail", "Special:AllMessages", "SITENAME");
$wgGroupPermissions['*']['read']            = false;
$wgGroupPermissions['*']['edit']            = false;
$wgGroupPermissions['*']['createaccount']   = false;
$wgGroupPermissions['*']['createpage']      = false;

/** Misc MediaWiki configuration parameters */
$wgEnableMWSuggest = true;      //Use AJAX search suggestions
$wgRCMaxAge = 30 * 24 * 3600;   //Keep recent changes for 30 days
#$wgEdititis = true;            //Show user edit counts in Special:UserList
#$wgRedirectOnLogin='Main_Page'; //Automatically redirect to Main_Page on login
$wgJobRunRate = 1000;           //Hack to fix redirect fixer assigning fixes to some user other than itself

#$wgReadOnly = '<b>Annoki is undergoing some maintenance.  Try back later, or contact tansey@cs.ualberta.ca for more information.';

/** Set debugging variables **/
$wgShowSQLErrors = true;
$wgDebugDumpSql  = true;
error_reporting(E_ALL);
ini_set("display_errors", 1);
$wgShowExceptionDetails = true;

/** Disable watch list e-mails **/
#$wgEnotifWatchlist = false;
?>
