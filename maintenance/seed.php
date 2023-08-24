<?php

define('TESTING', true);

require_once("../config/Config.php");

$wgDBuser           = $config->getValue("dbUser");
$wgDBpassword       = $config->getValue("dbPassword");

// Drop Test DB
$drop = "echo 'DROP DATABASE IF EXISTS {$config->getValue('dbTestName')}; CREATE DATABASE {$config->getValue('dbTestName')};' | mysql -u {$wgDBuser} -p{$wgDBpassword} 2> /dev/null";
system($drop);

// Create Test DB Structure
$dump = "mysqldump --no-data -u {$wgDBuser} -p{$wgDBpassword} {$config->getValue('dbName')} -d --single-transaction 2> /dev/null | sed 's/ AUTO_INCREMENT=[0-9]*\b//' | mysql -u {$wgDBuser} -p{$wgDBpassword} {$config->getValue('dbTestName')} 2> /dev/null";
system($dump);

// Now load the Forum
@require_once('commandLine.inc');

use MediaWiki\MediaWikiServices;

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
    $_POST['challenge'] = array(Theme::newFromName($challenge)->getId());
    $_POST['parent_id'] = $parent_id;
    $_POST['problem'] = $problem;
    $_POST['solution'] = $solution;
    APIRequest::doAction('CreateProject', true);
}

function createProduct($title, $tags) {
    $_POST['category'] = 'Publication';
    $_POST['type'] = 'Journal Paper';
    $_POST['title'] = $title;
    $_POST['date_created'] = '2010-01-01 00:00:00';
    $_POST['date_changed'] = '2017-08-08 00:00:00';
    $_POST['status'] = 'Submitted';
    $_POST['access'] = 'Public';
    $_POST['authors'] = array();
    $_POST['projects'] = array();
    $_POST['tags'] = $tags;

    $api = new ProductAPI();
    $api->doPOST();
}

function addUserRole($name, $role, $projectNames){
    Person::$cache = array();
    Person::$namesCache = array();
    $projects = array();
    foreach($projectNames as $projectName){
        $x = new stdClass();
        $x->name = $projectName;
        $projects[] = $x;
    }
    $person = Person::newFromName($name);
    $_POST['userId'] = $person->getId();
    $_POST['name'] = $role;
    $_POST['projects'] = $projects;
    $_POST['startDate'] = '2010-01-01 00:00:00';
    $_POST['endDate'] = '0000-00-00 00:00:00';
    $_POST['comment'] = '';
    $api = new RoleAPI();
    $api->doPOST();
}

function addUserUniversity($name, $uni, $dept, $pos){
    $person = Person::newFromName($name);
    $_POST['university'] = $uni;
    $_POST['department'] = $dept;
    $_POST['position'] = $pos;
    $_POST['startDate'] = '2010-01-01 00:00:00';
    $_POST['endDate'] = '0000-00-00 00:00:00';
    $api = new PersonUniversitiesAPI();
    $api->params['id'] = $person->getId();
    $api->doPOST();
}

function addThemeLeader($name, $theme, $coLead='False', $coord='False'){
    $_POST['name'] = $name;
    $_POST['theme'] = Theme::newFromName($theme)->getId();
    $_POST['co_lead'] = $coLead;
    $_POST['coordinator'] = $coord;
    APIRequest::doAction('AddThemeLeader', true);
}

function addRelation($name1, $name2, $type){
    $person1 = Person::newFromName($name1);
    $person2 = Person::newFromName($name2);
    $_POST['user1'] = $person1->getId();
    $_POST['user2'] = $person2->getId();
    $_POST['type'] = $type;
    $_POST['startDate'] = '2010-01-01';
    $_POST['endDate'] = '0000-00-00 00:00:00';
    $_POST['comment'] = "";
    $api = new PersonRelationsAPI();
    $api->doPOST();
}

function passwordCrypt($passwd){
    return MediaWikiServices::getInstance()->getPasswordFactory()->newFromPlaintext($passwd)->toString();
}

global $wgTestDBname, $wgDBname, $wgRoles, $wgUser, $wgDBserver;

Cache::delete("*", true);

// Copy select table data to Test DB
DBFunctions::execSQL("INSERT INTO `{$config->getValue('dbTestName')}`.`grand_universities` SELECT * FROM `{$config->getValue('dbName')}`.`grand_universities`", true);
DBFunctions::execSQL("INSERT INTO `{$config->getValue('dbTestName')}`.`grand_provinces` SELECT * FROM `{$config->getValue('dbName')}`.`grand_provinces`", true);
DBFunctions::execSQL("INSERT INTO `{$config->getValue('dbTestName')}`.`grand_positions` SELECT * FROM `{$config->getValue('dbName')}`.`grand_positions`", true);
DBFunctions::execSQL("INSERT INTO `{$config->getValue('dbTestName')}`.`grand_partners` SELECT * FROM `{$config->getValue('dbName')}`.`grand_partners`", true);
DBFunctions::execSQL("INSERT INTO `{$config->getValue('dbTestName')}`.`mw_page` SELECT * FROM `{$config->getValue('dbName')}`.`mw_page` WHERE page_id < 10", true);
DBFunctions::execSQL("INSERT INTO `{$config->getValue('dbTestName')}`.`mw_revision` SELECT * FROM `{$config->getValue('dbName')}`.`mw_revision` WHERE rev_page < 10", true);
//DBFunctions::execSQL("INSERT INTO `{$config->getValue('dbTestName')}`.`mw_text` SELECT * FROM `{$config->getValue('dbName')}`.`mw_text` WHERE old_id IN (SELECT rev_text_id FROM `{$config->getValue('dbTestName')}`.`mw_revision`)", true);

// Start populating custom data

// Initialize test mailing lists in db
DBFunctions::execSQL("INSERT INTO wikidev_projects (`projectid`,`mailListName`) VALUES (1, 'test-hqps')", true);
DBFunctions::execSQL("INSERT INTO wikidev_projects (`projectid`,`mailListName`) VALUES (2, 'test-researchers')", true);
DBFunctions::execSQL("INSERT INTO wikidev_projects (`projectid`,`mailListName`) VALUES (3, 'test-leaders')", true);
DBFunctions::execSQL("INSERT INTO wikidev_projects_rules (`type`,`project_id`,`value`) VALUES ('ROLE', 1, '".HQP."')", true);
DBFunctions::execSQL("INSERT INTO wikidev_projects_rules (`type`,`project_id`,`value`) VALUES ('ROLE', 2, '".NI."')", true);
DBFunctions::execSQL("INSERT INTO wikidev_projects_rules (`type`,`project_id`,`value`) VALUES ('ROLE', 3, '".PL."')", true);

//Initialize Themes
DBFunctions::execSQL("INSERT INTO grand_themes (`acronym`,`name`,`description`,`phase`) VALUES ('Theme1', 'Theme 1', 'Theme 1 Description', 2)", true);
DBFunctions::execSQL("INSERT INTO grand_themes (`acronym`,`name`,`description`,`phase`) VALUES ('Theme2', 'Theme 2', 'Theme 2 Description', 2)", true);
DBFunctions::execSQL("INSERT INTO grand_themes (`acronym`,`name`,`description`,`phase`) VALUES ('Theme3', 'Theme 3', 'Theme 3 Description', 2)", true);
DBFunctions::execSQL("INSERT INTO grand_themes (`acronym`,`name`,`description`,`phase`) VALUES ('Theme4', 'Theme 4', 'Theme 4 Description', 2)", true);
DBFunctions::execSQL("INSERT INTO grand_themes (`acronym`,`name`,`description`,`phase`) VALUES ('Theme5', 'Theme 5', 'Theme 5 Description', 2)", true);
DBFunctions::execSQL("INSERT INTO grand_themes (`acronym`,`name`,`description`,`phase`) VALUES ('Theme6', 'Theme 6', 'Theme 6 Description', 2)", true);

//Initialize Boards
DBFunctions::execSQL("INSERT INTO grand_boards (`title`,`description`) VALUES ('General', 'General Description')", true);
DBFunctions::execSQL("INSERT INTO grand_boards (`title`,`description`) VALUES ('Other Topics', 'Other Topics Description')", true);

/*
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
*/

User::createNew("Admin.User1", array('password' => passwordCrypt("Admin.Pass1"), 'email' => "admin.user1@behat-test.com"));
User::createNew("Manager.User1", array('password' => passwordCrypt("Manager.Pass1"), 'email' => "manager.user1@behat-test.com"));
User::createNew("Staff.User1", array('password' => passwordCrypt("Staff.Pass1"), 'email' => "staff.user1@behat-test.com"));
User::createNew("PL.User1", array('password' => passwordCrypt("PL.Pass1"), 'email' => "pl.user1@behat-test.com"));
User::createNew("PL.User2", array('password' => passwordCrypt("PL.Pass2"), 'email' => "pl.user2@behat-test.com"));
User::createNew("PL.User3", array('password' => passwordCrypt("PL.Pass3"), 'email' => "pl.user3@behat-test.com"));
User::createNew("PL.User4", array('password' => passwordCrypt("PL.Pass4"), 'email' => "pl.user4@behat-test.com"));
User::createNew("TL.User1", array('password' => passwordCrypt("TL.Pass1"), 'email' => "tl.user1@behat-test.com"));
User::createNew("TC.User1", array('password' => passwordCrypt("TC.Pass1"), 'email' => "tc.user1@behat-test.com"));
User::createNew("RMC.User1", array('password' => passwordCrypt("RMC.Pass1"), 'email' => "rmc.user1@behat-test.com"));
User::createNew("RMC.User2", array('password' => passwordCrypt("RMC.Pass2"), 'email' => "rmc.user2@behat-test.com"));
User::createNew("CHAMP.User1", array('password' => passwordCrypt("CHAMP.Pass1"), 'email' => "champ.user1@behat-test.com"));
User::createNew("CHAMP.User2", array('password' => passwordCrypt("CHAMP.Pass2"), 'email' => "champ.user2@behat-test.com"));
User::createNew("NI.User1", array('password' => passwordCrypt("NI.Pass1"), 'email' => "ni.user1@behat-test.com"));
User::createNew("NI.User2", array('password' => passwordCrypt("NI.Pass2"), 'email' => "ni.user2@behat-test.com"));
User::createNew("NI.User3", array('password' => passwordCrypt("NI.Pass3"), 'email' => "ni.user3@behat-test.com"));
User::createNew("NI.User4", array('password' => passwordCrypt("NI.Pass4"), 'email' => "ni.user4@behat-test.com"));
User::createNew("NI.User5", array('password' => passwordCrypt("NI.Pass5"), 'email' => "ni.user5@behat-test.com"));
User::createNew("HQP.User1", array('password' => passwordCrypt("HQP.Pass1"), 'email' => "hqp.user1@behat-test.com"));
User::createNew("HQP.User2", array('password' => passwordCrypt("HQP.Pass2"), 'email' => "hqp.user2@behat-test.com"));
User::createNew("HQP.User3", array('password' => passwordCrypt("HQP.Pass3"), 'email' => "hqp.user3@behat-test.com"));
User::createNew("HQP.User4", array('password' => passwordCrypt("HQP.Pass4"), 'email' => "hqp.user4@behat-test.com"));
User::createNew("HQP-Candidate.User1", array('password' => passwordCrypt("HQP-Candidate.Pass1"), 'email' => "hqp-candidate.user1@behat-test.com"));
User::createNew("Already.Existing", array('password' => passwordCrypt("Already.Existing1"), 'email' => "already.existing@behat-test.com"));
User::createNew("Üšër.WìthÁççénts", array('password' => passwordCrypt("Üšër WìthÁççénts"), 'email' => "ÜšërWìthÁççénts@behat-test.com"));
User::createNew("HQP.ToBeInactivated", array('password' => passwordCrypt("HQP.ToBeInactivated"), 'email' => "HQP.ToBeInactivated@behat-test.com"));
User::createNew("Inactive.User1", array('password' => passwordCrypt("Inactive.User1"), 'email' => "Inactive.User1@behat-test.com"));
User::createNew("External.User1", array('password' => passwordCrypt("External.User1"), 'email' => "External.User1@behat-test.com"));

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

DBFunctions::update('mw_user',
                    array('candidate' => 1),
                    array('user_name' => 'HQP-Candidate.User1'));

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

addUserRole("Manager.User1", MANAGER, array());
addUserRole("Staff.User1", STAFF, array());
addUserRole("PL.User1", CI, array());
addUserRole("TL.User1", CI, array());
addUserRole("RMC.User1", RMC, array());
addUserRole("RMC.User1", CI, array());
addUserRole("RMC.User2", RMC, array());
addUserRole("CHAMP.User1", CHAMP, array());
addUserRole("CHAMP.User2", CHAMP, array());
addUserRole("NI.User1", CI, array("Phase1Project1", "Phase1Project5", "Phase2Project1", "Phase2Project2", "Phase2BigBetProject1"));
addUserRole("NI.User2", CI, array("Phase2Project1"));
addUserRole("NI.User3", CI, array("Phase2Project2"));
addUserRole("NI.User4", CI, array("Phase2Project3"));
addUserRole("NI.User5", CI, array());
addUserRole("HQP.User1", HQP, array("Phase1Project1"));
addUserRole("HQP.User2", HQP, array("Phase2Project3"));
addUserRole("HQP.User3", HQP, array("Phase2Project1"));
addUserRole("HQP.User4", HQP, array("Phase1Project1"));
addUserRole("HQP-Candidate.User1", HQP, array());
addUserRole("HQP.ToBeInactivated", HQP, array());
addUserRole("External.User1", EXTERNAL, array());
addUserRole("PL.User1", PL, array("Phase2Project1"));
addUserRole("PL.User2", PL, array("Phase2Project3"));
addUserRole("PL.User4", PL, array("Phase1Project1"));

addUserUniversity("NI.User1", "University of Alberta", "Computing Science", "Professor");
addUserUniversity("NI.User2", "University of Calgary", "Computing Science", "Professor");
addUserUniversity("NI.User3", "University of Saskatchewan", "Computing Science", "Associate Professor");
addUserUniversity("HQP.User1", "University of Alberta", "Computing Science", "Graduate Student");
addUserUniversity("HQP.User2", "University of Calgary", "Computing Science", "PhD Student");

addThemeLeader("TL.User1", "Theme1", 'False', 'False');
addThemeLeader("TC.User1", "Theme1", 'False', 'True');

addRelation("NI.User1", "HQP.User1", "Supervises");
addRelation("NI.User1", "HQP.User2", "Supervises");
addRelation("NI.User1", "HQP.ToBeInactivated", "Supervises");
addRelation("NI.User1", "NI.User2", "Works With");

createProduct("Product 1", array("tag 1", "example", "tag test"));
createProduct("Product 2", array("testing", "research"));

?>
