<?php

class GrandExperienceTab extends AbstractSurveyTab {

    var $warnings = false;
    
    function GrandExperienceTab(){
        global $config;
        parent::AbstractSurveyTab("grand-experience");
        $this->title = "{$config->getValue('networkName')}";
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath, $config;
        
        $this->html .=<<<EOF
            <p>
            Please tell us about your experience in {$config->getValue('networkName')} and your views on professional networking. This information is treated as confidential; it will be anonymized and used only in a summary form. The survey area is accessible only to the programmers. 
            </p>
EOF;
        $this->showForm();  
        $this->submitForm(); 
        
        return $this->html;
    }


    function showForm(){
        global $wgOut, $wgServer, $wgScriptPath, $config;

        $experience = $this->getSavedData();
        $grand_comments = "";
        $network_comments = "";

        extract($experience);

        $all_values = array("strongly_disagree", "disagree", "undecided", "agree", "strongly_agree", "dont_know");

        $grand_comments = urldecode($grand_comments);
        $network_comments = urldecode($network_comments);

        //Rendering
        $this->html .=<<<EOF
            <h3>{$config->getValue('networkName')}</h3>
            <div>
            <table width='100%' id='grand_experience' class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all'>
            <thead>
            <tr>
            <th width='40%'>&nbsp;</th>
            <th align="left" width='10%' valign='top'>1. Strongly Disagree</th>
            <th align="left" width='10%' valign='top'>2. Disagree</th>
            <th align="left" width='10%' valign='top'>3. Undecided</th>
            <th align="left" width='10%' valign='top'>4. Agree</th>
            <th align="left" width='10%' valign='top'>5. Strongly Agree</th>
            <th align="left" bgcolor='#CCCCCC' width='10%' valign='top'>Don't Know</th>
            </tr>
            </thead>
            <tbody>
            <tr>
            <th align="left">I am satisfied with the networking opportunities provided by {$config->getValue('networkName')} initiatives.</th>
EOF;
        foreach ($all_values as $v){
            $checked = "";
            if(isset($grand_q1) && $grand_q1 == $v){
                $checked = 'checked="checked"';
            }
            $bgcolor = ($v=="dont_know")? "bgcolor='#CCCCCC' " : "";
            $this->html .=<<<EOF
                <td align="center" $bgcolor><input type="radio" name="grand_q1" value="$v" {$checked} /></td>
EOF;
        }

        $this->html .=<<<EOF
            </tr>
            <tr>
            <th align="left">I am satisfied with how {$config->getValue('networkName')} communication procedures keep me informed about events, news, or opportunities related to {$config->getValue('networkName')}.</th>
EOF;
        foreach ($all_values as $v){
            $checked = "";
            if(isset($grand_q2) && $grand_q2 == $v){
                $checked = 'checked="checked"';
            }
            $bgcolor = ($v=="dont_know")? "bgcolor='#CCCCCC' " : "";
            $this->html .=<<<EOF
                <td align="center" $bgcolor><input type="radio" name="grand_q2" value="$v" {$checked} /></td>
EOF;
        }

        $this->html .=<<<EOF
            </tr>
            <tr>
            <th align="left">I am satisfied with the administrative procedures in {$config->getValue('networkName')}.</th>
EOF;
        foreach ($all_values as $v){
            $checked = "";
            if(isset($grand_q3) && $grand_q3 == $v){
                $checked = 'checked="checked"';
            }
            $bgcolor = ($v=="dont_know")? "bgcolor='#CCCCCC' " : "";
            $this->html .=<<<EOF
                <td align="center" $bgcolor><input type="radio" name="grand_q3" value="$v" {$checked} /></td>
EOF;
        }

        $this->html .=<<<EOF
            </tr>
            <tr>
            <th align="left">I am satisfied with the reporting procedures in {$config->getValue('networkName')}.</th>
EOF;
        foreach ($all_values as $v){
            $checked = "";
            if(isset($grand_q4) && $grand_q4 == $v){
                $checked = 'checked="checked"';
            }
            $bgcolor = ($v=="dont_know")? "bgcolor='#CCCCCC' " : "";
            $this->html .=<<<EOF
                <td align="center" $bgcolor><input type="radio" name="grand_q4" value="$v" {$checked} /></td>
EOF;
        }

        $this->html .=<<<EOF
            </tr>
            <tr>
            <th align="left">I am satisfied with the funding allocation procedures in {$config->getValue('networkName')}.</th>
EOF;
        foreach ($all_values as $v){
            $checked = "";
            if(isset($grand_q5) && $grand_q5 == $v){
                $checked = 'checked="checked"';
            }
            $bgcolor = ($v=="dont_know")? "bgcolor='#CCCCCC' " : "";
            $this->html .=<<<EOF
                <td align="center" $bgcolor><input type="radio" name="grand_q5" value="$v" {$checked} /></td>
EOF;
        }

        $this->html .=<<<EOF
            </tr>
            <tr>
            <th align="left">I am satisfied with the impact my participation in {$config->getValue('networkName')} has on my research agenda.</th>
EOF;
        foreach ($all_values as $v){
            $checked = "";
            if(isset($grand_q6) && $grand_q6 == $v){
                $checked = 'checked="checked"';
            }
            $bgcolor = ($v=="dont_know")? "bgcolor='#CCCCCC' " : "";
            $this->html .=<<<EOF
                <td align="center" $bgcolor><input type="radio" name="grand_q6" value="$v" {$checked} /></td>
EOF;
        }

        $this->html .=<<<EOF
            </tr>
            <tr>
            <th align="left">I am satisfied with the impact my participation in {$config->getValue('networkName')} has on my career.</th>
EOF;
        foreach ($all_values as $v){
            $checked = "";
            if(isset($grand_q7) && $grand_q7 == $v){
                $checked = 'checked="checked"';
            }
            $bgcolor = ($v=="dont_know")? "bgcolor='#CCCCCC' " : "";
            $this->html .=<<<EOF
                <td align="center" $bgcolor><input type="radio" name="grand_q7" value="$v" {$checked} /></td>
EOF;
        }

        $this->html .=<<<EOF
            </tr>
            </tbody>
            </table>
            <br /><strong>Comments:</strong><br />
            <textarea style="height:150px; width:98.5%;" id="grand_comments">{$grand_comments}</textarea>
            </div>

            <h3>VALUE OF NETWORKING</h3>
            <div>
            <table width='100%' id='network_experience' class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all'>
            <thead>
            <tr>
            <th width='40%'>&nbsp;</th>
            <th align="left" valign='top' width='10%'>1. Strongly Disagree</th>
            <th align="left" valign='top' width='10%'>2. Disagree</th>
            <th align="left" valign='top' width='10%'>3. Undecided</th>
            <th align="left" valign='top' width='10%'>4. Agree</th>
            <th align="left" valign='top' width='10%'>5. Strongly Agree</th>
            <th align="left" valign='top' bgcolor='#CCCCCC' width='10%'>Don't Know</th>
            </tr>
            </thead>
            <tbody>
            <tr>
            <th align="left">I believe networking opportunities are important for researchers.</th>
EOF;
        foreach ($all_values as $v){
            $checked = "";
            if(isset($network_q1) && $network_q1 == $v){
                $checked = 'checked="checked"';
            }
            $bgcolor = ($v=="dont_know")? "bgcolor='#CCCCCC' " : "";
            $this->html .=<<<EOF
                <td align="center" $bgcolor><input type="radio" name="network_q1" value="$v" {$checked} /></td>
EOF;
        }

        $this->html .=<<<EOF
            </tr>
            <tr>
            <th align="left">On a project, it is more important to have all members working hard individually than working and sharing knowledge collectively.</th>
EOF;
        foreach ($all_values as $v){
            $checked = "";
            if(isset($network_q2) && $network_q2 == $v){
                $checked = 'checked="checked"';
            }
            $bgcolor = ($v=="dont_know")? "bgcolor='#CCCCCC' " : "";
            $this->html .=<<<EOF
                <td align="center" $bgcolor><input type="radio" name="network_q2" value="$v" {$checked} /></td>
EOF;
        }

        $this->html .=<<<EOF
            </tr>
            <tr>
            <th align="left">Collaborators who can efficiently share their knowledge are strategically important for a project’s success.</th>
EOF;
        foreach ($all_values as $v){
            $checked = "";
            if(isset($network_q3) && $network_q3 == $v){
                $checked = 'checked="checked"';
            }
            $bgcolor = ($v=="dont_know")? "bgcolor='#CCCCCC' " : "";
            $this->html .=<<<EOF
                <td align="center" $bgcolor><input type="radio" name="network_q3" value="$v" {$checked} /></td>
EOF;
        }

        $this->html .=<<<EOF
            </tr>
            <tr>
            <th align="left">In projects, researchers with larger networks have greater opportunities to collaborate with others.</th>
EOF;
        foreach ($all_values as $v){
            $checked = "";
            if(isset($network_q4) && $network_q4 == $v){
                $checked = 'checked="checked"';
            }
            $bgcolor = ($v=="dont_know")? "bgcolor='#CCCCCC' " : "";
            $this->html .=<<<EOF
                <td align="center" $bgcolor><input type="radio" name="network_q4" value="$v" {$checked} /></td>
EOF;
        }

        $this->html .=<<<EOF
            </tr>
            </tbody>
            </table>
            <br /><strong>Comments:</strong><br />
            <textarea style="height:150px; width:98.5%;" id="network_comments">{$network_comments}</textarea>
            </div>
EOF;


        $this->html .=<<<EOF
            <form id='grandExperienceForm' action='$wgServer$wgScriptPath/index.php/Special:Survey' method='post'>
            <input type='hidden' value='' name='experience_str2' id='experience_str2' />
            <input type='hidden' value='' name='warnings' id='grand_warnings_str' />
            <div>
            <input type='hidden' name='submit' value='{$this->name}' />
EOF;

        if(!$this->isSubmitted()){
            $this->html .= '<button onclick="submitGrandExperience();return false;">Save {$config->getValue('networkName')} Experience</button>';
        }
        $this->html .=<<<EOF
            </div>
            </form>
EOF;

    }
    

    function submitForm(){
        global $wgServer, $wgScriptPath, $wgOut, $config;

        if($this->warnings){
            $validate_onload = "validateGrandExperience();";
        }
        else{
            $validate_onload = "";
        }

        $completed = $this->getCompleted();
        $completed = implode(",", $completed);

        $js =<<<EOF
            <script type="text/javascript">
            //$(document).ready(function() {
                {$validate_onload}
            //});

            var completed = new Array({$completed});

            function validateGrandExperience(){
                $("#grand_experience tbody tr").each(function(index) {
                    vval = $(this).find("input[type='radio']:checked").val();
                    nname = $(this).find("input[type='radio']:checked").attr("name");
                    if(vval && nname){
                        $(this).removeAttr("bgcolor");
                    }
                    else{
                        $(this).attr("bgcolor", "yellow");
                    }
                    
                });

                $("#network_experience tbody tr").each(function(index) {
                    vval = $(this).find("input[type='radio']:checked").val();
                    nname = $(this).find("input[type='radio']:checked").attr("name");
                    if(vval && nname){
                        $(this).removeAttr("bgcolor");
                    }
                    else{
                        $(this).attr("bgcolor", "yellow");
                    }
                });
            }

            function submitGrandExperience(){
                window.onbeforeunload = null;
                saveEventInfo();
                exper = '{';
                cnt = 0;
                error_msg = "";
                $("#grand_experience tbody tr").each(function(index) {
                    vval = $(this).find("input[type='radio']:checked").val();
                    nname = $(this).find("input[type='radio']:checked").attr("name");
                    if(vval && nname){
                        if(cnt != 0){
                            exper += ',';
                        } 
                        exper += '"'+nname+'":"'+vval+'"';
                        cnt++;
                        $(this).removeAttr("bgcolor");
                    }
                    else{
                        $(this).attr("bgcolor", "yellow");
                        error_msg = "{$config->getValue('networkName')} Experience: You need to provide answers for all {$config->getValue('networkName')} and Value of Networking questions to successfully complete the section.";
                    }
                    
                });
                grand_comments = escape($('#grand_comments').val());
                exper +=',"grand_comments":"'+grand_comments+'"';

                $("#network_experience tbody tr").each(function(index) {
                    vval = $(this).find("input[type='radio']:checked").val();
                    nname = $(this).find("input[type='radio']:checked").attr("name");
                    if(vval && nname){
                        if(cnt != 0){
                            exper += ',';
                        } 
                        exper += '"'+nname+'":"'+vval+'"';
                        cnt++;
                        $(this).removeAttr("bgcolor");
                    }
                    else{
                        $(this).attr("bgcolor", "yellow");
                        error_msg = "{$config->getValue('networkName')} Experience: You need to provide answers for all {$config->getValue('networkName')} and Value of Networking questions to successfully complete the section.";
                    }
                    
                });
                network_comments = escape($('#network_comments').val());
                exper +=',"network_comments":"'+network_comments+'"';
                

                exper += '}';
                $('#experience_str2').val(exper);

                if(error_msg != ""){
                    $('#grand_warnings_str').val(error_msg);
                    //alert(error_msg);
                    //return false;
                }
                
                $('#grandExperienceForm').submit();
                
            }
            addEventTracking();
            $('th[title], input[title], td[title], tr.tr_qtip[title]').qtip({position: {my: "top left", at: "center center"}});
            </script>
EOF;
        //$wgOut->addScript($js);
        $this->html .= $js;
    }
    
    function getSavedData(){
        global $wgUser;
        $my_id = $wgUser->getId();
        //$me = Person::newFromId($my_id);

        $experience = array();
        
        $sql = "SELECT experience2 FROM survey_results WHERE user_id = {$my_id}";
        $data = DBFunctions::execSQL($sql);
        
        if(isset($data[0])){
            $row = $data[0];
            $json = json_decode($row['experience2'], true);
            $experience = ($json)? $json : array(); 
        }

        return $experience;
    }

    
    function handleEdit(){
        global $wgUser, $wgMessage;
        $my_id = $wgUser->getId();
        $me = Person::newFromId($my_id);

        //First let's see if there are any warnings
        $warnings = $_POST['warnings'];
        if(!empty($warnings)){
            $wgMessage->addWarning($warnings);
            $this->warnings = true;
        }

        $experience = ($_POST['experience_str2'])? $_POST['experience_str2'] : "";

        $current_tab = (empty($warnings))? 4 : 3; 
        $completed = $this->getCompleted();
        $completed[3] = (empty($warnings))? 1 : 0;
        $completed = json_encode($completed);

        $sql = "UPDATE survey_results 
                SET experience2 = '%s',
                current_tab = %d,
                timestamp = CURRENT_TIMESTAMP,
                completed = '%s'
                WHERE user_id = {$my_id}";
        $sql = sprintf($sql, $experience, $current_tab, $completed);

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
