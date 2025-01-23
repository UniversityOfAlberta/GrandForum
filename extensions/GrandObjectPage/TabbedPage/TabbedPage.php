<?php

require_once("TabbedAjaxPage.php");
require_once('AbstractTab.php');
require_once('InnerTabbedPage.php');
require_once('AbstractEditableTab.php');

class TabbedPage {

    var $id;
    var $tabs;

    // Constructs the tabbed page, using the given id
    function __construct($id="tabs"){
        global $wgOut;
        $wgOut->addHTML("<style type='text/css'>
            #bodyContent > h1:first-child {
                display: none;
            }
            
            #contentSub {
                display: none;
            }
        </style>");
        $this->id = $id;
        
        $this->tabs = array();
    }
    
    // Adds the given tab to the page
    function addTab($tab){
        $this->tabs[] = $tab;
    }
    
    // Writes all of the html
    function showPage($init_tab = 0){
        global $wgOut, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        $active_tab = $init_tab;
        $activeTabIndex = "";
        $i = 0;

        foreach($this->tabs as $tab){
            if($tab instanceof AbstractEditableTab && $tab->canEdit()){
                if(isset($_POST['submit']) && 
                   ($_POST['submit'] == "Save {$tab->name}" || ($tab instanceof AbstractInlineEditableTab && $_POST['submit'] == "{$tab->name}"))){
                    $activeTabIndex = $tab->id;
                    $active_tab = $i;
                    $errors = $tab->handleEdit();
                    if($errors != null && $errors != ""){
                        $wgMessage->addError("$errors");
                        $_POST['submit'] = "Edit {$tab->name}";
                        $_POST['edit'] = true;
                    }
                    else{
                        $wgMessage->addSuccess("'{$tab->name}' updated successfully.");
                    }
                }
                if(isset($_POST['submit']) && ($_POST['submit'] == "Edit {$tab->name}" || $_POST['submit'] == "{$tab->name}" || $_POST['submit'] == "Cancel" ) ) {
                    $activeTabIndex = $tab->id;
                    $active_tab = $i;
                }
            }
            if(isset($_GET['tab']) && $_GET['tab'] == $tab->id){
                $activeTabIndex = $tab->id;
                $active_tab = $i;
            }
            $i++;
        }
        
        $wgOut->addHTML("<form action='$wgServer$wgScriptPath/index.php/{$wgTitle->getNsText()}:{$wgTitle->getText()}' method='post' enctype='multipart/form-data'>
            <div id='currentViewSpinner' style='text-align:center;margin-top:10%;'></div>
            <script type='text/javascript'>
                spin = spinner('currentViewSpinner', 40, 75, 12, 10, '#888');
            </script>");
        $wgOut->addHTML("<div style='display:none;' id='{$this->id}'>");
        $wgOut->addHTML("<ul>");
        foreach($this->tabs as $tab){
            if($tab instanceof AbstractEditableTab){
                if($tab->canEdit() && isset($_POST['edit']) && $tab->id == $activeTabIndex){
                    $tab->generateEditBody();
                    $tab->showSaveButton();
                    $tab->showCancelButton();
                }
                else if($tab->canEdit() && !isset($_POST['edit'])){
                    $tab->generateBody();
                    $tab->showEditButton();
                }
                else{
                    $tab->generateBody();
                }
            }
            else{
                $tab->generateBody();
            }
            if($tab->html != ""){
                $wgOut->addHTML("<li title='".str_replace("'", "&#39;", $tab->tooltip)."' class='tooltip'><a href='#{$tab->id}'>{$tab->name}</a></li>");
            }
        }
        $wgOut->addHTML("</ul><h1 class='custom-title'>{$wgOut->getPageTitle()}</h1>");
        $i = 0;
        foreach($this->tabs as $tab){
            if($tab->html != ""){
                $wgOut->addHTML("<div id='{$tab->id}' style='overflow-x:auto;'>{$tab->html}</div>");
                if($tab->id == $activeTabIndex){
                    $active_tab = $i;
                }
                $i++;
            }
        }
        $wgOut->addHTML("</div>\n</form>");
        $wgOut->addHTML("<script type='text/javascript'>
                var selectedTab = $('#{$this->id} > ul > .ui-tabs-selected');
                if(selectedTab.length > 0){
                    // If the tabs were created previously but removed from the dome, 
                    // make sure to reselect the same tab as before
                    var i = 0;
                    $.each($('#{$this->id} li.ui-state-default'), function(index, val){
                        if($(val).hasClass('ui-tabs-selected')){
                            i = index;
                        }
                    });
                    $('#{$this->id}').tabs({ selected: i });
                }
                else{
                    $('#{$this->id}').tabs({ selected: {$active_tab} });
                }
                $('#currentViewSpinner').remove();
                $('#{$this->id}').show();
        </script>");
        foreach($this->tabs as $key => $tab){
            $wgOut->addHTML("<script type='text/javascript'>
                $('#{$this->id}').bind('tabsselect', function(event, ui) {
                    if(ui.panel.id == '{$tab->id}'){
                        {$tab->tabSelect()}
                    }
                });
            </script>");
            if(isset($_POST['edit']) && $_POST['edit'] == true){
                $wgOut->addHTML("<script type='text/javascript'>
                    $('#{$this->id}').tabs('disable', {$key});
                </script>");
            }
        }
    }

}

?>
