<?php

class CalendarReportItem extends AbstractReportItem {
	
	function render(){
		global $wgOut;
		$value = $this->getBlobValue();
		$width = (isset($this->attributes['width'])) ? $this->attributes['width'] : "150px";
		$item = "<input type='text' name='{$this->getPostId()}' style='width:{$width};' value='{$value}' />";
		$item = $this->processCData($item);
		$item .= "<script type='text/javascript'>
		    $('input[name={$this->getPostId()}]').datepicker(
		        {dateFormat: 'yy-mm-dd',
		         changeMonth: true,
                 changeYear: true,
                 yearRange: 'c-25:c+5'
		        });
            $('input[name={$this->getPostId()}]').keydown(function(){
                return false;
            });
		</script>";
		$wgOut->addHTML("$item");
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $item = $this->processCData($this->getBlobValue());
		$wgOut->addHTML($item);
	}
}

?>
