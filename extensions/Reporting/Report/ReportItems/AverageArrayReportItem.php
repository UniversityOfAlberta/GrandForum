<?php

class AverageArrayReportItem extends AbstractReportItem {

	function render(){
	    global $wgOut;
	    $values = $this->getBlobValue();
		$indices = explode("|", $this->getAttr("indices"));
		$sum = 0;
		$count = 0;
		foreach($indices as $index){
		    if(isset($values[$index]) && is_numeric($values[$index])){
		        $sum += $values[$index];
		        $count++;
		    }
		}
		$avg = number_format($sum/max(1,$count), 2);
		$wgOut->addHTML($this->processCData("{$avg}"));
	}
	
	function renderForPDF(){
        $this->render();
	}
}

?>
