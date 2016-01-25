<?php

class ProjectMilestonesTab extends AbstractEditableTab {
    // This should be converted into a Backbone Page in the future most likely

    var $project;
    var $visibility;

    function ProjectMilestonesTab($project, $visibility){
        parent::AbstractTab("Milestones");
        $this->project = $project;
        $this->visibility = $visibility;
    }
    
    function handleEdit(){
        global $config;
        $startYear = @substr($config->getValue('projectPhaseDates', PROJECT_PHASE), 0, 4);
        $me = Person::newFromWgUser();
        
        $_POST['user_name'] = $me->getName();
        $_POST['project'] = $this->project->getName();
        
        foreach($_POST['milestone_activity'] as $activityId => $activity){
            foreach($_POST['milestone_title'][$activityId] as $milestoneId => $title){
                $quarters = array();
                if(isset($_POST['milestone_q'][$activityId][$milestoneId])){
                    foreach($_POST['milestone_q'][$activityId][$milestoneId] as $year => $qs){
                        foreach($qs as $qId => $q){
                            $quarters[] = ($year).":$qId";
                        }
                    }
                }
                
                $_POST['leader'] = $_POST['milestone_leader'][$activityId][$milestoneId];
                $_POST['activity'] = $activity;
                $_POST['activity_id'] = $activityId;
                $_POST['milestone'] = $_POST['milestone_old'][$activityId][$milestoneId];
                $_POST['title'] = $_POST['milestone_old'][$activityId][$milestoneId];
                $_POST['new_title'] = $title;
                $_POST['problem'] = "";
                $_POST['description'] = "";
                $_POST['assessment'] = "";
                $_POST['status'] = $_POST['milestone_status'][$activityId][$milestoneId];
                $_POST['people'] = $_POST['milestone_people'][$activityId][$milestoneId];
                $_POST['end_date'] = ($startYear+2)."-12-31 00:00:00";
                $_POST['quarters'] = implode(",", $quarters);
                $_POST['comment'] = "";
                $_POST['id'] = $milestoneId;
                
                $milestoneApi = new ProjectMilestoneAPI(true);
                $milestoneApi->doAction(true);
                
                if(isset($_POST['milestone_delete'][$activityId][$milestoneId]) &&
                   $_POST['milestone_delete'][$activityId][$milestoneId] == 'delete'){
                    $milestone = Milestone::newFromId($milestoneId);
                    DBFunctions::update('grand_milestones',
                                        array('status' => 'Deleted'),
                                        array('id' => $milestone->getId()));
                }
            }
        }
        
        if(isset($_POST['new_activity_title']) && $_POST['new_activity_title'] != ""){
            DBFunctions::insert('grand_activities',
                                array('name' => $_POST['new_activity_title'],
                                      'project_id' => $this->project->getId()));
            
            // Still show the edit interface
            redirect("{$this->project->getUrl()}?tab=milestones&edit");
        }
        if(isset($_POST['new_milestone_activity']) && isset($_POST['new_milestone_title']) &&
           $_POST['new_milestone_activity'] != "" && $_POST['new_milestone_title'] != ""){
            
            $_POST['leader'] = "";
            $_POST['activity'] = $_POST['new_milestone_activity'];
            $_POST['activity_id'] = "";
            $_POST['milestone'] = "";
            $_POST['title'] = $_POST['new_milestone_title'];
            $_POST['new_title'] = $_POST['new_milestone_title'];
            $_POST['problem'] = "";
            $_POST['description'] = "";
            $_POST['assessment'] = "";
            $_POST['status'] = "New";
            $_POST['people'] = "";
            $_POST['end_date'] = ($startYear+2)."-12-31 00:00:00";
            $_POST['quarters'] = "";
            $_POST['comment'] = "";
            $_POST['id'] = "";
            unset($_POST['id']);
            unset($_POST['activity_id']);
            
            $milestoneApi = new ProjectMilestoneAPI(false);
            $milestoneApi->doAction(true);
            
            // Still show the edit interface 
            redirect("{$this->project->getUrl()}?tab=milestones&edit");
        }
        redirect("{$this->project->getUrl()}?tab=milestones");
    }
    
    function canEdit(){
        $me = Person::newFromWgUser();
        return ($me->leadershipOf($this->project) || $me->isRoleAtLeast(STAFF));
    }
    
    function generateBody(){
        global $wgUser, $wgOut, $wgServer, $wgScriptPath;
        if($wgUser->isLoggedIn()){
            $project = $this->project;
            $me = Person::newFromId($wgUser->getId());
            if($me->isMemberOf($project) || $me->isRoleAtLeast(STAFF)){
                $this->showMilestones();
                return $this->html;
            }
        }
    }
    
    function generateEditBody(){
        global $wgUser, $wgOut, $wgServer, $wgScriptPath;
        if($wgUser->isLoggedIn()){
            $project = $this->project;
            $this->showMilestones();
            return $this->html;
        }
    }
    
    function showMilestones(){
        global $wgServer, $wgScriptPath, $wgUser, $wgOut, $config;
        $project = $this->project;
        
        $startYear = @substr($config->getValue('projectPhaseDates', $project->getPhase()), 0, 4);
        
        $activities = array();
        $activityNames = array();
        $milestones = $project->getMilestones(true);
        
        foreach($project->getActivities() as $activity){
            $activities[$activity->getId()] = array();
            $activityNames[$activity->getId()] = $activity->getName();
        }
        
        foreach($milestones as $milestone){
            $activities[$milestone->getActivity()->getId()][] = $milestone;
            $activityNames[$milestone->getActivity()->getId()] = $milestone->getActivity()->getName();
        }
        
        $this->html .= "<style type='text/css' rel='stylesheet'>
            .left_border {
                border-left: 2px solid #555555;
            }
            
            .top_border {
                border-top: 2px solid #555555;
            }
            
            #milestones_table input[type=text], #milestones_table select {
                box-sizing: border-box;
                margin: 0;
                width: 100%;
                height: 24px;
            }
        </style>";
        $statusHeader = "";
        if($this->visibility['edit'] == 1){
            $activityNames = array();
            foreach($project->getActivities() as $activity){
                $activityNames[$activity->getId()] = $activity->getName();
            }
            $activityBox = new SelectBox("new_milestone_activity", "new_milestone_activity", "", $activityNames);
            $activityText = $activityBox->render();
            $this->html .= "<div title='Add Activity' id='addActivityDialog' style='display:none;'>
                                <table>
                                    <tr>
                                        <td align='right'><b>Title:</b></td>
                                        <td><input type='text' name='new_activity_title' /></td>
                                    </tr>
                                </table>
                            </div>
                            <div title='Add Milestone' id='addMilestoneDialog' style='display:none;'>
                                <table>
                                    <tr>
                                        <td align='right'><b>Activity:</b></td>
                                        <td>{$activityText}</td>
                                    </tr>
                                    <tr>
                                        <td align='right'><b>Title:</b></td>
                                        <td><input type='text' name='new_milestone_title' /></td>
                                    </tr>
                                </table>
                            </div>
                            <a class='button' id='addActivity'>Add Activity</a>&nbsp;
                            <a class='button' id='addMilestone'>Add Milestone</a><br /><br />";
            $statusHeader = "<th>Status</th><th width='1%'>Delete?</td>";
        }
        $this->html .= "<table id='milestones_table' frame='box' rules='all' cellpadding='2' class='smallest dashboard' style='width:100%; border: 2px solid #555555;' >";
        $this->html .= "<thead>
                        <tr>
                            <th colspan='2' width='33%'></th>
                            <th colspan='4' class='left_border'>".($startYear)."</th>
                            <th colspan='4' class='left_border'>".($startYear+1)."</th>
                            <th colspan='4' class='left_border'>".($startYear+2)."</th>
                            <th colspan='3' class='left_border' width='33%'></th>
                        </tr>
                        <tr>
                            <th>Activity</th>
                            <th>Milestone</th>
                            <th class='left_border'>Q1</th>
                            <th>Q2</th>
                            <th>Q3</th>
                            <th>Q4</th>
                            <th class='left_border'>Q1</th>
                            <th>Q2</th>
                            <th>Q3</th>
                            <th>Q4</th>
                            <th class='left_border'>Q1</th>
                            <th>Q2</th>
                            <th>Q3</th>
                            <th>Q4</th>
                            <th class='left_border'>Leader</th>
                            <th>Personnel</th>
                            {$statusHeader}
                        </tr>
                        </thead>
                        <tbody>";
        foreach($activities as $activityId => $milestones){
            $count = max(1, count($milestones));
            $activity = $activityNames[$activityId];
            if($this->visibility['edit'] == 1){
                $activityTitle = str_replace("'", "&#39;", $activity);
                $activity = "<input type='text' name='milestone_activity[$activityId]' value='$activityTitle' />";
            }
            $this->html .= "<tr class='top_border'>
                                <td rowspan='$count'>$activity</td>";
            foreach($milestones as $key => $milestone){
                if($key != 0){
                    $this->html .= "<tr>";
                }
                if($this->visibility['edit'] == 1){
                    $milestoneTitle = str_replace("'", "&#39;", $milestone->getTitle());
                    $title = "<input type='hidden' name='milestone_old[$activityId][{$milestone->getMilestoneId()}]' value='{$milestoneTitle}' />
                              <input type='text' name='milestone_title[$activityId][{$milestone->getMilestoneId()}]' value='{$milestoneTitle}' />";
                }
                else{
                    $title = $milestone->getTitle();
                }
                $this->html .= "<td>{$title}</td>";
                $quarters = $milestone->getQuarters();
                for($y=$startYear; $y < $startYear+3; $y++){
                    for($q=1;$q<=4;$q++){
                        $class = ($q == 1) ? "class='left_border'" : "";
                        $color = @Milestone::$statuses[$milestone->getStatus()];

                        $assessment = str_replace("'", "&#39;", $milestone->getAssessment());
                        $checkbox = "";
                        if($this->visibility['edit'] == 1){
                            $checked = "";
                            if(isset($quarters[$y][$q])){
                                $checked = "checked='checked'";
                            }
                            $checkbox = "<input class='milestone' type='checkbox' name='milestone_q[$activityId][{$milestone->getMilestoneId()}][$y][$q]' $checked />";
                        }
                        if(isset($quarters[$y][$q])){
                            $this->html .= "<td style='background:$color;text-align:center;' title='{$assessment}' $class>$checkbox</td>";
                        }
                        else{
                            $this->html .= "<td style='text-align:center;' $class>$checkbox</td>";
                        }
                    }
                }
                
                $comment = str_replace("'", "&#39;", $milestone->getComment());
                $commentIcon = ($comment != "") ? "<img style='float:right;padding-top:2px;' src='../skins/icons/gray_light/comment_stroke_16x14.png' title='{$comment}' />" : "";
                $leader = $milestone->getLeader();
                $peopleText = $milestone->getPeopleText();
                
                if($this->visibility['edit'] == 1){
                    $members = $project->getAllPeople();
                    $peopleNames = array();
                    foreach($members as $person){
                        $peopleNames[$person->getNameForForms()] = $person->getNameForForms();
                    }
                    $selectBox = new SelectBox("milestone_leader[$activityId][{$milestone->getMilestoneId()}]", "leader", $leader->getNameForForms(), $peopleNames);
                    $leaderText = $selectBox->render();
                    
                    $personnel = str_replace("'", "&#39;", $milestone->getPeopleText());
                    $peopleText = "<input type='text' name='milestone_people[$activityId][{$milestone->getMilestoneId()}]' value='{$personnel}' />";
                }
                else{
                    $leaderText = ($leader->getName() != "") ? "<a href='{$leader->getUrl()}'>{$leader->getNameForForms()}</a>" : "";
                }
                
                $this->html .= "<td class='left_border' align='center' style='white-space:nowrap;'>{$leaderText}</td>";
                $this->html .= "<td class='left_comment' align='center'>{$commentIcon}{$peopleText}</td>";
                if($this->visibility['edit'] == 1){
                    $statuses = array();
                    foreach(Milestone::$statuses as $status => $color){
                        $statuses[$status] = $status;
                    }
                    
                    $selectBox = new SelectBox("milestone_status[$activityId][{$milestone->getMilestoneId()}]", "status", $milestone->getStatus(), $statuses);
                    $statusText = $selectBox->render();
                    $this->html .= "<td id='status' class='left_comment' align='center'>$statusText</td>";
                    $this->html .= "<td align='center'><input type='checkbox' name='milestone_delete[$activityId][{$milestone->getMilestoneId()}]' value='delete' /></td>";
                }
                $this->html .= "</tr>";
            }
        }
        $this->html .= "</tbody>
                        </table>";
        $this->html .= "<script type='text/javascript'>
            var colors = ".json_encode(Milestone::$statuses).";
            
            $('#milestones_table td').qtip();
            $('#milestones_table td.left_comment img').qtip({
                position: {
                    my: 'topRight',
                    at: 'bottomLeft'
                }
            });
            
            var changeColor = function(){
                var checked = $(this).is(':checked');
                if(checked){
                    var status = $('td#status select', $(this).parent().parent()).val();
                    var color = colors[status];
                    $(this).parent().css('background', color);
                }
                else{
                    $(this).parent().css('background', '#FFFFFF');
                }
            };
            
            $('#milestones_table td input.milestone[type=checkbox]').change(changeColor);
            $('#milestones_table td input.milestone[type=checkbox]').each(changeColor);
            $('#milestones_table td#status select').change(function(){
                var status = $(this).val();
                var color = colors[status];
                $('input.milestone:checked', $(this).parent().parent()).parent().css('background', color);
            });
            
            $('#addActivity').click(function(){
                $('#addActivityDialog').dialog({
                    width: 'auto',
                    buttons: {
                        'Add Activity': function(){
                            $(this).parent().prependTo($('#milestones'));
                            $('input[value=\"Save Milestones\"]').click();
                            $(this).dialog('close');
                        },
                        Cancel: function(){
                            $(this).dialog('close');
                        }
                    }
                });
            });
            
            $('#addMilestone').click(function(){
                $('#addMilestoneDialog').dialog({
                    width: 'auto',
                    buttons: {
                        'Add Milestone': function(){
                            $(this).parent().prependTo($('#milestones'));
                            $('input[value=\"Save Milestones\"]').click();
                            $(this).dialog('close');
                        },
                        Cancel: function(){
                            $(this).dialog('close');
                        }
                    }
                });
            });
            
        </script>";
    }

}    
    
?>
