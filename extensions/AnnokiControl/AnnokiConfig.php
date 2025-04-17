<?php
/** Set up Annoki logo **/
$wgLogo = "$wgScriptPath/skins/logo.png";

/** Set up Annoki-based global variables **/
$egAnnokiTablePrefix = "an_";

/** Set up Wiki state **/
$wgWhitelistRead = array ("Special:Userlogin", "Special:InvalidateEmail", "Special:AllMessages", "SITENAME");
$wgGroupPermissions['*']['read']            = false;
$wgGroupPermissions['*']['edit']            = false;
$wgGroupPermissions['*']['createaccount']   = false;
$wgGroupPermissions['*']['createpage']      = false;

/** Misc MediaWiki configuration parameters */
$wgRCMaxAge = 30 * 24 * 3600;   //Keep recent changes for 30 days

/** Set debugging variables **/
$wgShowSQLErrors = true;
$wgDebugDumpSql  = true;
$wgShowExceptionDetails = true;

?>
