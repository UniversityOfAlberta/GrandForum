<?php

class PersonDemographicsTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function PersonDemographicsTab($person, $visibility){
        parent::AbstractEditableTab("EDI");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        global $wgUser;
        $me = Person::newFromWgUser();       
        if($me->isAllowedToEditDemographics($this->person)){
            $this->html .= "<table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom:5px;>";
            $this->html .= "</td><td id='firstLeft' width='60%' valign='top'>";
            $this->html .= "<table>";
            $age = $this->person->getBirthDate();
            if($age != ''){
                $age = @date_diff(date_create($this->person->getBirthDate()), date_create('today'))->y;            
            }
            $this->html .= "<tr>
                 <td align='right'><b>Age:</b></td>
                 <td>".str_replace("'", "&#39;",$age)."</td>
            </tr>";
            $this->html .= "<tr>
                <td align='right'><b>Gender:</b></td>
                <td>".str_replace("'", "&#39;", $this->person->getGender())."</td>
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
        }
        
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
        return ($me->isAllowedToEditDemographics($this->person));
    }
    
    function handleEdit(){
        $this->person->gender = $_POST['gender'];
        $this->person->birthDate = $_POST['birthDate']; 
        $this->person->indigenousStatus = $_POST['indigenousStatus']; 
        $this->person->minorityStatus = $_POST['minorityStatus']; 
        $this->person->disabilityStatus = $_POST['disabilityStatus']; 
        $this->person->update();

        Person::$rolesCache = array();
        Person::$cache = array();
        Person::$namesCache = array();
        Person::$idsCache = array();
        
        $this->person = Person::newFromId($this->person->getId());
    }
    
    function showEditDemographics($person, $visibility){
        global $wgOut, $wgUser, $config, $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if($me->isAllowedToEditDemographics($person)){
            $birthDate = "<tr>
                <td align='right'><b>Date of birth:</b></td>
                <td><input type='date' name='birthDate' value='".str_replace("'", "&#39;", $person->getBirthDate())."'></td>
            </tr>";
            $blankSelected = ($person->getGender() == "") ? "selected='selected'" : "";
            $maleSelected = ($person->getGender() == "Male") ? "selected='selected'" : "";
            $femaleSelected = ($person->getGender() == "Female") ? "selected='selected'" : "";
            $genderFluidSelected = ($person->getGender() == "Gender-fluid") ? "selected='selected'" : "";
            $nonBinarySelected = ($person->getGender() == "Non-binary") ? "selected='selected'" : "";
            $twoSpiritSelected = ($person->getGender() == "Two-spirit") ? "selected='selected'" : "";
            $declinedSelected = ($person->getGender() == "Not disclosed") ? "selected='selected'" : "";
            $gender = "<tr>
                <td align='right'><b>Gender:</b></td>
                <td>
                    <select name='gender'>
                        <option value='' $blankSelected>---</option>
                        <option value='Male' $maleSelected>Male</option>
                        <option value='Female' $femaleSelected>Female</option>
                        <option value='Gender-fluid' $genderFluidSelected>Gender-fluid</option>
                        <option value='Non-binary' $nonBinarySelected>Non-binary</option>
                        <option value='Two-spirit' $twoSpiritSelected>Two-spirit</option>
                        <option value='Not disclosed' $declinedSelected>I prefer not to answer</option>
                    </select>
                </td>
            </tr>";
            $indigenousYes = ($person->getIndigenousStatus() == "Yes") ? "selected='selected'" : "";
            $indigenousNo = ($person->getIndigenousStatus() == "No") ? "selected='selected'" : "";
            $indigenousDeclined = ($person->getIndigenousStatus() == "Not disclosed") ? "selected='selected'" : "";
            $indigenous = "<tr>
                <td align='right'><b>Do you identify as Indigenous?:</b></td>
                <td>
                    <select name='indigenousStatus'>
                        <option value=''>---</option>
                        <option value='Yes' $indigenousYes>Yes</option>
                        <option value='No' $indigenousNo>No</option>
                        <option value='Not disclosed' $indigenousDeclined>I prefer not to answer</option>
                    </select>
                </td>
            </tr>";
            $disabilityYes = ($person->getDisabilityStatus() == "Yes") ? "selected='selected'" : "";
            $disabilityNo = ($person->getDisabilityStatus() == "No") ? "selected='selected'" : "";
            $disabilityDeclined = ($person->getDisabilityStatus() == "Not disclosed") ? "selected='selected'" : "";
            $disability = "<tr>
                <td align='right'><b>Are you a person with a disability?:</b></td>
                <td>
                    <select name='disabilityStatus'>
                        <option value=''>---</option>
                        <option value='Yes' $disabilityYes>Yes</option>
                        <option value='No' $disabilityNo>No</option>
                        <option value='Not disclosed' $disabilityDeclined>I prefer not to answer</option>
                    </select>
                </td>
            </tr>";
            $minorityYes = ($person->getMinorityStatus() == "Yes") ? "selected='selected'" : "";
            $minorityNo = ($person->getMinorityStatus() == "No") ? "selected='selected'" : "";
            $minorityDeclined = ($person->getMinorityStatus() == "Not disclosed") ? "selected='selected'" : "";
            $minority = "<tr>
                <td align='right'><b>Do you identify as a member<br> of a visible minority in Canada:</b></td>
                <td>
                    <select name='minorityStatus'>
                        <option value=''>---</option>
                        <option value='Yes' $minorityYes>Yes</option>
                        <option value='No' $minorityNo>No</option>
                        <option value='Not disclosed' $minorityDeclined>I prefer not to answer</option>
                    </select>
                </td>
            </tr>";
        }
        $this->html .= "<table>
                          {$birthDate}
                          {$gender}
                          {$indigenous}
                          {$disability}
                          {$minority}
                        </table>";
    }
    
}
?>
