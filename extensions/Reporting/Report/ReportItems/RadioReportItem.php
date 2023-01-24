<?php

class RadioReportItem extends AbstractReportItem {

	function render(){
		global $wgOut;
        $options = $this->parseOptions();
        $labels = explode("|", $this->getAttr('labels', ''));
        $showScore = (strtolower($this->getAttr('showScore', 'false')) == 'true');
        $orientation = $this->getAttr('orientation', 'vertical');
        $buttonPosition = $this->getAttr('buttonPosition', 'left');
        $value = $this->getBlobValue();
		$default = $this->getAttr('default', '');
		if(($value === null||$value == "") && $default != ''){
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
		            $score = "";
		            if($showScore){
		                $score = "<tr><td></td><td style='font-weight:normal;font-size:smaller;'>(Score = $option)</td></tr>";
		            }
		            if($buttonPosition == "left"){
		                $items[] = "<table cellspacing='0' cellpadding='2'><tr><td><input style='vertical-align:top;transform-origin:top;' type='radio' name='{$this->getPostId()}' value='{$option}' $checked />&nbsp;</td><td>{$labels[$i]}</td></tr>{$score}</table>";
		            }
		            else{
		                $items[] = "<table cellspacing='0' cellpadding='2'><tr><td>{$labels[$i]}&nbsp;</td><td><input style='vertical-align:top;transform-origin:top;' type='radio' name='{$this->getPostId()}' value='{$option}' $checked /></td></tr>{$score}</table>";
		            }
		        }
		        else{
		            if($orientation == 'horizontal'){
		                $items[] = "<input style='vertical-align:top;display:table-cell;transform-origin:top;' type='radio' name='{$this->getPostId()}' value='{$option}' $checked />&nbsp;{$option}";
		            }
		            else{
		                $items[] = "<div style='display:table;padding-bottom:1px;padding-top:1px;'><input style='vertical-align:top;display:table-cell;transform-origin:top;' type='radio' name='{$this->getPostId()}' value='{$option}' $checked />&nbsp;<div style='display:table-cell;'>{$option}</div></div>";
		            }
		        }
		    }
		    else{
		        // Show sub options
		        $score = "";
		        foreach($option as $subOption){
		            $checked = "";
		            if($value == $subOption){
		                $checked = "checked='checked'";
		            }
		            $subOption = str_replace("'", "&#39;", $subOption);
		            $score .= "<tr><td style='font-weight:normal;font-size:smaller;'><input style='vertical-align:top;' type='radio' name='{$this->getPostId()}' value='{$subOption}' $checked />&nbsp;$subOption</td></tr>";
		        }
		        $items[] = "<table cellspacing='0' cellpadding='0'><tr><td>{$labels[$i]}</td></tr>{$score}</table>";
		    }
		}

        $output = "";
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
            $output .= "<tr><td class='small' valign='top' align='middle'>".implode("</td><td class='small' valign='top' align='middle'>", $descriptions)."</td></tr>";
            $output .= "</table>";
        }
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
