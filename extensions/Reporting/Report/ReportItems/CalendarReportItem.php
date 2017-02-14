<?php

class CalendarReportItem extends AbstractReportItem {
	
	function render(){
		global $wgOut;
		$value = $this->getBlobValue();
		$width = (isset($this->attributes['width'])) ? $this->attributes['width'] : "150px";
		$format = $this->getAttr('format', 'yy-mm-dd');
		$item = "<input type='text' name='{$this->getPostId()}' style='width:{$width};' value='{$value}' />";
		$item = $this->processCData($item);
		$item .= "<script type='text/javascript'>
		    $('input[name={$this->getPostId()}]').datepicker(
		        {dateFormat: '$format',
		         changeMonth: true,
                 changeYear: true
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
