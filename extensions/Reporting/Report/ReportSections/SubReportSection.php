<?php

class SubReportSection extends AbstractReportSection {
    
    var $subReport;
    
    function __construct(){
        parent::__construct();
        $this->subReport = null;
    }
    
    // Sets the parent AbstractReport for this AbstractReportSection
    function setParent($report){
        $me = Person::newFromWgUser();
        $this->parent = $report;
        if($this->getParent()->xmlName == $this->getAttr('reportType')){
            // Prevent infinite recursion
            return;
        }
        $this->subReport = new DummyReport($this->getAttr('reportType'), 
                                           $me, 
                                           $this->getParent()->project,
                                           $this->getParent()->year);
        $this->subReport->currentSection->selected = false;
    }
    
    function render(){
        if($this->subReport != null){
            $this->subReport->currentSection->render();
        }
    }
    
    function renderForPDF(){
        if($this->subReport != null){
            $this->subReport->renderForPDF();
        }
    }
    
    function renderTab(){
        if((!isset($_GET['section']) && $this->getParent()->currentSection == $this) ||
           (isset($_GET['section']) && $this->subReport->currentSection->name == $_GET['section'])){
            $this->subReport->currentSection->selected = true;
        }
        
        if($this->subReport != null){
            $this->subReport->renderTabs();
        }
    }
    
    function getInstructions(){
        if($this->subReport != null){
            return $this->subReport->currentSection->getInstructions();
        }
        return "";
    }
}

?>
