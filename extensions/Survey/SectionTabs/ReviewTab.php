<?php

class ReviewTab extends AbstractSurveyTab {


    
    function ReviewTab(){
        parent::AbstractSurveyTab("review-submit");
        $this->title = "Review & Submit";
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        
        $this->showForm();
        $this->submitForm();
        return $this->html;
    }


    function showForm(){
        global $wgOut, $wgServer, $wgScriptPath;

        $saved_data = $this->getSavedData();
        //$experience = $this->getSavedExperience();
        
        $additional_comments = "";
        //$grand_comments = "";
        //$network_comments = "";
        
        //$all_values = array("dont_know", "strongly_disagree", "disagree", "undecided", "agree", "strongly_agree");

        extract($saved_data);
       // extract($experience);

        // $grand_comments = urldecode($grand_comments);
        // $network_comments = urldecode($network_comments);
        $additional_comments = urldecode($additional_comments);
        $receive_results0 = ($receive_results == 0)? 'checked="checked"' : ''; 
        $receive_results1 = ($receive_results == 1)? 'checked="checked"' : ''; 

        $this->html .=<<<EOF
            <form id='reviewForm' action='$wgServer$wgScriptPath/index.php/Special:Survey' method='post'>
            <input type='hidden' value='0' name='submitted' id='submitted' />
            <div>
            <p>Would you like to send us any additional comments about the survey? <br />
            <textarea style="height:100px; width:98.5%;" id="additional_comments" name="additional_comments">{$additional_comments}</textarea>
            </p>
            <p>Would you like to receive NAVEL's reports?<br />
            <input type="radio" name="receive_results" value="1" {$receive_results1} /> Yes<br />
            <input type="radio" name="receive_results" value="0" {$receive_results0} /> No<br />
            </p>
            <input type='hidden' name='submit' value='{$this->name}' />
EOF;
        if(!$this->isSubmitted()){
            $this->html .=<<<EOF
            <button onclick="submitSurvey(0);return false;">Save and come back to the survey later</button>
            <button onclick="submitSurvey(1);return false;">Submit final survey</button>
EOF;
        }
        $this->html .=<<<EOF
            </div>
            </form>
EOF;

    }


      function submitForm(){
        global $wgServer, $wgScriptPath, $wgOut;

        $completed = $this->getCompleted();
        $completed = implode(",", $completed);

        $js =<<<EOF
            <script type="text/javascript">
            var completed = new Array({$completed});

            var section_names = new Array("Intro & Consent", "You", "Network", "Relations", "Communication", "Project(s)", "GRAND", "Review & Submit");

            function submitSurvey(submitted){
                window.onbeforeunload = null;
                saveEventInfo();
                $("#submitted").val(submitted);

                if(submitted){
                    //Validate all sections
                    sections = new Array();
                    for (i=0; i<7; i++){
                        if(completed[i] != 1){
                            sections.push(section_names[i]); 
                        }
                    }

                    if(sections.length > 0){
                        error_msg = "Please complete the following sections before continuing: " + sections.join(', ');
                        alert(error_msg);
                        $("#submitted").val(0);
                        $('#reviewForm').submit();
                        return false;
                    }
                    if (confirm("You are submitting your survey. You will not be able to edit your responses any more.")) 
                    { 
                        $('#reviewForm').submit();
                    }
                }
                else{
                    alert("This will not submit your survey. When you finish the survey, please click 'Submit final survey'");
                    $('#reviewForm').submit();
                }
            }
            addEventTracking();
            </script>
EOF;
        //$wgOut->addScript($js);
        $this->html .= $js;
    }
    
    function getSavedData(){
        global $wgUser;
        $my_id = $wgUser->getId();
        
        $saved_data = array();
        
        $sql = "SELECT additional_comments, receive_results FROM survey_results WHERE user_id = {$my_id}";
        $data = DBFunctions::execSQL($sql);
        
        if(isset($data[0])){
            $saved_data = $data[0];
        }

        return $saved_data;
    }
    

    function handleEdit(){
        global $wgUser;
        $my_id = $wgUser->getId();
        $me = Person::newFromId($my_id);

        $additional_comments = ($_POST['additional_comments'])? $_POST['additional_comments'] : "";
        $receive_results = ($_POST['receive_results'])? $_POST['receive_results'] : 0;
        $submitted = ($_POST['submitted'])? $_POST['submitted'] : 0;

        $current_tab = 7; //out of limit
        $completed = $this->getCompleted();
        //if($submitted){
            $completed[7] = 1;
        //}
        $completed = json_encode($completed);

        $sql = "UPDATE survey_results 
                SET additional_comments = '%s',
                receive_results = %d,
                current_tab = %d,
                timestamp = CURRENT_TIMESTAMP,
                submitted = %d,
                completed = '%s'
                WHERE user_id = {$my_id}";
        $sql = sprintf($sql, mysql_real_escape_string($additional_comments), $receive_results, $current_tab, $submitted, $completed);

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