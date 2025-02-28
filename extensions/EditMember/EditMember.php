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
        SpecialPage::__construct("EditMember", MANAGER.'+', true, 'runEditMember');
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage, $config;
        $this->getOutput()->setPageTitle("Edit Roles");
        $me = Person::newFromWgUser();
        $date = date("Y-m-d");
        $wgOut->addScript("<script type='text/javascript'>
                                $(document).ready(function(){
                                $('.datepicker').datepicker({showOn: 'both',
                                                            buttonImage: '../skins/calendar.gif',
                                                            buttonText: 'Date',
                                                            buttonImageOnly: true,
                                                            onChangeMonthYear: function (year, month, inst) {
                                                                var curDate = $(this).datepicker('getDate');
                                                                if (curDate == null)
                                                                    return;
                                                                if (curDate.getYear() != year || curDate.getMonth() != month - 1) {
                                                                    curDate.setYear(year);
                                                                    curDate.setMonth(month - 1);
                                                                    while(curDate.getMonth() != month -1){
                                                                        curDate.setDate(curDate.getDate() - 1);
                                                                    }
                                                                    $(this).datepicker('setDate', curDate);
                                                                    $(this).trigger('change');
                                                                }
                                                            }
                                                        });
                                $('.datepicker').datepicker('option','dateFormat', 'yy-mm-dd');
                                $('.datepicker').datepicker('option','showAnim', 'blind');
                                $('.datepicker').keydown(function(){
                                    return false;
                                });
                                $('.datepicker').attr('value', '$date');
                                $('#tabs').tabs({
                                                    cookie: {
                                                        expires: 1
                                                    }
                                                });
                            });
                           </script>");
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
                else if(!$me->isAllowedToEdit($person)){ // Handles RMC-GOV
                    $wgMessage->addError("You do not have permissions to edit this user.");
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
    
    // Generates a more human readable form for the string used to add/remove roles
    function roleDiff($person, $current, $string, $type, $date=false){
        $output = "";
        //$date = "2013-11-28 10:24:25";
        //echo $date."<br />";
        if($type == "ROLE"){
            $roles = explode(", ", $string);
            if(!is_null($current)){
                $current = explode(", ", $current);
                foreach($current as $role){
                    $id = array_search($role, $roles);
                    if($id !== false){
                        // No Change
                        unset($roles[$id]);
                    }
                    else{
                        $output .= "-{$role}<br />\n";
                    }
                }
            }
            else{
                if(count($person->getRoles($date)) > 0){
                    foreach($person->getRoles($date) as $role){
                        $id = array_search($role->getRole(), $roles);
                        if($id !== false){
                            // No Change
                            unset($roles[$id]);
                        }
                        else{
                            $output .= "-{$role->getRole()}<br />\n";
                        }
                    }
                }
            }
            
            foreach($roles as $role){
                if($role != ""){
                    $output .= "+{$role}<br />\n";
                }
            }
        }
        return $output;
    }
    
    function generateMain(){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $config;
        $me = Person::newFromWgUser();
        $allPeople = Person::getAllPeople('all');
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
    
    function parse($text){
        $text = str_replace("'", "&#39;", $text);
        $text = str_replace("\"", "&quot;", $text); 
        return $text;
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(MANAGER)){
            $toolbox['Other']['links'][] = TabUtils::createToolboxLink("Edit Roles", "$wgServer$wgScriptPath/index.php/Special:EditMember");
        }
        return true;
    }
}

?>
