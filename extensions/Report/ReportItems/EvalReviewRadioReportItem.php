<?php

class EvalReviewRadioReportItem extends RadioReportItem {

	// Redefined: Sets the Blob Sub-Item of this AbstractReportItem
    function setBlobSubItem($item){
    	$type = $this->getAttr('subType', 'NI');
    	if($type == "NI"){
        	$this->blobSubItem = $this->personId;
    	}
    	else if($type == "Project"){
    		$this->blobSubItem = $this->projectId;
    	}
    }
}

?>
