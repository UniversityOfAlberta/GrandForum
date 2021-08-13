<?php

class UploadPDFReportItem extends UploadReportItem {
    
    function render(){
        global $wgOut;
        $data = $this->getBlobValue();
        $link = $this->getDownloadLink();
        $html = "";
        if($data !== null && $data != ""){
            $json = json_decode($data);
            $name = $json->name;
            $html = "<p><a class='externalLink' href='{$link}'>Download&nbsp;<b>{$name}</b></a></p><br />";
        }
        $item = $this->processCData($html);
        $wgOut->addHTML($item);
    }
    
    function renderForPDF(){
        global $wgOut;
        $md5 = $this->getMD5(false);
        if($md5 != ""){
            PDFGenerator::attachPDF($md5);
        }
    }

}

?>
