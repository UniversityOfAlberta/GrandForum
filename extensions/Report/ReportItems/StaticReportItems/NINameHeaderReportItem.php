<?php

class NINameHeaderReportItem extends StaticReportItem {

	function render(){
        global $wgServer, $wgScriptPath, $wgOut;
        
        $ni = null;
        $ni = Person::newFromId($this->personId);
        $ni_name = $ni->getNameForForms();
        $anonymous =  $this->getAttr('anonymous', false);
        $count = $this->parent->count;
        if($anonymous){
        	$html =<<<EOF
        	<h2>Review {$count}</h2>
EOF;
        }else{
        	$html =<<<EOF
        	<h2>{$ni_name}</h2>
EOF;
		}

	    $wgOut->addHTML($html);
	}
	
	function renderForPDF(){
	    $this->render();
	}
}

?>
