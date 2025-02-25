<?php
$dir = dirname(__FILE__) . '/';
$wgSpecialPages['AddHqp'] = 'AddHqp'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AddHqp'] = $dir . 'AddHqp.i18n.php';
$wgSpecialPageGroups['AddHqp'] = 'network-tools';

//$wgHooks['ToolboxLinks'][] = 'AddHqp::createToolboxLinks';
autoload_register('AddHqp/Validations');
require_once("$dir../AddMember/AddMember.body.php");

class AddHqp extends SpecialPage{

    function __construct() {
        parent::__construct("AddHqp", NI.'+', true);
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        $this->getOutput()->setPageTitle("Add Member");
        $user = Person::newFromId($wgUser->getId());
        if(isset($_POST['submit'])){
            $form = self::createForm();
            $status = $form->validate();
            if($status){
                $form->getElementById('first_name_field')->setPOST('wpFirstName');
                $form->getElementById('last_name_field')->setPOST('wpLastName');
                $form->getElementById('email_field')->setPOST('wpEmail');
                $form->getElementById('rel_field')->setPOST('relationship');
                $form->getElementById('university_field')->setPOST('university');
                $form->getElementById('dept_field')->setPOST('department');
                $form->getElementById('position_field')->setPOST('position');
                $form->getElementById('employee_field')->setPOST('employeeId');
                $form->getElementById('start_field')->setPOST('startDate');
                $form->getElementById('end_field')->setPOST('endDate');

                $_POST['wpFirstName'] = ucfirst($_POST['wpFirstName']);
                $_POST['wpLastName'] = ucfirst($_POST['wpLastName']);
                $_POST['wpRealName'] = "{$_POST['wpLastName']}, {$_POST['wpFirstName']}";
                $_POST['wpName'] = str_replace(" ", "", ucfirst(str_replace("&#39;", "", $_POST['wpFirstName'])).".".ucfirst(str_replace("&#39;", "", $_POST['wpLastName'])));
                $tmpName = $_POST['wpName'];
                $i = 1;
                while(count(DBFunctions::select(array('mw_user'),
                                                array('user_id'),
                                                array('LOWER(CONVERT(`user_name`, CHAR))' => EQ(strtolower($_POST['wpName']))))) > 0){
                    // Handle duplicates this way
                    $_POST['wpName'] = $tmpName.($i++);
                }
                $_POST['user_name'] = $user->getName();
                $_POST['wpUserType'] = HQP;
                $sendEmail = "false";
                $_POST['wpSendMail'] = "$sendEmail";
                $result = APIRequest::doAction('CreateUser', false);

                if($result){
                    $form->reset();
                }
                
                DBFunctions::update('mw_user',
                                    array('employee_id' => $_POST['employeeId']),
                                    array('user_name' => $_POST['wpName']));
                DBFUnctions::commit();
            }
            else{
                $wgMessage->addWarning("<div><b>WARNING:</b> If there are more than one of the same person, it may cause problems with your publications being associated with the wrong person.  Use 'Find Existing HQP' if the user already exists.</div>");
            }
            AddHqp::generateFormHTML($wgOut);
        }
        else{
            // Form not entered yet
            AddHqp::generateFormHTML($wgOut);
        }
        if(isset($_GET['embed'])){
            $wgOut->addHTML("<script type='text/javascript'>
                $('#bodyContent h1').hide();
                parent.enableAddButton();
                if($('#wgMessages div.success').text() != ''){
                    parent.closeAddHQP();
                }
                $('input[name=end_field]').parent().append('<span id=\"infinity\" style=\"font-weight:bold;font-size:18px;cursor:pointer;\" class=\"highlights-text\" title=\"Continuing\">&#8734;</span>');
                $('#infinity').click(function(){
                    $('input[name=end_field]').val(ZOT).change();
                });
                
                $('form').submit(function(){
                    _.defer(function(){
                        $('input[name=ignore_warnings]').prop('disabled', true);
                    });
                });
            </script>");
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
        $lastNameRow = new FormTableRow("last_name_row");
        $lastNameRow->append($lastNameLabel)->append($lastNameField->attr('size', 20));
        
        $emailLabel = new Label("email_label", "Email", "The email address of the user", VALIDATE_NOT_NULL);
        $emailField = new EmailField("email_field", "Email", "", VALIDATE_NOT_NULL);
        $emailField->registerValidation(new UniqueEmailValidation(VALIDATION_POSITIVE, VALIDATION_ERROR));
        $emailField->registerValidation(new UoAEmailValidation(VALIDATION_POSITIVE, VALIDATION_WARNING));        
        $emailRow = new FormTableRow("email_row");
        $emailRow->append($emailLabel)->append($emailField);
        
        $employeeLabel = new Label("employee_label", "Employee Id", "The uid of the ", VALIDATE_NOTHING);
        $employeeField = new TextField("employee_field", "Employee Id", "", VALIDATE_NOTHING);
        $employeeRow = new FormTableRow("employee_row");
        $employeeRow->append($employeeLabel)->append($employeeField->attr('size', 20));
        
        $relLabel = new Label("rel_label", "Relationship", "The relationship with this user", VALIDATE_NOTHING);
        $relField = new SelectBox("rel_field", "Relationship", "", array("", "Supervises", "Co-Supervises", "Committee Chair","Supervisory-Committee member","Examining-Committee member","Examining-Committee chair"), VALIDATE_NOTHING);
        $relRow = new FormTableRow("rel_row");
        $relRow->append($relLabel)->append($relField);
        
        $universities = Person::getAllUniversities();
        $positions = array("Other", "Graduate Student - Master of Engineering", "Graduate Student - Master's Course", "Graduate Student - Master's Thesis", "Graduate Student - Doctoral", "Post-Doctoral Fellow", "Research Associate", "Research Assistant", "Technical Assistant", "Research Internship", "Undergraduate Student", "Honors Thesis", "Visiting Student");
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
        
        $startLabel = new Label("start_label", "HQP Start Date", "The HQP's start date", VALIDATE_NOTHING);
        $startField = new CalendarField("start_field", "Start Date", "", VALIDATE_NOTHING);
        $startRow = new FormTableRow("start_row");
        $startRow->append($startLabel)->append($startField);
        
        $endLabel = new Label("end_label", "HQP End Date", "The HQP's end date", VALIDATE_NOTHING);
        $endField = new CalendarField("end_field", "End Date", "", VALIDATE_NOTHING);
        $endRow = new FormTableRow("end_row");
        $endRow->append($endLabel)->append($endField);
        
        $submitCell = new EmptyElement();
        $submitField = new SubmitButton("submit", "Submit Request", "Submit Request", VALIDATE_NOTHING);
        $submitRow = new FormTableRow("submit_row");
        $submitRow->append($submitCell)->append($submitField);
        
        $formTable->append($firstNameRow)
                  ->append($lastNameRow)
                  ->append($emailRow)
                  ->append($employeeRow)
                  ->append($relRow)
                  ->append($universityRow)
                  ->append($deptRow)
                  ->append($positionRow)
                  ->append($startRow)
                  ->append($endRow)
                  ->append($submitRow);
        
        $formContainer->append($formTable);
        return $formContainer;
    }
    
    function generateFormHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles;
        $user = Person::newFromId($wgUser->getId());
        $embed = isset($_GET['embed']) ? "?embed" : "";
        $wgOut->addHTML("<form action='$wgScriptPath/index.php/Special:AddHqp$embed' method='post'>\n");
        
        $form = self::createForm();
        $wgOut->addHTML($form->render());
        $wgOut->addHTML("</form>");
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(NI)){
            $toolbox['Tools']['links'][] = TabUtils::createToolboxLink("Add ".HQP, "$wgServer$wgScriptPath/index.php/Special:AddHqp");
        }
        return true;
    }

}

?>
