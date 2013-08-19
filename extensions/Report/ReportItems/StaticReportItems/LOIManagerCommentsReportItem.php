<?php

class LOIManagerCommentsReportItem extends StaticReportItem {

	function render(){
        global $wgServer, $wgScriptPath, $wgOut;
        
        $loi = null;
        $loi = LOI::newFromId($this->projectId);
        $manager_comments = $loi->getManagerComments();

        $html =<<<EOF
        	<p>{$manager_comments}</p>
EOF;

	    $wgOut->addHTML($html);
	}
	
	function renderForPDF(){
	    $this->render();
	}
}

?>
