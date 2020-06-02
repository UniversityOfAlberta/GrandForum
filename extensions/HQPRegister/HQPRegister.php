<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['HQPRegister'] = 'HQPRegister'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['HQPRegister'] = $dir . 'HQPRegister.i18n.php';
$wgSpecialPageGroups['HQPRegister'] = 'network-tools';

$wgHooks['OutputPageParserOutput'][] = 'HQPRegister::onOutputPageParserOutput';

function runHQPRegister($par) {
    HQPRegister::execute($par);
}

class HQPRegister extends SpecialPage{

    static function onOutputPageParserOutput(&$out, $parseroutput){
        global $wgServer, $wgScriptPath, $config, $wgTitle;
        
        $me = Person::newFromWgUser();
        if($wgTitle->getText() == "Main Page" && $wgTitle->getNsText() == ""){ // Only show on Main Page
            if(!$me->isLoggedIn()){
                $parseroutput->mText .= "<h2>Registration</h2><p>If you would like to apply to become a member of {$config->getValue('networkName')} then please fill out the <a href='$wgServer$wgScriptPath/index.php/Special:HQPRegister'>registration form</a>.</p>";
            }
        }
        return true;
    }

    function HQPRegister() {
        SpecialPage::__construct("HQPRegister", null, false, 'runHQPRegister');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return !$person->isLoggedIn();
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        if(!isset($_POST['submit'])){
            HQPRegister::generateFormHTML($wgOut);
        }
        else{
            HQPRegister::handleSubmit($wgOut);
            return;
        }
    }
    
    function createForm(){
        $formContainer = new FormContainer("form_container");
        $formTable = new FormTable("form_table");
        
        $firstNameLabel = new Label("first_name_label", "First Name", "The first name of the user (cannot contain spaces)", VALIDATE_NOT_NULL);
        $firstNameField = new TextField("first_name_field", "First Name", "", VALIDATE_NOSPACES);
        $firstNameRow = new FormTableRow("first_name_row");
        $firstNameRow->append($firstNameLabel)->append($firstNameField->attr('size', 20));
        
        $lastNameLabel = new Label("last_name_label", "Last Name", "The last name of the user (cannot contain spaces)", VALIDATE_NOT_NULL);
        $lastNameField = new TextField("last_name_field", "Last Name", "", VALIDATE_NOSPACES);
        $lastNameRow = new FormTableRow("last_name_row");
        $lastNameRow->append($lastNameLabel)->append($lastNameField->attr('size', 20));
        $lastNameField->registerValidation(new UniqueUserValidation(VALIDATION_POSITIVE, VALIDATION_ERROR));
        
        $emailLabel = new Label("email_label", "Email", "The email address of the user", VALIDATE_NOT_NULL);
        $emailField = new EmailField("email_field", "Email", "", VALIDATE_NOT_NULL);
        $emailRow = new FormTableRow("email_row");
        $emailRow->append($emailLabel)->append($emailField);
        
        $typeLabel = new Label("type_label", "User Type", "The type of user you are", VALIDATE_NOT_NULL);
        $typeField = new SelectBox("type_field", "User Type", "", array("", "Student", "Faculty"), VALIDATE_NOT_NULL);
        $typeRow = new FormTableRow("type_row");
        $typeRow->append($typeLabel)->append($typeField);
        
        $captchaLabel = new Label("captcha_label", "Enter Code", "Enter the code you see in the image", VALIDATE_NOT_NULL);
        $captchaField = new Captcha("captcha_field", "Captcha", "", VALIDATE_NOT_NULL);
        $captchaRow = new FormTableRow("captcha_row");
        $captchaRow->append($captchaLabel)->append($captchaField);
        
        $submitCell = new EmptyElement();
        $submitField = new SubmitButton("submit", "Submit Request", "Submit Request", VALIDATE_NOTHING);
        $submitRow = new FormTableRow("submit_row");
        $submitRow->append($submitCell)->append($submitField);
        
        $formTable->append($firstNameRow)
                  ->append($lastNameRow)
                  ->append($emailRow)
                  ->append($typeRow)
                  ->append($captchaRow)
                  ->append($submitRow);
        
        $formContainer->append($formTable);
        return $formContainer;
    }
    
     function generateFormHTML($wgOut){
        global $wgServer, $wgScriptPath, $wgRoles, $config;
        $user = Person::newFromWgUser();
        $wgOut->setPageTitle("Member Registration");
        $wgOut->addHTML("By registering with {$config->getValue('networkName')} you will be granted the role of Candidate.  You may need to check your spam/junk mail for the registration email if it doesn't show up after a few minutes.  If you still don't get the email, please contact <a href='mailto:{$config->getValue('supportEmail')}'>{$config->getValue('supportEmail')}</a>.  Your account will be limited until an Admin can approve your account.<br /><br />");
        if(count($config->getValue('hqpRegisterEmailWhitelist')) > 0){
            $wgOut->addHTML("<i><b>Note:</b> Email address must match one of the following: ".implode(", ", $config->getValue('hqpRegisterEmailWhitelist'))."</i><br /><br />");
        }
        $wgOut->addHTML("<form action='$wgScriptPath/index.php/Special:HQPRegister' method='post'>\n");
        $form = self::createForm();
        $wgOut->addHTML($form->render());
        $wgOut->addHTML("</form>");
    }
    
    function handleSubmit($wgOut){
        global $wgServer, $wgScriptPath, $wgMessage, $wgGroupPermissions, $config, $wgUser;
        $form = self::createForm();
        $status = $form->validate();
        if($status){
            $form->getElementById('first_name_field')->setPOST('wpFirstName');
            $form->getElementById('last_name_field')->setPOST('wpLastName');
            $form->getElementById('email_field')->setPOST('wpEmail');
            $form->getElementById('type_field')->setPOST('userType');
            
            $_POST['wpFirstName'] = ucfirst($_POST['wpFirstName']);
            $_POST['wpLastName'] = ucfirst($_POST['wpLastName']);
            $_POST['wpRealName'] = "{$_POST['wpFirstName']} {$_POST['wpLastName']}";
            $_POST['wpName'] = ucfirst(str_replace("&#39;", "", strtolower($_POST['wpFirstName']))).".".ucfirst(str_replace("&#39;", "", strtolower($_POST['wpLastName'])));
            
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
                $wgUser = User::newFromId(1);
                $result = APIRequest::doAction('CreateUser', false);
                $wgUser = User::newFromId(0);
                $wgGroupPermissions['*']['createaccount'] = false;
                GrandAccess::$alreadyDone = array();
                if($result){
                    $form->reset();
                    $wgMessage->addSuccess("A randomly generated password for <b>{$_POST['wpName']}</b> has been sent to <b>{$_POST['wpEmail']}</b>");
                    
                    // Send Email
                    if($wgScriptPath == ""){
                        $message = "
    <p>A user has registered on <a href='{$wgServer}{$wgScriptPath}'>{$config->getValue('networkName')}</a></p>
    <br />
    <b>Name:</b> {$_POST['wpFirstName']} {$_POST['wpLastName']}<br />
    <b>Email:</b> {$_POST['wpEmail']}<br />
    <b>Type:</b> {$_POST['userType']}
    ";

                        $to = "adele_newton@cscan-infocan.ca";
                        $subject = "[{$config->getValue('networkName')}] User Registration";
                        $headers = array();
                        $headers[] = 'MIME-Version: 1.0';
                        $headers[] = 'Content-type: text/html; charset=iso-8859-1';

                        // Additional headers
                        $headers[] = "To: {$to}";
                        $headers[] = "From: {$config->getValue('networkName')} Support <{$config->getValue('supportEmail')}>";
                        
                        mail($to, $subject, $message, implode("\r\n", $headers));
                    }
                    redirect("$wgServer$wgScriptPath");
                }
            }
        }
        HQPRegister::generateFormHTML($wgOut);
    }

}

?>
