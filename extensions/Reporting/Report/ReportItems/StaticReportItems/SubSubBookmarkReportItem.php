<?php

class SubSubBookmarkReportItem extends StaticReportItem {

	function render(){
	    global $wgOut;
        $wgOut->addHTML($this->processCData(""));
	}
	
	function renderForPDF(){
	    global $wgOut;
        $text = $this->getAttr('text', $this->getAttr("title"));
        $pageOffset = $this->getAttr('offset', 0);
		PDFGenerator::addSubSubChapter($text, $pageOffset);
		$wgOut->addHTML($this->processCData(""));
	}
}

?>
