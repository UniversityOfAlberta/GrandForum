<?php

class ProjectMilestonesTab extends AbstractEditableTab {

    var $project;
    var $visibility;

    function ProjectMilestonesTab($project, $visibility){
        parent::AbstractTab("Milestones");
        $this->project = $project;
        $this->visibility = $visibility;
    }
    
    function generateBody(){
        global $wgUser, $wgOut, $wgServer, $wgScriptPath;
        if($wgUser->isLoggedIn()){
            $project = $this->project;
            $me = Person::newFromId($wgUser->getId());
            if($me->isMemberOf($project) || $me->isRoleAtLeast(MANAGER)){
                $edit = $this->visibility['edit'];
                $dataUrl = "$wgServer$wgScriptPath/index.php?action=getProjectMilestoneTimelineData&project={$project->getId()}";
                $timeline = new Simile($dataUrl);
                $timeline->interval = "50";
                $timeline->popupWidth = "500";
                $timeline->popupHeight = "300";
                /*$wgOut->addScript("<script type='text/javascript'>
                    var firstTimeLoaded = true;
                    function toggleTimeline(){
                        $('#milestoneTimeline').toggle();
                        if($('#timelineButton').html() == 'Show Milestone Timeline'){
                            $('#timelineButton').html('Hide Milestone Timeline');
                            if(firstTimeLoaded){
                                onLoad{$timeline->index}();
                                firstTimeLoaded = false;
                            }
                        }
                        else{
                            $('#timelineButton').html('Show Milestone Timeline');
                        }
                    }
                </script>");
                $this->html .= "<a id='timelineButton' class='button' onClick=\"toggleTimeline();\">Show Milestone Timeline</a><div id='milestoneTimeline' class='pdfnodisplay' style='display:none;'>".$timeline->show()."</div>";
                */
                $this->showPastMilestones();
                $this->html .= "<br />";
                $this->showMilestones();
                return $this->html;
            }
        }
    }

    function generateEditBody(){
        global $wgUser, $wgOut, $wgServer, $wgScriptPath;
        if($wgUser->isLoggedIn()){
            $project = $this->project;
            $me = Person::newFromId($wgUser->getId());
            if($me->isMemberOf($project) || $me->isRoleAtLeast(MANAGER)){
                $this->showEditMilestones();
                return $this->html;
            }
        }
    }
    
    function handleEdit(){
        global $wgMessage, $wgUser;
        $me = Person::newFromId($wgUser->getId());
        $_POST['user_name'] = $me->getName();
        $errors = "";
        $project = $this->project;
        $_POST['project'] = $project->getName();
        if(isset($_POST['m_delete'])){
            foreach($_POST['m_delete'] as $key => $value){
                $sql = "DELETE FROM grand_milestones 
                        WHERE `identifier` = '{$value}'
                        AND `project_id` = '{$project->getId()}'
                        AND `status` = 'New'";
                DBFunctions::execSQL($sql, true);
            }
        }

        foreach($project->getMilestones() as $milestone){
            $key = $milestone->getMilestoneId();
            
            unset($_POST['description']);
            unset($_POST['assessment']);
            unset($_POST['title']);
            unset($_POST['new_title']);
            unset($_POST['end_date']);
            unset($_POST['comment']);
            unset($_POST['status']);
            $skip = false;
            
            if(isset($_POST['m_new_identifier'])){
                foreach($_POST['m_new_identifier'] as $identifier){
                    if($identifier == $milestone->getIdentifier()){
                        $skip = true;
                    }
                }
            }
            
            if(isset($_POST["m_{$key}_year"]) && !$skip){
                //$_POST['description'] = @addslashes(str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST["m_{$key}_description"])));
                $_POST['description'] = htmlspecialchars($_POST["m_{$key}_description"]);
                //$_POST['assessment'] = @addslashes(str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST["m_{$key}_assessment"])));
                $_POST['assessment'] = htmlspecialchars($_POST["m_{$key}_assessment"]);
                $_POST['title'] =  $milestone->getTitle();
                //$_POST['new_title'] = @addslashes(str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST["m_{$key}_title"]))); //$_POST['title'];
                $_POST['new_title'] = htmlspecialchars($_POST["m_{$key}_title"]);
                $_POST['end_date'] = $_POST["m_{$key}_year"]."-".$_POST["m_{$key}_month"];
                //$_POST['comment'] = @addslashes(str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST["m_{$key}_comment"])));
                $_POST['comment'] = htmlspecialchars($_POST["m_{$key}_comment"]);
                $_POST['status'] = $_POST["m_{$key}_status"];
                $_POST['people'] = @$_POST["ni_{$milestone->getMilestoneId()}"];
                
                $current_status = $milestone->getStatus();
                $nochange = false;
                if( $milestone->getDescription() == $_POST['description'] &&
                    $milestone->getAssessment() == $_POST['assessment'] && 
                    $milestone->getTitle() == $_POST['new_title'] && 
                    $milestone->getComment() == $_POST['comment'] && 
                    $milestone->getProjectedEndDate() == $_POST["end_date"]."-00" && count($milestone->getPeople()) == count($_POST['people'])){
                    foreach($milestone->getPeople() as $person){
                        if(array_search($person->getNameForForms(), $_POST['people'])){
                            $nochange = true;
                        }
                        else {
                            $nochange = false;
                        }
                    }
                    $nochange = true;
                }
                
                if($_POST['status'] != 'Closed' && $_POST['status'] != 'Abandoned'){
                    if($nochange){
                        $_POST['status'] = $current_status;
                    }
                    else{
                        $_POST['status'] = "Revised";
                    }
                }
                
                //When we revive the milestone from Closed/Abandoned
                //if( ($current_status == "Closed" || $current_status == "Abandoned") && 
                //        ($_POST['status'] == 'Continuing') ){
                //    $_POST['status'] = "Revised";
                //}
                
                if(isset($_POST['status']) && 
                   $_POST['status'] == $current_status && 
                   $nochange){    
                    $skip = true;
                }
                
                if(isset($_POST['status']) && !$skip){
                    $api = new ProjectMilestoneAPI(true);
                    $error = $api->doAction(true);
                    if($error != ""){
                        $errors .= $error.'<br />';
                    }
                }
            }
        }// milestones foreach
        if(isset($_POST['m_new_title'])){
            $i = 0;
            foreach($_POST['m_new_title'] as $key => $title){
                $_POST['identifier'] = htmlspecialchars($_POST['m_new_identifier'][$key]);
                $_POST['title'] = htmlspecialchars($_POST['m_new_title'][$key]);
                $_POST['new_title'] = htmlspecialchars($_POST['m_new_title'][$key]);
                $_POST['description'] = htmlspecialchars($_POST['m_new_description'][$key]);
                $_POST['assessment'] = htmlspecialchars($_POST['m_new_assessment'][$key]);
                $_POST['end_date'] = $_POST["m_new_year"][$key]."-".$_POST["m_new_month"][$key];
                $_POST['status'] = "New";
                while($i < 1000){
                    if(isset($_POST["ni_new_$i"])){
                        $_POST['people'] = $_POST["ni_new_$i"];
                        $i++;
                        break;
                    }
                    $i++;
                }
                $api = new ProjectMilestoneAPI(false);
                $error = $api->doAction(true);
                if($error != ""){
                    $errors .= $error.'<br />';
                }
            }
        }
        Project::$cache = array();
        $this->project = Project::newFromId($this->project->getId());
        return $errors;
    }
    
    function canEdit(){
        global $wgUser;
        $me = Person::newFromId($wgUser->getId());
        return (!$this->project->deleted && (($this->visibility['isMember'] && $me->isRoleAtLeast(CNI)) || $me->isRoleAtLeast(MANAGER)));
    }
    
    function showMilestones(){
        global $wgServer, $wgScriptPath, $wgUser, $wgOut;
        $project = $this->project;
        $months = array(0 => "--",
                        1 => "January",
                        2 => "February",
                        3 => "March",
                        4 => "April",
                        5 => "May",
                        6 => "June",
                        7 => "July",
                        8 => "August",
                        9 => "September",
                        10 => "October",
                        11 => "November",
                        12 => "December");
        
        $me = Person::newFromId($wgUser->getId());
        
        $milestones = $project->getMilestones();
       
        $this->html .=<<<EOF
            <h2><a href="#" id="curr_milestones_hdr" class='mw-headline'> - Current Project Milestones</a></h2>
            <div id="curr_milestones">
EOF;
                
        $custom_js =<<<EOF
            <script type='text/javascript'>
            $(document).ready(function () {
                $('#curr_milestones_hdr').click(function(e) {
                e.preventDefault();        
                $('#curr_milestones').toggle();
                if( $('#curr_milestones').is(":visible") ){
                    $('#curr_milestones_hdr').text(" - Current Project Milestones");
                }
                else{
                    $('#curr_milestones_hdr').text(" + Current Project Milestones");
                }
             });   
EOF;

        foreach($milestones as $milestone){
            $key = $milestone->getMilestoneId();
            $title = $milestone->getTitle();
            $description = nl2br($milestone->getDescription());
            $assessment = nl2br($milestone->getAssessment());
            $start_date = date_parse($milestone->getVeryStartDate());
            $end_date = date_parse($milestone->getProjectedEndDate());
            $status = $milestone->getStatus();
            
            $history_html = $milestone->getHistoryPopup();

            $peopleInvolved = array();
            foreach($milestone->getPeople() as $person){
                $peopleInvolved[] = "<a href='{$person->getUrl()}'>{$person->getReversedName()}</a>";
            }
            $people = "";
            if(count($peopleInvolved) > 0){
                $people = implode("; ", $peopleInvolved);
                $people = "<tr>
                            <td align='right' valign='top'><b>People Involved:</b></td>
                            <td>{$people}</td>
                        </tr>";
            }
            
            $lastEdit = "";
            if($milestone->getEditedBy() != null && $milestone->getEditedBy()->getName() != ""){
                $lastEdit = "<tr>
                            <td align='right' valign='top'><b>Last Edited By:</b></td>
                            <td><a href='{$milestone->getEditedBy()->getUrl()}'>{$milestone->getEditedBy()->getReversedName()}</a></td>
                        </tr>";
            }
            
            $this->html .=<<<EOF
                <fieldset>
                <legend><b>$title</b></legend>
                <table>
                    <tr>
                        <td align='right' valign='top'><b>Start&nbsp;Date:</b></td>
                        <td>{$months[$start_date['month']]}, {$start_date['year']}</td>
                    </tr>
                    <tr>
                        <td align='right' valign='top'><b>Projected&nbsp;End&nbsp;Date:</b></td>
                        <td>{$months[$end_date['month']]}, {$end_date['year']}</td>
                    </tr>
                    <tr>
                        <td style='vertical-align:top;' align='right'><b>Status:</b></td>
                        <td>$status</td>
                    </tr>
                    $people
                    <tr>
                        <td align='right' valign='top'><b>Description:</b></td>
                        <td>{$description}</td>
                    </tr>
                    <tr>
                        <td align='right' valign='top'><b>Assessment:</b></td>
                        <td>{$assessment}</td>
                    </tr>
                    $lastEdit
                </table>
                <br />
                $history_html
                </fieldset>
EOF;
  
        }// milestones loop
        
        $this->html .= "</div>";    
        $custom_js .= "});</script>";
        $wgOut->addScript($custom_js);
                    
    }

    function showPastMilestones(){
        global $wgServer, $wgScriptPath, $wgUser, $wgOut;
        $project = $this->project;
        $months = array(0 => "--",
                        1 => "January",
                        2 => "February",
                        3 => "March",
                        4 => "April",
                        5 => "May",
                        6 => "June",
                        7 => "July",
                        8 => "August",
                        9 => "September",
                        10 => "October",
                        11 => "November",
                        12 => "December");
        
        $me = Person::newFromId($wgUser->getId());
        
        $milestones = $project->getPastMilestones();
       
        $this->html .=<<<EOF
            <h2><a href="#" id="past_milestones_hdr" class='mw-headline'> + Past Project Milestones</a></h2>
            <div id="past_milestones" style="display:none">
EOF;
                
        $custom_js =<<<EOF
            <script type='text/javascript'>
            $(document).ready(function () {
                $('#past_milestones_hdr').click(function(e) {
                e.preventDefault();        
                $('#past_milestones').toggle();
                if( $('#past_milestones').is(":visible") ){
                    $('#past_milestones_hdr').text(" - Past Project Milestones");
                }
                else{
                    $('#past_milestones_hdr').text(" + Past Project Milestones");
                }
            });   
EOF;

        foreach($milestones as $milestone){
            $key = $milestone->getMilestoneId();
            $title = $milestone->getTitle();
            $description = nl2br($milestone->getDescription());
            $assessment = nl2br($milestone->getAssessment());
            $start_date = date_parse($milestone->getVeryStartDate());
            $end_date = date_parse($milestone->getProjectedEndDate());
            $status = $milestone->getStatus();
            
            $history_html = $milestone->getHistoryPopup();
            
            $lastEdit = "";
            if($milestone->getEditedBy() != null && $milestone->getEditedBy()->getName() != ""){
                $lastEdit = "<tr>
                            <td align='right' valign='top'><b>Last Edited By:</b></td>
                            <td><a href='{$milestone->getEditedBy()->getUrl()}'>{$milestone->getEditedBy()->getNameForForms()}</a></td>
                        </tr>";
            }
            
            $peopleInvolved = array();
            foreach($milestone->getPeople() as $person){
                $peopleInvolved[] = "<a href='{$person->getUrl()}'>{$person->getNameForForms()}</a>";
            }
            $people = "";
            if(count($peopleInvolved) > 0){
                $people = implode("; ", $peopleInvolved);
                $people = "<tr>
                            <td align='right' valign='top'><b>People Involved:</b></td>
                            <td>{$people}</td>
                        </tr>";
            }
            
            $this->html .=<<<EOF
                <fieldset>
                <legend><b>$title</b></legend>
                <table>
                    <tr>
                        <td align='right' valign='top'><b>Start&nbsp;Date:</b></td>
                        <td>{$months[$start_date['month']]}, {$start_date['year']}</td>
                    </tr>
                    <tr>
                        <td align='right' valign='top'><b>Projected&nbsp;End&nbsp;Date:</b></td>
                        <td>{$months[$end_date['month']]}, {$end_date['year']}</td>
                    </tr>
                    <tr>
                        <td style='vertical-align:top;' align='right' valign='top'><b>Status:</b></td>
                        <td>$status</td>
                    </tr>
                    $people
                    <tr>
                        <td align='right' valign='top'><b>Description:</b></td>
                        <td>{$description}</td>
                    </tr>
                    <tr>
                        <td align='right' valign='top'><b>Assessment:</b></td>
                        <td>{$assessment}</td>
                    </tr>
                    $lastEdit
                </table>
                <br />
                $history_html
                </fieldset>
EOF;
        }// milestones loop
        
        $this->html .= "</div>";
        $custom_js .= "});</script>";
        $wgOut->addScript($custom_js);
                    
    }

    function showEditMilestones(){
        global $wgServer, $wgScriptPath, $wgUser, $wgOut;
        $project = $this->project;
        $months = array(0 => "--",
                        1 => "January",
                        2 => "February",
                        3 => "March",
                        4 => "April",
                        5 => "May",
                        6 => "June",
                        7 => "July",
                        8 => "August",
                        9 => "September",
                        10 => "October",
                        11 => "November",
                        12 => "December");
        
        $me = Person::newFromId($wgUser->getId());
        
        $milestones = $project->getMilestones();
        
        $this->html .= "<h2><span class='mw-headline'>Current Project Milestones</span></h2>";
        $date = date("Y-n");
        $date_pkr = str_replace("\"", "'", self::date_picker('new', $date, 2010, 2030));
        
        $personNames = array();
        
        $allPeople = Person::getAllPeople('all');
        $list = array();
        foreach($allPeople as $person){
            if($person->isRoleAtLeast(CNI) && $person->isMemberOf($project)){
                if(array_search($person->getNameForForms(), $personNames) === false){
                    $list[] = $person->getNameForForms();
                }
            }
        }
        
        $names = implode("</span><span>", $personNames);
        $list = implode("</span><span>", $list);
        
        $edit_js = <<<EOF
            <script type='text/javascript'>
            function clearCheck(id){
                $('#' + id).attr('checked',false);
            }
            
            function showComment(id){
                var checked = $('#' + id).is(':checked');
                if(checked){
                    id = id.replace(/_abandoned/g, '_comment').replace(/_closed/g, '_comment');
                    $('#' + id).css('display', 'table-row');
                }
                else{
                    id = id.replace(/_abandoned/g, '_comment').replace(/_closed/g, '_comment');
                    $('#' + id).css('display', 'none');
                }
            }
            
            var mI = 0;
            
            function addMilestone(){
                var d = new Date();
                var uniqueId = d.getTime();
            
                var html =  "<fieldset id='mI" + mI + "'>" +
                            "<legend><b>Title:</b> <input style='font-weight:bold; width: 350px;' id='" + uniqueId + "' type='text' name='m_new_title[]' /></legend>" +
                            "<input id='" + mI + "identifier' type='hidden' name='m_new_identifier[]' value='" + uniqueId + "' />" +
                            "<div style='display:table-row; width:50%; float:left; padding-top:20px;'>" +
                            "<table>" +
                            "<tr>" +
                            "<td align='right' style='padding: 3px 10px 3px 0px;'><b>Projected End Date:</b></td>" +
                            "<td>$date_pkr</td>" +
                            "</tr>" +
                            "<tr>" +
                            "<td align='right' style='vertical-align:top;padding: 3px 10px 3px 0px;'><b>Status:</b></td><td>New</td>" +
                            "</tr>" +
                            "</table>" +
                            "</div>" +
                            "<div style='display:table-row; width:50%;float:right;'>" +
                            "<table>" +
                            "<tr>" + 
                            "<td><b>Description:</b><br />"+
                            "<textarea style='width:450px;height:155px;' name='m_new_description[]'></textarea></td></tr>" +
                            "<tr>" +
                            "<td><b>Assessment:</b><br />" +
                            "<textarea style='width:450px;height:121px;' name='m_new_assessment[]'></textarea></td></tr>" +
                            "</table>" +
                            "</div>" +
                            "<div class='switcheroo noCustom' style='float:left;' name='Involved NI' id='ni_new_" + mI + "'>" +
                            "<div class='left'><span>$names</span></div>" +
                            "<div class='right'><span>$list</span></div>" +
                            "</div>" +
                            "<a style='display:block; clear:both;' href='javascript:removeMilestone(" + mI + ");'>[Remove Milestone]</a>" +
                            "</fieldset>";
                $('#newMilestones').append(html);
                createSwitcheroos();
                mI++;
            }
            
            function removeMilestone(id){
                var identifier = $('#' + id + 'identifier').attr('value');
                $('#mI' + id).detach();
                $('#newMilestones').append('<input type="hidden" name="m_delete[]" value="' + identifier + '" />');
                saveAll();
            }
            </script>
EOF;
        $wgOut->addScript($edit_js);
        
        $wgOut->addScript("<script type='text/javascript' src='$wgServer$wgScriptPath/scripts/switcheroo.js'></script>");
        $custom_js =<<<EOF
            <script type='text/javascript'>
            $(document).ready(function () {
                
EOF;
        foreach($milestones as $milestone){
            $key = $milestone->getMilestoneId();
            $title = $milestone->getTitle();
            $description = $milestone->getDescription();
            $assessment = $milestone->getAssessment();
            $start_date = date_parse($milestone->getVeryStartDate());
            $end_date = date_parse($milestone->getProjectedEndDate());
            $comment = $milestone->getComment();
            $status = $milestone->getStatus();
            
            //Get the history of the milestone
            $history_html = "";
            $parents = array();
        
            $m_parent = $milestone;
            while(!is_null($m_parent)){
                $parents[] = $m_parent;
                $m_parent = $m_parent->getParent();
            }
            $parents = array_reverse($parents);
            
            foreach($parents as $m_parent){    
                $p_status = $m_parent->getStatus();
                if($p_status == "Continuing"){
                    continue;
                }
                $changed_on = $m_parent->getStartDate();
                $p_title = $m_parent->getTitle();
                $p_end_date = $m_parent->getProjectedEndDate();
                $p_description = nl2br($m_parent->getDescription());
                $p_assessment = nl2br($m_parent->getAssessment());
                $p_comment = nl2br($m_parent->getComment());
                if($p_comment){
                    $p_comment = "<br /><strong>Comment:</strong> $p_comment";
                }
                if($p_status == "New"){
                    $label = "Created";
                }
                else{
                    $label = $status;
                }
                
                $peopleInvolved = array();
                foreach($m_parent->getPeople() as $person){
                    $peopleInvolved[] = "<a href='{$person->getUrl()}'>{$person->getNameForForms()}</a>";
                }
                $people = "";
                if(count($peopleInvolved) > 0){
                    $people = implode("; ", $peopleInvolved);
                    $people = "<strong>People Involved:</strong> $people<br />";
                }
                
                $lastEdit = "";
                if($m_parent->getEditedBy() != null && $m_parent->getEditedBy()->getName() != ""){
                    $lastEdit = "<strong>Last Edited By:</strong> <a href='{$m_parent->getEditedBy()->getUrl()}'>{$m_parent->getEditedBy()->getNameForForms()}</a><br />";
                }
                
                $history_html .=<<<EOF
                 <div style="padding: 10px; 0;"> 
                 <strong>$label</strong> on $changed_on<br />
                 <strong>Projected End Date:</strong> $p_end_date<br />
                 <strong>Title:</strong> $p_title<br />
                 $people
                 <strong>Description:</strong> $p_description<br />
                 <strong>Assessment:</strong> $p_assessment
                 $p_comment<br />
                 $lastEdit
                 </div>
                 <hr />    
EOF;
            }
            if($history_html != ""){
                $history_dialog_id = "history_m_$key";
                $custom_js .= "$(\"#$history_dialog_id\").dialog({ autoOpen: false, height: 600, width: 800 });";
                $history_html =<<<EOF
                <a class="pdf_hide" style="font-style:italic; font-weight:bold;" href="#" onclick="$('#$history_dialog_id').dialog('open'); return false;">See Milestone History</a>
                <div class="pdf_hide" title="Milestone History" style="white-space: pre-line;" id="$history_dialog_id">$history_html</div>
EOF;
            }
            

            $date = "{$end_date['year']}-{$end_date['month']}";
            $datepkr = self::date_picker($key, $date, 2010, 2030);

            $checked_continuing = "";
            $checked_closed = "";
            $checked_abandoned = "";
            if($status == "Continuing" || $status == "Revised" || $status == "New" ){
                $checked_continuing = "checked='checked'";
            }
            else if($status == "Closed"){
                $checked_closed = "checked='checked'";
                $status = "Revised";
            }
            else if($status == "Abandoned"){
                $checked_abandoned = "checked='checked'";
                $status = "Revised";
            }
            $status_checkboxes =<<<EOF
            <input type='radio' value='$status' name="m_{$key}_status" $checked_continuing /> Continuing<br />
            <input type='radio' id='m_{$key}_closed' value='Closed' name='m_{$key}_status' $checked_closed /> Closed <br />
            <input type='radio' id='m_{$key}_abandoned' value='Abandoned' name='m_{$key}_status' $checked_abandoned  /> Abandoned <br />        
EOF;

            $peopleInvolved = $milestone->getPeople();
            $personNames = array();
            foreach($peopleInvolved as $person){
                $personNames[] = $person->getNameForForms();
            }
            
            $allPeople = Person::getAllPeople('all');
            $list = array();
            foreach($allPeople as $person){
                if($person->isRoleAtLeast(CNI) && $person->isMemberOf($milestone->getProject())){
                    if(array_search($person->getNameForForms(), $personNames) === false){
                        $list[] = $person->getNameForForms();
                    }
                }
            }
            $person_html = "<div class='switcheroo noCustom' style='display:table-row;' name='Involved NI' id='ni_{$milestone->getMilestoneId()}'>
                                <div class='left'><span>".implode("</span>\n<span>", $personNames)."</span></div>
                                <div class='right'><span>".implode("</span>\n<span>", $list)."</span></div>
                            </div>";

            $this->html .=<<<EOF
            <fieldset>
                <legend>
                <input type='text' style='font-weight:bold; width: 350px;' name='m_{$key}_title' value="$title" />
                </legend>
                <div class="milestone_meta" style="display:table-row; width:50%;float:left;">
                <table>
                <tr>
                <td align="right" style="padding: 3px 10px 3px 0px;">
                    <b>Start Date:</b></td><td>{$months[$start_date['month']]}, {$start_date['year']}
                </td>
                </tr>
                <tr>
                <td align="right" style="padding: 3px 10px 3px 0px;">
                    <b>Projected End Date:</b>
                </td>
                <td>$datepkr</td>
                </tr>
                <tr>
                <td valing='top' align="right" style="vertical-align:top;padding: 3px 10px 3px 0px;"><b>Status:</b></td>
                <td>
                <div>
                $status_checkboxes
                </div>
                </td>
                </tr>
                </table>
                <div id='m_{$key}_comment' style='margin-top:44px;'>
                <b>Comment:</b><br />
                <textarea style='width:395px;height:100px;' name='m_{$key}_comment'>$comment</textarea>
                </div>
                
                $history_html
                
                </div>
                <div class="milestone_descr" style="display:table-row; width:50%;">
                <table>
                <tr>
                <td>
                <b>Description:</b><br />
                <textarea name='m_{$key}_description' style='width:450px;height:155px;'>$description</textarea>
                </td>
                </tr>
                <tr>
                <td>
                <b>Assessment:</b><br />
                <textarea name='m_{$key}_assessment' style='width:450px;height:121px;'>$assessment</textarea> 
                </td>
                </tr>
                </table>
                </div>
                $person_html
                </fieldset>
EOF;

        }// milestones loop
        
        $custom_js .= "});</script>";
        $wgOut->addScript($custom_js);
        
        $this->html .=<<<EOF
            <h2><span class='mw-headline'>New Milestone</span></h2>
            <div id='newMilestones'></div>
            <a href='javascript:addMilestone();'>[Add Milestone]</a><br /><br /><hr />
EOF;
        
    }
    
    function date_picker($key, $date, $startyear=NULL, $endyear=NULL){
        if($key == "new"){
            $keyArray = "[]";
        }
        else{
            $keyArray = "";
        }
        $newDate = explode("-", $date);
        $year = @$newDate[0];
        $month = @$newDate[1];
        if($startyear==NULL){
            $startyear = date("Y")-100;
        }
        if($endyear==NULL){
            $endyear=date("Y")+50;
        }

        $months=array('','January','February','March','April','May',
        'June','July','August', 'September','October','November','December');

        // Month dropdown
        $html="<select name=\"m_{$key}_month$keyArray\">";

        for($i=1;$i<=12;$i++){
            $selected = "";
            if($month == $i){
                $selected = "selected='selected'";
            }
            if($i < 10){
                $id = "0".$i;
            }
            else{
                $id = $i;
            }
            $html.="<option $selected value='$id'>$months[$i]</option>";
        }
        $html.="</select> ";

        // Year dropdown
        $html.="<select name=\"m_{$key}_year$keyArray\">";

        for($i=$endyear;$i>=$startyear;$i--){ 
            $selected = "";
            if($year == $i){
                $selected = "selected='selected'";
            }     
            $html.="<option $selected value='$i'>$i</option>";
        }
        $html.="</select> ";

        return $html;
    }

}    
    
?>
