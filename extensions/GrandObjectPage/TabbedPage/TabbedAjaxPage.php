<?php

class TabbedAjaxPage extends TabbedPage {

    // Constructs the tabbed page, using the given id
    function __construct($id="tabs"){
        parent::__construct($id);
    }
    
    // Writes all of the html
    function showPage($init_tab = 0){
        global $wgOut, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        $active_tab = $init_tab;
        $activeTabIndex = "";
        $i = 0;

        foreach($this->tabs as $tab){
            if(isset($_GET['showTab']) && $_GET['showTab'] == $tab->id){
                $tab->generatebody();
                echo $tab->html;
                exit;
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
            $wgOut->addHTML("<li title='".str_replace("'", "&#39;", $tab->tooltip)."' class='tooltip'><a href='$wgServer$wgScriptPath/index.php/{$wgTitle->getNsText()}:{$wgTitle->getText()}?showTab={$tab->id}'>{$tab->name}</a></li>");
        }
        $wgOut->addHTML("</ul><h1 class='custom-title'>{$wgOut->getPageTitle()}</h1>");
        $i = 0;
        foreach($this->tabs as $tab){
            if($tab->html != ""){
                $wgOut->addHTML("<div id='{$tab->id}' style='overflow-x:auto;'><span class='throbber'></span></div>");
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
                    $('#{$this->id}').tabs({
                        selected: i,
                        cache:true,
                        load: function (e, ui) {
                            $('#currentViewSpinner').remove();
                            $('#{$this->id}').show();
                        },
                        select: function(event, ui) {
                            var panel = $(ui.panel);
                            if(panel.is(':empty')) {
                                panel.append('<div class=\"throbber\"></div>');
                            }
                        }
                    });
                }
                else{
                    $('#{$this->id}').tabs({
                        selected: {$active_tab},
                        cache:true,
                        load: function (e, ui) {
                            $('#currentViewSpinner').remove();
                            $('#{$this->id}').show();
                        },
                        select: function(event, ui) {
                            var panel = $(ui.panel);
                            if(panel.is(':empty')) {
                                panel.append('<div class=\"throbber\"></div>');
                            }
                        }
                    });
                }
        </script>");
        foreach($this->tabs as $key => $tab){
            $wgOut->addHTML("<script type='text/javascript'>
                $('#{$this->id}').bind('tabsselect', function(event, ui) {
                    if(ui.panel.id == '{$tab->id}'){
                        {$tab->tabSelect()}
                    }
                });
            </script>");
        }
    }

}

?>
