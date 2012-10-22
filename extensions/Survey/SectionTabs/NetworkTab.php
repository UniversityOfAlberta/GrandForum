<?php

class NetworkTab extends AbstractSurveyTab {

    var $warnings = false;
    var $allpeps = array();

    function NetworkTab(){
        parent::AbstractSurveyTab("grand-network");
        $this->title = "Network";
        $this->allpeps = array_merge(Person::getAllPeople(CNI), Person::getAllPeople(PNI));
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        
        $this->showIntro();
        $this->showForm();

        $this->addCustomJS();
        
        return $this->html;
    }

    function showIntro(){
        $this->html =<<<EOF
<style type="text/css">
#survey_connections, #forum_connections {
    font-size:11px;
}
</style>
<div>
<p>Please tell us who you know among GRAND participants by checking off their names. You "know" someone if you have talked to that person at conferences or meetings, discussed professional or personal matters, or worked together on projects or publications.</p>
<p>You can select and confirm your network members from several sources:
<ul>
<li>from the list of connections you indicated in the 2010 survey (if you completed the survey);</li>
<li>from the information you have entered on the forum pages;</li>
<li>from the list of all GRAND members.</li>
</ul>
</p>
<p>You can search the list of GRAND participants. You can also sort all the lists by first name, last name, project, or university.</p>
<p>When you confirm a name from any of these sources, it is pooled in Your 2012 Network table at the bottom of the page and is no longer available for selection. You can edit the list of names before finalizing Your 2012 Network.</p>
</div>
EOF;

    }

    function showForm(){
        global $wgOut, $wgServer, $wgScriptPath;


        $this->html .=<<<EOF
            <form class='' action='$wgServer$wgScriptPath/index.php/Special:Survey' method='post'>
EOF;
    
        $data_consent = $this->getDataUsageConsent();

        $past_conn = array();
        if(isset($data_consent['use_survey_data']) && $data_consent['use_survey_data'] == 1){
            $past_conn = $this->getSurveyConnections();
        }
        
        if(isset($data_consent['use_forum_data']) && $data_consent['use_forum_data'] == 1){
            $this->getForumConnections($past_conn);
        }

        $this->newConnectionForm();

        $this->currentNetwork();

        $this->submitForm(); 
    }

    function addCustomJS(){
        global $wgOut, $wgStylePath; 

        if($this->warnings){
            $validate_onload = "validateNetwork();";
        }
        else{
            $validate_onload = "";
        }

        $js =<<<EOF
        <script type="text/javascript">
        {$validate_onload}
        
        $('th[title]').qtip({position: {my: "top left", at: "center right"}});
        $('th.farright[title]').qtip({position: {my: "bottom right", at: "center right"}});

        jQuery.fn.outerHTML = function(s) {
            return (s)
            ? this.before(s).remove()
            : jQuery("<p>").append(this.eq(0).clone()).html();
        }

        $("#current_connections").tablesorter({ 
            sortList: [[0,0], [1,0], [2,0], [3,0]],
            textExtraction: { 
                //0: function(node, table, cellIndex){ return $(node).attr("data-sort-value"); }
                //2: function(node, table, cellIndex){ return $(node).attr("data-sort-value") + $(node).text(); }
            }
            //sortForce: [[0,1]]
        });

        $("#survey_connections").tablesorter({ 
            sortList: [[1,0]]
        });
        $("#forum_connections").tablesorter({ 
            sortList: [[1,0]]
        });
        $("#new_connections").tablesorter({ 
            sortList: [[1,0]]
        });

        //EVENT HANDLERS
        $("#lnk_survey_connections").click(function(e) {
            e.preventDefault();
            if($("#spn_survey_connections").text()=="-"){
                $("#spn_survey_connections").text("+");
            }else{
                $("#spn_survey_connections").text("-");
            }
            $("#div_survey_connections").toggle();
        });
        $("#lnk_forum_connections").click(function(e) {
            e.preventDefault();
            if($("#spn_forum_connections").text()=="-"){
                $("#spn_forum_connections").text("+");
            }else{
                $("#spn_forum_connections").text("-");
            }
            $("#div_forum_connections").toggle();
        });
        $("#lnk_new_connections").click(function(e) {
            e.preventDefault();
            if($("#spn_new_connections").text()=="-"){
                $("#spn_new_connections").text("+");
            }else{
                $("#spn_new_connections").text("-");
            }
            $("#div_new_connections").toggle();
        });

        $("#confirm_survey_connections").click(function(e) {
            e.preventDefault();
            $("#survey_connections tbody tr input:checked").each(
                function(index) {
                    //row = $(this).parents("tr").html();
                    lname_cell = $(this).parents("tr").find("td.lname").outerHTML();
                    fname_cell = $(this).parents("tr").find("td.fname").outerHTML();
                    proj_name_cell = $(this).parents("tr").find("td.proj_names").outerHTML();
                    position_cell = $(this).parents("tr").find("td.position").outerHTML();
                    rname = $(this).parents("tr").attr("name");
                    rclass = $(this).parents("tr").attr("class");
                    cclass =rclass.replace(/\./g,'_');
                    if($("#current_connections tbody tr."+rclass).length == 0){
                        $(this).attr("disabled", "disabled");
                        $(this).removeAttr("checked");
                        $(this).parents("tr").attr("bgcolor", "#CCCCCC");
                        $("#current_connections").append(
                        "<tr name='"+rname+"' class='"+cclass+" hotlist'>"+
                        //"<td data-sort-value='0'><input type='checkbox' name='hotlist' onchange='highlightHotRow(this.checked, \"#current_connections tbody tr."+cclass+"\");' /></td>"+
                        lname_cell+
                        fname_cell+
                        proj_name_cell+
                        position_cell+
                        "<td align='center'><input class='current_conn_chkbox' type='checkbox' /></td>"+
                        "</tr>");
                        //$("#current_connections").trigger("sorton", [[[1,1], [2,0], [3,0], [4,0], [5,0]]]);
                        $("#current_connections").trigger("update", [true]);
        
                    }
                });
            colorSearchRows();
        });

        $("#confirm_forum_connections").click(function(e) {
            e.preventDefault();
            $("#forum_connections tbody tr input:checked").each(
                function(index) {
                    //row = $(this).parents("tr").html();
                    lname_cell = $(this).parents("tr").find("td.lname").outerHTML();
                    fname_cell = $(this).parents("tr").find("td.fname").outerHTML();
                    proj_name_cell = $(this).parents("tr").find("td.proj_names").outerHTML();
                    position_cell = $(this).parents("tr").find("td.position").outerHTML();
                    rname = $(this).parents("tr").attr("name");
                    rclass = $(this).parents("tr").attr("class");
                    cclass =rclass.replace(/\./g,'_');
                    if($("#current_connections tbody tr."+rclass).length == 0){
                        $(this).attr("disabled", "disabled");
                        $(this).removeAttr("checked");
                        $(this).parents("tr").attr("bgcolor", "#CCCCCC");
                        $("#current_connections").append(
                        "<tr name='"+rname+"' class='"+cclass+" hotlist'>"+
                        //"<td data-sort-value='0'><input type='checkbox' name='hotlist' onchange='highlightHotRow(this.checked, \"#current_connections tbody tr."+cclass+"\");' /></td>"+
                        lname_cell+
                        fname_cell+
                        proj_name_cell+
                        position_cell+
                        "<td align='center'><input class='current_conn_chkbox' type='checkbox' /></td>"+
                        "</tr>");
                        $("#current_connections").trigger("update", [true]);
                    }
                });
            colorSearchRows();
        });

        $("#confirm_new_connections").click(function(e) {
            e.preventDefault();
            $("#new_connections tbody tr:visible input:checked").each(
                function(index) {
                    //row = $(this).parents("tr").html();
                    lname_cell = $(this).parents("tr").find("td.lname").outerHTML();
                    fname_cell = $(this).parents("tr").find("td.fname").outerHTML();
                    proj_name_cell = $(this).parents("tr").find("td.proj_names").outerHTML();
                    position_cell = $(this).parents("tr").find("td.position").outerHTML();
                    rname = "new";
                    rclass = $(this).parents("tr").attr("id");
                    cclass =rclass.replace(/\./g,'_');
                    if($("#current_connections tbody tr."+cclass).length == 0){
                        $(this).attr("disabled", "disabled");
                        $(this).removeAttr("checked");
                        $(this).parents("tr").attr("bgcolor", "#CCCCCC");
                        $("#current_connections").append(
                        "<tr name='"+rname+"' class='"+cclass+" hotlist'>"+
                        //"<td data-sort-value='0'><input type='checkbox' name='hotlist' onchange='highlightHotRow(this.checked, \"#current_connections tbody tr."+cclass+"\");' /></td>"+
                        lname_cell+
                        fname_cell+
                        proj_name_cell+
                        position_cell+
                        "<td align='center'><input class='current_conn_chkbox' type='checkbox' /></td>"+
                        "</tr>");
                        $("#current_connections").trigger("update", [true]);
                    }
            });
        });

        colorSearchRows();
        colorForumRows();
        colorSurveyRows();
        
        function removeSelected(){
            $("#current_connections tbody tr input.current_conn_chkbox:checked").each(
                function(index) {
                    rname = $(this).parents("tr").attr("name");
                    rclass = $(this).parents("tr").attr("class");
                    $(this).parents("tr").remove();
                    
                   
                    $("#"+rname+"_connections tr[class='"+rclass+"']").each(
                        function(index){
                            $(this).find("input").removeAttr("disabled");
                            $(this).removeAttr("bgcolor");
                        }
                    );
                    if(rname != "new"){
                        $("#new_connections tr#"+rclass).each(
                        function(index){
                            $(this).find("input").removeAttr("disabled");
                            $(this).removeAttr("bgcolor");
                        }
                    );
                    }

                });
            colorSearchRows();
            colorForumRows();
            colorSurveyRows();
        }

        function highlightHotRow(status, selector){
            if(status){
                dsv_old = 0;
                dsv = 1;
                background = "#FF8888";
                $(selector).addClass("hotlist");
            }
            else{
                dsv_old = 1;
                dsv = 0;
                background = "";
                 $(selector).removeClass("hotlist");
            }
            $(selector).attr("bgcolor", background);
            $(selector).find("td[data-sort-value='"+dsv_old+"']").attr("data-sort-value", dsv);
            $(selector).closest("table").trigger("update", [true]);
           
        }

        function getSearchRowStyle(person_name){
            var bgcolor = "#FFFFFF";
            //check if this person exists in survey table
            //if($("#survey_connections tbody tr."+person_name).length != 0){
            //    bgcolor = "#FFFF99";
            //}
            //check if this person exists in forum table
            //else if($("#forum_connections tbody tr."+person_name).length != 0){
            //    bgcolor = "#FFCCFF";
            //}

            if($("#current_connections tbody tr."+person_name).length != 0){
                bgcolor = "#CCCCCC";
            }

            return bgcolor;
        }

        function colorSearchRows(){
            $("#new_connections tbody tr input").each(
                function(index) {
                    rclass = $(this).parents("tr").attr("id");
                    cclass = rclass.replace(/\./g,'_');
                    bgcolor = getSearchRowStyle(cclass);
                    $(this).parents("tr").attr("bgcolor", bgcolor);
                    if(bgcolor == "#CCCCCC"){
                        $(this).attr("disabled", "disabled");
                        //$(this).parents("tr").hide();
                    }
                    else{
                        //$(this).parents("tr").show();
                        $(this).removeAttr("disabled");
                    }
                }
            );
        }
        function colorSurveyRows(){
            $("#survey_connections tbody tr input").each(
                function(index) {
                    rclass = $(this).parents("tr").attr("class");
                    cclass = rclass.replace(/\./g,'_');
                    bgcolor = getSearchRowStyle(cclass);
                    $(this).parents("tr").attr("bgcolor", bgcolor);
                    if(bgcolor == "#CCCCCC"){
                        $(this).attr("disabled", "disabled");
                    }else{
                        $(this).removeAttr("disabled");
                    }
                }
            );
        }
        function colorForumRows(){
            $("#forum_connections tbody tr input").each(
                function(index) {
                    rclass = $(this).parents("tr").attr("class");
                    cclass = rclass.replace(/\./g,'_');
                    bgcolor = getSearchRowStyle(cclass);
                    $(this).parents("tr").attr("bgcolor", bgcolor);
                    if(bgcolor == "#CCCCCC"){
                        $(this).attr("disabled", "disabled");
                    }else{
                        $(this).removeAttr("disabled");
                    }
                }
            );
        }
        function validateNetwork(){
            var people = 0;
            $("#current_connections tbody tr").each(function(index){
               people++;
            });
            
            var error_msg = "";
            if(people == 0){
                error_msg = "Network: You need to add at least 1 connection in order to complete this section.";
            }

            return error_msg;
        }

        addEventTracking();
        </script>
EOF;
        //$wgOut->addScript($js);
        $this->html .= $js;
    }

    function getSurveyConnections(){
        global $wgUser;
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

        $people = array();
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
            return array();
        }        
        //ELSE

        $sql = "SELECT * FROM limesurvey.lime_survey_6 WHERE token='{$token}'";
        $data = DBFunctions::execSQL($sql);

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
                        //echo $name . "<br>";
                    }
                }
            }
        }
        asort($people);
        if(empty($people)){
            return array();
        }      

        $this->html .=<<<EOF
            <h3><a id="lnk_survey_connections" href="#"><span id='spn_survey_connections'>+</span> People you knew in NAVEL 2010 Survey</a></h3>
            <div id="div_survey_connections" style="display:none;">
            <table width='100%' id='survey_connections' class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all'>
            <thead>
            <tr bgcolor='#F2F2F2'>
            <th width='2%' class='sorter-false' title='Click to select all people in this table'>
                <input type='checkbox' name="survey_selectall_checkbox" onchange="toggleChecked(this.checked, 'input.survey_conn_chkbox');" />
            </th>
            <th width='15%' name="survey_lastname_header" title='Sort by last name'>Last Name</th>
            <th width='15%' name="survey_firstname_header" title='Sort by first name'>First Name</th>
            <th width='38%' name="survey_projects_header" title='Sort by projects'>Projects</th>
            <th width='20%' name="survey_university_header" title='Sort by university'>University</th>
            </tr>
            </thead>
            <tbody>
EOF;

        foreach($people as $name){

            //$this->html .= "<li>{$pers} <a href='#' onclick='$(this).parent().remove();return false;'> (remove)</a></li>";
            $this->html .= $this->getInfoRow($name, "survey");
        }
        $this->html .=<<<EOF
            </tbody>
            </table>
            <br /><button id="confirm_survey_connections">Confirm Selected</button>
            </div>
EOF;

        return $people;
    }
    
    function getForumConnections($past_conn){
        global $wgUser;
    
        $people = array();
        $me = Person::newFromId($wgUser->getId());
        foreach($me->getRelations('Works With', true) as $relation){
            $user2 = $relation->getUser2();
            $person_name = explode('.', $user2->getName());

            $fname = $person_name[0];
            $lname = implode(' ', array_slice($person_name, 1));
            $person_name = $lname . ", " . $fname;
            if(!in_array($person_name, $people) && !in_array($person_name, $past_conn) && ($user2->isRole(CNI) || $user2->isRole(PNI))){
                $people[] = $person_name;
            }
        }

        /*
        $my_projects = $me->getProjects(true);
        foreach($my_projects as $proj){
            $proj_people = $proj->getAllPeople();
            foreach($proj_people as $person){
                $person_name = explode('.', $person->getName());
                $fname = $person_name[0];
                $lname = implode(' ', array_slice($person_name, 1));
                $person_name = $lname . ", " . $fname;

                if($person->getName() == $me->getName()){
                    continue;
                }
                if(!in_array($person_name, $people) && !in_array($person_name, $past_conn) && ($person->isRole(CNI) || $person->isRole(PNI))){
                    $people[] = $person_name;
                }
            }
        }
        */

        $my_papers = $me->getPapersAuthored("all", "2000-01-01 00:00:00", "2020-01-01 00:00:00");
        foreach($my_papers as $paper){
            //echo $paper->getTitle() ."<br />";
            foreach($paper->getAuthors() as $author){
                if($author->getName() == $me->getName() || $author->getId() == 0){
                    continue;
                }

                $author_name = explode('.', $author->getName());
                $fname = $author_name[0];
                $lname = implode(' ', array_slice($author_name, 1));
                $author_name = $lname . ", " . $fname;
                if(!in_array($author_name, $people) && !in_array($author_name, $past_conn) && ($author->isCNI() || $author->isPNI())){
                    $people[] = $author_name;
                }
            }
        }

        asort($people);

        $this->html .=<<<EOF
            <h3><a id="lnk_forum_connections" href="#"><span id='spn_forum_connections'>+</span> People we think you know based on the FORUM data</a></h3>
            <div id="div_forum_connections" style="display:none;">
            <table width='100%' id='forum_connections' class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all'>
            <thead>
            <tr bgcolor='#F2F2F2'>
            <th width='2%' class='sorter-false' title='Click to select all people in this table'>
                <input type='checkbox' name="forum_selectall_checkbox" onchange="toggleChecked(this.checked, 'input.forum_conn_chkbox');" />
            </th>
            <th width='15%' name="forum_lastname_header" title='Sort by last name'>Last Name</th>
            <th width='15%' name="forum_firstname_header" title='Sort by first name'>First Name</th>
            <th width='38%' name="forum_projects_header" title='Sort by projects'>Projects</th>
            <th width='20%' name="forum_university_header" title='Sort by university'>University</th>
            </tr>
            </thead>
            <tbody>
EOF;
        foreach($people as $name){
            $this->html .= $this->getInfoRow($name, "forum");
            //$this->html .= "<li>{$pers} <a href='#' onclick='$(this).parent().remove();return false;'> (remove)</a></li>";
        }
        $this->html .=<<<EOF
            </tbody>
            </table>
            <br /><button id="confirm_forum_connections">Confirm Selected</button>
            </div>
EOF;

    }

    function newConnectionForm(){
        global $wgServer, $wgScriptPath, $wgUser, $wgOut;

        $me = Person::newFromId($wgUser->getId());
        $allPeople = $this->allpeps; //array_merge(Person::getAllPeople(CNI), Person::getAllPeople(PNI));
        $i = 0;
        $names = array();
        foreach($allPeople as $person){
            if($person->getName() != $me->getName()){
                $names[] = $person->getName();
            }
        }
        $names = implode("','", $names);
        
        $js =<<<EOF
        <script type="text/javascript">
        var sort = "first";
        var allPeople = new Array('{$names}');

        var oldOptions = Array();

        //SEARCH
        var no = $("#no").detach();
        if(no.length > 0){
            oldOptions["no"] = no;
        }
        filterResults($("#search").attr("value"));
        
        $("#search").keypress(function(event) {
            if(event.keyCode == 40){        //DOWN
                $.each($("#names").children(":selected").not("#no"), function(index, value){
                    if($(value).next().length > 0){
                        $(value).attr("selected", false);
                        $(value).next().attr("selected", true);
                    }
                });
            }
            else if(event.keyCode == 38){   //UP
                $.each($("#names").children(":selected").not("#no"), function(index, value){
                    if($(value).prev().length > 0){
                        $(value).attr("selected", false);
                        $(value).prev().attr("selected", true);
                    }
                });
            }
            colorSearchRows();
        });
        
        $("#search").keyup(function(event) {
            if(event.keyCode == 13){
                // Enter key was pressed
                var page = $("select option:selected").attr("name");
                if(typeof page != "undefined"){
                    document.location = "{$wgServer}{$wgScriptPath}/index.php/" + page;
                }
            }
            if(event.keyCode != 40 && event.keyCode != 38){
                filterResults(this.value);
            }
        });
            
        </script>
EOF;
        
        //$wgOut->addScript($js);    
        $this->html .= $js;    

        $this->html .=<<<EOF
        <h3><a id='lnk_new_connections' href='#'>
            <span id='spn_new_connections'>+</span> Select your connections in GRAND (if you have pre-populated data, this section provides additional connections)
        </a></h3>

        <div id='div_new_connections' style='display:none;'>
        
        <strong>Search:</strong> <input style='width:93%;' id='search' type='text' onKeyUp='filterResults(this.value);' />
        <div style='padding:2px;'></div>
        <table id='new_connections' class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all'>
        <thead>
        <tr bgcolor='#F2F2F2'>
        <th width='2%' class='sorter-false' title='Click to select all people in this table'>
            <input type='checkbox' name="search_selectall_checkbox" onchange="toggleChecked(this.checked, '#new_connections tbody tr:visible input.search_conn_chkbox');" />
        </th>
        <th width='15%' name="search_lastname_header" title='Sort by last name'>Last Name</th>
        <th width='15%' name="search_firstname_header" title='Sort by first name'>First Name</th>
        <th width='38%' name="search_projects_header" title='Sort by projects'>Projects</th>
        <th width='20%' name="search_university_header" title='Sort by university'>University</th>
        </tr>
        </thead>
        <tbody>
EOF;
        
        //$allPeople = NetworkTab::sort_people_by_last_name($allPeople);

        foreach($allPeople as $person){
            if($person->getName() == $me->getName()){
                continue;
            }    
            $projects = $person->getProjects();
            $position = $person->getUniversity();
            $position = $position['university'];
            if(empty($position)){
                $position = "Other Organizations";
            }
            $projs = array();
            foreach($projects as $project){
                $projs[] = $project->getName();
            }
            if(empty($projs)){
                continue;
            }
            $proj_names = implode(", ", $projs);

            $person_name = explode('.', $person->getName()); //preg_split('/\./', $person->getName(),2);

            $fname = $person_name[0];
            $lname = implode(' ', array_slice($person_name, 1));
            //$lname = $person_name[1];

            $bgcolor = "";
            //$row_id =  str_replace(".", ".", $person->getName());
            $row_id = $person->getName();
            $this->html .= <<<EOF
            <tr style='background-color:{$bgcolor};' name='search' id='{$row_id}' class='{$proj_names} {$position}'>
                <td><input class="search_conn_chkbox" type="checkbox" /></td>
                <td class='lname'>{$lname}</td>
                <td class='fname'>{$fname}</td>
                <td class='proj_names'>{$proj_names}</td>
                <td class='position'>{$position}</td>
            </tr>
EOF;
        }

        $this->html .=<<<EOF
        </tbody>
        </table>
        <button id="confirm_new_connections">Confirm Selected</button>
        </div>
EOF;
    }

    function currentNetwork(){
        global $wgUser;
        $current_connections = $this->getSavedData();

        $this->html .=<<<EOF
            <h3>Your 2012 Network</h3>
            <div>
            <table width='100%' id='current_connections' class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all'>
            <thead>
            <tr bgcolor='#F2F2F2'>
            <!--th width='2%' name="final_hotlist_header" title='Hot List'>HL</th-->
            <th width='15%' name="final_lastname_header" title='Sort by last name'>Last Name</th>
            <th width='15%' name="final_firstname_header" title='Sort by first name'>First Name</th>
            <th width='36%' name="final_projects_header" title='Sort by projects'>Projects</th>
            <th width='20%' name="final_university_header" title='Sort by university'>University</th>
            <th width='2%' class='sorter-false farright' title='Click on a row checkbox to select that row for removal'>
                <!--input type='checkbox' name="final_removeall_checkbox" onchange="toggleChecked(this.checked, 'tr input.current_conn_chkbox');" /-->Remove 
            </th>
            </tr>
            </thead>
            <tbody>
EOF;
        //var_dump($current_connections);
        foreach($current_connections as $con){
            $c = key($con);
            $hotlist = (isset($con[$c]['hotlist']) && $con[$c]['hotlist'])? "checked='checked'" : "";
            $hotlist_sort = 1; //(!empty($hotlist))? 1 : 0;
            $hotlist_class   = "hotlist"; // (!empty($hotlist))? "hotlist" : "";
            $bgcolor = ""; //($hotlist_sort)? "bgcolor='#FF8888'" : "";

            //$pname = preg_split('/\./', $c, 2);
            //$pnamef = (isset($pname[0]))? $pname[0] : "";
            //$pnamel = (isset($pname[1]))? $pname[1] : ""; 
            $pname = explode('.', $c); 
            $pnamef = $pname[0];
            $pnamel = implode(' ', array_slice($pname, 1));

            $pers = Person::newFromNameLike($c);
            if(!($pers instanceof Person)){
                continue;
            }
            $position = $pers->getUniversity();
            $position = $position['university'];
            if(empty($position)){
                $position = "Other Organizations";
            }
            $projects = $pers->getProjects();
            $proj_names = array();
            if(!is_array($projects)){
                $projects = array();
            }
            foreach($projects as $p){
                $proj_names[] = $p->getName();
            }
            $proj_names = implode(', ', $proj_names);
            $cclass = preg_replace('/\./', '_', $c);
            $this->html .=<<<EOF
            <tr {$bgcolor} name='current' class='{$cclass} {$hotlist_class}'>
                <!--td title='Click to add this person to Hot List' data-sort-value="{$hotlist_sort}">
                    <input name="hotlist" type="checkbox" {$hotlist} onchange="highlightHotRow(this.checked, '#current_connections tbody tr.{$cclass}');" />
                </td-->
                <td> <!--data-sort-value="{$hotlist_sort}"--> {$pnamel}</td>
                <td>{$pnamef}</td>
                <td>{$proj_names}</td>
                <td>{$position}</td>
                <td align="center" title='Click to select the person for removal'>
                    <input name="final_remove_checkbox" class="current_conn_chkbox" type="checkbox" />
                </td>
            </tr>
EOF;

            //$this->html .= $this->getInfoRow($pnamel.", ".$pnamef, "current");
        }

        $this->html .=<<<EOF
            </tbody>
            </table>
            </div>
EOF;
        
    }

    function getForumMatch($name){
        $name = preg_replace(array('/\s+/', '/\.+/', '/\s*\.+\s*/', '/<[^>]*>/', '/\-+/'), '', $name);
        
        foreach($this->allpeps as $person){
            $forum_name = preg_replace(array('/\s+/', '/\.+/', '/\s*\.+\s*/', '/<[^>]*>/', '/\-+/'), '', $person->getName());
            if(strcasecmp($name, $forum_name) == 0){
                return $person;
            }
        }
        return false;
    }

    function getInfoRow($name, $type){
        $pers_name = explode(', ', $name);
        //$pers_name = $pers_name[1]." ".$pers_name[0];
        $pers = Person::newFromNameLike($pers_name[1]." ".$pers_name[0]);
        
        if(is_null($pers->getId())){
            $pers = $this->getForumMatch($pers_name[1].$pers_name[0]);
        
            if($pers === false){
                //echo $pers_name[1]." ".$pers_name[0]."<br />";
                return "";
            }
        }
        $pers_name = preg_replace('/\./', ' ', $pers->getName());
        $pers_name = explode(' ', $pers_name, 2);

        $position = $pers->getUniversity();
        $position = $position['university'];
        if(empty($position)){
            $position = "Other Organizations";
        }
        $projects = $pers->getProjects();
        $proj_names = array();
        if(!is_array($projects)){
            $projects = array();
        }
        foreach($projects as $p){
            $proj_names[] = $p->getName();
        }
        $proj_names = implode(', ', $proj_names);
        $cclass = preg_replace('/\./', '_', $pers->getName());
        $html =<<<EOF
            <tr name='$type' class='{$cclass}'>
                <td><input class="{$type}_conn_chkbox" type="checkbox" /></td>
                <td class='lname'>{$pers_name[1]}</td>
                <td class='fname'>{$pers_name[0]}</td>
                <td class='proj_names'>{$proj_names}</td>
                <td class='position'>{$position}</td>
            </tr>
EOF;

        return $html;
    }


    function submitForm(){
        global $wgServer, $wgScriptPath, $wgOut;

        $js =<<<EOF
            <script type="text/javascript">
            function submitGrandConnections(){
                window.onbeforeunload = null;
                saveEventInfo();
                var error_msg = validateNetwork();
                if(error_msg != ""){
                    $('#netw_warnings_str').val(error_msg);
                }
                confirmed = '[';
                cnt = 0;
                $("#current_connections tbody tr").each(function(index) {
                    hotlist = 1;//($(this).find("input[name='hotlist']").is(":checked"))? 1 : 0;
                    $(this).removeClass("hotlist");
                    pname = $(this).attr("class");
                    pname = pname.replace(/_/g,'.');
                    if(pname){
                        if(cnt != 0){
                            confirmed += ',';
                        }
                        confirmed += '{"'+pname+'":{"acquaintance": -1, "work_with": -1, "gave_advice": -1, "received_advice": -1, "friend": -1, "hotlist":'+hotlist+', "communications":{}}}';
                        cnt++;
                    }
                });
                confirmed += ']';
                
                $('#connections').val(confirmed);    
                $('#submitForm').submit();
            }
            </script>
EOF;
        //$wgOut->addScript($js);
        $this->html .= $js;

        $this->html .=<<<EOF
            <br />
            <form id='submitForm' action='$wgServer$wgScriptPath/index.php/Special:Survey' method='post'>
            <input type='hidden' id='connections' name='connections' value='' />
            <input type='hidden' name='submit' value='{$this->name}' />
            <input type='hidden' id='netw_warnings_str' name='warnings' value='' /> 
EOF;

        if(!$this->isSubmitted()){
            $this->html .=<<<EOF
            <button style="float:left;" onclick="submitGrandConnections();">Save Network</button>
            <button style="float:right;" onclick="removeSelected(); return false;">Remove Selected</button> 
EOF;
        }
        $this->html .=<<<EOF
            <div style="clear:both;"></div>
            </form>
EOF;
    }

    function handleEdit(){
        global $wgUser, $wgMessage;
        $my_id = $wgUser->getId();
       
        //First let's see if there are any warnings
        $warnings = $_POST['warnings'];
        if(!empty($warnings)){
            $wgMessage->addWarning($warnings);
            $this->warnings = true;
        }

        $old_connections = $this->getSavedData();
        
        $confirmed = $_POST['connections'];
        $new_connections = json_decode($confirmed, true);
        //$new_connections = (is_empty($new_connections))? array() : $new_connections;
        foreach($new_connections as &$nc){
            $pname = key($nc);
            $new_array = &$nc[$pname];
            $old_array = NetworkTab::getPersonArray($pname, $old_connections);
            
            foreach($new_array as $k=>$v){

                if($k == "communications" && empty($v) && isset($old_array[$k]) && !empty($old_array[$k]) ){
                    $new_array[$k] = $old_array[$k];
                }
                else if($k != "communications" && $v < 0 && isset($old_array[$k])){
                    $new_array[$k] = $old_array[$k];
                }
            }   
        }

        $confirmed = json_encode($new_connections);

        $current_tab = (empty($warnings))? 5 : 4;
        $completed = $this->getCompleted();
        $completed[4] = (empty($warnings))? 1 : 0;
        $completed[5] = 0;
        $completed[6] = 0;
        $completed = json_encode($completed);
        
        $sql = "UPDATE survey_results 
                SET grand_connections = '%s',
                current_tab = %d,
                completed = '%s',
                timestamp = CURRENT_TIMESTAMP
                WHERE user_id = {$my_id}";
        $sql = sprintf($sql, $confirmed, $current_tab, $completed);
        $result = DBFunctions::execSQL($sql, true);
    
        //echo $result;
    }

    function getSavedData(){
        global $wgUser;
        $my_id = $wgUser->getId();
        //$me = Person::newFromId($my_id);

        $connections = array();
        
        $sql = "SELECT grand_connections FROM survey_results WHERE user_id = {$my_id}";
        $data = DBFunctions::execSQL($sql);
        
        if(isset($data[0])){
            $row = $data[0];
            //echo $row['grand_connections'];
            $json = json_decode($row['grand_connections'], true);
            //var_dump($json);
            $connections = ($json)? $json : array(); //explode(', ', $row['grand_connections']);
        }

        //$connections = NetworkTab::sort_connections_by_last_name($connections);

        return $connections;
    }

    function getDataUsageConsent(){
        global $wgUser;
        $my_id = $wgUser->getId();
    
        $consent = array();
        
        $sql = "SELECT use_forum_data, use_survey_data FROM survey_results WHERE user_id = {$my_id}";
        $data = DBFunctions::execSQL($sql);
        
        if(isset($data[0])){
            $consent = $data[0];
        }

        return $consent;
    }

    static function getPersonArray($pname, $connections){
        foreach($connections as $c){
            $name = key($c);
            if($name == $pname){
                return $c[$name];
            }
        }

        return array();
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