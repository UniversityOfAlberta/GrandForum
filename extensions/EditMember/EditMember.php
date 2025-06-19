<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['EditMember'] = 'EditMember'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['EditMember'] = $dir . 'EditMember.i18n.php';
$wgSpecialPageGroups['EditMember'] = 'network-tools';

$wgHooks['ToolboxLinks'][] = 'EditMember::createToolboxLinks';

function runEditMember($par) {
  EditMember::execute($par);
}

class EditMember extends SpecialPage{

    function __construct() {
        SpecialPage::__construct("EditMember", STAFF.'+', true, 'runEditMember');
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage, $config;
        $this->getOutput()->setPageTitle("Edit Roles");
        $me = Person::newFromWgUser();
        if(!isset($_POST['submit'])){
            // Form not entered yet
            if(isset($_GET['next']) || isset($_POST['next']) || isset($_GET['name'])){
                if(!isset($_GET['next']) && isset($_POST['next'])){
                    $_GET['next'] = $_POST['next'];
                }
                if(!isset($_GET['name']) && isset($_POST['name'])){
                    $_GET['name'] = $_POST['name'];
                }
                $person = @Person::newFromName($_GET['name']);
                $roles = $person->getRoles();
                if(!isset($_GET['name'])){
                    $wgMessage->addError("A user was not provided.");
                    EditMember::generateMain();
                    return;
                }
                EditMember::generateEditMemberFormHTML($wgOut);
            }
            else{
                EditMember::generateMain();
            }
        }
        else{
            // The Form has been entered
            $person = @Person::newFromName(str_replace(" ", ".", $_POST['name']));
            
            if($me->isRoleAtLeast(MANAGER)){
                // Sub-Role Changes
                $subRoles = @$_POST['sub_wpNS'];
                if(!is_array($subRoles)){
                    $subRoles = array();
                }
                $subKeys = array_flip($subRoles);
                $currentSubRoles = $person->getSubRoles();
                // Removing Sub-Roles
                foreach($currentSubRoles as $subRole){
                    if(!isset($subKeys[$subRole])){
                        DBFunctions::delete('grand_role_subtype',
                                            array('user_id' => EQ($person->getId()),
                                                  'sub_role' => EQ($subRole)));
                        $wgMessage->addSuccess("<b>{$person->getReversedName()}</b> is no longer a {$subRole}");
                    }
                }
                // Adding Sub-Roles
                foreach($subRoles as $subRole){
                    if(!$person->isSubRole($subRole)){
                        DBFunctions::insert('grand_role_subtype',
                                            array('user_id' => EQ($person->getId()),
                                                  'sub_role' => EQ($subRole)));
                        $wgMessage->addSuccess("<b>{$person->getReversedName()}</b> is now a {$subRole}");
                    }
                }
            }
            EditMember::generateMain();
        }
    }
    
    function generateMain(){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $config;
        $me = Person::newFromWgUser();
        $allPeople = Person::getAllFullPeople();
        $i = 0;
        $names = array();
        foreach($allPeople as $person){
            if(!$me->isAllowedToEdit($person)){
                // User does not have permission for this person
                continue;
            }
            $names[] = $person->getName();
        }
        
        $wgOut->addHTML("This page can be used to edit the sub-roles of members on the {$config->getValue('siteName')}.<br />
                         Select a user from the list below, and then click the 'Next' button.<table>
                            <tr><td>
                            <form action='$wgServer$wgScriptPath/index.php/Special:EditMember' method='post'>
                                <select data-placeholder='Choose a Person...' id='names' name='name' size='10' style='width:100%'>");
        foreach($allPeople as $person){
            if(!$me->isAllowedToEdit($person)){
                // User does not have permission for this person
                continue;           
            }
            $wgOut->addHTML("<option value=\"{$person->getName()}\">{$person->getNameForForms()}</option>\n");
        }
        $wgOut->addHTML("</select>
                </td></tr>
                <tr><td>
            <input id='button' type='submit' name='next' value='Next' disabled='disabled' />
        </form></td></tr></table>
        
        <h3>Current Sub-Roles</h3>
        <table class='wikitable' frame='box' rules='all'>
            <tr>
                <th>User</th><th>Sub-Roles</th>
            </tr>");
        foreach($allPeople as $person){
            $subRoles = implode(", ", $person->getSubRoles());
            if($subRoles != "" && $person->inFaculty()){
                $wgOut->addHTML("<tr>
                    <td>{$person->getNameForForms()}</td>
                    <td>{$subRoles}</td>
                </tr>");
            }
        }
        $wgOut->addHTML("</table>
        <script type='text/javascript'>
            $('#names').chosen();
            $(document).ready(function(){
                $('#names').change(function(){
                    var page = $('#names').val();
                    if(page != ''){
                        $('#button').prop('disabled', false);
                    }
                });
            });
        </script>");
    }
    
    function generateEditMemberFormHTML($wgOut){
        global $wgServer, $wgScriptPath, $wgUser, $config;
        $me = Person::newFromId($wgUser->getId());
        $person = Person::newFromName(str_replace(" ", ".", $_GET['name']));
        $wgOut->addHTML("<form id='editMember' action='$wgServer$wgScriptPath/index.php/Special:EditMember?project' method='post'>
        <p>Select the Sub-Roles to which <b>{$person->getNameForForms()}</b> should be a member of.  Deselecting a role or project will prompt further questions, relating to the reason why they are leaving that role.  All actions will need to be approved by an Administrator.</p>");
        EditMember::generateSubRoleFormHTML($wgOut);
        $wgOut->addHTML("<br />
                         <input type='hidden' name='name' value='{$_GET['name']}' />
                         <input type='submit' name='submit' value='Submit Request' onSubmit />
                         </form>");
    }
    
    function generateSubRoleFormHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config;
        $me = Person::newFromWgUser();
        if(!isset($_GET['name'])){
            return;
        }
        $person = Person::newFromName(str_replace(" ", ".", $_GET['name']));
        $wgOut->addHTML("<table style='min-width:300px;'><tr>
                        <td class='mw-input'>");
        $boxes = "";
        $projects = "";
        
        $subRoles = $config->getValue("subRoles");
        
        asort($subRoles);
        foreach($subRoles as $subRole => $fullSubRole){
            $checked = ($person->isSubRole($subRole)) ? " checked" : "";
            $boxes .= "&nbsp;<input id='role_$subRole' type='checkbox' name='sub_wpNS[]' value='".$subRole."' $checked />&nbsp;{$fullSubRole}<br />";            
        }
        $wgOut->addHTML($boxes);
        $wgOut->addHTML("</td></tr></table>\n");
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(STAFF)){
            $toolbox['Other']['links'][] = TabUtils::createToolboxLink("Edit Roles", "$wgServer$wgScriptPath/index.php/Special:EditMember");
        }
        return true;
    }
}

?>
