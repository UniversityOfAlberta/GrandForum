<?php

class RangeSliderReportItem extends TextReportItem {
	
	function render(){
		global $wgOut;
		$min = $this->getAttr('min', 0);
		$max = $this->getAttr('max', 100);
		$step = $this->getAttr('step', 1);
		$value = $this->getBlobValue();
		$width = $this->getAttr('width', '350px');
		$align = $this->getAttr('align', 'right');
		$size = $this->getAttr('size', '');
		$decimals = $this->getAttr('decimals', 0);
		$font = "";
		if($size != ''){
		    $width = '';
		    $font = "font-family: monospace;";
		}
		
		$item = "{$min} <input type='range' id='{$this->getPostId()}' class='slider' name='{$this->getPostId()}' size='$size' style='{$font}width:{$width};text-align:{$align};' step='{$step}' min='{$min}' max='{$max}' value='{$value}' /> {$max}";
		$item = $this->processCData($item);
		$wgOut->addHTML("$item");
		$wgOut->addHTML("<p>Value: <span id='value'></span></p>");
		$wgOut->addHTML("<script>
		var slider = document.getElementById('{$this->getPostId()}');
                var output = document.getElementById('value');
                output.innerHTML = slider.value;
                slider.oninput = function() {
                	output.innerHTML = this.value;
		}
		</script>");
	}
}

?>
