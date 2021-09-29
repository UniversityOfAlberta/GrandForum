<?php

class EliteSubmitReportItem extends AbstractReportItem {
    
    function EliteSubmitReportItem(){
        self::AbstractReportItem();
        $this->setAttr("optional", "true");
    }
    
    function render(){
        // DO NOTHING
    }
    
    function renderForPDF(){
        global $wgOut;
        if(isset($_GET['generatePDF']) && !isset($_GET['preview'])){
            $this->setAttr("blobReport", $this->getReport()->reportType);
            $this->setAttr("blobSection", "PROFILE");
            $this->blobItem = "STATUS";
            if($this->getBlobValue() == "Requested More Info" || 
               $this->getBlobValue() == "Submitted More Info"){
                $this->setBlobValue("Submitted More Info");
            }
            else {
                $this->setBlobValue("Submitted");
            }
        }
    }

}

?>
