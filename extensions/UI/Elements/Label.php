<?php

class Label extends UIElement {
    var $colon = true; 
    function Label($id, $name, $value,$validations=VALIDATE_NOTHING,$colon=true){
        parent::UIElement($id, $name, $value, $validations);
        $this->attr('class', 'label tooltip');
	$this->colon = $colon;
    }
    
    function render(){
        $redStar = "";
        if($this->isValidationSet(VALIDATE_NOT_NULL)){
            $redStar = "<span style='color:red;'>*</span>";
        }
	$str ="";
	if($this->colon){
	    $str = ":";
	}
        return "<div id='{$this->id}' {$this->renderAttr()} title='{$this->value}'>{$this->name}$str<sup>{$redStar}</sup></div>";
    }
}

?>
