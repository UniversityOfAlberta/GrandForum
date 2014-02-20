<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['MailingListRequest'] = 'MailingListRequest'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['MailingListRequest'] = $dir . 'MailingListRequest.i18n.php';
$wgSpecialPageGroups['MailingListRequest'] = 'other-tools';

function runMailingListRequest($par) {
  MailingListRequest::run($par);
}

class MailingListRequest extends SpecialPage{

    function MailingListRequest() {
        wfLoadExtensionMessages('MailingListRequest');
        SpecialPage::SpecialPage("MailingListRequest", 'Leadership+', true, 'runMailingListRequest');
    }

    function run($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
        $groups = $wgUser->getGroups();
        $me = Person::newFromId($wgUser->getId());
        if(isset($_GET['action']) && $_GET['action'] == "view" && array_search("sysop", $groups) !== false){
            if(isset($_POST['submit']) && $_POST['submit'] == "Accept"){
                // Admin Accepted
                $status = false;
                $user = Person::newFromId($_POST['user']);
                $projectName = explode("@", $_POST['project']);
                $projectName = strtoupper($projectName[0]);
                
                $output = "";
                if($_POST['type'] == "SUB"){
                    $status = MailingList::subscribe(Project::newFromName($projectName), $user, $output);
                }
                else if($_POST['type'] == "UNSUB"){
                    $status = MailingList::unsubscribe(Project::newFromName($projectName), $user, $output);
                }
                if($status){
                    DBFunctions::update('grand_list_request',
                                        array('created' => 1),
                                        array('id' => $_POST['id']));
                    $wgOut->addHTML("User <b>{$user->getNameForForms()}</b> ".strtolower($_POST['type'])."scribed to {$_POST['project']}<br /><br />");
                }
                else{
                    $wgOut->addHTML("User <b>{$user->getNameForForms()}</b> was not ".strtolower($_POST['type'])."scribed to {$_POST['project']}<br /><b>Reason:</b><i> ".implode("<br />", $output)."</i><br /><br />");
                }
            }
            else if(isset($_POST['submit']) && $_POST['submit'] == "Ignore"){
                // Admin Ignored
                DBFunctions::update('grand_list_request',
                                    array('`ignore`' => 1),
                                    array('id' => $_POST['id']));
            }
            MailingListRequest::generateViewHTML($wgOut);
        }
        else if(!isset($_POST['submit'])){
            // Form not entered yet
            if(isset($_GET['sub'])){
                MailingListRequest::generateSubscribeFormHTML($wgOut);
            }
            else if(isset($_GET['unsub'])){
                MailingListRequest::generateUnsubscribeFormHTML($wgOut);
            }
            else{
                $wgOut->addHTML("As a project leader or co-leader, you are able to subscribe and unsubscribe users from the project mailing lists.  After one of the following forms is submitted, an admin will be able to review and accept the request.<br /><br />
                                 <a href='$wgServer$wgScriptPath/index.php/Special:MailingListRequest?sub'>Subscribe User</a><br />
                                 <a href='$wgServer$wgScriptPath/index.php/Special:MailingListRequest?unsub'>Unsubscribe User</a>");
            }
        }
        else{
            // The Form has been entered
            $user = Person::newFromName($_POST['user']);
            if(isset($_GET['sub'])){
                DBFunctions::insert('grand_list_request',
                                    array('requesting_user' => $wgUser->getId(),
                                          'project' => MailingListRequest::parse($_POST['project']),
                                          'user' => $user->getId(),
                                          'type' => 'SUB',
                                          'created' => 0,
                                          '`ignore`' => 0));
                $wgOut->addHTML("The user <b>{$user->getNameForForms()}</b> has been requested to be subscribed to the {$_POST['project']} mailing list.  Once an admin sees this request they will review and accept it.");
            }
            else if(isset($_GET['unsub'])){
                DBFunctions::insert('grand_list_request',
                                    array('requesting_user' => $wgUser->getId(),
                                          'project' => MailingListRequest::parse($_POST['project']),
                                          'user' => $user->getId(),
                                          'type' => 'UNSUB',
                                          'created' => 0,
                                          '`ignore`' => 0));
                $wgOut->addHTML("The user <b>{$user->getNameForForms()}</b> has been requested to be unsubscribed from the {$_POST['project']} mailing list.  Once an admin sees this request they will review and accept it.");
            }
        }
    }
    
    function generateViewHTML($wgOut){
        global $wgScriptPath, $wgServer;
        $wgOut->addHTML("<table class='wikitable sortable' bgcolor='#aaaaaa' cellspacing='1' cellpadding='2' style='text-align:center;'>
                    <tr bgcolor='#F2F2F2'>
                        <th>Requesting User</th> <th>Project List</th> <th>User Name</th> <th>Email</th> <th>Type</th> <th>Accept</th> <th>Ignore</th>
                    </tr>\n");
    
        $rows = DBFunctions::select(array('grand_list_request'),
                                    array('*'),
                                    array('created' => EQ(0),
                                          '`ignore`' => EQ(0)));
        foreach($rows as $row){
            $rUser = Person::newFromId($row['requesting_user']);
            $uUser = Person::newFromId($row['user']);
            $wgOut->addHTML("<tr bgcolor='#FFFFFF'>
                        <td align='left'><a target='_blank' href='{$rUser->getUrl()}'>{$rUser->getNameForForms()}</a></td> <td align='left'>{$row['project']}</td> <td align='left'><a target='_blank' href='{$uUser->getUrl()}'>{$uUser->getNameForForms()}</a></td> <td align='left'>{$uUser->getEmail()}</td> <td align='left'>{$row['type']}</td>
                        <form action='$wgServer$wgScriptPath/index.php/Special:MailingListRequest?action=view&sub' method='post'>
                            <input type='hidden' name='project' value='{$row['project']}' />
                            <input type='hidden' name='user' value='{$row['user']}' />
                            <input type='hidden' name='requesting_user' value='{$row['requesting_user']}' />
                            <input type='hidden' name='type' value='{$row['type']}' />
                            <input type='hidden' name='id' value='{$row['id']}' />
                            <td><input type='submit' name='submit' value='Accept' /></td> <td><input type='submit' name='submit' value='Ignore' /></td>
                        </form>
                    </tr>");
        }
        $wgOut->addHTML("</table>");
    }
    
    function generateSubscribeFormHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath;
        $wgOut->addHTML("To subscribe a new user to a project mailing list, select the project list that the user should be added to, and select the user you wish to add.  If you do not see the user in the drop down list, then it either means one of three possibilities:
        <ol>
            <li>The user is already subscribed.  You can check this by going to the project main page and looking at the mailing list information.</li>
            <li>The user is not a member of the selected project.  You can request to have a project membership change by going to <a href='$wgServer$wgScriptPath/index.php/Special:EditMember' target='_blank'>Role Management</a>.</li>
            <li>The user does not exist.  You can request a new user by going to <a href='$wgServer$wgScriptPath/index.php/Special:AddMember'>Add Member</a>.</li>
        </ol><br />
                        <form action='$wgScriptPath/index.php/Special:MailingListRequest?sub' method='post'>\n");
        $person = Person::newFromId($wgUser->getId());
        $projects = $person->leadership();
        
        // Script to update the list of Users
        $wgOut->addScript("<script type='text/javascript'>
                            $(document).ready(function(){
                                updateList();
                            });
                            function updateList(){
                                var project = $('#projects').val();");
            foreach($projects as $project){
                $wgOut->addScript("if(project == '".strtolower($project->getName())."@forum.grand-nce.ca'){");
                $wgOut->addScript("$('#users').html('');");
                foreach($project->getAllPeople() as $user){
                    if(!MailingList::isSubscribed($project, $user)){
                        $wgOut->addScript("$('#users').append('<option>{$user->getName()}</option>');");
                    }
                }
                $wgOut->addScript("}");
            }
                            
        $wgOut->addScript("}
                        </script>");
        
        $wgOut->addHTML("<table><tr>
                        <td class='mw-label'><label for='wpName'>Project List:</label></td>
                        <td class='mw-input'>
                            <select id='projects' name='project' onchange='updateList();'>");
        foreach($projects as $project){
            $wgOut->addHTML("<option>".strtolower($project->getName())."@forum.grand-nce.ca</option>");
        }
        $wgOut->addHTML("</select>
                    </td>
                    </tr>
                    <tr>
                        <td class='mw-label'><label for='user'>User:</label></td>
                        <td class='mw-input'>");
        $wgOut->addHTML("<select id='users' name='user'>");
        foreach(Project::newFromId($projects[0]->getId())->getAllPeople() as $user){
            if(!MailingList::isSubscribed($projects[0], $user)){
                $wgOut->addHTML("<option>{$user->getName()}</option>\n");
            }
        }
        $wgOut->addHTML("</select>
                        </td>
                    </tr>");
            $wgOut->addHTML("</table>\n");
            $wgOut->addHTML("<tr>
                        <td class='mw-label'></td>
                        <td class='mw-input'>
                            <input type='submit' name='submit' value='Submit Request' />
                        </td>
                    </tr>");
            $wgOut->addHTML("</td></tr></table\n");
        $wgOut->addHTML("</form>\n");
    }
    
        function generateUnsubscribeFormHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath;
        $wgOut->addHTML("To unsubscribe a user from a project mailing list, select the project list that the user should be removed from, and select the user you wish to unsubscribe.<br />
                        <form action='$wgScriptPath/index.php/Special:MailingListRequest?unsub' method='post'>\n");
        $person = Person::newFromId($wgUser->getId());
        $projects = $person->leadership();
        
        // Script to update the list of Users
        $wgOut->addScript("<script type='text/javascript'>
                            $(document).ready(function(){
                                updateList();
                            });
                            function updateList(){
                                var project = $('#projects').val();");
            foreach($projects as $project){
                $wgOut->addScript("if(project == '".strtolower($project->getName())."@forum.grand-nce.ca'){");
                $wgOut->addScript("$('#users').html('');");
                foreach($project->getAllPeople() as $user){
                    if(MailingList::isSubscribed($project, $user)){
                        $wgOut->addScript("$('#users').append('<option>{$user->getName()}</option>');");
                    }
                }
                $wgOut->addScript("}");
            }
                            
        $wgOut->addScript("}
                        </script>");
        
        $wgOut->addHTML("<table><tr>
                        <td class='mw-label'><label for='wpName'>Project List:</label></td>
                        <td class='mw-input'>
                            <select id='projects' name='project' onchange='updateList();'>");
        foreach($projects as $project){
            $wgOut->addHTML("<option>".strtolower($project->getName())."@forum.grand-nce.ca</option>");
        }
        $wgOut->addHTML("</select>
                    </td>
                    </tr>
                    <tr>
                        <td class='mw-label'><label for='user'>User:</label></td>
                        <td class='mw-input'>");
        $wgOut->addHTML("<select id='users' name='user'>");
        foreach(Project::newFromId($projects[0]->getId())->getAllPeople() as $user){
            if(MailingList::isSubscribed($projects[0], $user)){
                $wgOut->addHTML("<option>{$user->getName()}</option>\n");
            }
        }
        $wgOut->addHTML("</select>
                        </td>
                    </tr>");
            $wgOut->addHTML("</table>\n");
            $wgOut->addHTML("<tr>
                        <td class='mw-label'></td>
                        <td class='mw-input'>
                            <input type='submit' name='submit' value='Submit Request' />
                        </td>
                    </tr>");
            $wgOut->addHTML("</td></tr></table\n");
        $wgOut->addHTML("</form>\n");
    }
    
    function parse($text){
        $text = str_replace("'", "&#39;", $text);
        $text = str_replace("\"", "&quot;", $text); 
        return $text;
    }
}

?>
