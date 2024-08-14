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
            $this->html .= "Your self-reported EDI information will be kept confidential, and only aggregate data is utilized for central reporting purposes. \"I prefer not to answer\" options are available for each prompt.";
            $this->html .= "<table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom:5px;>";
            $this->html .= "</td><td id='firstLeft' width='60%' valign='top'>";
            $this->html .= "<table>";
            $age = $this->person->getBirthDate();
            if($age != ''){
                $age = @date_diff(date_create($this->person->getBirthDate()), date_create('today'))->y;   
                if($age < 15){
                    $age = "Less than 15";
                }  
                else if($age >= 15 && $age < 25){
                    $age = "15 - 24 years";
                }
                else if($age >= 25 && $age < 35){
                    $age = "25 - 34 years";
                }
                else if($age >= 35 && $age < 45){
                    $age = "35 - 44 years";
                }
                else if($age >= 45 && $age < 55){
                    $age = "45 - 54 years";
                }
                else if($age >= 55 && $age < 65){
                    $age = "55 - 64 years";
                }
                else if($age >= 65 && $age < 75){
                    $age = "65 - 74 years";
                }
                else if($age >= 75){
                    $age = "75 years and over";
                }
            }
            $ethnicity = ($this->person->getMinorityStatus() == "Yes" && 
                          $this->person->getEthnicity() != "") ? " ({$this->person->getEthnicity()})" : "";
            
            $this->html .= "<tr>
                <td align='right'><b>Pronouns:</b></td>
                <td>".str_replace("'", "&#39;", $this->person->getPronouns())."</td>
            </tr>";
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
                <td>".str_replace("'", "&#39;", $this->person->getMinorityStatus())."{$ethnicity}</td>
            </tr>";
            $this->html .= "</table>";   
        
            $this->html .= "</table>";
            
            $this->html .= "<p style='margin-top:2em;'>Future Energy Systems (FES) is focused on leading the energy transition with a vision of optimal, fair, and environmentally responsible energy systems. Achieving this vision will rely on the research of many individuals across many fields, contributing to an interdisciplinary, multidisciplinary, transdisciplinary, intersectional environment where progress and pursuit of knowledge comes first, free from personal, social, or political bias.</p>
            
                            <p>Equity, diversity, and inclusion (EDI) are important aspects of this vision for progress. FES envisions EDI being understood and reframed as a concept; not as efforts separate from our work, but as central components of effective, high-quality research.</p>
                            
                            <p>To learn more about the FES EDI program or visit the EDI pages on our <a href='https://www.futureenergysystems.ca/about/equity-diversity-and-inclusion' target='_blank'>website</a>.</p>";
        }
        
        return $this->html;
    }
    
    function generateEditBody(){
        $this->html .= "Your self-reported EDI information will be kept confidential, and only aggregate data is utilized for central reporting purposes. \"I prefer not to answer\" options are available for each prompt.";
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
        $this->person->pronouns = $_POST['pronouns'];
        $this->person->indigenousStatus = $_POST['indigenousStatus']; 
        $this->person->minorityStatus = $_POST['minorityStatus']; 
        $this->person->disabilityStatus = $_POST['disabilityStatus']; 
        $this->person->ethnicity = $_POST['ethnicity']; 
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
            $pronounsField = new ComboBox("pronouns", "Pronouns", $person->getPronouns(), array("", "she/her", "he/him", "they/them"));
            $genderField = new SelectBox("gender", "Gender", $person->getGender(), array("Female", "Male", "Gender-Fluid", "Non-Binary", "Two-Spirit", "Other (not listed)", "I prefer not to answer"));
            $indigenousField = new SelectBox("indigenousStatus", "Indigenous", $person->getIndigenousStatus(), array("Yes", "No", "I prefer not to answer"));
            $disabilityField = new SelectBox("disabilityStatus", "Disability", $person->getDisabilityStatus(), array("Yes", "No", "I prefer not to answer"));
            $minorityField = new SelectBox("minorityStatus", "Minority", $person->getMinorityStatus(), array("Yes", "No", "I prefer not to answer"));
            $ethnicityField = new SelectBox("ethnicity", "Ethnicity", $person->getEthnicity(), array("South Asian", "Chinese", "Black", "Filipino", "Latin American", "Arab", "Southeast Asian", "West Asian", "Korean", "Japanese", "Other visible minority (not listed)", "Multiple visible minorities", "Prefer not to answer"));
            
            $genderField->emptyIfEmpty = true;
            $indigenousField->emptyIfEmpty = true;
            $disabilityField->emptyIfEmpty = true;
            $minorityField->emptyIfEmpty = true;
            $ethnicityField->emptyIfEmpty = true;
            
            $this->html .= "<h3>Pronouns</h3>
                            Select your pronouns <b>OR</b> type them in manually if not listed.<br />
                            {$pronounsField->render()}
                            
                            <h3>Date of birth</h3>
                            Your exact age will not be displayed, but rather the age group you would fall under, as defined by Statistics Canada<br />
                            <input type='date' name='birthDate' value='".str_replace("'", "&#39;", $person->getBirthDate())."'>
                            
                            <h3>From <a href='https://www23.statcan.gc.ca/imdb/p3Var.pl?Function=DEC&Id=410445' target='_blank'>Statistics Canada</a>, gender refers to the gender that a person internally feels ('gender identity' along the gender spectrum) and/or the gender a person publicly expresses ('gender expression') in their daily life. Which gender group do you belong to?</h3>
                            {$genderField->render()}
                            
                            <h3>From <a href='https://www23.statcan.gc.ca/imdb/p3Var.pl?Function=DECI&Id=1324435' target='_blank'>Statistics Canada</a>, Indigenous group refers to whether a person is First Nations (North American Indian), Métis and/or Inuk (Inuit). A person may be included in more than one of these three specific groups, Status or Non-Status. Do you belong to or have family ancestry in one of these groups?</h3>
                            {$indigenousField->render()}
                            
                            <h3>The Canadian Survey on Disability (CSD) identifies persons with disabilities as those whose everyday activities are limited due to a long-term condition or health-related problem. Do you live with a <a href='https://www150.statcan.gc.ca/t1/tbl1/en/tv.action?pid=1310034501' target='blank'>disability of any type</a>?</h3>
                            {$disabilityField->render()}
                            
                            <h3>From <a href='https://www23.statcan.gc.ca/imdb/p3Var.pl?Function=DEC&Id=45152' target='_blank'>Statistics Canada</a>, the Employment Equity Act defines visible minorities as \"persons, other than Aboriginal peoples, who are non-Caucasian in race or non-white in colour\". Do you belong to or have family ancestry in one of these groups: South Asian, Chinese, Black, Filipino, Latin American, Arab, Southeast Asian, West Asian, Korean, Japanese, Other visible minority (not listed), Multiple visible minorities?</h3>
                            {$minorityField->render()}
                            <span id='ethnicity' style='display:none;'>{$ethnicityField->render()}</span>

                            <p style='margin-top:2em;'>Future Energy Systems (FES) is focused on leading the energy transition with a vision of optimal, fair, and environmentally responsible energy systems. Achieving this vision will rely on the research of many individuals across many fields, contributing to an interdisciplinary, multidisciplinary, transdisciplinary, intersectional environment where progress and pursuit of knowledge comes first, free from personal, social, or political bias.</p>
            
                            <p>Equity, diversity, and inclusion (EDI) are important aspects of this vision for progress. FES envisions EDI being understood and reframed as a concept; not as efforts separate from our work, but as central components of effective, high-quality research.</p>
                            
                            <p>To learn more about the FES EDI program or visit the EDI pages on our <a href='https://www.futureenergysystems.ca/about/equity-diversity-and-inclusion' target='_blank'>website</a>.</p>
                            
                            <script type='text/javascript'>
                                $('[name=minorityStatus]').change(function(){
                                    var value = $('[name=minorityStatus]').val();
                                    if(value == 'Yes'){
                                        $('#ethnicity').show();
                                    }
                                    else{
                                        $('#ethnicity').hide();
                                    }
                                }).change();
                            </script>";
        }
    }
    
}
?>
