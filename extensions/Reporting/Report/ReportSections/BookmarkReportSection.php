<?php

class BookmarkReportSection extends AbstractReportSection {
    
    // Creates a new ReportSection (not editable)
    function BookmarkReportSection(){
        $this->AbstractReportSection();
        $this->setPageBreak(false);
    }
    
    function render(){
    
    }
    
    function renderForPDF(){
    
    }
    
}

?>
