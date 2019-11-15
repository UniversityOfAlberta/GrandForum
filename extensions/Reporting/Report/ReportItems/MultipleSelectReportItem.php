<?php

class MultipleSelectReportItem extends SelectReportItem {

	function render(){
		global $wgOut;
        $options = $this->parseOptions();
        $value = $this->getBlobValue();
        $max_options = $this->getAttr('max', '0');
        $width = (isset($this->attributes['width'])) ? $this->attributes['width'] : "150px";

        if(!is_array($value)){
            $value = array();
        }
    
        $output = "<input id='{$this->getPostId()}' type='text' name='{$this->getPostId()}[]' value='".implode(",", $value)."' />
        <style>
            .tagit-choice {
                cursor: move;
            }
        </style>
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
                beforeTagAdded: function(event, ui){
                    var tag = ui.tagLabel;
                    var availableTags = ".json_encode($options).";
                    return availableTags.includes(tag);
                },
                availableTags: ".json_encode($options).",
                caseSensitive: false,
                singleField: true,
                allowSpaces: true,
                removeConfirmation: true,
                showAutocompleteOnFocus: true,
                tagLimit: {$max_options}
            });
            $('input#{$this->getPostId()}').next().sortable({
                stop: function(event,ui) {
                    $('#{$this->getPostId()}').val(
                        $('.tagit-label',$(this))
                            .clone()
                            .text(function(index,text){ return (index == 0) ? text : ',' + text; })
                            .text()
                    ).change();
                }
            });
        </script>";
        
        $output = $this->processCData("<div>{$output}</div>");
		$wgOut->addHTML($output);
	}
	
    function setBlobValue($value){
        if(strtolower($this->getAttr('useArray', "false")) == "true"){
            $value = explode(",", $value[0]);
        }
        parent::setBlobValue($value);
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
