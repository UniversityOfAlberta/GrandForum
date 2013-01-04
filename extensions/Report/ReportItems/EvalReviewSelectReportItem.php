<?php

class EvalReviewSelectReportItem extends AbstractReportItem {

	// Redefined: Sets the Blob Sub-Item of this AbstractReportItem
    function setBlobSubItem($item){
    	//echo "PERSONID = ".$this->personId."<br>";
        $this->blobSubItem = $this->personId;
    }

	function render(){
		global $wgOut;
        //$options = $this->parseOptions();

        $value = $this->getBlobValue();
        $options = array(
        	"option 1",
        	"option 2",
        	"option 3",
        	"option 4",
        	"option 5"
        );

        $output = "<select name='{$this->getPostId()}'>";
		foreach($options as $option){
		    $checked = "";
		    if($value == $option){
		        $checked = "selected='selected'";
		    }
		    $output .= "<option value='{$option}' {$checked}>{$option}</option>";
		}
        $output .= "</select>";
        

	    //$addr = "BlobType=".$this->blobType."; Year=". $this->getReport()->year ."; PersonID=". $this->getReport()->person->getId()."; ProjectID=". $this->projectId."<br />";
        //$addr .= "ReportType=".$this->getReport()->reportType."; Section=". $this->getSection()->sec ."; BlobItem=". $this->blobItem ."; SubItem=". $this->blobSubItem ."<br />";

        $output = $this->processCData("<div style='padding-left:20px;'>{$output}</div>");
		$wgOut->addHTML($output);
	}
	
	function parseOptions(){
	    $options = @explode("|", $this->attributes['options']);
	    return $options;
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $item = $this->processCData($this->getBlobValue());
		$wgOut->addHTML($item);
	}
}

?>
