<?php

class WikiTextReportItem extends StaticReportItem {

	function render(){
		global $wgOut;
		$item = $this->processCData("");
		$wgOut->addWikiText($item);
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $item = $this->processCData("");
		$wgOut->addWikiText($item);
	}
}

?>
