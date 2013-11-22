<?php

require_once('commandLine.inc');

function createProject($acronym, $fullName, $status, $type, $bigbet, $phase, $effective_date, $description, $problem, $solution, $challenge=0, $parent_id=0){
    $_POST['acronym'] = $acronym;
    $_POST['fullName'] = $fullName;
    $_POST['status'] = $status;
    $_POST['type'] = $type;
    $_POST['bigbet'] = $bigbet;
    $_POST['phase'] = $phase;
    $_POST['effective_date'] = $effective_date;
    $_POST['description'] = $description;
    $_POST['challenge'] = $challenge;
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

function addRelation($name1, $name2, $type){
    $_POST['name1'] = $name1;
    $_POST['name2'] = $name2;
    $_POST['type'] = $type;
    APIRequest::doAction('AddRelation', true);
}

global $wgTestDBname, $wgDBname, $wgRoles, $wgUser;

// Drop Test DB
$drop = "echo 'DROP DATABASE IF EXISTS {$wgTestDBname}; CREATE DATABASE {$wgTestDBname};' | mysql -u {$wgDBuser} -p{$wgDBpassword}";
system($drop);

// Create Test DB Structure
$dump = "mysqldump --no-data -u {$wgDBuser} -p{$wgDBpassword} {$wgDBname} -d --single-transaction | sed 's/ AUTO_INCREMENT=[0-9]*\b//' | mysql -u {$wgDBuser} -p{$wgDBpassword} {$wgTestDBname}";
system($dump);

// Copy select table data to Test DB
DBFunctions::execSQL("INSERT INTO `{$wgTestDBname}`.`grand_universities` SELECT * FROM `{$wgDBname}`.`grand_universities`", true);
DBFunctions::execSQL("INSERT INTO `{$wgTestDBname}`.`grand_positions` SELECT * FROM `{$wgDBname}`.`grand_positions`", true);
DBFunctions::execSQL("INSERT INTO `{$wgTestDBname}`.`grand_challenges` SELECT * FROM `{$wgDBname}`.`grand_challenges`", true);
DBFunctions::execSQL("INSERT INTO `{$wgTestDBname}`.`grand_disciplines_map` SELECT * FROM `{$wgDBname}`.`grand_disciplines_map`", true);
DBFunctions::execSQL("INSERT INTO `{$wgTestDBname}`.`grand_partners` SELECT * FROM `{$wgDBname}`.`grand_partners`", true);
DBFunctions::execSQL("INSERT INTO `{$wgTestDBname}`.`mw_page` SELECT * FROM `{$wgDBname}`.`mw_page` WHERE page_id < 10", true);
DBFunctions::execSQL("INSERT INTO `{$wgTestDBname}`.`mw_revision` SELECT * FROM `{$wgDBname}`.`mw_revision` WHERE rev_page < 10", true);
DBFunctions::execSQL("INSERT INTO `{$wgTestDBname}`.`mw_text` SELECT * FROM `{$wgDBname}`.`mw_text` WHERE old_id IN (SELECT rev_text_id FROM `{$wgTestDBname}`.`mw_revision`)", true);

// Start populating custom data
$wgDBname = $wgTestDBname;
$dbw = wfGetDB(DB_MASTER);
$dbr = wfGetDB(DB_SLAVE);
$dbw->open($wgDBserver, $wgDBuser, $wgDBpassword, $wgDBname);
$dbr->open($wgDBserver, $wgDBuser, $wgDBpassword, $wgDBname);

DBFunctions::$dbr = null;
DBFunctions::$dbw = null;
DBFunctions::initDB();

$id = 100;
DBFunctions::insert('mw_an_extranamespaces', array('nsId' => $id, 'nsName' => 'Cal', 'public' => '0'));
$id += 2;
DBFunctions::insert('mw_an_extranamespaces', array('nsId' => $id, 'nsName' => 'GRAND', 'public' => '1'));
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
DBFunctions::insert('mw_an_extranamespaces', array('nsId' => $id, 'nsName' => 'Multimedia_Story', 'public' => '1'));
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

User::createNew("Admin.User1", array('password' => User::crypt("Admin.Pass1"), 'email' => "admin.user1@behat.com"));
User::createNew("Manager.User1", array('password' => User::crypt("Manager.Pass1"), 'email' => "manager.user1@behat.com"));
User::createNew("PL.User1", array('password' => User::crypt("PL.Pass1"), 'email' => "pl.user1@behat.com"));
User::createNew("COPL.User1", array('password' => User::crypt("COPL.Pass1"), 'email' => "copl.user1@behat.com"));
User::createNew("RMC.User1", array('password' => User::crypt("RMC.Pass1"), 'email' => "rmc.user1@behat.com"));
User::createNew("RMC.User2", array('password' => User::crypt("RMC.Pass2"), 'email' => "rmc.user2@behat.com"));
User::createNew("PNI.User1", array('password' => User::crypt("PNI.Pass1"), 'email' => "pni.user1@behat.com"));
User::createNew("PNI.User2", array('password' => User::crypt("PNI.Pass2"), 'email' => "pni.user2@behat.com"));
User::createNew("PNI.User3", array('password' => User::crypt("PNI.Pass3"), 'email' => "pni.user3@behat.com"));
User::createNew("CNI.User1", array('password' => User::crypt("CNI.Pass1"), 'email' => "cni.user1@behat.com"));
User::createNew("CNI.User2", array('password' => User::crypt("CNI.Pass2"), 'email' => "cni.user2@behat.com"));
User::createNew("CNI.User3", array('password' => User::crypt("CNI.Pass3"), 'email' => "cni.user3@behat.com"));
User::createNew("CNICOPL.User1", array('password' => User::crypt("CNICOPL.Pass1"), 'email' => "cnicopl.user1@behat.com"));
User::createNew("HQP.User1", array('password' => User::crypt("HQP.Pass1"), 'email' => "hqp.user1@behat.com"));
User::createNew("HQP.User2", array('password' => User::crypt("HQP.Pass2"), 'email' => "hqp.user2@behat.com"));
User::createNew("HQP.User3", array('password' => User::crypt("HQP.Pass3"), 'email' => "hqp.user3@behat.com"));
User::createNew("Already.Existing", array('password' => User::crypt("Already.Existing1"), 'email' => "already.existing@behat.com"));
User::createNew("Üšër.WìthÁççénts", array('password' => User::crypt("Üšër WìthÁççénts"), 'email' => "Üšër WìthÁççénts@behat.com"));

DBFunctions::insert('grand_roles',
                    array('user_id' => 1,
                          'role' => 'Staff',
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
createProject("Phase2Project1", "Phase 2 Project 1", "Active", "Research", "No", 2, "2014-04-01", "", "", "", 1, 0);
    createProject("Phase2Project1SubProject1", "Phase 2 Project 1 Sub Project 1", "Active", "Research", "No", 2, "2014-04-01", "", "", "", 1, Project::newFromName("Phase2Project1")->getId());
    createProject("Phase2Project1SubProject2", "Phase 2 Project 1 Sub Project 2", "Active", "Research", "No", 2, "2014-04-01", "", "", "", 1, Project::newFromName("Phase2Project1")->getId());
createProject("Phase2Project2", "Phase 2 Project 2", "Active", "Research", "No", 2, "2014-04-01", "", "", "", 1, 0);
createProject("Phase2Project3", "Phase 2 Project 3", "Active", "Research", "No", 2, "2014-04-01", "", "", "", 1, 0);
createProject("Phase2Project4", "Phase 2 Project 4", "Active", "Research", "No", 2, "2014-04-01", "", "", "", 1, 0);
createProject("Phase2Project5", "Phase 2 Project 5", "Active", "Research", "No", 2, "2014-04-01", "", "", "", 1, 0);
createProject("Phase2BigBetProject1", "Phase 2 Big Bet Project 1", "Active", "Research", "Yes", 2, "2014-04-01", "", "", "", 1, 0);

addUserRole("Manager.User1", MANAGER);
addUserRole("PL.User1", PNI);
addUserRole("COPL.User2", PNI);
addUserRole("RMC.User1", RMC);
addUserRole("RMC.User1", PNI);
addUserRole("RMC.User2", RMC);
addUserRole("PNI.User1", PNI);
addUserRole("PNI.User2", PNI);
addUserRole("PNI.User3", PNI);
addUserRole("CNI.User1", CNI);
addUserRole("CNI.User2", CNI);
addUserRole("CNI.User3", CNI);
addUserRole("CNICOPL.User1", CNI);
addUserRole("HQP.User1", HQP);
addUserRole("HQP.User2", HQP);
addUserRole("HQP.User3", HQP);

addUserProject("PNI.User1", "Phase1Project1");
addUserProject("PNI.User1", "Phase2Project1");
addUserProject("PNI.User1", "Phase2Project2");
addUserProject("PNI.User1", "Phase2BigBetProject1");
addUserProject("PNI.User2", "Phase2Project1");
addUserProject("CNI.User1", "Phase2Project1");
addUserProject("CNI.User1", "Phase2BigBetProject1");
addUserProject("CNI.User2", "Phase2Project1");
addUserProject("CNICOPL.User1", "Phase2Project1");
addUserProject("CNICOPL.User1", "Phase2Project2");
addUserProject("CNICOPL.User1", "Phase2BigBetProject1");
addUserProject("HQP.User1", "Phase1Project1");
addUserProject("HQP.User3", "Phase2Project1");

addProjectLeader("PL.User1", "Phase2Project1");
addProjectLeader("COPL.User1", "Phase2Project1");
addProjectLeader("CNICOPL.User1", "Phase2Project2", 'True');

addRelation("PNI.User1", "HQP.User1", "Supervises");
addRelation("PNI.User1", "HQP.User2", "Supervises");
addRelation("PNI.User1", "PNI.User2", "Works With");

?>
