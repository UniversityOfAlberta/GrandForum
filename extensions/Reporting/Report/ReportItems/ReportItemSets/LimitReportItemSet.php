<?php

class LimitReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $tuple = self::createTuple();
        $data[] = $tuple;
        return $data;
    }
    
    function getLimit(){
        if($this->getNTextareas() > 0){
            return $this->getAttr('limit', 0);
        }
        else{
            return 0;
        }
    }
    
    function getNChars(){
        $textareas = $this->getTextareas();
        $nChars = 0;
        foreach($textareas as $textarea){
            $nChars += $textarea->getNChars();
        }
        return min($this->getLimit(), $nChars);
    }
    
    function getActualNChars(){
        $textareas = $this->getTextareas();
        $nChars = 0;
        foreach($textareas as $textarea){
            $nChars += $textarea->getActualNChars();
        }
        return $nChars;
    }
    
    function getExceedingFields(){
        if($this->getActualNChars() > $this->getLimit()){
            return 1;
        }
        return 0;
    }
    
    function getEmptyFields(){
        if($this->getNTextareas() > 0 && $this->getActualNChars() == 0){
            return 1;
        }
        return 0;
    }
    
    function getTextareas($it=null){
        if($it == null){
            $it = $this;
        }
        $textareas = array();
        foreach($it->items as $item){
            if(!$this->getReport()->topProjectOnly || ($this->getReport()->topProjectOnly && !$item->private)){
                if(!$item->deleted){
                    if($item instanceof ReportItemSet){
                        $textareas = array_merge($textareas, $this->getTextareas($item));
                    }
                    else if($item instanceof TextareaReportItem){
                        $textareas[] = $item;
                    }
                }
            }
        }
        return $textareas;
    }
    
    function getNTextareas(){
        if(count($this->getTextareas()) > 0){
            return 1;
        }
        else{
            return 0;
        }
    }
    
    function render(){
        global $wgOut;
        $limit = $this->getLimit();
        $nChars = $this->getActualNChars();
        $textareas = $this->getTextareas();
        $noun = $this->getAttr("noun", "Project");
        $pluralNoun = strtolower($noun)."s";
        $recommended = $this->getAttr('recommended', false);
        if($recommended){
            $type = "recommended";
            $rec = 'true';
        }
        else{
            $type = "maximum of";
            $rec = 'false';
        }
        $wgOut->addHTML("<p id='limit_{$this->getPostId()}'><span class='pdf_hide inlineMessage'>(Reported By {$noun} - currently <span id='{$this->getPostId()}_chars_left'>{$nChars}</span> characters out of an overall {$type} {$limit} across all $pluralNoun)</span>&nbsp;<a style='font-style:italic; font-size:11px; font-weight:bold;cursor:pointer;' onClick='popup{$this->getPostId()}();'><i>Preview</i></a><div id='preview_{$this->getPostId()}' style='display:none;'></div></p>
        <div id='div_{$this->getPostId()}'>");
        $this->renderItems();
        $wgOut->addHTML("</div>");
        // Scripts
        $wgOut->addHTML("<script type='text/javascript'>
            $(document).ready(function(){
            var textareas = Array();\n");
        $nTextareas = count($textareas);
        foreach($textareas as $textarea){
            $postId = $textarea->getId();
            $h = $textarea->calculateHeight($limit/$nTextareas);
            $wgOut->addHTML("textareas.push($('textarea[name={$textarea->getPostId()}]'));
                             $('textarea[name={$textarea->getPostId()}]').height('$h');\n");
        }
        $wgOut->addHTML("$('#div_{$this->getPostId()}').multiLimit($limit, $('#{$this->getPostId()}_chars_left'), textareas);
            $('#preview_{$this->getPostId()}').dialog({ autoOpen: false, width: '700', height: '450'});
        });
        function popup{$this->getPostId()}(){
            $('#preview_{$this->getPostId()}').html($('#div_{$this->getPostId()}').html());
            $('#preview_{$this->getPostId()} .pdfnodisplay').remove();
            var limit = {$limit};
            var recommended = {$rec};
            var blobValues = Array();
            
            $.each($('#div_{$this->getPostId()} textarea'), function(index, value){
                
                var regex = RegExp('@\\\\[[^-]+-([^\\\\]]*)]','g');
                if(!recommended){
                    var blobValue = '';
                    var replacedLength = $(value).val().length;
                    if($(value).hasClass('autocomplete')){
                        replacedLength = $(value).val().replace(regex, ' ').length;
                    }
                    var lengthDiff = $(value).val().length - replacedLength;
                    var blobValue1 = $(value).val().substr(0, limit + lengthDiff);
                    var blobValue2 = $(value).val().substr(limit + lengthDiff);
                    if(blobValue2 != ''){
	                    blobValue = blobValue1 + '<s style=\"color:red;\">' + blobValue2 + '</s>';
	                }
	                else{
	                    blobValue = blobValue1;
	                }
                    limit -= (blobValue1.length - lengthDiff);
                }
                else{
                    var blobValue = $(value).val();
                }
                if($(value).hasClass('autocomplete')){
                    blobValues.push(blobValue.replace(regex, '<b>\$1</b>'));
                }
                else{
                    blobValues.push(blobValue);
                }
            });
            
            $('#preview_{$this->getPostId()} .autocomplete').css('display', 'none');
            
            $.each($('#preview_{$this->getPostId()} textarea'), function(index, value){
                $(value).replaceWith('<span>' + blobValues[index].replace(/\\n/g, '<br />') + '</span>');
            });
            
            $('#preview_{$this->getPostId()} .pdfnodisplay').css('display', 'none');
            
            $('#preview_{$this->getPostId()}').dialog('open');
        }
        </script>");
    }
    
    function renderForPDF(){
        global $wgOut;
        $limit = $this->getLimit();
	    $length = $this->getActualNChars();
	    $textareas = $this->getTextareas();
	    $text = "";
	    $html = "";
	    $noun = $this->getAttr("noun", "Project");
        $pluralNoun = strtolower($noun)."s";
	    $recommended = $this->getAttr('recommended', false);
        if($recommended){
            $type = "recommended";
        }
        else{
            $type = "maximum of";
        }
	    if($limit > 0){
	        $class = "inlineMessage";
	        foreach($textareas as $textarea){
	            $blobValue = str_replace("\r", "", $textarea->getBlobValue());
	            if(!$recommended){
	                $replacedLength = 0;
	                $lengthDiff = 0;
	                if($textarea instanceof AutoCompleteTextareaReportItem){
	                    $blobValue = $textarea->getReplacedBlobValue();
                        $replacedLength = $textarea->getActualNChars();
                        $lengthDiff = strlen(utf8_decode($blobValue)) - $replacedLength;
                    }
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
	            $text .= $textarea->processCData($blobValue);
	            if($length > $this->getLimit()){
	                $class = "inlineError";
	            }
	            else if($length == ""){
	                $class = "inlineWarning";
	            }
	        }
	        $plural = "s";
	        if($length == 1){
	            $plural = "";
	        }
	        $html .= "<span class='$class'><small>(<i>Reported By {$noun} - currently {$length} character{$plural} out of a {$type} {$this->getLimit()} across all {$pluralNoun}.</i>)</small></span>";
	        $html .= nl2br("<br />{$text}");
	    }
	    $wgOut->addHTML($html);
    }
}

?>
