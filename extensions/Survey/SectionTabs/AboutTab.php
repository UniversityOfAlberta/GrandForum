<?php

class AboutTab extends AbstractSurveyTab {

    var $warnings = false;
    
    function AboutTab(){
        parent::AbstractSurveyTab("about-you");
        $this->title = "You";
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        
        $this->showForm();
        
        return $this->html;
    }

    function showReview(){
        global $wgOut, $wgServer, $wgScriptPath;
        $saved_data = $this->getSavedData();
        
        $discipline_str = "";
        $dlist1 = self::getDisciplineList(0);
        $dlist2 = array();
        $dlist3 = array();
        foreach ($dlist1 as $key=>$arr){
            if($arr['id'] == $saved_data['d_level1']){
                $discipline_str .= $arr['name'];
                $dlist2 = $arr['children'];
                break;
            }
        }
        $discipline_str .= " | ";
        foreach ($dlist2 as $key=>$arr){
            if($arr['id'] == $saved_data['d_level2']){
                $discipline_str .= $arr['name'];
                $dlist3 = $arr['children'];
                break;
            }
        }
        $discipline_str .= " | ";
        foreach ($dlist3 as $key=>$arr){
            if($arr['id'] == $saved_data['d_level3']){
                $discipline_str .= $arr['name'];
                break;
            }
        }

        $use_survey = ($saved_data['use_survey_data'])? "Yes" : "No";
        $use_forum = ($saved_data['use_forum_data'])? "Yes" : "No";

        $this->html .=<<<EOF

            <table cellpadding='2'>
            <tr>
            <td><strong>Family Name:</strong></td><td>{$saved_data['lname']}</td>
            </tr>
            <tr>
            <td><strong>First Name:</strong></td><td>{$saved_data['fname']}</td>
            </tr>
            <tr>
            <td><strong>Disciplines:</strong></td><td>$discipline_str</td>
            </tr>
            <tr>
            <td><strong>Use Forum Data:</strong></td><td>$use_forum</td>
            </tr>
            <tr>
            <td><strong>Use 2010 Survey Data:</strong></td><td>$use_survey</td>
            </tr>
            </table>
EOF;

    }


    function showForm(){
        global $wgOut, $wgServer, $wgScriptPath;

        $disciplines = self::getDisciplineList();
        
        $saved_data = $this->getSavedData();   
        if(isset($saved_data['use_survey_data']) && $saved_data['use_survey_data'] == 1){
            $use_survey_data1 = 'checked="checked"';
            $use_survey_data0 = '';
        }else{
            $use_survey_data0 = 'checked="checked"';
            $use_survey_data1 = '';
        }
        if(isset($saved_data['use_forum_data']) && $saved_data['use_forum_data'] == 1){
            $use_forum_data1 = 'checked="checked"';
            $use_forum_data0 = '';
        }else{
            $use_forum_data0 = 'checked="checked"';
            $use_forum_data1 = '';
        }
        

        $discipline_options = "<option value=''>Select Discipline</option>";
        $specify_discipline = "";
        $specify_discipline_display = "display:none;";
        foreach($disciplines as $lbl => $darray){
            $discipline_options .= "<optgroup label='{$lbl}'>";
            
            foreach($darray as $d){
                $selected = "";
                if($saved_data["d_level2"] == $d){
                    $selected = "selected='selected'";
                }
                $discipline_options .= "<option value='{$d}' {$selected}>{$d}</option>";

            }
            $discipline_options .= "</optgroup>";
        }

        $others_array = array("Computer Science, other: please specify", "Engineering, other: please specify", "Information, Communication and Management, other: please specify", "Media and Arts, other: please specify", "Humanities, other: please specify", "Social Sciences, other: please specify", "Other professions, please specify");
        
        if( in_array($saved_data["d_level2"], $others_array)){
            $specify_discipline = $saved_data["specify_discipline"];
            $specify_discipline_display = "display:block;";
        }

        if($this->warnings){
            $validate_onload = "validateYou();";
        }
        else{
            $validate_onload = "";
        }

        $this->html .=<<<EOF
            <style type='text/css'>
            .label{
                font-weight:bold;
            }
            </style>
            <script type="text/javascript">
              
                
            </script>
            <form id='aboutYouForm' class='' action='$wgServer$wgScriptPath/index.php/Special:Survey' method='post'>
                <table cellpadding='2'>
                <tr><td class='label'>Family Name:</td><td><input type="text" name="last_name" value="{$saved_data['lname']}" /> </td></tr>
                <tr><td class='label'>First Name:</td><td><input type="text" name="first_name" value="{$saved_data['fname']}"/> </td></tr>
                <tr>
                <td class='label'>Primary funding agency<span style="color:red;">*</span>:</td>
                <td>
                <select name="d_level1a" id="level1a" onchange="updateFunding('level1a', 'level1b', agencies);" ></select>
                </td>
                </tr>
                <tr>
                <td class='label'>Secondary funding agency, if any:</td>
                <td>
                <select name="d_level1b" id="level1b" onchange="updateFunding('level1b', 'level1a', agencies);" ></select>
                </td>
                </tr>
                <tr>
                <td valign="top" class='label'>Primary Discipline<span style="color:red;">*</span>:</td>
                <td valign="top">
                <select name="d_level2" id="d_level2" onchange="isOther();">{$discipline_options}</select>
                <div id="specify_discipline" style="$specify_discipline_display">
                <input type="text" id="discipline_other" name="discipline_other" size="35" value='{$specify_discipline}' />
                </div>
                </td>
                </tr>
                <tr>
                <td class='label'>Area of Specialization:</td>
                <td>
                <input type="text" name="d_level3" id="d_level3" value="{$saved_data['d_level3']}"  /> (E.g. Virtual Play, eHealth, Culture and Technology)
                </td>
                </tr>
                </table>
EOF;

        if($this->surveyConnectionsExist()){
            $this->html .=<<<EOF
                <p>You have completed the 2010 survey; may we import your data? <br />
                <input type="radio" name="use_survey_data" value="1" $use_survey_data1 /> Yes 
                &nbsp;&nbsp;
                <input type="radio" name="use_survey_data" value="0" $use_survey_data0 /> No<br />
                </p>
EOF;
        }

        $this->html .=<<<EOF
                <input type='hidden' id='you_warnings_str' name='warnings' value='' />
                <input type="hidden" name="use_forum_data" value="1"  />
                <input type="hidden" name="submit" value="{$this->name}"  />
EOF;
        if(!$this->isSubmitted()){
            $this->html .= '<button onclick="submitAboutYou();return false;">Save You</button>';
        }
        $this->html .= "</form>";
    
        
        //$disciplines = self::getDisciplineList();
        $agencies = self::getFundingAgencies();

        
        //$disciplines = json_encode($disciplines);
        $agencies = json_encode($agencies);
   
        $js = <<<EOF
            <script type='text/javascript'>

            var disciplines = "";
            var agencies = "";
            var agency1 = "{$saved_data['d_level1a']}";
            var agency2 = "{$saved_data['d_level1b']}";

            function submitAboutYou(){
                    window.onbeforeunload = null;
                    saveEventInfo();
                    var error_msg = validateYou();
                    if(error_msg != ""){
                        $('#you_warnings_str').val(error_msg);
                    }
                    $('#aboutYouForm').submit();
                }

            //$(document).ready(function() {
                //$( "#section-1-accordion" ).accordion();

                //disciplines = $.parseJSON('{$disciplines}');
                agencies = $.parseJSON('{$agencies}');

                populateDisciplines("level1a", agencies, agency1);
                //agencies[0] = "None";
                populateDisciplines("level1b", agencies, agency2);
            //});


            function isOther(){
                var others_array = new Array("Computer Science, other: please specify", "Engineering, other: please specify", "Information, Communication and Management, other: please specify", "Media and Arts, other: please specify", "Humanities, other: please specify", "Social Sciences, other: please specify", "Other professions, please specify");

                var level2 = $('select#d_level2').val();
                if($.inArray(level2, others_array) > -1){
                    $("#specify_discipline").show();
                }
                else{
                    $("#specify_discipline").hide();
                }
            }
            function populateDisciplines(select_id, json, selected){
                var options = (select_id == "level1b")? '<option value="">None</option>' : '<option value="">Select</option>';
                for (var i = 0; i < json.length; i++) {
                    var sel = "";
                    if(selected == json[i]){
                        sel = "selected='selected'";
                    }
                    options += '<option value="' + json[i] + '" ' + sel + '>' + json[i] + '</option>';
                }
                $('select#'+select_id).html(options);
            }

            function updateFunding(select1, select2, json){
                var val1 =$('select#'+select1).val();
                var val2 =$('select#'+select2).val();
                var options = (select2 == "level1b")? '<option value="">None</option>' : '<option value="">Select</option>';
                for (var i = 0; i < json.length; i++) {
                    if(json[i] != val1){
                        options += '<option value="' + json[i] + '">' + json[i] + '</option>';
                    }
                }
                $('select#'+select2).html(options);
                $('select#'+select2).val(val2);
            }

            function validateYou(){
                primary_agency = $("#level1a").val();
                primary_discipline = $("#d_level2").val();

                var error_msg = "";
                var fields = new Array();
                if(primary_agency == ""){
                    $("#level1a").closest("tr").attr("bgcolor", "yellow");
                    fields.push("'Primary funding agency'");
                }
                if(primary_discipline == ""){
                    $("#d_level2").closest("tr").attr("bgcolor", "yellow");
                    fields.push("'Primary discipline'");
                }
                else if($("#specify_discipline").is(":visible")){
                    //alert(tmp.replace(/\s/g,""));
                    tmp = $("#discipline_other").val().replace(/\s/g,"");
                    if( tmp == "" ){
                        $("#d_level2").closest("tr").attr("bgcolor", "yellow");
                        fields.push("'Primary discipline'");
                    }
                }

                if(fields.length > 0){
                    error_msg = "You: Please provide "+ fields.join() +" to complete this section.";
                }
                return error_msg;
            }
            {$validate_onload}
            addEventTracking();
            </script>
EOF;
        //$wgOut->addScript($js);
        $this->html .= $js;

    }

    function getSavedData(){
        global $wgUser;
        $my_id = $wgUser->getId();
        $me = Person::newFromId($my_id);
        $my_name = explode('.', $me->getName());
        $my_fname = $my_name[0];
        $my_lname = implode(' ', array_slice($my_name, 1));

        $data_array = array('fname'=>"", 'lname'=>"", "d_level1a"=>"", "d_level1b"=>"", "d_level2"=>"", "specify_discipline"=>"", "d_level3"=>"", "use_forum_data"=>"", "use_survey_data"=>"");
        
        $sql = "SELECT * FROM survey_results WHERE user_id = {$my_id}";
        $data = DBFunctions::execSQL($sql);
        
        if(isset($data[0])){
            $row = $data[0];
            $data_array['fname'] = ($row['first_name'])? $row['first_name'] : $my_fname;
            $data_array['lname'] = ($row['last_name'])? $row['last_name'] : $my_lname;

            $discipline = ($row['discipline'])? $row['discipline'] : "";
            $disciplines = json_decode($discipline, true);
           
            $data_array['d_level1a'] = (isset($disciplines["d_level1a"]))? $disciplines["d_level1a"] : "";
            $data_array['d_level1b'] = (isset($disciplines["d_level1b"]))? $disciplines["d_level1b"] : "";
            $data_array['d_level2']  = (isset($disciplines["d_level2"]))? $disciplines["d_level2"] : "";
            if(preg_match('/please specify/', $data_array['d_level2'])){
                $strarr = preg_split('/\|/', $data_array['d_level2']);
                $data_array['d_level2'] = $strarr[0];
                $data_array['specify_discipline'] = $strarr[1];
            }

            $data_array['d_level3']  = (isset($disciplines["d_level3"]))? $disciplines["d_level3"] : "";

            $data_array['use_forum_data'] = $row['use_forum_data'];
            $data_array['use_survey_data'] = $row['use_survey_data'];


        }
        return $data_array;
    }


    static function getDisciplineList(){
        
        $disciplines = array(
            "Computer science"=>
            array(
            "Computer Graphics",
            "Computer-Human Communication/CHI/HCI",
            "Image Processing and Computer Vision",
            "Information Storage and Retrieval",
            "Interfaces and Presentation",
            "Numerical Analysis",
            "Operating Systems",
            "Processor Architectures",
            "Simulation and Modelling",
            "Software Engineering",
            "Computer Science, other: please specify"
            ),

            "Engineering"=>
            array(
            "Computer Engineering / Electrical Engineering",
            "Industrial Engineering",
            "Information Technology Engineering",
            "Mechanical Engineering",
            "Systems Design Engineering",
            "Engineering, other: please specify"
            ),

            "Information, Communication and Management"=>
            array(
            "Communication Studies/Science",
            "Communication Technologies",
            "Information and Media Studies",
            "Information Management",
            "Information Technology",
            "Information, Library and Archival Studies/Science",
            "Management Sciences",
            "Information, Communication and Management, other: please specify"
            ),

            "Media / Arts / Design"=>
            array(
            "Arts and Technology",
            "Design and Computational Arts",
            "Film",
            "Industrial Design",
            "Theatre",
            "Media and Arts, other: please specify"
            ),

            "Humanities"=>
            array(
            "English",
            "History",
            "Philosophy",
            "Humanities, other: please specify"
            ),

            "Social Sciences"=>
            array(
            "Anthropology",
            "Education",
            "Geography",
            "Psychology",
            "Sociology",
            "Social Sciences, other: please specify"
            ),

            "Other Professions"=>
            array(
            "Architecture",
            "Exercise Science, Physical and Health Education",
            "Forestry",
            "Gerontology",
            "Journalism",
            "Law",
            "Medicine",
            "Other professions, please specify"
            )
        );
        

        /*$disciplines = array(
            "Artificial Intelligence",
            "Anthropology",
            "Architecture/Landscape Architecture",
            "Archival and Information Studies / Library and Information Studies / Library Archival and Information Studies",
            "Arts and Technology",
            "Communication studies",
            "Communication Technologies",
            "Computer Engineering",
            "Computer Graphics",
            "Computer-Communication Networks",
            "Database Management",
            "Design and Computational Arts",
            "Education / Early childhood education",
            "Electrical and Computer Engineering",
            "Engineering / Science and Engineering",
            "English",
            "Exercise Science, Physical and Health Education",
            "Film",
            "Forestry",
            "Geography",
            "Gerontology",
            "History / History and Classics",
            "Image Processing and Computer Vision",
            "Information and Media Studies / Information and Media",
            "Information Interfaces and Presentation",
            "Information Management",
            "Information Storage and Retrieval",
            "Information Systems Applications",
            "Information Technology and Engineering / Software and Information Technology Engineering",
            "Information Technology Management",
            "Information Technology",
            "Journalism",
            "Law",
            "Liberal Arts",
            "Management Sciences",
            "Mechanical and Industrial Engineering",
            "Medicine",
            "Numerical Analysis",
            "Operating Systems",
            "Philosophy",
            "Processor Architectures",
            "Psychology",
            "Simulation and Modelling",
            "Sociology",
            "Software Engineering",
            "Systems Design Engineering / Systems and Computer Engineering",
            "Theatre",
            "Other, please specify:"
        );*/
        
        return $disciplines;
    }

    static function getFundingAgencies(){

        $agencies = array("NSERC", "SSHRC", "CIHR");
        
        return $agencies;
    }

    private static function surveyConnectionsExist(){
        global $wgUser;
        $me = Person::newFromId($wgUser->getId());
        
        $my_name = preg_split('/\./', $me->getName(), 2);
        $my_fname = (isset($my_name[0]))? $my_name[0] : "";
        $my_lname = (isset($my_name[1]))? $my_name[1] : ""; 


        $token = "";
        //Get the token
        $sql = "SELECT token FROM limesurvey.lime_tokens_6 WHERE firstname='{$my_fname}' AND lastname='{$my_lname}'";
        $data = DBFunctions::execSQL($sql);
        if(isset($data[0])){
            $token  = $data[0]['token'];
        }

        if(empty($token)){
            return 0;

        }
        
        $people = array();
        $sql = "SELECT * FROM limesurvey.lime_survey_6 WHERE token='{$token}'";
        $data = DBFunctions::execSQL($sql);


        $questions = array(
"6X5X176a1","6X5X176a2","6X5X176a3","6X5X176a4","6X5X176a5","6X5X177b1","6X5X177b2","6X5X177b3","6X5X177b4","6X5X177b5","6X5X177b6","6X5X177b7",
"6X5X177b8","6X5X177b9","6X5X178c1","6X5X178c2","6X5X178c3","6X5X179d1","6X5X180e1","6X5X180e2","6X5X180e3","6X5X180e4","6X5X181f1","6X5X182g1",
"6X5X182g2","6X5X182g3","6X5X182g4","6X5X182g5","6X5X183h1","6X5X183h2","6X5X183h3","6X5X184i1","6X5X184i2","6X5X184i3","6X5X184i4","6X5X184i5",
"6X5X185j1","6X5X185j2","6X5X185j3","6X5X185j4","6X5X185j5","6X5X185j6","6X5X185j7","6X5X185j8","6X5X185j9","6X5X185j10","6X5X185j11","6X5X185j12",
"6X5X185j13","6X5X185j14","6X5X185j15","6X5X185j16","6X5X186k1","6X5X186k2","6X5X186k3","6X5X186k4","6X5X186k5","6X5X186k6","6X5X186k7","6X5X186k8",
"6X5X186k9","6X5X186k10","6X5X186k11","6X5X186k12","6X5X186k13","6X5X186k14","6X5X186k15","6X5X187l1","6X5X187l2","6X5X187l3","6X5X187l4","6X5X187l5",
"6X5X187l6","6X5X187l7","6X5X187l8","6X5X187l9","6X5X187l10","6X5X187l11","6X5X187l12","6X5X187l13","6X5X187l14","6X5X187l15","6X5X187l16","6X5X187l17",
"6X5X187l18","6X5X187l19","6X5X188m1","6X5X188m2","6X5X188m3","6X5X188m4","6X5X188m5","6X5X189n1","6X5X189n2","6X5X190o1","6X5X190o2","6X5X191p1",
"6X5X191p2","6X5X191p3","6X5X192q1","6X5X192q2","6X5X192q3","6X5X193r1","6X5X193r2","6X5X193r3","6X5X194s1","6X5X194s2","6X5X194s3","6X5X194s4",
"6X5X194s5","6X5X194s6","6X5X194s7","6X5X194s8","6X5X194s9","6X5X194s10","6X5X194s11","6X5X194s12","6X5X194s13","6X5X194s14","6X5X194s15","6X5X195t1",
"6X5X195t2","6X5X195t3","6X5X195t4","6X5X195t5","6X5X196u1","6X5X196u2","6X5X196u3","6X5X196u4","6X5X196u5","6X5X196u6","6X5X196u7","6X5X197v1",
"6X5X197v2","6X5X197v3","6X5X197v4","6X5X197v5","6X5X197v6","6X5X197v7","6X5X197v8","6X5X197v9","6X5X198w1","6X5X198w2","6X5X198w3","6X5X198w4"
        );

        if(isset($data[0])){
            $sql = "SELECT question from limesurvey.lime_questions WHERE parent_qid = %d AND title= '%s'";
            foreach($questions as $q){
                if($data[0][$q] == 'Y'){
                    $parent_qid = substr($q, 4, 3);
                    $title = substr($q, 7);
                    $data2 = DBFunctions::execSQL(sprintf($sql, $parent_qid, $title));
                    if( isset($data2[0]) && $data2[0]['question'] != $my_lname.", ".$my_fname ){
                        $name = preg_split('/,/', $data2[0]['question'], 2);
                        $name[0] = preg_replace('/[^A-Za-z0-9\-_]/', '', $name[0]);
                        $name[1] = preg_replace('/[^A-Za-z0-9\-_]/', '', $name[1]);
                        $name = $name[0].", ".$name[1];
                        $people[] = $name;
                    }
                }
            }
        }

        //print_r($people);

        if(empty($people)){
            return 0;
        }
        else {
            return 1;
        }

    }

    function handleEdit(){
        global $wgUser, $wgMessage;
        $my_id = $wgUser->getId();
        //$me = Person::newFromId($my_id);

        $warnings = $_POST['warnings'];
        if(!empty($warnings)){
            $wgMessage->addWarning($warnings);
            $this->warnings = true;
        }

        $fname = DBFunctions::escape($_POST['first_name']);
        $lname = DBFunctions::escape($_POST['last_name']);
        $d_level1a = $_POST['d_level1a'];
        $d_level1b = $_POST['d_level1b'];
        $d_level2 = $_POST['d_level2'];
        $discipline_other = $_POST['discipline_other'];
        $others_array = array("Computer Science, other: please specify", "Engineering, other: please specify", "Information, Communication and Management, other: please specify", "Media and Arts, other: please specify", "Humanities, other: please specify", "Social Sciences, other: please specify", "Other professions, please specify");
        if( isset($d_level2) && in_array($d_level2, $others_array) && !empty($discipline_other) ){
            $d_level2 .= "|".$discipline_other;
            $d_level2 = htmlentities($d_level2, ENT_QUOTES);
        }
        //$d_level2 = 
        $d_level3 = $_POST['d_level3'];
        $use_forum_data = $_POST['use_forum_data'];
        $use_survey_data = (isset($_POST['use_survey_data']))? $_POST['use_survey_data'] : 0;

        $disciplines = array("d_level1a"=>$d_level1a, "d_level1b"=>$d_level1b, "d_level2"=>$d_level2, "d_level3"=>$d_level3);
        $disciplines = json_encode($disciplines);

        $completed = $this->getCompleted();
        $completed[1] = (empty($warnings))? 1 : 0;
        $completed = json_encode($completed);

        $current_tab = (empty($warnings))? 2 : 1;    
        $sql = "UPDATE survey_results 
                SET
                first_name = '%s',
                last_name = '%s',
                discipline = '%s',
                use_forum_data = %d,
                use_survey_data = %d,
                current_tab = %d,
                completed = '%s',
                timestamp = CURRENT_TIMESTAMP
                WHERE user_id = {$my_id}";
        $sql = sprintf($sql, $fname, $lname, DBFunctions::escape($disciplines), $use_forum_data, $use_survey_data, $current_tab, $completed);
        $result = DBFunctions::execSQL($sql, true);

        //echo $result;
    }
    

}    
    
?>
