<?php

class AdminCustomTab extends AbstractTab {
	
	function AdminCustomTab(){
        parent::AbstractTab("Custom Visualizations");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath;
	    $this->html .= "asdf";
	}
	
}
?>
