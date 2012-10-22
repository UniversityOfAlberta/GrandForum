<?php

class AutoCompleteTextareaReportItem extends TextareaReportItem {

	function render(){
		global $wgOut;
		$set = $this->getAttr("set", "");
		$index = $this->getAttr("index", "");
		$label = $this->getAttr("label", "");
		$name = $this->getAttr("name", "");
		$notReferenced = $this->getAttr("showNotReferenced", "false");
		$item = "";
		$reportItemSet = $this->getSet();
		if(class_exists($set)){
		    $item .= "<script type='text/javascript'>
		                var {$this->id} = Array();\n";
		    foreach($reportItemSet->getData() as $tuple){
		        $staticLabel = new StaticReportItem();
		        $staticValue = new StaticReportItem();
		        
		        $staticLabel->setPersonId($tuple['person_id']);
		        $staticLabel->setProjectId($tuple['project_id']);
		        $staticLabel->setMilestoneId($tuple['milestone_id']);
		        $staticLabel->setProductId($tuple['product_id']);
		        
		        $staticValue->setPersonId($tuple['person_id']);
		        $staticValue->setProjectId($tuple['project_id']);
		        $staticValue->setMilestoneId($tuple['milestone_id']);
		        $staticValue->setProductId($tuple['product_id']);
		        
		        $staticLabel->setValue('{$'.$label.'}');
		        $staticValue->setValue('{$'.$index.'}');
		        
		        $javascriptLabel = str_replace("'", "\\'", $staticLabel->processCData(""));
		        $javascriptValue = str_replace("'", "\\'", $staticValue->processCData(""));
		        
		        $item .= "{$this->id}.push({'value':'{$javascriptValue}', 'label':'{$javascriptValue} - {$javascriptLabel}'});\n";
		    }
		    $item .= "</script>";
		}
		$item .= $this->getHTML();
		$item .= "<div id='{$this->id}_div'></div>";
		$item .= "<script type='text/javascript'>
		            $('textarea[name={$this->getPostId()}]').triggeredAutocomplete({
                        hidden: '#hidden_inputbox{$this->id}',
                        source: {$this->id},
                        trigger: '@'
                    });
		</script>";
		if($notReferenced == "true"){
		    $item .= "<script type='text/javascript'>
		                function autocompleteLeft{$this->id}(){
		                    var innerHTML = '<fieldset><legend><b>Items not referenced:</b></legend><ul>';
		                    var left = 0;
                            for (index in {$this->id}){
                                var item = {$this->id}[index];
                                
                                var value = item.value;
                                var label = item.label;
                                var val = $('textarea[name={$this->getPostId()}]').val();
                                regex = RegExp('@' + value + '([^0-9]+?|$)','');
                                if(regex.test(val) == false){
                                    innerHTML += '<li>' + label + '</li>';
                                    left++;
                                }
                            }
                            innerHTML += '</ul></fieldset>';
                            if(left > 0){
                                $('#{$this->id}_div').html(innerHTML);
                            }
                            else{
                                $('#{$this->id}_div').html('');
                            }
                        }
                        
                        $('textarea[name={$this->getPostId()}]').keyup(function(){
                            autocompleteLeft{$this->id}();
                        });
                        autocompleteLeft{$this->id}();
		    </script>";
		}
		$item = $this->processCData($item);
		$item = "<span style='float:right;' class='pdfnodisplay tooltip' title='You should reference a milestone by writing <pre>@Milestone ID</pre> in the text box. You can also start typing <pre>@Milestone Title</pre> and a drop-down box will appear below the text box where you can select the milestone you wish to reference.'><b>@autocomplete:</b> {$name}</span>".$item;
		$wgOut->addHTML($item);
	}
	
	function getSet(){
	    $set = $this->getAttr("set", "");
	    if(class_exists($set)){
	        $reportItemSet = new $set();
	        $reportItemSet->setPersonId($this->personId);
		    $reportItemSet->setProjectId($this->projectId);
		    $reportItemSet->setMilestoneId($this->milestoneId);
		    return $reportItemSet;
	    }
	    return null;
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $value = nl2br($this->getBlobValue());
	    $limit = $this->getLimit();
	    $anchor = ($this->getAttr("anchor", "false") == "true");
	    
	    $html = "";
	    if($limit > 0){
	        $recommended = $this->getAttr('recommended', false);
	        if($recommended){
	            $type = "recommended";
	        }
	        else{
	            $type = "maximum";
	        }
	        $html .= "<span style='color:#888888;'><small>(<i>currently {$this->getNChars()} chars out of a {$type} {$limit}.</i>)</small></span>";
	    }
	    $set = $this->getAttr("set", "");
		$index = $this->getAttr("index", "");
		$label = $this->getAttr("label", "");
		if(class_exists($set)){
		    $reportItemSet = new $set();
		    $reportItemSet->setPersonId($this->personId);
		    $reportItemSet->setProjectId($this->projectId);
		    $reportItemSet->setMilestoneId($this->milestoneId);
		    $reportItemSet->setProductId($this->productId);
		    foreach($reportItemSet->getData() as $tuple){
		        $staticValue = new StaticReportItem();
		        $staticValue->setPersonId($tuple['person_id']);
		        $staticValue->setProjectId($tuple['project_id']);
		        $staticValue->setMilestoneId($tuple['milestone_id']);
		        $staticValue->setProductId($tuple['product_id']);
		        $staticValue->setValue('{$'.$index.'}');
		        $id = $staticValue->processCData("");
		        if($anchor){
		            $value = preg_replace("/(@{$id})([^0-9]+?|$)/", "<a class='anchor' href='#{$this->id}_{$id}'>$1</a>$2", $value);
		        }
		    }
		}
		$html .= "<p>$value</p>";
	    $item = $this->processCData($value);
		$wgOut->addHTML($item);
	}
}
?>
