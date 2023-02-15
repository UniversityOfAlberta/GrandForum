<?php
/** Set up Annoki logo **/
$wgLogo = "$wgScriptPath/skins/logo.png";

/** Set up Annoki-based global variables **/
$egAnnokiMoodleTablePrefix = "mdl_";
$egAnnokiTablePrefix = "an_";
$egAnnokiCommonPath = "$IP/extensions/AnnokiControl/common";

/** Set up Wiki state **/
$wgWhitelistRead = array ("Special:Userlogin", "Special:InvalidateEmail", "Special:AllMessages", "SITENAME");
$wgWhitelistRead[] = "File:Canadian Resource 1 - Medical Abortion Pharmacist Checklist 2018-02-02.pdf";
$wgWhitelistRead[] = "File:Canadian Medical Abortion Prescribers Checklist.pdf";

$wgWhitelistRead[] = "File:Canadian Resource 1 - Medical Abortion Pharmacy Checklist 2018-07-11.pdf";
$wgWhitelistRead[] = "File:Canadian Resource 1 - Medical Abortion Prescriber Checklist 2018-07-11.pdf";
$wgWhitelistRead[] = "File:Canadian Resource 1 FRENCH - Medical Abortion Pharmacy Checklist 2018-07-11.pdf";
$wgWhitelistRead[] = "File:Canadian Resource 1 FRENCH - Medical Abortion Prescriber Checklist 2018-07-11.pdf";
$wgWhitelistRead[] = "File:Canadian Resource 2 - Medical Abortion Pharmacy Reference Guide 2018-07-11.pdf";
$wgWhitelistRead[] = "File:Canadian Resource 2 - Medical Abortion Prescriber Reference Guide 2018-07-11.pdf";
$wgWhitelistRead[] = "File:Canadian Resource 2 FRENCH - Medical Abortion Pharmacy Reference Guide 2018-07-11.pdf";
$wgWhitelistRead[] = "File:Canadian Resource 2 FRENCH - Medical Abortion Prescriber Reference Guide 2018-07-11.pdf";
$wgWhitelistRead[] = "File:Canadian Medical Abortion Pharmacy Checklist 2018-10-10.pdf";
$wgWhitelistRead[] = "File:Canadian Medical Abortion Dispensing Guide V8 2018-10-18.pdf";
$wgWhitelistRead[] = "File:Canadian EN Resource 1 - Pharmacist Checklist for Medical Abortion 2020.pdf";
$wgWhitelistRead[] = "File:Canadian EN Resource 2 - Pharmacist Resource Guide for Medical Abortion 2020.pdf";
$wgWhitelistRead[] = "File:Canadian FR Resource 1 - Pharmacist Checklist for Medical Abortion 2020.pdf";
$wgWhitelistRead[] = "File:Canadian FR Resource 2 - Pharmacist Resource Guide for Medical Abortion 2020.pdf";
$wgWhitelistRead[] = "File:Clinical SOGC Induced Abortion- Updated Guidance during Pandemics and Periods of Social Disruption.pdf";
$wgWhitelistRead[] = "File:Clinical SOGC Canadian Protocol for the Provision of Medical Abortion via Telemedicine.pdf";
$wgWhitelistRead[] = "File:Medical Abortion Prescriber Checklist.pdf";
$wgWhitelistRead[] = "File:Medical Abortion Prescriber Checklist Resource Guide.pdf";
$wgWhitelistRead[] = "File:Procedural Abortion Care Guidebook.pdf";

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

$wgShowExceptionDetails = true;

/** Disable watch list e-mails **/
#$wgEnotifWatchlist = false;
?>
