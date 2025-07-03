<?php

class BookmarkReportSection extends AbstractReportSection {
    
    // Creates a new ReportSection (not editable)
    function __construct(){
        parent::__construct();
        $this->setPageBreak(false);
    }
    
    function render(){
    
    }
    
    function renderForPDF(){
    
    }
    
}

?>
