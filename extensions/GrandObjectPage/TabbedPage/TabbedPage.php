<?php

require_once('AbstractTab.php');
require_once('InnerTabbedPage.php');
require_once('AbstractEditableTab.php');

class TabbedPage {

    var $id;
    var $tabs;
    var $singleHeader;

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
        $this->singleHeader = true;
        $this->tabs = array();
    }
    
    // Adds the given tab to the page
    function addTab($tab){
        $this->tabs[] = $tab;
    }
    
    // Writes all of the html
    function showPage($init_tab = 0){
        global $wgOut, $wgServer, $wgScriptPath, $wgTitle, $wgMessage, $config;
        $me = Person::newFromWgUser();
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
                if(isset($_POST['submit']) && ($_POST['submit'] == "Edit {$tab->name}" || 
                                               $_POST['submit'] == "{$tab->name}" || 
                                               ($_POST['submit'] == "Cancel") && @$_POST['cancel'] == "{$tab->name}")){
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
        $pdftabs = @explode(",", $_GET['tab']);
        foreach($this->tabs as $tab){
            if(isset($_GET['generatePDF']) && $me->isLoggedIn()){
                if($tab->canGeneratePDF()){
                    if(@$_GET['tab'] == "" || array_search($tab->id, $pdftabs) !== false){
                        $tab->generatePDFBody();
                    }
                }
                continue;
            }
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
                $wgOut->addHTML("<li><a href='#{$tab->id}'>{$tab->name}</a></li>");
            }
        }
        if(isset($_GET['generatePDF']) && $me->isLoggedIn()){
            $pdfHtml = "<div style='font-size:150%;'>{$wgOut->getPageTitle()}</div><br />";
            $firstTab = true;
            $persObj = null;
            $projObj = null;
            $fileName = "";
            foreach($this->tabs as $tab){
                if(isset($tab->project)){
                    $projObj = $tab->project;
                    $fileName = "{$projObj->getName()}.pdf";
                }
                else if(isset($tab->person)){
                    $persObj = $tab->person;
                    $fileName = "{$pers->getName()}.pdf";
                }
                if($tab->html != ""){
                    if(!$firstTab){
                        $pdfHtml .= "<div style='page-break-after:always;'></div>";
                    }
                    $pdfHtml .= "<h1>{$tab->name}</h1>";
                    $pdfHtml .= $tab->html;
                    $firstTab = false;
                }
            }
            $pdfHtml = "<img style='position: fixed; height:75px; bottom:-75px; left:0; opacity: 1; z-index:-1;' src='{$wgServer}{$wgScriptPath}/skins/{$config->getValue('networkName')}_Logo.png' />{$pdfHtml}";
            $pdfHtml = str_replace("class='smallest dashboard", "class='smallest dashboard", $pdfHtml);
            $pdfHtml = str_replace("<td class='smallest'>", "<td class='smallest'>", $pdfHtml);
            $pdf = PDFGenerator::generate($wgOut->getPageTitle(), $pdfHtml, "", $persObj, $projObj, isset($_GET['preview']), null, false, 'landscape');
            if(!isset($_GET['preview'])){
                $len = strlen($pdf['pdf']);
                header("Content-Type: application/pdf");
                header('Content-Length: ' . $len);
                header('Content-Disposition: attachment; filename="'.$fileName.'"');
                header('Cache-Control: private, max-age=0, must-revalidate');
                header('Pragma: public');
                ini_set('zlib.output_compression','0');
            }
            echo $pdf['pdf'];
            exit;
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
        $wgOut->addHTML("<script type='text/javascript'>
            if($('#{$this->id} > ul li').length == 1){
                $('#{$this->id} > ul').hide();
                if(".json_encode($this->singleHeader)."){
                    $('#{$this->id}').prepend('<h1 style=\"margin-top: 0;\">' + $('#{$this->id} > ul li').text() + '</h1>');
                    $('#{$this->id} div.ui-tabs-panel').css('padding-top', 0);
                }
            }
        </script>");
    }

}

?>
