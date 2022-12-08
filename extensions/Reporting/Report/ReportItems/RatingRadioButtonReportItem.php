<?php

class RatingRadioButtonReportItem extends TextReportItem {
	function render(){
		global $wgOut;
		$minnumber = $this->getAttr('minnumber', 0);
		$maxnumber = $this->getAttr('maxnumber', 10);
		$minlabel = $this->getAttr('minlabel', "Unsatisfied");
		$maxlabel = $this->getAttr('maxlabel', "Very Satisfied");
		$width = $this->getAttr('width', "800px");
        $prefnotanswer = $this->getAttr('prefnotanswer', "True");
		$value = $this->getBlobValue();
		$default = $this->getAttr('default', '');
		if($value === null && $default != ''){
		    $value = $default;
		}
        $checked = "";
		if($value == "Prefer not to answer"){
			$checked = "checked='checked'";
		}
        $item = "";
        if($prefnotanswer == "True"){
            $item = "
                <input type='radio' name='{$this->getPostId()}' id='{$this->getPostId()}Pref' value='Prefer not to answer' {$checked} required>
                <label for='{$this->getPostId()}Pref' data-scale-rate='Pref' {$checked}>Prefer not to answer</label>";
        }
		$item .= "
            <div style='width:{$width}'>
			<div class='radio-toolbar' style='text-align: center;'>
";
		if($minnumber < 0){
			$minnumber = 0;
		}
		if($maxnumber > 10){
			$maxnumber = 10;
		}
		for ($option = $minnumber; $option < $maxnumber+1; $option++) {
				$checked = "";
		        if((int)$value == $option){
		            $checked = "checked='checked'";
		        }
				$item .= "
					<input type='radio' name='{$this->getPostId()}' id='{$this->getPostId()}{$option}' value='{$option}' {$checked} required>
					<label for='{$this->getPostId()}{$option}' data-scale-rate='{$option}' {$checked}>{$option}</label>
				";
		}
		
		$item .= "		
            </div>
            <div style='display:flex;justify-content: space-around;'>
			    <div style='float:left'>{$minlabel}</div><div style='float:right'>{$maxlabel}</div>
            </div>
            </div>
				";
		$item = $this->processCData($item);
		$wgOut->addHTML("$item");
	}
}

?>
