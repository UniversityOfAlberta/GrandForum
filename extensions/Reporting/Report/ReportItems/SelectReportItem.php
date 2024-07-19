<?php

class SelectReportItem extends AbstractReportItem {

	function render(){
		global $wgOut;
        $options = $this->parseOptions();
        if($this->getAttr('labels', '') == ""){
            $labels = array();
        }
        else {
            $labels = explode("|", $this->getAttr('labels', ''));
        }
        $value = $this->getBlobValue();
        $default = $this->getAttr('default', '');
        $placeholder = $this->getAttr('placeholder', '');
		if($value === "" && $default != ''){
		    $value = $default;
		}
        $width = $this->getAttr("width", "150px");
        $items = array();
        $found = ($value == "");
		foreach($options as $key => $option){
		    $selected = "";
		    $disabled = "";
		    if($value == $option){
		        $selected = "selected";
		        $found = true;
		    }
		    if($found && $placeholder != "" && $option == ""){
		        $labels[$key] = $placeholder;
		        $disabled = "disabled";
		    }
		    else{
		        $option = str_replace("'", "&#39;", $option);
		    }
		    if(isset($labels[$key])){
		        $items[] = "<option value='{$option}' $selected $disabled>{$labels[$key]}</option>";
		    }
		    else {
		        $items[] = "<option value='{$option}' $selected $disabled>{$option}</option>";
		    }
		}
		if(!$found){
		    $value = htmlentities($value);
		    $items[] = "<option value='{$value}' selected>{$value}</option>";
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
