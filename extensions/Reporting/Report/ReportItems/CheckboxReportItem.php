<?php

class CheckboxReportItem extends AbstractReportItem {

	function render(){
		global $wgOut;
        $options = $this->parseOptions();
        $value = $this->getBlobValue();
        $limit = $this->getAttr('limit', 0);
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
		    $items[] = "<div style='display:table;padding-bottom:1px;padding-top:1px;'><input style='vertical-align:top;' type='checkbox' name='{$this->getPostId()}[]' value='{$option}' $checked />&nbsp;<div style='display:table-cell;'>{$option}</div></div>";
		}

        $output = "";
        $orientation = $this->getAttr('orientation', 'vertical');
        if($orientation == 'vertical'){
            $output = implode("\n", $items);
        }
        else if($orientation == 'horizontal'){
            $output = implode("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $items);
        }
        $output = "<input type='hidden' name='{$this->getPostId()}[]' value='' />".$output;
        if($limit > 0){
            $output .= "<script type='text/javascript'>
                $(\"[name='{$this->getPostId()}[]']\").change(function(){
                    if($(\"[name='{$this->getPostId()}[]']:checked\").length > $limit){
                        // Just in case
                        this.checked = false;
                    }
                    if($(\"[name='{$this->getPostId()}[]']:checked\").length >= $limit){
                        $(\"[name='{$this->getPostId()}[]']:not(:checked)\").prop('disabled', true);
                    } 
                    else{
                        $(\"[name='{$this->getPostId()}[]']:not(:checked)\").prop('disabled', false);
                    }
                }).change();
            </script>";
        } 
        $output = $this->processCData("<div>{$output}</div>");
		$wgOut->addHTML($output);
	}
	
	function parseOptions(){
	    $options = @explode("|", $this->attributes['options']);
	    return $options;
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
