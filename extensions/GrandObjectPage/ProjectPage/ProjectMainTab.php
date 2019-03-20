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
        $address = $this->project->getMailingAddress();
        $website = $this->project->getWebsite();
        $bigbet = ($this->project->isBigBet()) ? "Yes" : "No";
        $title = "";
        
        if($edit){
            if($project->isSubProject()){
                $acronymField = new TextField("acronym", "New Acronym", $this->project->getName());
                $title .= "<tr><td><b>New Acronym:</b></td><td>{$acronymField->render()}</td></tr>";
            }
            $fullNameField = new TextField("fullName", "New Title", $this->project->getFullName());
            $fullNameField->attr('size', 30);
            $title .= "<tr><td align='right'><b>New Title:</b></td><td>{$fullNameField->render()}</td></tr>";
        }
        else{
            
        }
        $this->html .= "<table><tr>";
        
        // Column 1
        $this->html .= "<td><table>";
        if($edit){
            $this->showEditPhoto($this->project, $this->visibility);
        }
        else{
            $this->showPhoto($this->project, $this->visibility);
        }
        $this->html .= "</table></td></tr><tr><td valign='top' style='padding-right:25px;'><table>";
        $this->html .= "$title";
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
                $this->html .= "<tr><td align='right'><b>Status:</b></td><td>{$statusField->render()}</td></tr>";
            }
        }
        
        if(!$edit && $website != "" && $website != "http://" && $website != "https://"){
            $this->html .= "<tr><td><b>Website:</b></td><td><a href='{$website}' target='_blank'>{$website}</a></td></tr>";
        }
        else if($edit){
            $this->html .= "<tr><td align='right'><b>Website:</b></td><td><input type='text' name='website' value='{$website}' size='30' /></td></tr>";
            $this->html .= "<tr>
                                <td align='right' valign='top'>
                                    <b>Mailing Address:</b>
                                </td>
                                <td align='right'>
                                    <small>
                                        <b>Line 1:</b><input type='text' size='28' name='address_line1' value='".str_replace("'", "&#39;", $address->getLine1())."' /><br />
                                        <b>Line 2:</b><input type='text' size='28' name='address_line2' value='".str_replace("'", "&#39;", $address->getLine2())."' /><br />
                                        <b>Line 3:</b><input type='text' size='28' name='address_line3' value='".str_replace("'", "&#39;", $address->getLine3())."' /><br />
                                        <b>Line 4:</b><input type='text' size='28' name='address_line4' value='".str_replace("'", "&#39;", $address->getLine4())."' /><br />
                                        <b>Postal Code:</b><input type='text' size='28' name='address_code' value='".str_replace("'", "&#39;", $address->getPostalCode())."' /><br />
                                        <b>City:</b><input type='text' size='28' name='address_city' value='".str_replace("'", "&#39;", $address->getCity())."' /><br />
                                        <b>Province:</b><input type='text' size='28' name='address_province' value='".str_replace("'", "&#39;", $address->getProvince())."' /><br />
                                        <b>Country:</b><input type='text' size='28' name='address_country' value='".str_replace("'", "&#39;", $address->getCountry())."' />
                                    </small>
                                </td>
                            </tr>";
        }
        
        // Column 2
        $this->html .= "</table></td><td valign='top'>";
        if($edit){
            $values = array();
            $programs = $this->project->getPrograms();
            foreach($programs as $program){
                $values[] = array("programs" => $program['name'],
                                  "urls" => $program['url']);
            }
            
            $programPlusMinus = new PlusMinus("program_plusminus");
            $programTable = new FormTable("program_table");
            $programRow = new FormTableRow("programs_row");
            
            $programName = new TextField("programs[]", "Name", "", VALIDATE_NOTHING);
            $programUrl = new TextField("urls[]", "Url", "", VALIDATE_NOTHING);
            $programName->attr('size', 21);
            $programUrl->attr('size', 21);
            
            $programRow->append($programName);
            $programRow->append($programUrl);
            
            $programTable->append($programRow);
            $programPlusMinus->append($programTable);
            $programPlusMinus->values = $values;
            
            $this->html .= "<table><tr>
                                <td valign='top' colspan='2'><table width='100%'><tr><th width='50%'>Program Name</th><th width='50%'>Url</th></tr></table>{$programPlusMinus->render()}</td>
                            </tr></table>";
        }
        $this->html .= "</table></td></tr></table>";

        $this->showPeople();
        //$this->showChampions();
        $this->showDescription();
        if($me->isRoleAtLeast(STAFF) && $config->getValue('networkName') == "FES"){
            $this->html .= "<span class='pdfnodisplay'><a class='button' href='{$this->project->getUrl()}?generatePDF'>Download PDF</a></span>";
        }
        return $this->html;
    }
    
    /**
     * Displays the photo for this person
     */
    function showPhoto($project, $visibility){
        $this->html .= "<tr><td style='padding-right:25px;' valign='top' colspan='2'>";
        if($project->getPhoto() != ""){
            $this->html .= "<img src='{$project->getPhoto()}' style='max-height:120px;' />";
        }
        $this->html .= "</td></tr>";
    }
    
    function showEditPhoto($project, $visibility){
        global $config;
        $this->html .= "<tr><td style='padding-right:25px;' valign='top' colspan='2'>";
        $this->html .= "<img src='{$project->getPhoto()}' style='max-height:120px;' />";
        $this->html .= "</td></tr>";
        if($config->getValue('allowPhotoUpload') || $me->isRoleAtLeast(STAFF)){
            $this->html .= "<tr>
                                <td align='right'><b>Upload new Photo:</b></td>
                                <td><input type='file' name='photo' /></td>
                            </tr>
                            <tr>
                                <td></td><td><small><li>Max file size is 20MB</li>
                                                    <li>File type must be <i>gif</i>, <i>png</i> or <i>jpeg</i></li></small></td>
                            </tr>";
        }
    }
    
    function handleEdit(){
        global $wgOut, $wgMessage;
        $me = Person::newFromWgUser();
        $error = "";
        if(isset($_FILES['photo']) && $_FILES['photo']['tmp_name'] != ""){
            $type = $_FILES['photo']['type'];
            $size = $_FILES['photo']['size'];
            $tmp = $_FILES['photo']['tmp_name'];
            if($type == "image/jpeg" ||
               $type == "image/pjpeg" ||
               $type == "image/gif" || 
               $type == "image/png"){
                if($size <= 1024*1024*20){
                    //File is OK to upload
                    $fileName = "Photos/{$this->project->getName()}_{$this->project->getId()}.jpg";
                    move_uploaded_file($tmp, $fileName);
                    
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
        if($error == ""){
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
            
            $address = $this->project->getMailingAddress();
            $address->type = 'Mailing';
            $address->line1 = @$_POST['address_line1'];
            $address->line2 = @$_POST['address_line2'];
            $address->line3 = @$_POST['address_line3'];
            $address->line4 = @$_POST['address_line4'];
            $address->city = @$_POST['address_city'];
            $address->province = @$_POST['address_province'];
            $address->country = @$_POST['address_country'];
            $address->code = @$_POST['address_code'];
            $this->project->updateMailingAddress($address);
            
            $programs = array();
            if(isset($_POST['programs'])){
                foreach(@$_POST['programs'] as $key => $program){
                    $name = $program;
                    $url = $_POST['urls'][$key];
                    $programs[] = array(
                        'proj_id' => $this->project->getId(),
                        'name' => $name,
                        'url' => $url
                    );
                }
            }
            $this->project->updatePrograms($programs);
            
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
        $this->html .= "<tr><td align='right'><b>{$config->getValue("projectThemes")}:</b></td><td>";
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
