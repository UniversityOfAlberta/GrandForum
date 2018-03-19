<?php

class FootnotesReportItem extends AbstractReportItem {
	function render(){
            global $wgOut, $wgServer, $wgScriptPath, $config;
            $value = $this->getBlobValue();
            $width = (isset($this->attributes['width'])) ? $this->attributes['width'] : "100%";
            $height = (isset($this->attributes['height'])) ? $this->attributes['height'] : "100%";
            $value = str_replace("<", "<&lt;", $value);
            $value = str_replace(">", "<&gt;", $value);
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
                $('.footnote_dialog').dialog( "destroy" );
                $('.footnote_dialog').dialog({
                    autoOpen: false, 
                    width: 400, 
                    height: 200,
                    buttons: {
                        "Done": function() {
                            $( this ).dialog( "close" );
                        }
                    }
                });
                $('.footnote_dialog').parent().appendTo($('#reportBody'));
                function openDialog(num){
                    $('#topnote'+num).dialog("open");
                }
            </script>
EOF;
            $item .= $jscript;
            $item = $this->processCData($item);
            $wgOut->addHTML("$item");
	}
	
	function renderForPDF(){
            global $wgOut;
            static $top_anchor = 1;
            static $bottom_anchor = 1;
            $value = $this->getBlobValue();
            $value = str_replace("<", "<&lt;", $value);
            $value = str_replace(">", "<&gt;", $value);
            nl2br($value);
            $blob = $this->getMD5();
            $isTopAnchor = (strtolower($this->getAttr('isTopAnchor', 'true')) == 'true');
            if($isTopAnchor){ 
                $item = "<a href='#footnote$blob' name='topnote$blob' class='anchor' id='goToFootnote$blob'>[$top_anchor]</a>";
                $top_anchor+= 1;
            }
            else{
                $item = "<a href='#topnote$blob' name='footnote$blob' class='anchor' id='goToTopnote$blob'>[$bottom_anchor]</a> {$value}"; 
                $bottom_anchor+=1;
            }
	    $item = $this->processCData($item);
            $wgOut->addHTML("$item");
	}
}

?>
