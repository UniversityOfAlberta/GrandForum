<?php

class YourCommunicationTab extends AbstractSurveyTab {

    var $warnings = false;
    
    function YourCommunicationTab(){
        parent::AbstractSurveyTab("communication");
        $this->title = "Communication";
    }
    
    function generateBody(){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath;
        
        if($this->warnings){
            $validate_onload = "validateCommunication();";
        }
        else{
            $validate_onload = "";
        }

        $js =<<<EOF
        <script type="text/javascript">
        /*$("#local_communication").tablesorter({ 
            selectorHeaders: '> thead > tr th',
            sortList: [[0,0]],
            textExtraction: { 
                0: function(node, table, cellIndex){ return $(node).attr("data-sort-value"); }
            } 
            //sortForce: [[0,0]]
        });*/

        function cloneHeader(){
            var header = $("#local_communication > thead").clone(true,true);
            var fixedHeader = $("#header-fixed").html(header);

            /*$("#local_communication").tablesorter({ 
                sortList: [[0,0]],
                textExtraction: {
                    0: function(node, table, cellIndex){ return $(node).attr("data-sort-value"); }
                }
                //sortForce: [[0,0]]
            });*/
            //colorTableRows();
            $('#local_communication thead th, #local_communication thead td').qtip("disable");
            setupQtips();
        }

        function cloneHeaderBack(){
            var header = $("#local_communication > thead input");
            var fixedHeader = $("#header-fixed > thead input");
            header.each(
                function(index){
                    if($(this).is("input[type='text']")){
                        $(this).val(fixedHeader.eq(index).val());
                    }
                    else{
                        checked = fixedHeader.eq(index).attr("checked");
                        //console.log(checked);
                        if(checked == "checked"){
                            $(this).attr("checked", checked);
                        }else{
                            $(this).removeAttr("checked");
                        }
                    }
                }
            );
        }

        function onScrollRoutine(){
            var tableOffset = $("#local_communication").offset().top;
            var tableOffsetLeft = $("#local_communication").offset().left;
            var tableOffsetScrollLeft = $(this).scrollLeft();
            var offset = $(this).scrollTop();
            var fixedHeader = $("#header-fixed");

            if (offset >= tableOffset && fixedHeader.is(":hidden")) {
                cloneHeader();
                //console.log(tableOffsetLeft-tableOffsetScrollLeft);
                fixedHeader.css("left",tableOffsetLeft-tableOffsetScrollLeft);
                fixedHeader.show();
            }
            else if (offset < tableOffset && !fixedHeader.is(":hidden")) {
                cloneHeaderBack()
                fixedHeader.hide();
            }
            else if (offset < tableOffset) {
                fixedHeader.hide();
            }
        }

        $(window).bind("scroll", onScrollRoutine);
        
       
        $("a[href='#communication']").click(function(){ 
            onScrollRoutine(); 
        });
        {$validate_onload}
        

        function colorTableRows(){
            var rownum = 1;
            $("#local_communication tbody tr.person_row").each(function(index) {
                var rowcolor = (rownum % 2)? "#ffffff" : "#f2f2f2";
                $(this).attr("bgcolor", rowcolor);
                rownum++;
            });
        }
        
        function setupQtips(){
            $('th[title], td.tbl_medium_cln[title], td.hdr_medium_cln[title]').qtip({position: {my: "top left", at: "center center"}});
            $('td.hdr_radio').qtip({content: 'Click to apply this frequency for all people currently shown. This will also select the medium on this row for all people currently shown.', position: {my: "top left", at: "center center"}});
        }
        
        colorTableRows();
        addEventTracking();
        setupQtips();
        disableCheckboxes();
        </script>
EOF;

        //$wgOut->addScript($js);

        $this->showIntro();
        $this->showForm();
        
        $this->submitForm();

        $this->html .= $js;
        return $this->html;
    }

    function showIntro(){
        $this->html =<<<EOF
<div>
<p>For each of your friends or colleagues in GRAND, please choose the TOP TWO media you use to communicate with this person and then indicate how often you use them. To reduce the work on completing this section, we have excluded from the list below the acquaintances with whom you do not work or exchange advice.</p>
<p>If you only use one medium to contact a person, then please select just one option. You cannot choose more than two media. Once you have selected two media, the rest become unavailable; to change your media selection you have to first deselect an old option.</p>
<p>You can use the filter below to locate a specific person in your network.</p>
</div>
EOF;

    }

    function showForm(){
        global $wgOut, $wgServer, $wgScriptPath;

        $connections = $this->getSavedData();
       

        $this->html .=<<<EOF
<style>
#header-fixed { 
    position: fixed; 
    top: 0px;
    display:none;
    background-color:white;
}
.local_commun_inner_tbl td {
    text-align: left;
}
</style>

<div style="position:relative;">
<strong>Filter:</strong> <input style='width:93%;' id='comm_filter' type='text' onkeyup='filterResultsCom(this.value);' />
<div style='padding:2px;'></div>
<table width='987' id="header-fixed" class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all'></table>
<table width='987' id='local_communication' class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all'>
<thead>
<tr id="local_commun_1strow" bgcolor='#F3EBF5'>
<th class='sorter-false' width="12%" rowspan="3" align="left" valign="top" style="padding-top:24px;">Name</th>
<th class='sorter-false' class='sorter-false' width="18%" rowspan="2" align="left" valign="top" style="padding-top:24px;" title='Please choose 1 or 2 media for communication'>Top two media</th>
<th class='sorter-false' width="70%" colspan="7" align="center">Frequency</th>
</tr>
<tr bgcolor='#F3EBF5'>
<th align='left' class='sorter-false' width='10%' valign="top">Several times a day</th>
<th align='left' class='sorter-false' width='10%' valign="top">About daily</th>
<th align='left' class='sorter-false' width='10%' valign="top">About weekly</th>
<th align='left' class='sorter-false' width='10%' valign="top">About monthly</th>
<th align='left' class='sorter-false' width='10%' valign="top">A few times a year</th>
<th align='left' class='sorter-false' width='10%' valign="top">About yearly</th>
<th align='left' class='sorter-false' width='10%' valign="top">Less than<br /> once a year</th>
</tr>

<tr bgcolor='#F3EBF5'>
<th class='sorter-false' colspan="8" style="padding:0px;">
<table class="local_commun_inner_tbl" border="1" cellpadding="2" cellspacing="0" width="100%" style="border:0px;">
<tr>
<td width="18%" class="hdr_medium_cln" title='Click to apply this medium for all people currently shown'>
<input class="all_person_checkbox" type="checkbox" onchange="headerRowCheckbox(this, 'tr.hotlist:visible input.inperson');" /> In Person
</td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq1" onchange="headerRowRadio(this, 'tr.hotlist:visible input.inperson_moredaily');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq1" onchange="headerRowRadio(this, 'tr.hotlist:visible input.inperson_daily');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq1" onchange="headerRowRadio(this, 'tr.hotlist:visible input.inperson_weekly');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq1" onchange="headerRowRadio(this, 'tr.hotlist:visible input.inperson_monthly');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq1" onchange="headerRowRadio(this, 'tr.hotlist:visible input.inperson_moreyearly');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq1" onchange="headerRowRadio(this, 'tr.hotlist:visible input.inperson_yearly');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq1" onchange="headerRowRadio(this, 'tr.hotlist:visible input.inperson_lessyearly');" /></td>
</tr>
<tr>
<td width="18%" class="hdr_medium_cln" title='Click to apply this medium for all people currently shown'>
<input class="all_email_checkbox" type="checkbox" onchange="headerRowCheckbox(this, 'tr.hotlist:visible input.email');" /> Email
</td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq2" onchange="headerRowRadio(this, 'tr.hotlist:visible input.email_moredaily');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq2" onchange="headerRowRadio(this, 'tr.hotlist:visible input.email_daily');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq2" onchange="headerRowRadio(this, 'tr.hotlist:visible input.email_weekly');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq2" onchange="headerRowRadio(this, 'tr.hotlist:visible input.email_monthly');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq2" onchange="headerRowRadio(this, 'tr.hotlist:visible input.email_moreyearly');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq2" onchange="headerRowRadio(this, 'tr.hotlist:visible input.email_yearly');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq2" onchange="headerRowRadio(this, 'tr.hotlist:visible input.email_lessyearly');" /></td>
</tr>
<tr>
<td width="18%" class="hdr_medium_cln" title='Click to apply this medium for all people currently shown'>
<input class="all_phone_checkbox" type="checkbox" onchange="headerRowCheckbox(this, 'tr.hotlist:visible input.phone');" /> Phone, Skype
</td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq3" onchange="headerRowRadio(this, 'tr.hotlist:visible input.phone_moredaily');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq3" onchange="headerRowRadio(this, 'tr.hotlist:visible input.phone_daily');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq3" onchange="headerRowRadio(this, 'tr.hotlist:visible input.phone_weekly');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq3" onchange="headerRowRadio(this, 'tr.hotlist:visible input.phone_monthly');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq3" onchange="headerRowRadio(this, 'tr.hotlist:visible input.phone_moreyearly');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq3" onchange="headerRowRadio(this, 'tr.hotlist:visible input.phone_yearly');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq3" onchange="headerRowRadio(this, 'tr.hotlist:visible input.phone_lessyearly');" /></td>
</tr>
<tr>
<td width="18%" class="hdr_medium_cln" title='Click to apply this medium for all people currently shown'>
<input class="all_forum_checkbox" type="checkbox" onchange="headerRowCheckbox(this, 'tr.hotlist:visible input.forum');" /> GRAND Forum
</td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq4" onchange="headerRowRadio(this, 'tr.hotlist:visible input.forum_moredaily');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq4" onchange="headerRowRadio(this, 'tr.hotlist:visible input.forum_daily');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq4" onchange="headerRowRadio(this, 'tr.hotlist:visible input.forum_weekly');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq4" onchange="headerRowRadio(this, 'tr.hotlist:visible input.forum_monthly');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq4" onchange="headerRowRadio(this, 'tr.hotlist:visible input.forum_moreyearly');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq4" onchange="headerRowRadio(this, 'tr.hotlist:visible input.forum_yearly');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq4" onchange="headerRowRadio(this, 'tr.hotlist:visible input.forum_lessyearly');" /></td>
</tr>
<tr>
<td width="18%" class="hdr_medium_cln" title='Click to apply this medium for all people currently shown'>
<input class="all_other_checkbox" type="checkbox" onchange="headerRowCheckbox(this, 'tr.hotlist:visible input.other');" /> Other: <input type='text' class='other_option' value='Specify' onblur="if(this.value=='') this.value='Specify';" onfocus="if(this.value=='Specify') this.value='';" onchange="updateOtherMedium(this.value);" size='10' />
</td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq5" onchange="headerRowRadio(this, 'tr.hotlist:visible input.other_moredaily');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq5" onchange="headerRowRadio(this, 'tr.hotlist:visible input.other_daily');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq5" onchange="headerRowRadio(this, 'tr.hotlist:visible input.other_weekly');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq5" onchange="headerRowRadio(this, 'tr.hotlist:visible input.other_monthly');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq5" onchange="headerRowRadio(this, 'tr.hotlist:visible input.other_moreyearly');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq5" onchange="headerRowRadio(this, 'tr.hotlist:visible input.other_yearly');" /></td>
<td width="10%" class="hdr_radio"><input type="radio" name="selectallfreq5" onchange="headerRowRadio(this, 'tr.hotlist:visible input.other_lessyearly');" /></td>
</tr>
</table>
</th>
</tr>
</thead>
<tbody>
EOF;

        $i = 1;
        foreach ($connections as $con){
            $name = key($con);
            //$pname = preg_split('/\./', $name, 2);
            //$pnamef = (isset($pname[0]))? $pname[0] : "";
            //$pnamel = (isset($pname[1]))? $pname[1] : ""; 

            $pname = explode('.', $name); 
            $pnamef = $pname[0];
            $pnamel = implode(' ', array_slice($pname, 1));
            
            //print_r($con[$name]); echo "<br>";
            $this->html .= $this->getInfoRow($pnamel.", ".$pnamef, $con[$name], $i);
            $i++;

        }

       
        $this->html .=<<<EOF
            </tbody>
            </table>
            </div>
            <br />
            <form id='communicationForm' action='$wgServer$wgScriptPath/index.php/Special:Survey' method='post'>
            <input type='hidden' name='submit' value='{$this->name}' />
            <input type='hidden' value='' name='communication_str' id='communication_str' />
            <input type='hidden' value='' name='warnings' id='com_warnings_str' />
EOF;

        if(!$this->isSubmitted()){
            $this->html .= '<button onclick="submitCommunication(); return false;">Save Communication</button>';
        }

        $this->html .= "</form>";

    }

    function getInfoRow($name, $vals, $rownum){
        $all_media = array(
                        "inperson"=>"In Person",
                        "email"=>"Email",
                        "phone"=>"Phone, Skype",
                        "forum"=>"GRAND Forum",
                        "inperson"=>"In Person",
                        "other"=>"Other"
                    );
        
        $all_values = array(
                        "moredaily", 
                        "daily", 
                        "weekly", 
                        "monthly", 
                        "moreyearly", 
                        "yearly", 
                        "lessyearly"
                    );

        $pers_name = explode(', ', $name);
       
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

        $hotlist         = (isset($vals['hotlist']) && $vals['hotlist'])? "checked='checked'" : '';
        $hotlist_sort    = (!empty($hotlist))? 1 : 0;
        $hotlist_class   = (!empty($hotlist))? "hotlist" : "";   

        $pname = preg_replace('/\./', '_', $pers->getName());
        $rowcolor = "#ffffff"; //($rownum % 2)? "#ffffff" : "#f2f2f2";
        $bgcolor = $rowcolor; //($hotlist_sort)? "#FF8888" : $rowcolor;

        $acquaintance = (isset($vals['acquaintance']) && $vals['acquaintance']>0)? 1 : 0;
        $work_with = (isset($vals['work_with']) && $vals['work_with']>0)? 1 : 0;
        $gave_advice = (isset($vals['gave_advice']) && $vals['gave_advice']>0)? 1 : 0;
        $received_advice = (isset($vals['received_advice']) && $vals['received_advice']>0)? 1 : 0;

        if($acquaintance && !$work_with && !$gave_advice && !$received_advice){
            return "";
        }

        $html =<<<EOF
<tr name="search" bgcolor="{$bgcolor}" class="person_row {$hotlist_class} {$proj_names} {$position} {$pers->getName()}" title="{$pers->getName()}">
<td data-sort-value="{$pers_name[0]} {$pers_name[1]}" valign="top" align="left">{$pers_name[0]},<br />{$pers_name[1]}</td>
<td class='sorter-false' colspan="8" style="padding:0px;">
<table border="1" cellpadding="2" cellspacing="0" width="100%" style="border:0px;">
<thead></thead>
<tbody>
EOF;
        //echo "<br />$name ::: ";
        //print_r($vals['communications']);
        //echo "<br>";

        if(isset($vals['communications'])){
            $vals = $vals['communications'];
        }else{
            $vals = array();
        }

        foreach ($all_media as $m=>$mlabel){
            $checked = '';
            $other_option_key = "";
            $other_option_val = "Specify";
            if($m == "other"){
                foreach($vals as $v=>$vv){
                    if(preg_match('/other\=/', $v)){
                        $checked = 'checked="checked"';
                        $other_option_key = $v;
                        $other_option_val = htmlspecialchars(urldecode(preg_replace('/other\=/', '', $v)), ENT_QUOTES);
                        //echo $other_option ."<br>";
                    }
                }
            }else{
                $checked = (isset($vals[$m]))? 'checked="checked"' : '';
            }
            

            if($m != "other"){
                $html .=<<<EOF
<tr><td width="18%" class="tbl_medium_cln" title='Click to apply this medium for this person'><input type='checkbox' class='{$m} {$pname}' value='{$m}' {$checked} onchange="rowCheckbox(this);" />$mlabel</td>
EOF;
            }else{
                $html .=<<<EOF
<tr><td width="18%" class="tbl_medium_cln" title='Click to apply this medium for this person'><input type='checkbox' class='{$m} {$pname}' value='{$m}' {$checked} onchange="rowCheckbox(this);" /> Other: <input type='text' class='other_option' value='{$other_option_val}' onblur="if(this.value=='') this.value='Specify';" onfocus="if(this.value=='Specify') this.value='';" size='10' /></td>
EOF;
            }

            foreach ($all_values as $v){
                if($m == "other"){
                    $radioed = (isset($vals[$other_option_key]) && $vals[$other_option_key] == $v)? 'checked="checked"' : '';
                }
                else{
                    $radioed = (isset($vals[$m]) && $vals[$m] == $v)? 'checked="checked"' : '';
                }

                $html .=<<<EOF
<td width="10%"><input type="radio" class="{$m}_{$v}" name="{$pname}_{$m}" value="$v" {$radioed} onchange="rowRadio(this);" /></td>
EOF;

            }

            $html .= "</tr>";
        }

        $html .=<<<EOF
            </tbody>
            </table>
            </td>
            </tr>
EOF;
    
        return $html;

    }

    function submitForm(){
        global $wgServer, $wgScriptPath, $wgOut;

        $js =<<<EOF
            <script type="text/javascript">
            function updateOtherMedium(value){
                $('tr.hotlist:visible input.other_option').each(
                    function(index){
                        $(this).val(value);
                    }
                );
            }
            function filterResultsCom(value){
                if(typeof value != 'undefined'){
                    value = $.trim(value);
                    value = value.replace(/\s+/g, '|');
                    $.each($("tr[name=search]"), function(index, val){
                        if($(val).attr("class").toLowerCase().regexIndexOf(value.toLowerCase()) != -1){
                            $(val).show();
                        }
                        else{
                            $(val).hide();
                        }
                    });
                }
            }

            function rowCheckbox(subj){
                //if not checked -> clear radio buttons
                if(! $(subj).is(":checked") ){
                    $(subj).closest("tr").find(":radio:checked").removeAttr("checked"); 
                }

                //check if > 1 is checked, then disable others. Otherwise enable all
                var count_checked = $(subj).closest("tbody").find(":checkbox:checked").length;
                if(count_checked>1){
                    $(subj).closest("tbody").find(":checkbox:not(:checked)").attr("disabled", true);
                }
                else{
                    $(subj).closest("tbody").find(":checkbox").removeAttr("disabled");
                }
            }

            function rowRadio(subj){
                //First, if this row's checkbox is already checked, we're free
                rowcheck = $(subj).closest("tr").find(":checkbox").first();
                if(rowcheck.attr("checked")){
                    return true;
                }

                //Else
                //Now check if more than 1 checkbox is already checked off.
                //If it is, don't do anything and uncheck this radio button
                var count_checked = $(subj).closest("tbody").find(":checkbox:checked").length;
                if(count_checked>1){
                    $(subj).removeAttr("checked");
                }
                else{
                    rowcheck.attr("checked",true);
                    rowCheckbox(rowcheck);
                }
            }

            function headerRowCheckbox(subj, selector){
                rowCheckbox(subj);
                //status = $(subj).attr("checked");
                //status = (status == "checked")? 'checked' : 'unchecked';
                
                $(selector).each( function() {
                    //row_status = $(this).attr("checked");
                    //row_status = (row_status == "checked")? 'checked' : 'unchecked';
                    
                    //if(status.localeCompare(row_status) != 0){
                    if(subj.checked != this.checked ){
                        //console.log("ROW:"+row_status);
                        $(this).trigger('click');
                    }
                    
                });
            }

            function headerRowRadio(subj, selector){
                rowRadio(subj);
                status = $(subj).attr("checked");
                //console.log("HDR:"+status);
                $(selector).each( function() {
                    row_status = $(this).attr("checked");
                    if(row_status != status){
                        $(this).trigger('click');
                    }
                    
                });
            }

            function disableCheckboxes(){
                $("tr.person_row table").each(function(index){
                    if($(this).find(":checkbox:checked").length > 1){
                        $(this).find(":checkbox:not(:checked)").attr("disabled",true);
                    }
                });
            }

            ////
            function toggleCheckedS4(status, selector){
                toggleChecked(status, selector);
                uncheckRadio(status, selector);
            }

            function uncheckRadio(status, selector){
                if(!status){
                    $(selector).each( 
                        function(index){ 
                            $(this).closest("tr").find(":radio:checked").removeAttr("checked"); 
                    });
                }
            }

            function checkMedium(selector){
                $(selector).attr("checked", "checked");
            }

            /*function disableCheckboxes(subj, selector){
                var count_checked=0;
                var checked = $(subj).is(":checked");
                $(selector).each(
                    function(index){
                        if( $(this).is(":checked") ){
                            count_checked++;
                        }
                });
                
        
                if(count_checked>1){
                    $(selector).each(
                        function(index){
                            if( !$(this).is(":checked") ){
                                $(this).attr("disabled", true);
                            }
                    });
                }else{
                    $(selector).each(
                        function(index){
                            if( !$(this).is(":checked") ){
                                $(this).removeAttr("disabled");
                            }
                    });
                }
               
            }*/

            function validateCommunication(){
               
                $("#local_communication tbody tr.person_row").each(function(index) {
                    
                    checked_media=0;
                    $(this).find(":checkbox:checked").each(function(index) {
                        check_val = $(this).val();
                        if(check_val == "other"){
                            other_option = $(this).next("input.other_option").first().val();
                            check_val = check_val+'='+escape(other_option);
                        }
                        radio_val = $(this).closest("tr").find(":radio:checked").val();
                        
                        if(check_val && radio_val){
                            checked_media++;
                        }
                    });

                    if(checked_media<1 || checked_media>2){
                        $(this).find("td").each( function(index){ if( index>0){ $(this).attr("bgcolor", "yellow"); } });
                    }
                    else{
                        $(this).find("td").each( function(index){ $(this).removeAttr("bgcolor"); });
                    }                    
                });
                
            }

            function submitCommunication(){
                window.onbeforeunload = null;
                saveEventInfo();
                commun = '[{';
                cnt = 0;
                people = new Array();
                $("#local_communication tbody tr.person_row").each(function(index) {
                    if(cnt != 0){
                        commun += ',';
                    } 

                    pname = $(this).attr("title");
                    commun += '"'+pname+'":{';
                    cnt2=0;
                    checked_media=0;
                    $(this).find(":checkbox:checked").each(function(index) {
                        if(cnt2 != 0){
                            commun += ',';
                        }   
                        check_val = $(this).val();
                        if(check_val == "other"){
                            other_option = $(this).next("input.other_option").first().val();
                            check_val = check_val+'='+escape(other_option);
                        }
                        radio_val = $(this).closest("tr").find(":radio:checked").val();
                        commun += '"'+check_val+'":"'+radio_val+'"';
                        cnt2++;
                        if(check_val && radio_val){
                            checked_media++;
                        }
                    });

                    if(checked_media<1 || checked_media>2){
                        $(this).find("td").each( function(index){ if( index>0){ $(this).attr("bgcolor", "yellow"); } });
                        people.push(pname.replace(/\./g, ' '));
                    }
                    else{
                        $(this).find("td").each( function(index){ $(this).removeAttr("bgcolor"); });
                    }                    
                    commun += '}'
                    cnt++;
                });
                
                if(people.length > 0){
                    error_msg = "Communication: You need to provide 1 or 2 Media and Frequency of Communication for the following people to successfully complete the section:<br />"+ people.join('<br />');
                    $('#com_warnings_str').val(error_msg);
                    //alert(error_msg);
                    //return false;
                }
               
                commun += '}]';
                $('#communication_str').val(commun);

                $('#communicationForm').submit();
            }

            </script>
EOF;
        //$wgOut->addScript($js);
        $this->html .= $js;
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

        //$connections = YourCommunicationTab::sort_connections_by_last_name($connections);

        return $connections;
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

        $connections = $this->getSavedData();

        $communications = ($_POST['communication_str'])? $_POST['communication_str'] : "";
        
        $json = json_decode($communications, true);
        $communications = ($json)? $json : array(); 
        //print_r($communications[0]);
        foreach($communications[0] as $name=>$com){
            
            foreach($connections as &$c){
                if(isset($c[$name])){
                    $temp_comm_array = array();
                    foreach($com as $med=>$freq){
                        $temp_comm_array[$med] = $freq;
                        
                    }
                    $c[$name]['communications'] = $temp_comm_array;
                    //echo "<br /><br />".$name."::";
                    //print_r($c[$name]);
                }
            }
        }
        //echo "<br><br>";
        //print_r($connections);

        $connections = json_encode($connections);

        $current_tab = (empty($warnings))? 7 : 6;
        $completed = $this->getCompleted();
        $completed[6] = (empty($warnings))? 1 : 0;
        $completed = json_encode($completed);

        $sql = "UPDATE survey_results 
                SET grand_connections = '%s',
                current_tab = %d,
                completed = '%s',
                timestamp = CURRENT_TIMESTAMP
                WHERE user_id = {$my_id}";
        $sql = sprintf($sql, $connections, $current_tab, $completed);
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