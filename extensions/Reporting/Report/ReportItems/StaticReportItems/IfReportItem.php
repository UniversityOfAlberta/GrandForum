<?php

class IfReportItem extends StaticReportItem {

    function checkCondition(){
        $cond = $this->getAttr("if", '');
        return ($cond == "1");
    }

	function render(){
		global $wgOut;
		if($this->checkCondition()){
		    $item = $this->processCData("");
		    $wgOut->addHTML($item);
		}
	}
	
	function renderForPDF(){
	    global $wgOut;
	    if($this->checkCondition()){
		    $item = $this->processCData("");
		    $wgOut->addHTML($item);
		}
	}
}

?>
