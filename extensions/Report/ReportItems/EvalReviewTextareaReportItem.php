<?php

class EvalReviewTextareaReportItem extends TextareaReportItem {

	// Redefined: Sets the Blob Sub-Item of this AbstractReportItem
    function setBlobSubItem($item){
        $this->blobSubItem = $this->personId;
    }
}

?>
