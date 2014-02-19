<?php

class HeaderReportSection extends AbstractReportSection {
    
    // Creates a new ReportSection (not editable)
    function HeaderReportSection(){
        $this->AbstractReportSection();
    }
    
    function renderTab(){
        // do nothing;
        return;
    }
    
    function renderForPDF(){      
        //Render all the ReportItems's in the section    
        foreach ($this->items as $item){
            if(!$this->getParent()->topProjectOnly || ($this->getParent()->topProjectOnly && !$item->private)){
                if(!$item->deleted){
                    $item->renderForPDF();
                }
            }
        }
    }
    
}

?>
