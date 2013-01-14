<?php

class EvalReviewTextareaReportItem extends TextareaReportItem {

	// Redefined: Sets the Blob Sub-Item of this AbstractReportItem
    function setBlobSubItem($item){
        $type = $this->getParent()->getAttr('subType', 'NI');
        if($type == "NI"){
            $this->blobSubItem = $this->personId;
        }
        else if ($type == "Project"){
            $this->blobSubItem = $this->getParent()->projectId;
        }
        //$this->getSeenOverview();
        //echo $this->blobItem;
    }

    function getSeenOverview(){
    	global $wgUser, $wgImpersonating;
        $type = $this->getParent()->getAttr('subType', 'NI');
        if($type == "NI"){
            $blobSubItem = $this->personId;
        }
        else if ($type == "Project"){
            $blobSubItem = $this->getParent()->projectId;
        }

        if(!$wgImpersonating){
            $evaluator_id = $wgUser->getId();
           	
            //Check if the reviewer has completed his review
            $radio_questions = array(EVL_EXCELLENCE, EVL_HQPDEVELOPMENT, EVL_NETWORKING, EVL_KNOWLEDGE, EVL_MANAGEMENT, EVL_REPORTQUALITY, EVL_OVERALLSCORE, EVL_CONFIDENCE);

            $project_id = 0;
            if($this->getReport()->reportType == RP_EVAL_PROJECT){
                $project_id = $blobSubItem;
            }

            $complete = true;
            foreach ($radio_questions as $blobItem){
                $blob = new ReportBlob(BLOB_TEXT, $this->getReport()->year, $evaluator_id, $project_id);
                $blob_address = ReportBlob::create_address($this->getReport()->reportType, SEC_NONE, $blobItem, $blobSubItem);
                $blob->load($blob_address);
                if(!$blob_data = $blob->getData()){
                    $complete = false;
                    break;
                }
            }

            $blob = new ReportBlob(BLOB_TEXT, $this->getReport()->year, $evaluator_id, 0);
            $blob_address = ReportBlob::create_address($this->getReport()->reportType, SEC_NONE, EVL_SEENOTHERREVIEWS, 0);
            $blob->load($blob_address);
            $seeonotherreviews = $blob->getData();

           	//If the reviewer has seen the overview, use the second address.
           	//echo "OTHR".$this->personId."<br>";
            if($seeonotherreviews && $complete){
                
        		$this->blobItem = EVL_OTHERCOMMENTSAFTER;

                $blob = new ReportBlob(BLOB_TEXT, $this->getReport()->year, $evaluator_id, $blobSubItem);
                
                //copy over the data if the 'AFTER' blob does not yet exist
                $blob_address_from = ReportBlob::create_address($this->getReport()->reportType, SEC_NONE, EVL_OTHERCOMMENTS, $blobSubItem);
                $blob->load($blob_address_from);
                $orig_data = $blob->getData();

                $blob_address_to = ReportBlob::create_address($this->getReport()->reportType, SEC_NONE, EVL_OTHERCOMMENTSAFTER, $blobSubItem);

                if(!$blob->load($blob_address_to) && $orig_data){    
                    $blob->store($orig_data, $blob_address_to);
                }
        	}
        }
    }
}

?>
