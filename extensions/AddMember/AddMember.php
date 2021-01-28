<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['AddMember'] = 'AddMember'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AddMember'] = $dir . 'AddMember.i18n.php';
$wgSpecialPageGroups['AddMember'] = 'network-tools';

$wgHooks['ToolboxLinks'][] = 'AddMember::createToolboxLinks';
$wgHooks['SubLevelTabs'][] = 'AddMember::createSubTabs';

autoload_register('AddMember/Validations');

class AddMember extends SpecialPage{

    function __construct() {
        parent::__construct("AddMember", MANAGER.'+', true);
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        $user = Person::newFromId($wgUser->getId());
        if(isset($_GET['action']) && $_GET['action'] == "view" && $user->isRoleAtLeast(MANAGER)){
            if(isset($_POST['submit']) && $_POST['submit'] == "Accept"){
                $request = UserCreateRequest::newFromId($_POST['id']);
                $sendEmail = "false";
                if(isset($_POST['wpEmail']) && $_POST['wpEmail'] != ""){
                    $sendEmail = "true";
                }
                $_POST['wpSendMail'] = "$sendEmail";
                $aliases = array(array());
                do {
                    $_POST['wpAlias'] = "";
                    if($_POST['wpUserType'] == CI){
                        $_POST['wpAlias'] = "MD";
                    }
                    else if($_POST['wpUserType'] == AR){
                        $_POST['wpAlias'] = "PH";
                    }
                    else if($_POST['wpUserType'] == NP){
                        $_POST['wpAlias'] = "NP";
                    }
                    else if($_POST['wpUserType'] == MW){
                        $_POST['wpAlias'] = "MW";
                    }
                    else if($_POST['wpUserType'] == HQP){
                        if($_POST['wpOtherRole'] == "Nurse"){
                            $_POST['wpAlias'] = "RN";
                        }
                        else if($_POST['wpOtherRole'] == "Administrator"){
                            $_POST['wpAlias'] = "AD";
                        }
                        else if($_POST['wpOtherRole'] == "Counsellor"){
                            $_POST['wpAlias'] = "CS";
                        }
                        else if($_POST['wpOtherRole'] == "Clerical"){
                            $_POST['wpAlias'] = "CC";
                        }
                    }
                    else if($_POST['wpUserType'] == EXTERNAL){
                        $_POST['wpAlias'] = "OT";
                    }
                    if($_POST['wpAlias'] == ""){
                        $aliases = array();
                        break;
                    }
                    $digits = 5;
                    $_POST['wpAlias'] = str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT).$_POST['wpAlias'];
                    $aliases = DBFunctions::select(array('mw_user'),
                                                   array('alias'),
                                                   array('alias' => EQ($_POST['wpAlias'])));
                } while (count($aliases) > 0);
                
                $result = APIRequest::doAction('CreateUser', false);
                if(strstr($result, "already exists") === false){
                    $request->acceptRequest();
                }
            }
            else if(isset($_POST['submit']) && $_POST['submit'] == "Ignore"){
                $request = UserCreateRequest::newFromId($_POST['id']);
                $request->ignoreRequest();
            }
            else if(isset($_POST['submit']) && $_POST['submit'] == "Promote"){
                $request = UserCreateRequest::newFromId($_POST['id']);
                $person = $request->getPerson();
                $person->candidate = 0;
                DBFunctions::update('mw_user',
                                    array('candidate' => 0),
                                    array('user_id' => $person->getId()));
                Notification::addNotification(null, $person, "Account Certified", "Your CAPS-CPCA account was certified.", "$wgServer$wgScriptPath", true);
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
                //$form->getElementById('project_field')->setPOST('wpNS');
                //$form->getElementById('university_field')->setPOST('university');
                //$form->getElementById('dept_field')->setPOST('department');
                //$form->getElementById('position_field')->setPOST('position');
                $form->getElementById('cand_field')->setPOST('candidate');
                
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
                        <table id='requests' style='background:#ffffff;width:100%;text-align:center;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
                        <thead><tr bgcolor='#F2F2F2'>
                            <th>User Name</th>
                            <th>Timestamp</th>
                            <th>User Type</th>
                            {$hqpType}
                            <th>Reference</th>
                            <th>Certification</th>
			                <th>Extras</th>
                            <th>Action</th>
                        </tr></thead><tbody>\n");
        }
        else{
            $wgOut->addHTML("<a href='$wgServer$wgScriptPath/index.php/Special:AddMember?action=view&history=true'>View History</a><br /><br />
                        <table id='requests' style='background:#ffffff;width:100%;text-align:center;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
                        <thead><tr bgcolor='#F2F2F2'>
                            <th>User Name</th>
                            <th>Timestamp</th>
                            <th>User Type</th>
                            {$hqpType}
                            <th>Sponsors</th>
                            <th>Certification</th>
			                <th>Extras</th>
                            <th>Create?</th>
                            <th>Full User?</th>
                        </tr></thead><tbody>\n");
        }
    
        $requests = UserCreateRequest::getAllRequests($history);
        $forms = array();
        foreach($requests as $request){
            $wgOut->addHTML("<tr>");
            if($history && $request->isCreated()){
                $user = Person::newFromName($request->getName());
                $wgOut->addHTML("<td align='left'><a target='_blank' href='{$user->getUrl()}'>{$request->getName()}</a><br />{$request->getEmail()}</td>");
            }
            else{
                $wgOut->addHTML("<td align='left'>{$request->getName()}<br />{$request->getEmail()}</td>");
            } 
            $wgOut->addHTML("<td>".str_replace(" ", "<br />", $request->getLastModified())."</td>");
	        $extras = $request->getExtras();
            $wgOut->addHTML("<td>{$request->getRoles()}</td>");
            if(count($config->getValue('subRoles')) > 0 && !$history){
                $wgOut->addHTML("<td align='left' style='white-space:nowrap;'>");
                foreach($config->getValue('subRoles') as $subRole => $fullSubRole){
                    $wgOut->addHTML("<input type='checkbox' name='subtype[]' value='{$subRole}' />{$fullSubRole}<br />");
                }
                $wgOut->addHTML("</td>");
            }
            $wpSendMail = ($wgEnableEmail) ? "true" : "false";
            $certification = $request->getCertification();
            $file_name = @$certification['file_data']['name'];
            $file_name = ($file_name != "") ? "<a class='button' href='$wgServer$wgScriptPath/index.php?action=getCertification&id={$request->getId()}'>Certification</a>" : "";
            $other = array();
            if(is_array($extras)){
                foreach($extras as $key => $value){
                    if(!is_array($value) && $value !== ""){
                        if($key == "collect_demo" || $key == "collect_comments"){
                            if($value == 0 || $value == ""){
                                $value = "No";
                            }
                            else {
                                $value = "Yes";
                            }
                        }
                        $displayKey = ucwords(str_replace("_", " ", $key));
                        $other[] = "<b>$displayKey</b>: $value";
                    }
                }
            }
            $references = @$extras['references'];
            $refHTML = array();
            if(!empty($references)){
                foreach($references as $reference){
                    $refHTML[] = @"<li>{$reference['name']}<br />
                                       {$reference['affil']}, {$reference['email']}, {$reference['phone']}
                                   </li>";
                }
            }
            $refHTML = "<ul>".implode("", $refHTML)."</ul>";
            if(!$history){
                $forms[] = "<form action='$wgScriptPath/index.php/Special:AddMember?action=view' method='post'>
                                <input type='hidden' name='id' value='{$request->getId()}' />
                                <input type='hidden' name='wpName' value='{$request->getName()}' />
                                <input type='hidden' name='wpEmail' value='{$request->getEmail()}' />
                                <input type='hidden' name='wpRealName' value='{$request->getRealName()}' />
                                <input type='hidden' name='wpUserType' value='{$request->getRoles()}' />
                                <input type='hidden' name='wpNS' value='{$request->getProjects()}' />
                                <input type='hidden' name='candidate' value='{$request->getCandidate()}' />
                                <input type='hidden' name='wpOtherRole' value='".str_replace("'", "&#39;", @$extras['otherRole'])."' />
                                <input type='hidden' name='university' value='".str_replace("'", "&#39;", $request->getUniversity())."' />
                                <input type='hidden' name='department' value='".str_replace("'", "&#39;", $request->getDepartment())."' />
                                <input type='hidden' name='position' value='".str_replace("'", "&#39;", $request->getPosition())."' />
                                <input type='hidden' name='wpLanguage' value='".str_replace("'", "&#39;", @$extras['language'])."' />
                                <input type='hidden' name='wpPostalCode' value='".str_replace("'", "&#39;", @$extras['postal_code'])."' />
                                <input type='hidden' name='wpCity' value='".str_replace("'", "&#39;", @$extras['city'])."' />
                                <input type='hidden' name='wpProvince' value='".str_replace("'", "&#39;", @$extras['province'])."' />
                                <input type='hidden' name='wpClinic' value='".str_replace("'", "&#39;", @$extras['clinic'])."' />
                                <input type='hidden' name='wpSpecialty' value='".str_replace("'", "&#39;", @$extras['specialty'])."' />
                                <input type='hidden' name='wpProvision' value='".str_replace("'", "&#39;", @$extras['provision'])."' />
                                <input type='hidden' name='wpPharmacyName' value='".str_replace("'", "&#39;", @$extras['pharmacy_name'])."' />
                                <input type='hidden' name='wpPharmacyAddress' value='".str_replace("'", "&#39;", @$extras['pharmacy_address'])."' />
                                <input type='hidden' name='wpCollectDemo' value='".str_replace("'", "&#39;", @$extras['collect_demo'])."' />
                                <input type='hidden' name='wpCollectComments' value='".str_replace("'", "&#39;", @$extras['collect_comments'])."' />
                                <input type='hidden' name='wpSendMail' value='$wpSendMail' />
                                <input id='{$request->getId()}_promote' type='submit' name='submit' value='Promote' />
                                <input id='{$request->getId()}_ignore' type='submit' name='submit' value='Ignore' />
                                <input id='{$request->getId()}_accept' type='submit' name='submit' value='Accept' />
                            </form>";
            }
            $wgOut->addHTML("<td align='left'>{$refHTML}</td>
                        <td>{$file_name}</td>
			            <td align='left'>".implode("<br />", $other)."</td>
			                ");
            if($history){
                if($request->isCreated()){
                    $wgOut->addHTML("<td>Accepted</td>");
                }
                else{
                    $wgOut->addHTML("<td>Ignored</td>");
                }
            }
            else{
                if(!$request->isCreated()){
                    $wgOut->addHTML("<td><a class='button' onclick=\"if(confirm('Are you sure you want to accept the request?')){ $('#{$request->getId()}_accept').click(); }\">Accept</a><br /><a class='button' onclick=\"if(confirm('Are you sure you want to ignore the request?')){ $('#{$request->getId()}_ignore').click(); }\">Ignore</a></td>");
                    $wgOut->addHTML("<td></td>");
                }
                else{
                    $wgOut->addHTML("<td></td>");
                    $wgOut->addHTML("<td><a class='button' onclick=\"if(confirm('Are you sure you want to promote this user?')){ $('#{$request->getId()}_promote').click(); }\">Promote</a></td>");
                }
            }
            $wgOut->addHTML("</tr>");
        }
        $wgOut->addHTML("</tbody></table><div style='display:none;'>".implode("", $forms)."</div><script type='text/javascript'>
                                            $('#requests').dataTable({'autoWidth': true, 'scrollX': true}).fnSort([[1,'desc']]);
                                            $('#requests').show();
                                         </script>");
    }
    
    function createForm(){
        global $wgRoles, $wgUser, $config;
        $me = Person::newFromUser($wgUser);
        $committees = $config->getValue('committees');
        $aliases = $config->getValue('roleAliases');
        
        $formContainer = new FormContainer("form_container");
        $formTable = new FormTable("form_table");
        
        $firstNameLabel = new Label("first_name_label", "<span class='en' style='display:none'>First Name</span><span class='fr' style='display:none'>Prénom</span>", "The first name of the user (cannot contain spaces)", VALIDATE_NOT_NULL);
        $firstNameField = new TextField("first_name_field", "First Name", "", VALIDATE_NOT_NULL);
        $firstNameRow = new FormTableRow("first_name_row");
        $firstNameRow->append($firstNameLabel)->append($firstNameField->attr('size', 20));
        
        $lastNameLabel = new Label("last_name_label", "<span class='en' style='display:none'>Last Name</span><span class='fr' style='display:none'>Nom de famille</span>", "The last name of the user (cannot contain spaces)", VALIDATE_NOT_NULL);
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
            if($me->isRoleAtLeast($role) && !isset($committees[$role]) && !isset($aliases[$role])){
                $roleOptions[$config->getValue('roleDefs', $role)] = $role;
            }
        }
        if($me->isRoleAtLeast(PL) && in_array(CHAMP, $wgRoles)){
            $roleOptions[$config->getValue('roleDefs', CHAMP)] = CHAMP;
        }
        if($me->isRoleAtLeast(STAFF)){
            foreach($committees as $committee => $def){
                $roleOptions[$def] = $committee;
            }
            foreach($aliases as $alias => $role){
                $roleOptions[$alias] = $alias;
            }
        }
        ksort($roleOptions);
        $rolesLabel = new Label("role_label", "<span class='en'>Roles</span><span class='fr'>Rôles</span>", "The roles the new user should belong to", $roleValidations);
        $rolesField = new VerticalCheckBox("role_field", "Roles", array(), $roleOptions, $roleValidations);
        $rolesRow = new FormTableRow("role_row");
        $rolesRow->append($rolesLabel)->append($rolesField);

        
        $candLabel = new Label("cand_label", "<span class='en'>Candidate?</span><span class='fr'>Candidat?</span>", "Whether or not this user should be a candidate (not officially in the network yet)", VALIDATE_NOTHING);
        $candField = new VerticalRadioBox("cand_field", "Roles", "Yes", array("0" => "No", "1" => "Yes"), VALIDATE_NOTHING);
        $candRow = new FormTableRow("cand_row");
        $candRow->append($candLabel)->append($candField);
               
        $languageLabel = new Label("language_label", "<span class='en'>Language</span><span class='fr'>Langue</span>", "The language of the user", VALIDATE_NOT_NULL);
        $languageField = new SelectBox("language_field", "Language", "",array("English", "Français"), VALIDATE_NOT_NULL);
        $languageRow = new FormTableRow("language_row");
        $languageRow->append($languageLabel)->append($languageField);
 
        $submitCell = new EmptyElement();
        $submitField = new SubmitButton("submit", "Submit Request", "Submit Request", VALIDATE_NOTHING);
        $submitRow = new FormTableRow("submit_row");
        $submitRow->append($submitCell)->append($submitField);
        
        $formTable->append($firstNameRow)
                  ->append($lastNameRow)
                  ->append($emailRow)
                  ->append($rolesRow)
                  ->append($languageRow)
                  ->append($candRow)
                  ->append($submitRow);
                  
        $formTable->getElementById("cand_row")->attr('style', 'display:none;');
        
        $formContainer->append($formTable);
        return $formContainer;
    }
    
    function generateFormHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles;
        $user = Person::newFromId($wgUser->getId());
        if($user->isRoleAtLeast(MANAGER)){
            $wgOut->addHTML("<b><a href='$wgServer$wgScriptPath/index.php/Special:AddMember?action=view'><span class='en' style='display:none'>View Requests</span><span class='fr' style='display:none'>Voir Demandes
</span></a></b><br /><br />");
        }
        $wgOut->addHTML("<div class='en' style='display:none'>Adding a member to the forum will allow them to access content relevant to the user roles and projects which are selected below.  By selecting projects, the user will be automatically added to the projects on the forum, and subscribed to the project mailing lists.  The new user's email must be provided as it will be used to send a randomly generated password to the user.  After pressing the 'Submit Request' button, an administrator will be able to accept the request.  If there is a problem in the request (ie. there was an obvious typo in the name), then you may be contacted by the administrator about the request.</div>
<div class='fr' style='display:none'>Ajout d'un membre du forum qui leur permettra d'accéder à des contenus pertinents pour les rôles des utilisateurs et des projets qui sont sélectionnés ci-dessous. En sélectionnant les projets , l'utilisateur sera automatiquement ajouté aux projets sur le forum, et souscrit aux listes de diffusion du projet . Le courriel au nouvel utilisateur doit être fourni , il sera utilisé pour envoyer un mot de passe généré aléatoirement à l'utilisateur. Après avoir appuyé sur le bouton «Soumettre la demande» , un administrateur sera en mesure d'accepter la demande . S'il y a un problème dans la demande (ie. Il y avait une faute de frappe évidente dans le nom) , alors vous pouvez être contacté par l'administrateur au sujet de la demande.</div><br /><br />");
        $wgOut->addHTML("<form action='$wgScriptPath/index.php/Special:AddMember' method='post'>\n");
        
        $form = self::createForm();
        $wgOut->addHTML($form->render());
        $wgOut->addHTML("</form>");
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $wgLang;
        $me = Person::newFromWgUser();
	    $title = "Add Member";
	    if($wgLang->getCode() == "fr"){
	        $title = "Ajouter un Membre";
	    }
        if($me->isRoleAtLeast(MANAGER)){
            $toolbox['People']['links'][0] = TabUtils::createToolboxLink($title, "$wgServer$wgScriptPath/index.php/Special:AddMember");
            $toolbox['People']['links'][2] = TabUtils::createToolboxLink("Registration Queue", "$wgServer$wgScriptPath/index.php/Special:AddMember?action=view");
        }
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle;
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(MANAGER)){
            $selected = ("AddMember" == $wgTitle->getText() && @$_GET['action'] == 'view') ? "selected" : "";
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("Registration Queue", "$wgServer$wgScriptPath/index.php/Special:AddMember?action=view", $selected);
        }
        return true;
    }
}

?>
