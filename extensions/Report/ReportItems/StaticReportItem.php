<?php

class StaticReportItem extends AbstractReportItem {

	function render(){
		global $wgOut;
		$item = $this->processCData("");
		$wgOut->addHTML($item);
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $item = $this->processCData("");
		$wgOut->addHTML($item);
	}
	
	// Returns the number of completed values (usually 1, or 0)
    function getNComplete(){
        return 0;
    }
    
    // Returns the number of fields which are associated with this AbstractReportItem (usually 1)
    function getNFields(){
        return 0;
    }
}

?>
