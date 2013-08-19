<?php

class NINameHeaderReportItem extends StaticReportItem {

	function render(){
        global $wgServer, $wgScriptPath, $wgOut;
        
        $ni = null;
        $ni = Person::newFromId($this->personId);
        $ni_name = $ni->getNameForForms();
        $anonymous =  $this->getAttr('anonymous', false);
        $data = $this->parent->parent->getData();
        $count = 1;
        foreach ($data as $tuple) {
            if($tuple['person_id'] == $this->personId){
                break;
            }
            $count++;
        }

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
