<?php

class PersonProfileTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function PersonProfileTab($person, $visibility){
        parent::AbstractEditableTab("Profile");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        global $wgUser;
        $this->person->getLastRole();
        $this->html .= "<table width='100%'>";
        $this->showPhoto($this->person, $this->visibility);
        $this->html .= "</td><td width='40%' valign='top'>";
        $this->showContact($this->person, $this->visibility);
        if($wgUser->isLoggedIn()){
            $this->html .= "</td><td width='20%'>";
            $this->html .= "</td><td valign='top' width='40%'>";
            $this->showEthics($this->person, $this->visibility);
            $this->html .= "</td><td>";
            $this->showCCV($this->person, $this->visibility);
        }
        $this->html .= "</td></tr></table>";
        $this->html .= "<h2>Profile</h2>";
        $this->showProfile($this->person, $this->visibility);
        
        return $this->html;
    }
    
    function generateEditBody(){
        $this->html .= "<table>";
        $this->showEditPhoto($this->person, $this->visibility);
        $this->html .= "</td><td style='padding-right:25px;' valign='top'>";
        $this->showEditContact($this->person, $this->visibility);
        $this->html .= "</table>";
        $this->html .= "<h2>Profile</h2>";
        $this->showEditProfile($this->person, $this->visibility);
        $this->showEditEthics($this->person, $this->visibility);
    }
    
    function canEdit(){
        return ($this->visibility['isMe'] || 
                $this->visibility['isSupervisor']);
    }
    
    function handleEdit(){
        $this->handleContactEdit();
        $_POST['user_name'] = $this->person->getName();
        $_POST['type'] = "public";
        $_POST['profile'] = str_replace("'", "&#39;", $_POST['public_profile']);
        $_POST['profile'] = @addslashes(str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['profile'])));
        APIRequest::doAction('UserProfile', true);
        $_POST['type'] = "private";
        $_POST['profile'] = @addslashes(str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['private_profile'])));
        APIRequest::doAction('UserProfile', true);
        if($this->person->isHQP()){
            APIRequest::doAction('UserEthics', true);
        }
        Person::$cache = array();
        Person::$namesCache = array();
        Person::$idsCache = array();
        $this->person = Person::newFromId($this->person->getId());
    }
    
    function handleContactEdit(){
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
    
    /*
     * Displays the profile for this user
     */
    function showProfile($person, $visibility){
        global $wgUser;
        $this->html .= "<p>".nl2br($person->getProfile($wgUser->isLoggedIn()))."</p>";
    }
    
    function showEditProfile($person, $visibility){
        $this->html .= "<table>
                            <tr>
                                <td align='right' valign='top'><b>GRAND Website:</b></td>
                                <td><textarea style='width:600px; height:150px;' name='public_profile'>{$person->getProfile(false)}</textarea></td>
                            </tr>
                            <tr>
                                <td align='right' valign='top'><b>GRAND Forum:</b></td>
                                <td><textarea style='width:600px; height:150px;' name='private_profile'>{$person->getProfile(true)}</textarea></td>
                            </tr>
                        </table>";
    }

    /*
     * Displays the profile for this user
     */
    function showEthics($person, $visibility){
        global $wgUser, $wgServer, $wgScriptPath;
        
        $ethics = $person->getEthics();
        $completed_tutorial = ($ethics['completed_tutorial'])? "Yes" : "No";
        $date = ($ethics['date'] == '0000-00-00')? "" : $ethics['date'];
        $ethics_str = "<b>Have not completed the TCPS2 tutorial.</b>";
        if($completed_tutorial == "Yes"){
            $ethics_str = "<table><tr>
            <td><img style='vertical-align:bottom;' height='66px' src='$wgServer$wgScriptPath/skins/cavendish/ethical_btns/ethical_button.jpg' /></td>
            <td>&nbsp;<h3>I have completed the TCPS2 tutorial on {$date}.</h3></td>
            <tr></table>";
        }
        else{
            $ethics_str = "<table><tr>
            <td><img style='vertical-align:bottom;' height='66px' src='$wgServer$wgScriptPath/skins/cavendish/ethical_btns/ethical_button_not.jpg' /></td>
            <td>&nbsp;<h3>I have not completed the TCPS2 tutorial.</h3></td>
            <tr></table>";
        }
        if($person->isHQP()){
            $this->html .=<<<EOF
            {$ethics_str}
EOF;
        }
        else if($person->isCNI() || $person->isPNI()){
            $relations = $person->getRelations("Supervises");
            $total_hqp = 0;
            $ethical_hqp = 0;
            foreach($relations as $r){
                $hqp =  $r->getUser2();
                $ethics = $hqp->getEthics();
                if($ethics['completed_tutorial']){
                    $ethical_hqp++;
                }
                $total_hqp++;
            }
            $perc = 0;
            if($total_hqp >0 ){
                $perc = $ethical_hqp/$total_hqp;
            //$perc = floor($perc / 0.25)*0.25;
            }
            $perc = round($perc*100);
            if($ethical_hqp == 0){
                $perc = "";
                $button = "ethical_button_not.jpg";
            }
            else{
                $perc .= "%";
                $button = "ethical_button_ni.jpg";
            }

            $this->html .=<<<EOF
            <style>
            span.supervisor_lbl{
                text-align: center;
                color: #8C529D;
                bottom: 0px;
                left: 7px;
                display: block;
                font-size: 15px;
                font-weight: bold;
            }
            span.percent_lbl{
                text-align: center;
                color: #8C529D;
                top: 3px;
                right: 25px;
                display: block;
                font-size: 12px;
                font-weight: bold;
            }
            </style>
            <table><tr>
            <td style='position:relative; padding:0;'>
                <span class='percent_lbl'>{$perc}</span>
                <img style='vertical-align:bottom;' height='66px' src='$wgServer$wgScriptPath/skins/cavendish/ethical_btns/{$button}' />
                <span class='supervisor_lbl'>Supervisor</span>
            </td>
            <td style='padding-left:15px;'><h3>{$ethical_hqp} of my {$total_hqp} students have completed the TCPS2 Tutorial.</h3></td>
            <tr></table>
EOF;
        }

    }
    
    function showEditEthics($person, $visibility){

        $ethics = $person->getEthics();

        if($ethics['completed_tutorial']){
            $completed_tutorial_y = "checked='checked'";
            $completed_tutorial_n = "";
        }
        else{
            $completed_tutorial_n = "checked='checked'";
            $completed_tutorial_y = "";
        }

        $date = ($ethics['date'] == '0000-00-00')? "" : $ethics['date'];
        if($person->isHQP()){
            $this->html .=<<<EOF
            <script>
            $(function() {
                $( "#datepicker" ).datepicker( { dateFormat: "yy-mm-dd" } );
            });
            </script>
            <table border='0' cellpadding='5' cellspacing='0' width='70%'>
            <tr>
            <td>
            <i>
            <p>All GRAND HQP are required to complete the TCPS2 tutorial <b>Course on Research Ethics (CORE)</b>.  This interactive online tutorial can be completed in approximately two hours and provides an essential orientation to the Tri Council Policy Statement.</p>
            <p>Please note, the current version of the ethics module was released February 2011. If you completed a previous version (i.e. the one that HQP were asked to complete when GRAND started), you are still required to complete the most recent version.</p>
            </i>
            </td>
            </tr>
            </table>
            <table border='0' cellpadding='5' cellspacing='0'>
            <tr><th align='right' style='padding-right:15px;'>I have completed the TCPS2 tutorial:<br />
                <a target='_blank' href="http://grand-nce.ca/resource/tcps2-core">http://grand-nce.ca/resource/tcps2-core</a></th>
                <td valign='top'>
                    Yes <input type='radio' value='1' name='completed_tutorial' {$completed_tutorial_y} />&nbsp;&nbsp;
                    No <input type='radio' value='0' name='completed_tutorial' {$completed_tutorial_n} />
                </td>
            </tr>
            <tr>
                <th align='right' style='padding-right:15px;'>Date: </th>
                <td width='10%'>
                    <input id='datepicker' name='date' type='text' value='{$date}' />
                </td>
            </tr>
            </table>
EOF;
        }
    }
    
    /*
     * Displays the profile for this user
     */
    function showCCV($person, $visibility){
        global $wgUser, $wgServer, $wgScriptPath;
        if($person->isRole(PNI) || $person->isRole(CNI)){
            $this->html .= "<a style='margin-left:35px;' class='button' href='$wgServer$wgScriptPath/index.php/Special:CCVExport?getXML'>Download CCV</a>";
        }
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
            if($person->isRoleDuring(HQP, "0000", "9999") ||
               $person->isRoleDuring(CNI, "0000", "9999") ||
               $person->isRoleDuring(PNI, "0000", "9999") ||
               $person->isRoleDuring(AR, "0000", "9999")){
                $this->html .= "<tr>
                    <td align='right'><b>Nationality:</b></td>
                    <td>
                        {$person->getNationality()}
                    </td>
                </tr>";
            }
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
                                <td><a href='http://twitter.com/{$person->getTwitter()}' target='_blank'>{$person->getTwitter()}</a></td>
                            </tr>";
        }
        $this->html .= "</table>";
    }
    
    function showEditContact($person, $visibility){
        global $wgOut, $wgUser;
        $university = $person->getUniversity();
        $nationality = "";
        if($visibility['isMe'] || $visibility['isSupervisor']){
            if($person->isRoleDuring(HQP, "0000", "9999") ||
               $person->isRoleDuring(CNI, "0000", "9999") ||
               $person->isRoleDuring(PNI, "0000", "9999") ||
               $person->isRoleDuring(AR, "0000", "9999")){
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
            }
            
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
