<?php

class ConsentTab extends AbstractSurveyTab {

   
    function ConsentTab(){
        global $wgOut;
        parent::AbstractSurveyTab("intro-consent");
        $this->title = "Intro & Consent";
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        
        $this->showConsent();
        $this->showForm();
        return $this->html;
    }

    function showConsent(){
        global $config;
        $this->html =<<<EOF
<div>
<h3>Introductory Letter</h3>
<p>Part of {$config->getValue('networkName')}'s mandate is to create a community of professionals engaged in collaborative research, innovation, and knowledge transfer. Understanding the relationships among the members of this community is crucial for the success of {$config->getValue('networkName')}.</p>
<p>Many of you are already familiar with the NAVEL (Network Assessment and Validation for Effective Leadership), an internal project of {$config->getValue('networkName')}: we trace the formation and evolution of the collaborative relationships among {$config->getValue('networkName')}'s participants. Our 2010 baseline survey showed how the researchers in {$config->getValue('networkName')} are connected to each other before the network was fully operational. This second survey will enable us to capture the changes in the relations among {$config->getValue('networkName')} researchers and will give new participants a chance to get themselves on the {$config->getValue('networkName')} map.</p>
<p>
We are confident that our second survey is easier to complete than the 2010 baseline survey. The time for completing the survey varies significantly depending on how connected a person is; we estimate the survey will take from 15 min to 35 – 45 min for the most connected members. You can complete the survey in MULTIPLE SESSIONS: you can stop at any time and come back to it later.
</p>
<p>We know that there are constraints on your time but it is only with your help - and with the input of as many of {$config->getValue('networkName')}'s participants as possible - that we can learn how the network functions. For those who complete the survey, we will offer the analyses we have produced and a brief individual report. Thank you in advance for your time and effort.</p>

<p style='text-align:right;'>Barry Wellman, Project Leader, NAVEL project</p>
</div>
<div>
<h3>NAVEL Survey Consent Form</h3>
<p>
I have read the Introductory Letter from Professor Barry Wellman and I understand the purpose
of the NAVEL study as well as its potential benefits and/or risks to me. Further, I understand that (a) I may
raise any questions or concerns with the NAVEL team before proceeding with the survey (contact
information below) and that by providing this consent I am confirming that my questions have
been answered; (b) I can download a copy of the Consent Form for my records; and (c) I will
receive feedback about my personal network in {$config->getValue('networkName')}. Based on these, I voluntarily consent to
participate in the survey.
</p>
<p>
I understand that my participation is voluntary. I may withdraw from the survey at any time or
may decline to answer any questions. I may elect or decline to import some of the routine data I
have previously entered on the Forum (University affiliation, project membership, co-authors). I
can withdraw by choosing the "Disagree" button or, if I have already submitted some information,
by contacting the research team (contact information below) and notifying them that I withdraw
from the research. My information will not be included in the data. My withdrawal or my decline
to complete the survey will have no consequences.
</p>
<p>
I have been assured that I will not be identified in any specific way. My name will be replaced by
an identification number, the computer files storing the data will be password protected and kept
in a secure location, and hard copy documents will be kept in locked storage. All documents will be destroyed upon completion of this work. The
list containing the identification numbers and names will be kept separately from the dataset on a
password protected computer. The survey area is accessible only to the programmers. Only members of the research team, working directly with the
dataset and bound by a confidentiality agreement, will have access to the data.
</p>
<p>
I will remain anonymous and my identity will be protected in all reports and publications based on
this research. I have been promised access to these reports and publications.
</p>
<p>
With these safeguards in mind, I agree that the findings from the survey can be used in reports,
publications or presentations.
</p>
<p>I understand that I may contact the research team at <a href="mailto:navel@surveys.grand-nce.ca">navel@surveys.grand-nce.ca</a> or contact directly the offices
of research ethics of the University of Toronto at <a href="mailto:ethics.review@utoronto.ca">ethics.review@utoronto.ca</a> or of the University of Alberta at
<a href="mailto:reoffice@ualberta.ca">reoffice@ualberta.ca</a> for further clarification of my rights as a respondent.
</p>
</div>
EOF;
    }

    function showForm(){
        global $wgOut, $wgServer, $wgScriptPath;


        $saved_data = $this->getSavedData();

        if($saved_data['consent'] == 1 ){
            $agree = "checked='checked'";
            $disagree = "disabled='disabled'";
        }else{
            $disagree = "checked='checked'";
            $agree = "";
        }

        $js =<<<EOF
            <script type="text/javascript">
                //$(document).ready(function(){
                    $("#consentForm").submit(function(){
                        window.onbeforeunload = null;
                        saveEventInfo();
                        if($("input[name='consent']:checked").val() == "Agree"){
                            //$('#consentForm').submit();
                            $('#survey').tabs("option", "disabled", false);
                            return true;
                        }else{
                            alert("You must agree to proceed with the survey!");
                            return true;
                        }
                    });
                    addEventTracking();
                //});
            </script>
EOF;
        //$this->html .= $js;
        //$wgOut->addScript($js);

        $this->html .=<<<EOF
            <form id='consentForm' action='$wgServer$wgScriptPath/index.php/Special:Survey' method='post'>
                <input type='hidden' name='submit' value='{$this->name}'>
                <input type="radio" name="consent" value="Agree" {$agree} /> Agree<br />
                <input type="radio" name="consent" value="Disagree" {$disagree} /> Disagree<br />
                <br /> <br />
                <a href="/data/2nd survey Consent Form.pdf" target="_blank">[Download Consent Form PDF]</a><br /><br />
EOF;
        if(!$this->isSubmitted()){
            $this->html .= '<input type="submit" id="submit_consent" name="vnas" value="Save Consent" />';
        }

        $this->html .= "</form>";

        $this->html .= $js;
    }

    function getSavedData(){
        global $wgUser;
        $my_id = $wgUser->getId();
        $me = Person::newFromId($my_id);
       
        $data_array = array('consent'=>"");
        
        $sql = "SELECT * FROM survey_results WHERE user_id = {$my_id}";
        $data = DBFunctions::execSQL($sql);
        
        if(isset($data[0])){
            $row = $data[0];

            $data_array['consent'] = $row['consent'];
        }

        return $data_array;
    }

    function handleEdit(){
        global $wgUser;
        $my_id = $wgUser->getId();
        //$me = Person::newFromId($my_id);

        $consent = (isset($_POST['consent']) && $_POST['consent'] == "Agree")? 1 : 0;
        
        $completed = $this->getCompleted();
        $completed[0] = ($consent)? 1 : 0;
        $completed = json_encode($completed);

        $current_tab = 1;
        $sql = "INSERT INTO survey_results(user_id, consent, current_tab, completed, timestamp) 
                VALUES ({$my_id}, {$consent}, {$current_tab}, '{$completed}', CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE consent={$consent}, current_tab={$current_tab}, completed='{$completed}', timestamp = CURRENT_TIMESTAMP";
        
        $result = DBFunctions::execSQL($sql, true);

    }
    
    // Generates the HTML for the editing page
    function generateEditBody(){}
    
    // Returns true if the user has permissions to edit the page, false if otherwise
    function canEdit(){
        return true;
    }

    function showEditButton(){
    }
}    
    
?>
