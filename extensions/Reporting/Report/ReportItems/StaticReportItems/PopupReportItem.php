<?php

class PopupReportItem extends StaticReportItem {

	function render(){
		global $wgOut;
		$title = $this->getAttr("title", "Open Popup");
		$width = $this->getAttr("width", "800");
        $height = $this->getAttr("height", "600");
        $position = $this->getAttr("position", 'center');
        $draggable = $this->getAttr("draggable", "true");
        $modal = $this->getAttr("modal", "false");
        $text = $this->getAttr("text");
        $url = $this->getAttr("url");
        
        if($text == "" && $url == ""){
            $item = $this->processCData("");
            $wgOut->addHTML($item);
            return;
        }
        
        $html = "";
        if($url != ""){
            $html .= "<script type='text/javascript'>
                function openDialog{$this->getPostid()}(){
                    $('#{$this->getPostId()}').dialog('open');
                    $.get('$url', function(response){
                        $('#{$this->getPostId()}').html(response);
                    });
                }
            </script>";
        }
        else{
            $html .= "<script type='text/javascript'>
                function openDialog{$this->getPostid()}(){
                    $('#{$this->getPostId()}').dialog('open');
                }
            </script>";
        }
        $html .= <<<EOF
            <a class='pdfnodisplay' style="font-style:italic; font-size:11px; font-weight:bold;" onclick="openDialog{$this->getPostId()}(); return false;" href="#">{$title}</a>
            <div style='display:none;' title="{$title}" id="{$this->getPostId()}">{$text}</div>
            <script type='text/javascript'>
                $(document).ready(function(){
                    $("#{$this->getPostId()}").dialog({ autoOpen: false, height: '{$height}', width: '{$width}', position: '{$position}', draggable: {$draggable}, modal: {$modal} });
                });
           </script>
EOF;
	    $item = $this->processCData($html);
	    $wgOut->addHTML($item);
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $wgOut->addHTML($this->processCData(""));
	}
}

?>
