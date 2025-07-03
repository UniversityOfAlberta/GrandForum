<?php

class SingleCheckboxReportItem extends CheckboxReportItem {

	function render(){
		global $wgOut;
        $options = $this->parseOptions();
        $labels = $this->parseLabels();
        $value = $this->getBlobValue();
        $limit = $this->getAttr('limit', 0);
        $items = array();
		foreach($options as $key => $option){
		    $checked = "";
		    if($option == $value){
		        $checked = "checked='checked'";
		    }
		    $option = str_replace("'", "&#39;", $option);
		    $items[] = "<div style='display:table;padding-top:2px;'><input style='vertical-align:top;transform-origin:top;' type='checkbox' name='{$this->getPostId()}' value='{$option}' $checked />&nbsp;<div style='display:table-cell;'>{$labels[$key]}</div></div>";
		}

        $output = "";
        $orientation = $this->getAttr('orientation', 'vertical');
        if($orientation == 'vertical'){
            $output = implode("\n", $items);
        }
        else if($orientation == 'horizontal'){
            $output = implode("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $items);
        }
        $output = "<input type='hidden' name='{$this->getPostId()}' value='' />".$output;
        $output = $this->processCData("<div>{$output}</div>");
		$wgOut->addHTML($output);
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $item = $this->processCData("<i>{$this->getBlobValue()}</i>");
		$wgOut->addHTML($item);
	}
}

?>
