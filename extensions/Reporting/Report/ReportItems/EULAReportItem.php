<?php

class EULAReportItem extends AbstractReportItem {

    function save(){
        if(isset($_POST[$this->getPostId()])){
            $this->setBlobValue($_POST[$this->getPostId()]);
        }
        return array();
    }

	function render(){
		global $wgOut;
        $value = $this->getBlobValue();
        
        $yes = $this->getAttr('yes', "<b>Yes</b>, I agree with this statement");
        $no  = $this->getAttr('no', "<b>No</b>, I do not agree with this statement");
        
        $yesChecked = ($value == "Yes") ? "checked" : "";
        $noChecked  = ($value == "No")  ? "checked" : "";
        
        $disabled = ($this->getBlobValue() == "Yes");
        
        if(!$disabled){
            $output = "<input id='eula_yes' style='vertical-align:top;' type='radio' name='{$this->getPostId()}' value='Yes' {$yesChecked} />&nbsp;{$yes}<br />
                       <input id='eula_no'  style='vertical-align:top;' type='radio' name='{$this->getPostId()}' value='No'   {$noChecked} />&nbsp;{$no}";
            
            $output = $this->processCData("</div><br /><div>{$output}</div>");
            
		    $wgOut->addHTML("<div class='eula'>{$output}");
		    $wgOut->addHTML("<script type='text/javascript'>
		        var saveStatus = function(){
		            var el = $('input[name={$this->getPostId()}]:checked');
		            if(el.val() == 'Yes'){
		                $('input[name=submit]').prop('disabled', false);
		            }
		            else{
		                $('input[name=submit]').prop('disabled', true);
		            }
		        }
		        $('input[name={$this->getPostId()}]').change(saveStatus);
		        saveStatus();
		    </script>");
		}
		else{
		    $output = "{$yes}<br />";
		    $output = $this->processCData("</div><br /><div>{$output}</div>");
		    $wgOut->addHTML("<div class='eula'>{$output}");
		}
	}
	
	function renderForPDF(){
	    return;
	}
}

?>
