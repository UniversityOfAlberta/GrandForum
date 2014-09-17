<?php

class SubSubBookmarkReportItem extends StaticReportItem {

	function render(){
	    global $wgOut;
        $wgOut->addHTML($this->processCData(""));
	}
	
	function renderForPDF(){
	    global $wgOut;
        $text = $this->getAttr('text', $this->getAttr("title"));
		PDFGenerator::addSubSubChapter($text);
		$wgOut->addHTML($this->processCData(""));
	}
}

?>
