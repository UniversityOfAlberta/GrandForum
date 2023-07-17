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
                    $parseroutput->mText .= "<h2><span class='en'>Registration</span><span class='fr'>Inscription</span></h2><p><span class='en'>If you are applying for the first time, please complete the <a href='$wgServer$wgScriptPath/index.php/Special:Register'>registration form</a>.</span><span class='fr'>Si c’est la première fois que vous soumettez une demande, veuillez compléter le <a href='$wgServer$wgScriptPath/index.php/Special:Register'>formulaire d’inscription</a>.</span></p>";
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

    function Register() {
        SpecialPage::__construct("Register", null, false);
    }
    
    function userCanExecute($user){
        return true;
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        $me = Person::newFromWgUser();
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
        $firstNameField = new TextField("first_name_field", "First Name", "", VALIDATE_NOSPACES);
        $firstNameRow = new FormTableRow("first_name_row");
        $firstNameRow->append($firstNameLabel)->append($firstNameField->attr('size', 20));
        
        $lastNameLabel = new Label("last_name_label", "<span class='en'>Last Name</span><span class='fr'>Nom</span>", "The last name of the user (cannot contain spaces)", VALIDATE_NOT_NULL);
        $lastNameField = new TextField("last_name_field", "Last Name", "", VALIDATE_NOSPACES);
        $lastNameRow = new FormTableRow("last_name_row");
        $lastNameRow->append($lastNameLabel)->append($lastNameField->attr('size', 20));
        $lastNameField->registerValidation(new UniqueUserValidation(VALIDATION_POSITIVE, VALIDATION_ERROR));
        
        $emailLabel = new Label("email_label", "<span class='en'>Email</span><span class='fr'>Courriel</span>", "The email address of the user", VALIDATE_NOT_NULL);
        $emailField = new EmailField("email_field", "Email", "", VALIDATE_NOT_NULL);
        $emailRow = new FormTableRow("email_row");
        $emailRow->append($emailLabel)->append($emailField);
        
        $captchaLabel = new Label("captcha_label", "<span class='en'>Enter Code</span><span class='fr'>Entrez le code</span>", "Enter the code you see in the image", VALIDATE_NOT_NULL);
        $captchaField = new Captcha("captcha_field", "Captcha", "", VALIDATE_NOT_NULL);
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
                  ->append($emailRow);
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
        else{
            $wgOut->addHTML("By registering with {$config->getValue('networkName')} you will be granted the role of HQP-Candidate.  You may need to check your spam/junk mail for the registration email if it doesn't show up after a few minutes.  If you still don't get the email, please contact <a href='mailto:{$config->getValue('supportEmail')}'>{$config->getValue('supportEmail')}</a>.<br /><br />");
        }
        if(count($config->getValue('hqpRegisterEmailWhitelist')) > 0){
            $wgOut->addHTML("<i><b>Note:</b> Email address must match one of the following: ".implode(", ", $config->getValue('hqpRegisterEmailWhitelist'))."</i><br /><br />");
        }
        $wgOut->addHTML("<form action='$wgScriptPath/index.php/Special:Register' method='post'>\n");
        $form = self::createForm();
        $wgOut->addHTML($form->render());
        $wgOut->addHTML("</form>");
    }
    
    function handleSubmit($wgOut){
        global $wgServer, $wgScriptPath, $wgMessage, $wgGroupPermissions, $config;
        $form = self::createForm();
        $status = $form->validate();
        if($status){
            $form->getElementById('first_name_field')->setPOST('wpFirstName');
            $form->getElementById('last_name_field')->setPOST('wpLastName');
            $form->getElementById('email_field')->setPOST('wpEmail');
            
            $_POST['wpFirstName'] = ucfirst($_POST['wpFirstName']);
            $_POST['wpLastName'] = ucfirst($_POST['wpLastName']);
            $_POST['wpRealName'] = "{$_POST['wpFirstName']} {$_POST['wpLastName']}";
            $_POST['wpName'] = ucfirst(str_replace("&#39;", "", strtolower($_POST['wpFirstName']))).".".ucfirst(str_replace("&#39;", "", strtolower($_POST['wpLastName'])));
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
            else{
                $_POST['wpUserType'] = HQP;
            }
            $_POST['wpSendMail'] = "true";
            $_POST['candidate'] = "1";
            
            $splitEmail = explode("@", $_POST['wpEmail']);
            $domain = @$splitEmail[1];
            
            if(!preg_match("/^[À-Ÿa-zA-Z\-]+\.[À-Ÿa-zA-Z\-]+$/", $_POST['wpName'])){
                $wgMessage->addError("This User Name is not in the format 'FirstName.LastName'");
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
                $wgGroupPermissions['*']['createaccount'] = true;
                GrandAccess::$alreadyDone = array();
                $result = APIRequest::doAction('CreateUser', false);
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
    function HQPRegister() {
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
