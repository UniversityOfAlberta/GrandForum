<?php

class FacultyMembersReportItem extends MultipleSelectReportItem {

	function render(){
		global $wgOut;
		$facultyUrl = $this->getAttr('url');
		if ($facultyUrl == "") {
			$json = array();
		} else {
        	$json = json_decode(file_get_contents($facultyUrl));
        }
        $options = array();
        foreach($json as $prof) {
        	$options[] = $prof->realName;
        }

        $value = $this->getBlobValue();
        $maxOptions = $this->getAttr('max', '0');
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
            $('select#{$this->getPostId()}').chosen({ max_selected_options: {$maxOptions} });
        </script>";
        
        $output = $this->processCData("<div>{$output}</div>");
		$wgOut->addHTML($output);
	}
}

?>
