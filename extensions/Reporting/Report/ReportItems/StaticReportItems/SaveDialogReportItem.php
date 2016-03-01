<?php

class SaveDialogReportItem extends StaticReportItem {

	function render(){
		global $wgOut;
		$message = $this->getAttr("message", "");
		$item = $this->processCData("<div title='Section Complete' id='saveDialog' style='display:none;'>
		    $message
		</div>");
		$wgOut->addHTML($item);
	}
	
	function renderForPDF(){
        // Do nothing
	}
	
}

?>
