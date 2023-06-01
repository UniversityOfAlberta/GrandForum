<?php

class FootnotesReportItem extends AbstractReportItem {
    
    static $top_anchor = 1;
    static $bottom_anchor = 1;

    function getHTML(){
        global $wgServer, $wgScriptPath, $config;
        $value = $this->getBlobValue();
        $width = (isset($this->attributes['width'])) ? $this->attributes['width'] : "100%";
        $height = (isset($this->attributes['height'])) ? $this->attributes['height'] : "100%";
        $value = str_replace("&", "&amp;", $value);
        $value = str_replace("<", "&lt;", $value);
        $value = str_replace(">", "&gt;", $value);
        $isTopAnchor = (strtolower($this->getAttr('isTopAnchor', 'true')) == 'true');
        $item = "
          <a href='#' onclick='openDialog(\"{$this->getPostId()}\"); return false;' id='openfootnote{$this->getPostId()}'>
            <img src='$wgServer$wgScriptPath/{$config->getValue('iconPath')}pen_16x16.png' />
          </a>
          <div id='topnote{$this->getPostId()}' class='footnote_dialog' title='Add Footnote' style='display:none'>
            <textarea type='text' name='{$this->getPostId()}' style='width:{$width};height:{$height};'>{$value}</textarea>
          </div>";

         $jscript =<<<EOF
             <script type='text/javascript'>
                $("body > #topnote{$this->getPostId()}").remove(); // Sometimes dialog remains as a child of body for some reason
                $('#topnote{$this->getPostId()}').dialog({
                    autoOpen: false, 
                    width: 400, 
                    height: 200,
                    buttons: {
                        "Done": function() {
                            $(this).dialog( "close" );
                        }
                    }
                });
                $('#topnote{$this->getPostId()}').parent().detach().appendTo($('#reportBody'));
                function openDialog(num){
                    $('#topnote'+num).dialog("open");
                }
            </script>
EOF;
        $item .= $jscript;
        return $item;
    }
    
    function getPDFHTML(){
        $value = $this->getBlobValue();
        $value = str_replace("&", "&amp;", $value);
        $value = str_replace("<", "&lt;", $value);
        $value = str_replace(">", "&gt;", $value);
        $value = nl2br($value);
        $blob = md5($this->getPostId());
        $isTopAnchor = (strtolower($this->getAttr('isTopAnchor', 'true')) == 'true');
        $item = "";
        if(trim($value) != ""){
            if($isTopAnchor){ 
                $item = "<a href='#footnote$blob' name='topnote$blob' class='anchor' id='goToFootnote$blob'>[".self::$top_anchor."]</a>";
                self::$top_anchor += 1;
            }
            else{
                $item = "<a style='vertical-align:top;' href='#topnote$blob' name='footnote$blob' class='anchor' id='goToTopnote$blob'>[".self::$bottom_anchor."]</a> <div style='display:inline-block;vertical-align:top;'>{$value}</div>"; 
                self::$bottom_anchor += 1;
            }
        }
        if(trim($value) != "" || $isTopAnchor){
            return $item;
        }
        return "";
    }

    function render(){
        global $wgOut;
        $item = $this->processCData($this->getHTML());
        $wgOut->addHTML($item);
    }

    function renderForPDF(){
        global $wgOut;
        $isTopAnchor = (strtolower($this->getAttr('isTopAnchor', 'true')) == 'true');
        $item = $this->getPDFHTML();
        if(trim($item) != "" || $isTopAnchor){
            $item = $this->processCData($item);
            $wgOut->addHTML("$item");
        }
    }
}

?>
