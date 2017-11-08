<?php

require_once("TabbedAjaxPage.php");
require_once('AbstractTab.php');
require_once('InnerTabbedPage.php');
require_once('AbstractEditableTab.php');

class TabbedPage {

    var $id;
    var $tabs;

    // Constructs the tabbed page, using the given id
    function TabbedPage($id="tabs"){
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

}

?>
