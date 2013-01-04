<?php

class EvalReviewRadioReportItem extends RadioReportItem {

	// Redefined: Sets the Blob Sub-Item of this AbstractReportItem
    function setBlobSubItem($item){
    	//echo "PERSONID = ".$this->personId."<br>";
        $this->blobSubItem = $this->personId;
    }
}

?>
