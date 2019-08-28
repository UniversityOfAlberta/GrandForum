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
        $title = "";
        
        if($edit){
            $this->html .= "<table><tr>";
        }
        else{
            $this->html .= "<table style='width:100%;'><tr>";
        }
        
        // Column 1
        $this->html .= "<td colspan='2'><table>";
        if($edit){
            $this->showEditPhoto($this->project, $this->visibility);
        }
        else{
            $this->showPhoto($this->project, $this->visibility);
        }
        $this->html .= "</table></td></tr><tr><td valign='top' style='padding-right:25px;'>";
        
        if(!$edit){
            $this->html .= "<table style='width:50%; display: inline-block;'>";
            $addressLine1 = implode("<br />", array_filter(array($address->getLine1(), $address->getLine2(), $address->getLine3(), $address->getLine4())));
            $addressLine2 = implode(", ", array_filter(array($address->getCity(), $address->getProvince(), $address->getPostalCode(), $address->getCountry())));
            $programsLine = array();
            foreach($this->project->getPrograms() as $program){
                if($program['name'] != "" && $program['url'] != ""){
                    $programsLine[] = "<a href='{$program['url']}' target='_blank'>{$program['name']}</a>";
                }
            }
            $programsLine = implode("<br />", $programsLine);
            $useGeneric = $this->project->getUseGeneric();
            $adminUseGeneric = $this->project->getAdminUseGeneric();
            $techUseGeneric = $this->project->getTechUseGeneric();
            $email    = ($address->getEmail() != "") ? 
                        "<a href='mailto:{$address->getEmail()}'>{$address->getEmail()}</a>" : "";
            $chairEmail = $this->project->getEmail();
            $adminEmail = $this->project->getAdminEmail();
            $techEmail = $this->project->getTechEmail();
            $website  = ($this->project->getWebsite() != "" && $this->project->getWebsite() != "http://" && $this->project->getWebsite() != "https://") ? 
                        "<a href='{$this->project->getWebsite()}' target='_blank'>{$this->project->getWebsite()}</a>" : "";
            $deptWebsite  = ($this->project->getDeptWebsite() != "" && $this->project->getDeptWebsite() != "http://" && $this->project->getDeptWebsite() != "https://") ? 
                        "<a href='{$this->project->getDeptWebsite()}' target='_blank'>{$this->project->getDeptWebsite()}</a>" : "";
            $twitter  = ($address->getTwitter() != "" && $address->getTwitter() != "http://" && $address->getTwitter() != "https://") ? 
                        "<a href='{$address->getTwitter()}' target='_blank'>{$address->getTwitter()}</a>" : "";
            $facebook = ($address->getFacebook() != "" && $address->getFacebook() != "http://" && $address->getFacebook() != "https://") ? 
                        "<a href='{$address->getFacebook()}' target='_blank'>{$address->getFacebook()}</a>" : "";
            $linkedin = ($address->getLinkedIn() != "" && $address->getLinkedIn() != "http://" && $address->getLinkedIn() != "https://") ? 
                        "<a href='{$address->getLinkedIn()}' target='_blank'>{$address->getLinkedIn()}</a>" : "";
            $youtube  = ($address->getYoutube() != "" && $address->getYoutube() != "http://" && $address->getYoutube() != "https://") ? 
                        "<a href='{$address->getYoutube()}' target='_blank'>{$address->getYoutube()}</a>" : "";
                        
            if($useGeneric && $chairEmail != ""){
                $chairEmail = $chairEmail;
            }
            else {
                $leaders = $this->project->getLeaders();
                if(count($leaders) > 0){
                    foreach($leaders as $leader){
                        $chairEmail = $leader->getEmail();
                        break;
                    }
                }
            }
            
            if(($adminUseGeneric || count($this->project->getAllPeople(PA)) == 0) && $adminEmail != ""){
                $adminEmail = $adminEmail;
            }
            else {
                $admins = $this->project->getAllPeople(PA);
                if(count($admins) > 0){
                    foreach($admins as $admin){
                        $adminEmail = $admin->getEmail();
                        break;
                    }
                }
            }
            
            if(($techUseGeneric || count($this->project->getAllPeople(PS)) == 0) && $techEmail != ""){
                $techEmail = $techEmail;
            }
            else {
                $techs = $this->project->getAllPeople(PS);
                if(count($techs) > 0){
                    foreach($techs as $tech){
                        $techEmail = $tech->getEmail();
                        break;
                    }
                }
            }
            
            $chairEmail = ($chairEmail != "") ? "<a href='mailto:{$chairEmail}'>{$chairEmail}</a>" : "";
            $adminEmail = ($adminEmail != "") ? "<a href='mailto:{$adminEmail}'>{$adminEmail}</a>" : "";
            $techEmail = ($techEmail != "") ? "<a href='mailto:{$techEmail}'>{$techEmail}</a>" : "";
            
            $this->html .= "<tr>
                                <td valign='top' colspan='2'>
                                    <b>Mailing Address:</b>
                                    <div style='margin-left:25px;'>
                                        {$addressLine1}<br />
                                        {$addressLine2}
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td valign='top' colspan='2'>
                                    <b>Contact:</b>
                                    <div style='margin-left:25px; white-space: nowrap; max-width: 500px; overflow-x: hidden; text-overflow: ellipsis;'>
                                        <span style='display:inline-block; width:80px; color: #555;'>Phone</span>    {$address->getPhone()}<br />
                                        <span style='display:inline-block; width:80px; color: #555;'>Fax</span>      {$address->getFax()}<br />
                                        <span style='display:inline-block; width:80px; color: #555;'>Email</span>    {$email}<br />
                                        <span style='display:inline-block; width:80px; color: #555;'>Chair Email</span> {$chairEmail}<br />
                                        <span style='display:inline-block; width:80px; color: #555;'>Admin Email</span> {$adminEmail}<br />
                                        <span style='display:inline-block; width:80px; color: #555;'>Tech Email</span> {$techEmail}<br />
                                        <span style='display:inline-block; width:80px; color: #555;'>Dept Website</span>  {$deptWebsite}<br />
                                        <span style='display:inline-block; width:80px; color: #555;'>Uni Website</span>  {$website}<br />
                                        <span style='display:inline-block; width:80px; color: #555;'>Twitter</span>  {$twitter}<br />
                                        <span style='display:inline-block; width:80px; color: #555;'>Facebook</span> {$facebook}<br />
                                        <span style='display:inline-block; width:80px; color: #555;'>LinkedIn</span> {$linkedin}<br />
                                        <span style='display:inline-block; width:80px; color: #555;'>Youtube</span>  {$youtube}
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td valign='top' colspan='2'>
                                    <b>Programs:</b>
                                    <div style='margin-left:25px;'>
                                        {$programsLine}
                                    </div>
                                </td>
                            </tr>";
        }
        else if($edit){
            $genericYesChecked = ($this->project->getUseGeneric()) ? "checked='checked'" : "";
            $genericNoChecked = ($this->project->getUseGeneric()) ? "" : "checked='checked'";
            $adminGenericYesChecked = ($this->project->getAdminUseGeneric()) ? "checked='checked'" : "";
            $adminGenericNoChecked = ($this->project->getAdminUseGeneric()) ? "" : "checked='checked'";
            $techGenericYesChecked = ($this->project->getTechUseGeneric()) ? "checked='checked'" : "";
            $techGenericNoChecked = ($this->project->getTechUseGeneric()) ? "" : "checked='checked'";
            $provinceSelect = new SelectBox("address_province", "address_province", $address->getProvince(), array('AB', 'BC', 'MB', 'NB', 'NL', 'NS', 'NT', 'NU', 'ON', 'PE', 'QC', 'SK', 'YT'));
            $this->html .= "<table>";
            $this->html .= "<tr>
                                <td align='right' valign='top' colspan='2'>
                                    <fieldset>
                                        <legend>Mailing Address</legend>
                                        <b>Line 1:</b><input type='text' size='35' name='address_line1' placeholder='Department of Computing Science' value='".str_replace("'", "&#39;", $address->getLine1())."' /><br />
                                        <b>Line 2:</b><input type='text' size='35' name='address_line2' placeholder='Room 1234' value='".str_replace("'", "&#39;", $address->getLine2())."' /><br />
                                        <b>Line 3:</b><input type='text' size='35' name='address_line3' placeholder='Building Main' value='".str_replace("'", "&#39;", $address->getLine3())."' /><br />
                                        <b>Line 4:</b><input type='text' size='35' name='address_line4' placeholder='200 Main Street' value='".str_replace("'", "&#39;", $address->getLine4())."' /><br />
                                        <b>Postal Code:</b><input type='text' size='35' name='address_code' value='".str_replace("'", "&#39;", $address->getPostalCode())."' /><br />
                                        <b>City:</b><input type='text' size='35' name='address_city' value='".str_replace("'", "&#39;", $address->getCity())."' /><br />
                                        <b>Province:</b><div style='width: 306px;display:inline-block;text-align:left;vertical-align:middle;'>{$provinceSelect->render()}</div><br />
                                        <b>Country:</b><input type='text' size='35' name='address_country' value='".str_replace("'", "&#39;", $address->getCountry())."' />
                                    </fieldset>
                                </td>
                            </tr>
                            <tr>
                                <td align='right' valign='top' colspan='2'>
                                    <fieldset>
                                        <legend>Contact</legend>
                                        <b>Phone:</b><input type='text' size='35' name='address_phone' value='".str_replace("'", "&#39;", $address->getPhone())."' /><br />
                                        <b>Fax:</b><input type='text' size='35' name='address_fax' value='".str_replace("'", "&#39;", $address->getFax())."' /><br />
                                        <b>Email:</b><input type='text' size='35' name='address_email' value='".str_replace("'", "&#39;", $address->getEmail())."' /><br />
                                        <b>Generic Chair Email:</b><input type='text' size='35' name='email' value='".str_replace("'", "&#39;", $this->project->getEmail())."' placeholder='chair@university.ca' /><br />
                                            <div style='width: 300px; text-align:left;'>
                                                
                                                <b>Use Generic?</b><br />
                                                <div style='float:right;width:200px;'>If 'No', the chair's personal email will be displayed.</div>
                                                &nbsp;&nbsp;&nbsp;<input type='radio' name='use_generic' style='vertical-align:middle;' value='1' $genericYesChecked /> Yes<br />
                                                &nbsp;&nbsp;&nbsp;<input type='radio' name='use_generic' style='vertical-align:middle;' value='0' $genericNoChecked /> No<br />
                                            </div>
                                        <b>Generic Admin Email:</b><input type='text' size='35' name='admin_email' value='".str_replace("'", "&#39;", $this->project->getAdminEmail())."' placeholder='admin@university.ca' /><br />
                                            <div style='width: 300px; text-align:left;'>
                                                
                                                <b>Use Generic?</b><br />
                                                <div style='float:right;width:200px;'>If 'No', the admin's personal email will be displayed.</div>
                                                &nbsp;&nbsp;&nbsp;<input type='radio' name='admin_use_generic' style='vertical-align:middle;' value='1' $adminGenericYesChecked /> Yes<br />
                                                &nbsp;&nbsp;&nbsp;<input type='radio' name='admin_use_generic' style='vertical-align:middle;' value='0' $adminGenericNoChecked /> No<br />
                                            </div>
                                        <b>Generic Tech Email:</b><input type='text' size='35' name='tech_email' value='".str_replace("'", "&#39;", $this->project->getTechEmail())."' placeholder='tech@university.ca' /><br />
                                            <div style='width: 300px; text-align:left;'>
                                                
                                                <b>Use Generic?</b><br />
                                                <div style='float:right;width:200px;'>If 'No', the tech's personal email will be displayed.</div>
                                                &nbsp;&nbsp;&nbsp;<input type='radio' name='tech_use_generic' style='vertical-align:middle;' value='1' $techGenericYesChecked /> Yes<br />
                                                &nbsp;&nbsp;&nbsp;<input type='radio' name='tech_use_generic' style='vertical-align:middle;' value='0' $techGenericNoChecked /> No<br />
                                            </div>
                                        <b>Dept Website:</b><input type='text' name='dept_website' value='".str_replace("'", "&#39;", $this->project->getDeptWebsite())."' size='35' /><br />
                                        <b>Uni Website:</b><input type='text' name='website' value='".str_replace("'", "&#39;", $this->project->getWebsite())."' size='35' /><br />
                                        <b>Twitter:</b><input type='text' size='35' name='address_twitter' placeholder='https://twitter.com/*****' value='".str_replace("'", "&#39;", $address->getTwitter())."' /><br />
                                        <b>Facebook:</b><input type='text' size='35' name='address_facebook' placeholder='https://www.facebook.com/*****/' value='".str_replace("'", "&#39;", $address->getFacebook())."' /><br />
                                        <b>LinkedIn:</b><input type='text' size='35' name='address_linkedin' placeholder='https://www.linkedin.com/school/*****/' value='".str_replace("'", "&#39;", $address->getLinkedIn())."' /><br />
                                        <b>Youtube:</b><input type='text' size='35' name='address_youtube' placeholder='https://www.youtube.com/channel/*****' value='".str_replace("'", "&#39;", $address->getYoutube())."' />
                                    </fieldset>
                                </td>
                            </tr>";
        }
        
        // Column 2
        $this->html .= "</table>";
        if($project->getPhoto() != "" && !$edit){
            $this->html .= "<img src='{$project->getPhoto()}' style='max-height:300px;max-width:50%;vertical-align: top;' />";
        }
        $this->html .= "</td><td valign='top'>";
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
                                <td valign='top' colspan='2'>
                                    <fieldset>
                                        <legend>Programs</legend>
                                        <table width='100%' style='min-width: 400px;'><tr><th width='50%'>Program Name</th><th width='50%'>Url</th></tr></table>{$programPlusMinus->render()}
                                    </fieldset>
                                </td>
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
        if($project->getLogo() != ""){
            $this->html .= "<img src='{$project->getLogo()}' style='max-height:120px;' />";
        }
        $this->html .= "</td></tr>";
    }
    
    function showEditPhoto($project, $visibility){
        global $config;
        if($config->getValue('allowPhotoUpload') || $me->isRoleAtLeast(STAFF)){
            $shortNameField = new TextField("shortName", "University Abbreviation", $this->project->getShortName());
            $shortNameField->attr('size', 27);
            
            $fullNameField = new TextField("fullName", "Department Name", $this->project->getFullName());
            $fullNameField->attr('size', 27);
            
            $memberStatusField = new SelectBox("memberStatus", "Member Status", $this->project->getMemberStatus(), array("Member", "Associate Member", "Non-Member"));
            $facultyListField = new SelectBox("facultyList", "Faculty List", $this->project->getFacultyList(), array("", "Provided", "Missing"));
            
            $this->html .= "<tr>
                                <td align='right' style='white-space: nowrap; width: 1%;'><b>Upload new Photo:</b></td>
                                <td><input type='file' style='width:269px;' name='photo' /></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td><input type='text' name='photo_url' style='width:269px;' placeholder='Enter photo URL here instead of file upload' /></td>
                                <td style='white-space: nowrap;' align='right'><b>University Abbreviation:</b></td><td>{$shortNameField->render()}</td>
                            </tr>
                            <tr>
                                <td align='right' style='white-space: nowrap; width: 1%;'><b>Upload new Logo:</b></td>
                                <td><input type='file' style='width:269px;' name='logo' /></td>
                                <td style='white-space: nowrap;' align='right'><b>Department Name:</b></td><td>{$fullNameField->render()}</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td><input type='text' name='logo_url' style='width:269px;' placeholder='Enter logo URL here instead of file upload' /></td>
                                <td style='white-space: nowrap;' align='right'><b>Member Status:</b></td><td>{$memberStatusField->render()}</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td style='white-space: nowrap;' align='right'><b>Faculty List:</b></td><td>{$facultyListField->render()}</td>
                            </tr>";
        }
    }                                
    
    function handleEdit(){
        global $wgOut, $wgMessage;
        $me = Person::newFromWgUser();
        $error = "";
        if((isset($_FILES['photo']) && $_FILES['photo']['tmp_name'] != "") ||
           (isset($_POST['photo_url']) && $_POST['photo_url'] != "")){
            $fileName = "Photos/{$this->project->getName()}_{$this->project->getId()}.jpg";
            if(isset($_POST['photo_url']) && $_POST['photo_url'] != ""){
                $type = "";
                if(strstr(@$_POST['photo_url'], ".gif") !== false){
                    $type = "image/gif";
                }
                else if(strstr(@$_POST['photo_url'], ".png") !== false){
                    $type = "image/png";
                }
                else if(strstr(@$_POST['photo_url'], ".jpg") !== false ||
                        strstr(@$_POST['photo_url'], ".jpeg") !== false){
                    $type = "image/jpeg";
                }
                // create curl resource
                $ch = curl_init();

                // set url
                curl_setopt($ch, CURLOPT_URL, $_POST['photo_url']);

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
                $type = $_FILES['photo']['type'];
                $size = $_FILES['photo']['size'];
                $tmp = $_FILES['photo']['tmp_name'];
            }
            if($type == "image/jpeg" ||
               $type == "image/pjpeg" ||
               $type == "image/gif" || 
               $type == "image/png"){
                if($size <= 1024*1024*20){
                    //File is OK to upload
                    if(isset($_POST['photo_url']) && $_POST['photo_url'] != ""){
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
        
        if((isset($_FILES['logo']) && $_FILES['logo']['tmp_name'] != "") ||
           (isset($_POST['logo_url']) && $_POST['logo_url'] != "")){
            $fileName = "Photos/{$this->project->getName()}_Logo_{$this->project->getId()}.png";
            if(isset($_POST['logo_url']) && $_POST['logo_url'] != ""){
                $type = "";
                if(strstr(@$_POST['logo_url'], ".gif") !== false){
                    $type = "image/gif";
                }
                else if(strstr(@$_POST['logo_url'], ".png") !== false){
                    $type = "image/png";
                }
                else if(strstr(@$_POST['logo_url'], ".jpg") !== false ||
                        strstr(@$_POST['logo_url'], ".jpeg") !== false){
                    $type = "image/jpeg";
                }
                // create curl resource
                $ch = curl_init();

                // set url
                curl_setopt($ch, CURLOPT_URL, $_POST['logo_url']);

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
                $type = $_FILES['logo']['type'];
                $size = $_FILES['logo']['size'];
                $tmp = $_FILES['logo']['tmp_name'];
            }
            if($type == "image/jpeg" ||
               $type == "image/pjpeg" ||
               $type == "image/gif" || 
               $type == "image/png"){
                if($size <= 1024*1024*20){
                    //File is OK to upload
                    if(isset($_POST['logo_url']) && $_POST['logo_url'] != ""){
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
                        imagealphablending($src_image, false);
                        imagesavealpha($src_image, true);
                        $src_width = imagesx($src_image);
                        $src_height = imagesy($src_image);
                        $dst_width = $src_width;
                        $dst_height = $src_height;
                        $dst_image = imagecreatetruecolor($dst_width, $dst_height);
                        imagealphablending($dst_image, false);
                        
                        imagesavealpha($dst_image, true);
                        imagecopyresampled($dst_image, $src_image, 0, 0, 0, 0, $dst_width, $dst_height, $src_width, $src_height);
                        imagedestroy($src_image);
                        
                        imagepng($dst_image, $fileName);
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
            $_POST['shortName'] = @$_POST['shortName'];
            $_POST['description'] = @$_POST['description'];
            $_POST['website'] = @str_replace("'", "&#39;", $_POST['website']);
            $_POST['dept_website'] = @str_replace("'", "&#39;", $_POST['dept_website']);
            $_POST['email'] = @str_replace("'", "&#39;", $_POST['email']);
            $_POST['use_generic'] = @str_replace("'", "&#39;", $_POST['use_generic']);
            $_POST['admin_email'] = @str_replace("'", "&#39;", $_POST['admin_email']);
            $_POST['admin_use_generic'] = @str_replace("'", "&#39;", $_POST['admin_use_generic']);
            $_POST['tech_email'] = @str_replace("'", "&#39;", $_POST['tech_email']);
            $_POST['tech_use_generic'] = @str_replace("'", "&#39;", $_POST['tech_use_generic']);
            $_POST['long_description'] = $this->project->getLongDescription();
            if($_POST['description'] != $this->project->getDescription() ||
               $_POST['fullName'] != $this->project->getFullName() ||
               $_POST['shortName'] != $this->project->getShortName() ||
               $_POST['memberStatus'] != $this->project->getMemberStatus() ||
               $_POST['facultyList'] != $this->project->getFacultyList() ||
               $_POST['website'] != $this->project->getWebsite() ||
               $_POST['dept_website'] != $this->project->getDeptWebsite() ||
               $_POST['email'] != $this->project->getEmail() ||
               $_POST['use_generic'] != $this->project->getUseGeneric() ||
               $_POST['admin_email'] != $this->project->getAdminEmail() ||
               $_POST['admin_use_generic'] != $this->project->getAdminUseGeneric() ||
               $_POST['tech_email'] != $this->project->getTechEmail() ||
               $_POST['tech_use_generic'] != $this->project->getTechUseGeneric()){
                $error = APIRequest::doAction('ProjectDescription', true);
                if($error != ""){
                    return $error;
                }
                Project::$cache = array();
                $this->project = Project::newFromId($this->project->getId());
                $name = trim(preg_replace("/\(.*\)/", "", $this->project->getName()));
                $wgOut->setPageTitle("{$this->project->getFullName()} ({$name})");
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
            $address->phone = @$_POST['address_phone'];
            $address->fax = @$_POST['address_fax'];
            $address->email = @$_POST['address_email'];
            $address->twitter = @$_POST['address_twitter'];
            $address->facebook = @$_POST['address_facebook'];
            $address->linkedin = @$_POST['address_linkedin'];
            $address->youtube = @$_POST['address_youtube'];
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
        $this->html .= "<td align='right'><b>{$config->getValue("projectThemes")}:</b></td><td>";
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
        $this->html .= "</td>";
    }

    function showPeople(){
        global $wgUser, $wgServer, $wgScriptPath, $config;
        $me = Person::newFromWgUser();
        
        $edit = (isset($_POST['edit']) && $this->canEdit() && !isset($this->visibility['overrideEdit']));
        $project = $this->project;

        if(!$edit){
            $this->html .= "<table width='100%'><tr><td valign='top' width='50%'>";
            $this->showRole(PL);
            $this->showRole(ACHAIR);
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
