<?php

define('INIT_TESTING', true);
define('TESTING', true);
require_once('commandLine.inc');
global $config;

function createProject($acronym, $fullName, $status, $type, $bigbet, $phase, $effective_date, $description, $problem, $solution, $challenge="Not Specified", $parent_id=0){
    $_POST['acronym'] = $acronym;
    $_POST['fullName'] = $fullName;
    $_POST['status'] = $status;
    $_POST['type'] = $type;
    $_POST['bigbet'] = $bigbet;
    $_POST['phase'] = $phase;
    $_POST['effective_date'] = $effective_date;
    $_POST['description'] = $description;
    $_POST['long_description'] = $description;
    $_POST['challenge'] = Theme::newFromName($challenge)->getId();
    $_POST['parent_id'] = $parent_id;
    $_POST['problem'] = $problem;
    $_POST['solution'] = $solution;
    APIRequest::doAction('CreateProject', true);
}

function addUserRole($name, $role){
    Person::$cache = array();
    Person::$namesCache = array();
    $_POST['user'] = $name;
    $_POST['role'] = $role;
    APIRequest::doAction('AddRole', true);
}

function addUserProject($name, $project){
    $_POST['user'] = $name;
    $_POST['role'] = $project;
    APIRequest::doAction('AddProjectMember', true);
}

function addProjectLeader($name, $project, $coLead='False', $manager='False'){
    $_POST['user'] = $name;
    $_POST['role'] = $project;
    $_POST['co_lead'] = $coLead;
    $_POST['manager'] = $manager;
    APIRequest::doAction('AddProjectLeader', true);
}

function addThemeLeader($name, $theme, $coLead='False', $coord='False'){
    $_POST['name'] = $name;
    $_POST['theme'] = Theme::newFromName($theme)->getId();
    $_POST['co_lead'] = $coLead;
    $_POST['coordinator'] = $coord;
    APIRequest::doAction('AddThemeLeader', true);
}

function addRelation($name1, $name2, $type){
    $_POST['name1'] = $name1;
    $_POST['name2'] = $name2;
    $_POST['type'] = $type;
    APIRequest::doAction('AddRelation', true);
}

global $wgTestDBname, $wgDBname, $wgRoles, $wgUser;

// Drop Test DB
$drop = "echo 'DROP DATABASE IF EXISTS {$config->getValue('dbTestName')}; CREATE DATABASE {$config->getValue('dbTestName')};' | mysql -u {$wgDBuser} -p{$wgDBpassword}";
system($drop);

// Create Test DB Structure
$dump = "mysqldump --no-data -u {$wgDBuser} -p{$wgDBpassword} {$config->getValue('dbName')} -d --single-transaction | sed 's/ AUTO_INCREMENT=[0-9]*\b//' | mysql -u {$wgDBuser} -p{$wgDBpassword} {$config->getValue('dbTestName')}";
system($dump);

// Copy select table data to Test DB
DBFunctions::execSQL("INSERT INTO `{$config->getValue('dbTestName')}`.`grand_universities` SELECT * FROM `{$config->getValue('dbName')}`.`grand_universities`", true);
DBFunctions::execSQL("INSERT INTO `{$config->getValue('dbTestName')}`.`grand_provinces` SELECT * FROM `{$config->getValue('dbName')}`.`grand_provinces`", true);
DBFunctions::execSQL("INSERT INTO `{$config->getValue('dbTestName')}`.`grand_positions` SELECT * FROM `{$config->getValue('dbName')}`.`grand_positions`", true);
DBFunctions::execSQL("INSERT INTO `{$config->getValue('dbTestName')}`.`grand_disciplines_map` SELECT * FROM `{$config->getValue('dbName')}`.`grand_disciplines_map`", true);
DBFunctions::execSQL("INSERT INTO `{$config->getValue('dbTestName')}`.`grand_partners` SELECT * FROM `{$config->getValue('dbName')}`.`grand_partners`", true);
DBFunctions::execSQL("INSERT INTO `{$config->getValue('dbTestName')}`.`mw_page` SELECT * FROM `{$config->getValue('dbName')}`.`mw_page` WHERE page_id < 10", true);
DBFunctions::execSQL("INSERT INTO `{$config->getValue('dbTestName')}`.`mw_revision` SELECT * FROM `{$config->getValue('dbName')}`.`mw_revision` WHERE rev_page < 10", true);
DBFunctions::execSQL("INSERT INTO `{$config->getValue('dbTestName')}`.`mw_text` SELECT * FROM `{$config->getValue('dbName')}`.`mw_text` WHERE old_id IN (SELECT rev_text_id FROM `{$config->getValue('dbTestName')}`.`mw_revision`)", true);

// Start populating custom data
$wgDBname = $wgTestDBname;
$dbw = wfGetDB(DB_MASTER);
$dbr = wfGetDB(DB_SLAVE);
$dbw->open($wgDBserver, $wgDBuser, $wgDBpassword, $wgDBname);
$dbr->open($wgDBserver, $wgDBuser, $wgDBpassword, $wgDBname);

DBFunctions::$dbr = null;
DBFunctions::$dbw = null;
DBFunctions::initDB();

// Initialize test mailing lists in db
DBFunctions::execSQL("INSERT INTO wikidev_projects (`projectid`,`mailListName`) VALUES (1, 'test-hqps')", true);
DBFunctions::execSQL("INSERT INTO wikidev_projects (`projectid`,`mailListName`) VALUES (2, 'test-researchers')", true);
DBFunctions::execSQL("INSERT INTO wikidev_projects (`projectid`,`mailListName`) VALUES (3, 'test-leaders')", true);
DBFunctions::execSQL("INSERT INTO wikidev_projects_rules (`type`,`project_id`,`value`) VALUES ('ROLE', 1, '".HQP."')", true);
DBFunctions::execSQL("INSERT INTO wikidev_projects_rules (`type`,`project_id`,`value`) VALUES ('ROLE', 2, '".NI."')", true);
DBFunctions::execSQL("INSERT INTO wikidev_projects_rules (`type`,`project_id`,`value`) VALUES ('ROLE', 3, '".PL."')", true);

//Initialize Themes
DBFunctions::execSQL("INSERT INTO grand_themes (`acronym`,`name`,`description`) VALUES ('Theme1', 'Theme 1', 'Theme 1 Description')", true);
DBFunctions::execSQL("INSERT INTO grand_themes (`acronym`,`name`,`description`) VALUES ('Theme2', 'Theme 2', 'Theme 2 Description')", true);
DBFunctions::execSQL("INSERT INTO grand_themes (`acronym`,`name`,`description`) VALUES ('Theme3', 'Theme 3', 'Theme 3 Description')", true);
DBFunctions::execSQL("INSERT INTO grand_themes (`acronym`,`name`,`description`) VALUES ('Theme4', 'Theme 4', 'Theme 4 Description')", true);
DBFunctions::execSQL("INSERT INTO grand_themes (`acronym`,`name`,`description`) VALUES ('Theme5', 'Theme 5', 'Theme 5 Description')", true);
DBFunctions::execSQL("INSERT INTO grand_themes (`acronym`,`name`,`description`) VALUES ('Theme6', 'Theme 6', 'Theme 6 Description')", true);

$id = 100;
DBFunctions::insert('mw_an_extranamespaces', array('nsId' => $id, 'nsName' => 'Cal', 'public' => '0'));
$id += 2;
DBFunctions::insert('mw_an_extranamespaces', array('nsId' => $id, 'nsName' => $config->getValue('networkName'), 'public' => '1'));
$id += 2;
DBFunctions::insert('mw_an_extranamespaces', array('nsId' => $id, 'nsName' => 'Mail', 'public' => '1'));
$id += 2;
DBFunctions::insert('mw_an_extranamespaces', array('nsId' => $id, 'nsName' => 'Survey', 'public' => '1'));
$id += 2;
DBFunctions::insert('mw_an_extranamespaces', array('nsId' => $id, 'nsName' => 'Student_Committee', 'public' => '0'));
$id += 2;
DBFunctions::insert('mw_an_extranamespaces', array('nsId' => $id, 'nsName' => 'Poster', 'public' => '0'));
$id += 2;
DBFunctions::insert('mw_an_extranamespaces', array('nsId' => $id, 'nsName' => 'Conference', 'public' => '1'));
$id += 2;
DBFunctions::insert('mw_an_extranamespaces', array('nsId' => $id, 'nsName' => 'Presentation', 'public' => '1'));
$id += 2;
DBFunctions::insert('mw_an_extranamespaces', array('nsId' => $id, 'nsName' => 'FeatureRequest', 'public' => '0'));
$id += 2;
DBFunctions::insert('mw_an_extranamespaces', array('nsId' => $id, 'nsName' => 'Feedback', 'public' => '0'));
$id += 2;
DBFunctions::insert('mw_an_extranamespaces', array('nsId' => $id, 'nsName' => 'Publication', 'public' => '1'));
$id += 2;
DBFunctions::insert('mw_an_extranamespaces', array('nsId' => $id, 'nsName' => 'Artifact', 'public' => '1'));
$id += 2;
DBFunctions::insert('mw_an_extranamespaces', array('nsId' => $id, 'nsName' => 'Activity', 'public' => '1'));
$id += 2;
DBFunctions::insert('mw_an_extranamespaces', array('nsId' => $id, 'nsName' => 'Press', 'public' => '1'));
$id += 2;
DBFunctions::insert('mw_an_extranamespaces', array('nsId' => $id, 'nsName' => 'Contribution', 'public' => '1'));
$id += 2;
DBFunctions::insert('mw_an_extranamespaces', array('nsId' => $id, 'nsName' => 'Award', 'public' => '1'));
$id += 2;
DBFunctions::insert('mw_an_extranamespaces', array('nsId' => $id, 'nsName' => 'ConferenceOrganization', 'public' => '1'));
DBFunctions::insert('mw_an_extranamespaces', array('nsId' => $id+1, 'nsName' => 'ConferenceOrganization_Talk', 'public' => '1'));
$id += 2;
DBFunctions::insert('mw_an_extranamespaces', array('nsId' => $id, 'nsName' => 'Multimedia', 'public' => '1'));
$id += 2;
DBFunctions::insert('mw_an_extranamespaces', array('nsId' => $id, 'nsName' => 'Form', 'public' => '1'));
$id += 2;

DBFunctions::insert('mw_an_extranamespaces', array('nsId' => $id, 'nsName' => 'Inactive', 'public' => '1'));
DBFunctions::insert('mw_an_extranamespaces', array('nsId' => $id+1, 'nsName' => 'Inactive_Talk', 'public' => '1'));
$id += 2;
foreach($wgRoles as $role){
    DBFunctions::insert('mw_an_extranamespaces', array('nsId' => $id, 'nsName' => $role, 'public' => '1'));
    DBFunctions::insert('mw_an_extranamespaces', array('nsId' => $id+1, 'nsName' => $role.'_Talk', 'public' => '1'));
    $id += 2;
    DBFunctions::insert('mw_an_extranamespaces', array('nsId' => $id, 'nsName' => $role.'_Wiki', 'public' => '0'));
    $id += 2;
}

User::createNew("Admin.User1", array('password' => User::crypt("Admin.Pass1"), 'email' => "admin.user1@behat-test.com"));
User::createNew("Manager.User1", array('password' => User::crypt("Manager.Pass1"), 'email' => "manager.user1@behat-test.com"));
User::createNew("Staff.User1", array('password' => User::crypt("Staff.Pass1"), 'email' => "staff.user1@behat-test.com"));
User::createNew("PL.User1", array('password' => User::crypt("PL.Pass1"), 'email' => "pl.user1@behat-test.com"));
User::createNew("PL.User2", array('password' => User::crypt("PL.Pass2"), 'email' => "pl.user2@behat-test.com"));
User::createNew("TL.User1", array('password' => User::crypt("TL.Pass1"), 'email' => "tl.user1@behat-test.com"));
User::createNew("TC.User1", array('password' => User::crypt("TC.Pass1"), 'email' => "tc.user1@behat-test.com"));
User::createNew("RMC.User1", array('password' => User::crypt("RMC.Pass1"), 'email' => "rmc.user1@behat-test.com"));
User::createNew("RMC.User2", array('password' => User::crypt("RMC.Pass2"), 'email' => "rmc.user2@behat-test.com"));
User::createNew("CHAMP.User1", array('password' => User::crypt("CHAMP.Pass1"), 'email' => "champ.user1@behat-test.com"));
User::createNew("CHAMP.User2", array('password' => User::crypt("CHAMP.Pass2"), 'email' => "champ.user2@behat-test.com"));
User::createNew("NI.User1", array('password' => User::crypt("NI.Pass1"), 'email' => "ni.user1@behat-test.com"));
User::createNew("NI.User2", array('password' => User::crypt("NI.Pass2"), 'email' => "ni.user2@behat-test.com"));
User::createNew("NI.User3", array('password' => User::crypt("NI.Pass3"), 'email' => "ni.user3@behat-test.com"));
User::createNew("HQP.User1", array('password' => User::crypt("HQP.Pass1"), 'email' => "hqp.user1@behat-test.com"));
User::createNew("HQP.User2", array('password' => User::crypt("HQP.Pass2"), 'email' => "hqp.user2@behat-test.com"));
User::createNew("HQP.User3", array('password' => User::crypt("HQP.Pass3"), 'email' => "hqp.user3@behat-test.com"));
User::createNew("HQP.User4", array('password' => User::crypt("HQP.Pass4"), 'email' => "hqp.user4@behat-test.com"));
User::createNew("Already.Existing", array('password' => User::crypt("Already.Existing1"), 'email' => "already.existing@behat-test.com"));
User::createNew("Üšër.WìthÁççénts", array('password' => User::crypt("Üšër WìthÁççénts"), 'email' => "ÜšërWìthÁççénts@behat-test.com"));
User::createNew("HQP.ToBeInactivated", array('password' => User::crypt("HQP.ToBeInactivated"), 'email' => "HQP.ToBeInactivated@behat-test.com"));

DBFunctions::insert('grand_roles',
                    array('user_id' => 1,
                          'role' => 'Admin',
                          'start_date' => '0000-00-00 00:00:00',
                          'end_date' => '0000-00-00 00:00:00'));
DBFunctions::insert('mw_user_groups',
                    array('ug_user' => 1,
                          'ug_group' => 'bureaucrat'));
DBFunctions::insert('mw_user_groups',
                    array('ug_user' => 1,
                          'ug_group' => 'sysop'));
$wgUser = User::newFromName("Admin.User1");

createProject("Phase1Project1", "Phase 1 Project 1", "Active", "Research", "No", 1, "2010-01-01", "", "", "");
createProject("Phase1Project2", "Phase 1 Project 2", "Active", "Research", "No", 1, "2010-01-01", "", "", "");
createProject("Phase1Project3", "Phase 1 Project 3", "Active", "Research", "No", 1, "2010-01-01", "", "", "");
createProject("Phase1Project4", "Phase 1 Project 4", "Active", "Research", "No", 1, "2011-01-01", "", "", "");
createProject("Phase1Project5", "Phase 1 Project 5", "Active", "Research", "No", 1, "2012-01-01", "", "", "");
createProject("Phase2Project1", "Phase 2 Project 1", "Active", "Research", "No", 2, "2014-04-01", "", "", "", "Theme6", 0);
    createProject("Phase2Project1SubProject1", "Phase 2 Project 1 Sub Project 1", "Active", "Research", "No", 2, "2014-04-01", "", "", "", "Not Specified", Project::newFromName("Phase2Project1")->getId());
    createProject("Phase2Project1SubProject2", "Phase 2 Project 1 Sub Project 2", "Active", "Research", "No", 2, "2014-04-01", "", "", "", "Not Specified", Project::newFromName("Phase2Project1")->getId());
createProject("Phase2Project2", "Phase 2 Project 2", "Active", "Research", "No", 2, "2014-04-01", "", "", "", "Theme1", 0);
createProject("Phase2Project3", "Phase 2 Project 3", "Active", "Research", "No", 2, "2014-04-01", "", "", "", "Theme1", 0);
    createProject("Phase2Project3SubProject1", "Phase 2 Project 3 Sub Project 1", "Active", "Research", "No", 2, "2014-04-01", "", "", "", "Not Specified", Project::newFromName("Phase2Project3")->getId());
createProject("Phase2Project4", "Phase 2 Project 4", "Active", "Research", "No", 2, "2014-04-01", "", "", "", "Theme3", 0);
createProject("Phase2Project5", "Phase 2 Project 5", "Active", "Research", "No", 2, "2014-04-01", "", "", "", "Theme4", 0);
createProject("Phase2BigBetProject1", "Phase 2 Big Bet Project 1", "Active", "Research", "Yes", 2, "2014-04-01", "", "", "", "Theme5", 0);

addUserRole("Manager.User1", MANAGER);
addUserRole("Staff.User1", STAFF);
addUserRole("PL.User1", CI);
addUserRole("TL.User1", CI);
addUserRole("RMC.User1", RMC);
addUserRole("RMC.User1", CI);
addUserRole("RMC.User2", RMC);
addUserRole("CHAMP.User1", CHAMP);
addUserRole("CHAMP.User2", CHAMP);
addUserRole("NI.User1", CI);
addUserRole("NI.User2", CI);
addUserRole("NI.User3", CI);
addUserRole("HQP.User1", HQP);
addUserRole("HQP.User2", HQP);
addUserRole("HQP.User3", HQP);
addUserRole("HQP.User4", HQP);
addUserRole("HQP.ToBeInactivated", HQP);

addUserProject("NI.User1", "Phase1Project1");
addUserProject("NI.User1", "Phase1Project5");
addUserProject("NI.User1", "Phase2Project1");
addUserProject("NI.User1", "Phase2Project2");
addUserProject("NI.User1", "Phase2BigBetProject1");
addUserProject("NI.User2", "Phase2Project1");
addUserProject("NI.User3", "Phase2Project2");
addUserProject("HQP.User1", "Phase1Project1");
addUserProject("HQP.User3", "Phase2Project1");

addProjectLeader("PL.User1", "Phase2Project1");
addProjectLeader("PL.User2", "Phase2Project3");

addThemeLeader("TL.User1", "Theme1", 'False', 'False');
addThemeLeader("TC.User1", "Theme1", 'False', 'True');

addRelation("NI.User1", "HQP.User1", "Supervises");
addRelation("NI.User1", "HQP.User2", "Supervises");
addRelation("NI.User1", "HQP.ToBeInactivated", "Supervises");
addRelation("NI.User1", "NI.User2", "Works With");

?>
