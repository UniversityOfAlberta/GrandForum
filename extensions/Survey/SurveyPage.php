<?php

class SurveyPage {

    var $id;
    var $tabs;

    // Constructs the tabbed page, using the given id
    function SurveyPage($id="tabs"){
        global $wgOut;
        
        $this->id = $id;
        
        $this->tabs = array();
    }
    
    // Adds the given tab to the page
    function addTab($tab){
        $this->tabs[] = $tab;
    }
    

    function getTabContent($tab_id, $validate){
        foreach($this->tabs as $tab){
            if($tab->id == $tab_id){
                $tab->warnings = $validate;
                $tab->generateBody();
                return $tab->html;
            }
        }
        //else
        return "No tab found!";
    }

    /*
    function saveTab($tab_id){
        foreach($this->tabs as $tab){
            if($tab->id == $tab_id){
                $errors = $tab->handleEdit();
                if($errors === false){
                    return $errors;
                }
                else if($errors != null && $errors != ""){
                    return $errors;
                }
                else{
                    return "Updated successfully";
                }
            }
        }
        //else
        return "No tab found!";
    }
    */

    // Writes all of the html
    function showPage($init_tab = 0){
        global $wgOut, $wgServer, $wgScriptPath, $wgTitle, $wgMessage, $wgStylePath;

        //Handle Submit
        $active_tab = $init_tab;
        //echo $active_tab;
        $activeTabIndex = "";

        $i = 0;
        foreach($this->tabs as $tab){
            if(isset($_POST['submit']) && $_POST['submit'] == "{$tab->name}"){
                $activeTabIndex = $tab->id;
                $active_tab = (isset($_POST['warnings']) && $_POST['warnings'] !='')? $i : $i+1;
                
                $errors = $tab->handleEdit();
                if($errors === false){

                }
                else if($errors != null && $errors != ""){
                    $wgMessage->addError("$errors");
                }
                else{
                    $wgMessage->addSuccess("'{$tab->title}' updated successfully.");
                }
            }
            
            $i++;
        }

        
        //Save Event Info 
        if(isset($_POST['survey_event_string']) && !empty($_POST['survey_event_string'])){
            $this->saveEventInfo();
        }

        $isSubmitted = self::isSubmitted();
        if($isSubmitted){
            //$wgMessage->addInfo("Your Survey has already been submitted and cannot be modified.");
            $wgMessage->addInfo("The Survey has been closed.");
            //$wgOut->addHTML("<p style='font-size:16px; font-weight:bold; text-align:center; padding: 50px 0;'>Thank you for participating in NAVEL Survey. Your survey results have been successfully submitted!</p>");
            
            //return;
        }

        //Draw tabs
        $wgOut->addHTML("<div id='{$this->id}'>");
        $wgOut->addHTML("<div id='loadingDiv'><img width='16' height='16' src='../skins/Throbber.gif'>Loading...</div>");
        $wgOut->addHTML("<ul>");
        $completed = AbstractSurveyTab::getCompleted();
        $i=0;
        foreach($this->tabs as $tab){
            $bg = "";
            if($completed[$i] == 1){
                $bg = "<image style='vertical-align:top;' width='15px' src='/skins/cavendish/checkmark.png' alt='Done' />";
            }

            $validate = "";
            if($tab->warnings){
                $validate = "&validate=1";
            }
            $wgOut->addHTML("<li><a href='$wgServer$wgScriptPath/index.php/Special:Survey?get_tab_content={$tab->id}{$validate}'>{$bg} {$tab->title}</a></li>");
            
            $i++;
        }
        $wgOut->addHTML("</ul>");
        

        //Event Tracking Form
        $eventForm =<<<EOF
            <form id="eventTrackingForm" action='$wgServer$wgScriptPath/index.php/Special:Survey' method='post'> 
            <input type='hidden' id='survey_event_string' name='survey_event_string' value='' />
            </form>
EOF;
        $wgOut->addHTML($eventForm);

        $wgOut->addHTML("</div>");

        $last_completed = 0;
        $i=0;
        foreach($completed as $c){
            if($c){
                $last_completed = $i;
            }
            $i++;
        }

        //Disable all tabs > active_tab+1;
        $disabled = "[";
        $disabled .= implode(',', array_slice(array_keys($this->tabs), $last_completed+1)) . "]";
        //JavaScript
        $custom_js =<<<EOF
        <style type='text/css'>
        #loadingDiv {
            background-color: #F3EBF5;
            left: 360px;
            padding: 50px;
            position: absolute;
            top: 50px;
            display: none;
            z-index: 999;
        }
        </style>
        <script src='../scripts/jquery.tablesorter.js' type='text/javascript' charset='utf-8';></script>
        <script type='text/javascript'>
EOF;
        if(!$isSubmitted){
            $custom_js .= 'window.onbeforeunload=function(){ return "Please make sure you have saved all your content before leaving the Survey!"};';
        }
        $custom_js .=<<<EOF
            $('#loadingDiv')
                .hide()  // hide it initially
                .ajaxStart(function() {
                    $(this).show();
                })
                .ajaxStop(function() {
                    $(this).hide();
            });
            
            function toggleChecked(status, selector){
                $(selector).each( function() {
                    $(this).attr("checked",status);
                });
            }
            var selectedTab = $('#{$this->id} .ui-tabs-selected');
            if(selectedTab.length > 0){
                // make sure to reselect the same tab as before
                var i = 0;
                $.each($('#{$this->id} li.ui-state-default'), function(index, val){
                    if($(val).hasClass('ui-tabs-selected')){
                        i = index;
                    }
                });

                $('#{$this->id}').tabs({ 
EOF;

        if(!$isSubmitted){
                $custom_js .=<<<EOF
                    select: function(event, ui) {
                        return confirm("You will lose any unsaved content if you navigate away from this section right now. Click OK to continue or Cancel to stay on this section.");
                    },
EOF;
        }
        $custom_js .=<<<EOF
                    ajaxOptions: {
                        success: function( xhr, status, index, anchor) { $(".ui-tabs-hide").empty(); },
                        error: function( xhr, status, index, anchor ) {
                            $( anchor.hash ).html(
                            "Something went wrong and we could not get the contents of this tab. Please reload the page and try again. If the problem persists, please contact support@forum.grand-nce.ca.");
                        },
                        cache: false
            
                    }, 
                    selected: i });
            }
            else{
                $('#{$this->id}').tabs({
EOF;

        if(!$isSubmitted){
            $custom_js .=<<<EOF
                select: function(event, ui) {
                    return confirm("You will lose any unsaved content if you navigate away from this section right now. Click OK to continue or Cancel to stay on this section.");
                },
EOF;
        }
        $custom_js .=<<<EOF
                    ajaxOptions: {
                        success: function( xhr, status, index, anchor) { $(".ui-tabs-hide").empty(); },
                        error: function( xhr, status, index, anchor ) {
                            $( anchor.hash ).html(
                            "Something went wrong and we could not get the contents of this tab. Please reload the page and try again. If the problem persists, please contact support@forum.grand-nce.ca.");
                        },
                        cache: false
                    }, 
                    selected: {$active_tab}, 
                    disabled: {$disabled} });
            }

            //// Event Tracking Code ////
            function saveEventInfo(){
                event_str = $("#survey_event_string").val();
                $.ajax({
                        type: 'POST',
                        url: "$wgServer$wgScriptPath/index.php/Special:Survey", 
                        data: { "survey_event_string": event_str },
                        async: false
                    });
            }
            //$(document).ready(function(){
            function addEventTracking(){
                $("a, input, button, th.tablesorter-header").click(function(event) {
                    timest = event.timeStamp;
                    target = escape(event.currentTarget.outerHTML);
                    //grandParent = escape(event.currentTarget.offsetParent.offsetParent.outerHTML);
                    event_json = '{"timestamp":"'+timest+'", "currentTarget":"'+target+'"}';
                    
                    event_string = $("#survey_event_string").val();
                    if(event_string == ""){
                        event_string = event_json;
                    }
                    else{
                        event_string += ","+event_json;
                        
                    }
                    $("#survey_event_string").val(event_string);

                });  
                 $("select, textarea").change(function(event) {
                    timest = event.timeStamp;
                    target = escape(event.currentTarget.outerHTML);
                    //grandParent = escape(event.currentTarget.offsetParent.offsetParent.outerHTML);
                    event_json = '{"timestamp":"'+timest+'", "currentTarget":"'+target+'"}';
                    
                    event_string = $("#survey_event_string").val();
                    if(event_string == ""){
                        event_string = event_json;
                    }
                    else{
                        event_string += ","+event_json;
                        
                    }
                    $("#survey_event_string").val(event_string);
                });
            }
            //});
            
        </script>
EOF;
        $wgOut->addHTML($custom_js);
    }

    static function isSubmitted(){
        global $wgUser;
        $my_id = $wgUser->getId();

        /*
        $sql = "SELECT submitted FROM survey_results WHERE user_id='{$my_id}'";
        $data = DBFunctions::execSQL($sql);

        if(isset($data[0]) && $data[0]['submitted'] == 1){
            return true;
        }
        else{
            return false;
        }
        */
        return true;
        
    }

    function saveEventInfo(){
        global $wgUser;
        $user_id = $wgUser->getId();

        $event_string = (isset($_POST['survey_event_string']))? $_POST['survey_event_string'] : "";

        if(empty($event_string)){
            return;
        }
        $sql = "INSERT INTO survey_events(user_id, event_info) VALUES('{$user_id}', '{$event_string}')";
        $data = DBFunctions::execSQL($sql, true);
    }

}

?>
