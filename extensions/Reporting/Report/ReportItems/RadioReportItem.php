<?php

class RadioReportItem extends AbstractReportItem {

	function render(){
		global $wgOut;
        $options = $this->parseOptions();
        $labels = explode("|", $this->getAttr('labels', ''));
        $showScore = (strtolower($this->getAttr('showScore', 'false')) == 'true');
        $value = $this->getBlobValue();
        $items = array();
		foreach($options as $i => $option){
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
		        $items[] = "<table cellspacing='0' cellpadding='0'><tr><td><input style='vertical-align:top;' type='radio' name='{$this->getPostId()}' value='{$option}' $checked />&nbsp;</td><td>{$labels[$i]}</td></tr>{$score}</table>";
		    }
		    else{
		        $items[] = "<input style='vertical-align:top;' type='radio' name='{$this->getPostId()}' value='{$option}' $checked />&nbsp;{$option}";
		    }
		}

        $output = "";
        $orientation = $this->getAttr('orientation', 'vertical');
        $descriptions = explode("|", $this->getAttr('descriptions', ''));
        if($orientation == 'vertical' && count($descriptions) != count($items)){
            if(count($labels) == count($options)){
                $output = implode("\n", $items);
            }
            else{
                $output = implode("<br />\n", $items);
            }
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
        if($this->getBlobValue() == ""){
            $output = "<input type='hidden' name='{$this->getPostId()}' value='' />".$output; 
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
