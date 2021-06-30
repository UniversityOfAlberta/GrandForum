<?php

class Label extends UIElement {
    
    function Label($id, $name, $value, $validations=VALIDATE_NOTHING){
        parent::UIElement($id, $name, $value, $validations);
        $this->attr('class', 'label tooltip');
        $this->colon = ":";
    }
    
    function render(){
        $redStar = "";
        if($this->isValidationSet(VALIDATE_NOT_NULL)){
            $redStar = "<span style='color:red;'>*</span>";
        }
        return "<div id='{$this->id}' {$this->renderAttr()} title='{$this->value}'>{$this->name}{$this->colon}<sup>{$redStar}</sup></div>";
    }
}

?>
