<?php

class RangeSliderReportItem extends TextReportItem {
	
	function render(){
		global $wgOut;
		$min = $this->getAttr('min', 0);
		$max = $this->getAttr('max', 100);
		$step = $this->getAttr('step', 1);
		$width = $this->getAttr('width', '250px');
		$align = $this->getAttr('align', 'right');
		$size = $this->getAttr('size', '');
		$decimals = $this->getAttr('decimals', 0);
		$font = "";
        $orientation = $this->getAttr('orientation', 'horizontal');
        $value = $this->getBlobValue();
        $default = $this->getAttr('default', '');
		if($value === null && $default != ''){
		    $value = $default;
		}

		if($size != ''){
		    $width = '';
		    $font = "font-family: monospace;";
		}
		$item = "<div style='display: flex;'>
		    <div class='label-health'>YOUR HEALTH TODAY=<br /> <span id='value'></span></div>";
		if($orientation == "vertical"){
			$item .= "
            <div class='slider-wrapper'>	
	            <div class='label-top-slider'>The best health you can imagine</div>	 
	            <div style='height:{$width}; width: 150px;'>
	                <input type='range' id='{$this->getPostId()}' class='slider' name='{$this->getPostId()}' size='$size' style='{$font}width:{$width};text-align:{$align};max-width: initial;' step='{$step}' min='{$min}' max='{$max}' value='{$value}' list='tickmarks' />
                    <datalist id='tickmarks'>
                    <option value='0' label='0%'></option>
                    <option value='10'></option>
                    <option value='20'></option>
                    <option value='30'></option>
                    <option value='40'></option>
                    <option value='50' label='50%'></option>
                    <option value='60'></option>
                    <option value='70'></option>
                    <option value='80'></option>
                    <option value='90'></option>
                    <option value='100' label='100%'></option>
                    </datalist>
                </div>
                <div class='label-bottom-slider'>The worst health you can imagine</div>
            </div>";
		}
		else{
            $item .= "<div>
                <input type='range' id='{$this->getPostId()}' class='slider' name='{$this->getPostId()}' size='$size' style='{$font}width:{$width};text-align:{$align};' step='{$step}' min='{$min}' max='{$max}' value='{$value}' />
            </div>";
        }
        $item .= "</div>";
		$item = $this->processCData($item);
		$wgOut->addHTML("$item");
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
