<?php

class ProjectExperienceTab extends AbstractSurveyTab {

    var $warnings = false;
    
    function ProjectExperienceTab(){
        parent::AbstractSurveyTab("project-experience");
        $this->title = "Project(s)";
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        $this->html .=<<<EOF
            <p>
            Please tell us about your experience in the project(s) you participate in. This information is treated as confidential; it will be anonymized and used only in a summary form. The survey area is accessible only to the programmers. 
            </p>
EOF;
        $this->showForm();
        $this->submitForm();
        return $this->html;
    }


    function showForm(){
        global $wgOut, $wgServer, $wgScriptPath, $wgUser;

        $experience = $this->getSavedData();

        $my_id = $wgUser->getId();
        $me = Person::newFromId($my_id);
        $projects = $me->getProjects();

        extract($experience);

        $all_values = array("strongly_disagree", "disagree", "undecided", "agree", "strongly_agree", "dont_know");

        foreach ($projects as $project){
            $project_name = $project->getName();

            $project_q1 = $project_name."_project_q1";
            $project_q2 = $project_name."_project_q2";
            $project_q3 = $project_name."_project_q3";
            $project_comments = $project_name."_project_comments";

            $$project_comments = (isset($$project_comments))? urldecode($$project_comments) : "";


            $this->html .=<<<EOF
            <h3>PROJECT {$project_name}</h3>
            <div>
            <table width='100%' id='{$project_name}' class='wikitable project_experience' cellspacing='1' cellpadding='2' frame='box' rules='all'>
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
            <th align="left">I am satisfied with how well my project team coordinates who does what.</th>
EOF;

            foreach ($all_values as $v){
                $checked = "";
                if(isset($$project_q1) && $$project_q1 == $v){
                    //echo $$project_q1;
                    $checked = 'checked="checked"';
                }
                //echo "CHECKED: ".$checked."<br>";
                $bgcolor = ($v=="dont_know")? "bgcolor='#CCCCCC' " : "";
                $this->html .=<<<EOF
                    <td align="center" $bgcolor><input type="radio" name="{$project_name}_project_q1" value="$v" {$checked} /></td>
EOF;
            }

            $this->html .=<<<EOF
            </tr>
            <tr>
            <th align="left">I am satisfied with how well my project team works together as a group.</th>
EOF;
            foreach ($all_values as $v){
                $checked = "";
                if(isset($$project_q2) && $$project_q2 == $v){
                    $checked = 'checked="checked"';
                }
                $bgcolor = ($v=="dont_know")? "bgcolor='#CCCCCC' " : "";
                $this->html .=<<<EOF
                    <td align="center" $bgcolor><input type="radio" name="{$project_name}_project_q2" value="$v" {$checked} /></td>
EOF;
            }

            $this->html .=<<<EOF
            </tr>
            <tr>
            <th align="left">I am satisfied with the efficiency of my project team in completing projects.</th>
EOF;
            foreach ($all_values as $v){
                $checked = "";
                if(isset($$project_q3) && $$project_q3 == $v){
                    $checked = 'checked="checked"';
                }
                $bgcolor = ($v=="dont_know")? "bgcolor='#CCCCCC' " : "";
                $this->html .=<<<EOF
                    <td align="center" $bgcolor><input type="radio" name="{$project_name}_project_q3" value="$v" {$checked} /></td>
EOF;
            }

            $this->html .=<<<EOF
            </tr>
            </tbody>
            </table>
            <br /><strong>Comments:</strong><br />
            <textarea style="height:150px; width:98.5%;" class="project_comments" name="{$project_name}_project_comments">{$$project_comments}</textarea>
            </div>
EOF;
        }


$this->html .=<<<EOF
            <form id='experienceForm' action='$wgServer$wgScriptPath/index.php/Special:Survey' method='post'>
            <input style='display:none;' type='submit' name='submit' value='{$this->name}' />
            <input type='hidden' value='' name='experience_str' id='experience_str' />
            <input type='hidden' value='' name='warnings' id='proj_warnings_str' />
EOF;

        if(!$this->isSubmitted()){
            $this->html .= '<button onclick="submitExperience(); return false;">Save Project Experience</button>';
        }

        $this->html .= "</form>";

    }

    

    function submitForm(){
        global $wgServer, $wgScriptPath, $wgOut;

        if($this->warnings){
            $validate_onload = "validateProjectExperience();";
        }
        else{
            $validate_onload = "";
        }

        $js =<<<EOF
            <script type="text/javascript">
            //$(document).ready(function() {
                {$validate_onload}
            //});

            function validateProjectExperience(){
                $(".project_experience tbody tr").each(function(index) {
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

            function submitExperience(){
                window.onbeforeunload = null;
                saveEventInfo();
                exper = '{';
                cnt = 0;
                error_msg = "";
                $(".project_experience tbody tr").each(function(index) {
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
                        error_msg = "Project Experience: You need to provide answers for all project questions to successfully complete the section.";
                    }
                    
                });

                if(error_msg != ""){
                    $('#proj_warnings_str').val(error_msg);
                    //alert(error_msg);
                    //return false;
                }
                
                $(".project_comments").each(function(index) {
                    vval = escape($(this).val());
                    nname = $(this).attr("name");
                    if(vval && nname){
                        if(cnt != 0){
                            exper += ',';
                        } 
                        exper += '"'+nname+'":"'+vval+'"';
                        cnt++;
                    }
                    
                });
               

                exper += '}';
                $('#experience_str').val(exper);
                document.getElementById('experienceForm').submit.click();
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
        
        $sql = "SELECT experience FROM survey_results WHERE user_id = {$my_id}";
        $data = DBFunctions::execSQL($sql);
        
        if(isset($data[0])){
            $row = $data[0];
            $json = json_decode($row['experience'], true);
            $experience = ($json)? $json : array(); //explode(', ', $row['grand_connections']);
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

        $experience = ($_POST['experience_str'])? $_POST['experience_str'] : "";
        $current_tab = (empty($warnings))? 3 : 2;
        $completed = $this->getCompleted();
        $completed[2] = (empty($warnings))? 1 : 0;
        $completed = json_encode($completed);

        $sql = "UPDATE survey_results 
                SET experience = '%s',
                current_tab = %d,
                completed = '%s',
                timestamp = CURRENT_TIMESTAMP
                WHERE user_id = {$my_id}";
        $sql = sprintf($sql, $experience, $current_tab, $completed);
        $result = DBFunctions::execSQL($sql, true);
        
        //echo $result;
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
