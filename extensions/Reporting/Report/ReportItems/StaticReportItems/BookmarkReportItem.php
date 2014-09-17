<?php

class BookmarkReportItem extends StaticReportItem {

	function render(){
	    global $wgOut;
        $wgOut->addHTML($this->processCData(""));
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $text = $this->getAttr('text', $this->getAttr("title"));
		PDFGenerator::addChapter($text);
		$wgOut->addHTML($this->processCData(""));
	}
}

?>
