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
        $preds = $this->project->getPreds();
        if(count($preds) > 0 && !isset($_GET['generatePDF'])){
            $predLinks = array();
            foreach($preds as $pred){
                if($pred->getName() != $project->getName()){
                    $predLinks[] = "<a href='{$pred->getUrl()}'><b>{$pred->getName()}</b></a>";
                }
            }
            if(count($predLinks) > 0){
                $this->html .= "<div style='margin-left: 5px; margin-top: -20px;'>&#10551;<small> Evolved from ".implode(", ", $predLinks)."</small></div>";
            }
        }
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
        if($config->getValue("projectTypes")){
            $this->html .= "<tr><td><b>Type:</b></td><td>{$this->project->getType()}</td></tr>";
        }
        if($config->getValue("bigBetProjects") && !$this->project->isSubProject()){
            $this->html .= "<tr><td><b>Big-Bet:</b></td><td>{$bigbet}</td></tr>";
        }
        if($config->getValue("projectStatus")){
            if(!$edit || !$me->isRoleAtLeast(STAFF)){
                $endedhtml = ($this->project->getStatus() == "Ended") ? "(".substr($this->project->getEffectiveDate(), 0, 10).")" : "";
                $this->html .= "<tr><td><b>Status:</b></td><td>{$this->project->getStatus()} {$endedhtml}</td></tr>";
            }
            else{
                $statusField = new SelectBox("status", "Status", $this->project->getStatus(), array("Proposed", "Deferred", "Active", "Ended"));
                $startField = new CalendarField("start_date", "Start Date", substr($this->project->getCreated(), 0, 10));
                $endField = new CalendarField("effective_date", "End Date", substr($this->project->getEffectiveDate(), 0, 10));
                $this->html .= "<tr><td><b>Status:</b></td><td>{$statusField->render()}</td></tr>";
                $this->html .= "<tr><td><b>Start Date:</b></td><td>{$startField->render()}</td></tr>";
                $this->html .= "<tr><td><b>End Date:</b></td><td>{$endField->render()}</td></tr>";
            }
        }
        if(!$edit && $website != "" && $website != "http://" && $website != "https://"){
            $this->html .= "<tr><td><b>Website:</b></td><td><a href='{$website}' target='_blank'>{$website}</a></td></tr>";
        }
        else if($edit){
            $this->html .= "<tr><td><b>Website:</b></td><td><input type='text' name='website' value='{$website}' size='40' /></td></tr>";
        }
        $this->html .= "</table>
            <script type='text/javascript'>
                $('[name=status]').change(function(){
                    if($('[name=status]').val() == 'Ended'){
                        $('[name=effective_date]').closest('tr').show();
                    }
                    else{
                        $('[name=effective_date]').closest('tr').hide();
                    }
                });
                $('[name=status]').change();
            </script>";

        $this->showPeople();
        //$this->showChampions();
        $this->showDescription();
        $this->html .= $this->showTable();
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
        
        $this->project->themes = array();
        if(isset($_POST['challenge']) && is_array($_POST['challenge'])){
            foreach($_POST['challenge'] as $themeId){
                $theme = Theme::newFromId($themeId);
                $this->project->themes[] = $theme;
            }
        }
        $this->project->update();
        if(isset($_POST['status']) && $me->isRoleAtLeast(STAFF)){
            if($_POST['status'] == "Ended"){
                $_POST['project'] = $this->project->getName();
                APIRequest::doAction('DeleteProject', true);
            }
            else{
                DBFunctions::update('grand_project_status',
                                    array('status' => $_POST['status']),
                                    array('evolution_id' => EQ($this->project->getEvolutionId()),
                                          'project_id' => EQ($this->project->getId())));
            }
            Project::$cache = array();
            // Update Dates
            $startDate = @DBFunctions::escape($_POST['start_date']);
            $endDate = @DBFunctions::escape($_POST['effective_date']);
            DBFunctions::execSQL("UPDATE `grand_project_evolution`
                                  SET `effective_date` = '$endDate'
                                  WHERE `new_id` = '{$this->project->getId()}'
                                  ORDER BY `date` DESC
                                  LIMIT 1", true);
            DBFunctions::execSQL("UPDATE `grand_project_evolution`
                                  SET `effective_date` = '$startDate'
                                  WHERE `new_id` = '{$this->project->getId()}'
                                  ORDER BY `date` ASC
                                  LIMIT 1", true);
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
        $challenges = $this->project->getChallenges();
        
        if($edit){
            $challengeNames = array();
            $themes = Theme::getAllThemes($this->project->getPhase());
            foreach($themes as $challenge){
                $challengeNames[$challenge->getId()] = $challenge->getAcronym();
            }
            $collection = new Collection($challenges);
            $challengeCheckBox = new VerticalCheckBox2("challenge", "", $collection->pluck('getId()'), $challengeNames, VALIDATE_NOTHING);
            
            $this->html .= $challengeCheckBox->render();
        }
        else{
            $text = array();
            foreach($challenges as $challenge){
                $text[] = "{$challenge->getName()} ({$challenge->getAcronym()})";
            }
            $this->html .= implode(", ", $text);
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
        if($project->getType() == 'Administrative'){
            $researchProject = Project::newFromName($project->getName()." Research");
            if($researchProject != null && $researchProject->getId() != 0){
                $this->html .= "<h2>Research Project</h2>";
                $this->html .= "<a href='{$researchProject->getUrl()}'>{$researchProject->getName()}</a><br />";
            }
        }
    }
    
    /**
     * Shows a table of this Person's products, and is filterable by the
     * visualizations which appear above it.
     */
    function showTable(){
        global $config;
        $me = Person::newFromWgUser();
        $products = $this->project->getPapers("all", "0000-00-00", EOT);
        $string = "";
        if(count($products) > 0){
            $string = "<div class='pdfnodisplay'>";
            $string .= "<h2>".Inflect::pluralize($config->getValue('productsTerm'))."</h2>";
            $string .= "<table id='projectProducts' rules='all' frame='box'>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Authors</th>
                    </tr>
                </thead>
                <tbody>";
            foreach($products as $paper){

                $names = array();
                foreach($paper->getAuthors() as $author){
                    if($author->getId() != 0 && $author->getUrl() != ""){
                        $names[] = "<a href='{$author->getUrl()}'>{$author->getNameForProduct()}</a>";
                    }
                    else{
                        $names[] = $author->getNameForForms();
                    }
                }
                
                $string .= "<tr>";
                $string .= "<td><span class='productTitle' data-id='{$paper->getId()}' data-href='{$paper->getUrl()}'>{$paper->getTitle()}</span><span style='display:none'>{$paper->getDescription()} ".implode(", ", $paper->getUniversities())."</span></td>";
                $string .= "<td>{$paper->getCategory()}</td>";
                $string .= "<td style='white-space: nowrap;'>{$paper->getDate()}</td>";
                $string .= "<td>".implode(", ", $names)."</td>";
                
                $string .= "</tr>";
            }
            $string .= "</tbody>
                </table>
                <script type='text/javascript'>
                    var projectProducts = $('#projectProducts').dataTable({
                        order: [[ 2, 'desc' ]],
                        autoWidth: false,
                        drawCallback: renderProductLinks
                    });
                </script>
            </div>";
        }
        return $string;
    }

}    
    
?>
