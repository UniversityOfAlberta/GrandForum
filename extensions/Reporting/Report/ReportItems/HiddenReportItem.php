<?php

class HiddenReportItem extends TextReportItem {
	
	function render(){
		global $wgOut;
		$value = $this->getBlobValue();

		$default = $this->getAttr('default', '');
		if($default != ''){
		    $value = $default;
		}
		$disabled = strtolower($this->getAttr('disabled', 'false'));
		$disabled = ($disabled == 'true') ? "disabled='disabled'" : "";
		
		$item = "<input type='hidden' name='{$this->getPostId()}' value='{$value}' />";
		$item = $this->processCData($item);
		$wgOut->addHTML("$item");
	}
	
	function renderForPDF(){
	    $default = $this->getAttr('default', '');
		if($default != ''){
		    $value = $default;
		    $this->setBlobValue($value);
		}
	    parent::renderForPDF();
	}
}

?>
