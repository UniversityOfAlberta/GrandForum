<?php

class ProjectMainTab extends AbstractEditableTab {

    var $project;
    var $visibility;

    function ProjectMainTab($project, $visibility){
        parent::AbstractTab("Main");
        $this->project = $project;
        $this->visibility = $visibility;
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath, $config;
        $project = $this->project;
        $me = Person::newFromId($wgUser->getId());
        $edit = (isset($_POST['edit']) && $this->canEdit() && !isset($this->visibility['overrideEdit']));
        
        if($me->isLoggedIn() && 
           !$project->isDeleted() && 
           !$project->isSubProject() && 
           !$edit && 
           !$me->isMemberOf($project) &&
           $me->isRoleAtLeast(NI)){
            // Show a 'Join' button if this person isn't a member of the project, but is logged in
            $this->html .= "<a class='button' onClick=\"$('#joinForm').slideDown();$(this).remove();\">Join</a>
            <script type='text/javascript'>
                function submitJoinRequest(){
                    var project = '{$project->getName()}';
                    var reason = $('textarea[name=reason]').val();
                    var data = {
                        project: project,
                        reason: reason
                    };
                    $('#joinButton').prop('disabled', true);
                    $.post('{$wgServer}{$wgScriptPath}/index.php?action=api.addProjectJoinRequest', data, function(response){
                        clearSuccess();
                        clearError();
                        _.each(response.messages, function(m){addSuccess(m);});
                        _.each(response.errors, function(e){addError(e);});
                        $('#joinButton').prop('disabled', false);
                        $('#joinButton').prop('disable', true);
                        $('#joinForm').slideUp();
                    });
                }
            </script>
            <div id='joinForm' style='display:none;'>
                <fieldset>
                    <legend>Join Request Form</legend>
                    By pressing the join button you can request to join {$project->getName()}.  The request needs to be accepted by the Network Manager so please enter in your reason for wanting to join below:
                    <textarea name='reason' style='height:100px;'></textarea>
                    <a id='joinButton' class='button' onClick='submitJoinRequest()'>Submit Join Request</a>
                </fieldset>
            </div>";
        }
        
        if(!$project->isSubProject() && $wgUser->isLoggedIn()){
            // Show a mailing list link if the person is subscribed
            $this->html .="<h3><a href='$wgServer$wgScriptPath/index.php/Mail:{$project->getName()}'>{$project->getName()} Mailing List</a></h3>";
        }
        
        $bigbet = ($this->project->isBigBet()) ? "Yes" : "No";
        $title = "";
        if($edit){
            if($project->isSubProject()){
                $acronymField = new TextField("acronym", "New Acronym", $this->project->getName());
                $title .= "<tr><td><b>New Acronym:</b></td><td>{$acronymField->render()}</td></tr>";
            }
            $fullNameField = new TextField("fullName", "New Title", $this->project->getFullName());
            $title .= "<tr><td><b>New Title:</b></td><td>{$fullNameField->render()}</td></tr>";
        }
        $this->html .= "<table>
                            $title";
        if($config->getValue("projectTypes")){
            $this->html .= "<tr><td><b>Type:</b></td><td>{$this->project->getType()}</td></tr>";
        }
        if($config->getValue("bigBetProjects") && !$this->project->isSubProject()){
            $this->html .= "<tr><td><b>Big-Bet:</b></td><td>{$bigbet}</td></tr>";
        }
        if($config->getValue("projectStatus")){
            $this->html .= "<tr><td><b>Status:</b></td><td>{$this->project->getStatus()}</td></tr>";
        }
        $this->html .= "</table>";
        if($project->getType() != "Administrative"){
            $this->showChallenge();
        }

        $this->showPeople();
        //$this->showChampions();
        $this->showDescription();
        
        return $this->html;
    }
    
    function handleEdit(){
        global $wgOut, $wgMessage;
        $_POST['project'] = $this->project->getName();
        $_POST['fullName'] = @$_POST['fullName'];
        $_POST['description'] = @$_POST['description'];
        $_POST['long_description'] = $this->project->getLongDescription();
        if($_POST['description'] != $this->project->getDescription() ||
           $_POST['fullName'] != $this->project->getFullName()){
            $error = APIRequest::doAction('ProjectDescription', true);
            if($error != ""){
                return $error;
            }
            Project::$cache = array();
            $this->project = Project::newFromId($this->project->getId());
            $wgOut->setPageTitle($this->project->getFullName());
        }

        if(isset($_POST['challenge_id'])){
            APIRequest::doAction('ProjectChallenge', true);
        }
        
        if(isset($_POST['acronym'])){
            $_POST['new_acronym'] = str_replace(" ", "-", $_POST['acronym']);
            $_POST['old_acronym'] = $this->project->getName();
            $result = APIRequest::doAction('UpdateProjectAcronym', true);
            if($result){
                $this->project->name = $_POST['new_acronym'];
                redirect($this->project->getUrl());
                exit;
            }
        }
    }
    
    function generateEditBody(){
        $this->generateBody();
    }
    
    function canEdit(){
        return $this->project->userCanEdit();
    }

    function showChallenge(){
        global $wgServer, $wgScriptPath, $config;
        $edit = (isset($_POST['edit']) && $this->canEdit() && !isset($this->visibility['overrideEdit']));
        
        $this->html .= "<h2><span class='mw-headline'>{$config->getValue("projectThemes")}</span></h2>";
        $challenge = $this->project->getChallenge();
        
        $challenges = Theme::getAllThemes($this->project->getPhase());
        $chlg_opts = "<option value='0'>Not Specified</option>";
        foreach ($challenges as $chlg){
            $cid = $chlg->getId();
            $cname = $chlg->getAcronym();
            $selected = ($cname == $challenge->getAcronym())? "selected='selected'" : "";
            $chlg_opts .= "<option value='{$cid}' {$selected}>{$cname}</option>";
        }
        if($edit){
            $this->html .=<<<EOF
            <select name="challenge_id">{$chlg_opts}</select>
EOF;
        }
        else{
            $this->html .= "<h4>{$challenge->getAcronym()}</h4>";
        }   
    }

    function showPeople(){
        global $wgUser, $wgServer, $wgScriptPath, $config;
        $me = Person::newFromWgUser();
        
        $edit = (isset($_POST['edit']) && $this->canEdit() && !isset($this->visibility['overrideEdit']));
        $project = $this->project;

        if(!$edit){
            $this->html .= "<table width='100%'><tr><td valign='top' width='50%'>";
            $this->showRole(PL);
            $this->showRole(CI);
            $this->showRole(AR);
            $this->html .= "</td><td width='50%' valign='top'>";
            if($wgUser->isLoggedIn()){
                $this->showRole(HQP);
            }
            $this->html .= "</td></tr>";
            $this->html .= "<tr><td valign='top' width='50%'>";
            $this->showRole(CHAMP);
            $this->showRole(PARTNER);
            $this->html .= "</td><td width='50%' valign='top'>";
            $this->showRole(EXTERNAL);
            $this->html .= "</td></table>";
        }
    }
    
    function showRole($role){
        global $config;
        $me = Person::newFromWgUser();
        
        $edit = (isset($_POST['edit']) && $this->canEdit() && !isset($this->visibility['overrideEdit']));
        $project = $this->project;
        
        $people = $project->getAllPeople($role);
        if(count($people) > 0){
            $this->html .= "<h2><span class='mw-headline'>".ucwords(Inflect::pluralize($config->getValue('roleDefs', $role)))."</span></h2>";
        }
        $this->html .= "<ul>";
        foreach($people as $p){
            $this->html .= "<li><a href='{$p->getUrl()}'>{$p->getReversedName()}</a></li>";
        }
        $this->html .= "</ul>";
    }
    
    function showDescription(){
        global $wgServer, $wgScriptPath, $config;
        
        $edit = (isset($_POST['edit']) && $this->canEdit() && !isset($this->visibility['overrideEdit']));
        $project = $this->project;
        
        $description = $project->getDescription();
        
        if($edit || !$edit && $description != ""){
            $this->html .= "<h2><span class='mw-headline'>Project Overview (live on website)</span></h2>";
        }
        if(!$edit){
            $this->html .= $description."<br />";
        }
        else{
            $this->html .= "<textarea name='description' style='height:500px;'>{$description}</textarea>
            <script type='text/javascript'>
                $('textarea[name=description]').tinymce({
                    theme: 'modern',
                    menubar: false,
                    plugins: 'link image charmap lists table paste wordcount',
                    toolbar: [
                        'undo redo | bold italic underline | link charmap | table | bullist numlist outdent indent | alignleft aligncenter alignright alignjustify'
                    ],
                    paste_postprocess: function(plugin, args) {
                        var p = $('p', args.node);
                        p.each(function(i, el){
                            $(el).css('line-height', 'inherit');
                        });
                    }
                });
            </script>";
        }
    }

}    
    
?>
