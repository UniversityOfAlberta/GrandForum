<?php

require_once('commandLine.inc');
global $wgTestDBname, $wgDBname;

// Drop Test DB
$drop = "echo 'DROP DATABASE IF EXISTS {$wgTestDBname}; CREATE DATABASE {$wgTestDBname};' | mysql -u {$wgDBuser} -p{$wgDBpassword}";
system($drop);

// Create Test DB Structure
$dump = "mysqldump --no-data -u {$wgDBuser} -p{$wgDBpassword} {$wgDBname} -d --single-transaction | sed 's/ AUTO_INCREMENT=[0-9]*\b//' | mysql -u {$wgDBuser} -p{$wgDBpassword} {$wgTestDBname}";
system($dump);

// Copy select table data to Test DB
DBFunctions::execSQL("INSERT INTO `{$wgTestDBname}`.`grand_universities` SELECT * FROM `{$wgDBname}`.`grand_universities`", true);
DBFunctions::execSQL("INSERT INTO `{$wgTestDBname}`.`grand_disciplines_map` SELECT * FROM `{$wgDBname}`.`grand_disciplines_map`", true);
DBFunctions::execSQL("INSERT INTO `{$wgTestDBname}`.`grand_partners` SELECT * FROM `{$wgDBname}`.`grand_partners`", true);
DBFunctions::execSQL("INSERT INTO `{$wgTestDBname}`.`mw_an_extranamespaces` SELECT * FROM `{$wgDBname}`.`mw_an_extranamespaces`", true);
DBFunctions::execSQL("INSERT INTO `{$wgTestDBname}`.`mw_page` SELECT * FROM `{$wgDBname}`.`mw_page`", true);
DBFunctions::execSQL("INSERT INTO `{$wgTestDBname}`.`mw_text` SELECT * FROM `{$wgDBname}`.`mw_text`", true);
DBFunctions::execSQL("INSERT INTO `{$wgTestDBname}`.`mw_revision` SELECT * FROM `{$wgDBname}`.`mw_revision`", true);


// Start populating custom data
$wgDBname = $wgTestDBname;
$dbw = wfGetDB(DB_MASTER);
$dbr = wfGetDB(DB_SLAVE);
$dbw->open($wgDBserver, $wgDBuser, $wgDBpassword, $wgDBname);
$dbr->open($wgDBserver, $wgDBuser, $wgDBpassword, $wgDBname);

DBFunctions::$dbr = null;
DBFunctions::$dbw = null;
DBFunctions::initDB();

User::createNew("Admin.User1", array('password' => User::crypt("Admin.Pass1"), 'email' => "admin.user1@behat.com"));
User::createNew("PNI.User1", array('password' => User::crypt("PNI.Pass1"), 'email' => "pni.user1@behat.com"));
User::createNew("PNI.User2", array('password' => User::crypt("PNI.Pass2"), 'email' => "pni.user2@behat.com"));
User::createNew("PNI.User3", array('password' => User::crypt("PNI.Pass3"), 'email' => "pni.user3@behat.com"));
User::createNew("CNI.User1", array('password' => User::crypt("CNI.Pass1"), 'email' => "cni.user1@behat.com"));
User::createNew("CNI.User2", array('password' => User::crypt("CNI.Pass2"), 'email' => "cni.user2@behat.com"));
User::createNew("CNI.User3", array('password' => User::crypt("CNI.Pass3"), 'email' => "cni.user3@behat.com"));
User::createNew("HPQ.User1", array('password' => User::crypt("HQP.Pass1"), 'email' => "hqp.user1@behat.com"));
User::createNew("HQP.User2", array('password' => User::crypt("HQP.Pass2"), 'email' => "hqp.user2@behat.com"));
User::createNew("HQP.User3", array('password' => User::crypt("HQP.Pass3"), 'email' => "hqp.user3@behat.com"));

?>
