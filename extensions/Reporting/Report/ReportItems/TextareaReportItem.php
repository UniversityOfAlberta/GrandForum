<?php

$dir = dirname(__FILE__);
require_once($dir . '/../../../../Classes/SmartDomDocument/SmartDomDocument.php');

class TextareaReportItem extends AbstractReportItem {

    function render(){
        global $wgOut, $wgServer, $wgScriptPath;
        $item = $this->getHTML();
        $limit = $this->getLimit();
        $recommended = strtolower($this->getAttr('recommended', 'false'));
        if($limit > 0 && strtolower($this->getAttr('rich', 'false')) != 'true'){
            $item .= "<script type='text/javascript'>
                $(document).ready(function(){
                    var strlen = $('textarea[name={$this->getPostId()}]').val().length;
                    changeColor{$this->getPostId()}($('textarea[name={$this->getPostId()}]'), strlen);
                });
            </script>";
        }
        if(strtolower($this->getAttr('rich', 'false')) == 'true'){
            $imgConst = DPI_CONSTANT*72/96;
            $item .= "<script type='text/javascript'>
                if($('#tinyMCEUpload').length == 0){
                    $('body').append(\"<iframe id='tinyMCEUpload' name='tinyMCEUpload' style='display:none'></iframe>\" +
                                     \"<form id='tinyMCEUploadForm' action='$wgServer$wgScriptPath/index.php?action=tinyMCEUpload' target='tinyMCEUpload' method='post' enctype='multipart/form-data' style='width:0px;height:0;overflow:hidden;position:absolute;left:-1000px;'>\" +
                                         \"<input name='image' type='file'>\" +
                                     \"</form>\");
                    $('#tinyMCEUploadForm input').change(function(){
                        $('#tinyMCEUploadForm').ajaxSubmit({
                            success: function(d){
                                eval(d);
                            }
                        });
                        $('#tinyMCEUploadForm input').val('');
                    });
                }
                $(document).ready(function(){
                    //$('<div class=\"small\"><b>Note:</b> Inserted images should be at least 150dpi otherwise they will either appear as small or will be distorted if their size is enlarged.</div>').insertBefore('textarea[name={$this->getPostId()}]');
                    var readOnly = false;
                    if($('textarea[name={$this->getPostId()}]').attr('disabled') == 'disabled'){
                        readOnly = true;
                    }
                    $('textarea[name={$this->getPostId()}]').show();
                    $('textarea[name={$this->getPostId()}]').tinymce({
                        theme: 'modern',
                        readonly: readOnly,
                        menubar: false,
                        plugins: 'link image charmap lists table paste wordcount',
                        toolbar: [
                            'undo redo | bold italic underline | link image charmap | table | bullist numlist outdent indent | subscript superscript | alignleft aligncenter alignright alignjustify'
                        ],
                        file_browser_callback: function(field_name, url, type, win) {
                            if(type=='image') $('#tinyMCEUploadForm input').click();
                        },
                        paste_data_images: true,
                        invalid_elements: 'h1, h2, h3, h4, h5, h6, h7, font',
                        imagemanager_insert_template : '<img src=\"{\$url}\" width=\"{\$custom.width}\" height=\"{\$custom.height}\" />',
                        paste_postprocess: function(plugin, args) {
                            var imgs = $('img', args.node);
                            imgs.each(function(i, el){
                                var uniqueId = _.uniqueId('tinymceimg_');
                                $(el).removeAttr('style');
                                $(el).attr('id', uniqueId);
                                $(el).hide();
                                if($(el).css('display') != 'none'){
                                    $(el).remove();
                                }
                                else{
                                    el.onload = function() {
                                        // access image size here
                                        var content = $('<div>' + tinyMCE.activeEditor.getContent() + '</div>');
                                        $('img#' + uniqueId, content).attr('width', el.naturalWidth/$imgConst);
                                        $('img#' + uniqueId, content).attr('height', el.naturalHeight/$imgConst);
                                        $('img#' + uniqueId, content).css('width', el.naturalWidth/$imgConst);
                                        $('img#' + uniqueId, content).css('height', el.naturalHeight/$imgConst);
                                        $('img#' + uniqueId, content).show();
                                        $('img#' + uniqueId, content).attr('id', '');
                                        tinyMCE.activeEditor.setContent($(content).html());
                                    };
                                }
                            });
                        },
                        'formats' : {
                        },
                        setup: function(ed){
                            if('$limit' > 0){
                                var updateCount = function(e){
                                    initResizeEvent();
                                    ed.undoManager.add();
                                    var wordcount = ed.plugins.wordcount.getCount();
                                    changeColor{$this->getPostId()}(null, wordcount);
                                    $('#{$this->getPostId()}_chars_left').text(wordcount);
                                    if(wordcount > $limit && '$recommended' == 'false' && e.keyCode != 8 && e.keyCode != 46) {
                                        var status = tinymce.dom.Event.cancel(e);
                                        if(detectIE() != false){
                                            ed.execCommand('UNDO');
                                            ed.selection.collapse();
                                        }
                                        else{
                                            ed.execCommand('DELETE');
                                        }
                                        return status;
                                    }
                                };
                                ed.on('keydown', updateCount);
                                ed.on('keyup', updateCount);
                                ed.on('change', function(e){
                                    updateCount(e);
                                    saveAll();
                                });
                                ed.on('init', updateCount);
                                ed.on('paste', updateCount);
                                ed.on('undo', saveAll);
                                ed.on('redo', saveAll);
                            }
                            else{
                                var updateCount = function(e){
                                    initResizeEvent();
                                    ed.undoManager.add();
                                };
                                ed.on('keydown', updateCount);
                                ed.on('keyup', updateCount);
                                ed.on('change', function(e){
                                    updateCount(e);
                                    saveAll();
                                });
                                ed.on('init', updateCount);
                                ed.on('paste', updateCount);
                                ed.on('undo', saveAll);
                                ed.on('redo', saveAll);
                            }
                        }
                    });
                    initResizeEvent();
                });
            </script>";
        }
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
    }
    
    function calculateHeight($limit){
        $rich = (strtolower($this->getAttr('rich', 'false')) == 'true');
        if($limit > 0 && !$rich){
            $height = min(500, max(125, (pow($limit, 0.75))))."px";
        }
        else{
            $height = $this->getAttr('height', '200px');
        }
        return $height;
    }
    
    function getHTML(){
        $value = $this->getBlobValue();
		$default = $this->getAttr('default', '');
		if($value === null && $default != ''){
		    $value = $default;
		    $value = str_replace('<br />', '', $value);
		}
		
        $rows = $this->getAttr('rows', 5);
        $width = $this->getAttr('width', '100%');
        $limit = $this->getLimit();
        $height = $this->calculateHeight($limit);
        $rich = strtolower($this->getAttr('rich', 'false'));
        $item = "";
        if($limit > 0){
            $recommended = $this->getAttr('recommended', false);
            $words = "characters";
            if(strtolower($this->getAttr('rich', 'false')) == 'true'){
                $words = "words";
            }
            if($recommended){
                $type = "recommended";
            }
            else{
                $type = "maximum of";
            }
            
            $item =<<<EOF
            <p id='limit_{$this->getPostId()}'><span class="pdf_hide inlineMessage">(currently <span id="{$this->getPostId()}_chars_left">0</span> {$words} out of a {$type} {$limit})</span></p>
            <script type='text/javascript'>
                function changeColor{$this->getPostId()}(element, strlen){
                    var type = "{$type}";
                    if(strlen > $limit && type=="recommended"){
                        $('#limit_{$this->getPostId()} > span').addClass('inlineWarning');
                        $('#limit_{$this->getPostId()} > span').removeClass('inlineError');
                    }
                    else if(strlen > $limit){
                        $('#limit_{$this->getPostId()} > span').addClass('inlineError');
                        $('#limit_{$this->getPostId()} > span').removeClass('inlineWarning');
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
                    if('$rich' != 'true'){
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
                    }
                });
            </script>
EOF;
        }
        $divHeight = "";
        $hidden = "display:none;";
        if(strtolower($this->getAttr('rich', 'false')) != 'true'){
            $divHeight = "height:{$height};";
            $hidden = "";
        }
        $item .= <<<EOF
            <div style='display:inline-block;width:{$width};{$divHeight}padding:2px;box-sizing:border-box;'>
                <textarea id="{$this->getPostId()}" rows='$rows' style="width:100%;height:{$height};{$hidden}resize: vertical;margin:0;" 
                        name="{$this->getPostId()}">$value</textarea>
            </div>
EOF;
        return $item;
    }
    
    function getHTMLForPDF(){
        global $config;
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
            //$html .= "<span class='$class'><small>(<i>currently {$length} ".Inflect::smart_pluralize($length, "character")." out of a {$type} {$limit}</i>)</small></span>";
        }
        $dom = new SmartDOMDocument();
        $dom->loadHTML($blobValue);

        if(strtolower($this->getAttr('rich', 'false')) == 'true'){
            // Set links to externalLink for TinyMCE links
            $as = $dom->getElementsByTagName("a");
            for($i=0; $i<$as->length; $i++){
                $a = $as->item($i);
                if($a->getAttribute('class') != 'anchor' && 
                   $a->getAttribute('class') != 'mce-item-anchor' &&
                   $a->getAttribute('class') != 'externalLink' && 
                   $a->textContent != ""){
                    $a->setAttribute('class', "externalLink");
                }
            }
        }

        $imgs = $dom->getElementsByTagName("img");
        $margins = $config->getValue('pdfMargins');
        $maxWidth = PDFGenerator::cmToPixels(21.59 - $margins['left'] - $margins['right'])*DPI_CONSTANT;
        $maxHeight = PDFGenerator::cmToPixels(27.94 - $margins['top'] - $margins['bottom'])*DPI_CONSTANT;
        foreach($imgs as $img){
            $imgConst = DPI_CONSTANT*72/96;
            $style = $img->getAttribute('style');
            preg_match("/width:\s*([0-9]*\.{0,1}[0-9]*)/", $style, $styleWidth);
            preg_match("/height:\s*([0-9]*\.{0,1}[0-9]*)/", $style, $styleHeight);
            if(isset($styleWidth[1]) && isset($styleHeight[1])){
                $widthPerc = ($styleWidth[1]*$imgConst)/$maxWidth;
                $heightPerc = ($styleHeight[1]*$imgConst)/$maxHeight;
                $perc = max(1.0, $widthPerc, $heightPerc);
                $style .= "width: ".($styleWidth[1]*$imgConst/$perc)."px !important;";
                $style .= "height: ".($styleHeight[1]*$imgConst/$perc)."px !important;";
            }
            $style .= "max-width: {$maxWidth}px;";
            $style .= "max-height: {$maxHeight}px;";
            $img->setAttribute('style', $style);
            
            $attrWidth = floatval($img->getAttribute('width'));
            $attrHeight = floatval($img->getAttribute('height'));
            $widthPerc = $attrWidth*$imgConst/$maxWidth;
            $heightPerc = $attrHeight*$imgConst/$maxHeight;
            $perc = max(1.0, $widthPerc, $heightPerc);
            $img->setAttribute('width', $attrWidth*$imgConst/$perc);
            $img->setAttribute('height', $attrHeight*$imgConst/$perc);
        }
        
        $tables = $dom->getElementsByTagName('table');
        $margins = $config->getValue('pdfMargins');
        foreach($tables as $table){
            $maxWidth = PDFGenerator::cmToPixels(21.59 - $margins['left'] - $margins['right']); // Standard Letter width - margins
            $width = min($maxWidth, intval($table->getAttribute('width')));
            $table->setAttribute('width', $width);
            
            $table->setAttribute('cellspacing', ceil(1*DPI_CONSTANT));
            $table->setAttribute('cellpadding', ceil(3*DPI_CONSTANT));
            $table->setAttribute('rules', 'all');
            $table->setAttribute('frame', 'box');
        }
        
        $blobValue = "$dom";
        if(strtolower($this->getAttr('rich', 'false')) == 'true'){
            $blobValue = str_replace("</p>", "<br /><br style='font-size:1em;' />", $blobValue);
            $blobValue = str_replace("<p>", "", $blobValue);
            $blobValue = str_replace_last("<br /><br style='font-size:1em;' />", "", $blobValue);
            $html .= "<div class='tinymce'>$blobValue</div>";
        }
        else{
            $blobValue = str_replace("<p>", "", "{$blobValue}");
            $blobValue = str_replace("</p>", "", "{$blobValue}");
            $html .= nl2br($blobValue);
        }
        return $html;
    }
    
    function getBlobValue(){
        $value = parent::getBlobValue();
        if($value !== null){
            if(strtolower($this->getAttr('rich', 'false')) == 'true'){
                // Don't alter the text in any way
            }
            else{
                $value = str_replace("<", "&lt;", $value);
                $value = str_replace(">", "&gt;", $value);
                nl2br($value);
            }
        }
        return $value;
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
}

?>
