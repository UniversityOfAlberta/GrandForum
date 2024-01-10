<?php

class TextReportItem extends AbstractReportItem {
    
    function render(){
        global $wgOut;
        $value = $this->getBlobValue();
		$default = $this->getAttr('default', '');
		$placeholder = $this->getAttr('placeholder', '');
		if($value === null && $default != ''){
		    $value = $default;
		}
        $width = (isset($this->attributes['width'])) ? $this->attributes['width'] : "150px";
        $value = str_replace("'", "&#39;", $value);
        $placeholder = str_replace("'", "&#39;", $placeholder);
        $item = "<input type='text' name='{$this->getPostId()}' style='width:{$width};' placeholder='{$placeholder}' value='{$value}' />";
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
