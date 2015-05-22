<?php

class EvalReviewCheckboxReportItem extends AbstractReportItem {
	var $seenOverview = 0;

	// Redefined: Sets the Blob Sub-Item of this AbstractReportItem
    function setBlobSubItem($item){
        
        $type = $this->getParent()->getParent()->getAttr('subType', 'NI');
    	if($type == "NI"){
        	$this->blobSubItem = $this->personId;
    	}
    	else if ($type == "Project"){
    		$this->blobSubItem = $this->getParent()->getParent()->projectId;
    	}
    	$this->getSeenOverview();
    }

	function render(){
		global $wgOut;

        $value = $this->getBlobValue();
		
        $options = $this->parseOptions();
        
        $output = "";
      	$i = 1;
		foreach($options as $option){
		    $checked = "";
		    if($value == $option){
		        $checked = "checked='checked'";
		    }else{
		    	$output .= "<input type='hidden' name='{$this->getPostId()}' value='' />";
		    }
		    $output .= "<input type='checkbox' name='{$this->getPostId()}' value='{$option}' {$checked} /> {$option}<br />";
			$i++;
		}
        
        $output .=<<<EOF
        <script type="text/javascript">
        	$('input[name={$this->getPostId()}]').change(function(){
        		if($(this).attr('checked')){
        			console.log($(this).prev());
        			$(this).prev().remove();
        		}else{
        			$(this).before("<input type='hidden' name='{$this->getPostId()}' value='' />");
        		}
        	});
        </script>
EOF;

	    //$addr = "BlobType=".$this->blobType."; Year=". $this->getReport()->year ."; PersonID=". $this->getReport()->person->getId()."; ProjectID=". $this->projectId."<br />";
        //$addr .= "ReportType=".$this->getReport()->reportType."; Section=". $this->getSection()->sec ."; BlobItem=". $this->blobItem ."; SubItem=". $this->blobSubItem ."<br />";

        $output = $this->processCData("<div>{$output}</div>");
		$wgOut->addHTML($output);
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

        $parent = $this->getParent();
        $accessStr = "";
        if($this->id != ""){
            $accessStr = "['{$this->id}']";
        }
        while($parent instanceof ReportItemSet){
            if($parent->blobIndex != ""){
                $accessStr = "['{$this->{$parent->blobIndex}}']".$accessStr;
            }
            $parent = $parent->getParent();
        }
        $value = str_replace("\00", "", $value); // Fixes problem with the xml backup putting in random null escape sequences
        if($this->seenOverview){
            //$blob_data['revised'] = $value;
            eval("\$blob_data['revised']$accessStr = \$value;");
        }
        else{
            //$blob_data['original'] = $value;
            eval("\$blob_data['original']$accessStr = \$value;");
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

        $parent = $this->getParent();
        $accessStr = "";
        if($this->id != ""){
            $accessStr = "['{$this->id}']";
        }
        while($parent instanceof ReportItemSet){
            if($parent->blobIndex != ""){
                $accessStr = "['{$this->{$parent->blobIndex}}']".$accessStr;
            }
            $parent = $parent->getParent();
        }
        eval("\$value = @\$value$accessStr;");
       
        return $value;
    }    
	
	function parseOptions(){
	    $options = @explode("|", $this->attributes['options']);
	    return $options;
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $value = $this->getBlobValue();
	    if($value != null){
	        $item = $this->processCData($value);
		    $wgOut->addHTML("<p><i>".$item."</i></p>");
		}
	}
    
    // Checkboxes are optional so they don't count
    function getNComplete(){
        return 0;
    }
    function getNFields(){
        return 0;
    }

	function getSeenOverview(){
        global $wgUser, $wgImpersonating;
        $type = $this->getParent()->getParent()->getAttr('subType', 'NI');
        $project_id = 0;
        if($type == "NI"){
            $blobSubItem = $this->personId;
        }
        else if ($type == "Project"){
            $blobSubItem = $project_id = $this->getParent()->getParent()->projectId;
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
