<?php

class LOIHeaderReportItem extends StaticReportItem {

	function render(){
        global $wgServer, $wgScriptPath, $wgOut;
        
        $loi = null;
        $loi = LOI::newFromId($this->projectId);
        $loi_name = $loi->getName();

	    $wgOut->addHTML("<h2>{$loi_name}</h2>");
	}
	
	function renderForPDF(){
	    $this->render();
	}
}

?>
