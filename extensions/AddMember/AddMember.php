<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['AddMember'] = 'AddMember'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AddMember'] = $dir . 'AddMember.i18n.php';
$wgSpecialPageGroups['AddMember'] = 'network-tools';

$wgHooks['ToolboxLinks'][] = 'AddMember::createToolboxLinks';

autoload_register('AddMember/Validations');

class AddMember extends SpecialPage{

    function __construct() {
        parent::__construct("AddMember", NI.'+', true);
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        $this->getOutput()->setPageTitle("Add Member");
        $person = Person::newFromWgUser();
        if(!$person->isRoleAtLeast(ADMIN)){
            redirect("$wgServer$wgScriptPath/index.php/Special:AddHqp");
        }
        $user = Person::newFromId($wgUser->getId());
        if(isset($_GET['action']) && $_GET['action'] == "view" && $user->isRoleAtLeast(STAFF)){
            if(isset($_POST['submit']) && $_POST['submit'] == "Accept"){
                $request = UserCreateRequest::newFromId($_POST['id']);
                $sendEmail = "false";
                if(isset($_POST['wpEmail']) && $_POST['wpEmail'] != ""){
                    $sendEmail = "true";
                }
                $_POST['wpSendMail'] = "$sendEmail";
                $result = APIRequest::doAction('CreateUser', false);
                if(strstr($result, "already exists") === false){
                    $request->acceptRequest();
                }
            }
            else if(isset($_POST['submit']) && $_POST['submit'] == "Ignore"){
                $request = UserCreateRequest::newFromId($_POST['id']);
                $request->ignoreRequest();
            }
            AddMember::generateViewHTML($wgOut);
        }
        else if(!isset($_POST['submit'])){
            // Form not entered yet
            AddMember::generateFormHTML($wgOut);
        }
        else{
            $form = self::createForm();
            $status = $form->validate();
            if($status){
                $form->getElementById('first_name_field')->setPOST('wpFirstName');
                $form->getElementById('last_name_field')->setPOST('wpLastName');
                $form->getElementById('email_field')->setPOST('wpEmail');
                $form->getElementById('role_field')->setPOST('wpUserType');
                $form->getElementById('university_field')->setPOST('university');
                $form->getElementById('dept_field')->setPOST('department');
                $form->getElementById('position_field')->setPOST('position');
                
                if(isset($_POST['wpNS'])){
                    $nss = implode(", ", $_POST['wpNS']);
                }
                else{
                    $nss = "";
                }
                if(isset($_POST['wpUserType'])){
                    $types = implode(", ", $_POST['wpUserType']);
                }
                else{
                    $types = "";
                }
                
                $_POST['wpFirstName'] = ucfirst($_POST['wpFirstName']);
                $_POST['wpLastName'] = ucfirst($_POST['wpLastName']);
                $_POST['wpRealName'] = "{$_POST['wpFirstName']} {$_POST['wpLastName']}";
                $_POST['wpName'] = ucfirst(str_replace("&#39;", "", strtolower($_POST['wpFirstName']))).".".ucfirst(str_replace("&#39;", "", strtolower($_POST['wpLastName'])));
                $_POST['user_name'] = $user->getName();
                $_POST['wpUserType'] = $types;
                $_POST['wpNS'] = $nss;
                $result = APIRequest::doAction('RequestUser', false);
                if($result){
                    $form->reset();
                }
            }
            AddMember::generateFormHTML($wgOut);
            return;
        }
    }
    
    function generateViewHTML($wgOut){
        global $wgScriptPath, $wgServer, $config, $wgEnableEmail;
        $history = false;
        if(isset($_GET['history']) && $_GET['history'] == true){
            $history = true;
        }
        $hqpType = "";
        if(count($config->getValue('subRoles')) > 0 && !$history){
            $hqpType = "<th>Sub-Role</th>";
        }
        if($history){
            $wgOut->addHTML("<a href='$wgServer$wgScriptPath/index.php/Special:AddMember?action=view'>View New Requests</a><br /><br />
                        <table id='requests' style='display:none;background:#ffffff;text-align:center;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
                        <thead><tr bgcolor='#F2F2F2'>
                            <th>Requesting User</th>
                            <th>User Name</th>
                            <th>Timestamp</th>
                            <th>Staff</th>
                            <th>User Type</th>
                            <th>Institution</th>
                            <th>Action</th>
                        </tr></thead><tbody>\n");
        }
        else{
            $wgOut->addHTML("<a href='$wgServer$wgScriptPath/index.php/Special:AddMember?action=view&history=true'>View History</a><br /><br />
                        <table id='requests' style='display:none;background:#ffffff;text-align:center;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
                        <thead><tr bgcolor='#F2F2F2'>
                            <th>Requesting User</th>
                            <th>User Name</th>
                            <th>Timestamp</th>
                            <th>User Type</th>
                            <th>Institution</th>
                            {$hqpType}
                            <th>Action</th>
                        </tr></thead><tbody>\n");
        }
    
        $requests = UserCreateRequest::getAllRequests($history);
        foreach($requests as $request){
            $req_user = $request->getRequestingUser();
            $projs = array();
            $roles = array();
            if($req_user->getRoles() != null){
                foreach($req_user->getRoles() as $role){
                    $roles[] = $role->getRole();
                }
            }
            $wgOut->addHTML("<tr><form action='$wgServer$wgScriptPath/index.php/Special:AddMember?action=view' method='post'>
                        <td align='left'>
                            <a target='_blank' href='{$req_user->getUrl()}'><b>{$req_user->getName()}</b></a> (".implode(",", $roles).")
                        </td>");
            if($history && $request->isCreated()){
                $user = Person::newFromName($request->getName());
                $wgOut->addHTML("<td align='left'><a target='_blank' href='{$user->getUrl()}'>{$request->getName()}</a></td>");
            }
            else{
                $wgOut->addHTML("<td align='left'>{$request->getName()}<br />{$request->getEmail()}</td>");
            } 
            $wgOut->addHTML("<td>".str_replace(" ", "<br />", $request->getLastModified())."</td>");
            if($history){
                $wgOut->addHTML("<td><a target='_blank' href='{$request->getAcceptedBy()->getUrl()}'>{$request->getAcceptedBy()->getName()}</a></td>");
            }
            $wgOut->addHTML("<td>{$request->getRoles()}</td>
                             <td>{$request->getUniversity()}<br />
                                 {$request->getDepartment()}<br />
                                 {$request->getPosition()}</td> ");
            if(count($config->getValue('subRoles')) > 0 && !$history){
                $wgOut->addHTML("<td align='left' style='white-space:nowrap;'>");
                foreach($config->getValue('subRoles') as $subRole => $fullSubRole){
                    $wgOut->addHTML("<input type='checkbox' name='subtype[]' value='{$subRole}' />{$fullSubRole}<br />");
                }
                $wgOut->addHTML("</td>");
            }
            $wpSendMail = ($wgEnableEmail) ? "true" : "false";
            $wgOut->addHTML("<input type='hidden' name='id' value='{$request->getId()}' />
                             <input type='hidden' name='wpName' value='{$request->getName()}' />
                             <input type='hidden' name='wpEmail' value='{$request->getEmail()}' />
                             <input type='hidden' name='wpRealName' value='{$request->getRealName()}' />
                             <input type='hidden' name='wpUserType' value='{$request->getRoles()}' />
                             <input type='hidden' name='university' value='".str_replace("'", "&#39;", $request->getUniversity())."' />
                             <input type='hidden' name='department' value='".str_replace("'", "&#39;", $request->getDepartment())."' />
                             <input type='hidden' name='position' value='".str_replace("'", "&#39;", $request->getPosition())."' />
                             <input type='hidden' name='wpSendMail' value='$wpSendMail' />");
            if($history){
                if($request->isCreated()){
                    $wgOut->addHTML("<td>Accepted</td>");
                }
                else{
                    $wgOut->addHTML("<td>Ignored</td>");
                }
            }
            else{
                $wgOut->addHTML("<td><input type='submit' name='submit' value='Accept' /><br /><input type='submit' name='submit' value='Ignore' /></td>");
            }
            $wgOut->addHTML("</form>
                    </tr>");
        }
        $wgOut->addHTML("</tbody></table><script type='text/javascript'>
                                            $('#requests').dataTable({'autoWidth': false}).fnSort([[2,'desc']]);
                                            $('#requests').css('display', 'table');
                                         </script>");
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
        $emailRow = new FormTableRow("email_row");
        $emailRow->append($emailLabel)->append($emailField);
        
        $roleValidations = VALIDATE_NOT_NULL;
        if($me->isRoleAtLeast(STAFF)){
            $roleValidations = VALIDATE_NOTHING;
        }
        $roleOptions = array();
        foreach($wgRoles as $role){
            if($me->isRoleAtLeast($role) && $role != CHAIR && 
                                            $role != EA && 
                                            $role != FEC){
                $roleOptions[$config->getValue('roleDefs', $role)] = $role;
            }
        }
        if($me->isRoleAtLeast(STAFF)){
            if(in_array(CHAIR, $wgRoles)){
                $roleOptions[$config->getValue('roleDefs', CHAIR)] = CHAIR;
            }
            if(in_array(EA, $wgRoles)){
                $roleOptions[$config->getValue('roleDefs', EA)] = EA;
            }
            if(in_array(FEC, $wgRoles)){
                $roleOptions[$config->getValue('roleDefs', FEC)] = FEC;
            }
        }
        ksort($roleOptions);
        $rolesLabel = new Label("role_label", "Roles", "The roles the new user should belong to", $roleValidations);
        $rolesField = new VerticalCheckBox("role_field", "Roles", array(), $roleOptions, $roleValidations);
        $rolesRow = new FormTableRow("role_row");
        $rolesRow->append($rolesLabel)->append($rolesField);

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
        
        $submitCell = new EmptyElement();
        $submitField = new SubmitButton("submit", "Submit Request", "Submit Request", VALIDATE_NOTHING);
        $submitRow = new FormTableRow("submit_row");
        $submitRow->append($submitCell)->append($submitField);
        
        $formTable->append($firstNameRow)
                  ->append($lastNameRow)
                  ->append($emailRow)
                  ->append($rolesRow)
                  ->append($universityRow)
                  ->append($deptRow)
                  ->append($positionRow)
                  ->append($submitRow);
        
        $formContainer->append($formTable);
        return $formContainer;
    }
    
    function generateFormHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config;
        $user = Person::newFromId($wgUser->getId());
        if($user->isRoleAtLeast(STAFF)){
            $wgOut->addHTML("<b><a href='$wgServer$wgScriptPath/index.php/Special:AddMember?action=view'>View Requests</a></b><br /><br />");
        }
        $wgOut->addHTML("Adding a member to the {$config->getValue('siteName')} will allow them to access content relevant to the user roles and projects which are selected below.  By selecting projects, the user will be automatically added to the projects on the {$config->getValue('siteName')}, and subscribed to the project mailing lists.  The new user's email must be provided as it will be used to send a randomly generated password to the user.  After pressing the 'Submit Request' button, an administrator will be able to accept the request.  If there is a problem in the request (ie. there was an obvious typo in the name), then you may be contacted by the administrator about the request.<br /><br />");
        $wgOut->addHTML("<form action='$wgScriptPath/index.php/Special:AddMember' method='post'>\n");
        
        $form = self::createForm();
        $wgOut->addHTML($form->render());
        $wgOut->addHTML("</form>");
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(ADMIN)){
            $toolbox['Tools']['links'][-1] = TabUtils::createToolboxLink("Add Member", "$wgServer$wgScriptPath/index.php/Special:AddMember");
        }
        return true;
    }
}

?>
