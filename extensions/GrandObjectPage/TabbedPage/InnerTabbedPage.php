<?php

class InnerTabbedPage extends TabbedPage {

    var $html;

    function __construct($id="tabs"){
        parent::__construct($id);
        $this->html = "";
    }
    
    function showPage($init_tab = 0){
        $active_tab = $init_tab;
        $activeTabIndex = "";
        $i = 0;
        
        foreach($this->tabs as $tab){
            if(isset($_GET['tab']) && $_GET['tab'] == $tab->id){
                $activeTabIndex = $tab->id;
                $active_tab = $i;
            }
            $i++;
        }
        
        $this->html .= "<div style='display:none;' id='{$this->id}'><ul>";
        foreach($this->tabs as $tab){
            if($tab instanceof AbstractEditableTab){
                if($tab->canEdit() && isset($_POST['edit'])){
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
                $this->html .= "<li><a href='#{$this->id}_{$tab->id}'>{$tab->name}</a></li>";
            }
        }
        $this->html .= "</ul>";
        $i = 0;
        foreach($this->tabs as $tab){
            if($tab->html != ""){
                $this->html .= "<div id='{$this->id}_{$tab->id}'>{$tab->html}</div>";
                if($tab->id == $activeTabIndex){
                    $active_tab = $i;
                }
                $i++;
            }
        }
        $this->html .= "</div>";
        $this->html .= "<script type='text/javascript'>
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
                $('#{$this->id}').show();
        </script>";
        return $this->html;
    }

}

?>
