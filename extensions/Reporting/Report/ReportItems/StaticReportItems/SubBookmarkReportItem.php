<?php

class SubBookmarkReportItem extends StaticReportItem {

	function render(){
	    global $wgOut;
        $wgOut->addHTML($this->processCData(""));
	}
	
	function renderForPDF(){
	    global $wgOut;
        $text = $this->getAttr('text');
		PDFGenerator::addSubChapter($text);
		$wgOut->addHTML($this->processCData(""));
	}
}

?>
