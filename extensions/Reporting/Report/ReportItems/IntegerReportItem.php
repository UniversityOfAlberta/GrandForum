<?php

class IntegerReportItem extends TextReportItem {
	
	function render(){
		global $wgOut;
		$min = $this->getAttr('min', 0);
		$max = $this->getAttr('max', 1000000000);
		$value = $this->getBlobValue();
		$width = $this->getAttr('width', '150px');
		$align = $this->getAttr('align', 'right');
		$size = $this->getAttr('size', '');
		$decimals = $this->getAttr('decimals', 0);
		
		if($size != ''){
		    $width = '';
		    $font = "font-family: monospace;";
		}
		
		$item = "<input type='text' name='{$this->getPostId()}' size='$size' style='{$font}width:{$width};text-align:{$align};' value='{$value}' />";
		$item = $this->processCData($item);
		$wgOut->addHTML("$item");
		$wgOut->addHTML("<script type='text/javascript'>
		    $('input[name={$this->getPostId()}]').forceNumeric({min: $min, max: $max, decimals: $decimals});
		</script>");
	}
}

?>
