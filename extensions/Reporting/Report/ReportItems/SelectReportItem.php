<?php

class SelectReportItem extends AbstractReportItem {

	function render(){
		global $wgOut;
        $options = $this->parseOptions();
        $value = $this->getBlobValue();
        $default = $this->getAttr('default', '');
		if($value === "" && $default != ''){
		    $value = $default;
		}
        $width = $this->getAttr("width", "150px");
        $items = array();
		foreach($options as $option){
		    $selected = "";
		    if($value == $option){
		        $selected = "selected";
		    }
		    $option = str_replace("'", "&#39;", $option);
		    $items[] = "<option value='{$option}' $selected >{$option}</option>";
		}

        $output = "<select style='width:{$width};' name='{$this->getPostId()}'>".implode("\n", $items)."</select>";
        
        $output = $this->processCData("<div style='display:inline-block;'>{$output}</div>");
		$wgOut->addHTML($output);
	}
	
	function parseOptions(){
	    $options = @explode("|", $this->attributes['options']);
	    return $options;
	}
	
	function getBlobValue(){
	    $value = parent::getBlobValue();
	    if($value == ""){
	        $options = $this->parseOptions();
	        return @$options[0];
	    }
	    return $value;
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $attr = strtolower($this->getAttr("onlyShowIfNotEmpty"));
	    $val = $this->getBlobValue();
	    if($attr == "true" && empty($val)){
	        return "";
	    }
	    else if(empty($val)){
	    	$val = "N/A";
	    }

	    $item = $this->processCData("<i>{$val}</i>");
		$wgOut->addHTML($item);
	}
}

?>
