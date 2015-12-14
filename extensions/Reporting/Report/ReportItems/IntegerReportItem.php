<?php

class IntegerReportItem extends TextReportItem {
	
	function render(){
		global $wgOut;
		$min = $this->getAttr('min', 0);
		$max = $this->getAttr('max', 1000000000);
		$value = $this->getBlobValue();
		$width = $this->getAttr('width', '150px');
		$align = $this->getAttr('align', 'right');
		$item = "<input type='text' name='{$this->getPostId()}' style='width:{$width};text-align:{$align};' value='{$value}' />";
		$item = $this->processCData($item);
		$wgOut->addHTML("$item");
		$wgOut->addHTML("<script type='text/javascript'>
		    $('input[name={$this->getPostId()}]').forceNumeric({min: $min, max: $max});
		</script>");
	}
}

?>
