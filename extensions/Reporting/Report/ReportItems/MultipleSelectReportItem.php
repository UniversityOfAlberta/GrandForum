<?php

class MultipleSelectReportItem extends SelectReportItem {

	function render(){
		global $wgOut;
        $options = $this->parseOptions();
        $value = $this->getBlobValue();
        $max_options = $this->getAttr('max', '0');
        $width = (isset($this->attributes['width'])) ? $this->attributes['width'] : "150px";
        $items = array();
		foreach($options as $key => $option){
		    $selected = "";
		    if (is_array($value) && in_array($option, $value)) {
		        $selected = "selected";
		    }
		    $option = str_replace("'", "&#39;", $option);
		    $items[] = "<option value='{$option}' $selected >{$option}</option>";
		}

        $output = "<input type='hidden' name='{$this->getPostId()}[]' /><select id='{$this->getPostId()}' style='width:{$width};' name='{$this->getPostId()}[]' multiple>".implode("\n", $items)." </select>
        <script type='text/javascript'>
            $('select#{$this->getPostId()}').chosen({ max_selected_options: {$max_options} });
        </script>";
        
        $output = $this->processCData("<div>{$output}</div>");
		$wgOut->addHTML($output);
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $delimiter = $this->getAttr("delimiter", ", ");
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

	    $item = $this->processCData("<i>".implode($delimiter, $val)."</i>");
		$wgOut->addHTML($item);
	}
}

?>
