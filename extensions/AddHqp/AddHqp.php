<?php
$dir = dirname(__FILE__) . '/';
$wgSpecialPages['AddHqp'] = 'AddHqp'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AddHqp'] = $dir . 'AddHqp.i18n.php';
$wgSpecialPageGroups['AddHqp'] = 'network-tools';

$wgHooks['ToolboxLinks'][] = 'AddHqp::createToolboxLinks';
$wgHooks['SpecialPage_initList'][] = 'AddHqp::redirect';
autoload_register('AddHqp/Validations');
require_once("$dir../AddMember/AddMember.body.php");

class AddHqp extends SpecialPage{

    function AddHqp() {
            parent::__construct("AddHqp", NI.'+', true);
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        $user = Person::newFromId($wgUser->getId());
        if(isset($_POST['submit'])){
            $form = self::createForm();
            $status = $form->validate();
            if($status){
                $form->getElementById('first_name_field')->setPOST('wpFirstName');
                $form->getElementById('last_name_field')->setPOST('wpLastName');
                $form->getElementById('email_field')->setPOST('wpEmail');
                $form->getElementById('university_field')->setPOST('university');
                $form->getElementById('dept_field')->setPOST('department');
                $form->getElementById('position_field')->setPOST('position');

                $_POST['wpFirstName'] = ucfirst($_POST['wpFirstName']);
                $_POST['wpLastName'] = ucfirst($_POST['wpLastName']);
                $_POST['wpRealName'] = "{$_POST['wpFirstName']} {$_POST['wpLastName']}";
                $_POST['wpName'] = ucfirst(str_replace("&#39;", "", strtolower($_POST['wpFirstName']))).".".ucfirst(str_replace("&#39;", "", strtolower($_POST['wpLastName'])));
                $_POST['user_name'] = $user->getName();
                $_POST['wpUserType'] = HQP;
                $sendEmail = "false";
                $_POST['wpSendMail'] = "$sendEmail";
                $result = APIRequest::doAction('CreateUser', false);

                if($result){
                    $form->reset();
                }
            }
            AddHqp::generateFormHTML($wgOut);
            return;
        }
        else{
            // Form not entered yet
            AddHqp::generateFormHTML($wgOut);
        }
    }
    
    function createForm(){
        global $wgRoles, $wgUser, $config;
        $me = Person::newFromUser($wgUser);
        $formContainer = new FormContainer("form_container");
        $formTable = new FormTable("form_table");
        
        $firstNameLabel = new Label("first_name_label", "First Name", "The first name of the user (cannot contain spaces)", VALIDATE_NOT_NULL);
        $firstNameField = new TextField("first_name_field", "First Name", "", VALIDATE_NOT_NULL);
        $firstNameRow = new FormTableRow("first_name_row");
        $firstNameRow->append($firstNameLabel)->append($firstNameField->attr('size', 20));
        
        $lastNameLabel = new Label("last_name_label", "Last Name", "The last name of the user (cannot contain spaces)", VALIDATE_NOT_NULL);
        $lastNameField = new TextField("last_name_field", "Last Name", "", VALIDATE_NOT_NULL);
        $lastNameField->registerValidation(new SimilarUserValidation(VALIDATION_POSITIVE, VALIDATION_WARNING));
        $lastNameField->registerValidation(new UniqueUserValidation(VALIDATION_POSITIVE, VALIDATION_ERROR));
        $lastNameRow = new FormTableRow("last_name_row");
        $lastNameRow->append($lastNameLabel)->append($lastNameField->attr('size', 20));
        
        $emailLabel = new Label("email_label", "Email", "The email address of the user", VALIDATE_NOT_NULL);
        $emailField = new EmailField("email_field", "Email", "", VALIDATE_NOT_NULL);
        $emailField->registerValidation(new UniqueEmailValidation(VALIDATION_POSITIVE, VALIDATION_WARNING));
        $emailField->registerValidation(new UoAEmailValidation(VALIDATION_POSITIVE, VALIDATION_ERROR));        
        $emailRow = new FormTableRow("email_row");
        $emailRow->append($emailLabel)->append($emailField);
        $universities = Person::getAllUniversities();
        $positions = array("Other", "Graduate Student - Master's", "Graduate Student - Doctoral", "Post-Doctoral Fellow", "Research Associate", "Research Assistant", "Technician", "Summer Student", "Undergraduate Student");
        $departments = Person::getAllDepartments();
        $universityLabel = new Label("university_label", "Institution", "The intitution that the user is a member of", VALIDATE_NOTHING);
        $universityField = new ComboBox("university_field", "Instutution", $me->getUni(), $universities, VALIDATE_NOTHING);
        $universityField->attr("style", "width: 250px;");
        $universityRow = new FormTableRow("university_row");
        $universityRow->append($universityLabel)->append($universityField);
        
        $deptLabel = new Label("dept_label", "Department", "The department of this user", VALIDATE_NOTHING);
        $deptField = new ComboBox("dept_field", "Department", $me->getDepartment(), $departments, VALIDATE_NOTHING);
        $deptField->attr("style", "width: 250px;");
        $deptRow = new FormTableRow("dept_row");
        $deptRow->append($deptLabel)->append($deptField);
        
        $positionLabel = new Label("position_label", "HQP Academic Status", "The academic title of this user (only required for HQP)", VALIDATE_NOTHING);
        $positionField = new SelectBox("position_field", "HQP Academic Status", "", $positions, VALIDATE_NOTHING);
        $positionField->attr("style", "width: 260px;");
        $positionRow = new FormTableRow("university_row");
        $positionRow->append($positionLabel)->append($positionField);
        
        $submitCell = new EmptyElement();
        $submitField = new SubmitButton("submit", "Submit Request", "Submit Request", VALIDATE_NOTHING);
        $submitRow = new FormTableRow("submit_row");
        $submitRow->append($submitCell)->append($submitField);
        
        $formTable->append($firstNameRow)
                  ->append($lastNameRow)
                  ->append($emailRow)
                  ->append($universityRow)
                  ->append($deptRow)
                  ->append($positionRow)
                  ->append($submitRow);
        
        $formContainer->append($formTable);
        return $formContainer;
    }
    
    function generateFormHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles;
        $user = Person::newFromId($wgUser->getId());
        $wgOut->addHTML("<form action='$wgScriptPath/index.php/Special:AddHqp' method='post'>\n");
        
        $form = self::createForm();
        $wgOut->addHTML($form->render());
        $wgOut->addHTML("</form>");
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(NI)){
            $toolbox['People']['links'][] = TabUtils::createToolboxLink("Add ".HQP, "$wgServer$wgScriptPath/index.php/Special:AddHqp");
        }
        return true;
    }

    static function redirect($specialPages){
	global $wgTitle, $wgServer, $wgScriptPath;
	$person = Person::newFromWgUser();
	if($wgTitle->getNSText() == "Special" && $wgTitle->getText() == "AddMember" && !$person->isRoleAtLeast(ADMIN)){
	    redirect("$wgServer$wgScriptPath/index.php/Special:AddHqp");
	}
	return true;
    }
}

?>
