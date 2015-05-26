<?php

class UploadPDFReportItem extends UploadReportItem {
    
    function renderForPDF(){
        global $wgOut;
        $md5 = $this->getMD5();
        if($md5 != ""){
            PDFGenerator::attachPDF($md5);
        }
    }

}

?>
