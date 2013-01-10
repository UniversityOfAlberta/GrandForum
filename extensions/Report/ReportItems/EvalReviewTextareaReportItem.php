<?php

class EvalReviewTextareaReportItem extends TextareaReportItem {

	// Redefined: Sets the Blob Sub-Item of this AbstractReportItem
    function setBlobSubItem($item){
        $this->blobSubItem = $this->personId;
        $this->getSeenOverview();
        //echo $this->blobItem;
    }

    function getSeenOverview(){
    	global $wgUser, $wgImpersonating;
        if(!$wgImpersonating){
            $evaluator_id = $wgUser->getId();
            $blob = new ReportBlob(BLOB_TEXT, $this->getReport()->year, $evaluator_id, $this->projectId);
            $blob_address = ReportBlob::create_address($this->getReport()->reportType, SEC_NONE, EVL_SEENOTHERREVIEWS, 0);
            $blob->load($blob_address);
           	$blob_data = $blob->getData();
           	
           	//If the reviewer has seen the overview, use the second address.
           	if($blob_data){
        		$this->blobItem = EVL_OTHERCOMMENTSAFTER;
        	}
        }
    }
}

?>
