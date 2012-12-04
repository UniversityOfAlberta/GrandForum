<?php

class SubReportSection extends AbstractReportSection {
    
    var $subReport;
    
    function SubReportSection(){
        $this->AbstractReportSection();
        $this->subReport = null;
    }
    
    // Sets the parent AbstractReport for this AbstractReportSection
    function setParent($report){
        $me = Person::newFromWgUser();
        $this->parent = $report;
        $this->subReport = new DummyReport($this->getAttr('reportType'), 
                                           $me, 
                                           $this->getParent()->project,
                                           $this->getParent()->year);
        if(!isset($_GET['section']) && count($this->getParent()->sections) == 1){
            $this->subReport->currentSection->selected = false;
        }
    }
    
    function render(){
        $this->subReport->currentSection->render();
    }
    
    function renderForPDF(){
        $this->subReport->renderForPDF();
    }
    
    function renderTab(){
        $this->subReport->renderTabs();
    }
    
    function getInstructions(){
        return $this->subReport->currentSection->getInstructions();
    }
}

?>
