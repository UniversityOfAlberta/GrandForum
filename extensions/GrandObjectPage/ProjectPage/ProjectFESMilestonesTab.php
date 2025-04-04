<?php

class ProjectFESMilestonesTab extends ProjectMilestonesTab {

    function ProjectFESMilestonesTab($project, $visibility){
        parent::ProjectMilestonesTab($project, $visibility);
        parent::AbstractTab("Schedule");
        $this->maxNYears = 10;
        $this->nYears = (isset($_GET['generatePDF'])) ? 10 : $this->maxNYears;
    }
    
    function handleEdit(){
        global $config;
        $startDate = $this->project->getCreated();
        $startYear = substr($startDate, 0, 4);
        $startMonth = substr($startDate, 5, 2);
        $startYear = @substr($config->getValue('projectPhaseDates', PROJECT_PHASE), 0, 4);
        $me = Person::newFromWgUser();
        $_POST['user_name'] = $me->getName();
        $_POST['project'] = $this->project->getName();
        foreach($_POST['milestone_activity'] as $activityId => $activity){
            if(isset($_POST['milestone_activity_delete'][$activityId]) && $me->isRoleAtLeast(STAFF)){
                // Delete the Activity
                DBFunctions::update('grand_activities',
                                    array('deleted' => 1),
                                    array('id' => $activityId));
                continue;
            }
            if(!isset($_POST['milestone_title'][$activityId])){
                // Not created yet, to continue to next
                continue;
            }
            foreach($_POST['milestone_title'][$activityId] as $milestoneId => $title){
                $milestone = Milestone::newFromId($milestoneId);
                if(!$this->canEditMilestone($milestone)){
                    // This person can't edit this milestone, try next one
                    continue;
                }
                
                if(isset($_POST['milestone_delete'][$activityId][$milestoneId]) &&
                   $_POST['milestone_delete'][$activityId][$milestoneId] == 'delete'){
                    DBFunctions::update('grand_milestones',
                                        array('status' => 'Deleted'),
                                        array('id' => $milestone->getId()));
                }
                else{
                    $quarters = array();
                    if(isset($_POST['milestone_q'][$activityId][$milestoneId])){
                        foreach($_POST['milestone_q'][$activityId][$milestoneId] as $year => $qs){
                            foreach($qs as $qId => $q){
                                $quarters[] = ($year).":$qId";
                            }
                        }
                    }
                    
                    if(isset($_POST['milestone_leader'])){
                        $_POST['leader'] = @$_POST['milestone_leader'][$activityId][$milestoneId];
                    }
                    $_POST['activity'] = $activity;
                    $_POST['activity_id'] = $activityId;
                    $_POST['milestone'] = $_POST['milestone_old'][$activityId][$milestoneId];
                    $_POST['title'] = $_POST['milestone_old'][$activityId][$milestoneId];
                    $_POST['new_title'] = $title;
                    $_POST['description'] = @$_POST['milestone_description'][$activityId][$milestoneId];
                    $_POST['end_user'] = @$_POST['milestone_end_user'][$activityId][$milestoneId];
                    $_POST['status'] = $_POST['milestone_status'][$activityId][$milestoneId];
                    $_POST['modification'] = @$_POST['milestone_modification'][$activityId][$milestoneId];
                    $_POST['people'] = $_POST['milestone_people'][$activityId][$milestoneId];
                    $_POST['end_date'] = ($startYear+2)."-12-31 00:00:00";
                    $_POST['quarters'] = implode(",", $quarters);
                    $_POST['comment'] = str_replace(">", "&gt;", str_replace("<", "&lt;", $_POST['milestone_comment'][$activityId][$milestoneId]));
                    $_POST['id'] = $milestoneId;
                    
                    $milestoneApi = new ProjectMilestoneAPI(true);
                    $milestoneApi->doAction(true);
                }
            }
        }
        
        if(isset($_POST['new_activity_title']) && $_POST['new_activity_title'] != "" && $this->canEditMilestone(null)){
            DBFunctions::insert('grand_activities',
                                array('name' => $_POST['new_activity_title'],
                                      'project_id' => $this->project->getId()));
            
            // Still show the edit interface
            redirect("{$this->project->getUrl()}?tab=schedule&edit");
        }
        if(isset($_POST['new_milestone_activity']) && isset($_POST['new_milestone_title']) &&
           $_POST['new_milestone_activity'] != "" && $_POST['new_milestone_title'] != "" && 
           $this->canEditMilestone(null)){
            $activity = Activity::newFromId($_POST['new_milestone_activity']);
            if($_POST['new_milestone_activity'] == 0){
                // Milestone needs to have an auto generated title
                $ms = $this->project->getMilestones(true, true);
                $id = 1;
                if(count($ms) > 0){
                    $last = $ms[count($ms)-1];
                    preg_match("/MST([0-9]*)-.*/", $last->getTitle(), $matches);
                    $id = @intval($matches[1]) + 1;
                }
                $id = str_pad($id, 2, "0", STR_PAD_LEFT);
                $_POST['new_milestone_title'] = "MST{$id}-{$this->project->getName()}";
            }
            $_POST['leader'] = "";
            $_POST['activity'] = $activity->getName();
            $_POST['activity_id'] = $_POST['new_milestone_activity'];
            $_POST['milestone'] = "";
            $_POST['title'] = $_POST['new_milestone_title'];
            $_POST['new_title'] = $_POST['new_milestone_title'];
            $_POST['description'] = "";
            $_POST['end_user'] = "";
            $_POST['status'] = "New";
            $_POST['modification'] = "";
            $_POST['people'] = "";
            $_POST['end_date'] = ($startYear+2)."-12-31 00:00:00";
            $_POST['quarters'] = "";
            $_POST['comment'] = "Created " . date('Y-m-d');
            $_POST['id'] = "";
            unset($_POST['id']);
            //unset($_POST['activity_id']);
            $milestoneApi = new ProjectMilestoneAPI(false);
            $milestoneApi->doAction(true);
            
            // Still show the edit interface 
            redirect("{$this->project->getUrl()}?tab=schedule&edit");
        }
        Messages::addSuccess("'Schedule' updated successfully.");
        redirect("{$this->project->getUrl()}?tab=schedule");
    }
    
    function showYearsHeader(){
        global $config;
        $html = "";
        $startDate = $this->project->getCreated();
        $startYear = substr($startDate, 0, 4);
        $startYear = @substr($config->getValue('projectPhaseDates', PROJECT_PHASE), 0, 4);
        for($y=1; $y <= $this->nYears; $y++){
            $year = $startYear+($y-1);
            if(true || $y < $this->maxNYears){
                $html .= "<th colspan='4' class='left_border'>FY".($y+1)."<br />Apr{$year} – Mar".($year+1)."</th>";
            }
            else {
                $html .= "<th colspan='2' class='left_border'>FY".($y+1)."<br />Apr{$year} – Sep".($year)."</th>";
            }
        }
        return $html;
    }
    
    function showQuartersHeader(){
        $html = "";
        for($y=1; $y <= $this->nYears; $y++){
            if(true || $y < $this->maxNYears){
                $html .= "<th class='left_border'>Q1</th>
                          <th>Q2</th>
                          <th>Q3</th>
                          <th>Q4</th>";
            }
            else {
                $html .= "<th class='left_border'>Q1</th>
                          <th>Q2</th>";
            }
        }
        return $html;
    }
    
    function showQuartersCells($milestone, $activityId){
        global $config, $wgServer, $wgScriptPath;
        $startDate = $this->project->getCreated();
        $startYear = substr($startDate, 0, 4);
        $startYear = @substr($config->getValue('projectPhaseDates', PROJECT_PHASE), 0, 4);
        $quarters = $milestone->getQuarters();
        
        // First need to check if more than one are selected
        $lastY = 0;
        $lastQ = 0;
        for($y=$startYear; $y < $startYear+$this->nYears; $y++){
            $nQuarters = 4;
            if($y == $this->maxNYears+$startYear-1){
                $nQuarters = 4;
            }
            for($q=1;$q<=$nQuarters;$q++){
                if(isset($quarters[$y][$q])){
                    $lastY = $y;
                    $lastQ = $q;
                }
            }
        }
        for($y=$startYear; $y < $startYear+$this->nYears; $y++){
            $nQuarters = 4;
            if($y == $this->maxNYears+$startYear-1){
                //$nQuarters = 2;
            }
            for($q=1;$q<=$nQuarters;$q++){
                $class = ($q == 1) ? "class='left_border'" : "";
                $colors = array_merge(Milestone::$statuses, Milestone::$fesStatuses);
                $color = @$colors[$milestone->getStatus()];
                $color2 = @Milestone::$modifications[$milestone->getModification()];

                $checkbox = "";
                if($this->visibility['edit'] == 1 && $this->canEditMilestone($milestone)){
                    $checked = "";
                    if(isset($quarters[$y][$q])){
                        $checked = "checked='checked'";
                    }
                    $single = "";
                    if($activityId == 0){
                        $single = "single";
                    }
                    $checkbox = "<input data-id='{$activityId}_{$milestone->getMilestoneId()}' class='milestone {$single}' type='checkbox' name='milestone_q[$activityId][{$milestone->getMilestoneId()}][$y][$q]' $checked />";
                }
                if(isset($quarters[$y][$q])){
                    $border = "";
                    if($lastY == $y && $lastQ == $q){
                        if($color2 != "transparent"){
                            $img = (isset($_GET['generatePDF'])) ? "$wgServer$wgScriptPath/skins/".str_replace("#", "", $color2)."_diag_large.png" : 
                                                                   "$wgServer$wgScriptPath/skins/".str_replace("#", "", $color2)."_diag.png";
                            $border = "outline-offset: -2px; outline: 2px solid $color2; background-image: url($img);";
                            if(isset($_GET['generatePDF'])){
                                $border .= "border: 3px solid $color2";
                            }
                        }
                    }
                    else{
                        if($activityId == 0){
                            $color = "#BBBBBB";
                        }
                    }
                    $this->html .= "<td style='background:$color; $border; text-align:center;' $class>$checkbox</td>";
                }
                else{
                    $this->html .= "<td style='text-align:center;' $class>$checkbox</td>";
                }
            }
        }
    }
    
    function generateBody(){
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(HQP) && ($me->isMemberOf($this->project) || $me->isRoleAtLeast(STAFF))){
            $this->html .= "<div id='activitySchedule'><h2 style='margin-top:0;padding-top:0;'>Activity Schedule</h2>";
            $this->showMilestones(false, false, true);
            $this->html .= "</div>";
            $this->addScript();
            $this->html .= "<h2 style='clear:both;'>Milestones</h2>";
            $this->showFESMilestones();
            if(!$me->isRoleAtLeast(STAFF)){
                $this->html .= "<style type='text/css' rel='stylesheet'>
                    #activitySchedule {
                        display: none;
                    }
                </style>";
            }
        }
    }
    
    function generateEditBody(){
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(HQP) && ($me->isMemberOf($this->project) || $me->isRoleAtLeast(STAFF))){
            $this->html .= "<div id='activitySchedule'><h2 style='margin-top:0;padding-top:0;'>Activity Schedule</h2>";
            $this->showMilestones(false, false, true);
            $this->html .= "<hr style='clear:both;' />";
            $this->html .= "</div>";
            $this->addScript();
            $this->html .= "<h2 style='clear:both;'>Milestones</h2>";
            $this->showFESMilestones();
            $this->html .= "<hr style='clear:both;' />";
            if(!$me->isRoleAtLeast(STAFF)){
                $this->html .= "<style type='text/css' rel='stylesheet'>
                    #activitySchedule {
                        display: none;
                    }
                </style>";
            }
        }
    }
    
    function generatePDFBody(){
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(HQP) && ($me->isMemberOf($this->project) || !$me->isSubRole("UofC"))){
            //$this->html .= "<h2>Activity Schedule</h2>";
            //$this->showMilestones(true, false, true);
            //$this->html .= "<div style='page-break-after:always;'></div>";
            $this->html .= "<h2>Milestones</h2>";
            $this->showFESMilestones(true);
        }
    }

    function canGeneratePDF(){
        return true;
    }
    
    function addScript(){
        global $wgServer, $wgScriptPath;
        $this->html .= "<script type='text/javascript'>
            $('.milestone_header').text('Task');
            $('.milestones_note').remove();
            $('.new_milestones_message').remove();
            $('#addMilestone').text('Add Task');
            $('#addMilestoneDialog').attr('title', 'Add Task');
            $('.milestone_info1').html('If there any new tasks or activities, please contact the project leader.  If there are any changes to the tasks, leave comments by clicking the <img src=\'$wgServer$wgScriptPath/skins/icons/gray_light/comment_stroke_16x14.png\' /> icon.');
            $('.milestone_info2').text('If a task was mistakenly added, then contact someone on staff to delete it.  If a task was planned, but was abandoned, then select the \'Abandoned\' status.');
            
            $('#addActivity').off('click');
            $('#addActivity').click(function(){
                    $('#addActivityDialog').dialog({
                        width: 'auto',
                        resizable: false,
                        buttons: {
                            'Add Activity': function(){
                                $(this).parent().prependTo($('#schedule'));
                                $(this).dialog('close');
                                $('button[value=\"Save Schedule\"]').click();
                            },
                            Cancel: function(){
                                $(this).dialog('close');
                            }
                        }
                    });
                });
            
            $('#addMilestone').off('click');
            $('#addMilestone').click(function(){
                    $('#addMilestoneDialog').dialog({
                        width: 'auto',
                        resizable: false,
                        buttons: {
                            'Add Task': function(){
                                $('#addFESMilestoneDialog').remove();
                                $(this).parent().prependTo($('#schedule'));
                                $(this).dialog('close');
                                $('button[value=\"Save Schedule\"]').click();
                            },
                            Cancel: function(){
                                $(this).dialog('close');
                            }
                        }
                    });
                });
        </script>";
    }
    
    function showFESMilestones($pdf=false, $year=false){
        global $wgServer, $wgScriptPath, $wgUser, $wgOut, $config;
        $me = Person::newFromWgUser();
        $project = $this->project;
        $startDate = $this->project->getCreated();
        $startYear = substr($startDate, 0, 4);
        $startMonth = substr($startDate, 5, 2);
        $startYear = @substr($config->getValue('projectPhaseDates', PROJECT_PHASE), 0, 4);

        if($year === false){
            $milestones = $project->getMilestones(true, true);
        }
        else{
            $milestones = $project->getMilestonesDuring(substr($year, 0, 4));
        }
        
        $uofaMilestones = array();
        $otherMilestones = array();
        foreach($milestones as $milestone){
            $leaders = $milestone->getLeaders();
            $leader = @$leaders[0];
            $uni = "";
            if($leader != null && $leader->getId() != 0){
                $uni = $leader->getUni();
            }
            if($uni == "University of Alberta"){
                $uofaMilestones[] = $milestone;
            }
            else{
                $otherMilestones[] = $milestone;
            }
        }
        $milestones = array_merge($uofaMilestones, $otherMilestones);
        
        usort($milestones, function($a, $b){
            $aQuarters = explode(",", $a->quarters);
            $bQuarters = explode(",", $b->quarters);
            return ($aQuarters[count($aQuarters)-1] > $bQuarters[count($bQuarters)-1]);
        });
        
        $this->html .= "<style type='text/css' rel='stylesheet'>
            .left_border {
                border-left: 2px solid #555555;
            }
            
            .top_border {
                border-top: 2px solid #555555;
            }
            
            #milestones_table_fes input[type=text], #milestones_table_fes select {
                box-sizing: border-box;
                margin: 0;
                width: 100%;
            }
            
            .requiresChange {
                background: #fdeeb2;
            }
        </style>";
        $commentsHeader = "";
        $statusHeader = "";
        $statusColspan = 1;
        if($this->visibility['edit'] == 1){
            if($this->canEditMilestone(null)){
                $this->html .= "<div title='Add Milestone' id='addFESMilestoneDialog' style='display:none;'>
                                    <input type='hidden' name='new_milestone_activity' value='0' />
                                    <table>
                                        <tr>
                                            <td align='right'><b>Title:</b></td>
                                            <td><input type='text' name='new_milestone_title' value='' /></td>
                                        </tr>
                                    </table>
                                </div>
                                <a class='button' id='addFESMilestone'>Add Milestone</a><br /><br />";
            
                $statusHeader = "<th>Status</th><th>Modification</th>";
                if($me->isRoleAtLeast(STAFF)){
                    $statusHeader .= "<th width='1%'>Delete?</td>";
                }
            }
            else{
                $statusHeader = "<th>Status</th><th>Modification</th>";
            }
            $statusColspan += 2; // Status & Modification
            if(!$this->canEditMilestone(null)){
                $this->html .= "<p class='milestone_info1'>If there any new milestones or activities, please contact the project leader.  If there are any changes to the milestones, leave comments by clicking the <img src='$wgServer$wgScriptPath/skins/icons/gray_light/comment_stroke_16x14.png' /> icon.</p>";
            }
            else {
                $this->html .= "<p class='milestone_info2'>If a milestone was mistakenly added, then contact someone on staff to delete it.  If a milestone was planned, but was abandoned, then select the 'Abandoned' status.</p>";
            }
            if($me->isRoleAtLeast(STAFF)){
                $statusColspan++;
            }
        }
        $statusColspan+=1;

        $header = "<tr>
                       <th colspan='1'></th>
                       {$this->showYearsHeader()}
                       <th colspan='{$statusColspan}' class='left_border'></th>
                   </tr>
                   <tr>
                       <th style='min-width:200px;width:25%;'>Description</th>
                       {$this->showQuartersHeader()}
                       <th class='left_border'>Leader</th>
                       <th>Personnel</th>
                       {$commentsHeader}
                       {$statusHeader}
                   </tr>";
                   
        $this->html .= "<input type='hidden' name='milestone_activity[0]' value='' />
                        <table id='milestones_table_fes' frame='box' rules='all' cellpadding='2' class='smallest dashboard milestones' style='width:100%; border: 2px solid #555555;'>
                        <thead>{$header}</thead>
                        <tbody>";
        
        foreach($milestones as $key => $milestone){
            if($key % 100 == 0 && $key != 0 && $pdf){
                // Every 100 rows start a new table
                $this->html .= "
                        </tbody>
                    </table>
                    <table id='milestones_table_fes' frame='box' rules='all' cellpadding='2' class='smallest dashboard milestones' style='width:100%; border: 2px solid #555555;'>
                        <thead>{$header}</thead>
                        <tbody>";
            }
            $activityId = 0;
            if($this->visibility['edit'] == 1 && $this->canEditMilestone($milestone)){
                // Editing
                $milestoneTitle = str_replace("'", "&#39;", $milestone->getTitle());
                $milestoneDescription = str_replace(">", "&gt;", str_replace("<", "&lt;", $milestone->getDescription()));
                $title = "<input type='hidden' name='milestone_old[$activityId][{$milestone->getMilestoneId()}]' value='{$milestoneTitle}' />
                          <input type='hidden' name='milestone_title[$activityId][{$milestone->getMilestoneId()}]' value='{$milestoneTitle}' />";
                $description = "<div style='display:inline-block;width:100%;padding:1px;box-sizing:border-box;'><textarea style='width:100%;height:auto;resize: vertical;margin:0;' name='milestone_description[$activityId][{$milestone->getMilestoneId()}]' placeholder='Description'>{$milestoneDescription}</textarea></div>";
                $title .= $milestoneTitle;
            }
            else{
                // Viewing
                $title = $milestone->getTitle();
                $description = nl2br(str_replace(">", "&gt;", str_replace("<", "&lt;", $milestone->getDescription())));
                $description = "<div style='max-height:75px;overflow-y: auto;'>{$description}</div>";
                $title = "<b>$title</b>";
            }
            $height = "";
            if($pdf){
                $height = "height:".(DPI_CONSTANT*10)."px;";
            }
            $yearOffset = ($this->nYears < $this->maxNYears) ? 2 : 0;
            $yearOffset -= 0; // Change to 2 if only showing 2 quarters
            $leaders = $milestone->getLeaders();
            $leader = @$leaders[0];
            $peopleText = $milestone->getPeopleText();
            $uniText = "";
            if($leader->getName() != "" && $leader->getUniversity() != null){
                $uni = University::newFromName($leader->getUni());
                $uniText = " ({$uni->getShortName()})";
            }
            if(!$pdf){
                $this->html .= str_replace("<tr", "<tr data-id='header{$activityId}-{$key}' style='display:none;'", str_replace("<th", "<th style='background:#CCCCCC;color:black;font-weight:bold;'", $header));
            }
            $this->html .= "<tr class='top_border' data-id='{$activityId}-{$key}'>
                                <td style='background:#555555;font-weight:bold;color:white;' colspan='".($statusColspan+1+($this->nYears*4) + $yearOffset)."' style='white-space:nowrap;{$height};'>{$title} {$uniText}</td>
                            </tr>";
            
            $this->html .= "<tr>
                                <td>{$description}</td>";
            $this->showQuartersCells($milestone, $activityId);
            
            $comment = str_replace("'", "&#39;", $milestone->getComment());
            $doubleEscapeComment = nl2br(str_replace("&", "&amp;", $comment));
            $leaderText = ($leader->getName() != "") ? "<a href='{$leader->getUrl()}'>{$leader->getNameForForms()}</a>" : "";
            
            if($this->visibility['edit'] == 1 && $this->canEditMilestone($milestone)){
                $members = $project->getAllPeople();
                $peopleNames = array();
                foreach($members as $person){
                    $peopleNames[$person->getId()] = $person->getNameForForms();
                }
                if($this->canEditMilestone(null)){
                    $selectBox = new SelectBox("milestone_leader[$activityId][{$milestone->getMilestoneId()}][]", "leader", $leader->getId(), $peopleNames);
                    $selectBox->forceKey = true;
                    $leaderText = $selectBox->render();
                }
                else{
                    $leaderText = "<input type='hidden' name='milestone_leader[$activityId][{$milestone->getMilestoneId()}][]' value='{$leader->getId()}' />$leaderText";
                }
                $personnel = str_replace("'", "&#39;", $milestone->getPeopleText());
                $peopleText = "<input type='text' class='milestone_people' name='milestone_people[$activityId][{$milestone->getMilestoneId()}]' value='{$personnel}' />";
            }
            $this->html .= "<td class='left_border' align='center'>{$leaderText}</td>";
            $this->html .= "<td class='left_comment' align='center'>{$peopleText}</td>";
            if($this->visibility['edit'] == 1 && $this->canEditMilestone($milestone)){
                $statuses = array();
                foreach(Milestone::$fesStatuses as $status => $color){
                    $statuses[$status] = $status;
                }
                
                $modifications = array();
                foreach(Milestone::$modifications as $modification => $color){
                    $modifications[$modification] = $modification;
                }
                
                $selectBox = new SelectBox("milestone_status[$activityId][{$milestone->getMilestoneId()}]", "status", $milestone->getStatus(), $statuses);
                $statusText = $selectBox->render();
                $selectBox = new SelectBox("milestone_modification[$activityId][{$milestone->getMilestoneId()}]", "modification", $milestone->getModification(), $modifications);
                $modificationText = $selectBox->render();
                $this->html .= "<td id='status' class='left_comment' align='center'>$statusText</td>";
                $this->html .= "<td id='modification' align='center'>$modificationText</td>";
                if($me->isRoleAtLeast(STAFF)){
                    $this->html .= "<td align='center'><input type='checkbox' name='milestone_delete[$activityId][{$milestone->getMilestoneId()}]' value='delete' /></td>";
                }
            }
            else if($this->visibility['edit'] && !$this->canEditMilestone($milestone)){
                $this->html .= "<td id='status' class='left_comment' align='center'></td>";
            }
            $this->html .= "</tr>";
            if($this->visibility['edit'] == 1 && $this->canEditMilestone($milestone)){
                $firstChange = ($milestone->getParent() == null) ? "firstChange" : "";
                $this->html .= "<tr>
                        <td colspan='".($statusColspan+1+($this->nYears*4) + $yearOffset)."'>
                            <div style='position:relative;height:100px;'>
                                <textarea class='placeholder' style='height:100px;width:100%;padding-bottom:1.5em !important;position:absolute;top:0;left:0;z-index:0;color:#AAAAAA;resize:none;overflow-y:scroll;'></textarea>
                                <textarea class='comment $firstChange' style='height:100px;width:100%;padding-bottom:1.5em !important;position:absolute;top:0;left:0;z-index:1;background:transparent;resize:none;overflow-y:scroll;' name='milestone_comment[$activityId][{$milestone->getMilestoneId()}]'>{$comment}</textarea>
                            </div>
                        </td>
                    </tr>";
            }
            else{
                $this->html .= "<tr><td colspan='".($statusColspan+1+($this->nYears*4) + $yearOffset)."'><div style='max-height:100px;overflow-y: auto;'>".nl2br("{$comment}")."</div></td></tr>";
            }
        }
        $this->html .= "</tbody>
                        </table>";
        if(!$pdf){
            $this->html .= "<table style='float:right;'>";
        }
        else{
            $this->html .= "<table style='vertical-align:top;'>";
        }
        $this->html .= "<tr>
                            <th>Status</th>
                        </tr>";
        foreach(Milestone::$fesStatuses as $status => $color){
            $this->html .= "<tr>
                                <td class='smallest'><div style='text-align:center;padding:1px 3px;background:{$color};border:1px solid #555555;white-space:nowrap;'>$status</div></td>
                            </tr>";
        }
        $this->html .= "</table>&nbsp;";
        if(!$pdf){
            $this->html .= "<table style='float:right;'>";
        }
        else{
            $this->html .= "<table style='vertical-align:top;'>";
        }
        $this->html .= "<tr>
                            <th>Modification</th>
                        </tr>";
        foreach(Milestone::$modifications as $modification => $color){
            $status = "Pending";
            if($color == "transparent"){
                $color = "#555555";
                $modification = "N/A";
                $status = "New";
            }
            $img = (isset($_GET['generatePDF'])) ? "$wgServer$wgScriptPath/skins/".str_replace("#", "", $color)."_diag_large.png" : 
                                                   "$wgServer$wgScriptPath/skins/".str_replace("#", "", $color)."_diag.png";
            $this->html .= "<tr>
                                <td class='smallest'><div style='text-align:center;padding:1px 3px;outline-offset: -2px; outline:2px solid {$color}; background: ".Milestone::$fesStatuses[$status]."; background-image: url($img); border:1px solid;white-space:nowrap;'><span style='background: ".Milestone::$fesStatuses[$status].";'>$modification</span></div></td>
                            </tr>";
        }
        $this->html .= "</table><br style='clear:both;' />";

        if(!$pdf){
            $this->html .= "<script type='text/javascript'>
                var colors = ".json_encode(array_merge(Milestone::$statuses, Milestone::$fesStatuses)).";
                var colors2 = ".json_encode(Milestone::$modifications).";
                
                $('#milestones_table_fes td').qtip();
                $('#milestones_table_fes td.comment img').qtip({
                    position: {
                        my: 'topRight',
                        at: 'bottomLeft'
                    }
                });
                
                $('#milestones_table_fes div.comment').click(function(){
                    var that = $(this);
                    $('.comment_dialog', $(this).parent()).dialog({
                        width: 'auto',
                        resizable: false,
                        buttons: {
                            'Done': function(){
                                $(this).dialog('close');
                            }
                        },
                        close: function(event, ui){
                            $(this).parent().prependTo(that.parent());
                        }
                    });
                });
                
                var changeColorFES = function(){
                    var checked = $(this)[0].checked;
                    var allChecks = $('input.milestone.single[type=checkbox]:checked', $(this).parent().parent());
                    if(checked){
                        var status = $('td#status select', $(this).parent().parent()).val();
                        var modification = $('td#modification select', $(this).parent().parent()).val();
                        var color = colors[status];
                        var color2 = colors2[modification];
                        if(allChecks.length <= 1 || allChecks.last()[0] == this){
                            $(this).parent()[0].style.backgroundColor = color;
                            $(this).parent()[0].style.outline = '2px solid ' + color2;
                            $(this).parent()[0].style.outlineOffset = '-1px';
                            if(color2 != undefined){
                                $(this).parent()[0].style.backgroundImage = 'url($wgServer$wgScriptPath/skins/' + color2.replace('#', '') + '_diag.png)';
                            }
                        }
                        else{
                            $(this).parent()[0].style.backgroundColor = '#BBBBBB';
                            $(this).parent()[0].style.backgroundImage = '';
                            $(this).parent()[0].style.outline = '0 solid transparent';
                            $(this).parent()[0].style.outlineOffset = '';
                        }
                    }
                    else{
                        $(this).parent()[0].style.backgroundColor = '#FFFFFF';
                        $(this).parent()[0].style.backgroundImage = '';
                        $(this).parent()[0].style.outline = '0 solid transparent';
                        $(this).parent()[0].style.outlineOffset = '';
                    }
                };

                $('#milestones_table_fes td#status select').change(function(){
                    if($(this).val() == 'Pending'){
                        $('#modification select', $(this).parent().parent()).prop('disabled', false);
                    }
                    else{
                        $('#modification select', $(this).parent().parent()).val('').change();
                        $('#modification select', $(this).parent().parent()).prop('disabled', true);
                        $('#modification select', $(this).parent().parent()).change();
                    }
                    var checked = $('input.milestone:checked', $(this).parent().parent());
                    var proxyFn = clickFn.bind(checked.last());
                    proxyFn();
                });
                $('#milestones_table_fes td#modification select').change(function(){
                    var checked = $('input.milestone:checked', $(this).parent().parent());
                    var proxyFn = clickFn.bind(checked.last());
                    proxyFn();
                });
                
                $('#addFESMilestone').click(function(){
                    $('input[name=new_milestone_title]').val('New Milestone');
                    $('button[value=\"Save Schedule\"]').click();
                });
                
                var clickFn = function(){
                    var dataId = $(this).attr('data-id');
                    var checked = $('input[data-id=' + dataId + ']:checked');
                    var modification = $('td#modification select', $(this).parent().parent()).val();

                    if(modification != ''){
                        if(checked.first()[0] == this){
                            checked.not(this).not(checked.last()).prop('checked', false);
                        }
                        else{
                            checked.not(this).not(checked.first()).prop('checked', false);
                        }
                    }
                    else{
                        checked.not(this).prop('checked', false);
                    }
                    $('td input.milestone.single[type=checkbox]', $(this).parent().parent()).each(changeColorFES);
                };
                
                $('input.single').click(clickFn);
                $('#milestones_table_fes td#status select').change();
                
                $(document).ready(function(){
                    $('input.milestone, td#status select', $('#milestones_table_fes .top_border').next()).change(function(e){
                        var el = e.currentTarget;
                        var textbox = $('textarea.comment', $(el).closest('tr').next());
                        var placeholder = $(textbox).parent().children('.placeholder');
                        if(!textbox.hasClass('alreadyChanged') && !textbox.hasClass('firstChange')){
                            var nl = '';
                            if(textbox.val() != ''){
                                nl = \"\\n\";
                            }
                            textbox.val(textbox.val() + nl + \"Updated \" + new Date().toLocaleDateString('en-CA') + nl);
                            textbox.addClass('alreadyChanged');
                            placeholder.addClass('requiresChange');
                            textbox.scrollTop(10000000000000);
                            textbox.change();
                            textbox.change(function(){
                                placeholder.removeClass('requiresChange');
                            });
                        }
                    });
                    
                    $(document).ready(function(){
                        $('#milestones_table_fes textarea.comment').on('change input scroll', function(e){
                            var textarea = $(e.currentTarget);
                            var placeholder = $(textarea).parent().children('.placeholder');

                            if(textarea.val().slice(-1) == \"\\n\" || textarea.val() == ''){
                                placeholder.val(textarea.val() + \"Add comment here...\");
                            }
                            else{
                                placeholder.val(textarea.val() + \"\\nAdd comment here...\");
                            }
                            placeholder.scrollTop(textarea.scrollTop());
                        }).change();
                    });
                });
                
                $('form').submit(function(e){
                    if($(e.originalEvent.submitter).val() == 'Cancel'){
                        return true;
                    }
                    if($('.requiresChange').length > 0){
                        alert('You must enter a comment in the highlighted milestones');
                        e.preventDefault();
                        return false;
                    }
                });
                
            </script>";
        }
    }

}
