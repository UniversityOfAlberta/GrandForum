<?php

    autoload_register('AddMember/Validations');

    function createForm(){
        $formContainer = new FormContainer("form_container");
        $formTable = new FormTable("form_table");
        
        $firstNameLabel = new Label("first_name_label", "First Name", "The first name of the user (cannot contain spaces)", VALIDATE_NOT_NULL);
        $firstNameField = new TextField("first_name_field", "First Name", "", VALIDATE_NOT_NULL);
        $firstNameRow = new FormTableRow("first_name_row");
        $firstNameRow->append($firstNameLabel)->append($firstNameField->attr('size', 20));
        
        $lastNameLabel = new Label("last_name_label", "Last Name", "The last name of the user (cannot contain spaces)", VALIDATE_NOT_NULL);
        $lastNameField = new TextField("last_name_field", "Last Name", "", VALIDATE_NOT_NULL);
        $lastNameRow = new FormTableRow("last_name_row");
        $lastNameRow->append($lastNameLabel)->append($lastNameField->attr('size', 20));
        $lastNameField->registerValidation(new UniqueUserValidation(VALIDATION_POSITIVE, VALIDATION_ERROR));
        
        $emailLabel = new Label("email_label", "Email", "The email address of the user", VALIDATE_NOT_NULL);
        $emailField = new EmailField("email_field", "Email", "", VALIDATE_NOT_NULL);
        $emailRow = new FormTableRow("email_row");
        $emailRow->append($emailLabel)->append($emailField);
        
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
                  ->append($captchaRow)
                  ->append($submitRow);
        
        $formContainer->append($formTable);
        return $formContainer;
    }
    
    function generateFormHTML(){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config;
        $user = Person::newFromId($wgUser->getId());
        echo "<form action='$wgServer$wgScriptPath/index.php' method='post'>\n";
        $form = createForm();
        echo $form->render();
        echo "</form>";
    }
    
    function handleSubmit(){
        global $wgUser, $wgServer, $wgScriptPath, $wgMessage, $wgGroupPermissions, $config;
        $wgUser = User::newFromId(1);
        $form = createForm();
        $status = $form->validate();
        if($status){
            $form->getElementById('first_name_field')->setPOST('wpFirstName');
            $form->getElementById('last_name_field')->setPOST('wpLastName');
            $form->getElementById('email_field')->setPOST('wpEmail');
            
            $_POST['wpFirstName'] = ucfirst($_POST['wpFirstName']);
            $_POST['wpLastName'] = ucfirst($_POST['wpLastName']);
            $_POST['wpRealName'] = "{$_POST['wpFirstName']} {$_POST['wpLastName']}";
            $_POST['wpName'] = "{$_POST['wpEmail']}";
            //$_POST['wpName'] = ucfirst(str_replace("&#39;", "", strtolower($_POST['wpFirstName']))).".".ucfirst(str_replace("&#39;", "", strtolower($_POST['wpLastName'])));
            $_POST['wpUserType'] = $config->getValue('shibDefaultRole');
            $_POST['wpSendMail'] = "true";
            
            /*if(!preg_match("/^[À-Ÿa-zA-Z\-]+\.[À-Ÿa-zA-Z\-]+$/", $_POST['wpName'])){
                $wgMessage->addError("This User Name is not in the format 'FirstName.LastName'");
            }*/
            if(!User::isValidEmailAddr($_POST['wpEmail'])){
                $wgMessage->addError("'{$_POST['wpEmail']}' is not a valid email address");
            }
            else{
                $wgGroupPermissions['*']['createaccount'] = true;
                GrandAccess::$alreadyDone = array();
                $result = APIRequest::doAction('CreateUser', false);
                $wgGroupPermissions['*']['createaccount'] = false;
                GrandAccess::$alreadyDone = array();
                if($result){
                    
                    // Don't make the user a candidate
                    DBFunctions::update('mw_user',
                                        array('candidate' => 0),
                                        array('user_name' => $_POST['wpName']));
                                        
                    $person = Person::newFromName($_POST['wpName']);
                    $user = $person->getUser();
                    
                    // Make sur ethe 'real name' is set
                    $user->setRealName($_POST['wpRealName']);
                    $user->saveSettings();
                    
                    DBFunctions::commit();        
                    $form->reset();
                    $wgMessage->addSuccess("A randomly generated password for <b>{$_POST['wpName']}</b> has been sent to <b>{$_POST['wpEmail']}</b>");
                    redirect("$wgServer$wgScriptPath");
                }
            }
        }
        generateFormHTML();
    }
    
?>
