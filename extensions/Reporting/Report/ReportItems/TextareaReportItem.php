<?php

class TextareaReportItem extends AbstractReportItem {

	function render(){
		global $wgOut;
		$item = $this->getHTML();
		if($this->getlimit() > 0){
		    $item .= "<script type='text/javascript'>
		        $(document).ready(function(){
                    var strlen = $('textarea[name={$this->getPostId()}]').val().length;
                    changeColor{$this->getPostId()}($('textarea[name={$this->getPostId()}]'), strlen);
                });
		    </script>";
		}
		if(strtolower($this->getAttr('rich', 'false')) == 'true'){
		    $item .= "<script type='text/javascript'>
		        $(document).ready(function(){
                    $('textarea[name={$this->getPostId()}]').tinymce({
                       theme: 'modern',
                       menubar: false,
                       plugins: 'link image contextmenu charmap lists',
                       toolbar: [
                            'undo redo | bold italic underline | link image charmap | bullist numlist outdent indent | alignleft aligncenter alignright'
                       ]
                    });
                });
		    </script>";
		}
		$item = $this->processCData($item);
		$wgOut->addHTML($item);
	}
	
	function calculateHeight($limit){
	    if($limit > 0){
            $height = max(125, (pow($limit, 0.75)))."px";
        }
        else{
            $height = $this->getAttr('height', '200px');
        }
        return $height;
	}
	
	function getHTML(){
	    $value = $this->getBlobValue();
        $rows = $this->getAttr('rows', 5);
        $width = $this->getAttr('width', '100%');
        $height = $this->getAttr('height', '');
        $limit = $this->getLimit();
        $height = $this->calculateHeight($limit);
        $item = "";
        if($limit > 0){
            $recommended = $this->getAttr('recommended', false);
	        if($recommended){
	            $type = "recommended";
	        }
	        else{
	            $type = "maximum of";
	        }
	        
            $item =<<<EOF
            <p id='limit_{$this->getPostId()}'><span class="pdf_hide inlineMessage">(currently <span id="{$this->getPostId()}_chars_left">0</span> characters out of a {$type} {$limit})</span></p>
            <script type='text/javascript'>
                function changeColor{$this->getPostId()}(element, strlen){
                	var type = "{$type}";
                    if(strlen > $limit && type=="recommended"){
                        $('#limit_{$this->getPostId()} > span').addClass('inlineWarning');
                        $('#limit_{$this->getPostId()} > span').removeClass('inlineError');
                    }
                    else if(strlen > $limit){
                        $('#limit_{$this->getPostId()} > span').addClass('inlineError');
                        $('#limit_{$this->getPostId()} > span').removeClass('warningError');
                    }
                    else if(strlen == 0){
                        $('#limit_{$this->getPostId()} > span').addClass('inlineWarning');
                        $('#limit_{$this->getPostId()} > span').removeClass('inlineError');
                    }
                    else{
                        $('#limit_{$this->getPostId()} > span').removeClass('inlineError');
                        $('#limit_{$this->getPostId()} > span').removeClass('inlineWarning');
                    }
                }
                $(document).ready(function(){
                    $('textarea[name={$this->getPostId()}]').off('limit');
                    $('textarea[name={$this->getPostId()}]').off('keyup');
                    $('textarea[name={$this->getPostId()}]').off('keypress');
                    $('textarea[name={$this->getPostId()}]').limit(1000000000000, '#{$this->getPostId()}_chars_left');
                    
                    $('textarea[name={$this->getPostId()}]').keypress(function(){
                        changeColor{$this->getPostId()}(this, $(this).val().length);
                    });
                    $('textarea[name={$this->getPostId()}]').keyup(function(){
                        changeColor{$this->getPostId()}(this, $(this).val().length);
                    });
                });
            </script>
EOF;
        }
		$item .= <<<EOF
			<textarea rows='$rows' style="height:{$height};width:{$width};" 
                        name="{$this->getPostId()}">$value</textarea>
EOF;
        return $item;
	}
	
	function getHTMLForPDF(){
	    $limit = $this->getLimit();
	    $html = "";
	    $blobValue = str_replace("\r", "", $this->getBlobValue());
	    $recommended = $this->getAttr('recommended', false);
	    $reportLimits = strtolower($this->getAttr('reportLimits', "false"));
	    $length = strlen(utf8_decode($blobValue));
	    $lengthDiff = strlen($blobValue) - $length;
	    $class = "inlineMessage";
	    if($limit > 0 && $reportLimits == "true"){
	        if(!$recommended){
	            $type = "maximum of";
	            $blobValue1 = substr($blobValue, 0, $limit + $lengthDiff);
	            $blobValue2 = substr($blobValue, $limit + $lengthDiff);
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
	            if($blobValue2 != ""){
	                $class = "inlineError";
	            }
	            else if($blobValue1 == ""){
	                $class = "inlineWarning";
	            }
	        }
	        else{
	            $type = "recommended";
	        }
	        $html .= "<span class='$class'><small>(<i>currently {$length} ".Inflect::smart_pluralize($length, "character")." out of a {$type} {$limit}</i>)</small></span>";
	    }
	    $dom = new SmartDOMDocument();
        $dom->loadHTML($blobValue);
        
        $imgs = $dom->getElementsByTagName("img");
        foreach($imgs as $img){
            $img->setAttribute('width', intval($img->getAttribute('width'))*DPI_CONSTANT);
            $img->setAttribute('height', intval($img->getAttribute('height'))*DPI_CONSTANT);
            $style = $img->getAttribute('style');
            preg_match("/width:\s*([0-9]*)/", $style, $styleWidth);
            preg_match("/height:\s*([0-9]*)/", $style, $styleHeight);
            if(isset($styleWidth[1]) && isset($styleHeight[1])){
                $style .= "width: ".($styleWidth[1]*DPI_CONSTANT)."px !important;";
                $style .= "height: ".($styleHeight[1]*DPI_CONSTANT)."px !important;";
            }
            $img->setAttribute('style', $style);
        }
	    $blobValue = "$dom";
	    $html .= nl2br("<p>{$blobValue}</p>");
	    return $html;
	}
	
	function getLimit(){
	    return $this->getAttr('limit', 0);
	}
	
	function getNChars(){
	    $blobValue = utf8_decode(str_replace("\r", "", $this->getBlobValue()));
	    return min($this->getLimit(), strlen($blobValue));
	}
	
	function getActualNChars(){
	    $blobValue = utf8_decode(str_replace("\r", "", $this->getBlobValue()));
	    return strlen($blobValue);
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $item = $this->getHTMLForPDF();
	    $item = $this->processCData($item);
		$wgOut->addHTML($item);
	}

	function getNComplete(){
        $opt = $this->getAttr('optional', '0');
        if($opt == '1'){
            return 0;
        }
        else{
            return parent::getNComplete();
        }
    }
    function getNFields(){
        $opt = $this->getAttr('optional', '0');

        if($opt == '1'){
            return 0;
        }
        else{
            return parent::getNFields();
        }
    }
}

?>
