<?php

class DiffReportItem extends StaticReportItem {

	function render(){
	    global $wgOut;
	    $oldText = str_replace("\r", "", str_replace("<br />", "", str_replace("\n", " ", $this->getAttr('oldText', " "))));
	    $newText = str_replace("\r", "", str_replace("<br />", "", str_replace("\n", " ", $this->getAttr('newText', " "))));
        $diff = @htmldiffNL($oldText, $newText);
        $item = $this->processCData($diff);
		$wgOut->addHTML($item);
	}
	
	function renderForPDF(){
	    $this->render();
	}
	
}

?>
