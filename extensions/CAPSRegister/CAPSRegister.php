<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['CAPSRegister'] = 'CAPSRegister'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['CAPSRegister'] = $dir . 'CAPSRegister.i18n.php';
$wgSpecialPageGroups['CAPSRegister'] = 'network-tools';

$wgHooks['OutputPageParserOutput'][] = 'CAPSRegister::onOutputPageParserOutput';

function runCAPSRegister($par) {
    CAPSRegister::execute($par);
}

class CAPSRegister extends SpecialPage{

    static function onOutputPageParserOutput(&$out, $parseroutput){
        global $wgServer, $wgScriptPath, $config, $wgTitle;
        
        $me = Person::newFromWgUser();
        if($wgTitle->getText() == "Main Page" && $wgTitle->getNsText() == ""){ // Only show on Main Page
            if(!$me->isLoggedIn()){
                $parseroutput->mText .= "<h2>Membership Registration</h2><p>If you would like to apply to become a member in {$config->getValue('networkName')} then please fill out the <a href='$wgServer$wgScriptPath/index.php/Special:CAPSRegister'>registration form</a>.</p>";
            }
            /*else if($me->isRole(HQP.'-Candidate')){
                $parseroutput->mText .= "<h2>HQP Application</h2><p>To apply to become an Affiliate HQP in {$config->getValue('networkName')} then please fill out the <a href='{$me->getUrl()}?tab=hqp-profile'>HQP Application form</a>.</p>";
            }*/
        }
        return true;
    }

    function CAPSRegister() {
        SpecialPage::__construct("CAPSRegister", null, false, 'runCAPSRegister');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return !$person->isLoggedIn();
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        if(!isset($_POST['submit'])){
            CAPSRegister::generateFormHTML($wgOut);
        }
        else{
            CAPSRegister::handleSubmit($wgOut);
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

        $roleLabel = new Label("role_label", "Role", "The role of the user", VALIDATE_NOT_NULL);
        $roleField = new SelectBox("role_field", "Role", "Choose one..",array("Physician", "Pharmacist", "Other (specify)"), VALIDATE_NOT_NULL);
        $roleRow = new FormTableRow("role_row");
        $roleRow->append($roleLabel)->append($roleField);

        $languageLabel = new Label("language_label", "Language", "The language of the user", VALIDATE_NOT_NULL);
        $languageField = new SelectBox("language_field", "Language", "Choose one..",array("English", "French"), VALIDATE_NOT_NULL);
        $languageRow = new FormTableRow("language_row");
        $languageRow->append($languageLabel)->append($languageField);
       
        $postalcodeLabel = new Label("postalcode_label", "Postal Code", "The postalcode of the user", VALIDATE_NOT_NULL);
        $postalcodeField = new TextField("postalcode_field", "Postal Code", "", VALIDATE_NOT_NULL);
        $postalcodeRow = new FormTableRow("postalcode_row");
        $postalcodeRow->append($postalcodeLabel)->append($postalcodeField->attr('size', 20));

        $specialtyLabel = new Label("specialty_label", "Specialization", "The specialty of the user", VALIDATE_NOTHING);
        $specialtyField = new TextField("specialty_field", "Specialization", "", VALIDATE_NOTHING);
        $specialtyRow = new FormTableRow("specialty_row");
        $specialtyRow->append($specialtyLabel)->append($specialtyField);
 
        $yearsLabel = new Label("years_label", "Years of Practice", "The years of practice of the user", VALIDATE_NOTHING);
        $yearsField = new TextField("years_field", "Years of Practice", "", VALIDATE_NOTHING);
        $yearsRow = new FormTableRow("years_row");
        $yearsRow->append($yearsLabel)->append($yearsField->attr('size',5));

        $provisionLabel = new Label("provision_label", "Prior Provision of<br>Abortion Services", "The prior provision of medical or surgical abortion services of the user", VALIDATE_NOTHING);
        $provisionField = new TextField("provision_field", "Prior Provision of Abortion Services", "", VALIDATE_NOTHING);
        $provisionRow = new FormTableRow("provision_row");
        $provisionRow->append($provisionLabel)->append($provisionField);

        $fileLabel = new Label("file_label", "Proof of Certification", "The prior file of medical or surgical abortion services of the user", VALIDATE_NOTHING);
        $fileField = new FileField("file_field", "Proof of Certification", "", VALIDATE_NOTHING);
        $fileRow = new FormTableRow("file_row");
        $fileRow->append($fileLabel)->append($fileField);

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
		  ->append($roleRow)
		  ->append($languageRow)
		  ->append($postalcodeRow)
		  ->append($specialtyRow)
		  ->append($yearsRow)
		  ->append($provisionRow)
		  ->append($fileRow)
                  ->append($captchaRow)
                  ->append($submitRow);
        
        $formContainer->append($formTable);
        return $formContainer;
    }
    
     function generateFormHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config;
        $user = Person::newFromId($wgUser->getId());
        $wgOut->addHTML("Each submitted form is reviewed by an administrator. You will be contacted by email with your login details when your submission has been approved. You may need to check your spam/junk mail for the registration email.  If you do not get an email after a few business days, please contact <a href='mailto:{$config->getValue('supportEmail')}'>{$config->getValue('supportEmail')}</a>.<br /><br />");
        $wgOut->addHTML("<form action='$wgScriptPath/index.php/Special:CAPSRegister' method='post' enctype='multipart/form-data'>\n");
        $form = self::createForm();
        $wgOut->addHTML($form->render());
        $wgOut->addHTML("</form>");
    }
    
    function handleSubmit($wgOut){
        global $wgServer, $wgScriptPath, $wgMessage, $wgGroupPermissions;
        $form = self::createForm();
        $status = $form->validate();
        if($status){
            $firstname = $form->getElementById('first_name_field')->setPOST('wpFirstName');
            $lastname = $form->getElementById('last_name_field')->setPOST('wpLastName');
ST('wpEmail');
            $email = $form->getElementById('email_field')->setPOST('wpEmail');
            $role = $form->getElementById('role_field')->setPOST('wpRole');;
	    $language = $form->getElementById('language_field')->setPOST('wpLanguage');;
	    $postalcode = $form->getElementById('postalcode_field')->setPOST('wpPostalCode');;
	    $specialty = $form->getElementById('specialty_field')->setPOST('wpSpecialty');
	    $years = $form->getElementById('years_field')->setPOST('wpYears');;
	    $provision = $form->getElementById('provision_field')->setPOST('wpProvision');;
	    $file = $_FILES['file_field']['tmp_name'];
            $file_size= filesize($file);
            $handle = fopen($file, "r");
            $content = fread($handle, $file_size);
            fclose($handle);
            $content = chunk_split(base64_encode($content));
            $uid = md5(uniqid(time()));
            $name = basename($file);

            // header
            $header = "From: ".$firstname." ".$lastname." <".$email.">\r\n";
            $header .= "Reply-To: ".$email."\r\n";
            $header .= "MIME-Version: 1.0\r\n";
            $header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";

            // message & attachment
            $nmessage = "--".$uid."\r\n";
            $nmessage .= "Content-type:text/plain; charset=iso-8859-1\r\n";
            $nmessage .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $nmessage .= $_POST['wpSpecialty']."\r\n\r\n";
            $nmessage .= "--".$uid."\r\n";
            $nmessage .= "Content-Type: application/octet-stream; name=\""."credentials"."\"\r\n";
            $nmessage .= "Content-Transfer-Encoding: base64\r\n";
            $nmessage .= "Content-Disposition: attachment; filename=\""."credentials"."\"\r\n\r\n";
            $nmessage .= $content."\r\n\r\n";
            $nmessage .= "--".$uid."--";

            if (mail("rdejesus@ualberta.ca", "hi", $nmessage, $header)) {
		print_r("true");
                return true; // Or do something here
            } else {
		print_r("false");
              return false;
            }

/*
            $_POST['wpFirstName'] = ucfirst($_POST['wpFirstName']);
            $_POST['wpLastName'] = ucfirst($_POST['wpLastName']);
            $_POST['wpRealName'] = "{$_POST['wpFirstName']} {$_POST['wpLastName']}";
            $_POST['wpName'] = ucfirst(str_replace("&#39;", "", strtolower($_POST['wpFirstName']))).".".ucfirst(str_replace("&#39;", "", strtolower($_POST['wpLastName'])));
            $_POST['wpUserType'] = HQP;
            $_POST['wpSendMail'] = "true";
            $_POST['candidate'] = "1";
            
            if(!preg_match("/^[À-Ÿa-zA-Z\-]+\.[À-Ÿa-zA-Z\-]+$/", $_POST['wpName'])){
                $wgMessage->addError("This User Name is not in the format 'FirstName.LastName'");
                
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
            }*/
        }
        CAPSRegister::generateFormHTML($wgOut);
    }

}

?>
