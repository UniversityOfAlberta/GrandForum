<?php

require_once('../../config/Config.php');

function question($question){
    echo "\n$question: ";
    return trim(fgets(STDIN));
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
    $_POST['long_description'] = "";
    $_POST['challenge'] = Theme::newFromName($challenge)->getId();
    $_POST['parent_id'] = $parent_id;
    $_POST['problem'] = $problem;
    $_POST['solution'] = $solution;
    APIRequest::doAction('CreateProject', true);
}

function addUserWebsite($name, $website){
    $_POST['user_name'] = $name;
    $_POST['website'] = $website;
    APIRequest::doAction('UserWebsite', true);
}

function addUserProfile($name, $profile){
    $_POST['user_name'] = $name;
    $_POST['profile'] = $profile;
    $_POST['type'] = 'public';
    APIRequest::doAction('UserProfile', true);
    $_POST['type'] = 'private';
    APIRequest::doAction('UserProfile', true);
}

function addUserRole($name, $role){
    Person::$cache = array();
    Person::$namesCache = array();
    $_POST['user'] = $name;
    $_POST['role'] = $role;
    APIRequest::doAction('AddRole', true);
}

function addUserUniversity($name, $university, $department, $title){
    $_POST['user'] = $name;
    $_POST['user_name'] = $name;
    $_POST['university'] = $university;
    $_POST['department'] = $department;
    $_POST['title'] = $title;
    APIRequest::doAction('UserUniversity', true);
}

function addUserProject($name, $project){
    $_POST['user'] = $name;
    $_POST['role'] = $project;
    APIRequest::doAction('AddProjectMember', true);
}

function addProjectLeader($name, $project, $coLead='False', $manager='False'){
    $_POST['user'] = $name;
    $_POST['role'] = $project;
    $_POST['project'] = $project;
    $_POST['co_lead'] = $coLead;
    $_POST['manager'] = $manager;
    APIRequest::doAction('AddProjectLeader', true);
}

echo "\nInitializing Tables...";
$link = mysqli_connect($config->getValue('dbServer'), 
                       $config->getValue('dbUser'),
                       $config->getValue('dbPassword'),
                       $config->getValue('dbName')) or die("Could not connect to database");

$sql = file_get_contents("tables.sql");
$link->multi_query($sql);
do {
    if($result = mysqli_store_result($link)){
        mysqli_free_result($result);
    }
} while(mysqli_next_result($link));

if(mysqli_error($link)) {
    die(mysqli_error($link));
}
chdir("../../symfony");
system("./bin/phinx migrate -c phinx.php");
chdir("../maintenance/install");
echo "done!\n";
require_once('../commandLine.inc');

if(question("Initialize namespaces (y/n)") == 'y'){
    DBFunctions::execSQL("TRUNCATE TABLE `mw_an_extranamespaces`", true);

    $nsId = 100;
    DBFunctions::execSQL("INSERT INTO `mw_an_extranamespaces` (`nsId`, `nsName`, `nsUser`, `public`)
                              VALUES ('".($nsId++)."', 'Cal', NULL, 0)", true);
    $nsId++;
    DBFunctions::execSQL("INSERT INTO `mw_an_extranamespaces` (`nsId`, `nsName`, `nsUser`, `public`)
                              VALUES ('".($nsId++)."', '{$config->getValue('networkName')}', NULL, 1)", true);
    $nsId++;
    DBFunctions::execSQL("INSERT INTO `mw_an_extranamespaces` (`nsId`, `nsName`, `nsUser`, `public`)
                              VALUES ('".($nsId++)."', 'Mail', NULL, 0)", true);
    $nsId++;
    DBFunctions::execSQL("INSERT INTO `mw_an_extranamespaces` (`nsId`, `nsName`, `nsUser`, `public`)
                              VALUES ('".($nsId++)."', 'Inactive', NULL, 1)", true);
    DBFunctions::execSQL("INSERT INTO `mw_an_extranamespaces` (`nsId`, `nsName`, `nsUser`, `public`)
                          VALUES ('".($nsId++)."', 'Inactive_Talk', NULL, 1)", true);
    foreach($wgRoles as $role){
        DBFunctions::execSQL("INSERT INTO `mw_an_extranamespaces` (`nsId`, `nsName`, `nsUser`, `public`)
                              VALUES ('".($nsId++)."', '{$role}', NULL, 1)", true);
        DBFunctions::execSQL("INSERT INTO `mw_an_extranamespaces` (`nsId`, `nsName`, `nsUser`, `public`)
                              VALUES ('".($nsId++)."', '{$role}_Talk', NULL, 1)", true);
    }
    $structure = Product::structure();
    $productNamespaces = array_keys($structure['categories']);
    foreach($productNamespaces as $product){
        DBFunctions::execSQL("INSERT INTO `mw_an_extranamespaces` (`nsId`, `nsName`, `nsUser`, `public`)
                              VALUES ('".($nsId++)."', '{$product}', NULL, 1)", true);
        DBFunctions::execSQL("INSERT INTO `mw_an_extranamespaces` (`nsId`, `nsName`, `nsUser`, `public`)
                              VALUES ('".($nsId++)."', '{$product}_Talk', NULL, 1)", true);
    }
}
$wgUser = User::newFromName("Admin");
if($wgUser->getID() == 0){
    $email = question("What should the Admin Email be");
    $password = question("What should the Admin Password be");
    // Create Admin User

    User::createNew("Admin", array('real_name' => "Admin",
                                   'password' => User::crypt($password), 
                                   'email' => $email));
    DBFunctions::insert('grand_roles',
                        array('user_id' => 1,
                              'role' => 'Admin',
                              'start_date' => date('Y-m-d'),
                              'end_date' => '0000-00-00 00:00:00'));
    DBFunctions::insert('mw_user_groups',
                        array('ug_user' => 1,
                              'ug_group' => 'bureaucrat'));
    DBFunctions::insert('mw_user_groups',
                        array('ug_user' => 1,
                              'ug_group' => 'sysop'));
    $wgUser = User::newFromName("Admin");
}

// Creating Provinces
if(file_exists("provinces.csv")){
    if(question("Import Provinces from provinces.csv (y/n)") == 'y'){
        DBFunctions::execSQL("TRUNCATE TABLE `grand_provinces`", true);
        $lines = explode("\n", file_get_contents("provinces.csv"));
        foreach($lines as $line){
            $cells = str_getcsv($line);
            if(count($cells) > 1){
                $prov = DBFunctions::escape($cells[0]);
                $color = DBFunctions::escape($cells[1]);
                DBFunctions::execSQL("INSERT INTO `grand_provinces` (`province`,`color`)
                                      VALUES ('$prov','$color')", true); 
            }
        }
    }
    Province::$cache = array();
}

// Creating Universities
if(file_exists("universities.csv")){
    if(question("Import Universities from universities.csv (y/n)") == 'y'){
        DBFunctions::execSQL("TRUNCATE TABLE `grand_universities`", true);
        $lines = explode("\n", file_get_contents("universities.csv"));
        foreach($lines as $line){
            $cells = str_getcsv($line);
            if(count($cells) > 1){
                $uni = DBFunctions::escape($cells[0]);
                $prov = Province::newFromName(DBFunctions::escape($cells[1]));
                $lat = DBFunctions::escape($cells[2]);
                $long = DBFunctions::escape($cells[3]);
                $order = DBFunctions::escape($cells[4]);
                $default = DBFunctions::escape($cells[5]);
                DBFunctions::execSQL("INSERT INTO `grand_universities` (`university_name`,`province_id`,`latitude`,`longitude`,`order`,`default`)
                                      VALUES ('$uni','{$prov->getId()}','$lat','$long','$order','$default')", true); 
            }
        }
    }
    University::$cache = array();
}

// Creating Other Users
if(file_exists("people.csv")){
    if(question("Import People from people.csv (y/n)") == 'y'){
        DBFunctions::execSQL("TRUNCATE TABLE `grand_positions`", true);
        DBFunctions::execSQL("TRUNCATE TABLE `grand_user_university`", true);
        DBFunctions::execSQL("INSERT INTO `grand_positions` (`position`,`order`,`default`)
                              VALUES ('Other', 0, 1)", true);
        $lines = explode("\n", file_get_contents("people.csv"));
        foreach($lines as $line){
            $cells = str_getcsv($line);
            if(count($cells) > 1){
                $lname = @trim($cells[0]);
                $fname = @trim($cells[1]);
                $role = @trim($cells[2]);
                $website = @trim($cells[3]);
                $university = @trim($cells[4]);
                $department = @trim($cells[5]);
                $title = @trim($cells[6]);
                $email = @trim($cells[7]);
                $profile = @trim($cells[8]);
                $username = str_replace(" ", "", str_replace("'", "", "$fname.$lname"));
                
                User::createNew($username, array('real_name' => "$fname $lname", 
                                                 'password' => User::crypt(mt_rand()), 
                                                 'email' => $email));
                Person::$cache = array();
                Person::$namesCache = array();
                Person::$idsCache = array();
                Person::$rolesCache = array();
                addUserUniversity($username, $university, $department, $title);
                addUserRole($username, $role);
                addUserWebsite($username, $website);
                addUserProfile($username, $profile);
            }
        }
    }
}

if(file_exists("themes.csv")){
    if(question("Import Themes from themes.csv (y/n)") == 'y'){
        DBFunctions::execSQL("TRUNCATE TABLE `grand_themes`", true);
        $lines = explode("\n", file_get_contents("themes.csv"));
        foreach($lines as $line){
            $cells = str_getcsv($line);
            if(count($cells) > 1){
                $acronym = DBFunctions::escape($cells[0]);
                $name = DBFunctions::escape($cells[1]);
                $description = DBFunctions::escape($cells[2]);
                $phase = DBFunctions::escape($cells[3]);
                $color = DBFunctions::escape($cells[4]);
                if($name != ""){
                    DBFunctions::execSQL("INSERT INTO `grand_themes` (`acronym`,`name`,`description`,`phase`,`color`)
                                          VALUES('$acronym','$name','$description','$phase','$color')", true);
                }
            }
        }
    }
}

if(file_exists("projects.csv")){
    if(question("Import Projects from projects.csv (y/n)") == 'y'){
        DBFunctions::execSQL("TRUNCATE TABLE `grand_project`", true);
        DBFunctions::execSQL("TRUNCATE TABLE `grand_project_champions`", true);
        DBFunctions::execSQL("TRUNCATE TABLE `grand_project_descriptions`", true);
        DBFunctions::execSQL("TRUNCATE TABLE `grand_project_evolution`", true);
        DBFunctions::execSQL("TRUNCATE TABLE `grand_project_leaders`", true);
        DBFunctions::execSQL("TRUNCATE TABLE `grand_project_status`", true);
        DBFunctions::execSQL("TRUNCATE TABLE `grand_project_challenges`", true);
        DBFunctions::execSQL("TRUNCATE TABLE `grand_project_members`", true);
        $lines = explode("\n", file_get_contents("projects.csv"));
        foreach($lines as $line){
            $cells = str_getcsv($line);
            if(count($cells) > 1){
                $acronym = $cells[0];
                $challenge = $cells[1];
                $title = $cells[2];
                $status = $cells[3];
                $type = $cells[4];
                $description = $cells[5];
                $problem = $cells[6];
                $solution = $cells[7];
                $leaders = explode(",", $cells[8]);
                $coleaders = explode(",", $cells[9]);
                $phase = $cells[10];
                $bigBet = $cells[11];
                
                createProject($acronym, $title, $status, $type, $bigBet, $phase, date('Y-m-d'), $description, $problem, $solution, $challenge);
                foreach($leaders as $leader){
                    $pl = Person::newFromName($leader);
                    if($pl->getId() != 0){
                        addProjectLeader($pl->getName(), $acronym, 'False');
                    }
                }
                foreach($coleaders as $leader){
                    $copl = Person::newFromName($leader);
                    if($copl->getId() != 0){
                        addProjectLeader($copl->getName(), $acronym, 'True');
                    }
                }
            }
        }
    }
}
else{
    echo "\n'projects.csv' not found...skipping\n";
}

if(file_exists("project_members.csv")){
    if(question("Import Project Memberships from project_members.csv (y/n)") == 'y'){
        DBFunctions::execSQL("TRUNCATE TABLE `grand_project_members`", true);
        $lines = explode("\n", file_get_contents("project_members.csv"));
        foreach($lines as $line){
            $cells = str_getcsv($line);
            if(count($cells) > 1){
                $username = $cells[0];
                $project = $cells[1];
                if($project != ""){
                    addUserProject($username, $project);
                }
            }
        }
    }
}

echo "\nForum Successfully Installed!\n";
?>
