<?php

class FootnoteReportItem extends StaticReportItem {

    static $nFootnotes = 0;

	function render(){
	    global $wgOut;
        self::$nFootnotes++;
	    $text = $this->getAttr('text', "");
	    $cdata = $this->processCData("<sup title='{$text}' class='tooltip'>[".self::$nFootnotes."]</sup>");
		$wgOut->addHTML($cdata);
	}
	
	function renderForPDF(){
	    global $wgOut;
	    self::$nFootnotes++;
	    $text = $this->getAttr('text', "");
	    $cdata = $this->processCData("<sup title='{$text}' class='tooltip'>[".self::$nFootnotes."]</sup>");
		PDFGenerator::addFootnote($text);
		$wgOut->addHTML($cdata);
	}
}

?>
