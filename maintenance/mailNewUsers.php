<?php

require_once('commandLine.inc');
global $wgServer, $wgScriptPath;

$wgUser = User::newFromId(1);
$lines = explode("\n", file_get_contents("newUsers.csv"));

foreach($lines as $line){
    $cells = str_getcsv($line);
    if(count($cells) > 1){
        $first = trim($cells[0]);
        $last = trim($cells[1]);
        $email = strtolower(trim($cells[2]));
        $department = trim($cells[3]);
        $university = trim($cells[4]);
        $role1 = trim($cells[5]);
        $role2 = isset($cells[6]) ? trim($cells[6]) : "";
        $roles = array($role1, $role2);
        
        // Check to see if the person is deleted first
        $data = DBFunctions::select(array('mw_user'),
                                    array('deleted'),
                                    array('user_email' => "$email"));
        if(isset($data[0]) && $data[0]['deleted'] == true){
            // They were deleted, so skip them
            continue;
        }
        
        $username = str_replace("'", "", 
                    str_replace(" ", "", "$first.$last"));
        
        $person = Person::newFromEmail("$email");
        if($person == null || $person->getId() == ""){
            $person = Person::newFromName($username);
        }
        // --------------------------UNCOMMENT THIS TO CREATE USERS-------------------//

	if($person == null || $person->getId() == ""){
            // Create New User
            User::createNew($username, array('real_name' => "$first $last", 
                                             'password' => User::crypt(mt_rand()), 
                                             'email' => "$email"));
            
            Person::$namesCache = array();
            Person::$cache = array();
            Person::$rolesCache = array();
            Person::$universityCache = array();
            Person::$leaderCache = array();
            Person::$themeLeaderCache = array();
            Person::$aliasCache = array();
            Person::$authorshipCache = array();
            Person::$namesCache = array();
            Person::$idsCache = array();
            Person::$allocationsCache = array();
            Person::$disciplineMap = array();
            Person::$allPeopleCache = array();
            
            $person = Person::newFromName($username);
        }
        $project = Project::newFromName($university);
        foreach($roles as $r){
            if($r != "" && $r != PL){
                if($person->isRole($r)){
                    // Already has role, just update it
                    $myRoles = $person->getRoles();
                    
                    foreach($myRoles as $mR){
                        if($mR->getRole() == $r){
                            $role = $mR;
                        }
                    }
                }
                else{
                    $role = new Role(array());
                }
                $role->user = $person->getId();
                $role->role = $r;
                $role->startDate = date('Y-m-d');
                $role->endDate = "0000-00-00 00:00:00";
                $role->projects = array($project);
                if($role->getId() != 0 && $role->getId() != ""){
                    $role->update();
                }
                else{
                    $role->create();
                }
            }
            else if($r == PL){
                $leadership = new PersonLeadershipAPI();
                $leadership->params['id'] = $person->getId();
                $_POST['name'] = $university;
                $_POST['startDate'] = date('Y-m-d');
                $_POST['endDate'] = "0000-00-00 00:00:00";
                if($person->leadershipOf($project)){
                    // Already has role, just update it
                    $leadership->doPUT();
                }
                else{
                    $leadership->doPOST();
                }
            }
        }
        //-----------------------COMMENT ENDS HERE------------------------------//

	// Now Send the Email
        $token = $person->getUser()->getToken();
        $deleteUrl = "https://forum.cscan-infocan.ca/index.php?action=deleteUser&user={$token}";
        if($person->getUser()->getEmailAuthenticationTimestamp() != ""){
            // User is activated, skip the email
            continue;
        }
        $message = "<p>[ENGLISH]<br />
You are receiving this message because you are a faculty member in a department, faculty, or school associated with CS-Can | Info-Can. 
To facilitate future features of our mailing list (such as being able to select only the types of messages that you receive) and to provide a mechanism for individuals to vote in CS-Can | Info-Can elections, a User Account has been created for you on the CS-Can | Info-Can Forum (the back-end website for CS-Can | Info-Can). 
Information about activating your account is on the main CS-Can | Info-Can website (<a href='https://cscan-infocan.ca/members/forum/forum-user-accounts/'>https://cscan-infocan.ca/members/forum/forum-user-accounts/</a>). 
We hope that you will activate your account. 
This will allow you to vote in the 2019 election when two vacancies will be filled on the Board of Directors based on voting by individual faculty members. 
Voting will be done using the Forum. You must activate you User Account on the Forum in order to vote. 
There is a \"practice poll\" you can fill out when you activate your account so you know how voting works. 
You will receive email once the actual election is underway with instructions for voting. 
If you have any questions (or if you do not wish to have a User Account) please contact Kellogg Booth (<a href='mailto:ksbooth@cs.ubc.ca'>ksbooth@cs.ubc.ca</a>) who is on the Board of Directors for CS-Can | Info-Can and is managing the process of adding accounts.</p>

<p>[FRANÇAIS]<br />
Vous recevez ce message parce que vous êtes membre du corps professoral d'un département, d'une faculté ou d'une école associé à CS-Can | Info-Can. 
Pour faciliter les fonctionnalités futures de notre liste de diffusion (par exemple, être en mesure de sélectionner uniquement les types de messages que vous recevez) et fournir un mécanisme permettant aux personnes de voter dans CS-Can | Info-Can Elections, un compte utilisateur a été créé pour vous sur le CS-Can | Forum Info-Can (le site Web principal de CS-Can | Info-Can). 
Des informations sur l'activation de votre compte se trouvent sur le CS-Can | Site Web Info-Can (<a href='https://cscan-infocan.ca/fr/members/forum-des-membres/comptes-dutilisateurs-du-forum/'>https://cscan-infocan.ca/fr/members/forum-des-membres/comptes-dutilisateurs-du-forum/</a>). 
Nous espérons que vous activerez votre compte. 
Cela vous permettra de voter lors de l'élection de 2019, lorsque deux postes vacants seront pourvus au conseil d'administration sur la base du vote de chaque membre du corps professoral. 
Le vote se fera via le forum. 
Vous devez activer votre compte d'utilisateur sur le forum pour pouvoir voter. 
Il existe un \"sondage de pratique\" que vous pouvez remplir lorsque vous activez votre compte afin de savoir comment fonctionne le vote. 
Une fois les élections en cours, vous recevrez un courrier électronique contenant les instructions de vote. 
Si vous avez des questions (ou si vous ne souhaitez pas créer de compte utilisateur), veuillez contacter Kellogg Booth (<a href='mailto:ksbooth@cs.ubc.ca'>ksbooth@cs.ubc.ca</a>), membre du conseil d'administration de CS-Can | Info-Can et gère le processus d'ajout de comptes.</p>";			
        
        $current_encoding = mb_detect_encoding($person->getEmail(), 'auto');
        echo "{$person->getName()}: {$person->getEmail()}\n";

        $headers = array();
        // To send HTML mail, the Content-type header must be set
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=utf-8';
        $headers[] = 'From: CS-CAN Forum <support@forum.cscan-infocan.ca>';
        // UNCOMMENT THIS TO SEND EMAILS
        //mail($person->getEmail(), "CS-CAN Forum Account", $message, implode("\r\n", $headers));
    }
}

?>
