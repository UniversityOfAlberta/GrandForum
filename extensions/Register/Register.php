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

<p>If you would like access to the Catalyst or SIP Accelerator applications, please do not use the Affiliates Application instructions. Instead, please email <a href='mailto:info@agewell-nce.ca'>info@agewell-nce.ca</a>.</p>

                    <b>AGE-WELL Conference Abstracts</b><p>In order to submit a conference abstract to the AGE-WELL Conference, you must be an AGE-WELL member. Please see list below for potential membership options.</p>
<p><u>Student/Trainees:</u> If you would like to apply to become an HQP (trainee) in AGE-WELL then please fill out the <a href='$wgServer$wgScriptPath/index.php/Special:Register'>registration form</a>.</p>
<p><u>Partner Organizations/Start-ups:</u> Please email <a href='mailto:partnerships@agewell-nce.ca'>partnerships@agewell-nce.ca</a> for more information on how to partner with AGE-WELL.</p>
<p><u>Researchers:</u>  A researcher must be actively engaged in an AGE-WELL project to submit an abstract to the AGE-WELL conference. Please email <a href='mailto:info@agewell-nce.ca'>info@agewell-nce.ca</a> for information on how to apply to become a project researcher.</p>";
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
                    $parseroutput->mText .= "<h2><span class='en'>Registration</span><span class='fr'>Inscription</span></h2><p><span class='en'>If you are applying for the first time, please complete the <a href='$wgServer$wgScriptPath/index.php/Special:Register'>registration form</a>.</span><span class='fr'>Si c’est la première fois que vous soumettez une demande, veuillez compléter le <a href='$wgServer$wgScriptPath/index.php/Special:Register'>formulaire d’inscription</a>.</span></p>";
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
        global $config;
        $formContainer = new FormContainer("form_container");
        $formTable = new FormTable("form_table");
        
        $firstNameLabel = new Label("first_name_label", "<span class='en'>First Name</span><span class='fr'>Prénom</span>", "The first name of the user (cannot contain spaces)", VALIDATE_NOT_NULL);
        $firstNameField = new TextField("first_name_field", "First Name", "", VALIDATE_NOT_NULL);
        $firstNameRow = new FormTableRow("first_name_row");
        $firstNameRow->append($firstNameLabel)->append($firstNameField->attr('size', 20));
        
        $lastNameLabel = new Label("last_name_label", "<span class='en'>Last Name</span><span class='fr'>Nom</span>", "The last name of the user (cannot contain spaces)", VALIDATE_NOT_NULL);
        $lastNameField = new TextField("last_name_field", "Last Name", "", VALIDATE_NOT_NULL);
        $lastNameRow = new FormTableRow("last_name_row");
        $lastNameRow->append($lastNameLabel)->append($lastNameField->attr('size', 20));
        
        $userNameLabel = new Label("user_name_label", "<span class='en'>Username</span><span class='fr'>Username</span>", "The username", VALIDATE_NOT_NULL);
        $userNameField = new TextField("user_name_field", "Last Name", "", VALIDATE_NOT_NULL);
        $userNameRow = new FormTableRow("user_name_row");
        $userNameRow->append($userNameLabel)->append($userNameField->attr('size', 20));
        $userNameField->registerValidation(new UniqueUserValidation(VALIDATION_POSITIVE, VALIDATION_ERROR));
        
        $emailLabel = new Label("email_label", "<span class='en'>Email</span><span class='fr'>Courriel</span>", "The email address of the user", VALIDATE_NOT_NULL);
        $emailField = new EmailField("email_field", "Email", "", VALIDATE_NOT_NULL);
        $emailField->registerValidation(new UniqueEmailValidation(VALIDATION_POSITIVE, VALIDATION_ERROR));
        $emailRow = new FormTableRow("email_row");
        $emailRow->append($emailLabel)->append($emailField);
        
        // These next 5 fields for are for AVOID
        $ageOfLovedOneLabel = new Label("age_of_loved_one_label", "or Age of loved one", "The age of the loved one", VALIDATE_NOTHING);
        $ageOfLovedOneField = new TextField("age_of_loved_one_field", "Age of loved one", "", VALIDATE_NOTHING);
        $ageOfLovedOneRow = new FormTableRow("age_of_loved_one_row");
        $ageOfLovedOneRow->append($ageOfLovedOneLabel)->append($ageOfLovedOneField->attr('size', 3));
        
        $ageLabel = new Label("age_label", "Guest User Age", "The age of the user", VALIDATE_NOTHING);
        $ageField = new TextField("age_field", "Guest User Age", "", VALIDATE_NOTHING);
        $ageRow = new FormTableRow("age_row");
        $ageRow->append($ageLabel)->append($ageField->attr('size', 3));
        
        $practiceLabel = new Label("practice_label", "Practice", "The practice of the user", VALIDATE_NOT_NULL);
        $practiceField = new TextField("practice_field", "Practice", "", VALIDATE_NOT_NULL);
        $practiceRow = new FormTableRow("practice_row");
        $practiceRow->append($practiceLabel)->append($practiceField->attr('size', 20));
        
        $roleLabel = new Label("role_label", "Role", "The role of the user", VALIDATE_NOT_NULL);
        $roleField = new TextField("role_field", "Role", "", VALIDATE_NOT_NULL);
        $roleRow = new FormTableRow("role_row");
        $roleRow->append($roleLabel)->append($roleField->attr('size', 20));
        
        $hearLabel = new Label("hear_label", "How did you hear about the AVOID Frailty program?", "How did you hear about the AVOID Frailty program?", VALIDATE_NOT_NULL);
        $hearLabel->colspan = 2;
        $hearRow1 = new FormTableRow("hear_row1");
        $hearRow1->append($hearLabel);
        $hearField = new SelectBox("hear_field", "Hear", "", array("", "Canadian Frailty Network website","Poster, flyer, or pamphlet at community venue","Newspaper","Magazine or Newsletter","Healthcare practitioner","Social media","Word of mouth","Other"), VALIDATE_NOT_NULL);
        $hearRow2 = new FormTableRow("hear_row2");
        $hearRow2->append(new EmptyElement())->append($hearField);

        $typeLabel = new Label("type_label", "<span class='en'>Please select your role</span><span class='fr'>Veuillez sélectionner votre rôle</span>", "The role of user", VALIDATE_NOT_NULL);
        $typeField = new VerticalRadioBox("type_field", "Role", HQP, array(HQP => "<span class='en'>Candidate (ELITE Program Intern, PhD Fellowship Candidate)</span>
                                                                                   <span class='fr'>Candidat-e (Stagiaire du Programme ELITE, Candidat-e de bourse doctorale)</span>", 
                                                                           EXTERNAL => "<span class='en'>Host (ELITE Program Internship Host, PhD Fellowship Supervisor)</span>
                                                                                        <span class='fr'>Responsable (Responsable de stage du Programme ELITE, Superviseur-e de candidat-e de bourse doctorale)</span>"), VALIDATE_NOT_NULL);
        $typeRow = new FormTableRow("type_row");
        $typeRow->append($typeLabel)->append($typeField);
        
        $captchaLabel = new EmptyElement();
        $captchaField = new Captcha("captcha_field", "Captcha", "", VALIDATE_NOTHING);
        $captchaRow = new FormTableRow("captcha_row");
        $captchaRow->append($captchaLabel)->append($captchaField);
        
        $submitCell = new EmptyElement();
        $submitField = new SubmitButton("submit", "Submit Request", "Submit Request", VALIDATE_NOTHING);
        $submitField->buttonText = "<span class='en'>Submit Request</span>
                                    <span class='fr'>Soumettre la demande</span>";
        $submitRow = new FormTableRow("submit_row");
        $submitRow->append($submitCell)->append($submitField);
        
        $formTable->append($firstNameRow)
                  ->append($lastNameRow)
                  ->append($userNameRow)
                  ->append($emailRow);
        if($config->getValue('networkName') == 'ELITE'){
            $formTable->append($typeRow);
        }
        if($config->getValue('networkName') == 'AVOID'){
            if(isset($_GET['role']) && $_GET['role'] == "Partner"){
                $formTable->append($ageRow);
                $formTable->append($ageOfLovedOneRow);
                $formTable->append($hearRow1)->append($hearRow2);
            }
            if(isset($_GET['role']) && $_GET['role'] == "Clinician"){
                $formTable->append($practiceRow);
                $formTable->append($roleRow);
                $formTable->append($hearRow1)->append($hearRow2);
            }
        }
        $formTable->append($captchaRow)
                  ->append($submitRow);
        
        $formContainer->append($formTable);
        return $formContainer;
    }
    
     function generateFormHTML($wgOut){
        global $wgServer, $wgScriptPath, $wgRoles, $config, $wgLang;
        $user = Person::newFromWgUser();
        if($config->getValue('networkName') == "ADA" || $config->getValue('networkName') == "CFN"){
            $wgOut->setPageTitle("Member Registration");
            $wgOut->addHTML("By registering with {$config->getValue('networkName')} you will be granted the role of Candidate.  You may need to check your spam/junk mail for the registration email if it doesn't show up after a few minutes.  If you still don't get the email, please contact <a href='mailto:{$config->getValue('supportEmail')}'>{$config->getValue('supportEmail')}</a>.<br /><br />");
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
            if($wgLang->getCode() == 'en'){
                $wgOut->setPageTitle("Member Registration");
            }
            else{
                $wgOut->setPageTitle("Inscription des membres");
            }
            $wgOut->addHTML("<span class='en'>Your registration with {$config->getValue('networkName')} Program Application Portal will grant you access. You will receive a registration email within a few minutes after submission of your information. If you do not receive the registration email in your main inbox, please check your spam or junk mail folder. If you did not receive the email, please contact <a href='mailto:{$config->getValue('supportEmail')}'>{$config->getValue('supportEmail')}</a>.</span>
                            <span class='fr'>
                                Votre inscription au portail du formulaire de demande pour le Programme ELITE vous donnera accès au portail. Vous recevrez un courriel d'inscription quelques minutes après la soumission de vos informations. Si vous ne recevez pas le courriel d'inscription dans votre boîte de réception principale, veuillez vérifier votre dossier de courriels indésirables. Si vous n'avez pas reçu le courriel, veuillez contacter <a href='mailto:{$config->getValue('supportEmail')}'>{$config->getValue('supportEmail')}</a>.
                            </span><br /><br />");
        }
        else if($config->getValUE("networkName") == "AVOID"){
            $role = (isset($_GET['role']) && ($_GET['role'] == "Partner" || $_GET['role'] == "Clinician")) ? $_GET['role'] : CI; // Member
            if($role == CI){
                $wgOut->setPageTitle("Member Registration");
            }
            else if($role == "Partner"){
                $wgOut->setPageTitle("Care Partner/Guest Registration");
            }
            else if($role == "Clinician"){
                $wgOut->setPageTitle("Clinician Registration");
            }
            $wgOut->addHTML("<div class='program-body'>
                                By registering with {$config->getValue('networkName')} you will be granted the role of {$role}.  You may need to check your spam/junk mail for the registration email if it doesn't show up after a few minutes.  If you still don't get the email, please contact <a href='mailto:{$config->getValue('supportEmail')}'>{$config->getValue('supportEmail')}</a>.
                                <br /><br />
                                If completing the online registration or healthy aging assessment presents any challenges for you (such as vision problems, or an unsteady hand), program administration can complete it on your behalf over the phone. Please call 613-549-6666. Ex. 2834 to organize this.
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
            else if($config->getValue('networkName') == "ELITE"){
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
            else{
                $_POST['wpExtra'] = array();
                if($config->getValue("networkName") == "AVOID"){                    
                    $_POST['wpExtra']['ageOfLovedOne'] = @$_POST['age_of_loved_one_field'];
                    $_POST['wpExtra']['ageField'] = @$_POST['age_field'];
                    $_POST['wpExtra']['practiceField'] = @$_POST['practice_field'];
                    $_POST['wpExtra']['roleField'] = @$_POST['role_field'];
                    $_POST['wpExtra']['hearField'] = @$_POST['hear_field'];
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
                    $wgMessage->addSuccess("A randomly generated password for <b>{$_POST['wpName']}</b> has been sent to <b>{$_POST['wpEmail']}</b>");
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
