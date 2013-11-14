<?php

class PersonContactTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function PersonContactTab($person, $visibility){
        parent::AbstractEditableTab("Contact");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        $this->person->getLastRole();
        $this->html .= "<table>";
            $this->showPhoto($this->person, $this->visibility);
        $this->html .= "</td><td style='padding-right:25px;' valign='top'>";
            $this->showContact($this->person, $this->visibility);
        $this->html .= "</table>";
        return $this->html;
    }
    
    function handleEdit(){
        global $wgImpersonating;
        $error = "";
        if(!$wgImpersonating && isset($_FILES['photo']) && $_FILES['photo']['tmp_name'] != ""){
            $type = $_FILES['photo']['type'];
            $size = $_FILES['photo']['size'];
            $tmp = $_FILES['photo']['tmp_name'];
            if($type == "image/jpeg" ||
               $type == "image/pjpeg" ||
               $type == "image/gif" || 
               $type == "image/png"){
                if($size <= 1024*1024*5){
                    //File is OK to upload
                    $fileName = "Photos/".str_replace(".", "_", $this->person->getName()).".jpg";
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
                        $dst_width = 50;
                        $dst_height = 66;
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
                    $error .= "The file you uploaded is too large.  It should be smaller than 5MB.<br />";
                }
            }
            else{
                //File is not an ok filetype
                $error .= "The file you uploaded is not of the right type.  It should be either gif, png or jpeg.<br />";
            }
        }
        if($error == ""){
            // Insert the new data into the DB
            $_POST['user_name'] = $this->person->getName();
            $_POST['twitter'] = @addslashes($_POST['twitter']);
            $_POST['nationality'] = @addslashes($_POST['nationality']);
            $_POST['email'] = @addslashes($_POST['email']);
            $_POST['university'] = @$_POST['university'];
            $_POST['department'] = @$_POST['department'];
            $_POST['title'] = @$_POST['title'];
            $_POST['gender'] = @addslashes($_POST['gender']);
            if($this->visibility['isChampion']){
                $_POST['partner'] = @$_POST['org'];
                $_POST['title'] = @$_POST['title'];
                $_POST['department'] = @$_POST['department'];
                $api = new UserPartnerAPI();
                $api->doAction(true);
            }
            else{
                $api = new UserUniversityAPI();
                $api->processParams(array());
                $api->doAction(true);
            }
            $api = new UserTwitterAccountAPI();
            $api->doAction(true);
            $api = new UserNationalityAPI();
            $api->doAction(true);
            $api = new UserEmailAPI();
            $api->doAction(true);
            $api = new UserGenderAPI();
            $api->doAction(true);
        }
        
        //Reset the cache to use the changed data
        unset(Person::$cache[$this->person->id]);
        unset(Person::$cache[$this->person->getName()]);
        Person::$idsCache = array();
        Person::$namesCache = array();
        $this->person = Person::newFromId($this->person->id);
        return $error;
    }
    
    function generateEditBody(){
        $this->html .= "<table>";
            $this->showEditPhoto($this->person, $this->visibility);
        $this->html .= "</td><td style='padding-right:25px;' valign='top'>";
            $this->showEditContact($this->person, $this->visibility);
        $this->html .= "</table>";
    }
    
    function canEdit(){
        return ($this->visibility['isMe'] || 
                $this->visibility['isSupervisor']);
    }
    
    /*
     * Displays the photo for this person
     */
    function showPhoto($person, $visibility){
        $this->html .= "<tr><td style='padding-right:25px;' valign='top'>";
        if($person->getPhoto() != ""){
            $this->html .= "<img src='{$person->getPhoto()}' alt='{$person->getName()}' />";
        }
        $this->html .= "<div id=\"special_links\"></div>";
    }
    
    function showEditPhoto($person, $visibility){
        $this->html .= "<tr><td style='padding-right:25px;' valign='top' colspan='2'>";
        if($person->getPhoto() != ""){
            $this->html .= "<img src='{$person->getPhoto()}' alt='{$person->getName()}' />";
        }
        $this->html .= "<div id=\"special_links\"></div>";
        $this->html .= "</td></tr>";
        $this->html .= "<tr><td style='padding-right:25px;' valign='top'><table>
                            <tr>
                                <td align='right'><b>Upload new Photo:</b></td>
                                <td><input type='file' name='photo' /></td>
                            </tr>
                            <tr>
                                <td></td><td><small><li>The image will be scaled to 50x66.</li>
                                                    <li>Max file size is 5MB</li>
                                                    <li>File type must be <i>gif</i>, <i>png</i> or <i>jpeg</i></li></small></td>
                            </tr>
                            <tr>
                                <td align='right'><b>Twitter Account:</b></td>
                                <td><input type='text' name='twitter' value='".str_replace("'", "&#39;", $person->getTwitter())."' /></td>
                            </tr>
                        </table></td>";
    }
    
   /*
    * Displays the contact information for this person
    */
    function showContact($person, $visibility){
        global $wgOut, $wgUser, $wgTitle, $wgServer, $wgScriptPath;
        $this->html .= "<table>";
        if($wgUser->isLoggedIn()){
            $this->html .= "<tr>
                                <td align='right'><b>Email:</b></td>
                                <td><a href='mailto:{$person->getEmail()}'>{$person->getEmail()}</a></td>
                            </tr>";
        }
        if($visibility['isMe'] || $visibility['isSupervisor']){
            $this->html .= "<tr>
                <td align='right'><b>Nationality:</b></td>
                <td>
                    {$person->getNationality()}
                </td>
            </tr>";
            if($person->getGender() != ""){
                $this->html .= "<tr>
                    <td align='right'><b>Gender:</b></td>
                    <td>
                        {$person->getGender()}
                    </td>
                </tr>";
            }
        }
        if($person->isRole(CHAMP)){
            $org = $person->getPartnerName();
            $title = $person->getPartnerTitle();
            $dept = $person->getPartnerDepartment();
            if($title != ""){
                $this->html .= "<tr>
                                    <td align='right'><b>Title:</b></td>
                                    <td>{$title}</td>
                                </tr>";
            }
            if($org != ""){
                $this->html .= "<tr>
                                    <td align='right'><b>Organization:</b></td>
                                    <td>{$org}</td>
                                </tr>";
            }
            if($dept != ""){
                $this->html .= "<tr>
                                    <td align='right'><b>Department:</b></td>
                                    <td>{$dept}</td>
                                </tr>";
            }
        }
        else{
            $university = $person->getUniversity();
            if(isset($university['university'])){
                $this->html .= "<tr>
                                    <td align='right'><b>Title:</b></td>
                                    <td>{$university['position']}</td>
                                </tr>
                                <tr>
                                    <td align='right'><b>University:</b></td>
                                    <td>{$university['university']}</td>
                                </tr>
                                <tr>
                                    <td align='right'><b>Department:</b></td>
                                    <td>{$university['department']}</td>
                                </tr>";
            }
        }
        if($person->getTwitter() != ""){
            $this->html .= "<tr>
                                <td align='right'><b>Twitter:</b></td>
                                <td><a href='$wgServer$wgScriptPath/index.php/{$wgTitle->getNsText()}:{$wgTitle->getText()}?action=getTwitterFeed'>{$person->getTwitter()}</a></td>
                            </tr>";
        }
        $this->html .= "</table>";
    }
    
    function showEditContact($person, $visibility){
        global $wgOut, $wgUser;
        $university = $person->getUniversity();
        $nationality = "";
        if($visibility['isMe'] || $visibility['isSupervisor']){
            $canSelected = ($person->getNationality() == "Canadian") ? "selected='selected'" : "";
            $immSelected = ($person->getNationality() == "Landed Immigrant" || $person->getNationality() == "Foreign") ? "selected='selected'" : "";
            $visaSelected = ($person->getNationality() == "Visa Holder") ? "selected='selected'" : "";
            $nationality = "<tr>
                <td align='right'><b>Nationality:</b></td>
                <td>
                    <select name='nationality'>
                        <option value='Canadian' $canSelected>Canadian</option>
                        <option value='Landed Immigrant' $immSelected>Landed Immigrant</option>
                        <option value='Visa Holder' $visaSelected>Visa Holder</option>
                    </select>
                </td>
            </tr>";
            
            $blankSelected = ($person->getGender() == "") ? "selected='selected'" : "";
            $maleSelected = ($person->getGender() == "Male") ? "selected='selected'" : "";
            $femaleSelected = ($person->getGender() == "Female") ? "selected='selected'" : "";
            $gender = "<tr>
                <td align='right'><b>Gender:</b></td>
                <td>
                    <select name='gender'>
                        <option value='' $blankSelected>----</option>
                        <option value='Male' $maleSelected>Male</option>
                        <option value='Female' $femaleSelected>Female</option>
                    </select>
                </td>
            </tr>";
            $partnerHTML = "";
            if($visibility['isChampion']){
                $partners = array();
                foreach(Partner::getAllPartners() as $partner){
                    $partners[] = $partner->getOrganization();
                }
                $wgOut->addScript("<script type='text/javascript'>
                var partners = [\"".implode("\",\n\"", $partners)."\"];
                
                $(document).ready(function(){
                    $('#partner').autocomplete({
                        source: partners
                    });
                });</script>");
                $partner = str_replace("'", "&#39;", $person->getPartnerName());
                $partnerHTML = "<tr>
                                 <td align='right'><b>Partner:</b></td>
                                 <td>
                                    <input type='text' value='{$partner}' id='partner' name='partner' />
                                 </td>
                                 </tr>
                                 ";
            }
        }
        $this->html .= "<table>
                            <tr>
                                <td align='right'><b>Email:</b></td>
                                <td><input size='30' type='text' name='email' value='".str_replace("'", "&#39;", $person->getEmail())."' /></td>
                            </tr>
                            {$nationality}
                            {$gender}";
                            
        if($person->isRole(CHAMP)){
            $titles = array_merge(array(""), Person::getAllPartnerTitles());
            $organizations = array_merge(array(""), Person::getAllPartnerNames());
            $depts = array_merge(array(""), Person::getAllPartnerDepartments());
            $titleCombo = new ComboBox('title', "Title", $person->getPartnerTitle(), $titles);
            $orgCombo = new ComboBox('org', "Organization", $person->getPartnerName(), $organizations);
            $deptCombo = new ComboBox('department', "Department", $person->getPartnerDepartment(), $depts);
            $this->html .= "<tr>
                                <td align='right'><b>Title:</b></td>
                                <td>{$titleCombo->render()}
                                </td>
                            </tr>
                            <tr>
                                <td align='right'><b>Organization:</b></td>
                                <td>{$orgCombo->render()}
                                </td>
                            </tr>
                            <tr>
                                <td align='right'><b>Department:</b></td>
                                <td>{$deptCombo->render()}
                                </td>
                            </tr>";
        }
        else{
            $universities = Person::getAllUniversities();
            $positions = Person::getAllPositions();
            $myPosition = "";
            foreach($positions as $key => $position){
                if($university['position'] == $position){
                    $myPosition = $key;
                }
            }
            $departments = Person::getAllDepartments();
            $positionCombo = new ComboBox('title', "Title", $myPosition, $positions);
            $departmentCombo = new ComboBox('department', "Department", $university['department'], $departments);
            $this->html .= "<tr>
                                <td align='right'><b>Title:</b></td>
                                <td>{$positionCombo->render()}
                                </td>
                            </tr>
                            <tr>
                                <td align='right'><b>University:</b></td>
                                <td><select name='university'>";
            foreach($universities as $uni){
                $selected = "";
                if($uni == $university['university']){
                    $selected = " selected";
                }
                $this->html .= "<option$selected>{$uni}</option>";
            }
            $this->html .= "</select></td></tr>
                            <tr>
                                <td align='right'><b>Department:</b></td>
                                <td>{$departmentCombo->render()}</td>
                            </tr>";
        }
        $this->html .= "</table>";
    }
}
?>
