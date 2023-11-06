<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Register'] = 'Register'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Register'] = $dir . 'Register.i18n.php';
$wgSpecialPageGroups['Register'] = 'network-tools';

$wgHooks['OutputPageParserOutput'][] = 'Register::onOutputPageParserOutput';

class Register extends SpecialPage{

    static function onOutputPageParserOutput(&$out, $parseroutput){
        global $wgServer, $wgScriptPath, $config, $wgTitle;
        
        $me = Person::newFromWgUser();
        if($wgTitle->getText() == "Main Page" && $wgTitle->getNsText() == ""){ // Only show on Main Page
            if(!$me->isLoggedIn()){
                if($config->getValue('networkName') == "AGE-WELL"){
                    $parseroutput->mText .= "<h2>HQP Affiliates Registration</h2><p>If you would like to apply to become an HQP (trainee) in {$config->getValue('networkName')} then please fill out the <a href='$wgServer$wgScriptPath/index.php/Special:Register'>registration form</a>.</p>

                    <h2>External Registration</h2><p>If you would like to apply for a Catalyst or SIP application and do not yet have an account, you can register as an 'External-Candidate' by using this <a href='$wgServer$wgScriptPath/index.php/Special:Register?role=External'>registration form</a>.</p>

                    <h2>AGE-WELL Conference Abstracts</h2><p>Abstract submissions for <i>AGEWELL2023</i> are encouraged from all stakeholders who work or have an interest in technology and aging or ‘AgeTech”. Submitters do not need to be current members of AGE-WELL (e.g. funded or affiliate researcher, HQP, startup affiliate, etc.).</p>
                    <p>Applicants must have a Forum Research Portal account to submit a conference abstract. Applicants <u>without</u> existing Forum accounts must first register as an 'External-Candidate' to access the conference abstract module by using this <a href='$wgServer$wgScriptPath/index.php/Special:Register?role=External'>registration form</a>.</p>
                    <p>Please see the <a href='https://agewell-nce.ca/wp-content/uploads/2023/07/Revised_2023-AGE-WELL-Conference-Abstract-Submissions.pdf' target='_blank'>Call for Abstracts</a> for additional information.</p>";
                }
                else if($config->getValue('networkName') == "ADA"){
                    $parseroutput->mText .= "<h2>Registration</h2><p>If you would like to apply to become a member in {$config->getValue('networkName')} then please fill out the <a href='$wgServer$wgScriptPath/index.php/Special:Register'>registration form</a>.</p>";
                }
                else if($config->getValue('networkName') == "CFN"){
                    $parseroutput->mText .= "<h2>Registration</h2><p>If you would like to apply for the KT Intent to Apply {$config->getValue('networkName')} then please fill out the <a href='$wgServer$wgScriptPath/index.php/Special:Register'>registration form</a>.</p>";
                }
                else if($config->getValue('networkName') == "IntComp"){
                    $parseroutput->mText .= "<h2>Registration</h2><p>If you would like to apply for the LOI then please fill out the <a href='$wgServer$wgScriptPath/index.php/Special:Register'>registration form</a>.</p>";
                }
                else if($config->getValue('networkName') == "MtS"){
                    $parseroutput->mText .= "<h2>New Applicant Registration</h2><p>If you are applying for the first time, please complete the <a href='$wgServer$wgScriptPath/index.php/Special:Register'>registration form</a>.</p>";
                }
                else if($config->getValue('networkName') == "ELITE"){
                    $parseroutput->mText .= "<h2><en>Registration</en><fr>Inscription</fr></h2><p><en>If you are applying for the first time, please complete the <a href='$wgServer$wgScriptPath/index.php/Special:Register'>registration form</a>.</en><fr>Si c’est la première fois que vous soumettez une demande, veuillez compléter le <a href='$wgServer$wgScriptPath/index.php/Special:Register'>formulaire d’inscription</a>.</fr></p>";
                }
                else if($config->getValue('networkName') == "AVOID"){
                    // Do Nothing
                }
                else if($config->getValue('networkName') == "IDeaS"){
                    $parseroutput->mText .= "<h2>Forum Registration</h2><p>If you would like to apply to become a member in {$config->getValue('networkName')} then please fill out the <a href='$wgServer$wgScriptPath/index.php/Special:Register'>registration form</a>.</p>";
                }
                else{
                    $parseroutput->mText .= "<h2>HQP Registration</h2><p>If you would like to apply to become an HQP in {$config->getValue('networkName')} then please fill out the <a href='$wgServer$wgScriptPath/index.php/Special:Register'>registration form</a>.</p>";
                }
            }
            /*else if($me->isRole(HQP.'-Candidate')){
                $parseroutput->mText .= "<h2>HQP Application</h2><p>To apply to become an Affiliate HQP in {$config->getValue('networkName')} then please fill out the <a href='{$me->getUrl()}?tab=hqp-profile'>HQP Application form</a>.</p>";
            }*/
        }
        return true;
    }

    function __construct() {
        SpecialPage::__construct("Register", null, false);
    }
    
    function userCanExecute($user){
        return true;
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        $me = Person::newFromWgUser();
        $this->getOutput()->setPageTitle("Registration");
        if($me->isLoggedIn()){
            $wgOut->clearHTML();
            $wgOut->setPageTitle("Account already exists");
            $wgOut->addHTML("Your account already exists.");
            return;
        }
        if(!isset($_POST['submit'])){
            Register::generateFormHTML($wgOut);
        }
        else{
            Register::handleSubmit($wgOut);
            return;
        }
    }
    
    function createForm(){
        global $config, $wgLang;
        $formContainer = new FormContainer("form_container");
        $formTable = new FormTable("form_table");
        
        $firstNameLabel = new Label("first_name_label", "<en>First Name</en><fr>Prénom</fr>", "The first name of the user (cannot contain spaces)", VALIDATE_NOT_NULL);
        $firstNameField = new TextField("first_name_field", "First Name", "", VALIDATE_NOT_NULL);
        $firstNameRow = new FormTableRow("first_name_row");
        $firstNameRow->append($firstNameLabel)->append($firstNameField->attr('size', 20));
        
        $lastNameLabel = new Label("last_name_label", "<en>Last Name</en><fr>Nom</fr>", "The last name of the user (cannot contain spaces)", VALIDATE_NOT_NULL);
        $lastNameField = new TextField("last_name_field", "Last Name", "", VALIDATE_NOT_NULL);
        $lastNameRow = new FormTableRow("last_name_row");
        $lastNameRow->append($lastNameLabel)->append($lastNameField->attr('size', 20));
        
        $userNameLabel = new Label("user_name_label", "<en>Username</en><fr>Nom d’usager</fr>", "The username", VALIDATE_NOT_NULL);
        $userNameField = new TextField("user_name_field", "Last Name", "", VALIDATE_NOT_NULL);
        $userNameRow = new FormTableRow("user_name_row");
        $userNameRow->append($userNameLabel)->append($userNameField->attr('size', 20));
        $userNameField->registerValidation(new UniqueUserValidation(VALIDATION_POSITIVE, VALIDATION_ERROR));
        
        $emailLabel = new Label("email_label", "<en>Email</en><fr>Courriel</fr>", "The email address of the user", VALIDATE_NOT_NULL);
        $emailField = new EmailField("email_field", "Email", "", VALIDATE_NOT_NULL);
        $emailField->registerValidation(new UniqueEmailValidation(VALIDATION_POSITIVE, VALIDATION_ERROR));
        $emailRow = new FormTableRow("email_row");
        $emailRow->append($emailLabel)->append($emailField->attr('size', 20));
        
        $passwordLabel = new Label("password_label", "<en>Password</en><fr>Mot de passe</fr>", "The password of the user", VALIDATE_NOT_NULL);
        $passwordField = new PasswordField("password_field", "Password", "", VALIDATE_NOT_NULL);
        $passwordRow = new FormTableRow("password_row");
        $passwordRow->append($passwordLabel)->append($passwordField);
        
        $password2Label = new Label("password2_label", "<en>Password (again)</en><fr>Mot de passe (confirmation)</fr>", "The password of the user", VALIDATE_NOT_NULL);
        $password2Field = new PasswordField("password2_field", "Password (again)", "", VALIDATE_NOT_NULL);
        $password2Row = new FormTableRow("password2_row");
        $password2Row->append($password2Label)->append($password2Field);
        
        // These next fields for are for AVOID
        $phoneLabel = new Label("phone_label", "<en>Phone Number</en><fr>Numéro de téléphone</fr>", 
                                               "<en>Phone Number</en><fr>Numéro de téléphone</fr>", VALIDATE_NOT_NULL);
        $phoneField = new TextField("phone_field", "Phone Number", "", VALIDATE_NOT_NULL);
        $phoneRow = new FormTableRow("phone_row");
        $phoneRow->append($phoneLabel)->append($phoneField->attr('size', 10));
        
        $ageOfLovedOneLabel = new Label("age_of_loved_one_label", "<en>or Age of loved one</en><fr>ou Âge de l'être cher</fr>", 
                                                                  "<en>or Age of loved one</en><fr>ou Âge de l'être cher</fr>", VALIDATE_NOTHING);
        $ageOfLovedOneField = new TextField("age_of_loved_one_field", "Age of loved one", "", VALIDATE_NOTHING);
        $ageOfLovedOneRow = new FormTableRow("age_of_loved_one_row");
        $ageOfLovedOneRow->append($ageOfLovedOneLabel)->append($ageOfLovedOneField->attr('size', 3));
        
        $ageLabel = new Label("age_label", "<en>Guest User Age</en><fr>Âge de l'utilisateur invité</fr>", 
                                           "<en>Guest User Age</en><fr>Âge de l'utilisateur invité</fr>", VALIDATE_NOTHING);
        $ageField = new TextField("age_field", "Guest User Age", "", VALIDATE_NOTHING);
        $ageRow = new FormTableRow("age_row");
        $ageRow->append($ageLabel)->append($ageField->attr('size', 3));
        
        $practiceLabel = new Label("practice_label", "<en>Practice</en><fr>Pratique</fr>", "<en>Practice</en><fr>Pratique</fr>", VALIDATE_NOT_NULL);
        $practiceField = new TextField("practice_field", "Practice", "", VALIDATE_NOT_NULL);
        $practiceRow = new FormTableRow("practice_row");
        $practiceRow->append($practiceLabel)->append($practiceField->attr('size', 20));
        
        $roleLabel = new Label("role_label", "Role", "The role of the user", VALIDATE_NOT_NULL);
        $roleField = new TextField("role_field", "Role", "", VALIDATE_NOT_NULL);
        $roleRow = new FormTableRow("role_row");
        $roleRow->append($roleLabel)->append($roleField->attr('size', 20));
        
        $hearLabel = new Label("hear_label", "<en>How did you hear about the AVOID Frailty program?</en><fr>Comment avez-vous entendu parler du programme AVOID Frailty?</fr><span style='color:red;font-weight:bold;'>*</span>", 
                                             "<en>How did you hear about the AVOID Frailty program?</en><fr>Comment avez-vous entendu parler du programme AVOID Frailty?</fr>", VALIDATE_NOTHING);
        $hearLabel->colspan = 2;
        $hearLabel->colon = "";
        $hearLabel->attr('class', 'label tooltip left-align');
        $hearLabel->attr('style', 'white-space: normal;');
        $hearRow1 = new FormTableRow("hear_row1");
        $hearRow1->append($hearLabel);
        $hearField = new SelectBox("hear_field", "Hear", "", 
                                   array("", 
                                         "Canadian Frailty Network website" => 
                                            showLanguage("Canadian Frailty Network website", 
                                                         "Site Internet du Réseau canadien des soins aux personnes fragilisées"), 
                                         "Poster, flyer, or pamphlet at community venue" => 
                                            showLanguage("Poster, flyer, or pamphlet at community venue",
                                                         "Affiche, dépliant ou brochure dans un lieu communautaire"),
                                         "Newspaper" =>
                                            showLanguage("Newspaper", 
                                                         "Journal"), 
                                         "Magazine or Newsletter" =>
                                            showLanguage("Magazine or Newsletter", 
                                                         "Magazine ou bulletin d'information"),
                                         "Healthcare practitioner" =>
                                            showLanguage("Healthcare practitioner",
                                                         "Fournisseur de soins de santé"), 
                                         "Social media" =>
                                            showLanguage("Social media",
                                                         "Médias sociaux"), 
                                         "Word of mouth" =>
                                            showLanguage("Word of mouth",
                                                         "Bouche-à-oreille"), 
                                         "Event" => 
                                            showLanguage("Event",
                                                         "Evénement"), 
                                         "Radio" =>
                                            showLanguage("Radio",
                                                         "À la radio"), 
                                         "Mail" => 
                                            showLanguage("Mail",
                                                         "Courrier"), 
                                         "Television" =>
                                            showLanguage("Television",
                                                         "Télévision"),
                                         "Bus ad" => 
                                            showLanguage("Bus ad",
                                                         "Annonce de bus"),
                                         "Other" => 
                                            showLanguage("Other",
                                                         "Autre")), VALIDATE_NOT_NULL);
        $hearField->colspan = 2;
        $hearRow2 = new FormTableRow("hear_row2");
        $hearRow2->append($hearField);
        
        $hearLocationLabel = new Label("hear_label", "<en>If you remember the location, please specify</en><fr>si vous vous souvenez de l’endroit, veuillez l’indiquer</fr>", 
                                                     "<en>If you remember the location, please specify</en><fr>si vous vous souvenez de l’endroit, veuillez l’indiquer</fr>", VALIDATE_NOTHING);
        $hearLocationLabel->colspan = 2;
        $hearLocationLabel->attr('class', 'tooltip left-align');
        $hearRow3 = new FormTableRow("hear_row3");
        $hearRow3->append($hearLocationLabel);
        $hearLocationField = new TextField("hear_location_specify", "Hear", "", VALIDATE_NOTHING);
        $hearLocationField->colspan = 2;
        $hearRow4 = new FormTableRow("hear_row4");
        $hearRow4->append($hearLocationField);
        
        $hearPlatformLabel = new Label("hear_label", "<en>Please specify platform</en><fr>veuillez indiquer la plateforme</fr>", 
                                                     "<en>Please specify platform</en><fr>veuillez indiquer la plateforme</fr>", VALIDATE_NOTHING);
        $hearPlatformLabel->colspan = 2;
        $hearPlatformLabel->attr('class', 'tooltip left-align');
        $hearRow5 = new FormTableRow("hear_row5");
        $hearRow5->append($hearPlatformLabel);
        $hearPlatformField = new VerticalRadioBox("hear_platform_specify", "Hear", "", array("Facebook", "Twitter", "LinkedIn", "Other"), VALIDATE_NOTHING);
        $hearPlatformField->colspan = 2;
        $hearRow6 = new FormTableRow("hear_row6");
        $hearRow6->append($hearPlatformField);
        
        $hearPlatformOtherLabel = new Label("hear_label", "<en>Specify</en><fr>Précisez</fr>", "<en>Specify</en><fr>Précisez</fr>", VALIDATE_NOTHING);
        $hearPlatformOtherLabel->colspan = 2;
        $hearPlatformOtherLabel->attr('class', 'tooltip left-align');
        $hearRow7 = new FormTableRow("hear_row7");
        $hearRow7->append($hearPlatformOtherLabel);
        $hearPlatformOtherField = new TextField("hear_platform_other_specify", "Hear", "", VALIDATE_NOTHING);
        $hearPlatformOtherField->colspan = 2;
        $hearRow8 = new FormTableRow("hear_row8");
        $hearRow8->append($hearPlatformOtherField);
        
        $hearOtherLabel = new Label("hear_label", "<en>Please specify</en><fr>Précisez</fr>", "<en>Please specify</en><fr>Précisez</fr>", VALIDATE_NOTHING);
        $hearOtherLabel->colspan = 2;
        $hearOtherLabel->attr('class', 'tooltip left-align');
        $hearRow9 = new FormTableRow("hear_row9");
        $hearRow9->append($hearOtherLabel);
        $hearOtherField = new TextField("hear_other_specify", "Hear", "", VALIDATE_NOTHING);
        $hearOtherField->colspan = 2;
        $hearRow10 = new FormTableRow("hear_row10");
        $hearRow10->append($hearOtherField);
        
        $handbookLabel = new Label("handbook_label", "<br />We have an AVOID Frailty Handbook that will explain the program,  help you navigate the website, and troubleshoot any problems you run into. How would you like to receive it?", "", VALIDATE_NOTHING);
        $handbookLabel->colspan = 2;
        $handbookLabel->colon = "";
        $handbookLabel->attr('class', 'label tooltip left-align');
        $handbookLabel->attr('style', 'max-width: 700px; white-space: normal;');
        $handbookRow1 = new FormTableRow("handbook_row1");
        $handbookRow1->append($handbookLabel);
        $handbookField = new VerticalCheckBox("handbook_field", "Handbook", array(), 
                                   array("Electronically via email",
                                         "Paper copy in the mail",
                                         "I do not want the handbook"), VALIDATE_NOTHING);
        $handbookField->colspan = 2;
        $handbookRow2 = new FormTableRow("handbook_row2");
        $handbookRow2->append($handbookField);
        
        $handbookAddressLabel = new Label("handbook_address_label", "Please Specify Mailing Address", "Address", VALIDATE_NOTHING);
        $handbookAddressLabel->colspan = 2;
        $handbookAddressLabel->attr('class', 'tooltip left-align');
        $handbookRow3 = new FormTableRow("handbook_row3");
        $handbookRow3->append($handbookAddressLabel);
        $handbookAddressField = new TextField("handbook_address_specify", "Address", "", VALIDATE_NOTHING);
        $handbookAddressField->colspan = 2;
        $handbookRow4 = new FormTableRow("handbook_row4");
        $handbookRow4->append($handbookAddressField);
        
        $recommendLabel = new Label("recommend_label", "<br />Was this program recommended by a Queen's Family Health Team Community Service Worker?", "", VALIDATE_NOTHING);
        $recommendLabel->colspan = 2;
        $recommendLabel->colon = "";
        $recommendLabel->attr('class', 'label tooltip left-align');
        $recommendLabel->attr('style', 'max-width: 700px; white-space: normal;');
        $recommendRow1 = new FormTableRow("recommend_row1");
        $recommendRow1->append($recommendLabel);
        $recommendField = new VerticalRadioBox("recommend_field", "Recommend", array(), 
                                   array("Yes",
                                         "No"), VALIDATE_NOTHING);
        $recommendField->colspan = 2;
        $recommendRow2 = new FormTableRow("recommend_row2");
        $recommendRow2->append($recommendField);
        
        // End AVOID Fields
        
        $typeLabel = new Label("type_label", "<en>Please select your role</en><fr>Veuillez sélectionner votre rôle</fr>", "The role of user", VALIDATE_NOT_NULL);
        $typeField = new VerticalRadioBox("type_field", "Role", HQP, array(HQP => "<en>Candidate (ELITE Program Intern, PhD Fellowship Candidate)</en>
                                                                                   <fr>Candidat-e (Stagiaire du Programme ELITE, Candidat-e de bourse doctorale)</fr>", 
                                                                           EXTERNAL => "<en>Host (ELITE Program Internship Host, PhD Fellowship Supervisor)</en>
                                                                                        <fr>Responsable (Responsable de stage du Programme ELITE, Superviseur-e de candidat-e de bourse doctorale)</fr>"), VALIDATE_NOT_NULL);
        $typeRow = new FormTableRow("type_row");
        $typeRow->append($typeLabel)->append($typeField);
        
        $captchaField = new Captcha("captcha_field", "Captcha", "", VALIDATE_NOTHING);
        $captchaField->colspan = 2;
        $captchaRow = new FormTableRow("captcha_row");
        $captchaRow->append($captchaField);
        
        $submitField = new SubmitButton("submit", "Submit Request", "Submit Request", VALIDATE_NOTHING);
        $submitField->colspan = 2;
        $submitField->buttonText = "<en>Submit Request</en>
                                    <fr>Soumettre la demande</fr>";
        $submitRow = new FormTableRow("submit_row");
        $submitRow->append($submitField);
        
        $formTable->append($firstNameRow)
                  ->append($lastNameRow)
                  ->append($userNameRow)
                  ->append($emailRow)
                  ->append($passwordRow)
                  ->append($password2Row);
        if($config->getValue('networkName') == 'ELITE'){
            $typeLabel = new Label("type_label", "<span class='en'>Please select your role</span><span class='fr'>Veuillez sélectionner votre rôle</span>", "The role of user", VALIDATE_NOT_NULL);
            $typeField = new VerticalRadioBox("type_field", "Role", HQP, array(HQP => "<span class='en'>Candidate (ELITE Program Intern, PhD Fellowship Candidate)</span>
                                                                                       <span class='fr'>Candidat-e (Stagiaire du Programme ELITE, Candidat-e de bourse doctorale)</span>", 
                                                                               EXTERNAL => "<span class='en'>Host (ELITE Program Internship Host, PhD Fellowship Supervisor)</span>
                                                                                            <span class='fr'>Responsable (Responsable de stage du Programme ELITE, Superviseur-e de candidat-e de bourse doctorale)</span>"), VALIDATE_NOT_NULL);
            $typeRow = new FormTableRow("type_row");
            $typeRow->append($typeLabel)->append($typeField);
            $formTable->append($typeRow);
        }
        else if($config->getValue('networkName') == 'AGE-WELL'){
            $typeLabel = new Label("type_label", "Please select your role", "The role of user", VALIDATE_NOT_NULL);
            $role = (isset($_GET['role']) && ($_GET['role'] == HQP || $_GET['role'] == EXTERNAL)) ? $_GET['role'] : HQP;
            $typeField = new VerticalRadioBox("type_field", "Role", $role, array(HQP => "HQP-Candidate", 
                                                                                 EXTERNAL => "External-Candidate"), VALIDATE_NOT_NULL);
            $typeRow = new FormTableRow("type_row");
            $typeRow->append($typeLabel)->append($typeField);
            $formTable->append($typeRow);
        }
        if($config->getValue('networkName') == 'AVOID'){
            $formTable->append($phoneRow);
            if(isset($_GET['role']) && $_GET['role'] == "Partner"){
                $formTable->append($ageRow);
                $formTable->append($ageOfLovedOneRow);
            }
            if(isset($_GET['role']) && $_GET['role'] == "Clinician"){
                $formTable->append($practiceRow);
                $formTable->append($roleRow);
            }
            $emptyRow = new FormTableRow("");
            $emptyRow->append(new EmptyElement());
            $formTable->append($emptyRow)
                      ->append($hearRow1)
                      ->append($hearRow2)
                      ->append($hearRow3)
                      ->append($hearRow4)
                      ->append($hearRow5)
                      ->append($hearRow6)
                      ->append($hearRow7)
                      ->append($hearRow8)
                      ->append($hearRow9)
                      ->append($hearRow10);
            if($wgLang->getCode() == "en"){
                $formTable->append($handbookRow1)
                          ->append($handbookRow2)
                          ->append($handbookRow3)
                          ->append($handbookRow4);
            }
            if($config->getValue("siteName") == "AVOID Frailty: Program for Healthy Aging"){
                $formTable->append($recommendRow1)
                          ->append($recommendRow2);
            }
        }
        $emptyRow = new FormTableRow("");
        $emptyRow->append(new EmptyElement());
        $formTable->append($emptyRow)
                  ->append($captchaRow)
                  ->append($submitRow);
        
        $formContainer->append($formTable);
        return $formContainer;
    }
    
     function generateFormHTML($wgOut){
        global $wgServer, $wgScriptPath, $wgRoles, $config;
        $user = Person::newFromWgUser();
        if($config->getValue('networkName') == "ADA" || $config->getValue('networkName') == "CFN"){
            $wgOut->setPageTitle("Member Registration");
            $wgOut->addHTML("By registering with {$config->getValue('networkName')} you will be granted the role of Candidate.  You may need to check your spam/junk mail for the registration email if it doesn't show up after a few minutes.  If you still don't get the email, please contact <a href='mailto:{$config->getValue('supportEmail')}'>{$config->getValue('supportEmail')}</a>.<br /><br />");
        }
        else if($config->getValue('networkName') == "AGE-WELL"){
            $wgOut->addHTML("By registering with {$config->getValue('networkName')} you will be granted the role of HQP-Candidate or External-Candidate.  You may need to check your spam/junk mail for the registration email if it doesn't show up after a few minutes.  If you still don't get the email, please contact <a href='mailto:{$config->getValue('supportEmail')}'>{$config->getValue('supportEmail')}</a>.<br />
             <ul>
                <li><b>HQP-Candidate:</b> Select this role if you would like to apply for an HQP (trainee) related application (ie. HQP Affiliate)</li>
                <li><b>External-Candidate:</b> Select this role if you would like to apply for a funding opportunity (ie. Catalyst or SIP Accelerator)</li>
             </ul>");
        }
        else if($config->getValue('networkName') == "IntComp"){
            $wgOut->setPageTitle("Member Registration");
            $wgOut->addHTML("By registering with {$config->getValue('networkName')} you will be granted the role of PI-Candidate.  You may need to check your spam/junk mail for the registration email if it doesn't show up after a few minutes.  If you still don't get the email, please contact <a href='mailto:{$config->getValue('supportEmail')}'>{$config->getValue('supportEmail')}</a>.<br /><br />");
        }
        else if($config->getValue('networkName') == "MtS"){
            $wgOut->setPageTitle("New Applicant Registration");
            $wgOut->addHTML("By registering with {$config->getValue('networkName')} you will be granted the role of Applicant.  You may need to check your spam/junk mail for the registration email if it doesn't show up after a few minutes.  If you still don't get the email, please contact <a href='mailto:{$config->getValue('supportEmail')}'>{$config->getValue('supportEmail')}</a>.<br />
            Applicants may register using their institutional email address only. For permission to use a non .ca email address, please contact <a href='mailto:mtsfunding@yorku.ca'>mtsfunding@yorku.ca</a>.<br /><br />");
        }
        else if($config->getValue('networkName') == 'ELITE'){
            $wgOut->setPageTitle(showLanguage("Member Registration", "Inscription des membres"));
            $wgOut->addHTML("<en>Your registration with {$config->getValue('networkName')} Program Application Portal will grant you access. You will receive a registration email within a few minutes after submission of your information. If you do not receive the registration email in your main inbox, please check your spam or junk mail folder. If you did not receive the email, please contact <a href='mailto:{$config->getValue('supportEmail')}'>{$config->getValue('supportEmail')}</a>.</en>
                            <fr>
                                Votre inscription au portail du formulaire de demande pour le Programme ELITE vous donnera accès au portail. Vous recevrez un courriel d'inscription quelques minutes après la soumission de vos informations. Si vous ne recevez pas le courriel d'inscription dans votre boîte de réception principale, veuillez vérifier votre dossier de courriels indésirables. Si vous n'avez pas reçu le courriel, veuillez contacter <a href='mailto:{$config->getValue('supportEmail')}'>{$config->getValue('supportEmail')}</a>.
                            </fr><br /><br />");
        }
        else if($config->getValUE("networkName") == "AVOID"){
            $role = (isset($_GET['role']) && ($_GET['role'] == "Partner" || $_GET['role'] == "Clinician")) ? $_GET['role'] : CI; // Member
            if($role == CI){
                $wgOut->setPageTitle(showLanguage("Member Registration", "Inscription - Membre"));
            }
            else if($role == "Partner"){
                $wgOut->setPageTitle(showLanguage("Care Partner/Guest Registration", "Inscription - Partenaire/Invité"));
            }
            else if($role == "Clinician"){
                $wgOut->setPageTitle(showLanguage("Clinician Registration", "Inscription - Partenaire/Invité"));
            }
            $wgOut->addHTML("<div class='program-body'>
                                <en>By registering with {$config->getValue('networkName')} you will be granted the role of {$role}.  You may need to check your spam/junk mail for the registration email if it doesn't show up after a few minutes.  If you still don't get the email, please contact <a href='mailto:{$config->getValue('supportEmail')}'>{$config->getValue('supportEmail')}</a>.</en>
                                <fr>En vous inscrivant au site Web Proactif, vous obtenez le statut de {$role}. Consultez vos pourriels si vous ne recevez pas le courriel de confirmation d’inscription dans les prochaines minutes. Si vous n’avez toujours rien reçu, veuillez écrire aux adresses suivantes: <a href='mailto:{$config->getValue('supportEmail')}'>{$config->getValue('supportEmail')}</a>.</fr>
                                <br /><br />
                                <en>If completing the online registration or healthy aging assessment presents any challenges for you (such as vision problems, or an unsteady hand), program administration can complete it on your behalf over the phone. Please call 613-549-6666. Ex. 2834 to organize this.</en>
                                <fr>Si vous avez de la difficulté à remplir le questionnaire (par exemple, si vous avez des problèmes de vision ou que vos mains tremblent), nous pouvons le remplir pour vous. Pour organiser une rencontre téléphonique d’assistance, appelez au 418-663-5313, poste 12218.</fr>
                                <br /><br />");
        }
        else if($config->getValue('networkName') == 'IDeaS'){
            $wgOut->setPageTitle("Forum Registration");
            $wgOut->addHTML("By registering with {$config->getValue('networkName')} you will be granted the role of Member.  You may need to check your spam/junk mail for the registration email if it doesn't show up after a few minutes.  If you still don't get the email, please contact <a href='mailto:{$config->getValue('supportEmail')}'>{$config->getValue('supportEmail')}</a>.<br /><br />");
        }
        else{
            $wgOut->addHTML("By registering with {$config->getValue('networkName')} you will be granted the role of HQP-Candidate.  You may need to check your spam/junk mail for the registration email if it doesn't show up after a few minutes.  If you still don't get the email, please contact <a href='mailto:{$config->getValue('supportEmail')}'>{$config->getValue('supportEmail')}</a>.<br /><br />");
        }
        if(count($config->getValue('hqpRegisterEmailWhitelist')) > 0){
            $wgOut->addHTML("<i><b>Note:</b> Email address must match one of the following: ".implode(", ", $config->getValue('hqpRegisterEmailWhitelist'))."</i><br /><br />");
        }
        $getRole = (isset($_GET['role']) && ($_GET['role'] == "Partner" || $_GET['role'] == "Clinician")) ? "?role={$_GET['role']}" : "";
        $wgOut->addHTML("<form action='$wgScriptPath/index.php/Special:Register{$getRole}' method='post'>\n");
        $form = self::createForm();
        $wgOut->addHTML($form->render());
        $wgOut->addHTML("</form>");
        if($config->getValUE("networkName") == "AVOID"){
            $wgOut->addHTML("</div>");
        }
        $wgOut->addHTML("<script type='text/javascript'>
            $('[name=first_name_field], [name=last_name_field]').on('input', function(){
                var username = $('[name=first_name_field]').val() + '.' + $('[name=last_name_field]').val();
                username = username.replaceAll(' ', '')
                                   .replaceAll(\"'\", '');
                username = username.charAt(0).toUpperCase() + username.slice(1);
                if(!$('[name=user_name_field]').hasClass('changed')){
                    $('[name=user_name_field]').val(username);
                }
            });
            $('[name=user_name_field]').on('input', function(){
                var username = $('[name=user_name_field]').val();
                username = username.charAt(0).toUpperCase() + username.slice(1);
                $('[name=user_name_field]').val(username);
                $('[name=user_name_field]').addClass('changed');
            });
            $('#side').hide();
            $('#outerHeader').css('left', 0);
            $('#bodyContent').css('left', 0);
            
            // How did you hear about us?
            $('#hear_row3, #hear_row4').hide();
            $('#hear_row5, #hear_row6').hide();
            $('#hear_row7, #hear_row8').hide();
            $('#hear_row9, #hear_row10').hide();
            
            function specifyFrail(){
                if($(\"select[name='hear_field\").val() == 'Poster, flyer, or pamphlet at community venue'){
                    $('#hear_row3, #hear_row4').show();
                    $('#hear_row5, #hear_row6').hide();
                    $('#hear_row7, #hear_row8').hide();
                    $('#hear_row9, #hear_row10').hide();
                }
                else if($(\"select[name='hear_field\").val() == 'Social media'){
                    $('#hear_row3, #hear_row4').hide();
                    $('#hear_row5, #hear_row6').show();
                    $('#hear_row7, #hear_row8').hide();
                    $('#hear_row9, #hear_row10').hide();
                }
                else if($(\"select[name='hear_field\").val() == 'Other'){
                    $('#hear_row3, #hear_row4').hide();
                    $('#hear_row5, #hear_row6').hide();
                    $('#hear_row7, #hear_row8').hide();
                    $('#hear_row9, #hear_row10').show();
                }
                else{ 
                    $('#hear_row3, #hear_row4').hide();
                    $('#hear_row5, #hear_row6').hide();
                    $('#hear_row7, #hear_row8').hide();
                    $('#hear_row9, #hear_row10').hide();
                }
                
                if($(\"input:radio[name='hear_platform_specify']\").is(':visible') && 
                   $(\"input:radio[name='hear_platform_specify']:checked\").val() == 'Other'){
                    $('#hear_row7, #hear_row8').show();
                }
                else{ 
                    $('#hear_row7, #hear_row8').hide();
                }
                
                if($(\"input[name='handbook_field[]'][value='Paper copy in the mail']\").is(':checked')){
                    $('#handbook_row3, #handbook_row4').show();
                }
                else{
                    $('#handbook_row3, #handbook_row4').hide();
                } 
            }
            
            $(\"select[name='hear_field']\").change(specifyFrail);
            $(\"input:radio[name='hear_platform_specify']\").change(specifyFrail);
            $(\"input[name='handbook_field[]']\").change(specifyFrail);
            specifyFrail();
            
        </script>");
    }
    
    function handleSubmit($wgOut){
        global $wgServer, $wgScriptPath, $wgMessage, $wgGroupPermissions, $config, $wgUser;
        $form = self::createForm();
        $status = $form->validate();
        if($status){
            $form->getElementById('first_name_field')->setPOST('wpFirstName');
            $form->getElementById('last_name_field')->setPOST('wpLastName');
            $form->getElementById('user_name_field')->setPOST('wpName');
            $form->getElementById('email_field')->setPOST('wpEmail');
            $form->getElementById('password_field')->setPOST('wpPassword');
            $form->getElementById('password2_field')->setPOST('wpPassword2');
            
            $_POST['wpFirstName'] = ucfirst($_POST['wpFirstName']);
            $_POST['wpLastName'] = ucfirst($_POST['wpLastName']);
            $_POST['wpRealName'] = "{$_POST['wpFirstName']} {$_POST['wpLastName']}";
            $_POST['candidate'] = "1";
            if($config->getValue('networkName') == "ADA" || 
               $config->getValue('networkName') == "CFN" ||
               $config->getValue('networkName') == "MtS"){
                // No Role
            }
            else if($config->getValue('networkName') == "IntComp"){
                $_POST['wpUserType'] = CI;
            }
            else if($config->getValue('networkName') == "ELITE" || $config->getValue('networkName') == "AGE-WELL"){
                $form->getElementById('type_field')->setPOST('wpUserType');
                if($_POST['wpUserType'] != HQP && 
                   $_POST['wpUserType'] != EXTERNAL){
                    $_POST['wpUserType'] = HQP;
                }
            }
            else if($config->getValue('networkName') == "AVOID"){
                if(isset($_GET['role']) && ($_GET['role'] == "Partner" || $_GET['role'] == "Clinician")){
                    $_POST['wpUserType'] = "Provider";
                }
                else{
                    $_POST['wpUserType'] = CI;
                }
                $_POST['candidate'] = "0";
            }
            else if($config->getValue('networkName') == "IDeaS"){
                $_POST['wpUserType'] = CI;
                $_POST['candidate'] = "0";
            }
            else{
                $_POST['wpUserType'] = HQP;
            }
            $_POST['wpSendMail'] = "true";
            
            $splitEmail = explode("@", $_POST['wpEmail']);
            $domain = @$splitEmail[1];
            $_POST['wpName'] = ucfirst($_POST['wpName']);
            $emptyUser = new User();
            if(strlen($_POST['wpName']) < 5){
                $wgMessage->addError("This User Name must be atleast 5 characters long.");
            }
            else if(!preg_match("/[À-Ÿa-zA-Z]+/", $_POST['wpName'])){
                $wgMessage->addError("This User Name must include atleast 1 alphabet character.");
            }
            else if(!preg_match("/^[À-Ÿa-zA-Z\-\.0-9]+$/", $_POST['wpName'])){
                $wgMessage->addError("This User Name must only inlcude alphanumeric characters, periods and dashes.");
            }
            else if($_POST['wpFirstName'] == $_POST['wpLastName']){
                // Help filter out spam bots
                $wgMessage->addError("This is not a valid username");
            }
            else if(count($config->getValue('hqpRegisterEmailWhitelist')) > 0 &&
                    !preg_match("/".str_replace('.', '\.', implode("|", $config->getValue('hqpRegisterEmailWhitelist')))."/i", $domain)){
                $wgMessage->addError("Email address must match one of the following: ".implode(", ", $config->getValue('hqpRegisterEmailWhitelist')));
            }
            else if(!$emptyUser->isValidPassword($_POST['wpPassword'])){
                $wgMessage->addError("The password you entered is not valid");
            }
            else if($_POST['wpPassword'] != $_POST['wpPassword2']){
                $wgMessage->addError("Both passwords do not match");
            }
            else{
                $_POST['wpExtra'] = array();
                if($config->getValue("networkName") == "AVOID"){
                    $_POST['wpExtra']['phone'] = @$_POST['phone_field'];
                    $_POST['wpExtra']['ageOfLovedOne'] = @$_POST['age_of_loved_one_field'];
                    $_POST['wpExtra']['ageField'] = @$_POST['age_field'];
                    $_POST['wpExtra']['practiceField'] = @$_POST['practice_field'];
                    $_POST['wpExtra']['roleField'] = @$_POST['role_field'];
                    // How did you hear about us?
                    $_POST['wpExtra']['hearField'] = @$_POST['hear_field'];
                    $_POST['wpExtra']['hearLocationSpecify'] = @$_POST['hear_location_specify'];
                    $_POST['wpExtra']['hearPlatformSpecify'] = @$_POST['hear_platform_specify'];
                    $_POST['wpExtra']['hearPlatformOtherSpecify'] = @$_POST['hear_platform_other_specify'];
                    $_POST['wpExtra']['hearProgramOtherSpecify'] = @$_POST['hear_other_specify'];
                    // Handbook
                    $_POST['wpExtra']['handbook'] = @$_POST['handbook_field'];
                    $_POST['wpExtra']['handbookAddress'] = @$_POST['handbook_address_specify'];
                    // Recommended
                    $_POST['wpExtra']['recommended'] = @$_POST['recommend_field'];
                }
                
                $wgGroupPermissions['*']['createaccount'] = true;
                GrandAccess::$alreadyDone = array();
                $wgUser = User::newFromId(1);
                $result = APIRequest::doAction('CreateUser', false);
                $wgUser = User::newFromId(0);
                $wgGroupPermissions['*']['createaccount'] = false;
                GrandAccess::$alreadyDone = array();
                if($result){
                    $form->reset();
                    $wgMessage->addSuccess("A confirmation email for <b>{$_POST['wpName']}</b> has been sent to <b>{$_POST['wpEmail']}</b>");
                    redirect("$wgServer$wgScriptPath");
                }
            }
        }
        Register::generateFormHTML($wgOut);
    }
}

$wgSpecialPages['HQPRegister'] = 'HQPRegister'; # Let MediaWiki know about the special page.
class HQPRegister extends SpecialPage{
    function __construct() {
        SpecialPage::__construct("HQPRegister", null, false);
    }
    
    function userCanExecute($user){
        return true;
    }

    function execute($par){
        global $wgServer, $wgScriptPath;
        redirect("{$wgServer}{$wgScriptPath}/index.php/Special:Register");
    }
}

?>
