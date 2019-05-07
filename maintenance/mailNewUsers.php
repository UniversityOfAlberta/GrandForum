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
        $email = trim($cells[2]);
        $department = trim($cells[3]);
        $university = trim($cells[4]);
        $role1 = trim($cells[5]);
        $role2 = trim($cells[6]);
        $roles = array($role1, $role2);
        
        $person = Person::newFromEmail("$email");
        if($person->getId() == ""){
            $person = Person::newFromName("$first.$last");
        }
        if($person->getId() == ""){
            // Create New User
            User::createNew("$first.$last", array('real_name' => "$first $last", 
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
            
            $person = Person::newFromName("$first.$last");
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
        
        // Now Send the Email
        $token = $person->getUser()->getToken();
        $deleteUrl = "https://forum.cscan-infocan.ca/index.php?action=deleteUser&user={$token}";
        $message = "<p>Une version française suit ci-dessous.</p>
        
                    <hr />

                    <p>Welcome to the <a href='https://forum.cscan-infocan.ca'>CS-Can | Info-Can Forum</a>.</p>

                    <p>You are receiving this email because you are one of the Beta-test users for user accounts that are being set up by CS-Can | Info-Can to allow the Canadian computer science community to have a collaboration space for shared information. The Forum is an adjunct to the main CS-Can | Info-Can website that will provide additional services such as managing subscriptions to mailing lists (you can opt out of any list at any time), voting in elections, and participating in surveys or other outreach activities.</p>

                    <p>A user account has been set up for you associated with the email address to which this email has been sent.</p>

                    <p>You can activate your account by going to the login page for the <a href='https://forum.cscan-infocan.ca'>Forum</a> and clicking on the Reset Password button. This will send email to the address associated with your account that will include a custom URL that will allow you to set a password for your user account. Once you have set a password, your account will be active.</p>

                    <p>You can change the email address associated with your account once it has been activated. You can change the password at any time using the procedure above. You will be able to add information to your account profile such as your research areas of interest and other demographic data if you choose to do so.</p>

                    <p>Additional information about the Forum is available on the CS-Can | Info-Can website.</p>

                    <p><a href='https://cscan-infocan.ca/forum'>https://cscan-infocan.ca/forum</a></p>

                    <p>If you have questions about any aspect of this, please send email to ksbooth@cs.ubc.ca (Kellogg Booth, UBC computer science and member of the CS-Can | Info-Can Board of Directors).</p>

                    <p>Thank you.</p>

                    <hr />

                    <p>Bienvenue sur le <a href='https://forum.cscan-infocan.ca'>CS-Can | Forum Info-Can</a>.</p>

                    <p>Vous recevez cet e-mail car vous êtes l'un des utilisateurs du test bêta des comptes d'utilisateur en cours de configuration par CS-Can | Info-Can permettra à la communauté informatique canadienne de disposer d'un espace de collaboration pour le partage d'informations. Le Forum est un complément du principal CS-Can | Le site Web d’Info-Can qui fournira des services supplémentaires tels que la gestion des abonnements aux listes de diffusion (vous pouvez vous désinscrire de toute liste à tout moment), le vote aux élections et la participation à des sondages ou à d’autres activités de sensibilisation.</p>

                    <p>Un compte utilisateur a été configuré pour vous, associé à l'adresse de messagerie à laquelle cet e-mail a été envoyé.</p>

                    <p>Vous pouvez activer votre compte en accédant à la page de connexion du <a href='https://forum.cscan-infocan.ca'>forum</a> et en cliquant sur le bouton Réinitialiser le mot de passe. Cela enverra un courrier électronique à l'adresse associée à votre compte, qui comprendra une URL personnalisée qui vous permettra de définir un mot de passe pour votre compte d'utilisateur. Une fois que vous avez défini un mot de passe, votre compte sera actif.</p>

                    <p>Vous pouvez modifier l'adresse e-mail associée à votre compte une fois qu'il a été activé. Vous pouvez changer le mot de passe à tout moment en utilisant la procédure ci-dessus. Si vous le souhaitez, vous pourrez ajouter des informations au profil de votre compte, telles que vos domaines d’intérêt de recherche et d’autres données démographiques.</p>

                    <p>Des informations supplémentaires sur le forum sont disponibles sur le site CS-Can | Site Internet Info-Can.</p>

                    <p><a href='https://cscan-infocan.ca/forum/fr'>https://cscan-infocan.ca/forum/fr</a></p>

                    <p>Si vous avez des questions sur l'un de ces aspects, veuillez envoyer un courrier électronique à ksbooth@cs.ubc.ca (Kellogg Booth, informaticien de UBC et membre du conseil d'administration de CS-Can | Info-Can).</p>

                    <p>Je vous remercie.</p>";
        // To send HTML mail, the Content-type header must be set
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=utf-8';
        $headers[] = 'From: CS-CAN Forum <support@forum.cscan-infocan.ca>';
        //mail($person->getEmail(), "CS-CAN Forum Account", $message, implode("\r\n", $headers));
    }
}

?>
