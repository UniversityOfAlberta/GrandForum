<?php

class TextReportItem extends AbstractReportItem {
    
    function render(){
        global $wgOut;
        $value = $this->getBlobValue();
        $width = (isset($this->attributes['width'])) ? $this->attributes['width'] : "150px";
        $editable = $this->getAttr('editable', "true");
        $value = str_replace("'", "&#39;", $value);
        if ($editable == "true"){
            $item = "<input type='text' name='{$this->getPostId()}' style='width:{$width};' value='{$value}' />";
        } else {
            $item = "<input type='text' name='{$this->getPostId()}' style='width:{$width};' value='{$value}' readonly/>";
        }
        $item = $this->processCData($item);
        $wgOut->addHTML("$item");
    }
    
    function renderForPDF(){
        global $wgOut;
        $item = $this->processCData($this->getBlobValue());
        $wgOut->addHTML($item);
    }

}

?>
