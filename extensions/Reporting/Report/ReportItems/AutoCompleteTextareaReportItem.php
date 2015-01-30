<?php

class AutoCompleteTextareaReportItem extends TextareaReportItem {

	function render(){
		global $wgOut;
		$set = $this->getAttr("set", "");
		$index = $this->getAttr("index", "");
		$label = $this->getAttr("label", "");
		$name = $this->getAttr("name", "");
		$tooltipOptionId = $this->getAttr("tooltipOptionId", "ID");
		$tooltipOptionName = $this->getAttr("tooltipOptionName", "Name");
		$notReferenced = $this->getAttr("showNotReferenced", "false");
		$item = "";
		$reportItemSet = $this->getSet();
		if(class_exists($set)){
		    $item .= "<script type='text/javascript'>
		                var {$this->getId()} = Array();\n";
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
		        
		        $javascriptLabel = str_replace("'", "\'", str_replace("\'", "'", $staticLabel->processCData("")));
		        $javascriptValue = str_replace("'", "\'", str_replace("\'", "'", $staticValue->processCData("")));
		        
		        $item .= "{$this->getId()}.push({'value':'{$javascriptValue}', 'label':'{$javascriptValue} - {$javascriptLabel}'});\n";
		    }
		    $item .= "</script>";
		}
		$item .= "<span style='float:right;margin-right:30px;' class='pdfnodisplay tooltip' title='You should reference $name by writing <code>@$tooltipOptionId</code> in the text box. You can also start typing <code>@$tooltipOptionName</code> and a drop-down box will appear below the text box where you can select the one you wish to reference.'><b>@autocomplete:</b> {$name}</span>".$this->getHTML();
		$item .= "<div id='{$this->getId()}_div'></div>";
		$item .= "<script type='text/javascript'>";
		$item .= "$('textarea[name={$this->getPostId()}]').addClass('autocomplete');
		            $('textarea[name={$this->getPostId()}]').triggeredAutocomplete({
                        hidden: '#hidden_inputbox{$this->getId()}',
                        source: {$this->getId()},
                        trigger: '@'
                    });";
	    if($this->getLimit() > 0){
	        $item .= "
		            $(document).ready(function(){
		                var regex = RegExp('@\\\\[[^-]+-([^\\\\]]*)]','g');
                        var strlen = $('textarea[name={$this->getPostId()}]').val().replace(regex, ' ').length;
                        changeColor{$this->getPostId()}($('textarea[name={$this->getPostId()}]'), strlen);
                        $('textarea[name={$this->getPostId()}]').off('keypress');
                        $('textarea[name={$this->getPostId()}]').off('keyup');
                        $('textarea[name={$this->getPostId()}]').keypress(function(){
                            var regex = RegExp('@\\\\[[^-]+-([^\\\\]]*)]','g');
                            var strlen = $(this).val().replace(regex, ' ').length;
                            changeColor{$this->getPostId()}(this, strlen);
                        });
                        $('textarea[name={$this->getPostId()}]').keyup(function(){
                            var regex = RegExp('@\\\\[[^-]+-([^\\\\]]*)]','g');
                            var strlen = $(this).val().replace(regex, ' ').length;
                            changeColor{$this->getPostId()}(this, strlen);
                        });
                    });";
        }
        $item .= "</script>";
		if($notReferenced == "true"){
		    $item .= "<script type='text/javascript'>
		                function autocompleteLeft{$this->getId()}(){
		                    var innerHTML = '<fieldset><legend><b>$name not referenced:</b></legend><ul>';
		                    var left = 0;
                            for (index in {$this->getId()}){
                                var item = {$this->getId()}[index];
                                
                                var value = item.value;
                                var label = item.label;
                                
                                var str = label.replace('[', '\\\[')
                                               .replace('(', '\\\(')
                                               .replace(')', '\\\)');
                                var val = $('textarea[name={$this->getPostId()}]').val();
                                var regex = RegExp('@\\\[' + str + ']','g');
                                if(regex.test(val) == false){
                                    innerHTML += '<li>' + label + '</li>';
                                    left++;
                                }
                            }
                            innerHTML += '</ul></fieldset>';
                            if(left > 0){
                                $('#{$this->getId()}_div').html(innerHTML);
                            }
                            else{
                                $('#{$this->getId()}_div').html('');
                            }
                        }
                        
                        $('textarea[name={$this->getPostId()}]').keyup(function(){
                            autocompleteLeft{$this->getId()}();
                        });
                        autocompleteLeft{$this->getId()}();
		    </script>";
		}
		$item = $this->processCData($item);
		//$item = .$item;
		$wgOut->addHTML($item);
	}
	
	function getSet(){
	    $set = $this->getAttr("set", "");
	    if(class_exists($set)){
	        $reportItemSet = new $set();
	        $reportItemSet->setPersonId($this->personId);
		    $reportItemSet->setProjectId($this->projectId);
		    $reportItemSet->setMilestoneId($this->milestoneId);
		    $reportItemSet->setProductId($this->productId);
		    return $reportItemSet;
	    }
	    return null;
	}
	
	function getHTMLForPDF(){
	    $limit = $this->getLimit();
	    $nChars = $this->getActualNChars();
	    $anchor = ($this->getAttr("anchor", "false") == "true");
	    $recommended = $this->getAttr('recommended', false);
	    
	    $html = "";
	    if($limit > 0){
	        $class = "";
	        if($nChars > $limit && $recommended){
	            $class = "inlineWarning";
	        }
	        else if($nChars > $limit){
                $class = "inlineError";
            }
            else if($nChars == 0){
                $class = "inlineWarning";
            }
	        
	        if($recommended){
	            $type = "recommended";
	        }
	        else{
	            $type = "maximum";
	        }
	        $html .= "<span class='$class'><small>(<i>currently {$nChars} chars out of a {$type} {$limit}.</i>)</small></span><br />";
	        $blobValue = str_replace("\r", "", $this->getReplacedBlobValue());
            if(!$recommended){
                $blobValue = $this->getReplacedBlobValue();
                $replacedLength = $this->getActualNChars();
                $lengthDiff = strlen($blobValue) - $replacedLength;
                $blobValue1 = substr($blobValue, 0, $limit + $lengthDiff);
                $blobValue2 = substr($blobValue, $limit + $lengthDiff);
                $limit -= (strlen(utf8_decode($blobValue1)) - $lengthDiff);
                if($blobValue2 != ""){
                    if(isset($_GET['preview'])){
                        $blobValue = "{$blobValue1}<s style='color:red;'>{$blobValue2}</s>";
                    }
                    else{
                        $blobValue = "$blobValue1...";
                    }
                }
                else{
                    $blobValue = $blobValue1;
                }
            }
            $html .= nl2br($blobValue);
	    }
	    else{
	        $value = nl2br($this->getReplacedBlobValue());
		    $html .= "{$value}";
	    }
	    return $html;
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $item = $this->getHTMLForPDF();
	    $item = $this->processCData($item);
		$wgOut->addHTML($item);
	}
	
	function getReplacedBlobValue(){
	    $value = $this->getBlobValue();
	    $limit = $this->getLimit();
	    $anchor = ($this->getAttr("anchor", "false") == "true");
	    $set = $this->getAttr("set", "");
		$index = $this->getAttr("index", "");
		$label = $this->getAttr("label", "");
		if(class_exists($set)){
		    $reportItemSet = $this->getSet();
		    $anchorFormat = $this->getAttr("anchorFormat", "", false);
		    foreach($reportItemSet->getData() as $tuple){
		        $staticValue = new StaticReportItem();
		        $staticValue->setPersonId($tuple['person_id']);
		        $staticValue->setProjectId($tuple['project_id']);
		        $staticValue->setMilestoneId($tuple['milestone_id']);
		        $staticValue->setProductId($tuple['product_id']);
		        $staticValue->setValue('{$'.$index.'}');
		        $id = $staticValue->processCData("");
		        $staticValue->setValue($anchorFormat);
		        $anchorText = $staticValue->processCData("");
		        
		        if($anchor && !isset($_GET['preview'])){
		            $value = preg_replace("/@\[[^-]+-([^\]]*)]/", "<a class='anchor' href='#{$this->getId()}_{$id}'>$1</a>$2", $value);
		        }
		        else{
		            $value = preg_replace("/@\[[^-]+-([^\]]*)]/", "<b>$1</b>$2", $value);
		        }
		    }
		}
		return str_replace("\r", "", $value);
	}
	
	function getNChars(){
	    return min($this->getLimit(), $this->getActualNChars());
	}
	
	function getActualNChars(){
	    $set = $this->getAttr("set", "");
		$index = $this->getAttr("index", "");
		$label = $this->getAttr("label", "");
		
		$value = str_replace("\r", "", $this->getBlobValue());
		$value = preg_replace("/@\[[^-]+-([^\]]*)]/", " ", $value);
	    return strlen(utf8_decode($value));
	}
}
?>
