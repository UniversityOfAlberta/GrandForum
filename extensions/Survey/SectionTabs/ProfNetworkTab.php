<?php

class ProfNetworkTab extends AbstractSurveyTab {

    var $warnings = false;
    
    function ProfNetworkTab(){
        parent::AbstractSurveyTab("relationships");
        $this->title = "Relations";
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        
        $this->showIntro();
        $this->showForm();
        $this->submitForm();
        return $this->html;
    }

    function showIntro(){
        $this->html =<<<EOF
<div>

<p>For your professional relationships, please tell us (click on all that apply):
<ul>
<li>WHO YOU HAVE WORKED WITH in the past 12 months as part of your research -- such as collaborated on a research project, consulted, or wrote a paper.</li>
<li>WHO YOU GAVE ADVICE TO in the past 12 months when they had a question or problem related to research issues.</li>
<li>WHO YOU RECEIVED ADVICE FROM in the past 12 months when you had a question or a problem related to research issues.</li>
</ul>
Leave the boxes blank if you know someone but do not work or exchange advice with them.</p>

<p>For your social relationships, please tell us (click on either one or the other):
<ul>
<li>WHO is an ACQUAINTANCE - a person who you know but with whom you do not discuss matters important to you regardless of whether you work together at the moment or not.</li>
<li>WHO is a FRIEND - a person with whom you discuss matters important to you, regardless of whether you work together at the moment or not.</li>
</ul>
</p>
<p>You can use the filter below to locate a specific person in your network.</p>
</div>
EOF;

    }

    function showForm(){
        global $wgOut, $wgServer, $wgScriptPath;

        $connections = $this->getSavedData();

        $this->html .=<<<EOF
            
            <div>
            <strong>Filter:</strong> <input style='width:93%;' id='rel_filter' type='text' onkeyup='filterResultsRel(this.value);' />
            <table width='100%' id='prof_connections' class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all'>
            <thead>
            <tr bgcolor='#F2F2F2'>
            <th rowspan='2' width='17%' valign='top' class='relationships-header' style='padding-top:25px;'>Name</th>
            <th rowspan='2' width='21%' valign='top' class='relationships-header' style='padding-top:25px;'>University</th>
            <th colspan='4' class='sorter-false' align='center' style='border-left: 2px solid gray;'>Professional Relationship</th>
            <th colspan='2' class='sorter-false' align='center' style='border-left: 2px solid gray;'>Social Relationship</th>
            </tr>

            <tr bgcolor='#F2F2F2'>
            <th class='sorter-false relationships-header' width='8%' align='left' valign='top' style='border-left: 2px solid gray;' title='Click to apply all Professional Relationship options for all people currently shown'><input type="checkbox" onchange="toggleSelectAll(this, 'tr.hotlist:visible input.select_row');" />Select<br />All</th>
            <th class='sorter-false' width='10%' valign='top' align='left' title='Click to apply this professional relationship to to all people currently shown'><input type="checkbox" onchange="toggleChecked(this.checked, 'tr.hotlist:visible input.work_with');" /> Worked with in past 12 months on research</th>
            <th class='sorter-false' width='10%' valign='top' align='left' title='Click to apply this professional relationship to all people currently shown'><input type="checkbox" onchange="toggleChecked(this.checked, 'tr.hotlist:visible input.gave_advice');" /> Gave advice in past 12 months on research</th>
            <th class='sorter-false' width='10%' valign='top' align='left' title='Click to apply this professional relationship to all people currently shown'><input type="checkbox" onchange="toggleChecked(this.checked, 'tr.hotlist:visible input.received_advice');" /> Received advice in past 12 months on research</th>
            <th class='sorter-false' width='12%' valign='top' align='left' style='border-left: 2px solid gray;' title='Click to apply this social relationship to all people currently shown'><input type="radio" name='friend_all' onchange="toggleChecked(this.checked, 'tr.hotlist:visible input.acquaintance');" /> Acquaintance</th>
            <th class='sorter-false' width='12%' valign='top' align='left' title='Click to apply this social relationship to all people currently shown'><input type="radio" name='friend_all' onchange="toggleChecked(this.checked, 'tr.hotlist:visible input.friend');" /> Friend</th>
            </tr>
            </thead>
            <tbody>
EOF;
            $i = 1;
            foreach($connections as $c){
                $name = key($c);
            
                $pname = explode('.', $name); 
                $pnamef = $pname[0];
                $pnamel = implode(' ', array_slice($pname, 1));
                
                $this->html .= $this->getInfoRow($pnamel.", ".$pnamef, $c[$name], $i);
                $i++;
            }


            $this->html .=<<<EOF
            </tbody>
            </table>
            </div>
            
EOF;

        
    }

    function getInfoRow($name, $vals, $rownum){
        $pers_name = explode(', ', $name);
        //$pers_name = $pers_name[1]." ".$pers_name[0];
        $pers = Person::newFromNameLike($pers_name[1]." ".$pers_name[0]);
        if(!($pers instanceof Person)){
            return "";
        }   
        $projects = $pers->getProjects();
        $position = $pers->getUniversity();
        $position = $position['university'];
        if(empty($position)){
            $position = "Other Organizations";
        }
        $projs = array();
        foreach($projects as $project){
            $projs[] = $project->getName();
        }
        $proj_names = implode(", ", $projs);
        
        $work_with       = (isset($vals['work_with']) && $vals['work_with']==1)            ? 'checked="checked"' : '';
        $gave_advice     = (isset($vals['gave_advice']) && $vals['gave_advice']==1)        ? 'checked="checked"' : '';
        $received_advice = (isset($vals['received_advice']) && $vals['received_advice']==1)? 'checked="checked"' : '';

        $friend          = (isset($vals['friend']) && $vals['friend']==1)                  ? 'checked="checked"' : '';
        $acquaintance    = (isset($vals['acquaintance']) && $vals['acquaintance']==1)      ? 'checked="checked"' : '';

        $hotlist         = (isset($vals['hotlist']) && $vals['hotlist']==1)                ? "checked='checked'" : '';
        $hotlist_sort    = (!empty($hotlist))                                              ? 1 : 0;
        $hotlist_class   = (!empty($hotlist))                                              ? "hotlist" : "";

        $pname = preg_replace('/\./', '_', $pers->getName());
        $rowcolor = "#ffffff"; //($rownum % 2)? "#ffffff" : "#f2f2f2";
        $bgcolor = $rowcolor; //($hotlist_sort)? "#FF8888" : $rowcolor;
        //cclass = preg_replace('/\./', '_', $c);
        $html =<<<EOF
            <tr bgcolor="{$bgcolor}" name='prof' class='{$pname} {$hotlist_class}' title='{$pers->getName()} {$position} {$proj_names}'>
            <td>{$pers_name[0]}, {$pers_name[1]}</td>
            <td>{$position}</td>
            <td style='border-left: 2px solid gray;'><input class='select_row' title='Click to apply all Professional Relationship options for this person' type="checkbox" onchange="toggleChecked(this.checked, 'tr.{$pname} input.apply_check');" /></td>
            <td><input type='checkbox' class='work_with {$pname} apply_check' name='work_with' {$work_with} /></td>
            <td><input type='checkbox' class='gave_advice {$pname} apply_check' name='gave_advice' {$gave_advice} /></td>
            <td><input type='checkbox' class='received_advice {$pname} apply_check' name='received_advice' {$received_advice} /></td>
            <td style='border-left: 2px solid gray;'><input type='radio' class='acquaintance {$pname}' name='friend_{$pname}' {$acquaintance} /></td>
            <td><input type='radio' class='friend {$pname}' name='friend_{$pname}' value='friend' {$friend} /></td>
            </tr>
EOF;

        return $html;
    }

      function submitForm(){
        global $wgServer, $wgScriptPath, $wgOut;

        if($this->warnings){
            $validate_onload = "validateSection3();";
        }
        else{
            $validate_onload = "";
        }

        $js =<<<EOF
            <script type="text/javascript">
            $("#prof_connections").tablesorter({ 
                sortList: [[0,0]]
                //textExtraction: { 
                    //1: function(node, table, cellIndex){ return $(node).attr("data-sort-value"); }
                //}
                //sortForce: [[1,1]]
            });
            {$validate_onload}

            function toggleSelectAll(subj, selector){
                //status = $(subj).attr("checked");
                //status = $(subj).is(":checked");
                //status = (status == "checked")? 'checked' : 'unchecked';
            
                $(selector).each( function() {
                    //row_status = $(this).attr("checked");
                    //row_status = $(this).is(":checked");
                    //row_status = (row_status == "checked")? 'checked' : 'unchecked';
                    
                    if(  subj.checked != this.checked ){
                        $(this).trigger('click');
                    }
                });
            }
            
            function filterResultsRel(value){
                if(typeof value != 'undefined'){
                    value = $.trim(value);
                    value = value.replace(/\s+/g, '|');
                    //console.log(value);
                    $.each($("tr[name=prof]"), function(index, val){
                        if($(val).attr("title").toLowerCase().regexIndexOf(value.toLowerCase()) != -1){
                            $(val).show();
                        }
                        else{
                            $(val).hide();
                        }
                    });
                }
            }

            function submitProfConnections(){
                window.onbeforeunload = null;
                saveEventInfo();
                confirmed = '[';
                cnt = 0;

                var error_msg = validateSection3();
                if(error_msg != ""){
                    //alert(error_msg);
                    //return  false;
                    $('#rel_warnings_str').val(error_msg);
                }

                $("#prof_connections tbody tr").each(function(index) {
                    
                    work_with = ($(this).find("input[name='work_with']").is(":checked"))? 1 : 0;
                    gave_advice = ($(this).find("input[name='gave_advice']").is(":checked"))? 1 : 0;
                    received_advice = ($(this).find("input[name='received_advice']").is(":checked"))? 1 : 0;
                    friend = ($(this).find("input.friend").is(":checked"))? 1 : 0;
                    acquaintance = ($(this).find("input.acquaintance").is(":checked"))? 1 : 0;
                    hotlist = 1; //($(this).find("input[name='hotlist']").is(":checked"))? 1 : 0;
                    $(this).removeClass("hotlist");
                    pname = $(this).attr("class");
                    pname = pname.replace(/_/g,'.');
                    if(pname){
                        if(cnt != 0){
                            confirmed += ',';
                        } 
                        confirmed += '{"'+pname+'":{"work_with":'+work_with+',"gave_advice":'+gave_advice+',"received_advice":'+received_advice+', "acquaintance":'+acquaintance+', "friend":'+friend+', "hotlist":'+hotlist+', "communications":{}}}';
                        cnt++;
                    }
                });
    
                confirmed += ']';
                $('#profconnections_str').val(confirmed);
                $('#profConForm').submit();
            }


            function validateSection3(){
                var people = new Array();
                $("#prof_connections tbody tr").each(function(index){
                    //valid = false;
                    //valid = ($(this).find("input.work_with").is(":checked"))? true : valid;
                    //valid = ($(this).find("input.gave_advice").is(":checked"))? true : valid;
                    //valid = ($(this).find("input.received_advice").is(":checked"))? true : valid;

                    valid2 = false;
                    valid2 = ($(this).find("input.friend").is(":checked"))? true : valid2;
                    valid2 = ($(this).find("input.acquaintance").is(":checked"))? true : valid2;

                    if( !valid2 ){
                        $(this).find("td").each( function(index){ if( index>0){ $(this).attr("bgcolor", "yellow"); } });
                        name = $(this).attr("class");
                        name = name.replace(' hotlist', '');
                        people.push(name.replace(/_/g, ' '));
                    }
                    else{
                         $(this).find("td").each( function(index){ $(this).removeAttr("bgcolor"); });
                    }
                });
                
                var error_msg = "";
                if(people.length > 0){
                    error_msg = "Relationships: You need to provide input for the following people to successfully complete the section:<br />" + 
                                 people.join('<br />');
                }

                return error_msg;
            }
            addEventTracking();
            $('th[title], input[title], td[title], tr.tr_qtip[title]').qtip({position: {my: "top left", at: "center center"}});
            </script>
EOF;
        //$wgOut->addScript($js);
        $this->html .= $js; 

        $this->html .=<<<EOF
            <br />
            <form id='profConForm' action='$wgServer$wgScriptPath/index.php/Special:Survey' method='post'>
            <input type='hidden' id='profconnections_str' name='connections' value='' />
            <input type='hidden' id='rel_warnings_str' name='warnings' value='' />
            <input type='hidden' name='submit' value='{$this->name}' />
EOF;
        if(!$this->isSubmitted()){
            $this->html .= '<button onclick="submitProfConnections(); return false;">Save Relationships</button>';
        }

        $this->html .= "</form>";
    }
    
    function getSavedData(){
        global $wgUser;
        $my_id = $wgUser->getId();
      
        $connections = array();
        
        $sql = "SELECT grand_connections FROM survey_results WHERE user_id = {$my_id}";
        $data = DBFunctions::execSQL($sql);
        
        if(isset($data[0])){
            $row = $data[0];
            $json = json_decode($row['grand_connections'], true);
            $connections = ($json)? $json : array(); 
        }

        return $connections;
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
        
        foreach($new_connections as &$nc){
            $pname = key($nc);
            $new_array = &$nc[$pname];
            $old_array = NetworkTab::getPersonArray($pname, $old_connections);
            
            foreach($new_array as $k=>$v){
                if($k == "communications" && empty($v) && isset($old_array[$k]) && !empty($old_array[$k]) ){
                    $new_array[$k] = $old_array[$k];
                }
            }   
        }

        $confirmed = json_encode($new_connections);

        $current_tab = (empty($warnings))? 6 : 5;
        $completed = $this->getCompleted();
        $completed[5] = (empty($warnings))? 1 : 0;
        $completed = json_encode($completed);
        
        $sql = "UPDATE survey_results 
                SET grand_connections = '%s',
                current_tab = %d,
                completed = '%s',
                timestamp = CURRENT_TIMESTAMP
                WHERE user_id = {$my_id}";
        $sql = sprintf($sql, $confirmed, $current_tab, $completed);
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


    private static function sort_connections_by_last_name($connections){
        $to_sort = array();
        foreach($connections as $c){
            $name = key($c);
            //$person = Person::newFromId($a);
            $name = preg_split('/\./', $name, 2);
            $name = $name[1].", ".$name[0];
            $to_sort[$name] = $c;
        }
        ksort($to_sort);
        
        $sorted = array();
        foreach($to_sort as $name => $c){
            $sorted[] = $c;
        }
        return $sorted;
        
    }
}    
    
?>