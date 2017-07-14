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

    function EditMember() {
        SpecialPage::__construct("EditMember", STAFF.'+', true, 'runEditMember');
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage, $config;
        $me = Person::newFromWgUser();
        $date = date("Y-m-d");
        $wgOut->addScript("<script type='text/javascript'>
                            $(document).ready(function(){
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

            $me = Person::newFromId($wgUser->getId());

            $wgOut->addHTML("<a href='$wgServer$wgScriptPath/index.php/Special:EditMember'>Click Here</a> to continue Editing Members.");

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
                Cache::delete("idsCache_{$person->getId()}");
                $person->candidate = true;
                $wgMessage->addSuccess("<b>{$person->getReversedName()}</b> is now a candidate user");
                MailingList::subscribeAll($person);
            }
            else if(!isset($_POST['candidate']) && $person->isCandidate()){
                MailingList::unsubscribeAll($person);
                DBFunctions::update('mw_user',
                                    array('candidate' => '0'),
                                    array('user_id' => EQ($person->getId())));
                Cache::delete("idsCache_{$person->getId()}");
                $person->candidate = false;
                $wgMessage->addSuccess("<b>{$person->getReversedName()}</b> is now a full user");
                MailingList::subscribeAll($person);
            }
            
            // Project Leadership Changes
            $pl = array();
            $pm = array();
            if(isset($_POST['pl'])){
                foreach($_POST['pl'] as $value){
                    $pl[$value] = $value;
                }
            }
        
            $currentPL = array();
            // Removing Project Leaders
            foreach($person->leadership() as $project){
                if(!isset($pl[$project->getName()])){
                    // Remove Project Leadership
                    $_POST['co_lead'] = 'False';
                    $_POST['manager'] = 'False';
                    $_POST['role'] = $project->getName();
                    $_POST['user'] = $person->getName();
                    $_POST['comment'] = @str_replace("'", "", $_POST["pl_comment"][$project->getName()]);
                    $_POST['effective_date'] = @$_POST["pl_datepicker"][$project->getName()];
                    APIRequest::doAction('DeleteProjectLeader', true);
                    $wgMessage->addSuccess("<b>{$person->getReversedName()}</b> is no longer a ".strtolower($config->getValue('roleDefs', PL))." of {$project->getName()}");
                }
                $currentPL[$project->getName()] = $project->getName();
            }
            
            // Adding Project Leaders
            foreach($pl as $project){
                if(!isset($currentPL[$project])){
                    // Add Project Leadership
                    $_POST['co_lead'] = 'False';
                    $_POST['manager'] = 'False';
                    $_POST['role'] = $project;
                    $_POST['user'] = $person->getName();
                    APIRequest::doAction('AddProjectLeader', true);
                    $wgMessage->addSuccess("<b>{$person->getReversedName()}</b> is now a ".strtolower($config->getValue('roleDefs', PL))." of {$project}");
                }
            }
            
            // Theme Leadership Changes
            $tl = array();
            if(isset($_POST['tl'])){
                foreach($_POST['tl'] as $value){
                    $tl[$value] = Theme::newFromId($value);
                }
            }
        
            $currentTL = array();
            // Removing Theme Leaders
            foreach($person->getLeadThemes() as $theme){
                if(!isset($tl[$theme->getId()])){
                    // Remove Theme Leadership
                    $_POST['coordinator'] = 'False';
                    $_POST['co_lead'] = 'False';
                    $_POST['theme'] = $theme->getId();
                    $_POST['name'] = $person->getName();
                    $_POST['comment'] = @str_replace("'", "", $_POST["tl_comment"][$theme->getId()]);
                    $_POST['effective_date'] = $_POST["tl_datepicker"][$theme->getId()];
                    APIRequest::doAction('DeleteThemeLeader', true);
                    $wgMessage->addSuccess("<b>{$person->getReversedName()}</b> is no longer a ".strtolower($config->getValue('roleDefs', TL))." of {$theme->getAcronym()}");
                }
                $currentTL[$theme->getId()] = $theme->getId();
            }
            
            // Adding Theme Leaders
            foreach($tl as $theme){
                if(!isset($currentTL[$theme->getId()])){
                    // Add Theme Leadership
                    $_POST['coordinator'] = 'False';
                    $_POST['co_lead'] = 'False';
                    $_POST['theme'] = $theme->getId();
                    $_POST['name'] = $person->getName();
                    APIRequest::doAction('AddThemeLeader', true);
                    $wgMessage->addSuccess("<b>{$person->getReversedName()}</b> is now a ".strtolower($config->getValue('roleDefs', TL))." of {$theme->getAcronym()}");
                }
            }
            
            // Theme Coordinator Changes
            $tc = array();
            if(isset($_POST['tc'])){
                foreach($_POST['tc'] as $value){
                    $tc[$value] = Theme::newFromId($value);
                }
            }
        
            $currentTC = array();
            // Removing Theme Coordinators
            foreach($person->getCoordThemes() as $theme){
                if(!isset($tc[$theme->getId()])){
                    // Remove Theme Coordinator
                    $_POST['coordinator'] = 'True';
                    $_POST['co_lead'] = 'False';
                    $_POST['theme'] = $theme->getId();
                    $_POST['name'] = $person->getName();
                    $_POST['comment'] = @str_replace("'", "", $_POST["tc_comment"][$theme->getId()]);
                    $_POST['effective_date'] = @$_POST["tc_datepicker"][$theme->getId()];
                    APIRequest::doAction('DeleteThemeLeader', true);
                    $wgMessage->addSuccess("<b>{$person->getReversedName()}</b> is no longer a ".strtolower($config->getValue('roleDefs', TC))." of {$theme->getAcronym()}");
                }
                $currentTC[$theme->getId()] = $theme->getId();
            }
            
            // Adding Theme Coordinators
            foreach($tc as $theme){
                if(!isset($currentTC[$theme->getId()])){
                    // Add Theme Coordinator
                    $_POST['coordinator'] = 'True';
                    $_POST['co_lead'] = 'False';
                    $_POST['theme'] = $theme->getId();
                    $_POST['name'] = $person->getName();
                    APIRequest::doAction('AddThemeLeader', true);
                    $wgMessage->addSuccess("<b>{$person->getReversedName()}</b> is now a ".strtolower($config->getValue('roleDefs', TC))." of {$theme->getAcronym()}");
                }
            }
        }
    }
    
    function generateMain(){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
        $me = Person::newFromWgUser();
        $wgOut->addScript('<script type="text/javascript">
                            var sort = "first";
                            var allPeople = new Array(');
        $allPeople = array_merge(Person::getAllPeople('all'), Person::getAllCandidates('all'));
        $i = 0;
        $names = array();
        foreach($allPeople as $person){
            if(!$me->isAllowedToEdit($person)){ 
                // User does not have permission for this person
                continue;
            }
            $names[] = $person->getName();
        }
        if($me->isRoleAtLeast(STAFF)){
            foreach(Person::getAllStaff() as $person){
                $names[] = $person->getName();
            }
        }
        $wgOut->addScript('\''.implode("','", $names).'\');
        var oldOptions = Array();

        function filterResults(value){
            $.each($("#names").children().not("#no"), function(index, value){
                var valSelect = value.id;
                oldOptions[valSelect] = $(value).detach();
            });
            
            if(value == ""){
                var no = $("#no").detach();
                if(no.length > 0){
                    oldOptions["no"] = no;
                }
            }
            var n = 0;
            var tmpDom = $("<select>");
            for(i = 0; i < allPeople.length; i++){
                var val = allPeople[i];
                var valSelect = "";
                if(sort == "last" && val.indexOf(".") != -1){
                    if(val.split(".").length == 2){
                        var firstName = val.split(".")[0];
                        var lastName = val.split(".")[1];
                    }
                    else{
                        var firstName = val.substring(0, val.lastIndexOf("."));
                        var lastName = val.split(".")[val.split(".").length - 1];
                    }
                    valSelect = lastName + firstName;
                    valSelect = valSelect.replace(/\./g, "");
                }
                else{
                    valSelect = val.replace(/\./g, "");
                }
                if(unaccentChars(val.replace(/\./g, " ")).regexIndexOf(unaccentChars(value)) != -1 || (typeof oldOptions[valSelect] != "undefined" && unaccentChars(oldOptions[valSelect].attr("class")).regexIndexOf(unaccentChars(value)) != -1)){
                    if(typeof oldOptions[valSelect] != "undefined"){
                        oldOptions[valSelect].appendTo(tmpDom);
                    }
                    n++;
                }
            }
            $("#names").html(tmpDom.html());
            if(n == 0){
                if(typeof oldOptions["no"] != "undefined"){
                    oldOptions["no"].appendTo($("#names"));
                }
            }
            else{
                var no = $("#no").detach();
                if(no.length > 0){
                    oldOptions["no"] = no;
                }
            }
        }
        
        function sortBy(type){
            var newAllPeople = Array();
            for(i = 0; i < allPeople.length; i++){
                var fullName = allPeople[i];
                var firstName = "";
                var lastName = "";
                if(fullName.indexOf(".") != -1){
                    if(type == "last" && sort == "first" || type == "first" && sort == "last"){
                        firstName = fullName.split(".")[0];
                        if(fullName.split(".").length == 2){
                            lastName = fullName.split(".")[1];
                        }
                        else{
                            lastName = fullName.substring(fullName.indexOf(".") + 1);
                        }
                    }
                    else{
                        if(fullName.split(".").length == 2){
                            firstName = fullName.split(".")[1];
                        }
                        else{
                            firstName = fullName.substring(fullName.indexOf(".") + 1);
                        }
                        lastName = fullName.split(".")[0];
                    }
                    newAllPeople[i] = lastName + "." + firstName;
                }
                else{
                    newAllPeople[i] = fullName;
                }
            }
            sort = type;
            allPeople = newAllPeople;
            allPeople.sort();
            filterResults($("#search").val());
        }
        
        $(document).ready(function(){
            var no = $("#no").detach();
            if(no.length > 0){
                oldOptions["no"] = no;
            }
            
            $("#search").keypress(function(event) {
                if(event.keyCode == 40){        //DOWN
                    $.each($("#names").children(":selected").not("#no"), function(index, value){
                        if($(value).next().length > 0){
                            $(value).attr("selected", false);
                            $(value).next().attr("selected", true);
                        }
                    });
                }
                else if(event.keyCode == 38){   //UP
                    $.each($("#names").children(":selected").not("#no"), function(index, value){
                        if($(value).prev().length > 0){
                            $(value).attr("selected", false);
                            $(value).prev().attr("selected", true);
                        }
                    });
                }
            });
            
            $("#search").keyup(function(event) {
                if(event.keyCode != 40 && event.keyCode != 38){
                    filterResults(this.value);
                }
            });
            sortBy("first");
        });
        </script>');
        if($me->isRoleAtLeast(STAFF)){
            foreach(Person::getAllStaff() as $person){
                $allPeople[] = $person;
            }
        }
        $wgOut->addHTML("This page can be used to edit the roles and projects of members on the forum.<br />
                         Select a user from the list below, and then click the 'Next' button.  You can filter out the selection box by searching a name or project below.<table>
                            <tr><td>
                                <a href='javascript:sortBy(\"first\");'>Sort by First Name</a> | <a href='javascript:sortBy(\"last\");'>Sort by Last Name</a><br />
                                <b>Search:</b> <input style='width:100%;' id='search' type='text' />
                            </td></tr>
                            <tr><td>
                            <form action='$wgServer$wgScriptPath/index.php/Special:EditMember' method='post'>
                                <select id='names' name='name' size='10' style='width:100%'>
                                    <option id='no' disabled>Search did not match anyone</option>\n");
        foreach($allPeople as $person){
            $projects = $person->getProjects(false, true);
            $projs = array();
            foreach($projects as $project){
                $projs[] = $project->getName();
            }
            if(!$me->isAllowedToEdit($person)){
                // User does not have permission for this person
                continue;           
            }
            $wgOut->addHTML("<option class='".implode(" ", $projs)."' id='".str_replace(".", "", $person->getName())."' value=\"".str_replace(".", " ", $person->getName())."\">{$person->getNameForForms()}</option>\n");
        }
        $wgOut->addHTML("</select>
                </td></tr>
                <tr><td>
            <input type='submit' name='next' value='Next' />
        </form></td></tr></table>");
    }
    
    function generateEditMemberFormHTML($wgOut){
        global $wgServer, $wgScriptPath, $wgUser, $config;
        $me = Person::newFromId($wgUser->getId());
        $person = Person::newFromName(str_replace(" ", ".", $_GET['name']));
        $wgOut->addHTML("<form id='editMember' action='$wgServer$wgScriptPath/index.php/Special:EditMember?project' method='post'>
        <p>Select the Roles and Projects to which <b>{$person->getNameForForms()}</b> should be a member of.  Deselecting a role or project will prompt further questions, relating to the reason why they are leaving that role.</p>");
        $wgOut->addHTML("<div id='tabs'>
                    <ul>
                        <li><a id='SubRolesTab' href='#tabs-1'>Sub-Roles</a></li>
                        <li><a id='LeadershipTab' href='#tabs-2'>Project Leadership</a></li>
                        <li><a id='ThemesTab' href='#tabs-3'>{$config->getValue('projectThemes')} Leaders</a></li>");
        $wgOut->addHTML("
                    </ul>");
                    
        $wgOut->addHTML("<div id='tabs-1'>");
                            EditMember::generateSubRoleFormHTML($wgOut);
        $wgOut->addHTML("</div>
                         <div id='tabs-2'>");
                            EditMember::generatePLFormHTML($wgOut);
        $wgOut->addHTML("</div>
                         <div id='tabs-3'>");
                            EditMember::generateTLFormHTML($wgOut);
        $wgOut->addHTML("</div>");

        $wgOut->addHTML("</div>");
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

    function generatePLFormHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath;
        if(!isset($_GET['name'])){
            return;
        }
        $person = Person::newFromName(str_replace(" ", ".", $_GET['name']));
        $projects = Project::getAllProjects();
        
        $leadProjects = new Collection($person->leadership());
        $myLeadProjects = $leadProjects->pluck('name');

        $projList = new ProjectList("pl", "Projects", $myLeadProjects, $projects);
        $projList->attr('expand', true);
        $wgOut->addHTML($projList->render());
        $wgOut->addHTML("<script type='text/javascript'>
            $('input.pl.already').change(function(){
                addComment(this, false);
            });
        </script>");
    }
    
    function generateTLFormHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath, $config;
        if(!isset($_GET['name'])){
            return;
        }
        $person = Person::newFromName(str_replace(" ", ".", $_GET['name']));
        $wgOut->addHTML("<h2>{$config->getValue('roleDefs', TL)}</h2>");
        $wgOut->addHTML("<table border='0' cellspacing='2'>");
        $leadThemes = $person->getLeadThemes();
        $themes = Theme::getAllThemes(PROJECT_PHASE);
        foreach($themes as $theme){
            $themeId = $theme->getId();
            $isLead = false;
            foreach($leadThemes as $t){
                if($t->getId() == $themeId){
                    $isLead = true;
                    break;
                }
            }
            if($isLead){
                $wgOut->addHTML("<tr><td style='min-width:150px;' valign='top'><input type='checkbox' name='tl[]' value='$themeId' checked='checked' class='already' onChange='addComment(this, false);' />{$theme->getAcronym()}<div style='display:none; padding-left:30px;'><fieldset><legend>Reasoning</legend><p>Date Effective:<input type='text' class='datepicker' id='tl_datepicker{$themeId}' name='tl_datepicker[$themeId]' /></p>Additional Comments:<br /><textarea name='tl_comment[$themeId]' cols='15' rows='4' style='height:auto;'></textarea></fielset></div><br /></td></tr>\n");
            }
            else {
                $wgOut->addHTML("<tr><td style='min-width:150px;' valign='top'><input type='checkbox' name='tl[]' value='$themeId' />{$theme->getAcronym()}</td></tr>\n");
            }
        }
        $wgOut->addHTML("</table>");
        $wgOut->addHTML("<h2>{$config->getValue('roleDefs', TC)}</h2>");
        $wgOut->addHTML("<table border='0' cellspacing='2'>");
        $coordThemes = $person->getCoordThemes();
        $themes = Theme::getAllThemes(PROJECT_PHASE);
        foreach($themes as $theme){
            $themeId = $theme->getId();
            $isLead = false;
            foreach($coordThemes as $t){
                if($t->getId() == $themeId){
                    $isLead = true;
                    break;
                }
            }
            if($isLead){
                $wgOut->addHTML("<tr><td style='min-width:150px;' valign='top'><input type='checkbox' name='tc[]' value='$themeId' checked='checked' class='already' onChange='addComment(this, false);' />{$theme->getAcronym()}<div style='display:none; padding-left:30px;'><fieldset><legend>Reasoning</legend><p>Date Effective:<input type='text' class='datepicker' id='tc_datepicker{$themeId}' name='tc_datepicker[$themeId]' /></p>Additional Comments:<br /><textarea name='tc_comment[$themeId]' cols='15' rows='4' style='height:auto;'></textarea></fielset></div><br /></td></tr>\n");
            }
            else {
                $wgOut->addHTML("<tr><td style='min-width:150px;' valign='top'><input type='checkbox' name='tc[]' value='$themeId' />{$theme->getAcronym()}</td></tr>\n");
            }
        }
        $wgOut->addHTML("</table>");
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(STAFF)){
            $toolbox['People']['links'][1] = TabUtils::createToolboxLink("Edit Roles", "$wgServer$wgScriptPath/index.php/Special:EditMember");
        }
        return true;
    }
}

?>
