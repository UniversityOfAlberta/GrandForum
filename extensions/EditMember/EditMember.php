<?php

require_once("EditMemberAdmin.php");

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['EditMember'] = 'EditMember'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['EditMember'] = $dir . 'EditMember.i18n.php';
$wgSpecialPageGroups['EditMember'] = 'network-tools';

$wgHooks['ToolboxLinks'][] = 'EditMember::createToolboxLinks';

function runEditMember($par) {
  EditMember::execute($par);
}

class EditMember extends SpecialPage{

    function EditMember() {
        SpecialPage::__construct("EditMember", MANAGER.'+', true, 'runEditMember');
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage, $config;
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
                            
                            function qualifyProjects(box){
                                if($(box).is(':checked')){
                                    $(box).next().next().show();
                                }
                                else{
                                    $(box).next().next().hide();
                                }
                            }
                            
                            function addComment(box, cannotchange){
                                if(cannotchange){
                                    if(!$(box).is(':checked') && $(box).hasClass('already')){
                                        $(box).attr('checked', 'checked');
                                        alert('You cannot change the role of an HQP that is supervised by someone else.');
                                    }
                                }
                                else{
                                    if(!$(box).is(':checked') && $(box).hasClass('already')){
                                        $(box).next().slideDown('fast');
                                    }
                                    else{
                                        $(box).next().slideUp('fast');
                                    }
                                }
                            }
                            
                            function openRoleProjects(roleId){
                                $('div#role_' + roleId + '_projects').dialog({
                                    width: 650,
                                    buttons: {
                                        'Ok': function(){
                                            $(this).dialog('close');
                                        }
                                    }
                                }).parent().appendTo($('#editMember'));
                                $('div#role_' + roleId + '_projects').dialog('option','position', {
                                    my: 'center center',
                                    at: 'center center',
                                    offset: '0 -75%'
                                });
                            }
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
                
                if(isset($_POST['candidate']) && !$person->isCandidate()){
                    MailingList::unsubscribeAll($person);
                    DBFunctions::update('mw_user',
                                        array('candidate' => '1'),
                                        array('user_id' => EQ($person->getId())));
                    Cache::delete("mw_user_{$person->getId()}");
                    Cache::delete("allPeopleCache");
                    $person->candidate = true;
                    $wgMessage->addSuccess("<b>{$person->getReversedName()}</b> is now a candidate user");
                }
                else if(!isset($_POST['candidate']) && $person->isCandidate()){
                    DBFunctions::update('mw_user',
                                        array('candidate' => '0'),
                                        array('user_id' => EQ($person->getId())));
                    Cache::delete("mw_user_{$person->getId()}");
                    Cache::delete("allPeopleCache");
                    $person->candidate = false;
                    $wgMessage->addSuccess("<b>{$person->getReversedName()}</b> is now a full user");
                    MailingList::subscribeAll($person);
                }
            }
            EditMember::generateMain();
        }
    }
    
    // Returns a string representation of the given variable containing role details
    private function varToString($var, $current, $nss, $type, $person){
        $diff = EditMember::roleDiff($person, $current, $nss, $type);
        $return = "";
        if(isset($var)){
            foreach($var as $key => $value){
                if($value != "" && ($nss == "" || strstr($nss, $key) === false) && strstr($diff, '-'.$key) !== false){
                    if($type == "PROJECT"){
                        $proj = Project::newFromName($key);
                        $key = $proj->getId();
                    }
                    $return .= "$key::".EditMember::parse($value)." ::";
                }
            }
        }
        return substr($return,0,-2);
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
        $allPeople = Person::getAllCandidates('all');
        $i = 0;
        $names = array();
        foreach($allPeople as $person){
            if(!$me->isAllowedToEdit($person)){ 
                // User does not have permission for this person
                continue;
            }
            $names[] = $person->getName();
        }
        
        $wgOut->addHTML("This page can be used to edit the roles and projects of members on the {$config->getValue('siteName')}.<br />
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
    
    function generateViewHTML($wgOut){
        global $wgScriptPath, $wgServer;
        $history = false;
        if(isset($_GET['history']) && $_GET['history'] == true){
            $history = true;
        }
        if($history){
            $wgOut->addHTML("<a href='$wgServer$wgScriptPath/index.php/Special:EditMember?action=view'>View New Requests</a><br /><br />
                        <table id='requests' style='display:none;background:#ffffff;text-align:center;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
                        <thead><tr bgcolor='#F2F2F2'>
                            <th>Requesting User</th> <th>User Name</th> <th>Timestamp</th> <th>Effective Dates</th> <th>Staff</th> <th>Role</th> <th>Comment</th> <th>Other</th> <th>Type</th> <th>Status</th>
                        </tr></thead><tbody>\n");
        }
        else{
            $wgOut->addHTML("<a href='$wgServer$wgScriptPath/index.php/Special:EditMember?action=view&history=true'>View History</a><br /><br />
                        <table id='requests' style='display:none;background:#ffffff;text-align:center;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
                        <thead><tr bgcolor='#F2F2F2'>
                            <th>Requesting User</th> <th>User Name</th> <th>Timestamp</th> <th>Effective Dates</th> <th>Role</th> <th>Comment</th> <th>Other</th> <th>Type</th> <th>Accept</th> <th>Ignore</th>
                        </tr></thead><tbody>\n");
        }
        if($history){
            $rows = DBFunctions::select(array('grand_role_request'),
                                        array('*'),
                                        array('created' => EQ(1),
                                              WHERE_OR('`ignore`') => EQ(1)),
                                        array('last_modified' => 'DESC'));
        }
        else{
            $rows = DBFunctions::select(array('grand_role_request'),
                                        array('*'),
                                        array('created' => EQ(0),
                                              '`ignore`' => EQ(0)));
        }
        foreach($rows as $row){
            $otherData = unserialize($row['other']);
            if(isset($otherData['thesisTitle'])){
                $other = "<b>Thesis:</b> {$otherData['thesisTitle']}";
            }
            else if(isset($otherData['where'])){
                $other = "<b>Now At:</b> {$otherData['where']}";
            }
            else{
                $other = "";
            }
            $req_user = Person::newFromId($row['requesting_user']);
            $staff = Person::newFromId($row['staff']);
            $person = Person::newFromId($row['user']);
            $projs = array();
            $roles = array();
            if($req_user->getName() != null){
                foreach($req_user->getRoles() as $role){
                    $roles[] = $role->getRole();
                }
            }
            if($history){
                $diff = EditMember::roleDiff(Person::newFromId($row['user']), $row['current_role'], $row['role'], $row['type'], $row['last_modified']);
            }
            else{
                $diff = EditMember::roleDiff(Person::newFromId($row['user']), $row['current_role'], $row['role'], $row['type']);
            }
            $roleProjects = unserialize($row['role_projects']);
            
            if(is_array($roleProjects) && count($roleProjects) > 0){
                $diff .= "<ul>";
                foreach($roleProjects as $r => $projs){
                    $diff .= "<li>{$r}<ul><li>".implode("</li><li>", $projs)."</li></ul></li>";
                }
                $diff .= "</ul>";
            }
            
            $dates = explode("::", $row['effective_date']);
            foreach($dates as $key => $date){
                if($key % 2 == 0 && is_numeric($date)){
                    $proj = Project::newFromId($date);
                    $dates[$key] = $proj->getName();
                }
            }
            $wgOut->addHTML("<tr bgcolor='#FFFFFF'>
                        <td align='left'>
                            <a target='_blank' href='{$req_user->getUrl()}'><b>{$req_user->getName()}</b></a> (".implode(",", $roles).")<br /><a onclick='$(\"#{$row['id']}\").slideToggle();$(this).remove();' style='cursor:pointer;'>Show Projects</a>
                            <div id='{$row['id']}' style='display:none;padding-left:15px;'>".implode("<br />", $projs)."</div>
                        </td> 
                        <td align='left'><a target='_blank' href='{$person->getUrl()}'>{$person->getName()}</a></td> <td>{$row['last_modified']}</td> <td>".str_replace(" ::", "<br />", implode("::", $dates))."</td>");
            if($history){
                $wgOut->addHTML("<td>{$staff->getName()}</td>");
            }
            $comments = explode("::", $row['comment']);
            foreach($comments as $key => $comment){
                if($key % 2 == 0 && is_numeric($comment)){
                    $proj = Project::newFromId($comment);
                    $comments[$key] = $proj->getName();
                }
            }
            $wgOut->addHTML("<td align='left'>{$diff}</td> <td align='left'>".str_replace(" ::", "<br />", implode("::", $comments))."</td> <td align='left'>".$other."</td> <td align='left'>{$row['type']}</td>
                        <form action='$wgServer$wgScriptPath/index.php/Special:EditMember?action=view&sub' method='post'>
                            <input type='hidden' name='current_role' value='{$row['current_role']}' />
                            <input type='hidden' name='role' value='{$row['role']}' />
                            <input type='hidden' name='role_projects' value='{$row['role_projects']}' />
                            <input type='hidden' name='comment' value='{$row['comment']}' />
                            <input type='hidden' name='effectiveDates' value='{$row['effective_date']}' />
                            <input type='hidden' name='user' value='{$row['user']}' />
                            <input type='hidden' name='requesting_user' value='{$row['requesting_user']}' />
                            <input type='hidden' name='type' value='{$row['type']}' />
                            <input type='hidden' name='id' value='{$row['id']}' />");
            if(isset($otherData['thesisTitle'])){
                $wgOut->addHTML("<input type='hidden' name='thesis' value='{$otherData['thesisId']}' />");
            }
            else if(isset($otherData['where'])){
                $wgOut->addHTML("<input type='hidden' name='where' value='{$otherData['where']}' />");
            }
            if($history){
                if($row['created']){
                    $wgOut->addHTML("<td>Accepted</td>");
                }
                else{
                    $wgOut->addHTML("<td>Ignored</td>");
                }
            }
            else{
                $wgOut->addHTML("<td><input type='submit' name='submit' value='Accept' /></td> <td><input type='submit' name='submit' value='Ignore' /></td>");
            }
            $wgOut->addHTML("
                        </form>
                    </tr>");
        }
        $wgOut->addHTML("</tbody></table><script type='text/javascript'>
                                            $('#requests').dataTable({'autoWidth': false}).fnSort([[2,'desc']]);
                                            $('#requests').css('display', 'table');
                                         </script>");
    }
    
    function generateEditMemberFormHTML($wgOut){
        global $wgServer, $wgScriptPath, $wgUser, $config;
        $me = Person::newFromId($wgUser->getId());
        $person = Person::newFromName(str_replace(" ", ".", $_GET['name']));
        $wgOut->addHTML("<form id='editMember' action='$wgServer$wgScriptPath/index.php/Special:EditMember?project' method='post'>
        <p>Select the Roles and Projects to which <b>{$person->getNameForForms()}</b> should be a member of.  Deselecting a role or project will prompt further questions, relating to the reason why they are leaving that role.  All actions will need to be approved by an Administrator.</p>");
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
        $wgOut->addHTML("<hr />");
        $checked = ($person->isCandidate()) ? " checked" : "";
        $wgOut->addHTML("&nbsp;<input id='candidate' type='checkbox' name='candidate' value='true' $checked />&nbsp;Candidate?");
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
            $toolbox['People']['links'][] = TabUtils::createToolboxLink("Edit Roles", "$wgServer$wgScriptPath/index.php/Special:EditMember");
        }
        return true;
    }
}

?>
