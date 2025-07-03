<?php

class CheckboxReportItem extends AbstractReportItem {

	function render(){
		global $wgOut;
        $options = $this->parseOptions();
        $labels = $this->parseLabels();
        $value = $this->getBlobValue();
        $limit = $this->getAttr('limit', 0);
        if(is_array($value)){
            $value = array_filter($value);
        }
        $items = array();
		foreach($options as $key => $option){
		    $checked = "";
		    if(is_array($value) && array_search($option, $value) !== false){
		        $checked = "checked='checked'";
		    }
		    $option = str_replace("'", "&#39;", $option);
		    $items[] = "<div style='display:table;padding-top:2px;'><input style='vertical-align:top;transform-origin:top;' type='checkbox' name='{$this->getPostId()}[]' value='{$option}' $checked />&nbsp;<div style='display:table-cell;'>{$labels[$key]}</div></div>";
		}

        $output = "";
        $orientation = $this->getAttr('orientation', 'vertical');
        $descriptions = explode("|", $this->getAttr('descriptions', ''));
        if($orientation == 'vertical' && count($descriptions) != count($items)){
            $output = implode("\n", $items);
        }
        else if($orientation == 'horizontal' && count($descriptions) != count($items)){
            $output = implode("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $items);
        }
        else if($orientation == 'vertical' && count($descriptions) == count($items)){
            $output = "<table class='wikitable'>";
            foreach($items as $i => $item){
                $output .= @"<tr><td style='white-space:nowrap;'><b>{$item}</b></td><td>{$descriptions[$i]}</td></tr>";
            }
            $output .= "</table>";
        }
        else if($orientation == 'horizontal' && count($descriptions) == count($items)){
            $width = 1/count($descriptions)*100;
            $output = "<table class='wikitable'>";
            $output .= "<tr><th style='width:$width%'><center>".implode("</center></th><th style='width:$width%;'><center>", $items)."</center></th></tr>";
            $output .= "<tr><td class='small' valign='top'>".implode("</td><td class='small' valign='top'>", $descriptions)."</td></tr>";
            $output .= "</table>";
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
	    $options = @explode("|", $this->getAttr('options'));
	    return $options;
	}
	
	function parseLabels(){
	    $labels = @explode("|", $this->getAttr('labels', $this->getAttr('options')));
	    return $labels;
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
