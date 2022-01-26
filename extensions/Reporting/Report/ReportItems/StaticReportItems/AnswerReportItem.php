<?php

class AnswerReportItem extends StaticReportItem {

	function render(){
	    global $wgOut;
	    $label = $this->getAttr("label");
	    $answer = $this->getAttr("answer");
	    $value = $this->getBlobValue();
	    $a = ($value == $answer) ? "<span style='color:#008800;'>Correct!</span>" : "<span style='color:#ff0000;'>Incorrect</span>";
	    $item = "<h3>{$label}: {$a}</h3>
                <p><b>{$answer}</b></p>
                <p>Change is difficult, but when you know the ingredients, it can be manageable.  Having confidence in your chosen actions, support from others and access to resources, and being motivated by internal goals will help you be successful in making healthy lifestyle choices. Keep these in mind if you hit a roadblock and come back to them to rethink how they apply to you.</p>";
        $item = $this->processCData($item);
		$wgOut->addHTML($item);
	}
	
	function renderForPDF(){
	    $this->render();
	}
	
}

?>
