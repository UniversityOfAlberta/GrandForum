<?php

class SnippetReportItem extends StaticReportItem {

	function render(){
	    global $wgOut;
	    $text = $this->getAttr('text', " ");
        $limit = $this->getAttr('limit', 300);
        if(strlen($text) > $limit){
            $text = str_replace("<br />", "", $text);
            $text = substr($text, 0, $limit)."...";
        }
        $item = $this->processCData($text);
		$wgOut->addHTML($item);
	}
	
	function renderForPDF(){
	    $this->render();
	}
	
}

?>
