<?php

class ProjectMainTab extends AbstractEditableTab {

    var $project;
    var $visibility;
    var $rolesShown = array();
    var $nRolesCells = 0;

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
        $title = "";
        if($edit){
            if($project->isSubProject()){
                $acronymField = new TextField("acronym", "New Identifier", $this->project->getName());
                $title .= "<tr><td class='label'>New Identifier:</td><td class='value'>{$acronymField->render()}</td></tr>";
            }
            $fullNameField = new TextField("fullName", "New Title", $this->project->getFullName());
            $title .= "<tr><td class='label'>New Title:</td><td class='value'>{$fullNameField->render()}</td></tr>";
        }
        $this->html .= "<div style='display:flex;flex-wrap:wrap;'>";
        if(!isset($_GET['generatePDF'])){
            $this->html .= "<div>";
            $this->showFiles();
            $this->html .= "</div>";
        }
        $this->html .= "    <div style='white-space:nowrap;margin-right:30px;'>
                                <table>
                            $title";
        if($project->getType() != "Administrative"){
            $this->showChallenge();
        }
        if($config->getValue("projectTypes")){
            $this->html .= ($edit) ? "<tr><td class='label'>Type:</td><td class='value'>{$this->project->getType()}</td></tr>"
                                   : "<tr><td><b>Type:</b></td><td>{$this->project->getType()}</td></tr>";
        }
        if($config->getValue("projectStatus")){
            if(!$edit || !$me->isRoleAtLeast(STAFF)){
                $this->html .= "<tr><td><b>Status:</b></td><td>{$this->project->getStatus()}</td></tr>";
                $this->html .= "<tr><td><b>Start Date:</b></td><td>".substr($this->project->getStartDate(), 0, 10)."</td></tr>";
                $this->html .= "<tr><td><b>End Date:</b></td><td>".substr($this->project->getEndDate(), 0, 10)."</td></tr>";
            }
            else{
                $statusField = new SelectBox("status", "Status", $this->project->getStatus(), array("Proposed", "Deferred", "Active", "Ended"));
                $startField = new CalendarField("start_date", "Start Date", substr($this->project->getStartDate(), 0, 10));
                $endField = new CalendarField("effective_date", "End Date", substr($this->project->getEndDate(), 0, 10));
                $this->html .= "<tr><td class='label'>Status:</td><td class='value'>{$statusField->render()}</td></tr>";
                $this->html .= "<tr><td class='label'>Start Date:</td><td class='value'>{$startField->render()}</td></tr>";
                $this->html .= "<tr><td class='label'>End Date:</td><td class='value'>{$endField->render()}</td></tr>";
            }
        }
        if(!$edit && $website != "" && $website != "http://" && $website != "https://"){
            $this->html .= "<tr><td><b>Website:</b></td><td><div style='display:block;max-width:30em;overflow:hidden;text-overflow:ellipsis;'><a href='{$website}' target='_blank'>{$website}</a></div></td></tr>";
        }
        else if($edit){
            $this->html .= "<tr><td class='label'>Website:</td><td class='value'><input type='text' name='website' value='{$website}' size='40' /></td></tr>";
        }
        if(!$edit){
            // Project Leader(s)
            $leaders = $this->showRole(PL, null, false, true);
            if(count($leaders) > 0){
                if(count($leaders) == 1){
                    // There is normally just 1 PL, so only use singlular
                    $this->html .= "<tr><td colspan='2' style='white-space:nowrap;'><b>".ucwords($config->getValue('roleDefs', PL))."</b></td></tr>";
                }
                else{
                    // Other roles will normally have multiple people, but also pluralize if there is more than one PL
                    $this->html .= "<tr><td colspan='2' style='white-space:nowrap;'><b>".ucwords(Inflect::pluralize($config->getValue('roleDefs', PL)))."</b></td></tr>";
                }
                $this->html .= "<tr><td colspan='2'>";
                $leadersString = array();
                foreach($leaders as $p){
                    $leadercss = (!isset($_GET['generatePDF'])) ? "font-weight:bold;" : "";
                    $leadersString[] = "&nbsp;&nbsp;&nbsp;<a href='{$p->getUrl()}' style='$leadercss'>{$p->getReversedName()}</a>";
                }
                $this->html .= implode("<br />", $leadersString);
                $this->html .= "</td></tr>";
            }
        }
        $this->html .= "</table></div><div style='flex-grow:1;min-width:250px;'>{$this->showCloud()}</div>";
        $this->html .= "</div>";
        
        $this->showPeople();
        $this->showDescription();
        $this->html .= $this->showTable();
        if($me->isRoleAtLeast(STAFF) && $config->getValue('networkName') == "FES"){
            $this->html .= "<span class='pdfnodisplay'><a class='button' href='{$this->project->getUrl()}?generatePDF' style='margin-top:2px;'>Download PDF</a></span>";
        }
        return $this->html;
    }
    
    function showFiles(){
        global $config;
        $edit = (isset($_POST['edit']) && $this->canEdit() && !isset($this->visibility['overrideEdit']));
        if($config->getValue('allowPhotoUpload')){
            if($edit){
                $this->html .= "<table style='margin:0 auto; width:1%; padding-right:25px;'>
                                    <tr>
                                        <th colspan='2'></th>
                                        <th>Delete?</th>
                                    </tr>";
                for($n=1;$n<=PROJECT_FILE_COUNT;$n++){
                    $image = $this->project->getImage($n);
                    $delete = "";
                    if($image != ""){
                        $image = "<img style='max-height:70px;max-width:100px;border-radius:5px;' src='{$image}' />";
                        $delete = "<input style='position:absolute;' type='checkbox' name='file_delete{$n}' value='1' />";
                    }
                    $this->html .= "<tr>
                                        <td class='label'><b>Image {$n}:</b></td>
                                        <td class='value'><input type='file' style='width:300px;' accept='image/*' name='file{$n}' /></td>
                                        <td rowspan='2' style='white-space:nowrap;'>{$delete}{$image}</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td><input type='text' name='file_url{$n}' style='width:300px;' placeholder='Enter image URL here instead of file upload' /></td>
                                        <td></td>
                                    </tr>";
                }
                $this->html .= "</table>";
            }
            else{
                $this->html .= "<div style='text-align:center;'>";
                $images = array();
                for($n=1;$n<=PROJECT_FILE_COUNT;$n++){
                    $image = $this->project->getImage($n);
                    if($image != ""){
                        $images[] = $image;
                    }
                }
                foreach($images as $image){
                    $this->html .= "<a href='{$image}' data-lightbox='images' style='display:inline-block;max-width: calc(".(100/count($images))."% - 20px);margin:0 10px;box-sizing:border-box;'>
                                        <div style='max-width:350px;max-height:200px;'>
                                            <img style='max-width:min(100%, 350px);max-height:200px;border-radius:5px;object-fit: contain;' src='{$image}' />
                                        </div>
                                    </a>";
                }
                $this->html .= "</div>";
            }
        }
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
            Project::$projectDataCache = array();
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
            Project::$projectDataCache = array();
            // Update Dates
            $this->project = Project::newFromId($this->project->getId());
            $startDate = @DBFunctions::escape($_POST['start_date']);
            $endDate = @DBFunctions::escape($_POST['effective_date']);
            DBFunctions::update('grand_project_status',
                                array('start_date' => $startDate,
                                      'end_date' => $endDate),
                                array('evolution_id' => EQ($this->project->getEvolutionId()),
                                      'project_id' => EQ($this->project->getId())));
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
            Project::$cache = array();
            Project::$projectDataCache = array();
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
        for($n=1;$n<=PROJECT_FILE_COUNT;$n++){
            $error = $this->uploadFile($n);
            if($error != ""){
                return $error;
            }
        }
    }
    
    function uploadFile($n){
        $error = "";
        $fileName = "Photos/{$this->project->getId()}_{$n}.jpg";
        // Do Deleting First
        if(isset($_POST["file_delete{$n}"]) && $_POST["file_delete{$n}"] == "1"){
            unlink($fileName);
        }
        // Then Try Upload
        if((isset($_FILES["file{$n}"]) && $_FILES["file{$n}"]['tmp_name'] != "") ||
           (isset($_POST["file_url{$n}"]) && $_POST["file_url{$n}"] != "")){
            if(isset($_POST["file_url{$n}"]) && $_POST["file_url{$n}"] != ""){
                $type = "";
                if(strstr(@$_POST["file_url{$n}"], ".gif") !== false){
                    $type = "image/gif";
                }
                else if(strstr(@$_POST["file_url{$n}"], ".png") !== false){
                    $type = "image/png";
                }
                else if(strstr(@$_POST["file_url{$n}"], ".jpg") !== false ||
                        strstr(@$_POST["file_url{$n}"], ".jpeg") !== false){
                    $type = "image/jpeg";
                }
                // create curl resource
                $ch = curl_init();

                // set url
                curl_setopt($ch, CURLOPT_URL, $_POST["file_url{$n}"]);

                //return the transfer as a string
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

                // $output contains the output string
                $file = curl_exec($ch);

                // close curl resource to free up system resources
                curl_close($ch);
                $size = strlen($file);
            }
            else{
                $type = $_FILES["file{$n}"]['type'];
                $size = $_FILES["file{$n}"]['size'];
                $tmp = $_FILES["file{$n}"]['tmp_name'];
            }
            if($type == "image/jpeg" ||
               $type == "image/pjpeg" ||
               $type == "image/gif" || 
               $type == "image/png"){
                if($size <= 1024*1024*20){
                    //File is OK to upload
                    if(isset($_POST["file_url{$n}"]) && $_POST["file_url{$n}"] != ""){
                        file_put_contents($fileName, $file);
                    }
                    else{
                        move_uploaded_file($tmp, $fileName);
                    }
                    
                    if($type == "image/jpeg" || $type == "image/pjpeg"){
                        $src_image = @imagecreatefromjpeg($fileName);
                    }
                    else if($type == "image/png"){
                        $src_image = @imagecreatefrompng($fileName);
                    }
                    else if($type == "image/gif"){
                        $src_image = @imagecreatefromgif($fileName);
                    }
                    if($src_image != false){
                        imagealphablending($src_image, true);
                        imagesavealpha($src_image, true);
                        $src_width = imagesx($src_image);
                        $src_height = imagesy($src_image);
                        $dst_width = $src_width;
                        $dst_height = $src_height;
                        $dst_image = imagecreatetruecolor($dst_width, $dst_height);
                        imagealphablending($dst_image, true);
                        
                        imagesavealpha($dst_image, true);
                        imagecopyresampled($dst_image, $src_image, 0, 0, 0, 0, $dst_width, $dst_height, $src_width, $src_height);
                        imagedestroy($src_image);
                        
                        imagejpeg($dst_image, $fileName, 100);
                        imagedestroy($dst_image);
                    }
                    else{
                        //File is not an ok filetype
                        $error .= "The file you uploaded is not of the right type.  It should be either gif, png or jpeg";
                    }
                }
                else{
                    //File size is too large
                    $error .= "The file you uploaded is too large.  It should be smaller than 20MB.<br />";
                }
            }
            else{
                //File is not an ok filetype
                $error .= "The file you uploaded is not of the right type.  It should be either gif, png or jpeg.<br />";
            }
        }
        return $error;
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
        $this->html .= ($edit) ? "<tr><td class='label'>{$config->getValue("projectThemes")}:</td><td class='value'>"
                               : "<tr><td><b>{$config->getValue("projectThemes")}:</b></td><td>";
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
    
    function showCloud(){
        global $wgServer, $wgScriptPath, $wgTitle, $wgOut, $wgUser;
        $dataUrl = "$wgServer$wgScriptPath/index.php/{$wgTitle->getNSText()}:{$wgTitle->getText()}?action=getProjectWordleData&project={$this->project->getId()}&limit=50";
        $wordle = new Wordle($dataUrl, true);
        $wordle->width = "100%";
        $wordle->height = 232;
        return $wordle->show()."<script type='text/javascript'>
                                    onLoad{$wordle->index}();
                                </script>";
    }

    function showPeople(){
        global $wgUser, $wgServer, $wgScriptPath, $config;
        $me = Person::newFromWgUser();
        $edit = (isset($_POST['edit']) && $this->canEdit() && !isset($this->visibility['overrideEdit']));
        if(!$edit){
            if(isset($_GET['generatePDF'])){ $this->html .= "\n<div style='font-size: smaller;display:table;width:100%;'>"; }
            else { $this->html .= "\n<div style='display:flex;flex-wrap:wrap;width:100%;'>"; }
            $this->showRole(PL);
            $this->showRole(PA);
            if($config->getValue('networkName') == "GlycoNet" && $this->project->getType() == "Administrative"){
                $this->showRole("GIS Leader");
                $this->showRole("GIS Manager");
            }
            if($this->project->getType() == "Innovation Hub"){
                $this->showRole(null, 'Innovation Hub Team');
            }
            else{
                if($this->project->getType() == "Administrative"){ $this->showRole("NMO"); }
                $this->showRole(CI);
                $this->showRole(AR);
                $this->showRole(CHAMP);
                $this->showRole(HQP);
                $this->showRole(PARTNER);
                $this->showRole(AG);
                $this->showRole(HQP, "Alumni ".HQP, true);
                $this->showRole(EXTERNAL);
            }
            $this->showRole("CRMContact", "Contact");
            $this->finishRoleRow();
            $this->html .= "</div>";
        }
    }
    
    function showRole($role, $text=null, $past=false, $returnOnly=false){
        global $config;
        $me = Person::newFromWgUser();
        if(!$past){
            if(isset($this->shownRoles[$role])){
                return;
            }
            $this->shownRoles[$role] = true;
        }
        if($role == "CRMContact"){
            $people = $this->project->getContacts();
        }
        else{
            $project = $this->project;
            if(!$project->isDeleted()){
                $people = $project->getAllPeople($role);
            }
            else{
                $people = $project->getAllPeopleOn($role, $project->getEffectiveDate());
            }
            // Filter for Alumni people
            if($past){
                $allPeople = $project->getAllPeopleDuring($role, "0000-00-00", EOT);
                $alumnis = array();
                foreach($allPeople as $p1){
                    $found = false;
                    foreach($people as $p2){
                        if($p1->getId() == $p2->getId()){
                            $found = true;
                            break;
                        }
                    }
                    if(!$found){
                        $alumnis[] = $p1;
                    }
                }
                $people = $alumnis;
            }
        }
        if($returnOnly){
            return $people;
        }
        
        if(count($people) > 0){
            $colcss = "flex: 0 1 33.333%;padding-right:1em;box-sizing: border-box;";
            if(isset($_GET['generatePDF'])){
                $colcss = "width:33.333%;display:table-cell;vertical-align:top;";
                switch($this->nRolesCells % 3){
                    case 0:
                        $this->html .= "<div style='display:table-row;width:100%;'>";
                        $colcss .= "padding-right: 0.667em;";
                        break;
                    case 1:
                        $colcss .= "padding-left: 0.333em;";
                        $colcss .= "padding-right: 0.333em;";
                        break;
                    case 2:
                        $colcss .= "padding-left: 0.667em;";
                }
            }
            $this->html .= "<div style='$colcss'>";
            if($text != null){
                $this->html .= "<h2><span class='mw-headline'>".Inflect::pluralize($text)."</span></h2>";
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
            $this->html .= "<table style='width:100%;' cellspacing='0' cellpadding='0'>
                                <tr><td valign='top' style='width:50%;'>
                                    <ul style='padding-left:1.5em;margin-left:0;margin-right:0;'>";
            $i=0;
            $limit = 5;
            if($role == "CRMContact"){
                foreach($people as $contact){
                    $this->html .= "<li><a href='{$contact->getUrl()}'>{$contact->getTitle()}</a></li>\n";
                    $i++;
                }
            }
            else{
                foreach($people as $p){
                    if(count($people) >= $limit && $i == ceil(count($people)/2)){
                        $this->html .= "</ul></td><td valign='top'><ul style='padding-left:1em;margin-left:0;margin-right:0;'>";
                    }
                    $this->html .= "<li><a href='{$p->getUrl()}'>{$p->getReversedName()}</a></li>\n";
                    $i++;
                }
            }
            $this->html .= "</ul></td></tr></table>";
            $this->html .= "</div>";
            $this->nRolesCells++;
            if(isset($_GET['generatePDF']) && $this->nRolesCells % 3 == 0){
                $this->html .= "</div>";
            }
        }
    }
    
    function finishRoleRow(){
        // Finish the row even if it isn't yet complete
        if(isset($_GET['generatePDF']) && $this->nRolesCells % 3 != 0){
            $this->html .= "</div>";
        }
        $this->nRolesCells = 0;
    }
    
    function showDescription(){
        global $wgServer, $wgScriptPath, $config;
        
        $edit = (isset($_POST['edit']) && $this->canEdit() && !isset($this->visibility['overrideEdit']));
        $project = $this->project;
        
        $description = $project->getDescription();
        
        if($edit || !$edit && $description != ""){
            $this->html .= "<h2><span class='mw-headline'>Project Overview</span></h2>";
        }
        if(!$edit){
            $this->html .= $description."<br />";
        }
        else{
            $this->html .= "<textarea name='description' style='height:500px;width:auto;'>{$description}</textarea>
            <script type='text/javascript'>
                $('textarea[name=description]').tinymce({
                    theme: 'modern',
                    relative_urls : false,
                    convert_urls: false,
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
