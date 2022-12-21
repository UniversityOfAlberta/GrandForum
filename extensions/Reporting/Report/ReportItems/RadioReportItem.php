<?php

class RadioReportItem extends AbstractReportItem {

	function render(){
		global $wgOut;
        $options = $this->parseOptions();
        $labels = explode("|", $this->getAttr('labels', ''));
        $showScore = (strtolower($this->getAttr('showScore', 'false')) == 'true');
        $orientation = $this->getAttr('orientation', 'vertical');
        $value = $this->getBlobValue();
        $other = $this->getAttr('withOther', false);
        $number = $this->getAttr('number', false);
        $items = array();
		foreach($options as $i => $option){
		    if(!is_array($option)){
		        $checked = "";
		        if($value == $option){
		            $checked = "checked='checked'";
		        }
		        $option = str_replace("'", "&#39;", $option);
		        if(count($labels) == count($options)){
		        	// With labels, force vertical
		            $score = "";
		            if($showScore){
		                $score = "<tr><td></td><td style='font-weight:normal;font-size:smaller;'>(Score = $option)</td></tr>";
		            }
		            $items[] = "<table cellspacing='0' cellpadding='0' style='padding-top:1px;padding-bottom:1px;'><tr><td><input style='vertical-align:top;' type='radio' name='{$this->getPostId()}' value='{$option}' $checked />&nbsp;</td><td>{$labels[$i]}</td></tr>{$score}</table>";
		        }
		        else{
		            if($orientation == 'horizontal'){
		                $items[] = "<input style='vertical-align:top;display:table-cell;' type='radio' name='{$this->getPostId()}' value='{$option}' $checked />&nbsp;{$option}";
		            }
		            else{
		                $items[] = "<div style='display:table;padding-bottom:1px;padding-top:1px;'><input style='vertical-align:top;display:table-cell;' type='radio' name='{$this->getPostId()}' value='{$option}' $checked />&nbsp;<div style='display:table-cell;'>{$option}</div></div>";
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

		if ($other) {
			$checked = "";
            	if (!in_array($value, $options)) {
            		$checked = "checked='checked'";
            	}

			// Add the "other" radio option to either horizontal or vertical layout.
			if($orientation == 'horizontal'){
                $items[] = "<input style='vertical-align:top;display:table-cell;' type='radio' name='{$this->getPostId()}' value='Other' $checked />&nbsp;Other";
            }
            else{
                $items[] = "<div style='display:table;padding-bottom:1px;padding-top:1px;'><input style='vertical-align:top;display:table-cell;' type='radio' name='{$this->getPostId()}' value='Other' $checked />&nbsp;<div style='display:table-cell;'>Other</div></div>";
    		
    		}
    		// show the "Other" textbox when the other radio option is selected.
    		$otherTextInput = "";
    		$otherTextInput .= "<script type='text/javascript'>
			    var toggleOther{$this->getPostId()} = function() {
			    	if ($('input[name={$this->getPostId()}]:checked').val() == 'Other') {
			            $('#{$this->getPostId()}_other').slideDown();
                        $('#{$this->getPostId()}_other input').prop('disabled', false);
                    } else {
                        $('#{$this->getPostId()}_other').slideUp();
                        $('#{$this->getPostId()}_other input').val('');
                        $('#{$this->getPostId()}_other input').prop('disabled', true);

                    }
				}
				$('input[name={$this->getPostId()}]').change(toggleOther{$this->getPostId()});
				$(document).ready(toggleOther{$this->getPostId()});
				</script>";
			$otherTextInput .= "<div id='{$this->getPostId()}_other' style='padding-bottom:1px;padding-top:1px;'>Other: <input name='{$this->getPostId()}' value='{$value}' style='vertical-align:middle;' />&nbsp;</div>";
			if ($number) {
				$otherTextInput .= "<script type='text/javascript'>
			    $('input[type=text][name={$this->getPostId()}]').forceNumeric({min: 0, max: 10000000, decimals: 2});
				</script>";
			}
			$items[] = $otherTextInput;
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
            $output .= "<tr><td class='small' valign='top'>".implode("</td><td class='small' valign='top'>", $descriptions)."</td></tr>";
            $output .= "</table>";
        }
        if($this->getBlobValue() == ""){
            $output = "<input type='hidden' name='{$this->getPostId()}' value='' />".$output; 
        }
	$sop = $this->getAttr('sopBlob', False);
	if($sop == "true"){
	     $output = $this->processCData("{$output}");
	}
	else{
             $output = $this->processCData("<div>{$output}</div>");
	}
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
