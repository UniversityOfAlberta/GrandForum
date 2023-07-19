<?php

class AvoidHealthRadioReportItem extends AbstractReportItem {

	function render(){
		global $wgOut;
        $options = $this->parseOptions();
        $labels = explode("|", $this->getAttr('labels', ''));
        $value = $this->getBlobValue();
		$default = $this->getAttr('default', '');
		if($value === null && $default != ''){
		    $value = $default;
		}
        $items = array();
		foreach($options as $i => $option){
		    if(!is_array($option)){
		        $checked = "";
		        if($value == $option){
		            $checked = "checked='checked'";
		        }
		        $option = str_replace("'", "&#39;", $option);
		        if(count($labels) == count($options)){
		            $items[] = "<div class='options'><label title='{$option}'>{$labels[$i]}<input style='vertical-align:top;display:table-cell;' type='radio' name='{$this->getPostId()}' value='{$option}' $checked />&nbsp;<img /></div>";
		        }
		        else{
		            $items[] = "<div class='options'><label title='{$option}'>{$option}<input style='vertical-align:top;display:table-cell;' type='radio' name='{$this->getPostId()}' value='{$option}' $checked />&nbsp;<img /></div>";
		        }
		    }
		}

        $output = implode("\n", $items);
        if($this->getBlobValue() == ""){
            $output = "<input type='hidden' name='{$this->getPostId()}' value='' />".$output; 
        }
        $output = $this->processCData("<div>{$output}</div>");
		$wgOut->addHTML($output);
	}
	
	function parseOptions(){
	    $options = @explode("|", $this->attributes['options']);
	    foreach($options as $key => $option){
	        $subOptions = array();
	        preg_match("/^\((.*)\)$/", $option, $subOptions);
	        if(isset($subOptions[1])){
	            $subOptions = explode(",", $subOptions[1]);
	            $options[$key] = $subOptions;
	        }
	    }
	    return $options;
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $attr = strtolower($this->getAttr("onlyShowIfNotEmpty"));
	    $showDescription = strtolower($this->getAttr("showDescription"));
        $options = $this->parseOptions();
        $descriptions = explode("|", $this->getAttr('descriptions', ''));
	    $val = $this->getBlobValue();
	    if($attr == "true" && empty($val)){
	        return "";
	    }
	    else if(empty($val)){
	    	$val = "N/A";
	    }
	    
	    if($showDescription == "true"){
	        foreach($options as $i => $option){
	            if($val == $option){
	                $val .= @" - {$descriptions[$i]}";
	            }
	        }
	    }

	    $item = $this->processCData("<i>{$val}</i>");
		$wgOut->addHTML($item);
	}
}

?>
