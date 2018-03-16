<?php

class FootnotesReportItem extends AbstractReportItem {
	function render(){
            global $wgOut, $wgServer, $wgScriptPath, $config;
            $value = $this->getBlobValue();
            $width = (isset($this->attributes['width'])) ? $this->attributes['width'] : "250px";
            $value = str_replace("'", "&#39;", $value);
            $isTopAnchor = (strtolower($this->getAttr('isTopAnchor', 'true')) == 'true');
            $item = "
              <a href='#' onclick='openDialog(\"{$this->getPostId()}\"); return false;' id='openfootnote{$this->getPostId()}'>
                <img src='$wgServer$wgScriptPath/{$config->getValue('iconPath')}pen_16x16.png' />
              </a>
              <div id='topnote{$this->getPostId()}' class='footnote_dialog' title='Add Footnote' style='display:none'>
                Footnote:<input type='text' name='{$this->getPostId()}' style='width:{$width};' value='{$value}' />
              </div>";

             $jscript =<<<EOF
            <script type='text/javascript'>
                $('.footnote_dialog').dialog( "destroy" );
                $('.footnote_dialog').dialog({ autoOpen: false, width: 400, height: 100 });
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
            $value = str_replace("'", "&#39;", $this->getBlobValue());
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
