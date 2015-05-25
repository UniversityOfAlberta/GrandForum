<?php

class EvalReviewRadioReportItem extends RadioReportItem {
    var $seenOverview = 0;

	// Redefined: Sets the Blob Sub-Item of this AbstractReportItem
    function setBlobSubItem($i){

        $type = $this->getParent()->getAttr('subType', 'NI');
    	if($type == "NI"){
        	$this->blobSubItem = $this->personId;
    	}
    	else if($type == "Project"){
    		$this->blobSubItem = $this->getParent()->projectId;
    	}
        $this->getSeenOverview();
    }


    // Overloading from AbstractReportItem Sets the Blob value for this item
    function setBlobValue($value){
        $report = $this->getReport();
        $section = $this->getSection();
        $blob = new ReportBlob($this->blobType, $this->getReport()->year, $this->getReport()->person->getId(), $this->projectId);
        $blob_address = ReportBlob::create_address($report->reportType, $section->sec, $this->blobItem, $this->blobSubItem);
        $blob->load($blob_address);
        $blob_data = $blob->getData();
        //$this->blobType == BLOB_ARRAY
        
        $value = str_replace("\00", "", $value); // Fixes problem with the xml backup putting in random null escape sequences
        if($this->seenOverview){
            $blob_data['revised'] = $value;
        }
        else{
            $blob_data['original'] = $value;
        }

        $blob->store($blob_data, $blob_address);
        
    }
    function getBlobValue(){
        $this->getSeenOverview();
        $report = $this->getReport();
        $section = $this->getSection();
        $blob = new ReportBlob($this->blobType, $this->getReport()->year, $this->getReport()->person->getId(), $this->projectId);
        $blob_address = ReportBlob::create_address($report->reportType, $section->sec, $this->blobItem, $this->blobSubItem);
        $blob->load($blob_address);
        $blob_data = $blob->getData();
        if($this->seenOverview){
            $value = (isset($blob_data['revised']))? $blob_data['revised'] : "";
        }
        else{
            $value = (isset($blob_data['original']))? $blob_data['original'] : "";
        }
       
        return $value;
    }    

    function getSeenOverview(){
        global $wgUser, $wgImpersonating;
        $type = $this->getParent()->getAttr('subType', 'NI');
        $project_id = 0;
        if($type == "NI"){
            $blobSubItem = $this->personId;
        }
        else if ($type == "Project"){
            $blobSubItem = $project_id = $this->getParent()->projectId;
        }

        if(!$wgImpersonating){
            $evaluator_id = $wgUser->getId();

            $blob = new ReportBlob(BLOB_TEXT, $this->getReport()->year, $evaluator_id, $project_id);
            $blob_address = ReportBlob::create_address($this->getReport()->reportType, SEC_NONE, EVL_SEENOTHERREVIEWS, $blobSubItem);
            $blob->load($blob_address);
            $seeonotherreviews = $blob->getData();

            //If the reviewer has seen the overview, use the second address.
            if($seeonotherreviews == "Yes"){
                $this->seenOverview = 1;
            }
        }
    }
    
}

?>
