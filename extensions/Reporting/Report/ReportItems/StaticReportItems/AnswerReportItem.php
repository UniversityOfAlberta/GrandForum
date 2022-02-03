<?php

class AnswerReportItem extends StaticReportItem {

	function render(){
	    global $wgOut;
	    $label = $this->getAttr("label");
	    $answer = $this->getAttr("answer");
	    $value = $this->getBlobValue();
	    if($value != ""){
	        $a = ($value == $answer) ? "<span style='color:#008800;'>Correct!</span>" : "<span style='color:#ff0000;'>Incorrect</span>";
	        $item = "<h3>{$label}: {$a}</h3>
                    <p><b>{$answer}</b></p>";
            $item = $this->processCData($item);
        }
        else {
            $item = "<h3>{$label}:</h3>
                     <p>Please answer the question before viewing the answer</p>";
        }
		$wgOut->addHTML($item);
	}
	
	function renderForPDF(){
	    $this->render();
	}
	
}

?>
