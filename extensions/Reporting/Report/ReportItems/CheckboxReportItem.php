<?php

class CheckboxReportItem extends AbstractReportItem {

	function render(){
		global $wgOut;
        $options = $this->parseOptions();
        $value = $this->getBlobValue();
        if(is_array($value)){
            $value = array_filter($value);
        }
        $items = array();
		foreach($options as $option){
		    $checked = "";
		    if(is_array($value) && array_search($option, $value) !== false){
		        $checked = "checked='checked'";
		    }
		    $option = str_replace("'", "&#39;", $option);
		    $items[] = "<input style='vertical-align:top;' type='checkbox' name='{$this->getPostId()}[]' value='{$option}' $checked />&nbsp;{$option}";
		}

        $output = "";
        $orientation = $this->getAttr('orientation', 'vertical');
        if($orientation == 'vertical'){
            $output = implode("<br />\n", $items);
        }
        else if($orientation == 'horizontal'){
            $output = implode("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $items);
        }
        $output = "<input type='hidden' name='{$this->getPostId()}[]' value='' />".$output; 
        $output = $this->processCData("<div>{$output}</div>");
		$wgOut->addHTML($output);
	}
	
	function parseOptions(){
	    $options = @explode("|", $this->attributes['options']);
	    return $options;
	}
	
	function parseLabels(){
	    $options = @explode("|", $this->attributes['labels']);
	    return $options;
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $attr = strtolower($this->getAttr("onlyShowIfNotEmpty"));
	    $val = $this->getBlobValue();
        if(is_array($val)){
            $value = array_filter($val);
        }
	    if($attr == "true" && empty($val)){
	        return "";
	    }
	    else if(empty($val)){
	    	$val = array("N/A");
	    }

	    $item = $this->processCData("<i>".implode(", ", $val)."</i>");
		$wgOut->addHTML($item);
	}
}

?>
