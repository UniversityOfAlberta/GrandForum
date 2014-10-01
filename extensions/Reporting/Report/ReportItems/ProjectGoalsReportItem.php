<?php

class ProjectGoalsReportItem extends AbstractReportItem {
    
    function render(){
        global $wgOut;
        $preview = strtolower($this->getAttr("preview", "false"));
        if($preview == "true"){
            $this->renderForPDF();
            return;
        }
        $project = Project::newFromId($this->projectId);
        $year = $this->getAttr("year", REPORTING_YEAR);
        $max = $this->getAttr("max", 5);
        $milestones = $project->getGoalsDuring($year);
        $width = (isset($this->attributes['width'])) ? $this->attributes['width'] : "150px";
        $item = "<div style='display:none;' id='{$this->getPostId()}_diag' title='Delete Project Goal?'>Are you sure you want to <b>permanently</b> delete this Goal?</div><span id='{$this->getPostId()}_deleted_milestones'></span><ol id='{$this->getPostId()}'></ol>
                 <button type='button' id='{$this->getPostId()}_add' onClick=\"addMilestone{$this->getPostId()}('new', '', '', '', 'Current', '')\">Add Goal</button>";
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
        $wgOut->addHTML(<<<EOF
<script type='text/javascript'>
    function updateMilestones{$this->getPostId()}(){
        if($("#{$this->getPostId()}").children().length >= $max){
            $('#{$this->getPostId()}_add').prop('disabled', true);
        }
        else{
            $('#{$this->getPostId()}_add').prop('disabled', false);
        }
    }

    function addMilestone{$this->getPostId()}(id, title, problem, description, status, assessment){
        var template = $("{$this->getTemplate()}");
        $("#milestone_id", template).val(id);
        $("#identifier", template).val(new Date().getTime());
        $("#title", template).val(title);
        $("#new_title", template).val(title);
        if(status == 'Current' ||
           status == 'Closed' ||
           status == 'Abandoned'){
            $("#status", template).val(status);
        }
        $("#problem", template).val(problem);
        $("#description", template).val(description);
        $("#assessment", template).val(assessment);
        $('#problem', template).limit(300, $('#{$this->getPostId()}_problem_chars_left', template));
        $('#description', template).limit(300, $('#{$this->getPostId()}_description_chars_left', template));
        $('#assessment', template).limit(500, $('#{$this->getPostId()}_assessment_chars_left', template));
        $('#delete', template).click(function(){
            deleteMilestone{$this->getPostId()}($(this).parent(), id, $("#identifier", template).val());
        });
        $("#{$this->getPostId()}").append(template);
        updateMilestones{$this->getPostId()}();
    }
    
    function deleteMilestone{$this->getPostId()}(item, id, identifier){
        $('#{$this->getPostId()}_diag').dialog({
            modal: true,
            buttons: {
                "Delete Goal": function() {
                    if(id != 'new'){
                        $('#{$this->getPostId()}_deleted_milestones').append("<input type='hidden' name='del_milestones_id[]' value='" + id + "' />");
                    }
                    else{
                        $('#{$this->getPostId()}_deleted_milestones').append("<input type='hidden' name='del_milestones_identifier[]' value='" + identifier + "' />");
                    }
                    item.remove();
                    updateMilestones{$this->getPostId()}();
                    $(this).dialog("close");
                },
                Cancel: function() {
                    $(this).dialog("close");
                }
            }
        });
        
    }
    
    updateMilestones{$this->getPostId()}();
</script>
EOF
        );
        if(count($milestones) > 0){
            foreach($milestones as $milestone){
                $wgOut->addHTML("<script type='text/javascript'>
                    var milestone = ".json_encode(array('id' => $milestone->getMilestoneId(),
                                                        'title' => $milestone->getTitle(),
                                                        'problem' => $milestone->getProblem(),
                                                        'description' => $milestone->getDescription(),
                                                        'status' => $milestone->getStatus(),
                                                        'assessment' => $milestone->getAssessment())).";
                    addMilestone{$this->getPostId()}(milestone.id, milestone.title, milestone.problem, milestone.description, milestone.status, milestone.assessment);
                </script>");
            }
        }
    }
    
    function renderForPDF(){
        global $wgOut;
        $project = Project::newFromId($this->projectId);
        $year = $this->getAttr("year", REPORTING_YEAR);
        $max = $this->getAttr("max", 5);
        $milestones = $project->getGoalsDuring($year);
        $width = (isset($this->attributes['width'])) ? $this->attributes['width'] : "150px";
        $item = "";
        if(count($milestones) > 0){
            foreach($milestones as $milestone){
                $item .= $this->getTemplateForPDF($milestone->getTitle(),
                                                  $milestone->getStatus(),
                                                  $milestone->getProblem(),
                                                  $milestone->getDescription(),
                                                  $milestone->getAssessment());
            }
        }
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
    }
    
    function getTemplate(){
        $year = $this->getAttr("year", REPORTING_YEAR);
        $display = ($year > REPORTING_YEAR) ? "display:none;" : "";
        $tplt = "<li style='font-weight: bold; font-size: 2.0em;margin-bottom: 25px;'>
                    <input id='milestone_id' type='hidden' name='{$this->getPostId()}_milestone_id[]' />
                    <input id='title' type='hidden' name='{$this->getPostId()}_title[]' style='width:97%;' />
                    <input id='identifier' type='hidden' name='{$this->getPostId()}_identifier[]' />
                    <table width='100%' style='font-size:9pt;'>
                        <tr>
                            <td width='1%'><b>Title:</b></td><td><input id='new_title' type='text' name='{$this->getPostId()}_new_title[]' style='width:97%;' /></td>
                            <td width='50%'><b>Status:</b>&nbsp;<select id='status' style='vertical-align: middle;' name='{$this->getPostId()}_status[]'>
                                    <option selected>Current</option>
                                    <option>Closed</option>
                                    <option>Abandoned</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td width='50%' colspan='2'><b>Problem Statement:</b></td>
                            <td><b>Plan & Expected Outcomes:</b></td>
                        </tr>
                        <tr>
                            <td width='50%' colspan='2'>
                                <small>(currently <span id='{$this->getPostId()}_problem_chars_left'>0</span> characters out of a maximum of 300)</small><br />
                                <textarea id='problem' name='{$this->getPostId()}_problem[]' style='height: 80px;resize: none;'></textarea></td>
                            <td>
                                <small>(currently <span id='{$this->getPostId()}_description_chars_left'>0</span> characters out of a maximum of 300)</small><br />
                                <textarea id='description' name='{$this->getPostId()}_description[]' style='height: 80px;resize: none;'></textarea>
                            </td>
                        </tr>
                        <tr style='{$display}'>
                            <td colspan='3'><b>Assessment:</b></td>
                        </tr>
                        <tr style='{$display}'>
                            <td colspan='3'>
                                <small>(currently <span id='{$this->getPostId()}_assessment_chars_left'>0</span> characters out of a maximum of 500)</small><br />
                                <textarea id='assessment' name='{$this->getPostId()}_assessment[]' style='height: 80px;resize: none;'></textarea>
                            </td>
                        </tr>
                    </table>
                    <button id='delete' type='button'>Delete</button>
                    <hr />
            </li>";
        return trim(str_replace("\n", "", $tplt));
    }
    
    function getTemplateForPDF($title, $status, $problem, $description, $assessment){
        $year = $this->getAttr("year", REPORTING_YEAR);
        $display = ($year > REPORTING_YEAR) ? "display:none;" : "";
        $problemLength = strlen($problem);
        $descriptionLength = strlen($description);
        $assessmentLength = strlen($assessment);
        $margin = 2*DPI_CONSTANT;
        $tplt = "<div style='page-break-inside:avoid;margin-bottom:{$margin}px;'>
                    <h4>$title ({$status})</h4>
                        <p style='margin-left:50px;'><b>Problem Statement:&nbsp;</b><small>(Currently $problemLength out of 300 characters)</small><br />{$problem}</p>
                        <p style='margin-left:50px;'><b>Plan & Expected Outcomes:&nbsp;</b><small>(Currently $descriptionLength out of 300 characters)</small><br />{$description}</p>
                        <p style='margin-left:50px;{$display}'><b>Assessment:&nbsp;</b><small>(Currently $assessmentLength out of 500 characters)</small><br />{$assessment}</p>
                 </div>";
        return $tplt;
    }
    
    // Overriden Functions
    function save(){
        $me = Person::newFromWgUser();
        $project = Project::newFromId($this->projectId);
        if(isset($_POST["{$this->getPostId()}_milestone_id"]) && count($_POST["{$this->getPostId()}_milestone_id"]) > 0){
            $milestone_ids = $_POST["{$this->getPostId()}_milestone_id"];
            $identifiers = $_POST["{$this->getPostId()}_identifier"];
            $titles = $_POST["{$this->getPostId()}_title"];
            $new_titles = $_POST["{$this->getPostId()}_new_title"];
            $problems = $_POST["{$this->getPostId()}_problem"];
            $descriptions = $_POST["{$this->getPostId()}_description"];
            $statuses = $_POST["{$this->getPostId()}_status"];
            $assessments = $_POST["{$this->getPostId()}_assessment"];
            foreach($milestone_ids as $key => $id){
                $_POST['title'] = $titles[$key];
                if($id == 'new'){
                    $_POST['identifier'] = $identifiers[$key];
                    $_POST['title'] = $new_titles[$key];
                }
                else{
                    $_POST['id'] = $id;
                }
                $_POST['new_title'] = $new_titles[$key];
                $_POST['problem'] = $problems[$key];
                $_POST['description'] = $descriptions[$key];
                $_POST['status'] = $statuses[$key];
                $_POST['assessment'] = $assessments[$key];
                $_POST['project'] = $project->getName();
                $_POST['user_name'] = $me->getName();
                $_POST['comment'] = "";
                $_POST['end_date'] = $this->getAttr("year", REPORTING_YEAR)."-12";
                
                $api = new ProjectMilestoneAPI(($id != "new"));
                $api->doAction(true);
                unset($_POST['identifier']);
                unset($_POST['id']);
            }
        }
        if(isset($_POST['del_milestones_id']) && is_array($_POST['del_milestones_id'])){
            foreach($_POST['del_milestones_id'] as $milestoneId){
                DBFunctions::delete('grand_milestones',
                                    array('milestone_id' => EQ($milestoneId)));
            }
        }
        if(isset($_POST['del_milestones_identifier']) && is_array($_POST['del_milestones_identifier'])){
            foreach($_POST['del_milestones_identifier'] as $milestoneId){
                DBFunctions::delete('grand_milestones',
                                    array('identifier' => EQ($milestoneId),
                                          'project_id' => EQ($this->projectId)));
            }
        }
        return array();
    }
    
    function getNFields(){
        return 0;
    }
        
}

?>
