<?php

class IfReportItem extends StaticReportItem {

    var $cond;

    function checkCondition(){
        if($this->cond === null){
            $this->cond = $this->getAttr("if", '');
        }
        return ($this->cond == "1");
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
