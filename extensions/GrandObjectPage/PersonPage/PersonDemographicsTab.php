<?php

class PersonDemographicsTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function PersonDemographicsTab($person, $visibility){
        parent::AbstractEditableTab("Demographics");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        global $wgUser;
        $this->person->getLastRole();
       // $this->html .= "<p>Yes this is working now edit in what you need ^_^</p>";
        $this->html .= "<table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom:5px;>";
        $this->html .= "</td><td id='firstLeft' width='60%' valign='top'>";
        $this->html .= "<table>";
        $this->html .= "<tr>
                <td align='right'><b>Age:</b></td>
                <td>".str_replace("'", "&#39;", $this->person->getAge())."</td>
            </tr>";
        $this->html .= "<tr>
                <td align='right'><b>Indigenous:</b></td>
                <td>".str_replace("'", "&#39;", $this->person->getIndigenousStatus())."</td>
            </tr>";  
        $this->html .= "<tr>
                <td align='right'><b>Disability:</b></td>
                <td>".str_replace("'", "&#39;", $this->person->getDisabilityStatus())."</td>
            </tr>";          
        $this->html .= "<tr>
                <td align='right'><b>Minority:</b></td>
                <td>".str_replace("'", "&#39;", $this->person->getMinorityStatus())."</td>
            </tr>";
        $this->html .= "</table>";       
        
        
        
        $this->html .= "</table>";
        
        return $this->html;
    }
    
    function generateEditBody(){
        $this->html .= "<table>";
        $this->html .= "<td style='padding-right:25px;' valign='top'>";
        $this->showEditDemographics($this->person, $this->visibility);
        $this->html .= "</td></tr></table>";
    }
    
    function canEdit(){
        $me = Person::newFromWgUser();
        return (($this->visibility['isMe'] || 
                 $this->visibility['isSupervisor']) &&
                $me->isAllowedToEdit($this->person));
    }
    
    function handleEdit(){
        $this->handleContactEdit();
        $_POST['user_name'] = $this->person->getName();
        
        $this->person->publicProfile = $_POST['public_profile'];
        $this->person->privateProfile = $_POST['private_profile'];
        $this->person->update();

        Person::$rolesCache = array();
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
                        $dst_width = 300;
                        $dst_height = ($src_height*300)/$src_width;
                        if($dst_height > 396){
                            $dst_height = 396;
                            $dst_width = ($src_width*396)/$src_height;
                        }
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
            $_POST['phone'] = @$_POST['phone'];
            $_POST['email'] = @$_POST['email'];

            $api = new UserPhoneAPI();
            $api->doAction(true);
            
            $this->person->firstName = @$_POST['first_name'];
            $this->person->lastName = @$_POST['last_name'];
            $this->person->realname = @"{$_POST['first_name']} {$_POST['last_name']}";
            $this->person->gender = @$_POST['gender'];
            $this->person->twitter = @$_POST['twitter'];
            $this->person->website = @$_POST['website'];
            $this->person->linkedin = @$_POST['linkedin'];
            $this->person->nationality = @$_POST['nationality'];
            $this->person->stakeholder = @$_POST['stakeholder'];
            $this->person->update();

            $api = new UserEmailAPI();
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
    
    /**
     * Displays the profile for this user
     */
    function showProfile($person, $visibility){
        global $wgUser;
        $this->html .= "<div style='text-align:justify;'>".$person->getProfile($wgUser->isLoggedIn())."</div>";
    }
    
    function showEditDemographics($person, $visibility){
        global $wgOut, $wgUser, $config, $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if($visibility['isMe'] || $visibility['isSupervisor']){
            $age = "<tr>
                <td align='right'><b>Age:</b></td>
                <td><input type='text' name='age' value='".str_replace("'", "&#39;", $person->getAge())."'></td>
            </tr>";
            
            $indigenousYes = ($person->getIndigenousStatus() == "Yes") ? "selected='selected'" : "";
            $indigenousNo = ($person->getIndigenousStatus() == "No") ? "selected='selected'" : "";
            $indigenousDeclined = ($person->getIndigenousStatus() == "Declined") ? "selected='selected'" : "";
            $indigenous = "<tr>
                <td align='right'><b>Do you identify as Indigenous?:</b></td>
                <td>
                    <select name='indigenousStatus'>
                        <option value=''>---</option>
                        <option value='Yes' $indigenousYes>Yes</option>
                        <option value='No' $indigenousNo>No</option>
                        <option value='Declined' $indigenousDeclined>I prefer not to answer</option>
                    </select>
                </td>
            </tr>";
            $disabilityYes = ($person->getDisabilityStatus() == "Yes") ? "selected='selected'" : "";
            $disabilityNo = ($person->getDisabilityStatus() == "No") ? "selected='selected'" : "";
            $disabilityDeclined = ($person->getDisabilityStatus() == "Declined") ? "selected='selected'" : "";
            $disability = "<tr>
                <td align='right'><b>Are you a person with a disability?:</b></td>
                <td>
                    <select name='disability'>
                        <option value=''>---</option>
                        <option value='Yes' $disabilityYes>Yes</option>
                        <option value='No' $disabilityNo>No</option>
                        <option value='declined' $disabilityDeclined>I prefer not to answer</option>
                    </select>
                </td>
            </tr>";
            $minorityYes = ($person->getMinorityStatus() == "Yes") ? "selected='selected'" : "";
            $minorityNo = ($person->getMinorityStatus() == "No") ? "selected='selected'" : "";
            $minorityDeclined = ($person->getMinorityStatus() == "Declined") ? "selected='selected'" : "";
            $minority = "<tr>
                <td align='right'><b>Do you identify as a member<br> of a visible minority in Canada:</b></td>
                <td>
                    <select name='minority'>
                        <option value=''>---</option>
                        <option value='Yes' $minorityYes>Yes</option>
                        <option value='No' $minorityNo>No</option>
                        <option value='declined' $minorityDeclined>I prefer not to answer</option>
                    </select>
                </td>
            </tr>";
        }
        $this->html .= "<table>
                          {$age}
                          {$indigenous}
                          {$disability}
                          {$minority}";
    }
    
}
?>
