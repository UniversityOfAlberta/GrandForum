<?php

class ProjectMainTab extends AbstractEditableTab {

    var $project;
    var $visibility;
    var $rolesShown = array();

    function ProjectMainTab($project, $visibility){
        parent::AbstractTab("Main");
        $this->project = $project;
        $this->visibility = $visibility;
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath, $config;
        $project = $this->project;
        $me = Person::newFromWgUser();
        $edit = (isset($_POST['edit']) && $this->canEdit() && !isset($this->visibility['overrideEdit']));
        
        if(!$project->isSubProject() && $wgUser->isLoggedIn() && MailingList::isSubscribed($project, $me)){
            // Show a mailing list link if the person is subscribed
            $this->html .="<h3><a href='$wgServer$wgScriptPath/index.php/Mail:{$project->getName()}'>{$project->getName()} Mailing List</a></h3>";
        }
        
        $website = $this->project->getWebsite();
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
        if($project->getType() != "Administrative"){
            $this->showChallenge();
        }
        if($config->getValue("networkName") != "CS-CAN" && $config->getValue("projectTypes")){
            $this->html .= "<tr><td><b>Type:</b></td><td>{$this->project->getType()}</td></tr>";
        }
        if($config->getValue("bigBetProjects") && !$this->project->isSubProject()){
            $this->html .= "<tr><td><b>Big-Bet:</b></td><td>{$bigbet}</td></tr>";
        }
        if($config->getValue("networkName") != "CS-CAN" && $config->getValue("projectStatus")){
            if(!$edit || !$me->isRoleAtLeast(STAFF)){
                $this->html .= "<tr><td><b>Status:</b></td><td>{$this->project->getStatus()}</td></tr>";
            }
            else{
                $statusField = new SelectBox("status", "Status", $this->project->getStatus(), array("Proposed", "Deferred", "Active", "Ended"));
                $this->html .= "<tr><td><b>Status:</b></td><td>{$statusField->render()}</td></tr>";
            }
        }
        if(!$edit && $website != "" && $website != "http://" && $website != "https://"){
            $this->html .= "<tr><td><b>Website:</b></td><td><a href='{$website}' target='_blank'>{$website}</a></td></tr>";
        }
        else if($edit){
            $this->html .= "<tr><td><b>Website:</b></td><td><input type='text' name='website' value='{$website}' size='40' /></td></tr>";
        }
        $this->html .= "</table>";

        $this->showPeople();
        //$this->showChampions();
        $this->showDescription();
        if($me->isRoleAtLeast(STAFF) && $config->getValue('networkName') == "FES"){
            $this->html .= "<span class='pdfnodisplay'><a class='button' href='{$this->project->getUrl()}?generatePDF'>Download PDF</a></span>";
        }
        return $this->html;
    }
    
    function handleEdit(){
        global $wgOut, $wgMessage;
        $me = Person::newFromWgUser();
        $_POST['project'] = $this->project->getName();
        $_POST['fullName'] = @$_POST['fullName'];
        $_POST['description'] = @$_POST['description'];
        $_POST['website'] = @str_replace("'", "&#39;", $_POST['website']);
        $_POST['long_description'] = $this->project->getLongDescription();
        if($_POST['description'] != $this->project->getDescription() ||
           $_POST['fullName'] != $this->project->getFullName() ||
           $_POST['website'] != $this->project->getWebsite()){
            $error = APIRequest::doAction('ProjectDescription', true);
            if($error != ""){
                return $error;
            }
            Project::$cache = array();
            $this->project = Project::newFromId($this->project->getId());
            $wgOut->setPageTitle($this->project->getFullName());
        }

        if(isset($_POST['challenge_id'])){
            $theme = Theme::newFromId($_POST['challenge_id']);
            $this->project->theme = $theme;
        }
        $this->project->update();
        if(isset($_POST['status']) && $me->isRoleAtLeast(STAFF)){
            DBFunctions::update('grand_project_status',
                                array('status' => $_POST['status']),
                                array('evolution_id' => EQ($this->project->getEvolutionId()),
                                      'project_id' => EQ($this->project->getId())));
            Project::$cache = array();
            $this->project = Project::newFromId($this->project->getId());
        }
        
        if(isset($_POST['acronym'])){
            if($this->project->getName() != $_POST['acronym']){
                $testProj = Project::newFromName($_POST['acronym']);
                if($testProj != null && $testProj->getId() != 0){
                    $wgMessage->addError("A project with the name '{$_POST['acronym']}' already exists");
                }
                if(!preg_match("/^[0-9À-Ÿa-zA-Z\-\. ]+$/", $_POST['acronym'])){
                    $wgMessage->addError("The project acronym cannot contain any special characters");
                }
                else{
                    $this->project->name = $_POST['acronym'];
                    $this->project->update();
                    $wgMessage->addSuccess("The project acronym was changed to '{$_POST['acronym']}'");
                    redirect($this->project->getUrl());
                }
            }
        }
    }
    
    function generatePDFBody(){
        $this->generateBody();
    }
    
    function generateEditBody(){
        $this->generateBody();
    }
    
    function canEdit(){
        return $this->project->userCanEdit();
    }
    
    function canGeneratePDF(){
        return true;
    }

    function showChallenge(){
        global $wgServer, $wgScriptPath, $config;
        $edit = (isset($_POST['edit']) && $this->canEdit() && !isset($this->visibility['overrideEdit']));
        $this->html .= "<tr><td><b>{$config->getValue("projectThemes")}:</b></td><td>";
        $challenge = $this->project->getChallenge();
        
        $challenges = Theme::getAllThemes();
        $chlg_opts = "<option value='0'>Not Specified</option>";
        foreach ($challenges as $chlg){
            $cid = $chlg->getId();
            $cname = $chlg->getAcronym();
            $selected = ($cname == $challenge->getAcronym())? "selected='selected'" : "";
            $chlg_opts .= "<option value='{$cid}' {$selected}>{$chlg->getAcronym()}</option>";
        }
        if($edit){
            $this->html .=<<<EOF
            <select name="challenge_id">{$chlg_opts}</select>
EOF;
        }
        else{
            $this->html .= "{$challenge->getName()} ({$challenge->getAcronym()})";
        }
        $this->html .= "</td></tr>";
    }

    function showPeople(){
        global $wgUser, $wgServer, $wgScriptPath, $config;
        $me = Person::newFromWgUser();
        
        $edit = (isset($_POST['edit']) && $this->canEdit() && !isset($this->visibility['overrideEdit']));
        $project = $this->project;

        if(!$edit){
            $this->html .= "<table width='100%'><tr><td valign='top' width='50%'>";
            $this->showRole(PL);
            $this->showRole(PA);
            if($this->project->getType() == "Innovation Hub"){
                $this->showRole(null, 'Innovation Hub Team');
            }
            else{
                if($this->project->getType() == "Administrative"){
                    $this->showRole("NMO");
                }
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
            }
            $this->html .= "</td></tr></table>";
        }
    }
    
    function showRole($role, $text=null){
        global $config;
        $me = Person::newFromWgUser();
        if(isset($this->shownRoles[$role])){
            return;
        }
        $this->shownRoles[$role] = true;
        $edit = (isset($_POST['edit']) && $this->canEdit() && !isset($this->visibility['overrideEdit']));
        $project = $this->project;

        if(!$project->isDeleted()){
            $people = $project->getAllPeople($role);
        }
        else{
            $people = $project->getAllPeopleOn($role, $project->getEffectiveDate());
        }
        if(count($people) > 0){
            if($text != null){
                $this->html .= "<h2><span class='mw-headline'>{$text}</span></h2>";
            }
            else{
                if($role == PL && count($people) == 1){
                    // There is normally just 1 PL, so only use singlular
                    $this->html .= "<h2><span class='mw-headline'>".ucwords($config->getValue('roleDefs', $role))."</span></h2>";
                }
                else{
                    // Other roles will normally have multiple people, but also pluralize if there is more than one PL
                    $this->html .= "<h2><span class='mw-headline'>".ucwords(Inflect::pluralize($config->getValue('roleDefs', $role)))."</span></h2>";
                }
            }
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
            $this->html .= "<h2><span class='mw-headline'>Department Profile (live on website)</span></h2>";
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
        if($project->getType() == 'Administrative'){
            $researchProject = Project::newFromName($project->getName()." Research");
            if($researchProject != null && $researchProject->getId() != 0){
                $this->html .= "<h2>Research Project</h2>";
                $this->html .= "<a href='{$researchProject->getUrl()}'>{$researchProject->getName()}</a><br />";
            }
        }
    }

}    
    
?>
