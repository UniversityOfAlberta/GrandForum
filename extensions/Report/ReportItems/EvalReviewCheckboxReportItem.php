<?php

class EvalReviewCheckboxReportItem extends AbstractReportItem {

	// Redefined: Sets the Blob Sub-Item of this AbstractReportItem
    function setBlobSubItem($item){
        $this->blobSubItem = $this->personId;
    }

	function render(){
		global $wgOut;

        $value = $this->getBlobValue();
		
        $options = $this->parseOptions();
        //print_r($options);

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
       // $output .= "</select>";
        
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
	
	function parseOptions(){
	    $options = @explode("|", $this->attributes['options']);
	    return $options;
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $item = $this->processCData($this->getBlobValue());
		$wgOut->addHTML($item);
	}

	// function save(){
	// 	if(!isset($_POST[$this->getPostId()])){
	// 		$_POST[$this->getPostId()] = "";
	// 		$_POST['oldData'][$this->getPostId()] = $this->getBlobValue();
	// 	}
 //        if(isset($_POST[$this->getPostId()])){
 //            if(!isset($_POST[$this->getPostId().'_ignoreConflict']) ||
 //               $_POST[$this->getPostId().'_ignoreConflict'] != "true"){
 //                if(isset($_POST['oldData'][$this->getPostId()]) &&
 //                   trim($_POST['oldData'][$this->getPostId()]) == trim($_POST[$this->getPostId()])){
 //                   // Don't save, but also don't display an error
 //                   return array();
 //                }
 //                else if(isset($_POST['oldData'][$this->getPostId()]) && 
 //                   trim($_POST['oldData'][$this->getPostId()]) != trim($this->getBlobValue()) &&
 //                   trim($_POST[$this->getPostId()]) != trim($this->getBlobValue())){
 //                    if(trim($_POST['oldData'][$this->getPostId()]) != trim($_POST[$this->getPostId()])){
 //                        // Conflict in blob values
 //                        return array(array('postId' => $this->getPostId(), 
 //                                           'value' => trim($this->getBlobValue()),
 //                                           'postValue' => trim($_POST[$this->getPostId()]),
 //                                           'oldValue' => trim($_POST['oldData'][$this->getPostId()]),
 //                                           'diff' => @htmlDiffNL(str_replace("\n", "\n ", $this->getBlobValue()), str_replace("\n", "\n ", $_POST[$this->getPostId()]))));
 //                    }
 //                }
 //            }
 //            $this->setBlobValue($_POST[$this->getPostId()]);
 //        }
 //        return array();
 //    }
}

?>
