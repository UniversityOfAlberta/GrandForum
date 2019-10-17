<?php

class MultipleSelectReportItem extends SelectReportItem {

	function render(){
		global $wgOut;
        $options = $this->parseOptions();
        $value = $this->getBlobValue();
        $max_options = $this->getAttr('max', '0');
        $width = (isset($this->attributes['width'])) ? $this->attributes['width'] : "150px";

        $output = "<input id='{$this->getPostId()}' type='text' name='{$this->getPostId()}[]' value='".implode(",", $value)."' />
        <script type='text/javascript'>
            $('input#{$this->getPostId()}').tagit({
                tagSource: function(search, showChoices) {
                    if(search.term.length < 0){ showChoices(); return; }
                    var filter = search.term.toLowerCase();
                    var choices = $.grep(this.options.availableTags, function(element) {
                        return (element.toLowerCase().match(filter) !== null);
                    });
                    showChoices(this._subtractArray(choices, this.assignedTags()));
                },
                availableTags: ".json_encode($options).",
                caseSensitive: false,
                singleField: true,
                allowSpaces: true,
                removeConfirmation: true,
                tagLimit: {$max_options}
            });
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
