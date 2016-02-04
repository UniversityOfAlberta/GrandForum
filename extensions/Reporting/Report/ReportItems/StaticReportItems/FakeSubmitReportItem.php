<?php

class FakeSubmitReportItem extends StaticReportItem {

	function render(){
        global $wgServer, $wgScriptPath, $wgOut;
        $message = str_replace("'", "&#39;", $this->getAttr('message', "Your Report was submitted"));
        $html = "<input id='{$this->getPostId()}' type='button' value='Submit' />";
        $wgOut->addHTML($this->processCData($html));
        $wgOut->addHTML("<script type='text/javascript'>
            $('#{$this->getPostId()}').click(function(){
                saveBackup(false);
                clearAllMessages();
                setTimeout(function(){
                    addSuccess('{$message}');
                }, 100);
            });
        </script>");
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $wgOut->addHTML($this->processCData(""));
	}
}

?>
