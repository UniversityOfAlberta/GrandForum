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

function createUser($username, $password, $email){
    $user = User::createNew($username, array('email' => $email));
    DBFunctions::update('mw_user',
                        array('user_password' => MediaWikiServices::getInstance()->getPasswordFactory()->newFromPlaintext($password)->toString()),
                        array('user_id' => EQ($user->getId())));
}

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


createUser("Admin.User1", "Admin.Pass1", "admin.user1@behat-test.com");
createUser("Manager.User1", "Manager.Pass1", "manager.user1@behat-test.com");
createUser("Staff.User1", "Staff.Pass1", "staff.user1@behat-test.com");
createUser("PL.User1", "PL.Pass1", "pl.user1@behat-test.com");
createUser("PL.User2", "PL.Pass2", "pl.user2@behat-test.com");
createUser("PL.User3", "PL.Pass3", "pl.user3@behat-test.com");
createUser("PL.User4", "PL.Pass4", "pl.user4@behat-test.com");
createUser("TL.User1", "TL.Pass1", "tl.user1@behat-test.com");
createUser("TC.User1", "TC.Pass1", "tc.user1@behat-test.com");
createUser("RMC.User1", "RMC.Pass1", "rmc.user1@behat-test.com");
createUser("RMC.User2", "RMC.Pass2", "rmc.user2@behat-test.com");
createUser("CHAMP.User1", "CHAMP.Pass1", "champ.user1@behat-test.com");
createUser("CHAMP.User2", "CHAMP.Pass2", "champ.user2@behat-test.com");
createUser("NI.User1", "NI.Pass1", "ni.user1@behat-test.com");
createUser("NI.User2", "NI.Pass2", "ni.user2@behat-test.com");
createUser("NI.User3", "NI.Pass3", "ni.user3@behat-test.com");
createUser("NI.User4", "NI.Pass4", "ni.user4@behat-test.com");
createUser("NI.User5", "NI.Pass5", "ni.user5@behat-test.com");
createUser("HQP.User1", "HQP.Pass1", "hqp.user1@behat-test.com");
createUser("HQP.User2", "HQP.Pass2", "hqp.user2@behat-test.com");
createUser("HQP.User3", "HQP.Pass3", "hqp.user3@behat-test.com");
createUser("HQP.User4", "HQP.Pass4", "hqp.user4@behat-test.com");
createUser("HQP-Candidate.User1", "HQP-Candidate.Pass1", "hqp-candidate.user1@behat-test.com");
createUser("Already.Existing", "Already.Existing1", "already.existing@behat-test.com");
createUser("Üšër.WìthÁççénts", "Üšër WìthÁççénts", "ÜšërWìthÁççénts@behat-test.com");
createUser("HQP.ToBeInactivated", "HQP.ToBeInactivated", "HQP.ToBeInactivated@behat-test.com");
createUser("Inactive.User1", "Inactive.User1", "Inactive.User1@behat-test.com");
createUser("External.User1", "External.User1", "External.User1@behat-test.com");

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
